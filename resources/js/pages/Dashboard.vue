<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { PencilIcon, XMarkIcon, TrashIcon, CheckIcon, PlusIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import api from '../services/api';
import { router } from '@inertiajs/vue3';
import { ArrowRightStartOnRectangleIcon } from '@heroicons/vue/24/outline';

// --- ESTADOS ---
const tasks = ref<Task[]>([]);
const newTaskTitle = ref('');
const newTaskDueDate = ref('');
const filter = ref<'all' | 'pending' | 'completed'>('all');
const taskInput = ref<HTMLInputElement | null>(null);
const dateInput = ref<HTMLInputElement | null>(null);
const isEditModalOpen = ref(false);
const editingTask = ref<Task | null>(null);
const logout = () => { router.post('/logout'); }

interface Task {
    id: number;
    title: string;
    is_completed: boolean;
    due_date?: string;
}

// --- LÓGICA DE DATAS (CORREÇÃO DE FUSO) ---
const formatDate = (dateString: string | undefined | null) => {
    if (!dateString) return '';

    const cleanDate = dateString.split('T')[0];
    const [year, month, day] = cleanDate.split('-').map(Number);
    const date = new Date(year, month - 1, day);

    if (!isNaN(date.getTime())) {
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    } else {
        return 'Data inválida';
    }
};

const getDueDateClass = (dueDate: string | undefined | null, isCompleted: boolean) => {
    if (isCompleted) return 'text-gray-400 dark:text-gray-500'; // Cor neutra se completada
    if (!dueDate) return 'text-gray-500 dark:text-gray-400';

    const cleanDate = dueDate.split('T')[0];
    const [year, month, day] = cleanDate.split('-').map(Number);
    const taskDate = new Date(year, month - 1, day);
    
    const today = new Date();
    today.setHours(0,0,0,0);

    if (isNaN(taskDate.getTime())) return 'text-gray-500';

    if (taskDate < today) return 'text-red-600 dark:text-red-400 font-bold'; // Atrasada
    if (taskDate.getTime() === today.getTime()) return 'text-amber-600 dark:text-amber-400 font-bold'; // Hoje
    return 'text-blue-600 dark:text-blue-400'; // Futuro
}

// --- COMPUTED PROPERTIES ---
const totalTasks = computed(() => tasks.value.length);
const completedTasks = computed(() => tasks.value.filter(t => t.is_completed).length);
const progressPerncetage = computed(() => {
    if (totalTasks.value === 0) return 0;
    return Math.round((completedTasks.value / totalTasks.value) * 100);
});

const filteredTasks = computed(() => {
    let result = tasks.value;
    if (filter.value === 'pending') result = tasks.value.filter(t => !t.is_completed);
    if (filter.value === 'completed') result = tasks.value.filter(t => t.is_completed);
    
    // Ordenar: Pendentes primeiro, depois por data de criação (simulada aqui pela ordem do array ou ID se tivesse)
    // Para simplificar, vamos manter a ordem que vem do array, mas você poderia adicionar .sort() aqui.
    return result;
});

// --- AÇÕES ---
const fetchTasks = async () => {
    try {
        const response = await api.get('/tasks');
        tasks.value = response.data;
    } catch (error) {
        console.error("Erro ao buscar tarefas", error);
    }
};

const addTask = async () => {
    if (!newTaskTitle.value.trim()) return;

    if (dateInput.value?.validity.badInput) {
        alert("Data inválida! Verifique o dia, mês e ano.");
        return;
    }

    if (newTaskDueDate.value) {
        const [year, month, day] = newTaskDueDate.value.split('-').map(Number);
        if (month < 1 || month > 12 || day < 1 || day > 31) {
            alert("Data inválida: Mês ou dia fora do limite.");
            return;
        }
        const date = new Date(year, month - 1, day);
        if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
             alert("Data inválida para este mês.");
             return;
        }
    }

    try {
        const response = await api.post('/tasks', {
            title: newTaskTitle.value,
            due_date: newTaskDueDate.value
        });
        tasks.value.push(response.data);
        newTaskTitle.value = ''; 
        newTaskDueDate.value = ''; 
        taskInput.value?.focus();
    } catch (error) {
        alert("Erro ao adicionar tarefa.");
    }
};

const toggleTask = async (task: Task) => {
    try {
        const response = await api.put(`/tasks/${task.id}`, {
            title: task.title,
            is_completed: !task.is_completed
        });
        // Atualiza a reatividade local
        const index = tasks.value.findIndex(t => t.id === task.id);
        if (index !== -1) {
             tasks.value[index] = response.data;
        }
    } catch (error) {
        alert("Erro ao atualizar status.");
    }
};

const deleteTask = async (id: number) => {
    if (!confirm('Excluir esta tarefa?')) return;
    try {
        await api.delete(`/tasks/${id}`);
        tasks.value = tasks.value.filter(t => t.id !== id);
    } catch (error) {
        alert("Erro ao excluir tarefa.");
    }
};

const openEditModal = (task: Task) => {
    editingTask.value = { ...task };
    if (editingTask.value.due_date) {
        editingTask.value.due_date = editingTask.value.due_date.split('T')[0];
    }
    isEditModalOpen.value = true;
};

const saveEdit = async () => {
    if (!editingTask.value) return;
    if (!editingTask.value.title.trim()) return;

    try {
        const response = await api.put(`/tasks/${editingTask.value.id}`, {
            title: editingTask.value.title,
            due_date: editingTask.value.due_date,
            is_completed: editingTask.value.is_completed
        });

        const index = tasks.value.findIndex(t => t.id === editingTask.value?.id);
        if (index !== -1) {
            tasks.value[index] = response.data;
        }

        isEditModalOpen.value = false;
        editingTask.value = null;
    } catch (error) {
        alert("Erro ao atualizar tarefa.");
    }
};

onMounted(fetchTasks);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans transition-colors duration-300">
        
        <!-- HEADER / NAVIGATION -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                            TM
                        </div>
                        <h1 class="text-xl font-bold tracking-tight text-blue-600 dark:text-blue-400">
                            Task Manager
                        </h1>
                    </div>
                    <button 
                        @click="logout" 
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors"
                        title="Sair da conta"
                    >
                        <span>Sair</span>
                        <ArrowRightStartOnRectangleIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- WELCOME & PROGRESS -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4">Visão Geral</h2>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center gap-6">
                    <!-- Progress Circle or Bar -->
                    <div class="flex-1 w-full">
                        <div class="flex justify-between mb-2 text-sm font-medium">
                            <span class="text-gray-600 dark:text-gray-400">Seu Progresso</span>
                            <span class="text-blue-600 dark:text-blue-400 font-bold">{{ progressPerncetage }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-1000 ease-out" 
                                 :style="{ width: progressPerncetage + '%' }"></div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-500 flex justify-between">
                            <span>{{ completedTasks }} concluídas</span>
                            <span>{{ totalTasks - completedTasks }} pendentes</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ADD TASK CARD -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 mb-8 transform transition hover:shadow-md">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide text-xs">Adicionar Nova Tarefa</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-grow relative">
                        <input 
                            ref="taskInput" 
                            v-model="newTaskTitle" 
                            @keyup.enter="addTask" 
                            type="text" 
                            placeholder="O que você precisa fazer?" 
                            class="w-full pl-4 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white transition-all outline-none"
                        >
                    </div>
                    <div class="relative min-w-[160px]">
                        <input 
                            ref="dateInput"
                            v-model="newTaskDueDate" 
                            type="date" 
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white transition-all outline-none text-gray-600 dark:text-gray-400"
                        >
                        <CalendarIcon class="h-5 w-5 text-gray-400 absolute left-3 top-3.5 pointer-events-none" />
                    </div>
                    <button 
                        @click="addTask" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium flex items-center justify-center gap-2 transition-transform active:scale-95 shadow-lg shadow-blue-500/20"
                    >
                        <PlusIcon class="h-5 w-5" />
                        <span class="hidden sm:inline">Adicionar</span>
                    </button>
                </div>
            </div>

            <!-- FILTERS -->
            <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                <button v-for="f in (['all', 'pending', 'completed'] as const)" :key="f"
                    @click="filter = f"
                    :class="filter === f 
                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' 
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-transparent dark:border-gray-700'"
                    class="px-4 py-2 rounded-full text-sm font-medium capitalize transition-all whitespace-nowrap"
                >
                    {{ f === 'all' ? 'Todas' : f === 'pending' ? 'Pendentes' : 'Concluídas' }}
                </button>
            </div>

            <!-- TASK LIST -->
            <TransitionGroup 
                tag="ul" 
                name="list" 
                class="space-y-3"
            >
                <li v-for="task in filteredTasks" :key="task.id" 
                    class="group bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4 transition-all hover:border-blue-200 dark:hover:border-blue-900 hover:shadow-md"
                    :class="{ 'opacity-60 bg-gray-50 dark:bg-gray-800/50': task.is_completed }"
                >
                    <!-- CHECKBOX -->
                    <button 
                        @click="toggleTask(task)"
                        class="flex-shrink-0 h-6 w-6 rounded-full border-2 flex items-center justify-center transition-colors duration-200"
                        :class="task.is_completed 
                            ? 'bg-green-500 border-green-500' 
                            : 'border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400'"
                    >
                        <CheckIcon v-if="task.is_completed" class="h-4 w-4 text-white" stroke-width="3" />
                    </button>

                    <!-- CONTENT -->
                    <div class="flex-grow min-w-0 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                        <span 
                            class="font-medium text-gray-900 dark:text-gray-100 truncate transition-all duration-300"
                            :class="{ 'line-through text-gray-500 dark:text-gray-500': task.is_completed }"
                        >
                            {{ task.title }}
                        </span>
                        
                        <div class="flex items-center gap-2 text-xs sm:ml-auto flex-shrink-0">
                             <div v-if="task.due_date" class="flex items-center gap-1" :class="getDueDateClass(task.due_date, task.is_completed)">
                                <CalendarIcon class="h-3.5 w-3.5" />
                                <span>{{ formatDate(task.due_date) }}</span>
                             </div>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex items-center gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                        <button @click="openEditModal(task)" class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Editar">
                            <PencilIcon class="h-4 w-4" />
                        </button>
                        <button @click="deleteTask(task.id)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Excluir">
                            <TrashIcon class="h-4 w-4" />
                        </button>
                    </div>
                </li>
            </TransitionGroup>

            <!-- EMPTY STATE -->
            <div v-if="filteredTasks.length === 0" class="text-center py-12">
                <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <CalendarIcon class="h-8 w-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Nenhuma tarefa encontrada</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                    {{ filter === 'all' ? 'Comece adicionando uma nova tarefa acima!' : 'Tente mudar o filtro para ver outras tarefas.' }}
                </p>
            </div>
            
        </main>

        <!-- EDIT MODAL -->
        <TransitionRoot appear :show="isEditModalOpen" as="template">
            <Dialog as="div" @close="isEditModalOpen = false" class="relative z-10">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all border border-gray-100 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-4">
                                    <DialogTitle as="h3" class="text-lg font-bold leading-6 text-gray-900 dark:text-white">
                                        Editar Tarefa
                                    </DialogTitle>
                                    <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <XMarkIcon class="h-5 w-5" />
                                    </button>
                                </div>
                                
                                <div class="mt-4 space-y-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
                                        <input 
                                            v-if="editingTask" 
                                            v-model="editingTask.title" 
                                            type="text" 
                                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:text-white outline-none"
                                        >
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Data de Vencimento</label>
                                        <input 
                                            v-if="editingTask" 
                                            v-model="editingTask.due_date" 
                                            type="date"
                                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:text-white outline-none text-gray-600 dark:text-gray-400"
                                        >
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end gap-3">
                                    <button
                                        type="button"
                                        class="inline-flex justify-center rounded-lg border border-transparent bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none"
                                        @click="isEditModalOpen = false"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex justify-center rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                        @click="saveEdit"
                                        :disabled="!editingTask?.title.trim()"
                                    >
                                        Salvar Alterações
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<style scoped>
/* List Transitions */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

/* Custom Scrollbar for dark mode compatibility if needed */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background-color: transparent;
}
::-webkit-scrollbar-thumb {
    background-color: #d1d5db; /* gray-300 */
    border-radius: 9999px;
}
:is(.dark) ::-webkit-scrollbar-thumb {
    background-color: #4b5563; /* gray-600 */
}
</style>