<template>
  <div>
    <div class="pb-5 border-b border-gray-200">
      <h3 class="text-2xl leading-6 font-bold text-gray-900">All Tasks</h3>
    </div>

    <div class="mt-6 mb-4 flex space-x-4">
      <select
        v-model="filters.status"
        @change="fetchTasks"
        class="block w-48 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
      >
        <option value="">All Statuses</option>
        <option value="todo">To Do</option>
        <option value="in_progress">In Progress</option>
        <option value="review">Review</option>
        <option value="completed">Completed</option>
      </select>

      <select
        v-model="filters.priority"
        @change="fetchTasks"
        class="block w-48 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
      >
        <option value="">All Priorities</option>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
        <option value="urgent">Urgent</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Loading...</p>
    </div>

    <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul v-if="tasks.length" class="divide-y divide-gray-200">
        <li v-for="task in tasks" :key="task.id" class="px-6 py-4 hover:bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <h5 class="text-sm font-medium text-gray-900">{{ task.title }}</h5>
                <router-link
                  v-if="task.project"
                  :to="`/projects/${task.project.id}`"
                  class="text-xs text-indigo-600 hover:text-indigo-900"
                >
                  {{ task.project.name }}
                </router-link>
              </div>
              <p class="mt-1 text-sm text-gray-500">{{ task.description }}</p>
              <div class="mt-2 flex items-center space-x-4">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-gray-100 text-gray-800': task.status === 'todo',
                    'bg-blue-100 text-blue-800': task.status === 'in_progress',
                    'bg-yellow-100 text-yellow-800': task.status === 'review',
                    'bg-green-100 text-green-800': task.status === 'completed'
                  }"
                >
                  {{ task.status }}
                </span>
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-gray-100 text-gray-800': task.priority === 'low',
                    'bg-blue-100 text-blue-800': task.priority === 'medium',
                    'bg-orange-100 text-orange-800': task.priority === 'high',
                    'bg-red-100 text-red-800': task.priority === 'urgent'
                  }"
                >
                  {{ task.priority }}
                </span>
                <span v-if="task.assignee" class="text-xs text-gray-500">
                  Assigned to: {{ task.assignee.name }}
                </span>
                <span v-else class="text-xs text-gray-500">
                  Unassigned
                </span>
                <span v-if="task.due_date" class="text-xs text-gray-500">
                  Due: {{ new Date(task.due_date).toLocaleDateString() }}
                </span>
              </div>
            </div>
          </div>
        </li>
      </ul>
      <div v-else class="text-center py-12">
        <p class="text-gray-500">No tasks found</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const tasks = ref([])
const loading = ref(true)

const filters = ref({
  status: '',
  priority: ''
})

const fetchTasks = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.priority) params.priority = filters.value.priority

    const response = await api.get('/tasks', { params })
    tasks.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching tasks:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTasks()
})
</script>
