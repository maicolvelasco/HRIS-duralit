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
    payroll: {
        type: Object,
        default: () => ({})
    },
    users: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

// PROTECCIÓN: Asegura datos seguros y formato correcto
const safeUsers = computed(() => {
    const users = Array.isArray(props.users) ? props.users : [];
    return users.map(user => ({
        id: user.id,
        nombre: `${user.nombre} ${user.apellido} (${user.codigo})`
    }));
});

const safePayroll = computed(() => props.payroll?.id ? props.payroll : {});

const form = useForm({
    user_id: null,
    periodo: '',
    fecha_inicio: '',
    fecha_fin: '',
});

// Cargar datos SOLO cuando el modal está visible y payroll existe
watch([() => safePayroll.value, () => props.show], ([payroll, isShown]) => {
    if (isShown && payroll.id) {
        console.log('📝 Editando nómina ID:', payroll.id);
        form.user_id = payroll.user_id;
        form.periodo = payroll.periodo || '';
        form.fecha_inicio = payroll.fecha_inicio || '';
        form.fecha_fin = payroll.fecha_fin || '';
        
        nextTick(() => {
            form.clearErrors();
        });
    }
}, { immediate: true });

function submit() {
    form.put(route('payroll.update', safePayroll.value.id), {
        onSuccess: () => emit('close'),
        onError: (errors) => {
            console.error('❌ Errores de actualización:', errors);
        },
        preserveScroll: true,
    });
}

function close() {
    form.reset();
    form.clearErrors();
    emit('close');
}
</script>

<template>
  <Modal :show="show" @close="close">
    <form @submit.prevent="submit" class="flex flex-col max-h-screen">
      <!-- Header -->
      <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <Icon name="PencilIcon" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
          Editar Nómina
        </h3>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- 🔴 CLAVE: SearchSelect con formato {id, nombre} -->
        <SearchSelect
          label="Empleado"
          :options="safeUsers"
          v-model="form.user_id"
          :error="form.errors.user_id"
          placeholder="Buscar empleado..."
          :clearable="false"
          disabled
        />

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
        <Button variant="primary" :disabled="form.processing">
          <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
          <span v-else>Actualizar Nómina</span>
        </Button>
      </div>
    </form>
  </Modal>
</template>