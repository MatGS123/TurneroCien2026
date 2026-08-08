@extends('adminlte::page')

@section('title', 'Nueva Obra Social')

@section('content_header')
<h1>Nueva Obra Social</h1>
@stop

@section('content')
<div class="card card-primary">
<div class="card-header">
<h3 class="card-title">Datos de la obra social</h3>
</div>
<form action="{{ route('obras-sociales.store') }}" method="POST">
@csrf
<div class="card-body">
<div class="form-group">
<label>Nombre <span class="text-danger">*</span></label>
<input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
value="{{ old('nombre') }}" placeholder="Ej: OSDE" required>
@error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
<label>Plan</label>
<input type="text" name="plan" class="form-control"
value="{{ old('plan') }}" placeholder="Ej: 210, 410, Bronce">
</div>

<div class="form-group">
<label>Prestadora</label>
<input type="text" name="prestadora" class="form-control"
value="{{ old('prestadora') }}" placeholder="Ej: CLISA, APS">
</div>

<div class="form-group">
<div class="custom-control custom-switch">
<input type="checkbox" class="custom-control-input" id="activo"
name="activo" value="1" {{ old('activo', '1') ? 'checked' : '' }}>
<label class="custom-control-label" for="activo">Activa</label>
</div>
</div>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary">Guardar</button>
<a href="{{ route('obras-sociales.index') }}" class="btn btn-secondary">Cancelar</a>
</div>
</form>
</div>
@stop
