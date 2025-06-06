<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 bg-recipe-primary-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">R</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-display font-bold text-gray-900">
          Forgot your password?
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your email address and we'll send you a link to reset your password.
        </p>
      </div>

      <form v-if="!emailSent" class="mt-8 space-y-6" @submit.prevent="handleForgotPassword">
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
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full flex justify-center items-center"
          >
            <div v-if="loading" class="loading-spinner w-5 h-5 mr-2"></div>
            {{ loading ? 'Sending...' : 'Send reset link' }}
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
                Failed to send reset link
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <p>{{ errorMessage }}</p>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Success Message -->
      <div v-if="emailSent" class="rounded-md bg-green-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <CheckCircleIcon class="h-5 w-5 text-green-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800">
              Reset link sent!
            </h3>
            <div class="mt-2 text-sm text-green-700">
              <p>
                We've sent a password reset link to <strong>{{ form.email }}</strong>. 
                Please check your email and follow the instructions to reset your password.
              </p>
            </div>
            <div class="mt-4">
              <router-link
                :to="{ name: 'login' }"
                class="text-sm font-medium text-green-800 hover:text-green-700"
              >
                Back to sign in
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { XCircleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

export default {
  name: 'ForgotPassword',
  components: {
    XCircleIcon,
    CheckCircleIcon
  },
  setup() {
    const authStore = useAuthStore();

    const form = reactive({
      email: ''
    });

    const errors = ref({});
    const errorMessage = ref('');
    const loading = ref(false);
    const emailSent = ref(false);

    const handleForgotPassword = async () => {
      errors.value = {};
      errorMessage.value = '';
      loading.value = true;

      const result = await authStore.forgotPassword(form.email);

      if (result.success) {
        emailSent.value = true;
      } else {
        errorMessage.value = result.message;
      }

      loading.value = false;
    };

    return {
      form,
      errors,
      errorMessage,
      loading,
      emailSent,
      handleForgotPassword
    };
  }
};
</script>
