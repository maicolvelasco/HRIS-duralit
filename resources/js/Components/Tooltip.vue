<!-- resources/js/Components/Tooltip.vue -->
<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  show: Boolean,
  placement: {
    type: String,
    default: 'top'
  },
  maxWidth: {
    type: String,
    default: 'sm' // xs, sm, md, lg, xl, full
  }
});

const emit = defineEmits(['close']);

const tooltipRef = ref(null);
const maxWidthClasses = {
  xs: 'max-w-xs',
  sm: 'max-w-sm',
  md: 'max-w-md',
  lg: 'max-w-lg',
  xl: 'max-w-xl',
  full: 'max-w-full'
};

function positionTooltip(triggerEl) {
  if (!tooltipRef.value || !triggerEl) return;

  nextTick(() => {
    const tooltipRect = tooltipRef.value.getBoundingClientRect();
    const triggerRect = triggerEl.getBoundingClientRect();
    
    let top = 0;
    let left = 0;

    switch (props.placement) {
      case 'top':
        top = triggerRect.top - tooltipRect.height - 10;
        left = triggerRect.left + (triggerRect.width - tooltipRect.width) / 2;
        break;
      case 'bottom':
        top = triggerRect.bottom + 10;
        left = triggerRect.left + (triggerRect.width - tooltipRect.width) / 2;
        break;
      case 'left':
        top = triggerRect.top + (triggerRect.height - tooltipRect.height) / 2;
        left = triggerRect.left - tooltipRect.width - 10;
        break;
      case 'right':
        top = triggerRect.top + (triggerRect.height - tooltipRect.height) / 2;
        left = triggerRect.right + 10;
        break;
    }

    // Ajustar para no salir de la pantalla
    const padding = 10;
    if (left < padding) left = padding;
    if (left + tooltipRef.value.offsetWidth > window.innerWidth - padding) {
      left = window.innerWidth - tooltipRef.value.offsetWidth - padding;
    }
    
    if (top < padding) top = triggerRect.bottom + 10;
    if (top + tooltipRect.height > window.innerHeight - padding) {
      top = triggerRect.top - tooltipRect.height - 10;
    }

    tooltipRef.value.style.top = `${top}px`;
    tooltipRef.value.style.left = `${left}px`;
  });
}

// Cerrar al hacer click fuera
function handleClickOutside(e) {
  if (tooltipRef.value && !tooltipRef.value.contains(e.target)) {
    emit('close');
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});

defineExpose({ positionTooltip });
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="show"
        ref="tooltipRef"
        :class="[
          'fixed z-50 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-800',
          'p-4',
          maxWidthClasses[props.maxWidth]
        ]"
        style="position: fixed; z-index: 9999;"
      >
        <div class="absolute w-3 h-3 bg-white dark:bg-slate-800 border-l border-t border-gray-200 dark:border-gray-700 transform rotate-45 -top-[6px] left-1/2 -translate-x-1/2"></div>
        <slot />
      </div>
    </Transition>
  </Teleport>
</template>