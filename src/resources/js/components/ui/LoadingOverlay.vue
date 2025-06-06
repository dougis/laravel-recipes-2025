<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
      <div class="loading-spinner"></div>
      <span class="text-gray-700 font-medium">{{ message }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoadingOverlay',
  props: {
    message: {
      type: String,
      default: 'Loading...'
    }
  }
};
</script>
