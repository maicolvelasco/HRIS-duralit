<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';
import SearchSelect from '@/Components/SearchSelect.vue';

const props = defineProps({
  show: Boolean,
  users: Array,
  isEditing: Boolean,
  hrManager: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
  user_id: props.hrManager?.user.id || null,
});

const usersOptions = computed(() =>
  props.users.map(u => ({
    id: u.id,
    nombre: `${u.nombre} ${u.apellido} (${u.codigo})`,
  }))
);

function submit() {
  form.transform((data) => ({
    ...data,
    _method: 'PUT', // 👈 Simula PUT
  })).post(route('rrhh.update'), {
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
    <form @submit.prevent="submit" class="p-6">
      <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
        <Icon name="UserPlusIcon" class="w-6 h-6 text-indigo-600" />
        {{ isEditing ? 'Cambiar usuario de RRHH' : 'Asignar usuario como RRHH' }}
      </h3>

      <div class="mb-4">
        <SearchSelect
        label="Seleccionar usuario"
        :options="usersOptions"
        v-model="form.user_id"
        :error="form.errors.user_id"
        placeholder="Buscar usuario..."
        clearable
        />
      </div>

      <div class="flex items-center justify-end gap-2">
        <SecondaryButton @click="close">Cancelar</SecondaryButton>
        <PrimaryButton :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Guardar</span>
        </PrimaryButton>
      </div>
    </form>
  </Modal>
</template>