<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import SearchSelect from '@/Components/SearchSelect.vue';

const props = defineProps({
  show: Boolean,
  users: Array,
  groups: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
  user_id: null,
  group_id: null,
});

const submit = () => {
  form.post(route('group-managers.assign'), {
    onSuccess: () => {
      form.reset();
      emit('close');
    },
  });
};
</script>

<template>
  <Modal :show="show" @close="emit('close')">
    <div class="p-6">
      <h3 class="text-lg font-semibold mb-4">Asignar jefe de grupo</h3>

      <SearchSelect
        label="Seleccionar usuario"
        :options="users.map(u => ({ id: u.id, nombre: `${u.nombre} ${u.apellido} (${u.codigo})` }))"
        v-model="form.user_id"
        placeholder="Buscar usuario..."
        clearable
      />

      <SearchSelect
        label="Seleccionar grupo"
        :options="groups"
        v-model="form.group_id"
        placeholder="Buscar grupo..."
        clearable
      />

      <div class="flex justify-end gap-2 mt-6">
        <PrimaryButton @click="emit('close')">Cancelar</PrimaryButton>
        <PrimaryButton @click="submit">Asignar</PrimaryButton>
      </div>
    </div>
  </Modal>
</template>