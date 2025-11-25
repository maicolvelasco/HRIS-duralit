<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: '¿Estás seguro?'
    },
    message: {
        type: String,
        default: 'Esta acción no se puede deshacer.'
    },
    confirmText: {
        type: String,
        default: 'Confirmar'
    },
    cancelText: {
        type: String,
        default: 'Cancelar'
    },
    type: {
        type: String,
        default: 'danger',
        validator: (value) => ['danger', 'warning', 'info', 'success'].includes(value)
    },
    processing: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'confirm', 'cancel']);

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('cancel');
    emit('close');
};

const getIcon = () => {
    switch (props.type) {
        case 'danger':
            return 'ExclamationTriangleIcon';
        case 'warning':
            return 'ExclamationCircleIcon';
        case 'info':
            return 'InformationCircleIcon';
        case 'success':
            return 'CheckCircleIcon';
        default:
            return 'ExclamationTriangleIcon';
    }
};

const getIconColor = () => {
    switch (props.type) {
        case 'danger':
            return 'text-red-600 dark:text-red-400';
        case 'warning':
            return 'text-yellow-600 dark:text-yellow-400';
        case 'info':
            return 'text-blue-600 dark:text-blue-400';
        case 'success':
            return 'text-green-600 dark:text-green-400';
        default:
            return 'text-red-600 dark:text-red-400';
    }
};

const getIconBackground = () => {
    switch (props.type) {
        case 'danger':
            return 'bg-red-100 dark:bg-red-900/30';
        case 'warning':
            return 'bg-yellow-100 dark:bg-yellow-900/30';
        case 'info':
            return 'bg-blue-100 dark:bg-blue-900/30';
        case 'success':
            return 'bg-green-100 dark:bg-green-900/30';
        default:
            return 'bg-red-100 dark:bg-red-900/30';
    }
};

const getGradient = () => {
    switch (props.type) {
        case 'danger':
            return 'from-red-50 to-transparent dark:from-red-900/10 dark:to-transparent';
        case 'warning':
            return 'from-yellow-50 to-transparent dark:from-yellow-900/10 dark:to-transparent';
        case 'info':
            return 'from-blue-50 to-transparent dark:from-blue-900/10 dark:to-transparent';
        case 'success':
            return 'from-green-50 to-transparent dark:from-green-900/10 dark:to-transparent';
        default:
            return 'from-red-50 to-transparent dark:from-red-900/10 dark:to-transparent';
    }
};
</script>

<template>
    <Modal :show="show" :closeable="!processing" @close="handleCancel" max-width="sm">
        <div class="relative overflow-hidden bg-white dark:bg-slate-800">
            <!-- Gradiente decorativo superior -->
            <div 
                :class="['absolute inset-x-0 top-0 h-32 bg-gradient-to-b', getGradient()]"
                class="opacity-50"
            ></div>

            <div class="relative p-6">
                <!-- Icono con animación -->
                <div class="flex items-center justify-center mb-6">
                    <div 
                        :class="['w-20 h-20 rounded-full flex items-center justify-center shadow-lg', getIconBackground()]"
                        class="animate-scale-in ring-8 ring-opacity-10"
                        :style="{ 
                            '--tw-ring-color': props.type === 'danger' ? 'rgb(220 38 38)' : 
                                               props.type === 'warning' ? 'rgb(234 179 8)' : 
                                               props.type === 'info' ? 'rgb(37 99 235)' : 
                                               'rgb(22 163 74)' 
                        }"
                    >
                        <Icon 
                            :name="getIcon()" 
                            :class="['w-11 h-11 animate-pulse-subtle', getIconColor()]" 
                        />
                    </div>
                </div>

                <!-- Título con animación -->
                <h3 
                    class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center mb-3 animate-fade-in-up"
                    style="animation-delay: 0.1s"
                >
                    {{ title }}
                </h3>

                <!-- Mensaje con animación -->
                <p 
                    class="text-base text-gray-600 dark:text-gray-400 text-center mb-8 leading-relaxed animate-fade-in-up"
                    style="animation-delay: 0.2s"
                >
                    {{ message }}
                </p>

                <!-- Botones con animación -->
                <div 
                    class="flex flex-col sm:flex-row gap-3 justify-center animate-fade-in-up"
                    style="animation-delay: 0.3s"
                >
                    <SecondaryButton 
                        type="button" 
                        @click="handleCancel"
                        :disabled="processing"
                        class="min-w-[140px] justify-center transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        {{ cancelText }}
                    </SecondaryButton>

                    <DangerButton 
                        v-if="type === 'danger'"
                        type="button" 
                        @click="handleConfirm"
                        :disabled="processing"
                        class="min-w-[140px] justify-center transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        <Icon 
                            v-if="processing" 
                            name="ArrowPathIcon" 
                            class="w-5 h-5 animate-spin mr-2" 
                        />
                        {{ confirmText }}
                    </DangerButton>

                    <PrimaryButton 
                        v-else
                        type="button" 
                        @click="handleConfirm"
                        :disabled="processing"
                        class="min-w-[140px] justify-center transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        <Icon 
                            v-if="processing" 
                            name="ArrowPathIcon" 
                            class="w-5 h-5 animate-spin mr-2" 
                        />
                        {{ confirmText }}
                    </PrimaryButton>
                </div>
            </div>

            <!-- Borde inferior decorativo -->
            <div 
                class="h-1.5 w-full"
                :class="{
                    'bg-gradient-to-r from-red-500 via-red-600 to-red-500': type === 'danger',
                    'bg-gradient-to-r from-yellow-500 via-yellow-600 to-yellow-500': type === 'warning',
                    'bg-gradient-to-r from-blue-500 via-blue-600 to-blue-500': type === 'info',
                    'bg-gradient-to-r from-green-500 via-green-600 to-green-500': type === 'success'
                }"
            ></div>
        </div>
    </Modal>
</template>

<style scoped>
@keyframes scale-in {
    0% {
        transform: scale(0.5);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse-subtle {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(0.95);
    }
}

.animate-scale-in {
    animation: scale-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
}

.animate-pulse-subtle {
    animation: pulse-subtle 2s ease-in-out infinite;
}
</style>