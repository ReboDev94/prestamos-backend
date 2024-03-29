<?php

namespace App\Http\Controllers;

use App\Http\Requests\Amortization\CalculatedForTypePeriod;
use App\Http\Requests\Amortization\CalculatedGroup;
use Illuminate\Http\Request;
use App\Traits\AmortizationTraits;
use Illuminate\Http\JsonResponse;

class AmortizationController extends Controller
{
    use AmortizationTraits;


    public function fnCalculatedAmortizationGroup(CalculatedGroup $request): JsonResponse
    {
        $amount_borrow          = $request->amount_borrow;
        $amount_interest        = $request->amount_interest;
        $amount_payment_period  = $request->amount_payment_period;
        $date_init_payment      = $request->date_init_payment;
        $payment_every_n_weeks  = $request->payment_every_n_weeks;

        $amortization = $this->calculatedAmortizationGroup($amount_borrow, $amount_interest, $amount_payment_period, $date_init_payment, $payment_every_n_weeks);
        return new JsonResponse(['amortization' => $amortization]);
    }

    public function fnCalculatedAmortization(CalculatedForTypePeriod $request): JsonResponse
    {
        $amount_borrow          = $request->amount_borrow;
        $amount_interest        = $request->amount_interest;
        $amount_payment_period  = $request->amount_payment_period;
        $date_init_payment      = $request->date_init_payment;
        $type_period            = $request->type_period;
        $payment_every_n        = $request->payment_every_n;
        $amortization = $this->calculatedAmortization($amount_borrow, $amount_interest, $amount_payment_period, $date_init_payment, $type_period, $payment_every_n);

        return new JsonResponse(['amortization' => $amortization]);
    }
}
