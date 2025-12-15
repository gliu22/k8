<template>
  <div v-if="project">
    <div class="pb-5 border-b border-gray-200">
      <div class="flex justify-between items-center">
        <div>
          <h3 class="text-2xl leading-6 font-bold text-gray-900">{{ project.name }}</h3>
          <p class="mt-2 text-sm text-gray-500">{{ project.description }}</p>
        </div>
        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
          {{ project.status }}
        </span>
      </div>
    </div>

    <div class="mt-6">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-lg font-medium text-gray-900">Tasks</h4>
        <button
          @click="showCreateTaskModal = true"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
        >
          Add Task
        </button>
      </div>

      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul v-if="project.tasks?.length" class="divide-y divide-gray-200">
          <li v-for="task in project.tasks" :key="task.id" class="px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h5 class="text-sm font-medium text-gray-900">{{ task.title }}</h5>
                <p class="mt-1 text-sm text-gray-500">{{ task.description }}</p>
                <div class="mt-2 flex items-center space-x-4">
                  <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                    {{ task.status }}
                  </span>
                  <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    {{ task.priority }}
                  </span>
                  <span v-if="task.assignee" class="text-xs text-gray-500">
                    Assigned to: {{ task.assignee.name }}
                  </span>
                </div>
              </div>
            </div>
          </li>
        </ul>
        <div v-else class="text-center py-12">
          <p class="text-gray-500">No tasks yet</p>
        </div>
      </div>
    </div>

    <div v-if="showCreateTaskModal" class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateTaskModal = false"></div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create New Task</h3>
          <form @submit.prevent="createTask">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input
                  v-model="newTask.title"
                  type="text"
                  required
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  v-model="newTask.description"
                  rows="3"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Priority</label>
                <select
                  v-model="newTask.priority"
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
                Create
              </button>
              <button
                type="button"
                @click="showCreateTaskModal = false"
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
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const project = ref(null)
const showCreateTaskModal = ref(false)

const newTask = ref({
  title: '',
  description: '',
  priority: 'medium',
  status: 'todo'
})

const fetchProject = async () => {
  try {
    const response = await api.get(`/projects/${route.params.id}`)
    project.value = response.data
  } catch (error) {
    console.error('Error fetching project:', error)
  }
}

const createTask = async () => {
  try {
    await api.post('/tasks', {
      ...newTask.value,
      project_id: route.params.id
    })
    showCreateTaskModal.value = false
    newTask.value = { title: '', description: '', priority: 'medium', status: 'todo' }
    await fetchProject()
  } catch (error) {
    console.error('Error creating task:', error)
  }
}

onMounted(() => {
  fetchProject()
})
</script>
