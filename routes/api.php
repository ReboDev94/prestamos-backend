<?php

use App\Http\Controllers\AmortizationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LoginMobileController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LogoutMobileController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\BorrowerController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LoansController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\Reports\ReportsPaymentsController;
use App\Http\Controllers\ShoppingController;
use App\Http\Controllers\TotalAmountsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('user/login',  LoginController::class);
Route::post('user/mobile/login',  LoginMobileController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('user/logout', LogoutController::class);
    Route::post('user/mobile/logout', LogoutMobileController::class);

    Route::get('/user', UserController::class);
    /* Beneficiary */
    Route::controller(BeneficiaryController::class)->group(function () {
        Route::post('/beneficiary', 'create');
        Route::put('/beneficiary/{beneficiary}', 'update');
        Route::delete('/beneficiary/{beneficiary}', 'destroy');
        Route::get('/beneficiary', 'getAll');
    });

    /* Borrower */
    Route::controller(BorrowerController::class)->group(function () {
        Route::post('/borrower', 'create');
        Route::get('/borrower/beneficiary/{beneficiary}', 'getAll');
        Route::post('/borrower/{borrower}', 'update');
        Route::delete('/borrower/{borrower}', 'delete');
        Route::get('/borrower/add/group/{group:slug}', 'listBorrowerAddGroup');
        Route::get('/borrower/add/loans/{beneficiary}', 'listBorrowerAddLoans');
    });

    /* Group */
    Route::controller(GroupController::class)->group(function () {
        Route::post('/group', 'create');
        Route::delete('/group/{group}', 'delete');
        Route::put('/group/{group}', 'update');
        Route::get('/group/{beneficiary}', 'getAll');
        Route::get('/group/slug/{group:slug}', 'group');

        /* members */
        Route::get('/group/members/{group:slug}', 'groupMembers');
        Route::post('/group/member', 'addMember');
        Route::delete('/group/member/{groupBorrower}',  'deleteMember');
        Route::put('/group/update-state/{group:slug}',  'changeStateGroup');
    });

    /* Amortization */
    Route::controller(AmortizationController::class)->group(function () {
        Route::post('/amortization/group/calculated', 'fnCalculatedAmortizationGroup');
        Route::post('/amortization/individual/calculated', 'fnCalculatedAmortization');
    });

    /* Payments */
    Route::controller(PaymentsController::class)->group(function () {
        Route::get('/payments/past-due/group/{group:slug}', 'fnPaymentsPastDueGroup');
        Route::get('/payments/next-due/group/{group:slug}', 'fnPaymentsNextDueGroup');
        Route::get('/payments/group/{group:slug}/borrower/{borrower:slug}', 'fnPaymentsBorrower');
        Route::patch('/payments/group/adjust/{payment}', 'adjustPayment');
        Route::post('/payments/update-state', 'updateStatePayment');
        Route::get('/payments/personal-loans/{borrower:slug}/{individualBorrow}', 'paymentsForIndividualLoan');
        Route::patch('/payments/personal-loans/adjust/{individualPayment}', 'adjustIndividualPayment');
    });

    /* Reports */
    Route::controller(ReportsPaymentsController::class)->group(function () {
        Route::get('/reports/payments/by-date/group/{group:slug}/', 'paymentsByDateGroup');
        Route::get('/reports/payments/past-due/group/{group:slug}', 'paymentsPastDueGroup');
        Route::get('/reports/payments/next-due/group/{group:slug}', 'paymentsNextDueGroup');
        Route::get('/reports/payments/group/{group:slug}/borrower/{borrower:slug}', 'paymentsBorrowerGroup');
        Route::get('/reports/payments/personal-loans/borrower/{borrower:slug}/{individualBorrow}', 'paymentsBorrowerPersonalLoan');
        Route::get('/reports/payments/personal-loans/beneficiary/{beneficiary:id_beneficiary}', 'paymentsBeneficiaryPersonalLoans');
    });

    /* Loans */
    Route::controller(LoansController::class)->group(function () {
        Route::post('/loans', 'addLoans');
        Route::delete('/loans/{individualBorrow}', 'deleteLoan');
        Route::get('/loans/{beneficiary}', 'getLoansBeneficiary');
        Route::get('/loans/amounts/{beneficiary}', 'amountsLoansBeneficiary');
    });

    Route::controller(ShoppingController::class)->prefix('shopping')->group(function () {
        Route::get('/beneficiary/{beneficiary}', 'index');
        Route::post('/', 'store');
        Route::delete('/{shopping}', 'destroy');
        Route::patch('/{shopping}', 'update');
    });

    Route::get('/totals-amounts/{beneficiary}', [TotalAmountsController::class, 'totalsAmount']);
});
