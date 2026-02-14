<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // Listar todas as tarefas (GET /api/tasks)
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->tasks()->latest()->get();
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
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $task = $user->tasks()->create($validated);
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorizeTask($task);
        return $task;
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
        $this->authorizeTask($task);
        
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'sometimes|boolean',
            'due_date' => 'nullable|date',
        ]);
        $task->update($validated);
        return response()->json($task);
    }

    // Deletar uma tarefa (DELETE /api/tasks/{id})
    public function destroy(Task $task)
    {
        $this->authorizeTask($task);
        
        $task->delete();
        return response()->json(['message' => 'Tarefa removida com sucesso!'], 204);
    }

    private function authorizeTask(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
