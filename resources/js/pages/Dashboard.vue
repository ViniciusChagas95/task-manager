<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../services/api'; // O arquivo que criamos acima

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

// Adicionar nova tarefa
const addTask = async () => {
    if (!newTaskTitle.value) return;
    
    const response = await api.post('/tasks', {
        title: newTaskTitle.value
    });
    
    tasks.value.push(response.data);
    newTaskTitle.value = '';
};

onMounted(fetchTasks);
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Minhas Tarefas</h1>
        
        <div class="mb-4">
            <input v-model="newTaskTitle" @keyup.enter="addTask" 
                   type="text" placeholder="Nova tarefa..." 
                   class="border p-2 rounded mr-2 text-white bg-black">
            <button @click="addTask" class="bg-blue-500 text-white px-4 py-2 rounded">Adicionar</button>
        </div>

         <ul class="mt-4 space-y-2">
            <li v-for="task in tasks" :key="task.id" 
                class="flex items-center justify-between p-3 border-b border-gray-800 hover:bg-gray-900 transition group">
        
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

        <button 
            @click="deleteTask(task.id)" 
            class="text-red-500 hover:text-red-400 p-1 opacity-0 group-hover:opacity-100 transition"
            title="Apagar tarefa">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
    </li>
</ul>
    </div>
</template>