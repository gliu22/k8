<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'estimated_hours',
        'actual_hours',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }
        return $this->due_date->isPast();
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->whereNotNull('due_date')
                     ->whereDate('due_date', '<', now());
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get detailed task analytics with complex aggregations using raw SQL
     * This demonstrates how to handle complex queries that are difficult with query builder
     *
     * @param int $projectId
     * @return array
     */
    public static function getTaskAnalytics($projectId)
    {
        return DB::select("
            SELECT
                p.id as project_id,
                p.name as project_name,
                COUNT(DISTINCT t.id) as total_tasks,
                COUNT(DISTINCT t.assigned_to) as unique_assignees,

                -- Status breakdown
                SUM(CASE WHEN t.status = 'todo' THEN 1 ELSE 0 END) as todo_count,
                SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN t.status = 'review' THEN 1 ELSE 0 END) as review_count,
                SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_count,

                -- Priority breakdown
                SUM(CASE WHEN t.priority = 'urgent' THEN 1 ELSE 0 END) as urgent_count,
                SUM(CASE WHEN t.priority = 'high' THEN 1 ELSE 0 END) as high_count,

                -- Time tracking
                COALESCE(SUM(t.estimated_hours), 0) as total_estimated_hours,
                COALESCE(SUM(t.actual_hours), 0) as total_actual_hours,
                COALESCE(AVG(t.actual_hours), 0) as avg_task_hours,

                -- Efficiency metrics
                CASE
                    WHEN SUM(t.estimated_hours) > 0
                    THEN ROUND((SUM(t.actual_hours) / SUM(t.estimated_hours)) * 100, 2)
                    ELSE 0
                END as efficiency_percentage,

                -- Completion rate
                CASE
                    WHEN COUNT(t.id) > 0
                    THEN ROUND((SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) / COUNT(t.id)) * 100, 2)
                    ELSE 0
                END as completion_rate,

                -- Overdue tasks
                SUM(CASE
                    WHEN t.status != 'completed'
                    AND t.due_date IS NOT NULL
                    AND t.due_date < CURDATE()
                    THEN 1 ELSE 0
                END) as overdue_count,

                -- Top assignee by task count
                (SELECT u.name
                 FROM tasks t2
                 JOIN users u ON t2.assigned_to = u.id
                 WHERE t2.project_id = p.id
                 GROUP BY u.id, u.name
                 ORDER BY COUNT(t2.id) DESC
                 LIMIT 1
                ) as top_assignee,

                -- Average days to complete
                (SELECT AVG(DATEDIFF(t3.updated_at, t3.created_at))
                 FROM tasks t3
                 WHERE t3.project_id = p.id
                 AND t3.status = 'completed'
                ) as avg_days_to_complete

            FROM projects p
            LEFT JOIN tasks t ON p.id = t.project_id AND t.deleted_at IS NULL
            WHERE p.id = ?
            AND p.deleted_at IS NULL
            GROUP BY p.id, p.name
        ", [$projectId]);
    }

    /**
     * Get user workload report using raw SQL with window functions
     * Demonstrates advanced SQL features like ROW_NUMBER and RANK
     *
     * @param int $organizationId
     * @return array
     */
    public static function getUserWorkloadReport($organizationId)
    {
        return DB::select("
            WITH user_stats AS (
                SELECT
                    u.id as user_id,
                    u.name as user_name,
                    u.email,
                    COUNT(t.id) as total_tasks,
                    SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) as active_tasks,
                    COALESCE(SUM(t.actual_hours), 0) as total_hours,
                    SUM(CASE
                        WHEN t.status != 'completed'
                        AND t.due_date < CURDATE()
                        THEN 1 ELSE 0
                    END) as overdue_tasks
                FROM users u
                LEFT JOIN tasks t ON u.id = t.assigned_to AND t.deleted_at IS NULL
                WHERE u.organization_id = ?
                AND u.deleted_at IS NULL
                GROUP BY u.id, u.name, u.email
            )
            SELECT
                *,
                ROW_NUMBER() OVER (ORDER BY total_hours DESC) as workload_rank,
                DENSE_RANK() OVER (ORDER BY completed_tasks DESC) as productivity_rank,
                CASE
                    WHEN total_tasks > 0
                    THEN ROUND((completed_tasks / total_tasks) * 100, 2)
                    ELSE 0
                END as completion_percentage
            FROM user_stats
            ORDER BY total_hours DESC
        ", [$organizationId]);
    }

    /**
     * Get project comparison report using complex subqueries
     * Useful for comparing multiple projects side by side
     *
     * @param int $organizationId
     * @return array
     */
    public static function getProjectComparisonReport($organizationId)
    {
        return DB::select("
            SELECT
                p.id,
                p.name,
                p.status,

                -- Task metrics
                (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND deleted_at IS NULL) as total_tasks,
                (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'completed' AND deleted_at IS NULL) as completed_tasks,
                (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status != 'completed' AND due_date < CURDATE() AND deleted_at IS NULL) as overdue_tasks,

                -- Time metrics
                (SELECT SUM(estimated_hours) FROM tasks WHERE project_id = p.id AND deleted_at IS NULL) as total_estimated,
                (SELECT SUM(actual_hours) FROM tasks WHERE project_id = p.id AND deleted_at IS NULL) as total_actual,

                -- Team size
                (SELECT COUNT(DISTINCT assigned_to) FROM tasks WHERE project_id = p.id AND assigned_to IS NOT NULL AND deleted_at IS NULL) as team_size,

                -- Critical tasks
                (SELECT COUNT(*)
                 FROM tasks
                 WHERE project_id = p.id
                 AND priority IN ('urgent', 'high')
                 AND status != 'completed'
                 AND deleted_at IS NULL
                ) as critical_tasks_pending,

                -- Health score (0-100)
                CASE
                    WHEN (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND deleted_at IS NULL) = 0 THEN 0
                    ELSE ROUND(
                        (
                            (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'completed' AND deleted_at IS NULL) * 40 /
                            GREATEST((SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND deleted_at IS NULL), 1)
                        ) +
                        (
                            60 - (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status != 'completed' AND due_date < CURDATE() AND deleted_at IS NULL) * 10
                        )
                    , 0)
                END as health_score

            FROM projects p
            WHERE p.organization_id = ?
            AND p.deleted_at IS NULL
            ORDER BY health_score DESC
        ", [$organizationId]);
    }
}