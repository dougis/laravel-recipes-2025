<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 bg-recipe-primary-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">R</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-display font-bold text-gray-900">
          Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your new password below
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleResetPassword">
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="form-input mt-1"
              :class="{ 'border-red-500': errors.email }"
              placeholder="Enter your email address"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              New password
            </label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
              class="form-input mt-1"
              :class="{ 'border-red-500': errors.password }"
              placeholder="Enter your new password"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirm new password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="form-input mt-1"
              :class="{ 'border-red-500': errors.password_confirmation }"
              placeholder="Confirm your new password"
            />
            <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
              {{ errors.password_confirmation[0] }}
            </p>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full flex justify-center items-center"
          >
            <div v-if="loading" class="loading-spinner w-5 h-5 mr-2"></div>
            {{ loading ? 'Resetting...' : 'Reset password' }}
          </button>
        </div>

        <div class="text-center">
          <router-link
            :to="{ name: 'login' }"
            class="font-medium text-recipe-primary-600 hover:text-recipe-primary-500"
          >
            Back to sign in
          </router-link>
        </div>

        <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <XCircleIcon class="h-5 w-5 text-red-400" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Password reset failed
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <p>{{ errorMessage }}</p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { XCircleIcon } from '@heroicons/vue/24/outline';

export default {
  name: 'ResetPassword',
  components: {
    XCircleIcon
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const authStore = useAuthStore();

    const form = reactive({
      email: '',
      password: '',
      password_confirmation: '',
      token: ''
    });

    const errors = ref({});
    const errorMessage = ref('');
    const loading = ref(false);

    const handleResetPassword = async () => {
      errors.value = {};
      errorMessage.value = '';
      loading.value = true;

      const result = await authStore.resetPassword({
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
        token: form.token
      });

      if (result.success) {
        window.$toast?.success(
          'Password reset successful!', 
          'You have been automatically signed in.'
        );
        router.push({ name: 'dashboard' });
      } else {
        errorMessage.value = result.message;
      }

      loading.value = false;
    };

    onMounted(() => {
      // Get token and email from URL parameters
      form.token = route.query.token || '';
      form.email = route.query.email || '';
      
      if (!form.token) {
        window.$toast?.error('Invalid reset link', 'This password reset link is invalid or has expired.');
        router.push({ name: 'forgot-password' });
      }
    });

    return {
      form,
      errors,
      errorMessage,
      loading,
      handleResetPassword
    };
  }
};
</script>
