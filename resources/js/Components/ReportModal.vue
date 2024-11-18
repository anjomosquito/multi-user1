<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

      <div class="relative bg-white rounded-lg w-full max-w-md mx-4">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Report</h3>

          <!-- Report Type Selection -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
            <div class="grid grid-cols-2 gap-4">
              <button 
                @click="selectedType = 'sales'"
                :class="[
                  'py-2 px-4 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                  selectedType === 'sales' 
                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                    : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                ]"
              >
                Sales Report
              </button>
              <button 
                @click="selectedType = 'payment'"
                :class="[
                  'py-2 px-4 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                  selectedType === 'payment' 
                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                    : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                ]"
              >
                Payment Report
              </button>
            </div>
          </div>

          <!-- Format Selection -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
            <div class="grid grid-cols-3 gap-4">
              <button 
                @click="selectedFormat = 'pdf'"
                :class="[
                  'py-2 px-4 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                  selectedFormat === 'pdf' 
                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                    : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                ]"
              >
                PDF
              </button>
              <button 
                @click="selectedFormat = 'xlsx'"
                :class="[
                  'py-2 px-4 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                  selectedFormat === 'xlsx' 
                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                    : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                ]"
              >
                Excel
              </button>
              <button 
                @click="selectedFormat = 'print'"
                :class="[
                  'py-2 px-4 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                  selectedFormat === 'print' 
                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                    : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                ]"
              >
                Print
              </button>
            </div>
          </div>

          <!-- Date Range Selection -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-gray-500 mb-1">Start Date</label>
                <input 
                  type="date" 
                  v-model="startDate"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
              </div>
              <div>
                <label class="block text-xs text-gray-500 mb-1">End Date</label>
                <input 
                  type="date" 
                  v-model="endDate"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mb-4 text-sm text-red-600">
            {{ error }}
          </div>

          <!-- Action Buttons -->
          <div class="mt-6 flex justify-end space-x-3">
            <button 
              @click="$emit('close')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Cancel
            </button>
            <button 
              @click="generateReport"
              :disabled="isLoading || !isValid"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isLoading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
              </span>
              <span v-else>Generate Report</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'success']);

// State
const selectedType = ref('sales');
const selectedFormat = ref('pdf');
const startDate = ref(new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0]);
const endDate = ref(new Date().toISOString().split('T')[0]);
const isLoading = ref(false);
const error = ref('');

// Computed
const isValid = computed(() => {
  if (!selectedType.value || !selectedFormat.value || !startDate.value || !endDate.value) {
    return false;
  }
  if (new Date(endDate.value) < new Date(startDate.value)) {
    return false;
  }
  return true;
});

// Methods
const printReport = async (url) => {
  // Create a hidden iframe
  const iframe = document.createElement('iframe');
  iframe.style.display = 'none';
  document.body.appendChild(iframe);

  // Load the content and print
  try {
    // Set iframe source
    iframe.src = url;

    // Wait for iframe to load
    await new Promise((resolve, reject) => {
      iframe.onload = resolve;
      iframe.onerror = reject;
      // Timeout after 5 seconds
      setTimeout(reject, 5000);
    });

    // Get the iframe window
    const iframeWindow = iframe.contentWindow;

    // Print the iframe content
    iframeWindow.print();

    // Remove iframe after printing
    setTimeout(() => {
      document.body.removeChild(iframe);
    }, 1000);

  } catch (error) {
    document.body.removeChild(iframe);
    throw new Error('Failed to load report for printing. Please try again.');
  }
};

const generateReport = async () => {
  if (!isValid.value) return;

  error.value = '';
  isLoading.value = true;

  try {
    const endpoint = selectedType.value === 'sales' 
      ? route('admin.reports.sales.download')
      : route('admin.reports.payments.download');

    const response = await axios.post(endpoint, {
      start_date: startDate.value,
      end_date: endDate.value,
      format: selectedFormat.value
    }, {
      responseType: selectedFormat.value === 'print' ? 'json' : 'blob'
    });

    // Handle print format
    if (selectedFormat.value === 'print') {
      await printReport(response.data.url);
      emit('success', 'Report generated successfully');
      return;
    }

    // Handle PDF/Excel download
    const blob = new Blob([response.data], {
      type: selectedFormat.value === 'pdf' 
        ? 'application/pdf' 
        : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    });

    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${selectedType.value}_report.${selectedFormat.value}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    emit('success', 'Report generated successfully');
  } catch (e) {
    console.error('Report generation error:', e);
    
    // Try to extract error message from response
    let errorMessage = 'Error generating report. Please try again.';
    if (e.response?.data) {
      try {
        const reader = new FileReader();
        reader.onload = () => {
          try {
            const errorData = JSON.parse(reader.result);
            error.value = errorData.error || errorData.message || errorMessage;
          } catch {
            error.value = errorMessage;
          }
        };
        reader.readAsText(e.response.data);
      } catch {
        error.value = e.response.data?.error || e.response.data?.message || errorMessage;
      }
    } else {
      error.value = errorMessage;
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-to,
.modal-leave-from {
  opacity: 1;
}
</style>
