<?php

namespace App\Livewire\Forms;

use App\Models\Task;
use Livewire\Form;

class TaskForm extends Form
{
    public $id;

    public $title = '';

    public $description = '';

    public $due_date = null;

    public $is_completed = false;

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'is_completed' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título de la tarea es obligatorio.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
        ];
    }

    public function setTask(Task $task): void
    {
        $this->id = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->due_date = $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : null;
        $this->is_completed = $task->is_completed;
    }

    public function store(): Task
    {
        $task = new Task($this->all());
        $task->user_id = auth()->id();
        $task->save();

        return $task;
    }

    public function update(): Task
    {
        $task = Task::findOrFail($this->id);
        $task->update($this->all());

        return $task;
    }
}
