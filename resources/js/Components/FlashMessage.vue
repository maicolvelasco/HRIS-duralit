<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref('success');
const progress = ref(100);
let progressInterval = null;
let hideTimeout = null;

// Observar cambios en los props de flash
watch(() => page.props.flash, (newFlash) => {
  if (newFlash && typeof newFlash === 'string' && newFlash.trim() !== '') {
    showNotification(newFlash, 'success');
  } else if (newFlash && typeof newFlash === 'object' && newFlash.message) {
    showNotification(newFlash.message, newFlash.type || 'success');
  }
}, { deep: true, immediate: true });

const showNotification = (msg, notifType) => {
  // Limpiar timeouts anteriores
  clearTimeout(hideTimeout);
  clearInterval(progressInterval);
  
  message.value = msg;
  type.value = notifType;
  show.value = true;
  progress.value = 100;
  
  // Animación de progreso
  const duration = 5000;
  const interval = 50;
  const decrement = (interval / duration) * 100;
  
  progressInterval = setInterval(() => {
    progress.value -= decrement;
    if (progress.value <= 0) {
      clearInterval(progressInterval);
    }
  }, interval);
  
  // Auto-ocultar después de 5 segundos
  hideTimeout = setTimeout(() => {
    close();
  }, duration);
};

const close = () => {
  show.value = false;
  clearTimeout(hideTimeout);
  clearInterval(progressInterval);
  progress.value = 100;
};

const getIcon = () => {
  switch (type.value) {
    case 'success':
      return 'CheckCircleIcon';
    case 'error':
      return 'XCircleIcon';
    case 'warning':
      return 'ExclamationTriangleIcon';
    case 'info':
      return 'InformationCircleIcon';
    default:
      return 'CheckCircleIcon';
  }
};

const getColors = () => {
  switch (type.value) {
    case 'success':
      return {
        bg: 'bg-white dark:bg-slate-800',
        border: 'border-green-500 dark:border-green-400',
        text: 'text-gray-800 dark:text-gray-100',
        progress: 'bg-green-500 dark:bg-green-400'
      };
    case 'error':
      return {
        bg: 'bg-white dark:bg-slate-800',
        border: 'border-red-500 dark:border-red-400',
        text: 'text-gray-800 dark:text-gray-100',
        progress: 'bg-red-500 dark:bg-red-400'
      };
    case 'warning':
      return {
        bg: 'bg-white dark:bg-slate-800',
        border: 'border-yellow-500 dark:border-yellow-400',
        text: 'text-gray-800 dark:text-gray-100',
        progress: 'bg-yellow-500 dark:bg-yellow-400'
      };
    case 'info':
      return {
        bg: 'bg-white dark:bg-slate-800',
        border: 'border-blue-500 dark:border-blue-400',
        text: 'text-gray-800 dark:text-gray-100',
        progress: 'bg-blue-500 dark:bg-blue-400'
      };
    default:
      return {
        bg: 'bg-white dark:bg-slate-800',
        border: 'border-green-500 dark:border-green-400',
        text: 'text-gray-800 dark:text-gray-100',
        progress: 'bg-green-500 dark:bg-green-400'
      };
  }
};

const getIconColor = () => {
  switch (type.value) {
    case 'success':
      return 'text-green-600 dark:text-green-400';
    case 'error':
      return 'text-red-600 dark:text-red-400';
    case 'warning':
      return 'text-yellow-600 dark:text-yellow-400';
    case 'info':
      return 'text-blue-600 dark:text-blue-400';
    default:
      return 'text-green-600 dark:text-green-400';
  }
};

const getIconBg = () => {
  switch (type.value) {
    case 'success':
      return 'bg-green-100 dark:bg-green-900/30';
    case 'error':
      return 'bg-red-100 dark:bg-red-900/30';
    case 'warning':
      return 'bg-yellow-100 dark:bg-yellow-900/30';
    case 'info':
      return 'bg-blue-100 dark:bg-blue-900/30';
    default:
      return 'bg-green-100 dark:bg-green-900/30';
  }
};
</script>

<template>
  <Transition
    enter-active-class="transform transition duration-500 ease-out"
    enter-from-class="translate-x-full opacity-0 scale-95"
    enter-to-class="translate-x-0 opacity-100 scale-100"
    leave-active-class="transform transition duration-300 ease-in"
    leave-from-class="translate-x-0 opacity-100 scale-100"
    leave-to-class="translate-x-full opacity-0 scale-95"
  >
    <div
      v-if="show"
      class="fixed top-6 right-6 z-50 max-w-md w-full"
      role="alert"
    >
      <div 
        :class="[
          'relative overflow-hidden rounded-xl shadow-2xl border-l-4 backdrop-blur-sm',
          getColors().bg,
          getColors().border
        ]"
        class="transform hover:scale-105 transition-transform duration-200"
      >
        <!-- Contenido principal -->
        <div class="p-4 flex items-start gap-4">
          <!-- Icono con animación -->
          <div 
            :class="['p-2 rounded-lg flex-shrink-0', getIconBg()]"
            class="animate-bounce-subtle"
          >
            <Icon 
              :name="getIcon()" 
              :class="['w-6 h-6', getIconColor()]" 
            />
          </div>
          
          <!-- Mensaje -->
          <div class="flex-1 pt-0.5">
            <p 
              :class="['font-medium text-sm leading-relaxed', getColors().text]"
              class="animate-fade-in"
            >
              {{ message }}
            </p>
          </div>
          
          <!-- Botón cerrar -->
          <button
            @click="close"
            :class="['flex-shrink-0 p-1 rounded-lg transition-all duration-200', getColors().text]"
            class="hover:bg-gray-100 dark:hover:bg-slate-700 opacity-70 hover:opacity-100"
            aria-label="Cerrar notificación"
          >
            <Icon name="XMarkIcon" class="w-5 h-5" />
          </button>
        </div>
        
        <!-- Barra de progreso -->
        <div class="h-1 bg-gray-200 dark:bg-slate-700">
          <div 
            :class="['h-full transition-all duration-100 ease-linear', getColors().progress]"
            :style="{ width: `${progress}%` }"
          ></div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
@keyframes bounce-subtle {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-4px);
  }
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-bounce-subtle {
  animation: bounce-subtle 0.6s ease-out;
}

.animate-fade-in {
  animation: fade-in 0.4s ease-out;
}
</style>