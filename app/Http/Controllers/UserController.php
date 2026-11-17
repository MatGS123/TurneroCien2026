<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Service;
use App\Models\Holiday;
use App\Models\Employee;
use Hash;
use Session;
class UserController extends Controller
{


    public function index(Request $request)
    {
        // Get the role type from the request (either 'employee', 'customer', or 'moderator')
        $query = User::query();

        if (auth()->user()->hasRole('secretarias') && !auth()->user()->hasRole('superadmin')) {
            $query->whereDoesntHave('roles', function ($roleQuery) {
                $roleQuery->where('name', 'superadmin');
            });
        }

        $users = $query->latest()->get();
        return view('backend.user.index', compact('users'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $days = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];

        $allowedRoles = auth()->user()->hasRole('superadmin')
            ? ['superadmin', 'secretarias', 'empleado']
            : ['secretarias', 'empleado'];
        $roles = Role::whereIn('name', $allowedRoles)->orderBy('name')->get();
        $services = Service::whereStatus(1)->get();
        return view('backend.user.create',compact('roles','services','days'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //dd($request->all());
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'roles' => ['required', 'array', 'size:1'],
            'roles.*' => ['required', Rule::in(auth()->user()->hasRole('superadmin')
                ? ['superadmin', 'secretarias', 'empleado']
                : ['secretarias', 'empleado'])],
            'service' => 'nullable',
            'slot_duration' => 'nullable',
            'break_duration' => 'nullable',
            'break' => 'nullable',
            'days' => 'nullable',
            'is_employee' => 'nullable',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'] ?? "", // Optional field
            'email_verified_at' => now(),
            'password' => \Hash::make($data['password']),
        ]);

        // Assign the role to the user
        $selectedRole = $data['roles'][0];
        $user->assignRole($selectedRole);


        // transform time slots into from and to combination
        if($selectedRole === 'empleado')
        {
            $transformedData = $this->transformOpeningHours($data['days']); // Use $this->transformOpeningHours
            $data['days'] = $transformedData;

            $employee = Employee::create([
                'user_id'           => $user['id'],
                'days'              => $data['days'],
                'slot_duration'     => $data['slot_duration'],
                'break_duration'    => $data['break_duration'],
            ]);

            $employee->services()->attach($data['service']);
        }

        return redirect()->back()->withSuccess('¡El usuario se creó correctamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $targetUser = User::findOrFail($id);
        abort_if($targetUser->hasRole('superadmin') && !auth()->user()->hasRole('superadmin'), 403);


        // Available days of the week
        $days = [
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
        ];

        // Available slot duration steps
        $steps = ['10', '15', '20', '30', '45', '60'];

        // Available break duration steps
        $breaks = ['5', '10', '15', '20', '25', '30'];

        // Get the user and the related employee data
        $user = User::with('employee.holidays')->findOrFail($id);

        //dd($user->employee->holidays);

        // Get the employee's availability (days) data if it exists and convert to an array
        $employeeDays = $user->employee->days ?? [];

        // Transform availability slots
        $employeeDays = $this->transformAvailabilitySlotsForEdit($employeeDays);

        //dd($employeeDays);

        $allowedRoles = auth()->user()->hasRole('superadmin')
            ? ['superadmin', 'secretarias', 'empleado']
            : ['secretarias', 'empleado'];
        $roles = Role::whereIn('name', $allowedRoles)->orderBy('name')->get();

        // Get all active services
        $services = Service::whereStatus(1)->get();

        // Return the view with data
        return view('backend.user.edit', compact('user', 'roles', 'services', 'days', 'steps', 'breaks', 'employeeDays'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->hasRole('superadmin') && !auth()->user()->hasRole('superadmin'), 403);


        //dd($user);
        // Validate request data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'social.*' => 'sometimes',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => ['required', 'array', 'size:1'],
            'roles.*' => ['required', Rule::in(auth()->user()->hasRole('superadmin')
                ? ['superadmin', 'secretarias', 'empleado']
                : ['secretarias', 'empleado'])],
            'service' => 'nullable',
            'slot_duration' => function ($attribute, $value, $fail) use ($request) {
                // Check if 'is_employee' is true and 'slot_duration' is missing
                if (in_array('empleado', (array) $request->input('roles', []), true) && !$value) {
                    $fail('El campo ' . $attribute . ' es obligatorio cuando el usuario es un profesional.');
                }
                // If it's present, it should be numeric
                if ($value && !is_numeric($value)) {
                    $fail('El campo ' . $attribute . ' debe ser numérico.');
                }
            },
            'break_duration' => 'nullable',
            'days' => 'nullable',
            'status' => 'nullable|numeric',
            'is_employee' => 'nullable',
            'holidays.date.*' => 'sometimes|required',
            'holidays.from_time' => 'nullable',
            'holidays.to_time' => 'nullable',
            'holidays.recurring' => 'nullable',
        ]);

        // Block logged-in user from changing their own role or status
        // Block logged-in user from changing their own role or status
        if (\Auth::id() === $user->id) {
            // Check if roles key exists AND its value is different from current roles
            if ($request->filled('roles') && !$user->hasAnyRole($request->roles)) {
                return redirect()->back()->withErrors(['roles' => 'No podés cambiar tu propio rol.']);
            }

            if ($request->has('status') && $request->status != $user->status) {
                return redirect()->back()->withErrors(['status' => 'No podés cambiar tu propio estado.']);
            }

        }


        // Always keep 'admin' role for super admin (user ID 1)
        $roles = $data['roles'];
        $selectedRole = $roles[0];

        if ($user->id === 1 && $selectedRole !== 'superadmin') {
            return redirect()->back()->withErrors(['roles' => 'El primer usuario siempre debe conservar el rol de superadmin.']);
        }

        if ($user->hasRole('superadmin') && $selectedRole !== 'superadmin') {
            return redirect()->back()->withErrors(['roles' => 'El rol de superadmin no se puede quitar.']);
        }


        // Ensure that user ID 1's status always remains 1
        if ($user->id === 1) {
            $status = 1;
        } else {
            $status = $request->status ?? 0;
        }

        // Update user details
        $user->update([
            'name' => $request->name,
            'email' => $request->input('email') ?: null,
            'phone' => $request->phone ?? $user->phone,
            'password' => $request->password ? \Hash::make($request->password) : $user->password,
            'status' => $status,
        ]);

        // Sync roles: Always retain admin role for the first user
        $user->syncRoles($roles);



        // Check if is_employee is set and true
        if ($selectedRole === 'empleado') {
            // Transform days data if provided
            if (!empty($data['days'])) {
                $data['days'] = $this->transformOpeningHours($data['days']);
            }

            // Update or create Employee record
            $employee = Employee::updateOrCreate(
                ['user_id' => $user->id], // Condition to check
                [
                    'days' => $data['days'] ?? null,
                    'slot_duration' => $data['slot_duration'] ?? null,
                    'break_duration' => $data['break_duration'] ?? null
                ]
            );

            // Attach services if provided
            if (!empty($data['service'])) {
                $employee->services()->sync($data['service']); // Use sync to avoid duplicate entries
            }

            if ($request->has('holidays.date') && is_array($request->input('holidays.date'))) {
                // Get all existing holiday IDs for this employee
                $existingHolidayIds = $user->employee->holidays->pluck('id')->toArray();
                $submittedHolidayIds = [];

                $dates = $request->input('holidays.date');
                $fromTimes = $request->input('holidays.from_time');
                $toTimes = $request->input('holidays.to_time');
                $recurring = $request->input('holidays.recurring');
                $holidayIds = $request->input('holidays.id', []); // Add hidden input for holiday IDs in your form

                foreach ($dates as $index => $date) {
                    $holidayData = [
                        'employee_id' => $user->employee->id,
                        'hours' => isset($fromTimes[$index]) && isset($toTimes[$index])
                            ? [$fromTimes[$index] . '-' . $toTimes[$index]]
                            : [],
                        'recurring' => isset($recurring[$index]) && $recurring[$index] == 1,
                    ];

                    // Handle date format based on recurring
                    if ($holidayData['recurring']) {
                        $holidayData['date'] = \Carbon\Carbon::parse($date)->format('m-d');
                    } else {
                        $holidayData['date'] = $date;
                    }

                    // Check if this is an existing holiday (has an ID)
                    if (isset($holidayIds[$index])) {
                        $holiday = Holiday::find($holidayIds[$index]);
                        if ($holiday) {
                            $holiday->update($holidayData);
                            $submittedHolidayIds[] = $holiday->id;
                        }
                    } else {
                        // Create new holiday
                        $holiday = Holiday::create($holidayData);
                        $submittedHolidayIds[] = $holiday->id;
                    }
                }

                // Delete any holidays that weren't submitted in the form
                $holidaysToDelete = array_diff($existingHolidayIds, $submittedHolidayIds);
                if (!empty($holidaysToDelete)) {
                    Holiday::whereIn('id', $holidaysToDelete)->delete();
                }
            } else {
                // If no holidays were submitted but there were existing ones, delete them all
                if ($user->employee->holidays()->exists()) {
                    $user->employee->holidays()->delete();
                }
            }

        }


        return redirect()->route('user.index')->with('success', '¡El perfil se actualizó correctamente!');
    }



    // Custom method to log out a specific user
    protected function logoutUser(User $user)
    {
          // Check if the application is using the database session driver
        if (config('session.driver') === 'database') {
            // Delete all sessions for this user by matching the user ID
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Request $request)
    {
        if ($user->hasRole('superadmin') && !$request->user()->hasRole('superadmin')) {
            return back()->withErrors('Solo un superadmin puede eliminar a otro superadmin.');
        }

        if($user->id == 1)
        {
            return back()->withErrors('El primer administrador no se puede eliminar.');
        }

        if ($user->id === $request->user()->id) {
            return back()->withErrors('No podés eliminarte a vos mismo.');
        }

        $user->delete();
        return redirect()->back()->with('success', '¡El usuario se envió a la papelera correctamente!');
    }


    public function trashView(Request $request)
    {
        $query = User::onlyTrashed();

        if (auth()->user()->hasRole('secretarias') && !auth()->user()->hasRole('superadmin')) {
            $query->whereDoesntHave('roles', function ($roleQuery) {
                $roleQuery->where('name', 'superadmin');
            });
        }

        $users = $query->latest()->get();
        return view('backend.user.trash',compact('users'));
    }

    // restore data
    public function restore($id)
    {
        $user = User::withTrashed()->find($id);
        if ($user) {
            $this->protectSuperadminAccount($user);
        }
        if(!is_null($user)){
            $user->restore();
        }
        return redirect()->back()->with("success", "Usuario restaurado correctamente");
    }


    public function force_delete($id)
    {
        // Retrieve the trashed user with its associated employee, holidays, appointments, and bookings
        $user = User::withTrashed()->findOrFail($id);
        $this->protectSuperadminAccount($user);

        //for employee
        if($user->employee && $user->employee->appointments->count())
        {
            return back()->withErrors('El usuario no se puede eliminar permanentemente porque tiene turnos asociados.');
        }

        //for user
        if($user->appointments->count())
        {
            return back()->withErrors('El usuario no se puede eliminar permanentemente porque tiene turnos asociados.');
        }

        // Check if the user has an associated employee
        if ($user->employee) {
            // Delete all holidays related to the employee
            foreach ($user->employee->holidays as $holiday) {
                $holiday->forceDelete(); // Force delete each holiday
            }


            // Delete all appointments related to the employee
            // foreach ($user->employee->appointments as $appointment) {
            //     $appointment->forceDelete(); // Force delete each appointment
            // }

                 // Detach all services related to the employee (many-to-many relationship)
            if ($user->employee->services()->exists()) {
                $user->employee->services()->detach(); // Detach the services from the employee
            }

            // Finally, delete the employee data
            $user->employee->forceDelete();
        }

        // Delete the user's profile image if exists
        if ($user->image) {
            $destination = public_path('uploads/images/profile/' . $user->image);
            if (\File::exists($destination)) {
                \File::delete($destination);
            }
        }

        // Permanently delete the user from the database
        $user->forceDelete();

        return back()->withSuccess('El usuario y todos sus datos relacionados se eliminaron permanentemente.');
    }



    public function password_update(Request $request, User $user)
    {
        $this->protectSuperadminAccount($user);
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => '¡La contraseña actual no coincide!']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', '¡La contraseña se actualizó correctamente!');
    }

    public function updateProfileImage(Request $request, User $user)
    {
                $this->protectSuperadminAccount($user);
$request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
            'delete_image' => 'nullable'
        ]);

        //remove old image
        $destination = public_path('uploads/images/profile/'. $user->image);
        if(\File::exists($destination))
        {
            \File::delete($destination);
        }

        $imageName = time().'.'.$request->image->getClientOriginalExtension();
        $request->image->move(public_path('uploads/images/profile/'),$imageName);
        $user->update([
            'image' => $imageName
        ]);

        return back()->withSuccess('Profile image has been updated successfully!');

    }


    //delete profile image
    public function deleteProfileImage(User $user)
    {
                $this->protectSuperadminAccount($user);
$destination = public_path('uploads/images/profile/'.$user->image);
        if(\File::exists($destination))
        {
            \File::delete($destination);
        }

        $user->update([
            'image' => null
        ]);
        return back()->withSuccess('Profile image deleted!');
    }


    private function protectSuperadminAccount(User $targetUser): void
    {
        $actor = auth()->user();

        abort_if(
            $targetUser->hasRole('superadmin')
            && (!$actor || !$actor->hasRole('superadmin')),
            403
        );
    }

    // Transform the data
    function transformOpeningHours($data)
    {
        $result = [];

        foreach ($data as $day => $times) {
            $dayHours = [];
            for ($i = 0; $i < count($times); $i += 2) {
                if (isset($times[$i + 1])) {
                    $dayHours[] = $times[$i] . '-' . $times[$i + 1];
                }
            }
            $result[$day] = $dayHours;
        }

        return $result;
    }



    protected function transformAvailabilitySlotsForEdit(array $employeeDays)
    {
        foreach ($employeeDays as $day => $slots) {
            $transformedSlots = [];
            foreach ($slots as $slot) {
                list($startTime, $endTime) = explode('-', $slot);
                $transformedSlots[] = $startTime;
                $transformedSlots[] = $endTime;
            }
            $employeeDays[$day] = $transformedSlots;
        }

        return $employeeDays;
    }






}
