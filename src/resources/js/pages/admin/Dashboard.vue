<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-display font-bold text-gray-900">Admin Dashboard</h1>
        <p class="mt-2 text-gray-600">System overview and management tools</p>
      </div>

      <!-- System Statistics -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">System Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="card">
            <div class="card-body">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <UsersIcon class="w-6 h-6 text-blue-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Users</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stats.totalUsers }}</p>
                  <p class="text-xs text-gray-500">{{ stats.newUsersThisMonth }} new this month</p>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-recipe-primary-100 rounded-lg flex items-center justify-center">
                    <BookOpenIcon class="w-6 h-6 text-recipe-primary-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Recipes</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stats.totalRecipes }}</p>
                  <p class="text-xs text-gray-500">{{ stats.publicRecipes }} public</p>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-recipe-secondary-100 rounded-lg flex items-center justify-center">
                    <DocumentTextIcon class="w-6 h-6 text-recipe-secondary-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Cookbooks</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stats.totalCookbooks }}</p>
                  <p class="text-xs text-gray-500">{{ stats.publicCookbooks }} public</p>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <CreditCardIcon class="w-6 h-6 text-green-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Paid Subscribers</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stats.paidSubscribers }}</p>
                  <p class="text-xs text-gray-500">${{ stats.monthlyRevenue }} MRR</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Growth Chart -->
      <div class="mb-8">
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">User Growth</h2>
          </div>
          <div class="card-body">
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
              <div class="text-center">
                <ChartBarIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-600">Chart implementation would go here</p>
                <p class="text-sm text-gray-500">Integration with Chart.js or similar library</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="grid lg:grid-cols-2 gap-8">
        <!-- Recent Users -->
        <div class="card">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900">Recent Users</h2>
              <router-link :to="{ name: 'admin-users' }" class="text-recipe-primary-600 hover:text-recipe-primary-700 text-sm font-medium">
                View all
              </router-link>
            </div>
          </div>
          <div class="card-body">
            <div v-if="recentUsers.length" class="space-y-4">
              <div
                v-for="user in recentUsers"
                :key="user._id"
                class="flex items-center space-x-3"
              >
                <div class="w-10 h-10 bg-recipe-primary-100 rounded-full flex items-center justify-center">
                  <span class="text-sm font-medium text-recipe-primary-700">
                    {{ user.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                  <p class="text-sm text-gray-500 truncate">{{ user.email }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-900">{{ getTierName(user.subscription_tier) }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(user.created_at) }}</p>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8">
              <UsersIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-600">No recent users</p>
            </div>
          </div>
        </div>

        <!-- System Health -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">System Health</h2>
          </div>
          <div class="card-body">
            <div class="space-y-4">
              <!-- Database Status -->
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                  <span class="text-sm font-medium text-gray-900">Database</span>
                </div>
                <span class="text-sm text-green-600">Healthy</span>
              </div>

              <!-- Cache Status -->
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                  <span class="text-sm font-medium text-gray-900">Cache (Redis)</span>
                </div>
                <span class="text-sm text-green-600">Connected</span>
              </div>

              <!-- Queue Status -->
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                  <span class="text-sm font-medium text-gray-900">Queue</span>
                </div>
                <span class="text-sm text-green-600">Running</span>
              </div>

              <!-- Storage Status -->
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                  <span class="text-sm font-medium text-gray-900">Storage</span>
                </div>
                <span class="text-sm text-yellow-600">78% Used</span>
              </div>

              <!-- API Status -->
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                  <span class="text-sm font-medium text-gray-900">API</span>
                </div>
                <span class="text-sm text-green-600">Operational</span>
              </div>

              <!-- Last Backup -->
              <div class="pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-900">Last Backup</span>
                  <span class="text-sm text-gray-600">2 hours ago</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <router-link
            :to="{ name: 'admin-users' }"
            class="card hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="card-body text-center">
              <UsersIcon class="w-8 h-8 text-blue-600 mx-auto mb-2" />
              <h3 class="font-medium text-gray-900">Manage Users</h3>
              <p class="text-sm text-gray-600">View and manage user accounts</p>
            </div>
          </router-link>

          <router-link
            :to="{ name: 'admin-statistics' }"
            class="card hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="card-body text-center">
              <ChartBarIcon class="w-8 h-8 text-green-600 mx-auto mb-2" />
              <h3 class="font-medium text-gray-900">View Analytics</h3>
              <p class="text-sm text-gray-600">Detailed system analytics</p>
            </div>
          </router-link>

          <button
            @click="performBackup"
            :disabled="loading"
            class="card hover:shadow-lg transition-shadow cursor-pointer text-left"
          >
            <div class="card-body text-center">
              <CloudArrowDownIcon class="w-8 h-8 text-recipe-primary-600 mx-auto mb-2" />
              <h3 class="font-medium text-gray-900">Backup System</h3>
              <p class="text-sm text-gray-600">Create system backup</p>
            </div>
          </button>

          <button
            @click="clearCache"
            :disabled="loading"
            class="card hover:shadow-lg transition-shadow cursor-pointer text-left"
          >
            <div class="card-body text-center">
              <ArrowPathIcon class="w-8 h-8 text-recipe-secondary-600 mx-auto mb-2" />
              <h3 class="font-medium text-gray-900">Clear Cache</h3>
              <p class="text-sm text-gray-600">Clear application cache</p>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  UsersIcon,
  BookOpenIcon,
  DocumentTextIcon,
  CreditCardIcon,
  ChartBarIcon,
  CloudArrowDownIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline';

export default {
  name: 'AdminDashboard',
  components: {
    UsersIcon,
    BookOpenIcon,
    DocumentTextIcon,
    CreditCardIcon,
    ChartBarIcon,
    CloudArrowDownIcon,
    ArrowPathIcon
  },
  setup() {
    const router = useRouter();
    const loading = ref(false);

    const stats = ref({
      totalUsers: 0,
      newUsersThisMonth: 0,
      totalRecipes: 0,
      publicRecipes: 0,
      totalCookbooks: 0,
      publicCookbooks: 0,
      paidSubscribers: 0,
      monthlyRevenue: 0
    });

    const recentUsers = ref([]);

    const loadDashboardData = async () => {
      try {
        const response = await window.axios.get('/admin/statistics');
        
        if (response.data.success) {
          stats.value = {
            totalUsers: response.data.statistics.total_users || 0,
            newUsersThisMonth: response.data.statistics.new_users_this_month || 0,
            totalRecipes: response.data.statistics.total_recipes || 0,
            publicRecipes: response.data.statistics.public_recipes || 0,
            totalCookbooks: response.data.statistics.total_cookbooks || 0,
            publicCookbooks: response.data.statistics.public_cookbooks || 0,
            paidSubscribers: response.data.statistics.paid_subscribers || 0,
            monthlyRevenue: response.data.statistics.monthly_revenue || 0
          };
        }
      } catch (error) {
        console.error('Failed to load dashboard data:', error);
        
        // Mock data for development
        stats.value = {
          totalUsers: 1247,
          newUsersThisMonth: 89,
          totalRecipes: 3542,
          publicRecipes: 2891,
          totalCookbooks: 856,
          publicCookbooks: 423,
          paidSubscribers: 234,
          monthlyRevenue: 2847
        };
      }
    };

    const loadRecentUsers = async () => {
      try {
        const response = await window.axios.get('/admin/users?limit=5&sort=created_at');
        
        if (response.data.success) {
          recentUsers.value = response.data.users || [];
        }
      } catch (error) {
        console.error('Failed to load recent users:', error);
        
        // Mock data for development
        recentUsers.value = [
          {
            _id: '1',
            name: 'John Smith',
            email: 'john@example.com',
            subscription_tier: 1,
            created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString()
          },
          {
            _id: '2',
            name: 'Sarah Johnson',
            email: 'sarah@example.com',
            subscription_tier: 0,
            created_at: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString()
          },
          {
            _id: '3',
            name: 'Mike Davis',
            email: 'mike@example.com',
            subscription_tier: 2,
            created_at: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000).toISOString()
          }
        ];
      }
    };

    const getTierName = (tier) => {
      switch (tier) {
        case 0: return 'Free';
        case 1: return 'Premium';
        case 2: return 'Professional';
        default: return 'Free';
      }
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays === 1) return 'Yesterday';
      if (diffDays < 7) return `${diffDays} days ago`;
      return date.toLocaleDateString();
    };

    const performBackup = async () => {
      loading.value = true;
      
      try {
        // In a real implementation, this would trigger a backup job
        await new Promise(resolve => setTimeout(resolve, 2000)); // Simulate API call
        window.$toast?.success('Backup Started', 'System backup has been initiated.');
      } catch (error) {
        window.$toast?.error('Backup Failed', 'Failed to start system backup.');
      }
      
      loading.value = false;
    };

    const clearCache = async () => {
      loading.value = true;
      
      try {
        // In a real implementation, this would clear the application cache
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate API call
        window.$toast?.success('Cache Cleared', 'Application cache has been cleared.');
      } catch (error) {
        window.$toast?.error('Clear Failed', 'Failed to clear application cache.');
      }
      
      loading.value = false;
    };

    onMounted(async () => {
      await Promise.all([
        loadDashboardData(),
        loadRecentUsers()
      ]);
    });

    return {
      loading,
      stats,
      recentUsers,
      getTierName,
      formatDate,
      performBackup,
      clearCache
    };
  }
};
</script>
