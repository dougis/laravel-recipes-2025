<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 bg-recipe-primary-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">R</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-display font-bold text-gray-900">
          Sign in to your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link
            :to="{ name: 'register' }"
            class="font-medium text-recipe-primary-600 hover:text-recipe-primary-500"
          >
            create a new account
          </router-link>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
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
              placeholder="Enter your email"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="form-input mt-1"
              :class="{ 'border-red-500': errors.password }"
              placeholder="Enter your password"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="form.remember"
              name="remember-me"
              type="checkbox"
              class="h-4 w-4 text-recipe-primary-600 focus:ring-recipe-primary-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>

          <div class="text-sm">
            <router-link
              :to="{ name: 'forgot-password' }"
              class="font-medium text-recipe-primary-600 hover:text-recipe-primary-500"
            >
              Forgot your password?
            </router-link>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full flex justify-center items-center"
          >
            <div v-if="loading" class="loading-spinner w-5 h-5 mr-2"></div>
            {{ loading ? 'Signing in...' : 'Sign in' }}
          </button>
        </div>

        <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <XCircleIcon class="h-5 w-5 text-red-400" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Sign in failed
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
import { ref, reactive, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { XCircleIcon } from '@heroicons/vue/24/outline';

export default {
  name: 'Login',
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
      remember: false
    });

    const errors = ref({});
    const errorMessage = ref('');
    const loading = computed(() => authStore.loading);

    const handleLogin = async () => {
      errors.value = {};
      errorMessage.value = '';

      const result = await authStore.login({
        email: form.email,
        password: form.password,
        remember: form.remember
      });

      if (result.success) {
        window.$toast?.success('Welcome back!', 'You have been successfully signed in.');
        
        // Redirect to intended page or dashboard
        const redirectTo = route.query.redirect || '/dashboard';
        router.push(redirectTo);
      } else {
        if (result.errors) {
          errors.value = result.errors;
        } else {
          errorMessage.value = result.message;
        }
      }
    };

    return {
      form,
      errors,
      errorMessage,
      loading,
      handleLogin
    };
  }
};
</script>
