<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
  modelValue: [String, Number, null], // Permite valores null
  options: Array,
  label: String,
  placeholder: { type: String, default: 'Escriba para filtrar…' },
  error: String,
  clearable: { type: Boolean, default: true }, // Nueva prop para permitir limpiar
});

const emit = defineEmits(['update:modelValue']);

const search = ref('');
const open = ref(false);
const selectedText = ref('');

const filtered = computed(() =>
  props.options.filter(o =>
    o.nombre.toLowerCase().includes(search.value.toLowerCase())
  )
);

// cuando cambia el valor externo, sincronizamos el texto visible
watch(
  () => props.modelValue,
  val => {
    const match = props.options.find(o => o.id === val);
    selectedText.value = match ? match.nombre : '';
    search.value = selectedText.value;
  },
  { immediate: true }
);

function select(option) {
  emit('update:modelValue', option.id);
  selectedText.value = option.nombre;
  search.value = option.nombre;
  open.value = false;
}

function clear() {
  emit('update:modelValue', null);
  selectedText.value = '';
  search.value = '';
  open.value = false;
}

function focusInput() {
  open.value = true;
  nextTick(() => (search.value = ''));
}

function blurList() {
  setTimeout(() => {
    open.value = false;
    // Si el campo está vacío, limpiar la selección
    if (!search.value.trim()) {
      if (props.modelValue) {
        emit('update:modelValue', null);
      }
      selectedText.value = '';
    } else if (selectedText.value) {
      // Restaurar el texto de la selección actual
      search.value = selectedText.value;
    }
  }, 150);
}
</script>

<template>
  <div>
    <InputLabel :value="label" />
    <div class="relative mt-1">
      <TextInput
        :placeholder="placeholder"
        v-model="search"
        @focus="focusInput"
        @blur="blurList"
        class="w-full pr-10"
      />
      
      <!-- Botón para limpiar selección -->
      <button
        v-if="clearable && modelValue"
        type="button"
        @click.stop="clear"
        @mousedown.prevent
        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
      >
        <Icon name="XMarkIcon" class="w-4 h-4" />
      </button>
      
      <!-- Ícono de flecha -->
      <Icon
        name="ChevronUpDownIcon"
        class="w-5 h-5 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
      />
      
      <!-- Lista desplegable -->
      <ul
        v-show="open && filtered.length"
        class="absolute z-10 w-full mt-1 max-h-60 overflow-auto bg-white dark:bg-slate-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg"
      >
        <li
          v-for="o in filtered"
          :key="o.id"
          @mousedown.prevent="select(o)"
          class="px-3 py-2 cursor-pointer text-gray-900 dark:text-gray-100 hover:bg-indigo-50 dark:hover:bg-slate-700"
        >
          {{ o.nombre }}
        </li>
      </ul>
    </div>
    <InputError :message="error" />
  </div>
</template>