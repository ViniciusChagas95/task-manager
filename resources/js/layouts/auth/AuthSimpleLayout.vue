<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 p-4 transition-colors duration-300">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">
            
            <!-- HEADER DO CARD -->
            <div class="p-8 text-center border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <Link :href="home()" class="inline-flex items-center gap-2 group">
                    <span class="text-2xl font-bold tracking-tight text-blue-600 dark:text-blue-400 group-hover:opacity-80 transition">
                        Task Manager
                    </span>
                </Link>
                <h2 v-if="title" class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ title }}
                </h2>
                <p v-if="description" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ description }}
                </p>
            </div>

            <!-- CONTEÚDO DO CARD (Formulário) -->
            <div class="p-8">
                <slot />
            </div>

        </div>
        
        <!-- FOOTER -->
        <div class="mt-8 text-center text-xs text-gray-400 dark:text-gray-500">
            &copy; {{ new Date().getFullYear() }} Task Manager
        </div>
    </div>
</template>
