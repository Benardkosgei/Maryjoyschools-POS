<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any students.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('Students_view');
    }

    /**
     * Determine whether the user can view the student.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Client  $student
     * @return mixed
     */
    public function view(User $user, Client $student)
    {
        return $user->hasPermissionTo('Students_view');
    }

    /**
     * Determine whether the user can create students.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('Students_add');
    }

    /**
     * Determine whether the user can update the student.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Client  $student
     * @return mixed
     */
    public function update(User $user, Client $student)
    {
        return $user->hasPermissionTo('Students_edit');
    }

    /**
     * Determine whether the user can delete the student.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Client  $student
     * @return mixed
     */
    public function delete(User $user, Client $student)
    {
        return $user->hasPermissionTo('Students_delete');
    }

    /**
     * Determine whether the user can manage student accounts.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function manageAccounts(User $user)
    {
        return $user->hasPermissionTo('Students_account_management');
    }

    /**
     * Determine whether the user can view student statistics.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewStatistics(User $user)
    {
        return $user->hasPermissionTo('Students_reports');
    }

    /**
     * Determine whether the user can export student data.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function export(User $user)
    {
        return $user->hasPermissionTo('Students_export');
    }

    /**
     * Determine whether the user can import student data.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function import(User $user)
    {
        return $user->hasPermissionTo('Students_import');
    }
}