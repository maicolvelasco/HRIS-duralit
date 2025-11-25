<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  users: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const form = useForm({
  user_id: null,
  fecha: new Date().toISOString().slice(0, 10),
  hora: new Date().toTimeString().slice(0, 5),
});

// Transformar los usuarios al formato que espera SearchSelect
const userOptions = computed(() => {
  if (!props.users || props.users.length === 0) return [];
  
  return props.users.map(user => ({
    id: user.id,
    nombre: `${user.nombre} ${user.apellido} (${user.codigo})`
  }));
});

function submit() {
  form.post(route('assistances.entrada.manual'), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
}

function close() {
  form.reset();
  emit('close');
}
</script>

<template>
  <Modal :show="show" :closeable="true" @close="close">
    <form @submit.prevent="submit" class="flex flex-col h-full">
      <!-- HEADER -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="ClockIcon" class="w-6 h-6 text-green-600" />
          Registrar entrada
        </h3>
      </div>

      <!-- BODY -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- Usuario -->
        <SearchSelect
          label="Usuario"
          :options="userOptions"
          v-model="form.user_id"
          :error="form.errors.user_id"
          placeholder="Buscar usuario por nombre, apellido o código..."
          :clearable="true"
        />

        <!-- Fecha -->
        <div>
          <InputLabel value="Fecha" />
          <TextInput type="date" v-model="form.fecha" class="w-full mt-1" required />
          <InputError :message="form.errors.fecha" class="mt-1" />
        </div>

        <!-- Hora -->
        <div>
          <InputLabel value="Hora" />
          <TextInput type="time" v-model="form.hora" class="w-full mt-1" required />
          <InputError :message="form.errors.hora" class="mt-1" />
        </div>
      </div>

      <!-- FOOTER -->
      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-2">
        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing || !form.user_id">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Guardar entrada</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>