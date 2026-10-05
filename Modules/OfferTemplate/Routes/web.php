<?php

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

use Illuminate\Support\Facades\Route;
use Modules\OfferTemplate\Http\Controllers\OfferTemplateController;

Route::group([
    'prefix' => 'admin/offer/template'
], function () {
    Route::get('/', [OfferTemplateController::class, 'index'])->name('admin.offer.template.index');
    Route::get('create', [OfferTemplateController::class, 'create'])->name('admin.offer.template.create');
    Route::get('edit/{id}', [OfferTemplateController::class, 'edit'])->name('admin.offer.template.edit');
    Route::post('update/{id}', [OfferTemplateController::class, 'update'])->name('admin.offer.template.update');
    Route::get('show/{id}', [OfferTemplateController::class, 'show'])->name('admin.offer.template.show');
    Route::post('/convert', [OfferTemplateController::class, 'convertDoc'])->name('admin.offer.template.convert');
    Route::post('/save-html', [OfferTemplateController::class, 'saveHtml'])->name('admin.offer.template.saveHtml');
});
