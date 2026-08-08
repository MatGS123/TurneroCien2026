@extends('adminlte::page')

@section('title', 'Editar Obra Social')

@section('content_header')
<h1>Editar: {{ $obraSocial->nombre }} {{ $obraSocial->plan }}</h1>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{ session('error') }}
</div>
@endif

{{-- Datos principales --}}
<div class="card card-primary">
<div class="card-header"><h3 class="card-title">Datos generales</h3></div>
<form action="{{ route('obras-sociales.update', $obraSocial) }}" method="POST">
@csrf @method('PUT')
<div class="card-body">
<div class="form-group">
<label>Nombre <span class="text-danger">*</span></label>
<input type="text" name="nombre"
class="form-control @error('nombre') is-invalid @enderror"
value="{{ old('nombre', $obraSocial->nombre) }}" required>
@error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="form-group">
<label>Plan</label>
<input type="text" name="plan" class="form-control"
value="{{ old('plan', $obraSocial->plan) }}"
placeholder="Ej: 210, 410">
</div>
<div class="form-group">
<label>Prestadora</label>
<input type="text" name="prestadora" class="form-control"
value="{{ old('prestadora', $obraSocial->prestadora) }}"
placeholder="Ej: CLISA, APS">
</div>
<div class="form-group">
<div class="custom-control custom-switch">
<input type="checkbox" class="custom-control-input" id="activo"
name="activo" value="1"
{{ old('activo', $obraSocial->activo) ? 'checked' : '' }}>
<label class="custom-control-label" for="activo">Activa</label>
</div>
</div>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary">Actualizar</button>
<a href="{{ route('obras-sociales.index') }}" class="btn btn-secondary">Volver</a>
</div>
</form>
</div>

{{-- Coseguros --}}
<div class="card card-warning">
<div class="card-header"><h3 class="card-title">Coseguros por servicio</h3></div>
<div class="card-body">

@if($coseguros->count())
<table class="table table-sm table-bordered mb-4">
<thead class="thead-light">
<tr>
<th>Servicio</th>
<th>Monto ($)</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
@foreach($coseguros as $coseguro)
<tr>
<td>{{ $coseguro->service->title ?? '—' }}</td>
<td>${{ number_format($coseguro->monto, 2) }}</td>
<td>
<form action="{{ route('obras-sociales.coseguros.destroy', [$obraSocial, $coseguro]) }}"
method="POST"
onsubmit="return confirm('¿Eliminar este coseguro?')">
@csrf @method('DELETE')
<button class="btn btn-xs btn-danger">
<i class="fas fa-trash"></i> Eliminar
</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-muted">No hay coseguros cargados aún.</p>
@endif

{{-- Formulario agregar coseguro --}}
<form action="{{ route('obras-sociales.coseguros.store', $obraSocial) }}" method="POST">
@csrf
<div class="row align-items-end">
<div class="col-md-5">
<div class="form-group mb-0">
<label>Servicio</label>
<select name="service_id" class="form-control" required>
<option value="">— Seleccionar —</option>
@foreach($services as $service)
<option value="{{ $service->id }}">{{ $service->title }}</option>
@endforeach
</select>
</div>
</div>
<div class="col-md-4">
<div class="form-group mb-0">
<label>Monto coseguro ($)</label>
<input type="number" name="monto" step="0.01" min="0"
class="form-control" placeholder="0.00" required>
</div>
</div>
<div class="col-md-3">
<button type="submit" class="btn btn-warning btn-block">
<i class="fas fa-plus"></i> Agregar
</button>
</div>
</div>
</form>

</div>
</div>
@stop

@section('js')
<script>
$(".alert").delay(6000).slideUp(300);
</script>
@stop
EOF
