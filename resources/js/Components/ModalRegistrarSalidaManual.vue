<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  assistance: Object, // { id, user: { nombre, apellido } }
});

const emit = defineEmits(['close']);

const form = useForm({
  fecha: new Date().toISOString().slice(0, 10),
  hora: new Date().toTimeString().slice(0, 5),
});

function submit() {
  form.patch(route('assistances.salida.manual', props.assistance.id), {
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
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="ArrowRightOnRectangleIcon" class="w-6 h-6 text-orange-600" />
          Registrar salida
        </h3>
      </div>

      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- Usuario (solo lectura) -->
        <div>
          <InputLabel value="Usuario" />
          <TextInput
            :modelValue="`${assistance.user.nombre} ${assistance.user.apellido}`"
            class="w-full mt-1"
            readonly
            disabled
          />
        </div>

        <!-- Fecha -->
        <div>
          <InputLabel value="Fecha" />
          <TextInput type="date" v-model="form.fecha" class="w-full mt-1" required />
          <InputError :message="form.errors.fecha" />
        </div>

        <!-- Hora -->
        <div>
          <InputLabel value="Hora" />
          <TextInput type="time" v-model="form.hora" class="w-full mt-1" required />
          <InputError :message="form.errors.hora" />
        </div>
      </div>

      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-2">
        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Guardar salida</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>