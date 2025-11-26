<script setup>
import { computed, ref, watch } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

defineProps({ show: { type: Boolean, default: false } });
const emit = defineEmits(['close']);

const user = usePage().props.auth.user;

/* ------------- form ------------- */
const form = useForm({
  user_id: user.id,
  fecha: new Date().toISOString().slice(0,10),
  desde: '',
  hasta: '',
  contador: 0,
  trabajo: '',
  estado: 'Pendiente',
});

/* ------------- calcular horas ------------- */
const calcularHoras = () => {
  if (!form.desde || !form.hasta) { form.contador = 0; return; }
  const [hd, md] = form.desde.split(':').map(Number);
  const [hh, mh] = form.hasta.split(':').map(Number);
  const minDesde = hd * 60 + md;
  const minHasta = hh * 60 + mh;
  const diff = minHasta >= minDesde ? minHasta - minDesde : (minHasta + 24*60) - minDesde;
  form.contador = Math.round(diff / 60 * 100) / 100;
};
watch([() => form.desde, () => form.hasta], calcularHoras);

/* ------------- submit ------------- */
function submit() {
  form.post(route('overtimes.store'), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
}
function close() {
  form.reset();
  form.fecha = new Date().toISOString().slice(0,10);
  emit('close');
}
</script>

<template>
  <Modal :show="show" :closeable="true" @close="close">
    <form @submit.prevent="submit" class="flex flex-col h-full">
      <!-- header -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="ClockIcon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
          Registrar Sobre Tiempo
        </h3>
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
          {{ user.codigo }} - {{ user.nombre }} {{ user.apellido }}
        </span>
      </div>

      <!-- body -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- Usuario (solo lectura) -->
        <div>
          <InputLabel value="Usuario" />
          <TextInput :value="`${user.codigo} - ${user.nombre} ${user.apellido}`" class="w-full mt-1" disabled />
        </div>

        <!-- Fecha -->
        <div>
          <InputLabel value="Fecha" />
          <TextInput type="date" v-model="form.fecha" class="w-full mt-1" required />
          <InputError :message="form.errors.fecha" />
        </div>

        <!-- Desde / Hasta -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel value="Desde" />
            <TextInput type="time" v-model="form.desde" class="w-full mt-1" required />
            <InputError :message="form.errors.desde" />
          </div>
          <div>
            <InputLabel value="Hasta" />
            <TextInput type="time" v-model="form.hasta" class="w-full mt-1" required />
            <InputError :message="form.errors.hasta" />
          </div>
        </div>

        <!-- Horas calculadas -->
        <div>
          <InputLabel value="Horas totales" />
          <TextInput :value="form.contador" class="w-full mt-1" disabled />
          <InputError :message="form.errors.contador" />
        </div>

        <!-- Trabajo -->
        <div>
          <InputLabel value="Trabajo realizado" />
          <Textarea v-model="form.trabajo" class="w-full mt-1" rows="3" required placeholder="Describa el motivo del sobretiempo..." />
          <InputError :message="form.errors.trabajo" />
        </div>
      </div>

      <!-- footer -->
      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-2">
        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Guardar</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>