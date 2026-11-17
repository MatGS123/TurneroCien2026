@extends('adminlte::page')

@section('title', 'All Appointments')

@section('content_header')
<div class="row ">
    <div class="col-sm-6">
        <h1>Mis reservas</h1>
    </div>

</div>
@stop

@section('content')
    <!-- Main content -->

    <!-- Main content -->
    <div class="content pl-md-2 ">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-lg-12 margin-tb">
                    {{-- <div class="pull-left">
                            <h2>Gestión de usuarios</h2>
                        </div> --}}
                    @can('user-create')
                        <div class="pull-right">
                            <a class="btn btn-success" href="{{ route('employee.create') }}"> Crear nuevo profesional</a>
                        </div>
                    @endcan
                </div>
            </div>


            @if (session()->has('success'))
                <div class="alert alert-dismissable alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>
                        {!! session()->get('success') !!}
                    </strong>
                </div>
            @endif


            <div class="row">
                <div class="col-md-12">
                    <div class="card p-3 shadow">
                        <div id="example_wrapper" class="dataTables_wrapper table-responsive">
                            <table id="example" class="display dataTable table table-striped" style="width: 100%;"
                                aria-describedby="example_info">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Correo electrónico</th>
                                        <th >Teléfono</th>
                                        <th width="20%">Hora</th>
                                        <th width="10%">Fecha</th>
                                        <th>Employee</th>
                                        <th>Estado</th>
                                        <th width="10%">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bookings as $booking)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $booking->first_name }} {{ $booking->last_name }}</td>
                                            <td>{{ $booking->email }}</td>
                                            <td>{{ $booking->phone }}</td>
                                            <td>@if($booking->booking_time) {{ $booking->booking_time }} @else NA @endif</td>
                                            <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d, M Y') }}</td>
                                            <td>{{ $booking->employee_name }}</td>


                                            <td>
                                                @if ($booking->payment_status == true)
                                                    <div class="badge badge-success">Pagado</div>
                                                @else
                                                    <div class="badge badge-danger">Pending</div>
                                                @endif
                                            </td>

                                            <td class="d-flex">


                                                    <a class="btn btn-primary btn-sm mr-2"
                                                        href="{{ Route('employee.booking.detail',$booking->id) }}">Ver</a>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Correo electrónico</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                        <th width="280px">Acción</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

            </div>



        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        function DeleteAlert() {
            var confirmed = confirm("¿Estás seguro de que querés eliminar estos datos?");
            if (confirmed) {
                // code to delete data goes here
                console.log("Los datos fueron eliminados.");

            } else {
                addEventListener("click", function(event) {
                    event.preventDefault()
                })
            }
        }
    </script>


    {{-- hide notifcation --}}
    <script>
        $(document).ready(function() {
            $(".alert").delay(6000).slideUp(300);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({ language: { url: "{{ asset('vendor/datatables/i18n/es-ES.json') }}" } });

        });
    </script>

    <script type="text/javascript">
        $("#example").DataTable({ language: { url: "{{ asset('vendor/datatables/i18n/es-ES.json') }}" } });
    </script>

@stop
