<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../services/api'; // O arquivo que criamos acima
import { computed } from 'vue';

const totalTasks = computed(() => tasks.value.length);
const completedTasks = computed(() => tasks.value.filter(task => task.is_completed).length);
const progressPerncetage = computed(() => {
    if (totalTasks.value === 0) return 0;
    return Math.round((completedTasks.value / totalTasks.value) * 100);
})
// Tipagem da Tarefa
interface Task {
    id: number;
    title: string;
    is_completed: boolean;
}

const tasks = ref<Task[]>([]);
const newTaskTitle = ref('');

// Buscar tarefas do Backend (Laravel)
const fetchTasks = async () => {
    const response = await api.get('/tasks');
    tasks.value = response.data;
};
//Alternar status da tarefa
const toggleTask = async (task: Task) => {
    try{
        // Fazemos a chamada PUT para a API
        // Invertendo o valor atual: Se for true vira false, e vice-versa
        const response = await api.put(`/tasks/${task.id}`, {
            title: task.title, // Enviamos o título atual para manter o valor
            is_completed: !task.is_completed
        });

        // Se deu certo no banco, atualizamos a nossa lista local para a interface refletir a mudança
        task.is_completed = response.data.is_completed;
    } catch (error) {
        console.error("Erro ao atualizar tarefa:", error);
        alert("Não foi possível atualizar a tarefa.");
    }
};

//Deletar uma tarefa
const deleteTask = async (id: number) => {
    if (!confirm('Tem certeza que deseja excluir esta tarefa?')) return;

    try{
        await api.delete(`/tasks/${id}`);

        // Removemos a tarefa da lista local
        tasks.value = tasks.value.filter(task => task.id !== id);
    } catch (error) {
        console.error("Erro ao excluir tarefa:", error);
        alert("Não foi possível excluir a tarefa.");0
    }
}
const taskInput = ref<HTMLInputElement | null>(null); // Referência para o elemento do DOM
// Adicionar nova tarefa
const addTask = async () => {
    if (!newTaskTitle.value) return;
    
    try {
        const response = await api.post('/tasks', {
            title: newTaskTitle.value
        });
        
        tasks.value.push(response.data);
        newTaskTitle.value = ''; // Limpa o campo
        taskInput.value?.focus(); // Mantém o foco no campo
    } catch (error) {
        console.error("Erro ao adicionar tarefa:", error);
        alert("Não foi possível adicionar a tarefa.");
    }
};

const filter = ref<'all' | 'pending' | 'completed'>('all');

// Esta função vai substituir o uso direto de 'tasks' no v-for
const filteredTasks = computed(() => {
    if (filter.value === 'pending') {
        return tasks.value.filter(t => !t.is_completed);
    }
    if (filter.value === 'completed') {
        return tasks.value.filter(t => t.is_completed);
    }
    return tasks.value;
})

onMounted(fetchTasks);
</script>

<template>
    <!-- Container Principal -->
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Minhas Tarefas</h1>
        <div class="mb-6">
            <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-white">Progresso</span>
                <span class="text-sm font-medium text-white">{{ progressPerncetage }}%</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" 
             :style="{ width: progressPerncetage + '%' }"></div>
            </div>
        </div>
        <!-- Filtros -->
        <div class="flex gap-2 mb-4">
            <button 
                @click="filter = 'all'" 
                :class="filter === 'all' ? 'bg-blue-600' : 'bg-gray-700'"
                class="px-3 py-1 rounded text-xs text-white transition"
            >
                Todas
            </button>
            <button 
                @click="filter = 'pending'" 
                :class="filter === 'pending' ? 'bg-blue-600' : 'bg-gray-700'"
                class="px-3 py-1 rounded text-xs text-white transition"
            >
                Pendentes
            </button>
            <button 
                @click="filter = 'completed'" 
                :class="filter === 'completed' ? 'bg-blue-600' : 'bg-gray-700'"
                class="px-3 py-1 rounded text-xs text-white transition"
            >
                Concluídas
            </button>
        </div>
        <!-- Campo de Adicionar Tarefa -->
        <div class="mb-4">
                <input ref="taskInput" v-model="newTaskTitle" @keyup.enter="addTask" 
                type="text" placeholder="Nova tarefa..." 
                class="border p-2 rounded mr-2 text-white bg-black">
                
                <button @click="addTask" 
                    class="bg-blue-500 hover:bg-blue-600 active:scale-95 transition-all text-white px-4 py-2 rounded">
                    Adicionar
                </button>
        </div>
        <!-- Lista de Tarefas -->
        <ul class="mt-4 space-y-2">
            <li v-for="task in filteredTasks" :key="task.id" 
                class="flex items-center justify-between p-3 border-b border-gray-800 hover:bg-gray-900 transition group">
        <!-- Checkbox e Título da Tarefa -->
        <div class="flex items-center gap-3">
            <input 
                type="checkbox" 
                :checked="task.is_completed" 
                @change="toggleTask(task)"
                class="rounded border-gray-700 bg-gray-800 text-blue-600 focus:ring-blue-500 cursor-pointer"
            >
            
            <span :class="task.is_completed ? 'line-through text-gray-500' : 'text-white'">
                {{ task.title }}
            </span>
        </div>
        <!-- Botão de Excluir -->
        <button 
            @click="deleteTask(task.id)" 
            class="text-red-500 hover:text-red-400 p-1 opacity-0 group-hover:opacity-100 transition"
            title="Apagar tarefa">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
    </li>
    <div v-if="filteredTasks.length === 0" class="text-center py-10 text-gray-500">
         <p>Nenhuma tarefa encontrada neste filtro. 🚀</p>
    </div>
</ul>
    </div>
</template>