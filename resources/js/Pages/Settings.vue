<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import Icon from '@/Components/Icon.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import CreatePersonModal from '@/Components/CreatePersonModal.vue';
import EditPersonModal from '@/Components/EditPersonModal.vue';
import CreateRolModal from '@/Components/CreateRolModal.vue';
import EditRolModal   from '@/Components/EditRolModal.vue';
import CreateBranchModal from '@/Components/CreateBranchModal.vue';
import EditBranchModal from '@/Components/EditBranchModal.vue';
import CreateGroupModal from '@/Components/CreateGroupModal.vue';
import EditGroupModal   from '@/Components/EditGroupModal.vue';
import CreateSectionModal from '@/Components/CreateSectionModal.vue';
import EditSectionModal   from '@/Components/EditSectionModal.vue';
import CreatePermissionModal from '@/Components/CreatePermissionModal.vue';
import EditPermissionModal from '@/Components/EditPermissionModal.vue';
import CreateShiftModal from '@/Components/CreateShiftModal.vue';
import EditShiftModal from '@/Components/EditShiftModal.vue';
import HolidayCalendar from '@/Components/HolidayCalendar.vue';
import SearchInput from '@/Components/SearchInput.vue';
import CreateAuthorizationModal from '@/Components/CreateAuthorizationModal.vue';
import EditAuthorizationModal   from '@/Components/EditAuthorizationModal.vue';
import GenerateReportModal from '@/Components/GenerateReportModal.vue';

// ==================== VARIABLES DE BÚSQUEDA ====================
const usersSearch = ref('');
const rolesSearch = ref('');
const permissionsSearch = ref('');
const userPermissionsSearch = ref('');
const branchesSearch = ref('');
const groupsSearch = ref('');
const sectionsSearch = ref('');
const shiftsSearch = ref('');

// ==================== COMPUTED PROPERTIES FILTRADAS ====================

// Filtrar usuarios (por nombre, apellido y código)
const filteredUsers = computed(() => {
  if (!usersSearch.value) return props.users;
  
  const search = usersSearch.value.toLowerCase();
  return props.users.filter(user => {
    const fullName = `${user.nombre} ${user.apellido}`.toLowerCase();
    const code = user.codigo?.toLowerCase() || '';
    return fullName.includes(search) || code.includes(search);
  });
});

// Filtrar roles (por nombre y descripción)
const filteredRoles = computed(() => {
  if (!rolesSearch.value) return props.roles;
  
  const search = rolesSearch.value.toLowerCase();
  return props.roles.filter(role => 
    role.nombre.toLowerCase().includes(search) || 
    role.descripcion?.toLowerCase().includes(search)
  );
});

// Filtrar permisos (por nombre y descripción)
const filteredPermissions = computed(() => {
  if (!permissionsSearch.value) return props.permissionsList;
  
  const search = permissionsSearch.value.toLowerCase();
  return props.permissionsList.filter(permission => 
    permission.nombre.toLowerCase().includes(search) || 
    permission.descripcion?.toLowerCase().includes(search)
  );
});

// Filtrar permisos por persona (por nombre, apellido y código)
const filteredUsersWithPermissions = computed(() => {
  if (!userPermissionsSearch.value) return props.usersWithPermissions;
  
  const search = userPermissionsSearch.value.toLowerCase();
  return props.usersWithPermissions.filter(user => {
    const fullName = `${user.nombre} ${user.apellido}`.toLowerCase();
    const code = user.codigo?.toLowerCase() || '';
    return fullName.includes(search) || code.includes(search);
  });
});

// Filtrar sucursales (por nombre, departamento, provincia y localidad)
const filteredBranches = computed(() => {
  if (!branchesSearch.value) return props.branches;
  
  const search = branchesSearch.value.toLowerCase();
  return props.branches.filter(branch => 
    branch.nombre.toLowerCase().includes(search) || 
    branch.departamento.toLowerCase().includes(search) || 
    branch.provincia.toLowerCase().includes(search) || 
    branch.localidad.toLowerCase().includes(search)
  );
});

// Filtrar grupos (por nombre y descripción)
const filteredGroups = computed(() => {
  if (!groupsSearch.value) return props.groups;
  
  const search = groupsSearch.value.toLowerCase();
  return props.groups.filter(group => 
    group.nombre.toLowerCase().includes(search) || 
    group.descripcion?.toLowerCase().includes(search)
  );
});

// Filtrar secciones (por nombre y descripción)
const filteredSections = computed(() => {
  if (!sectionsSearch.value) return props.sections;
  
  const search = sectionsSearch.value.toLowerCase();
  return props.sections.filter(section => 
    section.nombre.toLowerCase().includes(search) || 
    section.descripcion?.toLowerCase().includes(search)
  );
});

const authorizationsSearch = ref('');
// Filtrar permiso (por nombre y descripción)
const filteredAuthorizations = computed(() => {
  if (!authorizationsSearch.value) return props.authorizations;
  const s = authorizationsSearch.value.toLowerCase();
  return props.authorizations.filter(a =>
    a.nombre.toLowerCase().includes(s) ||
    a.descripcion?.toLowerCase().includes(s)
  );
});

// Filtrar turnos (por nombre)
const filteredShifts = computed(() => {
  if (!shiftsSearch.value) return props.shifts;
  
  const search = shiftsSearch.value.toLowerCase();
  return props.shifts.filter(shift => 
    shift.nombre.toLowerCase().includes(search)
  );
});

// ==================== TOOLTIP IMPORTS ====================
import Tooltip from '@/Components/Tooltip.vue';
import { useTooltip } from '@/Composables/useTooltip.js';

const props = defineProps({
  users: Array,
  roles: Array,
  branches: Array,
  groups: Array,
  sections: Array,
  permissionsList: Array,
  shifts: Array,
  holidays: Array,
  locations: Object,
  authorizations: {
    type: Array,
    default: () => []
  },
  usersWithPermissions: {
    type: Array,
    default: () => []
  },
  allPermissions: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Object,
    default: () => ({})
  },
  defaultSection: {
    type: String,
    default: 'users'
  }
});

const showSection = ref(props.defaultSection);
const isMobile = () => window.innerWidth < 1024;

// ==================== TOOLTIP SETUP ====================
const tooltipRef = ref(null);
const {
  tooltipData,
  show,
  onMouseEnter,
  onMouseLeave,
  onTouchStart,
  onTouchEnd,
  onTouchMove,
  hideTooltip
} = useTooltip();

// Guardar referencia global para posicionamiento
onMounted(() => {
  window.tooltipComponent = tooltipRef.value;
});

// Función para obtener nombre de relación
const getRelationName = (items, id, field = 'nombre') => {
  const item = items?.find(i => i.id === id);
  return item ? item[field] : 'N/A';
};

// Preparar datos para tooltip de usuario
const getUserTooltipData = (user) => {
  const branchName = getRelationName(props.branches, user.branch_id);
  const groupName = getRelationName(props.groups, user.group_id);
  const sectionName = getRelationName(props.sections, user.section_id);
  const rolName = getRelationName(props.roles, user.rol_id);
  
  return {
    type: 'user',
    data: {
      ...user,
      branchName,
      groupName,
      sectionName,
      rolName,
      permissionsCount: user.permissions?.length || 0
    }
  };
};

// Preparar datos para tooltip de turno
const getShiftTooltipData = (shift) => {
  return {
    type: 'shift',
    data: {
      ...shift,
      hasUsers: shift.users?.length > 0,
      hasBranches: shift.branches?.length > 0,
      hasGroups: shift.groups?.length > 0,
      hasSections: shift.sections?.length > 0,
      hasRoles: shift.roles?.length > 0
    }
  };
};

// Modales de Personas
const showCreatePerson = ref(false);
const showEditPerson = ref(false);
const selectedUser = ref({});
const expandedUsers = ref({});

// Modales de Roles
const showCreateRol = ref(false);
const showEditRol = ref(false);
const selectedRol = ref({});

// Modales de Sucursales
const showCreateBranch = ref(false);
const showEditBranch = ref(false);
const selectedBranch = ref({});

// Modales de Grupos
const showCreateGroup = ref(false);
const showEditGroup   = ref({});
const selectedGroup   = ref({});

// Modales de Secciones
const showCreateSection = ref(false);
const showEditSection   = ref(false);
const selectedSection   = ref({});

// Modales de permisos
const showCreateAuthorization = ref(false);
const showEditAuthorization   = ref(false);
const selectedAuthorization   = ref({});

// Modales de PERMISOS
const showCreatePermission = ref(false);
const showEditPermission = ref(false);
const selectedPermission = ref({});

// Modales de TURNOS
const showCreateShift = ref(false);
const showEditShift = ref(false);
const selectedShift = ref({});

// Estado del modal de reportes
const showReportModal = ref(false);
const selectedReport = ref('');

const availableReports = computed(() => {
  const reports = [];
  
  if (props.permissions['Ver Usuarios']) {
    reports.push({ key: 'users', title: 'Personas', subtitle: 'Exportar datos de usuarios', icon: 'UsersIcon' });
  }
  if (props.permissions['Ver Roles']) {
    reports.push({ key: 'roles', title: 'Roles', subtitle: 'Exportar datos de roles', icon: 'IdentificationIcon' });
  }
  if (props.permissions['Ver Permisos']) {
    reports.push({ key: 'permissions', title: 'Permisos', subtitle: 'Exportar datos de permisos', icon: 'KeyIcon' });
  }
  if (props.permissions['Ver Sucursales']) {
    reports.push({ key: 'branches', title: 'Sucursales', subtitle: 'Exportar datos de sucursales', icon: 'BuildingStorefrontIcon' });
  }
  if (props.permissions['Ver Grupos']) {
    reports.push({ key: 'groups', title: 'Grupos', subtitle: 'Exportar datos de grupos', icon: 'UserGroupIcon' });
  }
  if (props.permissions['Ver Secciones']) {
    reports.push({ key: 'sections', title: 'Secciones', subtitle: 'Exportar datos de secciones', icon: 'QueueListIcon' });
  }
  if (props.permissions['Ver Permisos de Trabajo']) {
    reports.push({ key: 'authorizations', title: 'Permisos de Trabajo', subtitle: 'Exportar datos de autorizaciones', icon: 'ClipboardDocumentCheckIcon' });
  }
  if (props.permissions['Ver Turnos']) {
    reports.push({ key: 'shifts', title: 'Turnos', subtitle: 'Exportar datos de turnos', icon: 'CalendarDaysIcon' });
  }
  if (props.permissions['Ver Feriados']) {
    reports.push({ key: 'holidays', title: 'Feriados', subtitle: 'Exportar datos de feriados', icon: 'CalendarDaysIcon' });
  }
  
  return reports;
});

function openReportModal(reportKey) {
  selectedReport.value = reportKey;
  showReportModal.value = true;
}

function handleResize() {
  if (window.innerWidth >= 1024 && showSection.value === null) {
    showSection.value = props.defaultSection;
  }
}

onMounted(() => {
  showSection.value = window.innerWidth < 1024 ? null : props.defaultSection;
  window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize);
});

// Determina el estado actual del permiso (considerando herencia)
const getPermissionStatus = (user, permission) => {
  const hasDirect = user.permissions.some(p => p.id === permission.id);
  const isDenied = user.denied_permission_ids?.includes(permission.id);
  
  const userRole = props.roles.find(r => r.id === user.rol_id);
  const roleHasPermission = userRole?.permissions?.some(p => p.id === permission.id);
  
  return hasDirect || (roleHasPermission && !isDenied);
};

const togglePermission = async (user, permission) => {
  const currentStatus = getPermissionStatus(user, permission);
  const newStatus = !currentStatus;
  
  console.log(`🔄 Cambiando permiso: ${permission.nombre} para ${user.nombre} → ${newStatus}`);
  
  const previousDirect = [...user.permissions];
  const previousDenied = [...(user.denied_permission_ids || [])];
  
  try {
    if (newStatus) {
      user.denied_permission_ids = user.denied_permission_ids?.filter(id => id !== permission.id) || [];
      if (!user.permissions.find(p => p.id === permission.id)) {
        user.permissions.push({...permission});
      }
    } else {
      const index = user.permissions.findIndex(p => p.id === permission.id);
      if (index > -1) user.permissions.splice(index, 1);
      
      if (!user.denied_permission_ids) {
        user.denied_permission_ids = [];
      }
      if (!user.denied_permission_ids.includes(permission.id)) {
        user.denied_permission_ids.push(permission.id);
      }
    }
    
    await router.post(
      route('settings.user-permissions.toggle', user.id),
      { permission_id: permission.id, granted: newStatus },
      { preserveScroll: true, preserveState: true }
    );
    
    setTimeout(() => {
      router.reload({ only: ['usersWithPermissions'], preserveState: true, preserveScroll: true });
    }, 300);

  } catch (error) {
    console.error('❌ ERROR:', error);
    user.permissions = previousDirect;
    user.denied_permission_ids = previousDenied;
    alert('Error al guardar el permiso. Por favor, intenta de nuevo.');
    await router.reload({ only: ['usersWithPermissions'], preserveState: true, preserveScroll: true });
  }
};

const toggleUserExpansion = (userId) => {
  expandedUsers.value[userId] = !expandedUsers.value[userId];
};

const iconNames = ['ChevronUpIcon', 'ChevronDownIcon', 'UserCircleIcon'];
</script>

<template>
  <Head title="Ajustes" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
        <Icon name="Cog6ToothIcon" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
        Ajustes
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-6">

          <!-- ========= MENÚ LATERAL ========= -->
          <aside :class="['w-full lg:w-1/4', { hidden: isMobile() && showSection !== null }]">
            <nav class="space-y-2">
              <!-- BOTÓN PERSONAS -->
              <button
                v-if="permissions['Ver Usuarios']"
                @click="showSection = 'users'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'users'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="UsersIcon" class="w-5 h-5" />
                Personas
              </button>

              <button
                v-if="permissions['Ver Roles']"
                @click="showSection = 'roles'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'roles'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="IdentificationIcon" class="w-5 h-5" />
                Roles
              </button>

              <button
                v-if="permissions['Ver Permisos']"
                @click="showSection = 'permissions'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'permissions'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="KeyIcon" class="w-5 h-5" />
                Permisos
              </button>

              <button
                v-if="permissions['Ver Permisos Personales']"
                @click="showSection = 'user-permissions'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'user-permissions'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="UserCircleIcon" class="w-5 h-5" />
                Permisos por persona
              </button>

              <button
                v-if="permissions['Ver Sucursales']"
                @click="showSection = 'branches'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'branches'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="BuildingStorefrontIcon" class="w-5 h-5" />
                Sucursales
              </button>

              <button
                v-if="permissions['Ver Grupos']"
                @click="showSection = 'groups'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'groups'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="UserGroupIcon" class="w-5 h-5" />
                Grupos
              </button>

              <button
                v-if="permissions['Ver Secciones']"
                @click="showSection = 'sections'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'sections'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="QueueListIcon" class="w-5 h-5" />
                Secciones
              </button>

              <button
                v-if="permissions['Ver Permisos de Trabajo']"
                @click="showSection = 'authorizations'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'authorizations'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="ClipboardDocumentCheckIcon" class="w-5 h-5" />
                Permisos de Trabajo
              </button>

              <button v-if="permissions['Ver Turnos']" @click="showSection = 'shifts'" :class="[
                'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                showSection === 'shifts'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
              ]">
                <Icon name="CalendarDaysIcon" class="w-5 h-5" />
                Turnos
              </button>

              <button
                v-if="permissions['Ver Feriados']"
                @click="showSection = 'holidays'"
                :class="[
                    'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                    showSection === 'holidays'
                        ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="CalendarDaysIcon" class="w-5 h-5" />
                Feriados
              </button>

              <!-- --------------- REPORTES --------------- -->
              <button
                v-if="permissions['Ver Reportes']"
                @click="showSection = 'reports'"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left transition',
                  showSection === 'reports'
                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
                ]"
              >
                <Icon name="DocumentArrowDownIcon" class="w-5 h-5" />
                Reportes
              </button>
            </nav>
          </aside>

          <!-- ========= CONTENIDO DINÁMICO ========= -->
          <main :class="['w-full lg:w-3/4', { hidden: isMobile() && showSection === null }]">

            <!-- --------------- PERSONAS --------------- -->
            <div v-if="showSection === 'users' && permissions['Ver Usuarios']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Listado de personas</h4>
                <Button v-if="permissions['Crear Usuarios']" variant="primary" @click="showCreatePerson = true">
                  <Icon name="UserPlusIcon" class="w-5 h-5" /> Nueva persona
                </Button>
              </div>

              <!-- AÑADIR BUSCADOR -->
              <SearchInput 
                v-model="usersSearch" 
                placeholder="Buscar por nombre o código..." 
              />

              <div v-if="!users || users.length === 0" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="UsersIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p class="mb-3">No hay usuarios para mostrar.</p>
              </div>

              <!-- Lista móvil -->
              <div v-else-if="isMobile()" class="space-y-4">
                <div v-for="user in filteredUsers" :key="user.id" 
                     class="bg-white dark:bg-slate-800 rounded-xl shadow p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700 cursor-pointer"
                     @mouseenter="(e) => onMouseEnter(e, getUserTooltipData(user))"
                     @mouseleave="onMouseLeave"
                     @touchstart="(e) => onTouchStart(e, getUserTooltipData(user))"
                     @touchend="onTouchEnd"
                     @touchmove="onTouchMove">
                  <div class="flex items-center gap-3">
                    <img v-if="user.foto" :src="`/storage/${user.foto}`" class="w-10 h-10 rounded-full object-cover" />
                    <Icon v-else name="UserCircleIcon" class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                    <div>
                      <p class="font-semibold text-gray-900 dark:text-gray-100">{{ user.nombre }} {{ user.apellido }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Código: {{ user.codigo }}</p>
                    </div>
                  </div>
                  <Badge :type="user.is_active ? 'success' : 'danger'">
                    {{ user.is_active ? 'Activo' : 'Inactivo' }}
                  </Badge>
                  <button v-if="permissions['Modificar Usuarios']"
                          @click.stop="selectedUser = user; showEditPerson = true" 
                          class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                    <Icon name="PencilIcon" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Tabla Desktop -->
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Código</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                      <th v-if="permissions['Modificar Usuarios']" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="user in filteredUsers" :key="user.id" 
                        class="hover:bg-gray-50 dark:hover:bg-slate-800 transition cursor-pointer"
                        @mouseenter="(e) => onMouseEnter(e, getUserTooltipData(user))"
                        @mouseleave="onMouseLeave"
                        @touchstart="(e) => onTouchStart(e, getUserTooltipData(user))"
                        @touchend="onTouchEnd"
                        @touchmove="onTouchMove">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-3">
                        <img v-if="user.foto" :src="`/storage/${user.foto}`" class="w-6 h-6 rounded-full object-cover" />
                        <Icon v-else name="UserCircleIcon" class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                        {{ user.nombre }} {{ user.apellido }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ user.codigo }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <Badge :type="user.is_active ? 'success' : 'danger'">
                          {{ user.is_active ? 'Activo' : 'Inactivo' }}
                        </Badge>
                      </td>
                      <td v-if="permissions['Modificar Usuarios']" class="px-6 py-4 text-right">
                        <button @click.stop="selectedUser = user; showEditPerson = true" 
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- --------------- ROLES --------------- -->
            <div v-else-if="showSection === 'roles' && permissions['Ver Roles']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Listado de roles</h4>
                <Button variant="primary" @click="showCreateRol = true">
                  <Icon name="PlusIcon" class="w-5 h-5" /> Nuevo rol
                </Button>
              </div>

              <!-- AÑADIR BUSCADOR -->
              <SearchInput 
                v-model="rolesSearch" 
                placeholder="Buscar roles..." 
              />

              <div v-if="!roles.length" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="IdentificationIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p class="mb-3">Aún no has creado ningún rol.</p>
                <Button variant="primary" @click="showCreateRol = true">Crear el primero</Button>
              </div>

              <!-- Móvil -->
              <div v-else-if="isMobile()" class="space-y-4">
                <div v-for="r in filteredRoles" :key="r.id" class="bg-white dark:bg-slate-800 rounded-xl shadow p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700">
                  <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ r.nombre }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ r.descripcion }}</p>
                  </div>
                  <button v-if="permissions['Modificar Roles']" @click="selectedRol = r; showEditRol = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                    <Icon name="PencilIcon" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Desktop -->
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                      <th v-if="permissions['Modificar Roles']" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="r in filteredRoles" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-slate-800 transition">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ r.nombre }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ r.descripcion }}</td>
                      <td v-if="permissions['Modificar Roles']" class="px-6 py-4 text-right">
                        <button @click="selectedRol = r; showEditRol = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- --------------- PERMISOS --------------- -->
            <div v-else-if="showSection === 'permissions' && permissions['Ver Permisos']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Listado de permisos</h4>
                <Button variant="primary" @click="showCreatePermission = true">
                  <Icon name="PlusIcon" class="w-5 h-5" /> Nuevo permiso
                </Button>
              </div>

              <!-- AÑADIR BUSCADOR -->
              <SearchInput 
                v-model="permissionsSearch" 
                placeholder="Buscar Permisos..." 
              />

              <div v-if="!permissionsList || permissionsList.length === 0" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="KeyIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p class="mb-3">No hay permisos para mostrar.</p>
                <Button v-if="permissions['Crear Permisos']" variant="primary" @click="showCreatePermission = true">
                  Crear el primero
                </Button>
              </div>

              <!-- Móvil -->
              <div v-else-if="isMobile()" class="space-y-4">
                <div v-for="p in filteredPermissions" :key="p.id" class="bg-white dark:bg-slate-800 rounded-xl shadow p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700">
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ p.nombre }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ p.descripcion }}</p>
                    <div class="flex flex-wrap gap-1 mt-2">
                      <span v-for="rol in p.roles" :key="rol.id" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ rol.nombre }}
                      </span>
                    </div>
                  </div>
                  <button v-if="permissions['Modificar Permisos']" @click="selectedPermission = p; showEditPermission = true" class="ml-4 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 flex-shrink-0">
                    <Icon name="PencilIcon" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Desktop -->
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Roles</th>
                      <th v-if="permissions['Modificar Permisos']" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="p in filteredPermissions" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-slate-800 transition">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ p.nombre }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ p.descripcion }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-wrap gap-1">
                          <span v-for="rol in p.roles" :key="rol.id" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ rol.nombre }}
                          </span>
                        </div>
                      </td>
                      <td v-if="permissions['Modificar Permisos']" class="px-6 py-4 text-right">
                        <button @click="selectedPermission = p; showEditPermission = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- =============== PERMISOS POR PERSONA =============== -->
            <div v-else-if="showSection === 'user-permissions' && permissions['Ver Permisos Personales']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Permisos por persona</h4>
              </div>

              <!-- AÑADIR BUSCADOR -->
              <SearchInput 
                v-model="userPermissionsSearch" 
                placeholder="Buscar persona por nombre o código..." 
              />

              <div v-if="!usersWithPermissions || usersWithPermissions.length === 0" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="UserCircleIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p>No hay usuarios para mostrar.</p>
              </div>

              <!-- VERSIÓN MÓVIL -->
              <div v-else-if="isMobile()" class="space-y-3">
                <div v-for="user in filteredUsersWithPermissions" :key="user.id" class="bg-white dark:bg-slate-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
                  <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleUserExpansion(user.id)">
                    <div class="flex items-center gap-3">
                      <img v-if="user.foto" :src="`/storage/${user.foto}`" class="w-10 h-10 rounded-full object-cover" />
                      <Icon v-else name="UserCircleIcon" class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                      <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ user.nombre }} {{ user.apellido }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.codigo }}</p>
                      </div>
                    </div>
                    <Icon :name="expandedUsers[user.id] ? 'ChevronUpIcon' : 'ChevronDownIcon'" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                  </div>
                  
                  <div v-if="expandedUsers[user.id]" class="border-t border-gray-200 dark:border-gray-700 p-4">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Permisos disponibles:</p>
                    <div class="space-y-2">
                      <div v-for="permission in allPermissions" :key="permission.id" class="flex items-center justify-between py-2 px-2 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-900">
                        <div class="flex-1 mr-3">
                          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ permission.nombre }}</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ permission.descripcion }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                          <input type="checkbox" :checked="user.permissions.some(p => p.id === permission.id)"
                            :disabled="(user.permissions.some(p => p.id === permission.id) && !permissions['Desactivar Permisos Personales']) || (!user.permissions.some(p => p.id === permission.id) && !permissions['Activar Permisos Personales'])"
                            @change="togglePermission(user, permission)" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- VERSIÓN DESKTOP -->
              <div v-else class="space-y-3">
                <div v-for="user in filteredUsersWithPermissions" :key="user.id" class="bg-white dark:bg-slate-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
                  <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleUserExpansion(user.id)">
                    <div class="flex items-center gap-4">
                      <img v-if="user.foto" :src="`/storage/${user.foto}`" class="w-10 h-10 rounded-full object-cover" />
                      <Icon v-else name="UserCircleIcon" class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                      <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ user.nombre }} {{ user.apellido }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Código: {{ user.codigo }}</p>
                      </div>
                    </div>
                    <Icon :name="expandedUsers[user.id] ? 'ChevronUpIcon' : 'ChevronDownIcon'" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                  </div>
                  
                  <div v-if="expandedUsers[user.id]" class="border-t border-gray-200 dark:border-gray-700 p-4">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Permisos disponibles:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                      <div v-for="permission in allPermissions" :key="permission.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-900 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                        <div class="flex-1 mr-3">
                          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ permission.nombre }}</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ permission.descripcion }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                          <input type="checkbox" :checked="user.permissions.some(p => p.id === permission.id)"
                            :disabled="(user.permissions.some(p => p.id === permission.id) && !permissions['Desactivar Permisos Personales']) || (!user.permissions.some(p => p.id === permission.id) && !permissions['Activar Permisos Personales'])"
                            @change="togglePermission(user, permission)" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- --------------- SUCURSALES --------------- -->
            <div v-else-if="showSection === 'branches' && permissions['Ver Sucursales']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Listado de sucursales</h4>
                <Button v-if="permissions['Crear Sucursales']" variant="primary" @click="showCreateBranch = true">
                  <Icon name="PlusIcon" class="w-5 h-5" /> Nueva sucursal
                </Button>
              </div>

              <!-- AÑADIR BUSCADOR -->
              <SearchInput 
                v-model="branchesSearch" 
                placeholder="Buscar sucursales..." 
              />

              <div v-if="!branches.length" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="BuildingStorefrontIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p class="mb-3">Aún no has creado ninguna sucursal.</p>
                <Button v-if="permissions['Crear Sucursales']" variant="primary" @click="showCreateBranch = true">Crear la primera</Button>
              </div>

              <!-- Móvil -->
              <div v-else-if="isMobile()" class="space-y-4">
                <div v-for="b in filteredBranches" :key="b.id" class="bg-white dark:bg-slate-800 rounded-xl shadow p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700">
                  <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ b.nombre }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ b.departamento }} / {{ b.provincia }} / {{ b.localidad }}</p>
                  </div>
                  <button v-if="permissions['Modificar Sucursales']" @click="selectedBranch = b; showEditBranch = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                    <Icon name="PencilIcon" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Desktop -->
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Departamento</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Provincia</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Localidad</th>
                      <th v-if="permissions['Modificar Sucursales']" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="b in filteredBranches" :key="b.id" class="hover:bg-gray-50 dark:hover:bg-slate-800 transition">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ b.nombre }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ b.departamento }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ b.provincia }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ b.localidad }}</td>
                      <td v-if="permissions['Modificar Sucursales']" class="px-6 py-4 text-right flex gap-2">
                        <button @click="selectedBranch = b; showEditBranch = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- --------------- GRUPOS --------------- -->
            <div v-else-if="showSection === 'groups' && permissions['Ver Grupos']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Listado de grupos</h4>
                <Button v-if="permissions['Crear Grupos']" variant="primary" @click="showCreateGroup = true">
                  <Icon name="PlusIcon" class="w-5 h-5" /> Nuevo grupo
                </Button>
              </div>

              <!-- AÑADIR BUSCADOR -->
              <SearchInput 
                v-model="groupsSearch" 
                placeholder="Buscar grupos..." 
              />

              <div v-if="!groups.length" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="UserGroupIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p class="mb-3">Aún no has creado ningún grupo.</p>
                <Button v-if="permissions['Crear Grupos']" variant="primary" @click="showCreateGroup = true">Crear el primero</Button>
              </div>

              <!-- Móvil -->
              <div v-else-if="isMobile()" class="space-y-4">
                <div v-for="g in filteredGroups" :key="g.id" class="bg-white dark:bg-slate-800 rounded-xl shadow p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700">
                  <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ g.nombre }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ g.descripcion }}</p>
                  </div>
                  <button v-if="permissions['Modificar Grupos']" @click="selectedGroup = g; showEditGroup = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                    <Icon name="PencilIcon" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Desktop -->
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                      <th v-if="permissions['Modificar Grupos']" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="g in filteredGroups" :key="g.id" class="hover:bg-gray-50 dark:hover:bg-slate-800 transition">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ g.nombre }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ g.descripcion }}</td>
                      <td v-if="permissions['Modificar Grupos']" class="px-6 py-4 text-right">
                        <button @click="selectedGroup = g; showEditGroup = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- --------------- SECCIONES --------------- -->
            <div v-else-if="showSection === 'sections' && permissions['Ver Secciones']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Listado de secciones</h4>
                <Button v-if="permissions['Crear Secciones']" variant="primary" @click="showCreateSection = true">
                  <Icon name="PlusIcon" class="w-5 h-5" /> Nueva sección
                </Button>
              </div>

              <!-- AÑADIR BUSCADOR -->
              <SearchInput 
                v-model="sectionsSearch" 
                placeholder="Buscar secciones..." 
              />

              <div v-if="!sections.length" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="QueueListIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p class="mb-3">Aún no has creado ninguna sección.</p>
                <Button v-if="permissions['Crear Secciones']" variant="primary" @click="showCreateSection = true">Crear la primera</Button>
              </div>

              <!-- Móvil -->
              <div v-else-if="isMobile()" class="space-y-4">
                <div v-for="s in filteredSections" :key="s.id" class="bg-white dark:bg-slate-800 rounded-xl shadow p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700">
                  <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ s.nombre }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ s.descripcion }}</p>
                  </div>
                  <button v-if="permissions['Modificar Secciones']" @click="selectedSection = s; showEditSection = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                    <Icon name="PencilIcon" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Desktop -->
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                      <th v-if="permissions['Modificar Secciones']" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="s in filteredSections" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-slate-800 transition">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ s.nombre }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ s.descripcion }}</td>
                      <td v-if="permissions['Modificar Secciones']" class="px-6 py-4 text-right">
                        <button @click="selectedSection = s; showEditSection = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- --------------- PERMISOS DE TRABAJO --------------- -->
            <div v-else-if="showSection === 'authorizations' && permissions['Ver Permisos de Trabajo']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Permisos de trabajo</h4>
                <Button v-if="permissions['Crear Permisos de Trabajo']" variant="primary" @click="showCreateAuthorization = true">
                  <Icon name="PlusIcon" class="w-5 h-5" /> Nuevo permiso
                </Button>
              </div>

              <SearchInput v-model="authorizationsSearch" placeholder="Buscar permisos de trabajo..." />

              <div v-if="!authorizations.length" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="ClipboardDocumentCheckIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p class="mb-3">Aún no has creado ningún permiso de trabajo.</p>
                <Button v-if="permissions['Crear Permisos de Trabajo']" variant="primary" @click="showCreateAuthorization = true">Crear el primero</Button>
              </div>

              <!-- Móvil -->
              <div v-else-if="isMobile()" class="space-y-4">
                <div v-for="a in filteredAuthorizations" :key="a.id" class="bg-white dark:bg-slate-800 rounded-xl shadow p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700">
                  <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ a.nombre }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ a.descripcion }}</p>
                  </div>
                  <button v-if="permissions['Modificar Permisos de Trabajo']" @click="selectedAuthorization = a; showEditAuthorization = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                    <Icon name="PencilIcon" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Desktop -->
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                      <th v-if="permissions['Modificar Permisos de Trabajo']" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="a in filteredAuthorizations" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-slate-800 transition">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ a.nombre }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ a.descripcion }}</td>
                      <td v-if="permissions['Modificar Permisos de Trabajo']" class="px-6 py-4 text-right">
                        <button @click="selectedAuthorization = a; showEditAuthorization = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- --------------- TURNOS --------------- -->
            <div v-else-if="showSection === 'shifts' && permissions['Ver Turnos']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Listado de turnos</h4>
                <Button v-if="permissions['Crear Turnos']" variant="primary" @click="showCreateShift = true">
                  <Icon name="PlusIcon" class="w-5 h-5" /> Nuevo turno
                </Button>
              </div>

              <!-- AÑADIR BUSCADOR -->
              <SearchInput 
                v-model="shiftsSearch" 
                placeholder="Buscar turnos..." 
              />

              <div v-if="!shifts || shifts.length === 0" class="text-center py-10 text-gray-500 dark:text-gray-400">
                <Icon name="CalendarDaysIcon" class="w-12 h-12 mx-auto mb-2 opacity-40" />
                <p class="mb-3">No hay turnos para mostrar.</p>
                <Button v-if="permissions['Crear Turnos']" variant="primary" @click="showCreateShift = true">Crear el primero</Button>
              </div>

              <!-- Móvil: Lista de tarjetas -->
              <div v-else-if="isMobile()" class="space-y-4">
                <div v-for="s in filteredShifts" :key="s.id" 
                     class="bg-white dark:bg-slate-800 rounded-xl shadow p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700 cursor-pointer"
                     @mouseenter="(e) => onMouseEnter(e, getShiftTooltipData(s))"
                     @mouseleave="onMouseLeave"
                     @touchstart="(e) => onTouchStart(e, getShiftTooltipData(s))"
                     @touchend="onTouchEnd"
                     @touchmove="onTouchMove">
                  <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ s.nombre }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ s.desde }} al {{ s.hasta }} · {{ s.schedules.length }} horario(s)</p>
                  </div>
                  <button v-if="permissions['Modificar Turnos']" @click.stop="selectedShift = s; showEditShift = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                    <Icon name="PencilIcon" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Desktop: Tabla -->
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Desde</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hasta</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Horarios</th>
                      <th v-if="permissions['Modificar Turnos']" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="s in filteredShifts" :key="s.id" 
                        class="hover:bg-gray-50 dark:hover:bg-slate-800 transition cursor-pointer"
                        @mouseenter="(e) => onMouseEnter(e, getShiftTooltipData(s))"
                        @mouseleave="onMouseLeave"
                        @touchstart="(e) => onTouchStart(e, getShiftTooltipData(s))"
                        @touchend="onTouchEnd"
                        @touchmove="onTouchMove">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ s.nombre }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ s.desde }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ s.hasta }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ s.schedules.length }} horario(s)</td>
                      <td v-if="permissions['Modificar Turnos']" class="px-6 py-4 text-right">
                        <button @click.stop="selectedShift = s; showEditShift = true" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                          <Icon name="PencilIcon" class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- --------------- FERIADOS --------------- -->
            <div v-else-if="showSection === 'holidays' && permissions['Ver Feriados']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 ml-2">Calendario de Feriados</h4>
              </div>

              <HolidayCalendar :branches="branches" :permissions="permissions" :locations="locations" />
            </div>

            <!-- --------------- REPORTES --------------- -->
            <div v-else-if="showSection === 'reports' && permissions['Ver Reportes']">
              <div v-if="isMobile()" class="flex items-center gap-2 mb-4">
                <button @click="showSection = null" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                  <Icon name="ArrowLeftIcon" class="w-5 h-5" /> Volver
                </button>
              </div>

              <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 ml-2">Generar Reportes</h4>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Tarjetas para cada sección -->
                <div 
                  v-for="report in availableReports" 
                  :key="report.key"
                  @click="openReportModal(report.key)"
                  class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6 cursor-pointer hover:shadow-lg transition-all hover:scale-105 border border-gray-100 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600"
                >
                  <div class="flex items-center gap-4">
                    <Icon :name="report.icon" class="w-10 h-10 text-blue-600 dark:text-blue-400" />
                    <div>
                      <h5 class="font-semibold text-gray-900 dark:text-gray-100">{{ report.title }}</h5>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ report.subtitle }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </main>
        </div>
      </div>
    </div>

    <!-- ==================== MODALES ==================== -->
    <CreatePersonModal v-if="permissions['Crear Usuarios']" :show="showCreatePerson" :roles="roles" :branches="branches" :groups="groups" :sections="sections" @close="showCreatePerson = false" />
    <EditPersonModal v-if="permissions['Modificar Usuarios']" :show="showEditPerson" :roles="roles" :user="selectedUser" :branches="branches" :groups="groups" :sections="sections" @close="showEditPerson = false" />

    <CreateRolModal v-if="permissions['Crear Roles']" :show="showCreateRol" @close="showCreateRol = false" />
    <EditRolModal v-if="permissions['Modificar Roles']" :show="showEditRol" :rol="selectedRol" @close="showEditRol = false" />

    <CreatePermissionModal v-if="permissions['Crear Permisos']" :show="showCreatePermission" :roles="roles" @close="showCreatePermission = false" />
    <EditPermissionModal v-if="permissions['Modificar Permisos']" :show="showEditPermission" :permission="selectedPermission" :roles="roles" @close="showEditPermission = false" />

    <CreateBranchModal v-if="permissions['Crear Sucursales']" :show="showCreateBranch" @close="showCreateBranch = false" />
    <EditBranchModal v-if="permissions['Modificar Sucursales']" :show="showEditBranch" :branch="selectedBranch" @close="showEditBranch = false" />

    <CreateGroupModal v-if="permissions['Crear Grupos']" :show="showCreateGroup" @close="showCreateGroup = false" />
    <EditGroupModal v-if="permissions['Modificar Grupos']" :show="showEditGroup" :group="selectedGroup" @close="showEditGroup = false" />

    <CreateSectionModal v-if="permissions['Crear Secciones']" :show="showCreateSection" @close="showCreateSection = false" />
    <EditSectionModal v-if="permissions['Modificar Secciones']" :show="showEditSection" :section="selectedSection" @close="showEditSection = false" />

    <CreateAuthorizationModal v-if="permissions['Crear Permisos de Trabajo']" :show="showCreateAuthorization" @close="showCreateAuthorization = false" />
    <EditAuthorizationModal   v-if="permissions['Modificar Permisos de Trabajo']" :show="showEditAuthorization" :authorization="selectedAuthorization" @close="showEditAuthorization = false" />

    <CreateShiftModal v-if="permissions['Crear Turnos']" :show="showCreateShift" :users="users" :branches="branches" :groups="groups" :sections="sections" :roles="roles" @close="showCreateShift = false" />
    <EditShiftModal v-if="permissions['Modificar Turnos']" :show="showEditShift" :shift="selectedShift" :users="users" :branches="branches" :groups="groups" :sections="sections" :roles="roles" @close="showEditShift = false" />

    <!-- Modal de Generar Reporte -->
    <GenerateReportModal :show="showReportModal" :report-section="selectedReport" @close="showReportModal = false; selectedReport = ''" />

    <!-- ==================== TOOLTIP ==================== -->
    <Tooltip ref="tooltipRef" :show="show" @close="hideTooltip">
      <div v-if="tooltipData?.type === 'user'" class="space-y-2">
        <div class="flex items-center gap-3 pb-2 border-b border-gray-200 dark:border-gray-700">
          <img v-if="tooltipData.data.foto" :src="`/storage/${tooltipData.data.foto}`" class="w-12 h-12 rounded-full object-cover">
          <Icon v-else name="UserCircleIcon" class="w-12 h-12 text-gray-400 dark:text-gray-500" />
          <div>
            <h4 class="font-bold text-gray-900 dark:text-gray-100">{{ tooltipData.data.nombre }} {{ tooltipData.data.apellido }}</h4>
            <p class="text-sm text-gray-500">Código: {{ tooltipData.data.codigo }}</p>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div>
            <span class="text-gray-500 dark:text-gray-400">Estado:</span>
            <Badge :type="tooltipData.data.is_active ? 'success' : 'danger'" class="ml-2">
              {{ tooltipData.data.is_active ? 'Activo' : 'Inactivo' }}
            </Badge>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Rol:</span>
            <span class="ml-2 font-medium dark:text-gray-100">{{ tooltipData.data.rolName }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Sucursal:</span>
            <span class="ml-2 dark:text-gray-100">{{ tooltipData.data.branchName }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Grupo:</span>
            <span class="ml-2 dark:text-gray-100">{{ tooltipData.data.groupName }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Sección:</span>
            <span class="ml-2 dark:text-gray-100">{{ tooltipData.data.sectionName }}</span>
          </div>
        </div>
      </div>

      <div v-if="tooltipData?.type === 'shift'" class="space-y-3">
        <h4 class="font-bold text-gray-900 dark:text-gray-100 pb-2 border-b border-gray-200 dark:border-gray-700">
          {{ tooltipData.data.nombre }}
        </h4>
        
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div>
            <span class="text-gray-500 dark:text-gray-400">Jornada:</span>
            <span class="ml-2 font-medium dark:text-gray-100">{{ tooltipData.data.jornada }} horas</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Semanal:</span>
            <span class="ml-2 font-medium dark:text-gray-100">{{ tooltipData.data.semanal }} horas</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Desde:</span>
            <span class="ml-2 dark:text-gray-100">{{ tooltipData.data.desde }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Hasta:</span>
            <span class="ml-2 dark:text-gray-100">{{ tooltipData.data.hasta }}</span>
          </div>
        </div>

        <div v-if="tooltipData.data.schedules?.length" class="border-t pt-2">
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horarios:</p>
          <div class="space-y-1">
            <div v-for="schedule in tooltipData.data.schedules" :key="schedule.id" class="text-xs text-gray-600 dark:text-gray-400">
              {{ schedule.hora_inicio }} - {{ schedule.hora_fin }} ({{ schedule.dias.join(', ') }})
              <span v-if="schedule.incluye_feriados" class="text-orange-500">• Incluye feriados</span>
            </div>
          </div>
        </div>

        <div class="border-t pt-2">
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asignado a:</p>
          <div class="flex flex-wrap gap-1">
            <span v-if="tooltipData.data.hasUsers" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Usuarios</span>
            <span v-if="tooltipData.data.hasBranches" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Sucursales</span>
            <span v-if="tooltipData.data.hasGroups" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Grupos</span>
            <span v-if="tooltipData.data.hasSections" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Secciones</span>
            <span v-if="tooltipData.data.hasRoles" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Roles</span>
          </div>
        </div>
      </div>
    </Tooltip>
  </AuthenticatedLayout>
</template>