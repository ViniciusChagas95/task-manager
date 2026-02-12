<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

defineProps<{
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Bem-vindo ao Task Manager" />
    
    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 flex flex-col transition-colors duration-300">
        
        <!-- HEADER -->
        <header class="w-full max-w-7xl mx-auto p-6 flex justify-between items-center">
            <div class="text-xl font-bold tracking-tight text-blue-600 dark:text-blue-400">
                Task Manager
            </div>
            
            <nav class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm"
                >
                    Ir para Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition"
                    >
                        Entrar
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition font-medium text-sm dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-400 dark:hover:text-gray-900"
                    >
                        Criar Conta
                    </Link>
                </template>
            </nav>
        </header>

        <!-- HERO SECTION -->
        <main class="flex-grow flex flex-col justify-center items-center text-center px-4">
            <div class="max-w-3xl animate-fade-in-up">
                <h1 class="text-5xl sm:text-6xl font-extrabold mb-6 tracking-tight">
                    Organize suas tarefas, <br />
                    <span class="text-blue-600 dark:text-blue-400">Liberte sua mente.</span>
                </h1>
                
                <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 mb-10 max-w-2xl mx-auto leading-relaxed">
                    A maneira mais simples e eficiente de gerenciar seus projetos e tarefas do dia a dia. 
                    Foque no que realmente importa.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                         v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="px-8 py-3 bg-blue-600 text-white text-lg font-semibold rounded-full hover:bg-blue-700 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1"
                    >
                        Acessar Minhas Tarefas
                    </Link>
                    <template v-else>
                         <Link
                            v-if="canRegister"
                            :href="register()"
                            class="px-8 py-3 bg-blue-600 text-white text-lg font-semibold rounded-full hover:bg-blue-700 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1"
                        >
                            Começar Gratuitamente
                        </Link>
                        <Link
                            :href="login()"
                            class="px-8 py-3 bg-white text-gray-800 text-lg font-semibold rounded-full border border-gray-200 hover:border-gray-300 hover:bg-gray-50 shadow-sm hover:shadow-md transition transform hover:-translate-y-1 dark:bg-gray-800 dark:text-white dark:border-gray-700 dark:hover:bg-gray-700"
                        >
                            Já tenho conta
                        </Link>
                    </template>
                </div>
            </div>
            
            <!-- FEATURE HIGHLIGHTS (Small) -->
            <div class="mt-20 grid grid-cols-1 sm:grid-cols-3 gap-8 max-w-4xl w-full text-left">
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 dark:border-gray-700">
                    <div class="h-10 w-10 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center mb-4 text-xl">🚀</div>
                    <h3 class="font-bold text-lg mb-2">Simples e Rápido</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Interface intuitiva para você não perder tempo aprendendo a usar ferramenta.</p>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 dark:border-gray-700">
                    <div class="h-10 w-10 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg flex items-center justify-center mb-4 text-xl">✅</div>
                    <h3 class="font-bold text-lg mb-2">Fique no Controle</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Acompanhe seu progresso e nunca mais perca um prazo importante.</p>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 dark:border-gray-700">
                    <div class="h-10 w-10 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg flex items-center justify-center mb-4 text-xl">🌙</div>
                    <h3 class="font-bold text-lg mb-2">Modo Escuro</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Conforto visual para trabalhar em qualquer hora do dia ou da noite.</p>
                </div>
            </div>

        </main>

        <!-- FOOTER -->
        <footer class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">
            &copy; {{ new Date().getFullYear() }} Task Manager. Simplificando sua rotina.
        </footer>
    </div>
</template>

<style scoped>
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
.animate-fade-in-up {
    animation: fade-in-up 0.8s ease-out forwards;
}
</style>
