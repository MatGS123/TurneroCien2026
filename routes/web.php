<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SummerNoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstudioController;
use App\Http\Controllers\ObraSocialController;
use App\Http\Controllers\AiSuiteController;
use App\Http\Controllers\ConsultantController;
use App\Http\Controllers\ScribeController;

use Illuminate\Http\Request;


Auth::routes();

Route::get('/',[FrontendController::class,'index'])->name('home');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:superadmin|secretarias|empleado');

    Route::prefix('suite-ia')->name('ai.')->middleware('role:superadmin|empleado')->group(function () {
        Route::get('/', [AiSuiteController::class, 'index'])->name('suite.index');
        Route::get('/researcher', [AiSuiteController::class, 'researcher'])->name('researcher');
        Route::post('/researcher/ask', [AiSuiteController::class, 'askResearcher'])->name('researcher.ask');
        Route::get('/consultant/documents', [ConsultantController::class, 'index'])->name('consultant.documents');
        Route::get('/consultant', [ConsultantController::class, 'index'])->name('consultant');
        Route::get('/consultant/studies', [ConsultantController::class, 'studies'])->name('consultant.studies');
        Route::post('/consultant/analyze', [ConsultantController::class, 'analyze'])->name('consultant.analyze');
        Route::get('/scribe', [ScribeController::class, 'index'])->name('scribe');
        Route::post('/scribe/transcribe', [ScribeController::class, 'transcribe'])->name('scribe.transcribe');
        Route::get('/scribe/notes/{clinicalNote}', [ScribeController::class, 'status'])->name('scribe.status');
        Route::post('/scribe/notes/{clinicalNote}/organize', [ScribeController::class, 'organize'])->name('scribe.organize');
        Route::post('/scribe/notes/{clinicalNote}/confirm', [ScribeController::class, 'confirm'])->name('scribe.confirm');
        Route::post('/scribe/notes/{clinicalNote}/export', [ScribeController::class, 'export'])->name('scribe.export');
        Route::get('/scribe/notes/{clinicalNote}/download/{format}', [ScribeController::class, 'download'])->name('scribe.download');
    });

    Route::prefix('obras-sociales')->name('obras-sociales.')->group(function () {
        Route::get('/',                                    [ObraSocialController::class, 'index'])->name('index');
        Route::get('/create',                              [ObraSocialController::class, 'create'])->name('create');
        Route::post('/',                                   [ObraSocialController::class, 'store'])->name('store');
        Route::get('/{obraSocial}/edit',                   [ObraSocialController::class, 'edit'])->name('edit');
        Route::put('/{obraSocial}',                        [ObraSocialController::class, 'update'])->name('update');
        Route::delete('/{obraSocial}',                     [ObraSocialController::class, 'destroy'])->name('destroy');
        Route::post('/{obraSocial}/coseguros',             [ObraSocialController::class, 'storeCoseguro'])->name('coseguros.store');
        Route::delete('/{obraSocial}/coseguros/{coseguro}',[ObraSocialController::class, 'destroyCoseguro'])->name('coseguros.destroy');
        Route::get('/listar',                              [ObraSocialController::class, 'listar'])->name('listar');
    });

    Route::prefix('estudios')->name('estudios.')->middleware('role:superadmin|empleado')->group(function () {
        Route::get('/', [EstudioController::class, 'index'])->name('index');
        Route::post('/', [EstudioController::class, 'store'])->name('store');
        Route::get('/{estudio}/descargar', [EstudioController::class, 'descargar'])->name('descargar');
    });


    //user
    Route::resource('user',UserController::class)->middleware('role:superadmin|secretarias');
    //update user password

    //profile page
    Route::get('profile',[ProfileController::class,'index'])->name('profile')->middleware('role:superadmin|empleado');
    //user profile update
    Route::patch('profile-update/{user}',[ProfileController::class,'profileUpdate'])->name('user.profile.update')->middleware('role:superadmin|empleado');
    Route::patch('user/pasword-update/{user}',[UserController::class,'password_update'])->name('user.password.update');
    Route::put('user/profile-pic/{user}',[UserController::class,'updateProfileImage'])->name('user.profile.image.update');

    //delete profile image
    Route::patch('delete-profile-image/{user}',[UserController::class,'deleteProfileImage'])->name('delete.profile.image');
    //trash view for users
    Route::get('user-trash', [UserController::class, 'trashView'])->name('user.trash')->middleware('role:superadmin|secretarias');
    Route::get('user-restore/{id}', [UserController::class, 'restore'])->name('user.restore')->middleware('role:superadmin|secretarias');
    //deleted permanently
    Route::delete('user-delete/{id}', [UserController::class, 'force_delete'])->name('user.force.delete')->middleware('role:superadmin|secretarias');

    Route::get('settings', [SettingController::class, 'index'])->name('setting')->middleware('role:superadmin');
    Route::post('settings/{setting}', [SettingController::class, 'update'])->name('setting.update')->middleware('role:superadmin');


    Route::resource('category', CategoryController::class)->middleware('role:superadmin|secretarias');


    // Services
    Route::resource('service', ServiceController::class)->middleware('role:superadmin|secretarias');
    Route::get('service-trash', [ServiceController::class, 'trashView'])->name('service.trash');
    Route::get('service-restore/{id}', [ServiceController::class, 'restore'])->name('service.restore');
    //deleted permanently
    Route::delete('service-delete/{id}', [ServiceController::class, 'force_delete'])->name('service.force.delete');


    //summernote image
    Route::post('summernote',[SummerNoteController::class,'summerUpload'])->name('summer.upload.image');
    Route::post('summernote/delete',[SummerNoteController::class,'summerDelete'])->name('summer.delete.image');


    //employee
    // Route::resource('user',UserController::class);
    Route::get('employee-booking',[UserController::class,'EmployeeBookings'])->name('employee.bookings')->middleware('role:empleado');
    Route::get('my-booking/{id}',[UserController::class,'show'])->name('employee.booking.detail')->middleware('role:empleado');

    // employee profile self data update
    Route::patch('employe-profile-update/{employee}',[ProfileController::class,'employeeProfileUpdate'])->name('employee.profile.update')->middleware('role:empleado');

    //employee bio
    Route::put('employee-bio/{employee}',[EmployeeController::class,'updateBio'])->name('employee.bio.update')->middleware('role:empleado');





    Route::get('test',function(Request $request){
        return view('test',  [
            'request' => $request
        ]);
    });



    Route::post('test', function (Request $request) {
        dd($request->all())->toArray();
    })->name('test');

});



//frontend routes
//fetch services from categories (usado por el flujo viejo, se deja por compatibilidad)
Route::get('/categories/{category}/services', [FrontendController::class, 'getServices'])->name('get.services');

//fetch employee from category (usado por el flujo viejo, se deja por compatibilidad)
Route::get('/services/{service}/employees', [FrontendController::class, 'getEmployees'])->name('get.employees');

// NUEVO: categorías disponibles para un profesional (paso 2 del flujo CIEN)
Route::get('/employees/{employee}/categories', [FrontendController::class, 'getEmployeeCategories'])->name('get.employee.categories');

// NUEVO: servicios de un profesional dentro de una categoría (paso 3 del flujo CIEN)
Route::get('/employees/{employee}/categories/{category}/services', [FrontendController::class, 'getEmployeeServices'])->name('get.employee.services');

//get availibility
Route::get('/employees/{employee}/availability/{date?}', [FrontendController::class, 'getEmployeeAvailability'])
->name('employee.availability');

//create appointment
Route::post('/bookings', [AppointmentController::class, 'store'])->name('bookings.store');
Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments')->middleware(['auth', 'role:superadmin|secretarias|empleado']);

Route::post('/appointments/update-status', [AppointmentController::class, 'updateStatus'])->name('appointments.update.status')->middleware(['auth', 'role:superadmin|secretarias|empleado']);

//update status from dashbaord
Route::post('/update-status', [DashboardController::class, 'updateStatus'])->name('dashboard.update.status')->middleware(['auth', 'role:superadmin|secretarias|empleado']);
