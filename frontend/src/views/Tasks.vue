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
            <button
              @click="openEditModal(task)"
              class="ml-4 text-indigo-600 hover:text-indigo-900 text-sm font-medium"
            >
              Edit
            </button>
          </div>
        </li>
      </ul>
      <div v-else class="text-center py-12">
        <p class="text-gray-500">No tasks found</p>
      </div>
    </div>

    <div v-if="showEditTaskModal" class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showEditTaskModal = false"></div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Task</h3>
          <form @submit.prevent="updateTask">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input
                  v-model="editingTask.title"
                  type="text"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  v-model="editingTask.description"
                  rows="3"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select
                  v-model="editingTask.status"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="todo">To Do</option>
                  <option value="in_progress">In Progress</option>
                  <option value="review">Review</option>
                  <option value="completed">Completed</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Priority</label>
                <select
                  v-model="editingTask.priority"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
              <button
                type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:text-sm"
              >
                Update
              </button>
              <button
                type="button"
                @click="showEditTaskModal = false"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const tasks = ref([])
const loading = ref(true)
const showEditTaskModal = ref(false)

const filters = ref({
  status: '',
  priority: ''
})

const editingTask = ref({
  id: null,
  title: '',
  description: '',
  priority: 'medium',
  status: 'todo'
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

const openEditModal = (task) => {
  editingTask.value = {
    id: task.id,
    title: task.title,
    description: task.description,
    priority: task.priority,
    status: task.status
  }
  showEditTaskModal.value = true
}

const updateTask = async () => {
  try {
    await api.put(`/tasks/${editingTask.value.id}`, {
      title: editingTask.value.title,
      description: editingTask.value.description,
      priority: editingTask.value.priority,
      status: editingTask.value.status
    })
    showEditTaskModal.value = false
    await fetchTasks()
  } catch (error) {
    console.error('Error updating task:', error)
  }
}

onMounted(() => {
  fetchTasks()
})
</script>
