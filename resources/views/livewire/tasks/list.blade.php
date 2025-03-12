<?php

use App\Livewire\Forms\TaskForm;
use App\Models\Task;
use function Livewire\Volt\{layout, title, with, form, mount, state, usesPagination};

layout('components.layouts.app');
title('Tasks List');
usesPagination();
form(TaskForm::class);

with(fn () => ['tasks' => Task::paginate(2)]);

state([
    'showingTaskModal' => false,
    'editingTask' => false,
]);

$openTaskModal = function () {
    $this->form->reset();
    $this->editingTask = false;
    $this->showingTaskModal = true;
};

$closeTaskModal = function () {
    $this->showingTaskModal = false;
};

$editTask = function (Task $task) {
    $this->authorize('update', $task);
    $this->form->setTask($task);
    $this->editingTask = true;
    $this->showingTaskModal = true;
};

$saveTask = function () {
    $this->form->validate();

    if ($this->editingTask) {
        $this->authorize('update', Task::findOrFail($this->form->id));
        $this->form->update();
    } else {
        $this->form->store();
    }

    $this->showingTaskModal = false;
};

$toggleComplete = function (Task $task) {
    $this->authorize('update', $task);

    $task->update([
        'is_completed' => ! $task->is_completed,
    ]);
};

$deleteTask = function (Task $task) {
    $this->authorize('delete', $task);

    $task->delete();

    $this->setPage(1);
};
?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg border border-zinc-700">
            <div class="p-6 text-zinc-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-zinc-100">Mis Tareas</h2>
                    <button
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-800"
                        wire:click="openTaskModal"
                    >
                        Nueva Tarea
                    </button>
                </div>

                <!-- Lista de tareas -->
                <div class="space-y-4">
                    @foreach($tasks as $task)
                        <div
                            class="p-4 border rounded-lg {{ $task->is_completed ? 'bg-zinc-700 border-zinc-600' : 'bg-zinc-900 border-zinc-700' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex items-start space-x-4">
                                    <input
                                        type="checkbox"
                                        class="mt-1 bg-zinc-700 border-zinc-500 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-zinc-800 rounded"
                                        wire:click="toggleComplete({{ $task->id }})"
                                        {{ $task->is_completed ? 'checked' : '' }}
                                    >
                                    <div>
                                        <h3 class="font-medium {{ $task->is_completed ? 'line-through text-zinc-400' : 'text-zinc-100' }}">
                                            {{ $task->title }}
                                        </h3>
                                        @if($task->description)
                                            <p class="text-zinc-400 mt-1">{{ $task->description }}</p>
                                        @endif
                                        @if($task->due_date)
                                            <p class="text-sm text-zinc-500 mt-2">
                                                Fecha límite: {{ $task->due_date->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        class="text-indigo-400 hover:text-indigo-300"
                                        wire:click="editTask({{ $task->id }})"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        class="text-rose-400 hover:text-rose-300"
                                        wire:click="deleteTask({{ $task->id }})"
                                        onclick="return confirm('¿Estás seguro de que deseas eliminar esta tarea?')"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($tasks->isEmpty())
                        <div class="text-center py-8 text-zinc-400">
                            No tienes tareas pendientes. ¡Crea una nueva tarea para empezar!
                        </div>
                    @endif

                    @if($tasks->hasPages())
                        <div class="mt-6 relative">
                            <div class="absolute top-0 left-1/2 transform -translate-x-1/2">
                                <div>
                                    {{ $tasks->links() }}
                                </div>
                            </div>
                        </div>
                        <div class="pb-8"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Tarea -->
    @if($showingTaskModal)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4">
            <div class="bg-zinc-800 rounded-lg shadow-xl max-w-md w-full border border-zinc-700">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-zinc-100 mb-4">
                        {{ $editingTask ? 'Editar Tarea' : 'Nueva Tarea' }}
                    </h3>

                    <form wire:submit.prevent="saveTask">
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-zinc-300">Título</label>
                            <input
                                type="text"
                                id="title"
                                wire:model="form.title"
                                class="mt-1 block w-full bg-zinc-700 border-zinc-600 text-zinc-100 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-800 text-sm px-3 py-2"
                            >
                            @error('form.title')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-zinc-300">Descripción</label>
                            <textarea
                                id="description"
                                wire:model="form.description"
                                rows="3"
                                class="mt-1 block w-full bg-zinc-700 border-zinc-600 text-zinc-100 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-800 text-sm px-3 py-2"
                            ></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="due_date" class="block text-sm font-medium text-zinc-300">Fecha límite</label>
                            <input
                                type="datetime-local"
                                id="due_date"
                                wire:model="form.due_date"
                                class="mt-1 block w-full bg-zinc-700 border-zinc-600 text-zinc-100 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-800 text-sm px-3 py-2"
                            >
                        </div>

                        @if($editingTask)
                            <div class="mb-4">
                                <label class="inline-flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="form.is_completed"
                                        class="bg-zinc-700 border-zinc-500 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-zinc-800 rounded"
                                    >
                                    <span class="ml-2 text-sm text-zinc-300">Completada</span>
                                </label>
                            </div>
                        @endif

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                class="px-4 py-2 bg-zinc-600 text-zinc-200 rounded-md hover:bg-zinc-500"
                                wire:click="closeTaskModal"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                {{ $editingTask ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
