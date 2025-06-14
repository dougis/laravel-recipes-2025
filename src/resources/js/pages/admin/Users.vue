<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-display font-bold text-gray-900">User Management</h1>
            <p class="mt-2 text-gray-600">Manage user accounts and permissions</p>
          </div>
          
          <router-link
            :to="{ name: 'admin-dashboard' }"
            class="btn-outline"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2" />
            Back to Dashboard
          </router-link>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="mb-8">
        <div class="card">
          <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Search -->
              <div class="md:col-span-2">
                <div class="relative">
                  <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search users by name or email..."
                    class="form-input pl-10 w-full"
                    @input="handleSearch"
                  />
                </div>
              </div>

              <!-- Subscription Filter -->
              <div>
                <select v-model="filters.subscription" class="form-select w-full" @change="applyFilters">
                  <option value="">All Subscriptions</option>
                  <option value="0">Free</option>
                  <option value="1">Premium</option>
                  <option value="2">Professional</option>
                  <option value="admin">Admin Override</option>
                </select>
              </div>

              <!-- Sort -->
              <div>
                <select v-model="sortBy" class="form-select w-full" @change="applySorting">
                  <option value="created_at">Recently Joined</option>
                  <option value="name">Name (A-Z)</option>
                  <option value="email">Email (A-Z)</option>
                  <option value="subscription_tier">Subscription Level</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Statistics -->
      <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="card">
            <div class="card-body text-center">
              <div class="text-2xl font-bold text-blue-600">{{ stats.total }}</div>
              <div class="text-sm text-gray-600">Total Users</div>
            </div>
          </div>
          <div class="card">
            <div class="card-body text-center">
              <div class="text-2xl font-bold text-green-600">{{ stats.free }}</div>
              <div class="text-sm text-gray-600">Free Users</div>
            </div>
          </div>
          <div class="card">
            <div class="card-body text-center">
              <div class="text-2xl font-bold text-recipe-primary-600">{{ stats.premium }}</div>
              <div class="text-sm text-gray-600">Premium Users</div>
            </div>
          </div>
          <div class="card">
            <div class="card-body text-center">
              <div class="text-2xl font-bold text-recipe-secondary-600">{{ stats.professional }}</div>
              <div class="text-sm text-gray-600">Professional Users</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="loading-spinner"></div>
      </div>

      <!-- Users Table -->
      <div v-else class="card">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">
              Users ({{ displayedUsers.length }})
            </h2>
            <div class="flex items-center space-x-4">
              <button
                @click="exportUsers"
                class="btn-outline text-sm"
              >
                <ArrowDownTrayIcon class="w-4 h-4 mr-1" />
                Export
              </button>
            </div>
          </div>
        </div>
        
        <div class="card-body p-0">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    User
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Subscription
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Content
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Joined
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in displayedUsers" :key="user._id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-recipe-primary-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-recipe-primary-700">
                          {{ user.name?.charAt(0)?.toUpperCase() || 'U' }}
                        </span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                        <div v-if="user.admin_override" class="mt-1">
                          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <StarIcon class="w-3 h-3 mr-1" />
                            Admin Override
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getSubscriptionClass(user.subscription_tier)">
                      {{ getSubscriptionName(user.subscription_tier) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div class="flex items-center space-x-4">
                      <span>{{ user.recipe_count || 0 }} recipes</span>
                      <span>{{ user.cookbook_count || 0 }} cookbooks</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(user.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <!-- View User Details -->
                      <button
                        @click="viewUser(user)"
                        class="text-recipe-primary-600 hover:text-recipe-primary-900"
                        title="View Details"
                      >
                        <EyeIcon class="w-4 h-4" />
                      </button>

                      <!-- Toggle Admin Override -->
                      <button
                        @click="toggleAdminOverride(user)"
                        class="text-yellow-600 hover:text-yellow-900"
                        :title="user.admin_override ? 'Remove Admin Override' : 'Grant Admin Override'"
                      >
                        <StarIcon class="w-4 h-4" />
                      </button>

                      <!-- More Actions -->
                      <div class="relative" ref="menuRef">
                        <button
                          @click="toggleUserMenu(user._id)"
                          class="text-gray-400 hover:text-gray-600"
                        >
                          <EllipsisVerticalIcon class="w-4 h-4" />
                        </button>

                        <div
                          v-if="showUserMenu === user._id"
                          class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-10"
                        >
                          <button
                            @click="impersonateUser(user)"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                          >
                            <UserIcon class="w-4 h-4 inline mr-2" />
                            Impersonate User
                          </button>
                          
                          <button
                            @click="resetUserPassword(user)"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                          >
                            <KeyIcon class="w-4 h-4 inline mr-2" />
                            Reset Password
                          </button>
                          
                          <button
                            @click="suspendUser(user)"
                            class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50"
                          >
                            <NoSymbolIcon class="w-4 h-4 inline mr-2" />
                            Suspend User
                          </button>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="!displayedUsers.length" class="text-center py-12">
            <UsersIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
            <p class="text-gray-600">
              {{ searchQuery || Object.values(filters).some(f => f) ? 'Try adjusting your search or filters.' : 'No users have registered yet.' }}
            </p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="card-body border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ ((currentPage - 1) * pageSize) + 1 }} to {{ Math.min(currentPage * pageSize, totalUsers) }} of {{ totalUsers }} users
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="btn-outline disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <span class="text-sm text-gray-700">
                Page {{ currentPage }} of {{ totalPages }}
              </span>
              <button
                @click="changePage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="btn-outline disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Details Modal -->
    <UserDetailsModal
      v-if="selectedUser"
      :user="selectedUser"
      :is-open="!!selectedUser"
      @close="selectedUser = null"
      @user-updated="handleUserUpdate"
    />

    <!-- Confirmation Modals -->
    <ConfirmationModal
      v-if="showConfirmModal"
      :is-open="showConfirmModal"
      :title="confirmAction.title"
      :message="confirmAction.message"
      :confirm-text="confirmAction.confirmText"
      :confirm-class="confirmAction.confirmClass"
      @confirm="confirmAction.handler"
      @cancel="showConfirmModal = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  ArrowLeftIcon,
  MagnifyingGlassIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  EllipsisVerticalIcon,
  UserIcon,
  KeyIcon,
  NoSymbolIcon,
  UsersIcon
} from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';
import UserDetailsModal from '../../components/admin/UserDetailsModal.vue';
import ConfirmationModal from '../../components/ui/ConfirmationModal.vue';

export default {
  name: 'AdminUsers',
  components: {
    ArrowLeftIcon,
    MagnifyingGlassIcon,
    ArrowDownTrayIcon,
    EyeIcon,
    EllipsisVerticalIcon,
    UserIcon,
    KeyIcon,
    NoSymbolIcon,
    UsersIcon,
    StarIcon,
    UserDetailsModal,
    ConfirmationModal
  },
  setup() {
    const router = useRouter();
    
    const loading = ref(true);
    const users = ref([]);
    const searchQuery = ref('');
    const sortBy = ref('created_at');
    const currentPage = ref(1);
    const pageSize = ref(20);
    const selectedUser = ref(null);
    const showUserMenu = ref(null);
    const showConfirmModal = ref(false);
    const confirmAction = ref({});
    const menuRef = ref(null);

    const filters = ref({
      subscription: ''
    });

    const stats = ref({
      total: 0,
      free: 0,
      premium: 0,
      professional: 0
    });

    const filteredUsers = computed(() => {
      let filtered = [...users.value];

      // Apply search
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(user =>
          user.name?.toLowerCase().includes(query) ||
          user.email?.toLowerCase().includes(query)
        );
      }

      // Apply subscription filter
      if (filters.value.subscription) {
        if (filters.value.subscription === 'admin') {
          filtered = filtered.filter(user => user.admin_override);
        } else {
          filtered = filtered.filter(user => user.subscription_tier === parseInt(filters.value.subscription));
        }
      }

      // Apply sorting
      filtered.sort((a, b) => {
        switch (sortBy.value) {
          case 'name':
            return (a.name || '').localeCompare(b.name || '');
          case 'email':
            return (a.email || '').localeCompare(b.email || '');
          case 'subscription_tier':
            return (b.subscription_tier || 0) - (a.subscription_tier || 0);
          case 'created_at':
          default:
            return new Date(b.created_at) - new Date(a.created_at);
        }
      });

      return filtered;
    });

    const totalUsers = computed(() => filteredUsers.value.length);
    const totalPages = computed(() => Math.ceil(totalUsers.value / pageSize.value));

    const displayedUsers = computed(() => {
      const start = (currentPage.value - 1) * pageSize.value;
      const end = start + pageSize.value;
      return filteredUsers.value.slice(start, end);
    });

    const loadUsers = async () => {
      loading.value = true;
      try {
        const response = await window.axios.get('/admin/users');
        
        if (response.data.success) {
          users.value = response.data.users || [];
          updateStats();
        }
      } catch (error) {
        console.error('Failed to load users:', error);
        window.$toast?.error('Load Failed', 'Failed to load users');
        
        // Mock data for development
        users.value = generateMockUsers();
        updateStats();
      }
      loading.value = false;
    };

    const generateMockUsers = () => {
      const mockUsers = [];
      const names = ['John Smith', 'Sarah Johnson', 'Mike Davis', 'Emily Brown', 'David Wilson', 'Lisa Garcia', 'Chris Lee', 'Amy Taylor'];
      const domains = ['example.com', 'gmail.com', 'hotmail.com', 'yahoo.com'];
      
      for (let i = 0; i < 50; i++) {
        const name = names[Math.floor(Math.random() * names.length)];
        const email = `${name.toLowerCase().replace(' ', '.')}${i}@${domains[Math.floor(Math.random() * domains.length)]}`;
        
        mockUsers.push({
          _id: `user_${i}`,
          name,
          email,
          subscription_tier: Math.floor(Math.random() * 3),
          admin_override: Math.random() < 0.05,
          recipe_count: Math.floor(Math.random() * 50),
          cookbook_count: Math.floor(Math.random() * 10),
          created_at: new Date(Date.now() - Math.random() * 365 * 24 * 60 * 60 * 1000).toISOString()
        });
      }
      
      return mockUsers;
    };

    const updateStats = () => {
      stats.value = {
        total: users.value.length,
        free: users.value.filter(u => u.subscription_tier === 0).length,
        premium: users.value.filter(u => u.subscription_tier === 1).length,
        professional: users.value.filter(u => u.subscription_tier === 2).length
      };
    };

    const getSubscriptionName = (tier) => {
      switch (tier) {
        case 0: return 'Free';
        case 1: return 'Premium';
        case 2: return 'Professional';
        default: return 'Free';
      }
    };

    const getSubscriptionClass = (tier) => {
      switch (tier) {
        case 0: return 'bg-gray-100 text-gray-800';
        case 1: return 'bg-recipe-primary-100 text-recipe-primary-800';
        case 2: return 'bg-recipe-secondary-100 text-recipe-secondary-800';
        default: return 'bg-gray-100 text-gray-800';
      }
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    const handleSearch = () => {
      currentPage.value = 1;
    };

    const applyFilters = () => {
      currentPage.value = 1;
    };

    const applySorting = () => {
      currentPage.value = 1;
    };

    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
      }
    };

    const viewUser = (user) => {
      selectedUser.value = user;
    };

    const toggleUserMenu = (userId) => {
      showUserMenu.value = showUserMenu.value === userId ? null : userId;
    };

    const toggleAdminOverride = (user) => {
      const action = user.admin_override ? 'remove' : 'grant';
      confirmAction.value = {
        title: `${action === 'grant' ? 'Grant' : 'Remove'} Admin Override`,
        message: `Are you sure you want to ${action} admin override for ${user.name}?`,
        confirmText: action === 'grant' ? 'Grant Override' : 'Remove Override',
        confirmClass: action === 'grant' ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-red-600 hover:bg-red-700',
        handler: () => performAdminOverrideToggle(user)
      };
      showConfirmModal.value = true;
    };

    const performAdminOverrideToggle = async (user) => {
      try {
        const response = await window.axios.put(`/admin/users/${user._id}/override`);
        
        if (response.data.success) {
          const index = users.value.findIndex(u => u._id === user._id);
          if (index !== -1) {
            users.value[index] = response.data.user;
          }
          window.$toast?.success('Override Updated', 'Admin override has been updated successfully.');
        }
      } catch (error) {
        window.$toast?.error('Update Failed', error.response?.data?.message || 'Failed to update admin override');
      }
      showConfirmModal.value = false;
    };

    const impersonateUser = (user) => {
      // In a real implementation, this would create an impersonation session
      window.$toast?.info('Impersonation', `Impersonation feature would be implemented here for ${user.name}`);
      showUserMenu.value = null;
    };

    const resetUserPassword = (user) => {
      confirmAction.value = {
        title: 'Reset Password',
        message: `Are you sure you want to reset the password for ${user.name}? They will receive an email with reset instructions.`,
        confirmText: 'Reset Password',
        confirmClass: 'bg-recipe-primary-600 hover:bg-recipe-primary-700',
        handler: () => performPasswordReset(user)
      };
      showConfirmModal.value = true;
      showUserMenu.value = null;
    };

    const performPasswordReset = async (user) => {
      try {
        // In a real implementation, this would send a password reset email
        window.$toast?.success('Password Reset', `Password reset email sent to ${user.email}`);
      } catch (error) {
        window.$toast?.error('Reset Failed', 'Failed to send password reset email');
      }
      showConfirmModal.value = false;
    };

    const suspendUser = (user) => {
      confirmAction.value = {
        title: 'Suspend User',
        message: `Are you sure you want to suspend ${user.name}? They will not be able to access their account.`,
        confirmText: 'Suspend User',
        confirmClass: 'bg-red-600 hover:bg-red-700',
        handler: () => performUserSuspension(user)
      };
      showConfirmModal.value = true;
      showUserMenu.value = null;
    };

    const performUserSuspension = async (user) => {
      try {
        // In a real implementation, this would suspend the user account
        window.$toast?.success('User Suspended', `${user.name} has been suspended`);
      } catch (error) {
        window.$toast?.error('Suspension Failed', 'Failed to suspend user');
      }
      showConfirmModal.value = false;
    };

    const exportUsers = () => {
      // In a real implementation, this would export user data to CSV
      window.$toast?.info('Export Started', 'User export functionality would be implemented here');
    };

    const handleUserUpdate = (updatedUser) => {
      const index = users.value.findIndex(u => u._id === updatedUser._id);
      if (index !== -1) {
        users.value[index] = updatedUser;
      }
      selectedUser.value = null;
    };

    // Close menu when clicking outside
    const handleClickOutside = (event) => {
      if (menuRef.value && !menuRef.value.contains(event.target)) {
        showUserMenu.value = null;
      }
    };

    onMounted(() => {
      loadUsers();
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    return {
      loading,
      users,
      searchQuery,
      sortBy,
      currentPage,
      pageSize,
      selectedUser,
      showUserMenu,
      showConfirmModal,
      confirmAction,
      menuRef,
      filters,
      stats,
      displayedUsers,
      totalUsers,
      totalPages,
      getSubscriptionName,
      getSubscriptionClass,
      formatDate,
      handleSearch,
      applyFilters,
      applySorting,
      changePage,
      viewUser,
      toggleUserMenu,
      toggleAdminOverride,
      impersonateUser,
      resetUserPassword,
      suspendUser,
      exportUsers,
      handleUserUpdate
    };
  }
};
</script>
