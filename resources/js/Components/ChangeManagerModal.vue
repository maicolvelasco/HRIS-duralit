<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import SearchSelect from '@/Components/SearchSelect.vue';

const props = defineProps({
  show: Boolean,
  users: Array,
  managers: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
  old_user_id: null,
  new_user_id: null,
});

const submit = () => {
  form.post(route('group-managers.change'), {
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
      <h3 class="text-lg font-semibold mb-4">Cambiar jefe de grupo</h3>

      <SearchSelect
        label="Usuario a cambiar"
        :options="users.map(u => ({ id: u.id, nombre: `${u.nombre} ${u.apellido} (${u.codigo})` }))"
        v-model="form.old_user_id"
        placeholder="Buscar usuario..."
        clearable
      />

      <SearchSelect
        label="Nuevo jefe"
        :options="managers.map(m => ({ id: m.user.id, nombre: `${m.user.nombre} ${m.user.apellido} (${m.user.codigo}) - Grupo: ${m.group.nombre}` }))"
        v-model="form.new_user_id"
        placeholder="Buscar jefe..."
        clearable
      />

      <div class="flex justify-end gap-2 mt-6">
        <PrimaryButton @click="emit('close')">Cancelar</PrimaryButton>
        <PrimaryButton @click="submit">Cambiar</PrimaryButton>
      </div>
    </div>
  </Modal>
</template>