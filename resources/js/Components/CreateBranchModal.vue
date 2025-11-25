<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({ show: Boolean });
const emit  = defineEmits(['close']);
const isMobile = computed(() => window.innerWidth < 768);

const form = useForm({
  nombre: '',
  departamento: '',
  provincia: '',
  localidad: '',
});

function submit() {
  form.post(route('settings.branches.store'), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
}
function close() { form.reset(); emit('close'); }
</script>

<template>
  <Modal :show="show" :closeable="true" @close="close" :max-width="isMobile?'full':'3xl'">
    <form @submit.prevent="submit" class="p-6">
      <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
        <Icon name="PlusIcon" class="w-6 h-6 text-indigo-600" />
        Crear nueva sucursal
      </h3>

      <div class="mb-4"><InputLabel value="Nombre" />
        <TextInput v-model="form.nombre" class="w-full mt-1" />
        <InputError :message="form.errors.nombre" />
      </div>

      <div class="mb-4"><InputLabel value="Departamento" />
        <TextInput v-model="form.departamento" class="w-full mt-1" />
        <InputError :message="form.errors.departamento" />
      </div>

      <div class="mb-4"><InputLabel value="Provincia" />
        <TextInput v-model="form.provincia" class="w-full mt-1" />
        <InputError :message="form.errors.provincia" />
      </div>

      <div class="mb-6"><InputLabel value="Localidad" />
        <TextInput v-model="form.localidad" class="w-full mt-1" />
        <InputError :message="form.errors.localidad" />
      </div>

      <div class="flex items-center justify-end gap-2">
        <SecondaryButton @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Crear sucursal</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>