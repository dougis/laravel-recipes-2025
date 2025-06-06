<template>
  <teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click="handleBackdropClick"
    >
      <div class="flex min-h-screen items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal -->
        <div
          class="relative w-full max-w-md transform rounded-lg bg-white p-6 shadow-xl transition-all"
          @click.stop
        >
          <!-- Icon -->
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
            <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
          </div>
          
          <!-- Content -->
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              {{ title }}
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                {{ message }}
              </p>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
            <button
              @click="handleConfirm"
              :disabled="loading"
              :class="[
                'inline-flex w-full justify-center rounded-md border border-transparent px-4 py-2 text-base font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:col-start-2 sm:text-sm',
                confirmClass || 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
                loading ? 'opacity-75 cursor-not-allowed' : ''
              ]"
            >
              <div v-if="loading" class="loading-spinner w-4 h-4 mr-2"></div>
              {{ loading ? 'Processing...' : confirmText }}
            </button>
            
            <button
              @click="handleCancel"
              :disabled="loading"
              class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm disabled:opacity-75 disabled:cursor-not-allowed"
            >
              {{ cancelText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script>
import { ref } from 'vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

export default {
  name: 'ConfirmationModal',
  components: {
    ExclamationTriangleIcon
  },
  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'Confirm Action'
    },
    message: {
      type: String,
      default: 'Are you sure you want to perform this action?'
    },
    confirmText: {
      type: String,
      default: 'Confirm'
    },
    cancelText: {
      type: String,
      default: 'Cancel'
    },
    confirmClass: {
      type: String,
      default: ''
    }
  },
  emits: ['confirm', 'cancel'],
  setup(props, { emit }) {
    const loading = ref(false);

    const handleConfirm = async () => {
      loading.value = true;
      emit('confirm');
      // Note: Parent component should handle setting loading to false
      // or closing the modal after the async operation completes
    };

    const handleCancel = () => {
      if (!loading.value) {
        emit('cancel');
      }
    };

    const handleBackdropClick = () => {
      if (!loading.value) {
        emit('cancel');
      }
    };

    return {
      loading,
      handleConfirm,
      handleCancel,
      handleBackdropClick
    };
  }
};
</script>
