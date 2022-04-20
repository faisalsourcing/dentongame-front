<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\PresentChoiceController;
use App\Http\Controllers\PastChoiceController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\LotteryController;
use App\Http\Controllers\RealEstateController;
use App\Http\Controllers\LifeHappenController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/home/{gameSlug}', function ($gameSlug) {
    session(['gameSlug' => $gameSlug]);
    return redirect('/login');
});
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware(['auth'])->name('dashboard');

Route::get('/career/{activity_id}', [CareerController::class, 'get'])->name('getCareer');
Route::post('/career/{activity_id}', [CareerController::class, 'save'])->name('saveCareer');

Route::get('/present_choice/{activity_id}', [PresentChoiceController::class, 'get'])->name('getPresentChoice');
Route::post('/present_choice/{activity_id}', [PresentChoiceController::class, 'save'])->name('savePresentChoice');

Route::get('/past_choice/{activity_id}', [PastChoiceController::class, 'get'])->name('getPastChoice');
Route::post('/past_choice/{activity_id}', [PastChoiceController::class, 'save'])->name('savePastChoice');

Route::get('/insurance/{activity_id}', [InsuranceController::class, 'get'])->name('getInsurance');
Route::post('/insurance/{activity_id}', [InsuranceController::class, 'save'])->name('saveInsurance');
Route::post('/insurance-opt', [InsuranceController::class, 'saveOpt'])->name('saveInsuranceOpt');

Route::get('/lottery/{activity_id}', [LotteryController::class, 'get'])->name('getLottery');
Route::post('/lottery/{activity_id}', [LotteryController::class, 'save'])->name('saveLottery');
Route::post('/lottery-opt', [LotteryController::class, 'saveOpt'])->name('saveLotteryOpt');

Route::get('/realestate/{activity_id}', [RealEstateController::class, 'get'])->name('getRealestate');
Route::get('/realestate/{id}/{activity_id}', [RealEstateController::class, 'assign'])->name('getRealestate');
Route::post('/realestate/{activity_id}', [RealEstateController::class, 'save'])->name('saveRealestate');
Route::post('/bidwinner', [RealEstateController::class, 'bidWinner'])->name('bidWinner');
Route::post('/sell_property', [RealEstateController::class, 'sell'])->name('sell');

Route::get('/lifehappens/{activity_id}/{slug?}', [LifeHappenController::class, 'get'])->name('getLifeHappen');
Route::post('/lifehappens/{activity_id}', [LifeHappenController::class, 'save'])->name('saveLifeHappen');

Route::get('/nextround', [DashboardController::class, 'nextRound'])->name('nextRound');

Route::post('/lifeaction', [LifeHappenController::class, 'action'])->name('LifeAction');

Route::post('/roll', [DashboardController::class, 'roll'])->name('roll');
Route::post('/roll_decision', [DashboardController::class, 'rollDecision'])->name('roll');

Route::get('/test', [DashboardController::class, 'test'])->name('test');

require __DIR__.'/auth.php';
