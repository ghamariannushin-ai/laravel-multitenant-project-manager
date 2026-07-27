<?php

namespace App\Policies;

use App\Models\User;
use App\Domain\Project\Models\Project;

class ProjectPolicy
{
    /**
     * آیا کاربر می‌تواند این پروژه را ببیند؟
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * آیا کاربر می‌تواند این پروژه را ویرایش کند؟
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * آیا کاربر می‌تواند این پروژه را حذف کند؟
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }
}
