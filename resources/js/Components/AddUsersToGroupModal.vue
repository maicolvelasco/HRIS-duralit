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
  branches: Array,
  sections: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
  branch_id: null,
  section_id: null,
  target_group_id: null,
  user_ids: [],
});

const submit = () => {
  form.post(route('group-managers.add-users'), {
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
      <h3 class="text-lg font-semibold mb-4">Añadir usuarios al grupo</h3>

      <SearchSelect
        label="Sucursal"
        :options="branches"
        v-model="form.branch_id"
        placeholder="Buscar sucursal..."
        clearable
      />

      <SearchSelect
        label="Sección"
        :options="sections"
        v-model="form.section_id"
        placeholder="Buscar sección..."
        clearable
      />

      <SearchSelect
        label="Grupo destino"
        :options="groups"
        v-model="form.target_group_id"
        placeholder="Buscar grupo destino..."
        clearable
      />

      <div class="mt-4">
        <label class="block text-sm font-medium mb-2">Usuarios individuales (opcional)</label>
        <select multiple v-model="form.user_ids" class="w-full border rounded px-3 py-2 h-32">
          <option v-for="u in users" :key="u.id" :value="u.id">
            {{ u.nombre }} {{ u.apellido }} ({{ u.codigo }})
          </option>
        </select>
      </div>

      <div class="flex justify-end gap-2 mt-6">
        <PrimaryButton @click="emit('close')">Cancelar</PrimaryButton>
        <PrimaryButton @click="submit">Añadir</PrimaryButton>
      </div>
    </div>
  </Modal>
</template>