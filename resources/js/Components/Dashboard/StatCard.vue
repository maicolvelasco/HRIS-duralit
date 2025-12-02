<script setup>
import { computed } from 'vue';
import { 
    UsersIcon, 
    ClockIcon, 
    DocumentTextIcon, 
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    title: String,
    value: [String, Number],
    total: [String, Number],
    trend: String,
    icon: String,
    color: {
        type: String,
        default: 'blue'
    }
});

const iconComponent = computed(() => {
    const icons = {
        UsersIcon,
        ClockIcon,
        DocumentTextIcon,
        CurrencyDollarIcon,
        ArrowTrendingUpIcon,
        UserGroupIcon
    };
    return icons[props.icon] || UsersIcon;
});

const colorClasses = computed(() => {
    const colors = {
        blue: 'bg-blue-500',
        green: 'bg-green-500',
        yellow: 'bg-yellow-500',
        purple: 'bg-purple-500',
        orange: 'bg-orange-500',
        red: 'bg-red-500'
    };
    return colors[props.color] || colors.blue;
});
</script>

<template>
    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <component :is="iconComponent" 
                              class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            {{ title }}
                        </dt>
                        <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ value }}
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400" v-if="total || trend">
                    <component :is="iconComponent" class="flex-shrink-0 h-4 w-4" />
                    <span class="ml-2">
                        <span v-if="total">Total: {{ total }}</span>
                        <span v-if="trend" class="text-gray-600 dark:text-gray-300">{{ trend }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>