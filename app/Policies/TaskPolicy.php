<?php

namespace App\Policies;

use App\Models\User;
use App\Domain\Task\Models\Task;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    private function ownsTask(User $user, Task $task): bool
    {
        return $task->project?->user_id === $user->id;
    }
}  
