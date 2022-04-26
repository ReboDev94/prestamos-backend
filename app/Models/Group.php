<?php

namespace App\Models;

use App\Enum\DayWeekEnum;
use App\Enum\StatePaymentEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    use HasFactory, Sluggable;

    protected $table        = 'groups';
    protected $primaryKey   = 'id_group';

    protected $guarded  = [
        'id_group',
        'day_payment',
        'slug',
        'state_archived_group'
    ];

    protected $fillable = [
        'name_group',
        'created_group',
        'day_payment',
        'id_beneficiary'
    ];

    protected $casts    = [
        'created_group'         => 'date',
        'state_archived_group'  => 'boolean',
        'day_payment'           => DayWeekEnum::class
    ];

    protected $appends  = ['day_payment_name'];

    /**
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name_group'
            ]
        ];
    }

    public function nameGroup(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => Str::lower($value),
        );
    }

    public function dayPaymentName(): Attribute
    {
        return new Attribute(
            get: fn () => DayWeekEnum::getLabel($this->day_payment),
        );
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'id_beneficiary', 'id_beneficiary');
    }


    public  function borrowers()
    {
        return $this->belongsToMany(Borrower::class, GroupBorrower::class, 'id_group', 'id_borrower')
            ->as('group_borrower')
            ->withPivot(['id_group_borrower', 'amount_borrow', 'amount_interest', 'state_borrow'])
            ->withTimestamps();
    }

    public function groupBorrowers()
    {
        return $this->hasMany(GroupBorrower::class, 'id_group', 'id_group');
    }

    public function paymentsPaid()
    {
        return $this->hasManyThrough(Payment::class, Payslip::class, 'id_group', 'id_payslip', 'id_group', 'id_payslip')
            ->where('state_payment', '=', StatePaymentEnum::STATUS_PAID);
    }
}
