<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Listar todas as tarefas (GET /api/tasks)
    public function index()
    {
        return Task::latest()->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    // Criar uma nova tarefa (POST /api/tasks)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $task = Task::create($validated);
        return response()->json($task, 211);// Retorna a tarefa criada com status 211(Created)
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    // Atualizar uma tarefa existente (PUT /api/tasks/{id})
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'sometimes|boolean',
        ]);
        $task->update($validated);
        return response()->json($task);
    }

    // Deletar uma tarefa (DELETE /api/tasks/{id})
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Tarefa removida com sucesso!'], 204); // Retorna status 204 (No Content
        //
    }
}
