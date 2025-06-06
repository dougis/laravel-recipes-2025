<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <transition-group name="toast" tag="div" class="space-y-2">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden',
          toast.type === 'error' ? 'border-l-4 border-red-500' : '',
          toast.type === 'success' ? 'border-l-4 border-green-500' : '',
          toast.type === 'warning' ? 'border-l-4 border-yellow-500' : '',
          toast.type === 'info' ? 'border-l-4 border-blue-500' : ''
        ]"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <CheckCircleIcon
                v-if="toast.type === 'success'"
                class="h-6 w-6 text-green-400"
              />
              <ExclamationTriangleIcon
                v-else-if="toast.type === 'warning'"
                class="h-6 w-6 text-yellow-400"
              />
              <XCircleIcon
                v-else-if="toast.type === 'error'"
                class="h-6 w-6 text-red-400"
              />
              <InformationCircleIcon
                v-else
                class="h-6 w-6 text-blue-400"
              />
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-medium text-gray-900">
                {{ toast.title }}
              </p>
              <p v-if="toast.message" class="mt-1 text-sm text-gray-500">
                {{ toast.message }}
              </p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeToast(toast.id)"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-recipe-primary-500"
              >
                <span class="sr-only">Close</span>
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline';

export default {
  name: 'ToastContainer',
  components: {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    InformationCircleIcon,
    XMarkIcon
  },
  setup() {
    const toasts = ref([]);
    let toastId = 0;

    const addToast = (toast) => {
      const id = ++toastId;
      const newToast = {
        id,
        type: toast.type || 'info',
        title: toast.title,
        message: toast.message,
        duration: toast.duration || 5000
      };

      toasts.value.push(newToast);

      // Auto remove toast after duration
      if (newToast.duration > 0) {
        setTimeout(() => {
          removeToast(id);
        }, newToast.duration);
      }

      return id;
    };

    const removeToast = (id) => {
      const index = toasts.value.findIndex(toast => toast.id === id);
      if (index > -1) {
        toasts.value.splice(index, 1);
      }
    };

    const clearToasts = () => {
      toasts.value = [];
    };

    // Global toast functions
    const showSuccess = (title, message, duration) => {
      return addToast({ type: 'success', title, message, duration });
    };

    const showError = (title, message, duration) => {
      return addToast({ type: 'error', title, message, duration });
    };

    const showWarning = (title, message, duration) => {
      return addToast({ type: 'warning', title, message, duration });
    };

    const showInfo = (title, message, duration) => {
      return addToast({ type: 'info', title, message, duration });
    };

    // Make toast functions globally available
    onMounted(() => {
      window.$toast = {
        success: showSuccess,
        error: showError,
        warning: showWarning,
        info: showInfo,
        clear: clearToasts
      };
    });

    return {
      toasts,
      addToast,
      removeToast,
      clearToasts
    };
  }
};
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
