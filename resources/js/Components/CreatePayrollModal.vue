<script setup>
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import Icon from '@/Components/Icon.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, watch, nextTick } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    users: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

// PROTECCIÓN CLAVE: Asegura array y formato correcto para SearchSelect
const safeUsers = computed(() => {
    const users = Array.isArray(props.users) ? props.users : [];
    // Transformamos al formato que espera SearchSelect: {id, nombre}
    return users.map(user => ({
        id: user.id,
        nombre: `${user.nombre} ${user.apellido} (${user.codigo})`
    }));
});

const form = useForm({
    user_id: null,
    periodo: '',
    fecha_inicio: '',
    fecha_fin: '',
});

// Limpia el formulario solo cuando se cierra el modal
watch(() => props.show, (isShown) => {
    if (!isShown) {
        form.reset();
        form.clearErrors();
    } else {
        // Asegura que el DOM esté listo antes de operar
        nextTick(() => {
            console.log('🚀 Modal abierto - Users disponibles:', safeUsers.value.length);
        });
    }
});

function submit() {
    // VALIDACIÓN EXTRA ANTES DE ENVIAR
    if (!form.user_id) {
        form.setError('user_id', 'Debe seleccionar un empleado');
        return;
    }
    
    form.post(route('payroll.store'), {
        onSuccess: () => emit('close'),
        onError: (errors) => {
            console.error('❌ Errores del servidor:', errors);
        },
        preserveScroll: true,
    });
}

function close() {
    emit('close');
}
</script>

<template>
  <Modal :show="show" @close="close">
    <form @submit.prevent="submit" class="flex flex-col max-h-screen">
      <!-- Header -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="PlusIcon" class="w-6 h-6 text-green-600 dark:text-green-400" />
          Crear Nueva Nómina
        </h3>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">

        <!-- MENSAJE SI NO HAY USUARIOS -->
        <div v-if="safeUsers.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
          <p class="text-sm text-yellow-800 dark:text-yellow-200 font-medium">
            ⚠️ No hay empleados disponibles. Verifica que los empleados tengan salario base asignado.
          </p>
        </div>

        <!-- FORMULARIO SOLO SI HAY USUARIOS -->
        <template v-else>
          <!-- 🔴 CLAVE: Pasamos options con formato {id, nombre} -->
          <SearchSelect
            label="Empleado *"
            :options="safeUsers"
            v-model="form.user_id"
            :error="form.errors.user_id"
            placeholder="Buscar empleado..."
            clearable
          />
          
          <!-- Feedback visual de selección -->
          <p v-if="form.user_id" class="text-xs text-green-600 dark:text-green-400 mt-1">
            ✓ Seleccionado: {{ safeUsers.find(u => u.id === form.user_id)?.nombre }}
          </p>
        </template>

        <!-- Período -->
        <div>
          <InputLabel value="Período (Ej: 2025-11-Q1) *" />
          <TextInput
            v-model="form.periodo"
            type="text"
            class="w-full mt-1"
            placeholder="2025-11-Q1"
            required
          />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Formato: Año-Mes-Quincena (Q1=Primera quincena, Q2=Segunda quincena)
          </p>
          <InputError :message="form.errors.periodo" />
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel value="Fecha Inicio *" />
            <TextInput v-model="form.fecha_inicio" type="date" class="w-full mt-1" required />
            <InputError :message="form.errors.fecha_inicio" />
          </div>
          <div>
            <InputLabel value="Fecha Fin *" />
            <TextInput v-model="form.fecha_fin" type="date" class="w-full mt-1" required />
            <InputError :message="form.errors.fecha_fin" />
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="sticky bottom-0 z-10 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-end gap-2">
        <Button type="button" variant="secondary" @click="close">Cancelar</Button>
        <Button variant="primary" :disabled="form.processing || safeUsers.length === 0">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Crear Nómina</span>
        </Button>
      </div>
    </form>
  </Modal>
</template>