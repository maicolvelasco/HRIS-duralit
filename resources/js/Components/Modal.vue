<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  maxWidth: { type: String, default: '2xl' },
  closeable: { type: Boolean, default: true },
  scrollable: { type: Boolean, default: false }, // Nueva prop
});

const emit = defineEmits(['close']);
const dialog = ref();
const showSlot = ref(props.show);

const isMobile = computed(() => window.innerWidth < 768);

watch(
  () => props.show,
  () => {
    if (props.show) {
      document.body.style.overflow = 'hidden';
      showSlot.value = true;
      dialog.value?.showModal();
    } else {
      document.body.style.overflow = '';
      setTimeout(() => {
        dialog.value?.close();
        showSlot.value = false;
      }, 200);
    }
  },
);

const close = () => {
  if (props.closeable) emit('close');
};

const closeOnEscape = (e) => {
  if (e.key === 'Escape' && props.show) {
    e.preventDefault();
    close();
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape);
  document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
  return {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '5xl': 'sm:max-w-5xl',
    full: 'max-w-full',
  }[props.maxWidth];
});
</script>

<template>
  <dialog
    class="z-50 m-0 min-h-screen min-w-full p-0 bg-transparent backdrop:bg-transparent"
    ref="dialog"
  >
    <div class="fixed inset-0 z-50 overflow-hidden" scroll-region>
      <Transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-show="show"
          class="fixed inset-0 transition-all"
          @click="close"
        >
          <div class="absolute inset-0 bg-gray-500 dark:bg-slate-900 opacity-75" />
        </div>
      </Transition>

      <Transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0 translate-y-full"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-100 translate-y-full"
      >
        <div
          v-show="show"
          class="fixed inset-0 flex items-end justify-center max-h-[100dvh]"
          :class="[
            isMobile ? '' : 'items-center',
            scrollable && !isMobile ? 'py-4' : 'px-4'
          ]"
        >
          <div
            class="flex flex-col w-full bg-white dark:bg-slate-800 shadow-xl rounded-t-2xl sm:rounded-lg"
            :class="[
              isMobile
                ? 'max-h-[100dvh] flex-grow flex flex-col'
                : scrollable 
                  ? `max-w-full ${maxWidthClass} max-h-[calc(100vh-2rem)] overflow-y-auto`
                  : `max-w-full ${maxWidthClass} max-h-[90vh]`
            ]"
            @click.stop
          >
            <div
              class="flex flex-col"
              :class="[isMobile ? 'flex-grow overflow-y-auto' : '']"
            >
              <slot v-if="showSlot" />
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </dialog>
</template>