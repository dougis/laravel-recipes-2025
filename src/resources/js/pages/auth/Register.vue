<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 bg-recipe-primary-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">R</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-display font-bold text-gray-900">
          Create your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link
            :to="{ name: 'login' }"
            class="font-medium text-recipe-primary-600 hover:text-recipe-primary-500"
          >
            sign in to your existing account
          </router-link>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              Full name
            </label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              autocomplete="name"
              required
              class="form-input mt-1"
              :class="{ 'border-red-500': errors.name }"
              placeholder="Enter your full name"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ errors.name[0] }}
            </p>
          </div>

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
              autocomplete="new-password"
              required
              class="form-input mt-1"
              :class="{ 'border-red-500': errors.password }"
              placeholder="Create a strong password"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
            <p class="mt-1 text-sm text-gray-500">
              Password must be at least 8 characters long
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirm password
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
              placeholder="Confirm your password"
            />
            <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
              {{ errors.password_confirmation[0] }}
            </p>
          </div>
        </div>

        <div class="flex items-center">
          <input
            id="agree-terms"
            v-model="form.agreeTerms"
            name="agree-terms"
            type="checkbox"
            required
            class="h-4 w-4 text-recipe-primary-600 focus:ring-recipe-primary-500 border-gray-300 rounded"
          />
          <label for="agree-terms" class="ml-2 block text-sm text-gray-900">
            I agree to the 
            <a href="#" class="text-recipe-primary-600 hover:text-recipe-primary-500">Terms of Service</a>
            and 
            <a href="#" class="text-recipe-primary-600 hover:text-recipe-primary-500">Privacy Policy</a>
          </label>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading || !form.agreeTerms"
            class="btn-primary w-full flex justify-center items-center disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <div v-if="loading" class="loading-spinner w-5 h-5 mr-2"></div>
            {{ loading ? 'Creating account...' : 'Create account' }}
          </button>
        </div>

        <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <XCircleIcon class="h-5 w-5 text-red-400" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Registration failed
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <p>{{ errorMessage }}</p>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Subscription Tier Info -->
      <div class="mt-8 bg-recipe-primary-50 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <InformationCircleIcon class="h-5 w-5 text-recipe-primary-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-recipe-primary-800">
              Free Tier Includes
            </h3>
            <div class="mt-2 text-sm text-recipe-primary-700">
              <ul class="list-disc list-inside space-y-1">
                <li>Up to 25 recipes</li>
                <li>1 cookbook</li>
                <li>Basic recipe management</li>
                <li>Print individual recipes</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { XCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

export default {
  name: 'Register',
  components: {
    XCircleIcon,
    InformationCircleIcon
  },
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();

    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      agreeTerms: false
    });

    const errors = ref({});
    const errorMessage = ref('');
    const loading = computed(() => authStore.loading);

    const handleRegister = async () => {
      errors.value = {};
      errorMessage.value = '';

      const result = await authStore.register({
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation
      });

      if (result.success) {
        window.$toast?.success(
          'Welcome to Laravel Recipes!', 
          'Your account has been created successfully.'
        );
        router.push({ name: 'dashboard' });
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
      handleRegister
    };
  }
};
</script>
