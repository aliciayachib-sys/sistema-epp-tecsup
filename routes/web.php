<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\EppController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\OrganizadorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\EntregaController;
use App\Http\Middleware\CheckFirstLogin;


Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::post('/', function (Request $request) {

    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $remember = $request->has('remember');

    if (Auth::attempt($credentials, $remember)) {

        $request->session()->regenerate();

        if (Auth::user()->created_at->eq(Auth::user()->updated_at)) {
            return redirect()->route('primer.ingreso');
        }

        return redirect()->route('dashboard');
    }

    return back()->withErrors([
        'email' => 'Acceso denegado. Verifique sus credenciales.',
    ])->onlyInput('email');

})->name('login.post');


Route::get('password/reset', [App\Http\Controllers\PasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [App\Http\Controllers\PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [App\Http\Controllers\PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [App\Http\Controllers\PasswordController::class, 'reset'])->name('password.update');


Route::middleware(['auth'])->group(function () {

    Route::get('/primer-ingreso', function () {
        return view('auth.primer-ingreso');
    })->name('primer.ingreso');

    Route::post('/primer-ingreso', [ProfileController::class, 'actualizarPasswordInicial'])
        ->name('primer.ingreso.update');
});


Route::middleware(['auth', 'isAdmin'])->group(function () {

    Route::middleware(CheckFirstLogin::class)->group(function () {


        Route::post('/logout', function (Request $request) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login');
        })->name('logout');


        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        Route::get('/perfil', [ProfileController::class, 'show'])->name('perfil.show');
        Route::post('/perfil/datos', [ProfileController::class, 'actualizarDatosPersonales'])->name('perfil.actualizar-datos');
        Route::post('/perfil/email', [ProfileController::class, 'actualizarEmail'])->name('perfil.actualizar-email');
        Route::post('/perfil/contrasena', [ProfileController::class, 'cambiarContrasena'])->name('perfil.cambiar-contrasena');


        Route::post('/epps/proceso-importacion', [EppController::class, 'import'])->name('epps.import_excel');
        Route::delete('/epps-truncate', [EppController::class, 'clearAll'])->name('epps.clearAll');
        Route::resource('epps', EppController::class);


        Route::resource('categorias', CategoriaController::class);


        Route::post('/personals/import_excel', [PersonalController::class, 'importExcel'])->name('personals.import_excel');
        Route::post('/personals/importar', [PersonalController::class, 'import'])->name('personals.import');
        Route::delete('/personals/delete-all', [PersonalController::class, 'deleteAll'])->name('personals.delete_all');
        Route::post('/personals/delete-multiple', [PersonalController::class, 'deleteMultiple'])->name('personals.delete_multiple');
        Route::resource('personals', PersonalController::class);


        Route::delete('/departamentos-destroy-all', [DepartamentoController::class, 'destroyAll'])->name('departamentos.destroy_all');
        Route::delete('/departamentos-destroy-selected', [DepartamentoController::class, 'destroySelected'])->name('departamentos.destroy_selected');
        Route::post('/departamentos/{id}/asignar-masivo', [DepartamentoController::class, 'asignarMasivo'])->name('departamentos.asignar_masivo');
        Route::resource('departamentos', DepartamentoController::class);


        Route::post('/entregas/asignar-masivo', [EntregaController::class, 'asignarMasivo'])->name('entregas.asignar_masivo');
        Route::resource('entregas', EntregaController::class);



        // --- AGREGAR ESTO AQUÍ ---
// Ruta para obtener la matriz de EPP por taller (AJAX)
Route::get('/api/matriz-taller/{taller}', [EntregaController::class, 'getMatrizPorTaller'])
    ->name('entregas.matriz_taller');


        Route::get('/asignaciones', [AsignacionController::class, 'index'])->name('asignaciones.index');
        Route::post('/asignaciones/entregar', [AsignacionController::class, 'store'])->name('asignaciones.store');
        Route::delete('/asignaciones/{id}', [AsignacionController::class, 'destroy'])->name('asignaciones.destroy');
        Route::put('/asignaciones/{id}/devolver', [AsignacionController::class, 'devolver'])->name('asignaciones.devolver');
        Route::put('/asignaciones/{id}/incidencia', [AsignacionController::class, 'reportarIncidencia'])->name('asignaciones.incidencia');


        Route::get('/organizador', [OrganizadorController::class, 'index'])->name('organizador.index');
        Route::post('/organizador/asignar', [OrganizadorController::class, 'asignarMasivo'])->name('organizador.asignar');


        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/stock', [ReporteController::class, 'stock'])->name('reportes.stock');
        Route::get('/reportes/departamento', [ReporteController::class, 'porDepartamento'])->name('reportes.departamento');
        Route::get('/reportes/incidencias', [ReporteController::class, 'incidencias'])->name('reportes.incidencias');
        Route::get('/reportes/vida-util', [ReporteController::class, 'vidaUtil'])->name('reportes.vida_util');


        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');

    });

});
