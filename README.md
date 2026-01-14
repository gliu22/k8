# Task Management Platform - Phase 1

A multi-tenant task management system built with Laravel, Vue.js, and MySQL.

## Tech Stack

- **Backend**: Laravel 11 + PHP 8.3
- **Frontend**: Vue 3 + Vite + Tailwind CSS
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum
- **State Management**: Pinia
- **Containerization**: Docker

## Project Structure

```
task-management-platform/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Models/         # Eloquent models
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   └── Policies/       # Authorization policies
│   ├── database/
│   │   └── migrations/     # Database migrations
│   ├── routes/
│   │   └── api.php        # API routes
│   └── Dockerfile
├── frontend/               # Vue.js SPA
│   ├── src/
│   │   ├── components/
│   │   ├── views/         # Page components
│   │   ├── router/        # Vue Router config
│   │   ├── stores/        # Pinia stores
│   │   └── services/      # API service
│   └── Dockerfile
└── docker-compose.yml     # Local development setup
```

## Features Implemented

### MVP Features
- ✅ User authentication (login/register with Sanctum)
- ✅ Organization management
- ✅ Project CRUD operations
- ✅ Task CRUD operations
- ✅ Task assignment to users
- ✅ Status and priority tracking
- ✅ Dashboard with statistics

### Database Schema
- **Organizations**: Multi-tenant support
- **Users**: Belongs to organization, role-based (admin/manager/member)
- **Projects**: Organization-scoped with status tracking
- **Tasks**: Project-based with assignment, priority, and due dates

## Getting Started

### Prerequisites

- Docker Desktop
- Git

### Installation

1. **Clone the repository**
   ```bash
   cd /Users/kenliu/Desktop/k8
   ```

2. **Set up environment variables**

   Backend:
   ```bash
   cd backend
   cp .env.example .env
   ```

   Frontend:
   ```bash
   cd ../frontend
   cp .env.example .env
   ```

3. **Start with Docker Compose**
   ```bash
   cd ..
   docker-compose up -d
   ```

   This will start:
   - MySQL on port 3306
   - Laravel backend on port 8000
   - Vue.js frontend on port 3000

4. **Set up Laravel**
   ```bash
   # Access the backend container
   docker exec -it task-manager-backend bash

   # Generate application key
   php artisan key:generate

   # Run migrations
   php artisan migrate

   # Create a test organization (optional)
   php artisan tinker
   >>> \App\Models\Organization::create(['name' => 'Test Org', 'slug' => 'test-org'])
   >>> exit
   ```

5. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000/api

### Alternative: Local Development (without Docker)

#### Backend Setup
```bash
cd backend

# Install dependencies
composer install

# Set up environment
cp .env.example .env

# Generate key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

#### Frontend Setup
```bash
cd frontend

# Install dependencies
npm install

# Set up environment
cp .env.example .env

# Start dev server
npm run dev
```

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login
- `POST /api/logout` - Logout (requires auth)
- `GET /api/me` - Get current user (requires auth)

### Organizations
- `GET /api/organizations` - List organizations
- `POST /api/organizations` - Create organization
- `GET /api/organizations/{id}` - Get organization
- `PUT /api/organizations/{id}` - Update organization
- `DELETE /api/organizations/{id}` - Delete organization

### Projects
- `GET /api/projects` - List projects (filtered by user's organization)
- `POST /api/projects` - Create project
- `GET /api/projects/{id}` - Get project with tasks
- `PUT /api/projects/{id}` - Update project
- `DELETE /api/projects/{id}` - Delete project

### Tasks
- `GET /api/tasks` - List tasks (supports filters: status, priority, assigned_to, project_id)
- `POST /api/tasks` - Create task
- `GET /api/tasks/{id}` - Get task
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task

## Usage

### Test Login Credentials

For testing and development, use these credentials:

**Email**: admin@example.com
**Password**: password123

**Organization**: Demo Organization

### Creating Your First Project

1. Register or login at http://localhost:3000/login
2. Navigate to Projects page
3. Click "Create Project"
4. Fill in project details
5. Click on the project to view details
6. Add tasks to the project

### Task Management

- Filter tasks by status and priority on the Tasks page
- Assign tasks to users
- Update task status (todo → in_progress → review → completed)
- Set priorities (low, medium, high, urgent)

## Development Notes

### Laravel Models
- All models use soft deletes
- Policies enforce multi-tenant access control
- Relationships are eager-loaded for performance

### Vue.js
- Composition API with `<script setup>`
- Pinia for state management
- Axios interceptors for auth token handling
- Tailwind CSS for styling

### Authentication Flow
1. User logs in via Vue.js form
2. Laravel Sanctum issues API token
3. Token stored in localStorage
4. Axios includes token in all requests
5. Laravel validates token on protected routes

## Next Steps (Phase 2)

- Deploy MySQL to AWS RDS
- Set up DynamoDB for activity logs
- Implement file uploads to S3
- Add real-time features with Laravel Echo
- Deploy to AWS infrastructure

## Troubleshooting

### Docker Issues
```bash
# Rebuild containers
docker-compose down
docker-compose up --build

# View logs
docker-compose logs backend
docker-compose logs frontend
```

### Database Connection Issues
- Ensure MySQL container is running: `docker ps`
- Check environment variables in `.env`
- Verify database credentials

### Frontend API Issues
- Check VITE_API_URL in frontend/.env
- Ensure CORS is configured in Laravel
- Check browser console for errors

## License

MIT
