<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    show: Boolean,
    holiday: Object,
    branches: Array,
    locations: Object,
    permissions: Object,
});

const emit = defineEmits(['close', 'updated', 'deleted']);

const form = useForm({
    id: '',
    nombre: '',
    fecha: '',
    departamento: null,
    provincia: null,
    localidad: null,
    branch_id: null,
});

// Estado para el modal de confirmación
const showDeleteConfirm = ref(false);
const isDeleting = ref(false);

// Flag para evitar reset durante la carga inicial
const isLoading = ref(false);

// Transformar ubicaciones en arrays de opciones
const departmentOptions = computed(() => 
    Object.keys(props.locations).map(dept => ({ id: dept, nombre: dept }))
);

const provinceOptions = computed(() => {
    if (!form.departamento) return [];
    return Object.keys(props.locations[form.departamento] || {}).map(prov => ({ id: prov, nombre: prov }));
});

const localityOptions = computed(() => {
    if (!form.departamento || !form.provincia) return [];
    return (props.locations[form.departamento][form.provincia] || []).map(loc => ({
        id: loc.id,
        nombre: loc.localidad,
    }));
});

// Cargar datos del feriado
const loadHolidayData = async () => {
    if (!props.holiday) return;
    
    isLoading.value = true;
    
    console.log('📥 Cargando datos del feriado:', props.holiday);
    
    // Encontrar la sucursal del feriado
    const branch = props.branches.find(b => b.id === props.holiday.branch_id);
    
    if (!branch) {
        console.error('❌ No se encontró la sucursal:', props.holiday.branch_id);
        isLoading.value = false;
        return;
    }
    
    console.log('🏢 Sucursal encontrada:', branch);
    
    // Asignar valores en el orden correcto
    form.id = props.holiday.id;
    form.nombre = props.holiday.nombre;
    form.fecha = props.holiday.fecha;
    form.branch_id = props.holiday.branch_id;
    
    // Primero asignar departamento
    form.departamento = branch.departamento;
    
    // Esperar a que Vue actualice el DOM y las opciones de provincia
    await nextTick();
    
    // Luego asignar provincia
    form.provincia = branch.provincia;
    
    // Esperar nuevamente para las opciones de localidad
    await nextTick();
    
    // Finalmente asignar localidad (usando el ID de la sucursal)
    form.localidad = String(branch.id);
    
    console.log('✅ Datos cargados:', {
        departamento: form.departamento,
        provincia: form.provincia,
        localidad: form.localidad,
        branch_id: form.branch_id
    });
    
    isLoading.value = false;
};

// Resetear selects dependientes solo si NO está cargando
watch(() => form.departamento, (newVal, oldVal) => {
    if (isLoading.value) return; // No resetear durante la carga
    if (oldVal !== undefined && newVal !== oldVal) {
        form.provincia = null;
        form.localidad = null;
        form.branch_id = null;
    }
});

watch(() => form.provincia, (newVal, oldVal) => {
    if (isLoading.value) return; // No resetear durante la carga
    if (oldVal !== undefined && newVal !== oldVal) {
        form.localidad = null;
        form.branch_id = null;
    }
});

watch(() => form.localidad, (value) => {
    if (value) {
        form.branch_id = value;
    }
});

// ✅ Cargar datos cuando cambia el prop holiday o se abre el modal
watch(() => [props.show, props.holiday], async ([isOpen, newHoliday]) => {
    if (isOpen && newHoliday) {
        await loadHolidayData();
    }
}, { immediate: true, deep: true });

const submit = () => {
    console.log('📤 Actualizando feriado:', form.data());
    
    form.put(route('settings.holidays.update', props.holiday.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('✅ Feriado actualizado exitosamente:', page.props.holiday);
            const holiday = page.props.holiday;
            if (holiday) {
                emit('updated', holiday);
            }
            close();
        },
        onError: (errors) => {
            console.error('❌ Errores al actualizar feriado:', errors);
        }
    });
};

// ✅ Mostrar modal de confirmación
const confirmDelete = () => {
    showDeleteConfirm.value = true;
};

// ✅ Eliminar feriado con confirmación
const deleteHoliday = () => {
    isDeleting.value = true;
    
    form.delete(route('settings.holidays.destroy', props.holiday.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('deleted', props.holiday.id);
            showDeleteConfirm.value = false;
            isDeleting.value = false;
            close();
        },
        onError: (errors) => {
            console.error('❌ Error al eliminar:', errors);
            isDeleting.value = false;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const close = () => {
    form.reset();
    emit('close');
};

// Cargar datos al montar
onMounted(async () => {
    if (props.show && props.holiday) {
        await loadHolidayData();
    }
});
</script>

<template>
    <div>
        <!-- Modal principal de edición -->
        <Modal :show="show" :closeable="true" @close="close" max-width="md">
            <form @submit.prevent="submit" class="p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <Icon name="PencilIcon" class="w-6 h-6 text-indigo-600" />
                    Editar Feriado
                </h3>

                <!-- Nombre -->
                <div class="mb-4">
                    <InputLabel value="Nombre del feriado" />
                    <TextInput 
                        v-model="form.nombre" 
                        class="w-full mt-1" 
                        placeholder="Ej. Día de la Independencia"
                        required 
                    />
                    <InputError :message="form.errors.nombre" />
                </div>

                <!-- Fecha (oculta) -->
                <div class="mb-4 hidden">
                    <InputLabel value="Fecha" />
                    <TextInput 
                        v-model="form.fecha" 
                        type="date"
                        class="w-full mt-1" 
                        readonly 
                        disabled
                    />
                    <InputError :message="form.errors.fecha" />
                </div>

                <!-- Departamento -->
                <div class="mb-4">
                    <SearchSelect
                        label="Departamento"
                        v-model="form.departamento"
                        :options="departmentOptions"
                        placeholder="Buscar departamento..."
                        :error="form.errors.departamento"
                        :clearable="true"
                    />
                </div>

                <!-- Provincia -->
                <div class="mb-4">
                    <SearchSelect
                        label="Provincia"
                        v-model="form.provincia"
                        :options="provinceOptions"
                        placeholder="Buscar provincia..."
                        :error="form.errors.provincia"
                        :disabled="!form.departamento"
                        :clearable="true"
                    />
                </div>

                <!-- Localidad -->
                <div class="mb-6">
                    <SearchSelect
                        label="Localidad / Sucursal"
                        v-model="form.localidad"
                        :options="localityOptions"
                        placeholder="Buscar localidad..."
                        :error="form.errors.localidad"
                        :disabled="!form.provincia"
                        :clearable="true"
                    />
                </div>

                <!-- Branch ID (oculto) -->
                <input type="hidden" v-model="form.branch_id" />

                <!-- Botones -->
                <div class="flex items-center justify-between gap-2 mt-6">
                    <div>
                        <DangerButton 
                            v-if="permissions['Eliminar Feriados']" 
                            type="button" 
                            @click="confirmDelete"
                        >
                            <Icon name="TrashIcon" class="w-5 h-5" />
                            Eliminar
                        </DangerButton>
                    </div>
                    <div class="flex gap-2">
                        <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">
                            <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
                            <span v-else>Guardar cambios</span>
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </Modal>

        <!-- ✅ Modal de confirmación de eliminación -->
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="¿Eliminar feriado?"
            :message="`¿Estás seguro de que deseas eliminar el feriado '${holiday?.nombre || ''}'? Esta acción no se puede deshacer.`"
            confirm-text="Sí, eliminar"
            cancel-text="Cancelar"
            type="danger"
            :processing="isDeleting"
            @confirm="deleteHoliday"
            @cancel="showDeleteConfirm = false"
            @close="showDeleteConfirm = false"
        />
    </div>
</template>