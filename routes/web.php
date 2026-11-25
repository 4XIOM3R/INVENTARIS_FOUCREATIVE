<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\ClientOnboardingController;
use App\Http\Controllers\ExportDocumentController;

Route::redirect('/', '/login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/clients', [AdminDashboardController::class, 'store'])->name('admin.clients.store');
    Route::get('/clients/{client}', [AdminClientController::class, 'show'])->name('admin.clients.show');
    Route::put('/clients/{client}', [AdminClientController::class, 'update'])->name('admin.clients.update');
    Route::delete('/clients/{client}', [AdminClientController::class, 'destroy'])->name('admin.clients.destroy');
    Route::put('/clients/{client}/status', [AdminClientController::class, 'updateStatus'])->name('admin.clients.status');
    Route::put('/clients/{client}/modules/{module}/review', [AdminClientController::class, 'reviewModule'])->whereNumber('module')->name('admin.clients.modules.review');
    Route::post('/clients/{client}/kblis', [AdminClientController::class, 'storeKbli'])->name('admin.clients.kblis.store');
    Route::put('/clients/{client}/kblis/{kbli}', [AdminClientController::class, 'updateKbli'])->name('admin.clients.kblis.update');
    Route::delete('/clients/{client}/kblis/{kbli}', [AdminClientController::class, 'destroyKbli'])->name('admin.clients.kblis.destroy');
    Route::post('/clients/{client}/items', [AdminClientController::class, 'storeItem'])->name('admin.clients.items.store');
    Route::put('/clients/{client}/items/{item}', [AdminClientController::class, 'updateItem'])->name('admin.clients.items.update');
    Route::delete('/clients/{client}/items/{item}', [AdminClientController::class, 'destroyItem'])->name('admin.clients.items.destroy');
    Route::delete('/clients/{client}/items/{item}/file', [AdminClientController::class, 'destroyItemFile'])->name('admin.clients.items.file.destroy');
    Route::post('/clients/{client}/credentials', [AdminClientController::class, 'storeCredential'])->name('admin.clients.credentials.store');
    Route::put('/clients/{client}/credentials/{credential}', [AdminClientController::class, 'updateCredential'])->name('admin.clients.credentials.update');
    Route::delete('/clients/{client}/credentials/{credential}', [AdminClientController::class, 'destroyCredential'])->name('admin.clients.credentials.destroy');
    Route::post('/clients/{client}/knowledge-bases', [AdminClientController::class, 'storeKnowledgeBase'])->name('admin.clients.knowledge.store');
    Route::put('/clients/{client}/knowledge-bases/{knowledgeBase}', [AdminClientController::class, 'updateKnowledgeBase'])->name('admin.clients.knowledge.update');
    Route::delete('/clients/{client}/knowledge-bases/{knowledgeBase}', [AdminClientController::class, 'destroyKnowledgeBase'])->name('admin.clients.knowledge.destroy');
    Route::post('/clients/{client}/checklists', [AdminClientController::class, 'storeChecklist'])->name('admin.clients.checklists.store');
    Route::put('/clients/{client}/checklists/{checklist}', [AdminClientController::class, 'updateChecklist'])->name('admin.clients.checklists.update');
    Route::delete('/clients/{client}/checklists/{checklist}', [AdminClientController::class, 'destroyChecklist'])->name('admin.clients.checklists.destroy');
    Route::get('/clients/{client}/export-pdf', [ExportDocumentController::class, 'download'])->name('admin.clients.export-pdf');
});

Route::prefix('onboarding')->middleware('verify.onboarding.token')->group(function () {
    Route::get('/{token}', [ClientOnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/{token}/profile', [ClientOnboardingController::class, 'profile'])->name('onboarding.profile');
    Route::post('/{token}/legal', [ClientOnboardingController::class, 'legal'])->name('onboarding.legal');
    Route::post('/{token}/contact', [ClientOnboardingController::class, 'contact'])->name('onboarding.contact');
    Route::post('/{token}/credentials', [ClientOnboardingController::class, 'credentials'])->name('onboarding.credentials');
    Route::post('/{token}/billing', [ClientOnboardingController::class, 'billing'])->name('onboarding.billing');
    Route::post('/{token}/assets', [ClientOnboardingController::class, 'assets'])->name('onboarding.assets');
    Route::post('/{token}/campaign', [ClientOnboardingController::class, 'campaign'])->name('onboarding.campaign');
    Route::post('/{token}/ecosystem', [ClientOnboardingController::class, 'ecosystem'])->name('onboarding.ecosystem');
    Route::post('/{token}/portfolio', [ClientOnboardingController::class, 'portfolio'])->name('onboarding.portfolio');
    Route::post('/{token}/submit', [ClientOnboardingController::class, 'submit'])->name('onboarding.submit');
    Route::post('/{token}/autosave/{module}', [ClientOnboardingController::class, 'autosave'])->whereNumber('module')->name('onboarding.autosave');
});
