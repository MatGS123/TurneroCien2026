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

use Illuminate\Http\Request;


Auth::routes();

Route::get('/',[FrontendController::class,'index'])->name('home');

Route::middleware(['auth'])->group(function () {

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

Route::prefix('estudios')->name('estudios.')->group(function () {
        Route::get('/', [EstudioController::class, 'index'])->name('index');
        Route::post('/', [EstudioController::class, 'store'])->name('store');
        Route::get('/{estudio}/descargar', [EstudioController::class, 'descargar'])->name('descargar');
    });


    //user
    Route::resource('user',UserController::class)->middleware('permission:users.view| users.create | users.edit | users.delete');
    //update user password

    //profile page
    Route::get('profile',[ProfileController::class,'index'])->name('profile');
    //user profile update
    Route::patch('profile-update/{user}',[ProfileController::class,'profileUpdate'])->name('user.profile.update');
    Route::patch('user/pasword-update/{user}',[UserController::class,'password_update'])->name('user.password.update');
    Route::put('user/profile-pic/{user}',[UserController::class,'updateProfileImage'])->name('user.profile.image.update');

    //delete profile image
    Route::patch('delete-profile-image/{user}',[UserController::class,'deleteProfileImage'])->name('delete.profile.image');
    //trash view for users
    Route::get('user-trash', [UserController::class, 'trashView'])->name('user.trash');
    Route::get('user-restore/{id}', [UserController::class, 'restore'])->name('user.restore');
    //deleted permanently
    Route::delete('user-delete/{id}', [UserController::class, 'force_delete'])->name('user.force.delete');

    Route::get('settings', [SettingController::class, 'index'])->name('setting')->middleware('permission:setting update');
    Route::post('settings/{setting}', [SettingController::class, 'update'])->name('setting.update');


    Route::resource('category', CategoryController::class)->middleware('permission:categories.view| categories.create | categories.edit | categories.delete');


    // Services
    Route::resource('service', ServiceController::class)->middleware('permission:services.view| services.create | services.edit | services.delete');
    Route::get('service-trash', [ServiceController::class, 'trashView'])->name('service.trash');
    Route::get('service-restore/{id}', [ServiceController::class, 'restore'])->name('service.restore');
    //deleted permanently
    Route::delete('service-delete/{id}', [ServiceController::class, 'force_delete'])->name('service.force.delete');


    //summernote image
    Route::post('summernote',[SummerNoteController::class,'summerUpload'])->name('summer.upload.image');
    Route::post('summernote/delete',[SummerNoteController::class,'summerDelete'])->name('summer.delete.image');


    //employee
    // Route::resource('user',UserController::class);
    Route::get('employee-booking',[UserController::class,'EmployeeBookings'])->name('employee.bookings');
    Route::get('my-booking/{id}',[UserController::class,'show'])->name('employee.booking.detail');

    // employee profile self data update
    Route::patch('employe-profile-update/{employee}',[ProfileController::class,'employeeProfileUpdate'])->name('employee.profile.update');

    //employee bio
    Route::put('employee-bio/{employee}',[EmployeeController::class,'updateBio'])->name('employee.bio.update');





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
Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments')->middleware('permission:appointments.view| appointments.create | services.appointments | appointments.delete');

Route::post('/appointments/update-status', [AppointmentController::class, 'updateStatus'])->name('appointments.update.status');

//update status from dashbaord
Route::post('/update-status', [DashboardController::class, 'updateStatus'])->name('dashboard.update.status');
