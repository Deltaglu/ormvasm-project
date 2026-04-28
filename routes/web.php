<?php

use App\Http\Controllers\AgriculteurController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PrestationController;
use App\Http\Controllers\QuittanceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TitreRecetteController;
use App\Exports\AgriculteursExport;
use App\Exports\PrestationsExport;
use App\Exports\TitresRecettesExport;
use App\Exports\PaiementsExport;
use App\Exports\QuittancesExport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    
    Route::get('/trash', [App\Http\Controllers\TrashController::class, 'index'])->name('trash.index');
    Route::get('/global-search', [App\Http\Controllers\GlobalSearchController::class, 'search'])->name('global-search');
    Route::get('/ai-assistant/ask', [App\Http\Controllers\AiAssistantController::class, 'ask'])->name('ai.ask');
    Route::post('/trash/restore/{type}/{id}', [App\Http\Controllers\TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('/trash/force-delete/{type}/{id}', [App\Http\Controllers\TrashController::class, 'forceDelete'])->name('trash.force-delete');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('agriculteurs/search', [AgriculteurController::class, 'search'])->name('agriculteurs.search');
    Route::get('agriculteurs/export', function() {
        return Excel::download(new AgriculteursExport, 'agriculteurs.xlsx');
    })->name('agriculteurs.export');
    Route::get('/agriculteurs/{agriculteur}/releve', [App\Http\Controllers\AgriculteurController::class, 'releve'])
        ->name('agriculteurs.releve')
        ->withTrashed();
    Route::resource('agriculteurs', App\Http\Controllers\AgriculteurController::class)->withTrashed();

    Route::get('prestations/search', [PrestationController::class, 'search'])->name('prestations.search');
    Route::get('prestations/export', function() {
        return Excel::download(new PrestationsExport, 'prestations.xlsx');
    })->name('prestations.export');
    Route::resource('prestations', PrestationController::class);

    Route::get('titres-recettes/search', [TitreRecetteController::class, 'search'])->name('titres-recettes.search');
    Route::get('titres-recettes/export', function() {
        return Excel::download(new TitresRecettesExport, 'titres-recettes.xlsx');
    })->name('titres-recettes.export');
    Route::resource('titres-recettes', TitreRecetteController::class)
        ->parameters(['titres-recettes' => 'titres_recette']);

    Route::get('paiements/export', function() {
        return Excel::download(new PaiementsExport, 'paiements.xlsx');
    })->name('paiements.export');
    Route::resource('paiements', PaiementController::class)->withTrashed();

    Route::get('quittances/search', [QuittanceController::class, 'search'])->name('quittances.search');
    Route::get('quittances/export', function() {
        return Excel::download(new QuittancesExport, 'quittances.xlsx');
    })->name('quittances.export');
    Route::get('quittances/rg8', [QuittanceController::class, 'rg8'])->name('quittances.rg8');
    Route::get('quittances', [QuittanceController::class, 'index'])->name('quittances.index');
    Route::get('quittances/{quittance}', [QuittanceController::class, 'show'])->name('quittances.show')->withTrashed();
    Route::get('quittances/{quittance}/pdf', [QuittanceController::class, 'pdf'])->name('quittances.pdf')->withTrashed();
    Route::get('quittances/{quittance}/pdf/download', [QuittanceController::class, 'download'])->name('quittances.download');
});