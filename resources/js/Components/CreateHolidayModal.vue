<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import InputError from '@/Components/InputError.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    show: Boolean,
    date: String,
    branches: Array,
    locations: Object,
});

const emit = defineEmits(['close', 'created']);

const form = useForm({
    nombre: '',
    fecha: '',
    departamento: null,
    provincia: null,
    localidad: null,
    branch_id: null,
});

// ✅ Actualizar la fecha cuando cambia el prop
watch(() => props.date, (newDate) => {
    if (newDate) {
        form.fecha = newDate;
    }
}, { immediate: true });

// ✅ Inicializar fecha cuando se abre el modal
watch(() => props.show, (isOpen) => {
    if (isOpen && props.date) {
        form.fecha = props.date;
    }
});

// Transformar ubicaciones en arrays de opciones para SearchSelect
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

// Resetear selects dependientes
watch(() => form.departamento, () => {
    form.provincia = null;
    form.localidad = null;
    form.branch_id = null;
});

watch(() => form.provincia, () => {
    form.localidad = null;
    form.branch_id = null;
});

watch(() => form.localidad, (value) => {
    form.branch_id = value;
});

const submit = () => {
    console.log('📤 Enviando feriado:', form.data());
    
    form.post(route('settings.holidays.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('✅ Feriado creado exitosamente:', page.props.holiday);
            const holiday = page.props.holiday;
            if (holiday) {
                emit('created', holiday);
            }
            close();
        },
        onError: (errors) => {
            console.error('❌ Errores al crear feriado:', errors);
        }
    });
};

const close = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <Modal :show="show" :closeable="true" @close="close" max-width="md">
        <form @submit.prevent="submit" class="p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <Icon name="PlusIcon" class="w-6 h-6 text-indigo-600" />
                Crear Feriado
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

            <!-- Fecha (mostrar pero ocultar input) -->
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
                    :clearable="true"
                />
            </div>

            <!-- Branch ID (oculto) -->
            <input type="hidden" v-model="form.branch_id" />

            <div class="flex items-center justify-end gap-2 mt-6">
                <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
                <PrimaryButton :disabled="form.processing">
                    <Icon v-if="form.processing" name="ArrowPathIcon" class="w-5 h-5 animate-spin" />
                    <span v-else>Crear Feriado</span>
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>