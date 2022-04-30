<?php

namespace App\Http\Controllers;

use App\Enum\StatePaymentEnum;
use App\Http\Requests\Group\AddPayslipRequest;
use App\Http\Requests\Group\GroupRequest;
use App\Http\Requests\Group\GroupUpdateRequest;
use App\Http\Requests\Group\MemberAddRequest;
use App\Http\Requests\Group\MemberUpdateRequest;
use App\Http\Requests\Group\UpdatePayslipRequest;
use App\Models\Beneficiary;
use App\Models\Borrower;
use App\Models\Group;
use App\Models\GroupBorrower;
use App\Models\Payslip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function create(GroupRequest $request): JsonResponse
    {
        $name_group     = $request->name_group;
        $created_group  = $request->created_group;
        $day_payment    = $request->day_payment;
        $id_beneficiary = $request->id_beneficiary;

        $this->authorize('create', [Group::class, $id_beneficiary]);

        $group = new Group();
        $group->name_group      = $name_group;
        $group->created_group   = $created_group;
        $group->day_payment     = $day_payment;
        $group->id_beneficiary  = $id_beneficiary;
        $group->save();

        return new JsonResponse(['group' => $group]);
    }

    public function delete(Group $group): JsonResponse
    {
        $this->authorize('delete', $group);
        $group->delete();
        return new JsonResponse(['group' => $group]);
    }

    public function update(GroupUpdateRequest $request, Group $group): JsonResponse
    {
        $this->authorize('update', $group);
        $group->slug = null;
        $group->update([
            'name_group'    => $request->name_group,
            'created_group' => $request->created_group,
            'day_payment'   => $request->day_payment
        ]);
        return new JsonResponse(['group' => $group]);
    }

    public function getAll(Request $request, Beneficiary $beneficiary): JsonResponse
    {
        $this->authorize('viewAny', [Group::class, $beneficiary]);
        $search = $request->input('search', '');
        $archived = $request->input('archived', 0);

        $perPage = 6;
        $groups = $beneficiary->groups()
            ->where('state_archived_group', $archived)
            ->where(function ($query) use ($search, $archived) {
                $query->where('name_group', 'LIKE', "%{$search}%");
            })
            ->orderBy('id_group', 'DESC')
            ->paginate($perPage);
        return new JsonResponse(['groups' => $groups]);
    }

    public function group(Request $request, Group $group): JsonResponse
    {
        $this->authorize('view', $group);
        $simple = filter_var($request->simple, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($simple) {
            return new JsonResponse(['group' => $group]);
        }

        $payments        = $group->paymentsPaid;
        $amount_charged  = $payments->sum('amount_payment');
        $group->unsetRelation('paymentsPaid');

        $group_borrower  = $group->groupBorrowers;
        $amount_borrow   = $group_borrower->sum('amount_borrow');
        $amount_interest = $group_borrower->sum('amount_interest');

        $amount_total    =  $amount_borrow +  $amount_interest;
        $group->unsetRelation('groupBorrowers');

        return new JsonResponse([
            'group'             => $group,
            'number_members'    => $group->borrowers()->count(),
            'amount_charged'    => round(($amount_charged / 100), 2),
            'amount_borrow'     => round($amount_borrow / 100, 2),
            'amount_interest'   => round($amount_interest / 100, 2),
            'amount_total'      => round($amount_total / 100, 2)
        ]);
    }

    /* TODO:MIEMBROS */

    public function addMember(MemberAddRequest $request): JsonResponse
    {
        $slug_group         = $request->slug_group;
        $id_borrower        = $request->id_borrower;
        $amount_borrow      = $request->amount_borrow;
        $amount_interest    = $request->amount_interest;

        $group    = Group::where('slug', $slug_group)->first();
        $borrower = Borrower::where('id_borrower', $id_borrower)->first();
        $this->authorize('addMember', [$group, $borrower]);

        $memberIsRegister = $group->borrowers()->where('borrowers.id_borrower', $borrower->id_borrower)->first();

        if ($memberIsRegister)
            return new JsonResponse(['isRegister' =>  true], 302);

        $in_proccess    = StatePaymentEnum::STATUS_INPROCCESS->value;
        $group->borrowers()->attach($borrower->id_borrower, [
            'state_borrow' => $in_proccess, 'amount_borrow' => $amount_borrow, 'amount_interest' => $amount_interest
        ]);

        $member = $group->borrowers()->where('borrowers.id_borrower', $borrower->id_borrower)->first();
        if ($member) {
            $amount_payment =  $member->group_borrower->paymentsPaid->sum('amount_payment');
            $member->group_borrower->unsetRelation('paymentsPaid');
            $member->group_borrower->amount_payment_total         =  $amount_payment;
            $member->group_borrower->amount_payment_total_decimal =  round($amount_payment / 100, 2);
        }
        return new JsonResponse(['member' =>  $member]);
    }

    public function updateMember(MemberUpdateRequest $request, GroupBorrower $groupBorrower): JsonResponse
    {

        $amount_borrow      = $request->amount_borrow;
        $amount_interest    = $request->amount_interest;
        $id_borrower        = $groupBorrower->id_borrower;

        $group    = Group::where('id_group', $groupBorrower->id_group)->first();
        $borrower = Borrower::where('id_borrower', $id_borrower)->first();

        $this->authorize('updateMember', [$group, $borrower]);

        $groupBorrower->update(['amount_borrow' =>  $amount_borrow, 'amount_interest' => $amount_interest]);
        $member = $group->borrowers()->where('borrowers.id_borrower', $id_borrower)->first();

        if ($member) {
            $amount_payment =  $member->group_borrower->paymentsPaid->sum('amount_payment');
            $member->group_borrower->unsetRelation('paymentsPaid');
            $member->group_borrower->amount_payment_total         =  $amount_payment;
            $member->group_borrower->amount_payment_total_decimal =  round($amount_payment / 100, 2);
        }

        return new JsonResponse(['member' => $member]);
    }

    public function deleteMember(GroupBorrower $groupBorrower): JsonResponse
    {

        $id_group       = $groupBorrower->id_group;
        $id_borrower    = $groupBorrower->id_borrower;

        $group    = Group::where('id_group', $id_group)->first();
        $borrower = Borrower::where('id_borrower', $id_borrower)->first();
        $this->authorize('deleteMember', [$group, $borrower]);
        $isDeleted = $groupBorrower->delete();

        return new JsonResponse(['isDeleted' => (bool) $isDeleted]);
    }

    public function groupMembers(Request $request, Group $group): JsonResponse
    {
        $search = $request->input('search', '');
        $this->authorize('view', $group);
        $borrowers = $group->borrowers()
            ->where(DB::raw("concat(name_borrower, ' ', last_name_borrower)"), 'LIKE', "%" . $search . "%")
            ->orderBy('name_borrower', 'DESC')
            ->paginate(5)
            ->through(function ($borrower) {
                $amount_payment =  $borrower->group_borrower->paymentsPaid->sum('amount_payment');
                $borrower->group_borrower->unsetRelation('paymentsPaid');
                $borrower->group_borrower->amount_payment_total         =  $amount_payment;
                $borrower->group_borrower->amount_payment_total_decimal =  round($amount_payment / 100, 2);
                return $borrower;
            });

        return new JsonResponse(['borrowers' => $borrowers]);
    }

    /* payslip */

    public function addPaySlip(AddPayslipRequest $request): JsonResponse
    {

        $name_payslip       = $request->name_payslip;
        $created_payslip    = $request->created_payslip;
        $slug_group         = $request->slug_group;

        $group = Group::where('slug', $slug_group)->first();

        $payslip = new Payslip();
        $payslip->name_payslip      = $name_payslip;
        $payslip->created_payslip   = $created_payslip;

        $group->payslips()->save($payslip);

        return new JsonResponse(['payslip' => $payslip]);
    }

    public function updatePayslip(UpdatePayslipRequest $request, Payslip $payslip): JsonResponse
    {
        $name_payslip       = $request->name_payslip;
        $created_payslip    = $request->created_payslip;
        $payslip->update(['name_payslip' => $name_payslip, 'created_payslip' => $created_payslip]);

        return new JsonResponse(['payslip' => $payslip]);
    }

    public function listPayslips(Request $request, Group $group): JsonResponse
    {
        $search  = $request->input('search', '');
        $payslips = $group->payslips()
        ->where('name_payslip', 'LIKE', "%" . $search . "%")
        ->orderBy('name_payslip', 'DESC')
        ->paginate(5);

        return new JsonResponse(['payslips' => $payslips]);
    }
}
