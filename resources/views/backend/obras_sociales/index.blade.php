@extends('adminlte::page')

@section('title', 'Obras Sociales')

@section('content_header')
<div class="row mb-2">
<div class="col-sm-6">
<h1>Obras Sociales</h1>
</div>
<div class="col-sm-6 text-right">
<a href="{{ route('obras-sociales.create') }}" class="btn btn-primary">
<i class="fas fa-plus"></i> Nueva Obra Social
</a>
</div>
</div>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{ session('success') }}
</div>
@endif

<div class="card">
<div class="card-body p-0">
<table id="tablaObras" class="table table-striped">
<thead>
<tr>
<th>#</th>
<th>Nombre</th>
<th>Plan</th>
<th>Prestadora</th>
<th>Estado</th>
<th>Coseguros</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
@foreach($obrasSociales as $obra)
<tr>
<td>{{ $loop->iteration }}</td>
<td>{{ $obra->nombre }}</td>
<td>{{ $obra->plan ?? '—' }}</td>
<td>{{ $obra->prestadora ?? '—' }}</td>
<td>
<span class="badge badge-{{ $obra->activo ? 'success' : 'secondary' }}">
{{ $obra->activo ? 'Activa' : 'Inactiva' }}
</span>
</td>
<td>{{ $obra->coseguros->count() }}</td>
<td>
<a href="{{ route('obras-sociales.edit', $obra) }}"
class="btn btn-sm btn-warning">
<i class="fas fa-edit"></i> Editar
</a>
<form action="{{ route('obras-sociales.destroy', $obra) }}"
method="POST" class="d-inline"
onsubmit="return confirm('¿Eliminar esta obra social?')">
@csrf @method('DELETE')
<button class="btn btn-sm btn-danger">
<i class="fas fa-trash"></i> Eliminar
</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#tablaObras').DataTable({
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 registros",
            infoFiltered: "(filtrado de _MAX_ totales)",
                               search: "Buscar:",
                               paginate: { next: "Siguiente", previous: "Anterior" }
        }
    });
});

$(".alert").delay(6000).slideUp(300);
</script>
@stop
