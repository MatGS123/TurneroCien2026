@extends('adminlte::page')

@section('title', 'Todas las Citas')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Todas las citas</h1>
        </div>
    </div>
@stop

@section('content')
    <!-- Modal -->
    <form id="appointmentStatusForm" method="POST" action="{{ route('appointments.update.status') }}">
        @csrf
        <input type="hidden" name="appointment_id" id="modalAppointmentId">

        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p><strong>Cliente:</strong> <span id="modalAppointmentName">N/A</span></p>
                        <p><strong>Servicio:</strong> <span id="modalService">N/A</span></p>
                        <p><strong>Email:</strong> <span id="modalEmail">N/A</span></p>
                        <p><strong>Teléfono:</strong> <span id="modalPhone">N/A</span></p>
                        <p><strong>Obra social:</strong> <span id="modalObraSocial">N/A</span></p>
                        <p><strong>Personal:</strong> <span id="modalStaff">N/A</span></p>
                        <p><strong>Inicio:</strong> <span id="modalStartTime">N/A</span></p>
                        <p><strong>Monto:</strong> <span id="modalAmount">N/A</span></p>
                        <p><strong>Notas:</strong> <span id="modalNotes">N/A</span></p>
                        <p><strong>Estado actual:</strong> <span id="modalStatusBadge">N/A</span></p>

                        <div class="form-group">
                            <label><strong>Estado:</strong></label>
                            <select name="status" class="form-control" id="modalStatusSelect">
                                <option value="Pending payment">Pendiente de pago</option>
                                <option value="Processing">Procesando</option>
                                <option value="Confirmed">Confirmado</option>
                                <option value="Cancelled">Cancelado</option>
                                <option value="Completed">Completado</option>
                                <option value="On Hold">En espera</option>
                                <option value="No Show">No asistió</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" onclick="return confirm('¿Confirmar actualización?')"
                            class="btn btn-danger">Actualizar estado</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <div class="">
        @if (session('success'))
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card py-2 px-2">
                            <div class="card-body p-0">
                                <table id="myTable" class="table table-striped projects">
                                    <thead>
                                        <tr>
                                            <th style="width: 1%">#</th>
                                            <th style="width: 15%">Usuario</th>
                                            <th style="width: 10%">Obra</th>
                                            <th style="width: 15%">Email</th>
                                            <th style="width: 10%">Teléfono</th>
                                            <th style="width: 10%">Personal</th>
                                            <th style="width: 10%">Servicio</th>
                                            <th style="width: 10%">Fecha</th>
                                            <th style="width: 10%">Tiempo</th>
                                            <th style="width: 15%" class="text-center">Estado</th>
                                            <th style="width: 18%">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $statusColors = [
                                                'Pending payment' => '#f39c12',
                                                'Processing' => '#3498db',
                                                'Confirmed' => '#2ecc71',
                                                'Cancelled' => '#ff0000',
                                                'Completed' => '#008000',
                                                'On Hold' => '#95a5a6',
                                                'Rescheduled' => '#f1c40f',
                                                'No Show' => '#e67e22',
                                            ];
                                            
                                            $statusTranslations = [
                                                'Pending payment' => 'Pendiente de pago',
                                                'Processing' => 'Procesando',
                                                'Confirmed' => 'Confirmado',
                                                'Cancelled' => 'Cancelado',
                                                'Completed' => 'Completado',
                                                'On Hold' => 'En espera',
                                                'Rescheduled' => 'Reprogramado',
                                                'No Show' => 'No asistió',
                                            ];
                                        @endphp
                                        @foreach ($appointments as $appointment)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>

                                                {{-- Usuario --}}
                                                <td>
                                                    <a>{{ $appointment->name }}</a>
                                                    <br>
                                                    <small>{{ $appointment->created_at->format('d M Y') }}</small>
                                                </td>

                                                {{-- Obra Social --}}
                                                <td>{{ $appointment->obra_social ?? 'N/A' }}</td>

                                                {{-- Email --}}
                                                <td>{{ $appointment->email }}</td>

                                                {{-- Teléfono --}}
                                                <td>{{ $appointment->phone }}</td>

                                                {{-- Personal --}}
                                                <td>{{ $appointment->employee->user->name }}</td>

                                                {{-- Servicio --}}
                                                <td>{{ $appointment->service->title ?? 'N/A' }}</td>

                                                {{-- Fecha --}}
                                                <td>{{ $appointment->booking_date }}</td>

                                                {{-- Tiempo --}}
                                                <td>{{ $appointment->booking_time }}</td>

                                                {{-- Estado --}}
                                                <td>
                                                    @php
                                                        $status = $appointment->status;
                                                        $color = $statusColors[$status] ?? '#7f8c8d';
                                                        $statusText = $statusTranslations[$status] ?? $status;
                                                    @endphp
                                                    <span class="badge px-2 py-1"
                                                        style="background-color: {{ $color }}; color: white;">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>

                                                {{-- Acción --}}
                                                <td>
                                                    <button class="btn btn-primary btn-sm py-0 px-1 view-appointment-btn"
                                                        data-toggle="modal" data-target="#appointmentModal"
                                                        data-id="{{ $appointment->id }}"
                                                        data-name="{{ $appointment->name }}"
                                                        data-service="{{ $appointment->service->title ?? 'N/A' }}"
                                                        data-email="{{ $appointment->email }}"
                                                        data-phone="{{ $appointment->phone }}"
                                                        data-obra_social="{{ $appointment->obra_social ?? 'N/A' }}"
                                                        data-employee="{{ $appointment->employee->user->name }}"
                                                        data-start="{{ $appointment->booking_date . ' ' . $appointment->booking_time }}"
                                                        data-amount="{{ $appointment->amount }}"
                                                        data-notes="{{ $appointment->notes }}"
                                                        data-status="{{ $appointment->status }}">Ver</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@stop

@section('css')
@stop

@section('js')
    {{-- hide notification --}}
    <script>
        $(document).ready(function() {
            $(".alert").delay(6000).slideUp(300);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                responsive: true,
                language: {
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "search": "Buscar:",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "emptyTable": "No hay datos disponibles"
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.view-appointment-btn', function() {
            // Set modal fields
            $('#modalAppointmentId').val($(this).data('id'));
            $('#modalAppointmentName').text($(this).data('name'));
            $('#modalService').text($(this).data('service'));
            $('#modalEmail').text($(this).data('email'));
            $('#modalPhone').text($(this).data('phone'));
            $('#modalObraSocial').text($(this).data('obra_social') || 'N/A');
            $('#modalStaff').text($(this).data('employee'));
            $('#modalStartTime').text($(this).data('start'));
            $('#modalAmount').text($(this).data('amount'));
            $('#modalNotes').text($(this).data('notes'));

            // Set status select dropdown
            var status = $(this).data('status');
            $('#modalStatusSelect').val(status);

            // Traducciones de estado
            var statusTranslations = {
                'Pending payment': 'Pendiente de pago',
                'Processing': 'Procesando',
                'Confirmed': 'Confirmado',
                'Cancelled': 'Cancelado',
                'Completed': 'Completado',
                'On Hold': 'En espera',
                'Rescheduled': 'Reprogramado',
                'No Show': 'No asistió'
            };

            // Set status badge
            var statusColors = {
                'Pending payment': '#f39c12',
                'Processing': '#3498db',
                'Confirmed': '#2ecc71',
                'Cancelled': '#ff0000',
                'Completed': '#008000',
                'On Hold': '#95a5a6',
                'Rescheduled': '#f1c40f',
                'No Show': '#e67e22',
            };

            var badgeColor = statusColors[status] || '#7f8c8d';
            var statusText = statusTranslations[status] || status;
            
            $('#modalStatusBadge').html(
                `<span class="badge px-2 py-1" style="background-color: ${badgeColor}; color: white;">${statusText}</span>`
            );
        });
    </script>
@endsection