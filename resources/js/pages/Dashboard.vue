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

        <ul>
            <li v-for="task in tasks" :key="task.id" class="border-b py-2">
                <input type="checkbox":checked="task.is_completed"@change="toggleTask(task)" class="mr-2 cursor-pointer">
                <span :class="{ 'line-through text-gray-500': task.is_completed }">{{ task.title }}</span>
            </li>
        </ul>
    </div>
</template>