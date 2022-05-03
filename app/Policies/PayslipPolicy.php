<?php

namespace App\Policies;

use App\Models\Payslip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class PayslipPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payslip  $payslip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Payslip $payslip)
    {
        $id_user = $payslip->group->beneficiary->id_user;
        $payslip->unsetRelations();
        return $user->id_user == $id_user;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payslip  $payslip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Payslip $payslip)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payslip  $payslip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Payslip $payslip)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payslip  $payslip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Payslip $payslip)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Payslip  $payslip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Payslip $payslip)
    {
        //
    }
}
