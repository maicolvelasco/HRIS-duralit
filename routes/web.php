<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AssistanceController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\HrManagerController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PermissionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    /* ---------- SETTINGS ---------- */
    Route::controller(SettingsController::class)->prefix('settings')->name('settings.')->group(function () {

        /* Vista principal */
        Route::get('/', 'index')->name('index');

        /* Personas */
        Route::post('/personas', 'storePerson')->name('personas.store');
        Route::put('/personas/{user}', 'updatePerson')->name('personas.update');
        Route::get('/personas/{user}', 'getPerson')->name('personas.show');

        // Roles
        Route::post('/roles', [SettingsController::class, 'storeRol'])->name('roles.store');
        Route::get('/roles/{rol}', [SettingsController::class, 'getRol'])->name('roles.get');
        Route::put('/roles/{rol}', [SettingsController::class, 'updateRol'])->name('roles.update');

        /* Sucursales */
        Route::post('/sucursales', 'storeBranch')->name('branches.store');
        Route::put('/sucursales/{branch}', 'updateBranch')->name('branches.update');
        Route::get('/sucursales/{branch}', 'getBranch')->name('branches.show');

        /* Grupos */
        Route::post('/grupos', 'storeGroup')->name('groups.store');
        Route::put('/grupos/{group}', 'updateGroup')->name('groups.update');
        Route::get('/grupos/{group}', 'getGroup')->name('groups.show');

        /* Secciones */
        Route::post('/secciones', 'storeSection')->name('sections.store');
        Route::put('/secciones/{section}', 'updateSection')->name('sections.update');
        Route::get('/secciones/{section}', 'getSection')->name('sections.show');

        // RUTAS NUEVAS PARA PERMISOS
        Route::post('/permissions', [SettingsController::class, 'storePermission'])->name('permissions.store');
        Route::put('/permissions/{permission}', [SettingsController::class, 'updatePermission'])->name('permissions.update');
        Route::get('/permissions/{permission}', [SettingsController::class, 'getPermission'])->name('permissions.get');
        
        // PERMISOS POR USUARIO - RUTA CORREGIDA
        Route::post('/user-permissions/{user}/toggle', 'toggleUserPermission')
         ->name('user-permissions.toggle');

        Route::post('/shifts', [SettingsController::class, 'storeShift'])->name('shifts.store');
        Route::get('/shifts/{shift}', [SettingsController::class, 'getShift'])->name('shifts.get');
        Route::put('/shifts/{shift}', [SettingsController::class, 'updateShift'])->name('shifts.update');

        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
        Route::get('/locations', [HolidayController::class, 'getLocations'])->name('holidays.locations');

        // Autorizaciones / Permisos de Trabajo
        Route::get('/authorizations/{authorization}/edit', [SettingsController::class, 'getAuthorization'])->name('authorizations.edit');
        Route::post('/authorizations', [SettingsController::class, 'storeAuthorization'])->name('authorizations.store');
        Route::put('/authorizations/{authorization}', [SettingsController::class, 'updateAuthorization'])->name('authorizations.update');
    
        Route::get('/reports/generate', [SettingsController::class, 'generateReport'])->name('reports.generate');
    });

    /* ---------- ASISTENCIAS ---------- */
    Route::get('/assistances', [AssistanceController::class, 'index'])->name('assistances.index');
    Route::post('/assistances/entrada-manual', [AssistanceController::class, 'storeEntradaManual'])->name('assistances.entrada.manual');
    Route::patch('/assistances/{assistance}/salida-manual', [AssistanceController::class, 'storeSalidaManual'])->name('assistances.salida.manual');

    Route::get('/rrhh', [HrManagerController::class, 'index'])->name('rrhh.index');
    Route::post('/rrhh', [HrManagerController::class, 'store'])->name('rrhh.store');
    Route::put('/rrhh', [HrManagerController::class, 'update'])->name('rrhh.update');

    /* ---------- PERMISOS DE TRABAJO ---------- */
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    Route::post('/permissions/{permission}/status', [PermissionController::class, 'updateStatus'])->name('permissions.update-status');

    // NUEVA RUTA PARA OBTENER TITULACIONES
    Route::get('/permissions/authorizations/{authorization}/titulations', [PermissionController::class, 'getTitulationsByAuthorization'])->name('permissions.authorizations.titulations');

    Route::prefix('group-managers')->name('group-managers.')->group(function () {
        Route::post('/assign', [HrManagerController::class, 'assignManager'])->name('assign');
        Route::post('/change', [HrManagerController::class, 'changeManager'])->name('change');
        Route::post('/remove-user', [HrManagerController::class, 'removeUserFromGroup'])->name('remove-user');
        Route::post('/add-users', [HrManagerController::class, 'addUsersByFilter'])->name('add-users');
    });

    Route::get('/overtimes', [OvertimeController::class, 'index'])->name('overtimes.index');
    Route::post('/overtimes', [OvertimeController::class, 'store'])->name('overtimes.store');
    Route::post('/overtimes/{overtime}/update-status', [OvertimeController::class, 'updateStatus'])->name('overtimes.update-status');
    Route::get('/overtimes/team', [OvertimeController::class, 'team'])->name('overtimes.team');

    Route::get('/permissions/team', [PermissionController::class, 'team'])->name('permissions.team');
    Route::post('/permissions/{permission}/update-status', [PermissionController::class, 'updateStatus'])->name('permissions.update-status');
    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
