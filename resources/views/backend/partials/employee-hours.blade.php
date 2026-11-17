<div id="employee" class="row pl-md-2 pb-5">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-12">
                <hr>
                <div class="mb-3">
                    <h4 class="mb-0">Solo para profesionales</h4>
                    <small class="text-muted">Completá estos datos solo si agregás un profesional</small>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 mb-3 select2-dark">
                        <label for="service_id" class="my-0">
                            <i class="fas fa-id-card"></i> Seleccionar servicio
                        </label>
                        <small class="text-muted"> Link employees to services they are assigned
                            to</small>

                        <select class="form-control select2 @error('service[]') is-invalid @enderror"
                            name="service[]" data-placeholder="Seleccionar servicio" id="service"
                            multiple>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}"
                                    {{ $user->employee && $user->employee->services->contains('id', $service->id) ? 'selected' : '' }}>
                                    {{ $service->title }}
                                </option>
                            @endforeach
                        </select>

                        @error('service')
                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                        @enderror
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                        <label for="slot_duration" class="my-0">
                            <i class="fas fa-stopwatch"></i> Duración del servicio
                        </label>
                        <small class="text-muted"> Creá los turnos según la duración de tu horario preferido.</small>

                        <select class="form-control @error('slot_duration') is-invalid @enderror"
                            name="slot_duration" id="slot_duration">
                            <option value=""
                                {{ old('slot_duration', optional($user->employee)->slot_duration) == '' ? 'selected' : '' }}>
                                Seleccionar duración
                            </option>

                            @foreach ($steps as $stepValue)
                                <option value="{{ $stepValue }}"
                                    {{ old('slot_duration', optional($user->employee)->slot_duration) == $stepValue ? 'selected' : '' }}>
                                    {{ $stepValue }} minutos
                                </option>
                            @endforeach
                        </select>


                        @error('slot_duration')
                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                        @enderror
                    </div>


                    <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                        <label for="break_duration" class="my-0">
                            <i class="fas fa-coffee"></i> Tiempo de preparación o descanso
                        </label>
                        <small class="text-muted"> Descanso entre un turno y otro</small>

                        <select class="form-control @error('break_duration') is-invalid @enderror"
                            name="break_duration" id="break_duration">
                            <option value=""
                                {{ old('break_duration', optional($user->employee)->break_duration) == '' ? 'selected' : '' }}>
                                Sin descanso
                            </option>

                            @foreach ($breaks as $breakValue)
                                <option value="{{ $breakValue }}"
                                    {{ old('break_duration', optional($user->employee)->break_duration) == $breakValue ? 'selected' : '' }}>
                                    {{ $breakValue }}
                                </option>
                            @endforeach
                        </select>

                        @error('break_duration')
                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                        @enderror
                    </div>



                </div>

                <hr>
                <div class="row">
                    <div class="mb-3">
                        <h4 class="mb-0">Configurar disponibilidad del profesional</h4>
                        <small class="text-muted">
                            Seleccioná los días y horarios. Podés agregar varios horarios en un mismo día, por ejemplo, de 9 a 12 y de 16 a 20.
                        </small>
                    </div>

                    <div class="col-md-12">
                        @foreach ($days as $day)
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input"
                                                id="{{ $day }}"
                                                @if (old('days.' . $day) || isset($employeeDays[$day])) checked @endif>
                                            <label class="custom-control-label"
                                                for="{{ $day }}">
                                                {{ ['monday'=>'Lunes','tuesday'=>'Martes','wednesday'=>'Miércoles','thursday'=>'Jueves','friday'=>'Viernes','saturday'=>'Sábado','sunday'=>'Domingo'][$day] ?? ucfirst($day) }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- First Time Input Row -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Desde:</strong>
                                        <input type="time" class="form-control from"
                                            name="days[{{ $day }}][]"
                                            id="{{ $day }}From"
                                            value="{{ old('days.' . $day . '.0') ?? ($employeeDays[$day][0] ?? '') }}" />
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Hasta:</strong>
                                        <input type="time" class="form-control to"
                                            name="days[{{ $day }}][]"
                                            id="{{ $day }}To"
                                            value="{{ old('days.' . $day . '.1') ?? ($employeeDays[$day][1] ?? '') }}" />
                                        <div style="" id="{{ $day }}AddMore"
                                            class="text-right d-none text-primary">
                                            Add More
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Render Additional Rows -->
                            @if (old('days.' . $day) || isset($employeeDays[$day]))
                                @foreach (old('days.' . $day) ?: $employeeDays[$day] as $index => $time)
                                    @if ($index > 1 && $index % 2 == 0)
                                        <div class="row additional-{{ $day }}">
                                            <div class="col-md-2"></div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <strong>Desde:</strong>
                                                    <input type="time" class="form-control from"
                                                        name="days[{{ $day }}][]"
                                                        value="{{ $time }}"
                                                        id="{{ $day }}MoreFrom" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <strong>Hasta</strong>
                                                    <input type="time" class="form-control to"
                                                        name="days[{{ $day }}][]"
                                                        value="{{ old('days.' . $day . '.' . ($index + 1)) ?? ($employeeDays[$day][$index + 1] ?? '') }}"
                                                        id="{{ $day }}" />
                                                    <div class="remove-field text-danger text-right">
                                                        Quitar</div>
                                                </div>
                                            </div>


                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>


                <hr>
                {{-- <div class="row d-flex ">
                    <div class="col-md-10">
                        <h2 class="mb-0">Agregar feriados</h2>
                        <p class="text-muted">No hace falta agregar horarios para un día completo; para jornadas parciales,
                            especificá el día y el horario.</p>
                        <span id="addHoliday" class="btn btn-primary mb-2 btn-sm"><i
                                class="fa fa-plus"></i> Agregar feriado</span>
                        <div class="holidayContainer"></div>
                    </div>
                </div> --}}
                {{-- <div class="row d-flex">
                    <div class="col-md-10">
                        <h2 class="mb-0">Agregar feriados</h2>
                        <p class="text-muted">No hace falta agregar horarios para un día completo; para jornadas parciales,
                            especificá el día y el horario.</p>
                        <span id="addHoliday" class="btn btn-primary mb-2 btn-sm"><i
                                class="fa fa-plus"></i> Agregar feriado</span>
                        <div class="holidayContainer">
                            @if (is_array(old('holidays.date')))
                                @foreach (old('holidays.date') as $index => $date)
                                    <div class="row holiday-row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="mb-0" for="date">Fecha</label>
                                                <input class="form-control" type="date"
                                                    name="holidays[date][]"
                                                    value="{{ $date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <strong>Desde:</strong>
                                                <input type="time" class="form-control from"
                                                    name="holidays[from_time][]"
                                                    value="{{ old('holidays.from_time')[$index] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <strong>Hasta:</strong>
                                                <input type="time" class="form-control to"
                                                    name="holidays[to_time][]"
                                                    value="{{ old('holidays.to_time')[$index] ?? '' }}">
                                                <div class="text-right text-danger removeHoliday"
                                                    style="cursor:pointer;">Eliminar</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="custom-control custom-switch pt-4">
                                                <input type="checkbox" value="1"
                                                    name="holidays[recurring][]"
                                                    class="custom-control-input"
                                                    id="recurring-{{ $index }}"
                                                    {{ (old('holidays.recurring')[$index] ?? 0) == 1 ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                    for="recurring-{{ $index }}">Recurring</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div> --}}
                {{-- <div class="row d-flex">
                    <div class="col-md-10">
                        <h2 class="mb-0">Agregar feriados</h2>
                        <p class="text-muted">No hace falta agregar horarios para un día completo; para jornadas parciales,
                            especificá el día y el horario.</p>
                        <span id="addHoliday" class="btn btn-primary mb-2 btn-sm"><i
                                class="fa fa-plus"></i> Agregar feriado</span>
                        <div class="holidayContainer">
                            @if ($user->employee && $user->employee->holidays->isNotEmpty())
                                @foreach ($user->employee->holidays as $index => $holiday)
                                    @php
                                        // Access the hours array directly
                                        $timeRange = $holiday->hours;
                                        $fromTime = isset($timeRange[0])
                                            ? explode('-', $timeRange[0])[0]
                                            : '';
                                        $toTime = isset($timeRange[0])
                                            ? explode('-', $timeRange[0])[1]
                                            : '';
                                    @endphp
                                    <div class="row holiday-row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="mb-0" for="date">Fecha</label>
                                                <input class="form-control" type="date"
                                                    name="holidays[date][]" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <strong>Desde:</strong>
                                                <input type="time" class="form-control from"
                                                    name="holidays[from_time][]"
                                                    value="{{ $fromTime }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <strong>Hasta:</strong>
                                                <input type="time" class="form-control to"
                                                    name="holidays[to_time][]"
                                                    value="{{ $toTime }}">
                                                <div class="text-right text-danger removeHoliday"
                                                    style="cursor:pointer;">Eliminar</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="custom-control custom-switch pt-4">
                                                <input type="checkbox" value="1"
                                                    name="holidays[recurring][]"
                                                    class="custom-control-input"
                                                    id="recurring-{{ $index }}"
                                                    {{ $holiday->recurring ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                    for="recurring-{{ $index }}">Recurring</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p>No se encontraron feriados para este usuario.</p>
                            @endif
                        </div>
                    </div>
                </div> --}}



                {{-- <div class="row d-flex">
                    <div class="col-md-10">
                        <h2 class="mb-0">Agregar feriados</h2>
                        <p class="text-muted">
                            No hace falta agregar horarios para un día completo; para jornadas parciales, especificá el día y el horario.
                        </p>
                        <span id="addHoliday" class="btn btn-primary mb-2 btn-sm">
                            <i class="fa fa-plus"></i> Agregar feriado
                        </span>
                        <div class="holidayContainer">
                            @if ($user->employee && $user->employee->holidays->isNotEmpty())
                                @foreach ($user->employee->holidays as $index => $holiday)
                                    @php
                                        $fromTime = '';
                                        $toTime = '';
                                        if (!empty($holiday->hours) && is_array($holiday->hours)) {
                                            $timeRange = explode('-', $holiday->hours[0] ?? '');
                                            $fromTime = $timeRange[0] ?? '';
                                            $toTime = $timeRange[1] ?? '';
                                        }
                                    @endphp
                                    <div class="row holiday-row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="mb-0" for="date">Fecha</label>
                                                <input class="form-control" type="date"
                                                    name="holidays[date][]"
                                                    value="{{ $holiday->date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <strong>Desde:</strong>
                                                <input type="time" class="form-control from"
                                                    name="holidays[from_time][]"
                                                    value="{{ $fromTime }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <strong>Hasta:</strong>
                                                <input type="time" class="form-control to"
                                                    name="holidays[to_time][]"
                                                    value="{{ $toTime }}">
                                                <div class="text-right text-danger removeHoliday"
                                                    style="cursor:pointer;">Eliminar</div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="holidays[recurring][]" value="{{ $holiday->recurring ? 1 : 0 }}">
                                    </div>
                                @endforeach
                            @else
                                <p>No se encontraron feriados para este usuario. Hacé clic en «Agregar feriado» para crear uno.</p>
                            @endif
                        </div>
                    </div>
                </div> --}}


                {{-- <div class="row d-flex">
                    <div class="col-md-10">
                        <h2 class="mb-0">Agregar feriados</h2>
                        <p class="text-muted">
                            No hace falta agregar horarios para un día completo; para jornadas parciales, especificá el día y el horario.
                        </p>
                        <span id="addHoliday" class="btn btn-primary mb-2 btn-sm">
                            <i class="fa fa-plus"></i> Agregar feriado
                        </span>
                        <div class="holidayContainer">
                            @forelse(old('holidays.date', $user->employee->holidays ?? []) as $index => $date)
                                @php
                                    $holiday = $user->employee->holidays[$index] ?? null;
                                    $fromTime = old("holidays.from_time.$index",
                                        $holiday && $holiday->hours ? explode('-', $holiday->hours[0])[0] ?? '' : '');
                                    $toTime = old("holidays.to_time.$index",
                                        $holiday && $holiday->hours ? explode('-', $holiday->hours[0])[1] ?? '' : '');
                                    $recurring = old("holidays.recurring.$index", $holiday->recurring ?? 0);
                                @endphp
                                <div class="row holiday-row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="mb-0">Fecha</label>
                                            <input class="form-control" type="date"
                                                   name="holidays[date][]"
                                                   value="{{ old("holidays.date.$index", $date) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Desde:</strong>
                                            <input type="time" class="form-control from"
                                                   name="holidays[from_time][]"
                                                   value="{{ $fromTime }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Hasta:</strong>
                                            <input type="time" class="form-control to"
                                                   name="holidays[to_time][]"
                                                   value="{{ $toTime }}">
                                            <div class="text-right text-danger removeHoliday" style="cursor:pointer;">
                                                Quitar
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="holidays[recurring][]" value="{{ $recurring }}">
                                </div>
                            @empty
                                <p>No se encontraron feriados para este usuario. Hacé clic en «Agregar feriado» para crear uno.</p>
                            @endforelse
                        </div>
                    </div>
                </div> --}}
                <div class="row d-flex">
                    <div class="col-md-10">
                        <h2 class="mb-0">Agregar feriados</h2>
                        <p class="text-muted">
                            No hace falta agregar horarios para un día completo; para jornadas parciales, especificá el día y el horario.
                        </p>
                        <span id="addHoliday" class="btn btn-primary mb-2 btn-sm">
                            <i class="fa fa-plus"></i> Agregar feriado
                        </span>
                        <div class="holidayContainer">
                            @php
                                // Get holidays from old input or database
                                $holidaysInput = old('holidays.date', []);
                                $dbHolidays = $user->employee->holidays ?? [];
                                $holidaysToDisplay = !empty($holidaysInput) ? $holidaysInput : $dbHolidays;
                            @endphp

                            @forelse($holidaysToDisplay as $index => $holidayItem)
                                @php
                                    // Determine if we're using old input or database data
                                    $usingOldInput = !empty($holidaysInput);

                                    if ($usingOldInput) {
                                        $date = old("holidays.date.$index");
                                        $holiday = null;
                                    } else {
                                        $holiday = $holidayItem;
                                        $date = $holiday->date;
                                        // Format date for input field if it's not already in YYYY-MM-DD format
                                        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                            try {
                                                $date = \Carbon\Carbon::parse($date)->format('Y-m-d');
                                            } catch (Exception $e) {
                                                $date = '';
                                            }
                                        }
                                    }

                                    $fromTime = old("holidays.from_time.$index",
                                        $holiday && $holiday->hours ? explode('-', $holiday->hours[0])[0] ?? '' : '');
                                    $toTime = old("holidays.to_time.$index",
                                        $holiday && $holiday->hours ? explode('-', $holiday->hours[0])[1] ?? '' : '');
                                    $recurring = old("holidays.recurring.$index", $holiday->recurring ?? 0);
                                @endphp
                                <div class="row holiday-row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="mb-0">Fecha</label>
                                            <input class="form-control" type="date"
                                                   name="holidays[date][]"
                                                   value="{{ $date }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Desde:</strong>
                                            <input type="time" class="form-control from"
                                                   name="holidays[from_time][]"
                                                   value="{{ $fromTime }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Hasta:</strong>
                                            <input type="time" class="form-control to"
                                                   name="holidays[to_time][]"
                                                   value="{{ $toTime }}">
                                            <div class="text-right text-danger removeHoliday" style="cursor:pointer;">
                                                Quitar
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="holidays[recurring][]" value="{{ $recurring }}">
                                </div>
                            @empty
                                <p>No se encontraron feriados para este usuario. Hacé clic en «Agregar feriado» para crear uno.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
