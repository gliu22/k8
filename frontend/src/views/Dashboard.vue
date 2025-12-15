<template>
  <div>
    <div class="pb-5 border-b border-gray-200">
      <h3 class="text-2xl leading-6 font-bold text-gray-900">Dashboard</h3>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Projects</dt>
                <dd class="text-3xl font-semibold text-gray-900">{{ stats.totalProjects }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Tasks</dt>
                <dd class="text-3xl font-semibold text-gray-900">{{ stats.totalTasks }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">My Tasks</dt>
                <dd class="text-3xl font-semibold text-gray-900">{{ stats.myTasks }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-8">
      <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Projects</h4>
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul v-if="recentProjects.length" class="divide-y divide-gray-200">
          <li v-for="project in recentProjects" :key="project.id">
            <router-link :to="`/projects/${project.id}`" class="block hover:bg-gray-50">
              <div class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-indigo-600 truncate">{{ project.name }}</p>
                  <div class="ml-2 flex-shrink-0 flex">
                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      {{ project.status }}
                    </p>
                  </div>
                </div>
                <div class="mt-2 sm:flex sm:justify-between">
                  <div class="sm:flex">
                    <p class="flex items-center text-sm text-gray-500">{{ project.description }}</p>
                  </div>
                </div>
              </div>
            </router-link>
          </li>
        </ul>
        <div v-else class="text-center py-12">
          <p class="text-gray-500">No projects yet</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const stats = ref({
  totalProjects: 0,
  totalTasks: 0,
  myTasks: 0
})

const recentProjects = ref([])

const fetchDashboardData = async () => {
  try {
    const [projectsRes, tasksRes] = await Promise.all([
      api.get('/projects'),
      api.get('/tasks')
    ])

    stats.value.totalProjects = projectsRes.data.total || 0
    stats.value.totalTasks = tasksRes.data.total || 0
    stats.value.myTasks = tasksRes.data.data?.filter(t => t.assigned_to === null).length || 0
    recentProjects.value = projectsRes.data.data?.slice(0, 5) || []
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
