<?php
namespace App\Http\Controllers;
use App\Models\Estudio;
use App\Models\Appointment;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EstudioController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('employee')) {
            $employee = Employee::where('user_id', $user->id)->first();
            $employeeId = $employee?->id;

            // 1. Solo los estudios de turnos que le pertenecen a este profesional
            $estudios = Estudio::with('appointment')
            ->whereHas('appointment', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->latest()
            ->get();

            // 2. El select del formulario de carga también se limita a sus propios turnos
            $appointments = Appointment::where('employee_id', $employeeId)->latest()->get();
        } else {
            // admin / moderator siguen viendo todo
            $estudios = Estudio::with('appointment')->latest()->get();
            $appointments = Appointment::latest()->get();
        }

        return view('backend.estudios.index', compact('estudios', 'appointments'));
    }

    public function store(Request $request)
    {
        // 1. Validamos que los datos cumplan con lo requerido (PDF obligatorio, máx 10MB)
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'nombre_estudio' => 'required|string|max:255',
            'archivo_pdf'    => 'required|mimes:pdf|max:10000',
        ]);

        $user = auth()->user();

        // 2. Si es un médico, verificamos que el turno elegido sea realmente suyo
        //    (protege contra manipular el <select> o pegar el request a mano)
        if ($user->hasRole('employee')) {
            $employee = Employee::where('user_id', $user->id)->first();
            $appointment = Appointment::findOrFail($request->appointment_id);

            if (!$employee || $appointment->employee_id !== $employee->id) {
                abort(403, 'No podés subir estudios de pacientes que no son tuyos.');
            }
        }

        // 3. Guardamos el archivo físico en la carpeta 'storage/app/public/estudios'
        $ruta = $request->file('archivo_pdf')->store('estudios', 'public');

        // 4. Guardamos el registro en la base de datos con la ruta del archivo
        Estudio::create([
            'appointment_id' => $request->appointment_id,
            'nombre_estudio' => $request->nombre_estudio,
            'archivo_pdf'    => $ruta,
            'observaciones'  => $request->observaciones,
        ]);

        return redirect()->back()->with('success', 'Estudio subido exitosamente.');
    }

    public function descargar(Estudio $estudio)
    {
        $user = auth()->user();

        // Si es médico, solo puede descargar estudios de sus propios turnos
        if ($user->hasRole('employee')) {
            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee || $estudio->appointment->employee_id !== $employee->id) {
                abort(403, 'No tenés permiso para descargar este estudio.');
            }
        }

        // Retorna el archivo PDF para forzar la descarga directa en el navegador
        return Storage::disk('public')->download($estudio->archivo_pdf, $estudio->nombre_estudio . '.pdf');
    }
}
