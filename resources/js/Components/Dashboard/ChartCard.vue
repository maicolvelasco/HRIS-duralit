<script setup>
import { ref, onMounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    title: String,
    type: String,
    data: Object,
    class: String
});

const canvasRef = ref(null);
let chart = null;

onMounted(() => {
    const ctx = canvasRef.value.getContext('2d');
    
    const config = {
        type: props.type,
        data: props.data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#64748b'
                    }
                }
            },
            scales: props.type !== 'doughnut' ? {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#64748b'
                    },
                    grid: {
                        color: document.documentElement.classList.contains('dark') ? '#334155' : '#f1f5f9'
                    }
                },
                x: {
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#64748b'
                    },
                    grid: {
                        color: document.documentElement.classList.contains('dark') ? '#334155' : '#f1f5f9'
                    }
                }
            } : {}
        }
    };

    chart = new Chart(ctx, config);
});
</script>

<template>
    <div :class="props.class" 
         class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ title }}
            </h3>
        </div>
        <div class="p-6 h-80">
            <canvas ref="canvasRef"></canvas>
        </div>
    </div>
</template>