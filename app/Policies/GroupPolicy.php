<?php

namespace App\Policies;

use App\Models\Borrower;
use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class GroupPolicy
{
    use HandlesAuthorization;

    public function updateState(User $user, Group $group)
    {
        $beneficiary = $group->beneficiary;
        $group->unsetRelation('beneficiary');
        return $beneficiary->id_user == $user->id_user;
    }

    public function deleteMember(User $user, Group $group, Borrower $borrower)
    {
        return $group->id_beneficiary == $borrower->id_beneficiary;
    }

    public function addMember(User $user, Group $group, Borrower $borrower)
    {
        return $group->id_beneficiary == $borrower->id_beneficiary;
    }

    public function viewAnyAddBorrower(User $user, Group $group)
    {
        $beneficiary = $group->beneficiary;
        $group->unsetRelation('beneficiary');
        return $user->id_user == $beneficiary->id_user;
    }
    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user, $beneficiary)
    {
        return $beneficiary->id_user == $user->id_user;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Group $group)
    {
        $beneficiary = $group->beneficiary;
        $group->unsetRelation('beneficiary');
        return $user->id_user == $beneficiary->id_user;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, $id_beneficiary)
    {
        return $user->beneficiarys()->where('id_beneficiary', $id_beneficiary)->first();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Group $group)
    {
        if (!$group->state_archived_group) {
            $beneficiary = $group->beneficiary;
            $group->unsetRelation('beneficiary');
            return $beneficiary->id_user == $user->id_user;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Group $group)
    {
        if (!$group->state_archived_group) {

            $beneficiary = $group->beneficiary;
            $group->unsetRelation('beneficiary');
            return $beneficiary->id_user == $user->id_user;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Group $group)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Group $group)
    {
        //
    }
}
