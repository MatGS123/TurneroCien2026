@extends('adminlte::page')

@section('title', 'Estudios Médicos')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Gestión de Estudios Médicos</h1>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- Formulario para Subir Estudio (Izquierda o Arriba) --}}
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Cargar Nuevo Estudio</h3>
                </div>
                <form action="{{ route('estudios.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Seleccionar Paciente (Turno)</label>
                            <select name="appointment_id" class="form-control" required>
                                <option value="">— Seleccionar Paciente —</option>
                                @foreach($appointments as $app)
                                    <option value="{{ $app->id }}">{{ $app->name }} ({{ $app->date }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nombre del Estudio</label>
                            <input type="text" name="nombre_estudio" class="form-control" placeholder="Ej: Informe Espirometría" required>
                        </div>
                        <div class="form-group">
                            <label>Archivo PDF</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="archivo_pdf" class="custom-file-input" accept=".pdf" required>
                                    <label class="custom-file-label">Elegir PDF...</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">Subir Estudio</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla de Estudios (Derecha) --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-gray">
                    <h3 class="card-title">Listado de Estudios Clínicos</h3>
                </div>
                <div class="card-body p-0">
                    <table id="tablaEstudios" class="table table-striped m-0">
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Estudio</th>
                                <th>Fecha de Carga</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estudios as $estudio)
                                <tr>
                                    <td>{{ $estudio->appointment->name ?? 'Desconocido' }}</td>
                                    <td>{{ $estudio->nombre_estudio }}</td>
                                    <td>{{ $estudio->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{-- Botón de Descarga Real --}}
                                        <a href="{{ route('estudios.descargar', $estudio) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> Descargar
                                        </a>

                                        {{-- Botón Placeholder de IA --}}
                                        <button class="btn btn-sm btn-purple bg-purple" onclick="alert('Analizando estudio con IA (Próximamente)...')">
                                            <i class="fas fa-robot"></i> Analizar IA
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializa DataTables automáticamente con traducción básica
            $('#tablaEstudios').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });

            // Código para que el input file muestre el nombre del PDF seleccionado
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@stop
