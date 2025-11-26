<script setup>
import { computed } from 'vue';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Icon from '@/Components/Icon.vue';
import CreateHrManagerModal from '@/Components/CreateHrManagerModal.vue';
import CreateGroupManagerModal from '@/Components/CreateGroupManagerModal.vue';
import AddUsersToGroupModal from '@/Components/AddUsersToGroupModal.vue';
import ChangeManagerModal from '@/Components/ChangeManagerModal.vue';
import SearchInput from '@/Components/SearchInput.vue';

const props = defineProps({
  hrManager: Object,
  users: Array,
  managers: Array,
  unassigned: Array,
  groups: Array,
  branches: Array,
  sections: Array,
});

const showModal = ref(false);
const isEditing = ref(!!props.hrManager);

const showCreateManager = ref(false);
const showAddUsers = ref(false);
const showChangeManager = ref(false);

const searchUnassigned = ref('');
const searchManagers = ref('');

const filteredUnassigned = computed(() =>
  props.unassigned.filter(u =>
    `${u.nombre} ${u.apellido} ${u.codigo}`.toLowerCase().includes(searchUnassigned.value.toLowerCase())
  )
);

const filteredManagers = computed(() =>
  props.managers.filter(m =>
    `${m.user.nombre} ${m.user.apellido} ${m.user.codigo}`.toLowerCase().includes(searchManagers.value.toLowerCase())
  )
);
</script>

<template>
  <Head title="Recursos Humanos" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
        <Icon name="UserGroupIcon" class="w-6 h-6 text-indigo-600" />
        Recursos Humanos
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

        <!-- RRHH -->
        <div class="bg-white dark:bg-slate-800 shadow-md rounded-xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Usuario asignado como RRHH</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ props.hrManager ? `${props.hrManager.user.nombre} ${props.hrManager.user.apellido} (${props.hrManager.user.codigo})` : 'No hay un usuario asignado aún.' }}
              </p>
            </div>
            <PrimaryButton @click="showModal = true">
              <Icon :name="isEditing ? 'ArrowPathIcon' : 'PlusIcon'" class="w-5 h-5 mr-2" />
              {{ isEditing ? 'Cambiar RRHH' : 'Asignar RRHH' }}
            </PrimaryButton>
          </div>
        </div>

        <!-- Jefes de grupo -->
        <div class="bg-white dark:bg-slate-800 shadow-md rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Jefes de grupo</h3>
            <PrimaryButton @click="showCreateManager = true">Asignar jefe</PrimaryButton>
        </div>

        <SearchInput v-model="searchManagers" placeholder="Buscar jefe..." />

        <div v-if="!filteredManagers.length" class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            No hay jefes asignados.
        </div>

        <div v-else class="space-y-2 mt-4">
            <details v-for="m in filteredManagers" :key="m.group_id" class="group">
            <summary class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-900 rounded-lg cursor-pointer">
                <div>
                <p class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ m.user.nombre }} {{ m.user.apellido }} ({{ m.user.codigo }})
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Grupo: {{ m.group.nombre }}</p>
                </div>
                <div class="flex gap-2">
                <PrimaryButton size="sm" @click.stop="showChangeManager = true">Cambiar</PrimaryButton>
                <PrimaryButton size="sm" @click.stop="showAddUsers = true">Añadir</PrimaryButton>
                <Icon name="ChevronDownIcon" class="w-5 h-5 text-gray-400 group-open:rotate-180 transition" />
                </div>
            </summary>

            <div class="mt-2 ml-4 space-y-2">
                <div v-for="u in m.group.users" :key="u.id" class="flex items-center justify-between p-2 bg-white dark:bg-slate-800 rounded">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ u.nombre }} {{ u.apellido }} ({{ u.codigo }})
                </p>
                <PrimaryButton
                    size="sm"
                    @click="$inertia.post(route('group-managers.remove-user'), { user_id: u.id })"
                >
                    Quitar
                </PrimaryButton>
                </div>
                <p v-if="!m.group.users.length" class="text-sm text-gray-500 dark:text-gray-400">No hay usuarios en este grupo.</p>
            </div>
            </details>
        </div>
        </div>

        <!-- Usuarios sin grupo -->
        <div class="bg-white dark:bg-slate-800 shadow-md rounded-xl p-6">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Usuarios sin grupo</h3>
          <SearchInput v-model="searchUnassigned" placeholder="Buscar usuario..." />
          <div v-if="!filteredUnassigned.length" class="text-sm text-gray-500 dark:text-gray-400 mt-2">No hay usuarios sin grupo.</div>
          <div v-else class="space-y-2 mt-4">
            <div v-for="u in filteredUnassigned" :key="u.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-900 rounded-lg">
              <div>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ u.nombre }} {{ u.apellido }} ({{ u.codigo }})</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Grupo: {{ u.group?.nombre || 'Sin grupo' }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Modales -->
    <CreateHrManagerModal :show="showModal" :users="users" :is-editing="isEditing" :hr-manager="hrManager" @close="showModal = false" />
    <CreateGroupManagerModal :show="showCreateManager" :users="users" :groups="groups" @close="showCreateManager = false" />
    <AddUsersToGroupModal :show="showAddUsers" :users="users" :groups="groups" :branches="branches" :sections="sections" @close="showAddUsers = false" />
    <ChangeManagerModal :show="showChangeManager" :users="users" :managers="managers" @close="showChangeManager = false" />
  </AuthenticatedLayout>
</template>