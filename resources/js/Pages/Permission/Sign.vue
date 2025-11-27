<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    permission: Object,
    isHrManager: Boolean,
});

const canvas = ref(null);
const isDrawing = ref(false);
const form = useForm({
    firma: '',
});

const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

const startDrawing = (e) => {
    isDrawing.value = true;
    const rect = canvas.value.getBoundingClientRect();
    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
    const ctx = canvas.value.getContext('2d');
    ctx.beginPath();
    ctx.moveTo(x, y);
};

const draw = (e) => {
    if (!isDrawing.value) return;
    const rect = canvas.value.getBoundingClientRect();
    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
    const ctx = canvas.value.getContext('2d');
    ctx.lineTo(x, y);
    ctx.stroke();
};

const stopDrawing = () => {
    isDrawing.value = false;
};

const clearCanvas = () => {
    const ctx = canvas.value.getContext('2d');
    ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);
};

const saveSignature = () => {
    const dataURL = canvas.value.toDataURL('image/png');
    form.firma = dataURL;
    form.post(route('permissions.store-sign', props.permission.id));
};
</script>

<template>
    <Head title="Firmar Permiso" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                Firmar Permiso
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 shadow-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">
                        Solicitante: {{ permission.user.nombre }} {{ permission.user.apellido }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                        Tipo: {{ permission.tipo === 'dias' ? 'Días' : 'Horas' }} | Motivo: {{ permission.motivo }}
                    </p>

                    <div v-if="isMobile" class="mb-4 text-sm text-red-600 dark:text-red-400">
                        Por favor, gira tu dispositivo horizontalmente para firmar.
                    </div>

                    <canvas
                        ref="canvas"
                        width="600"
                        height="200"
                        class="border border-gray-300 dark:border-gray-600 rounded w-full touch-none"
                        @mousedown="startDrawing"
                        @mousemove="draw"
                        @mouseup="stopDrawing"
                        @mouseleave="stopDrawing"
                        @touchstart="startDrawing"
                        @touchmove="draw"
                        @touchend="stopDrawing"
                    ></canvas>

                    <div class="mt-4 flex gap-2">
                        <PrimaryButton @click="saveSignature">Guardar Firma</PrimaryButton>
                        <PrimaryButton @click="clearCanvas" class="bg-gray-500 hover:bg-gray-600">Borrar</PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>