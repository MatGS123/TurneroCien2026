@extends('adminlte::page')

@section('title', 'Editar rol')

@section('content_header')
    <div class="row mb-2 pl-md-2">
        <div class="col-sm-6">
            <div class="pull-left">
                <h2>Editar rol y permisos</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('permission.index') }}"> Volver</a>
            </div>

        </div>
    </div>
@stop

@section('content')
    <div class="pl-md-2">
        @if (count($errors) > 0)
            <div class="alert alert-dismissable alert-danger">
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ups!</strong> Hay algunos problemas con los datos ingresados.<br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <strong class="h4 bg-warning px-2 rounded text-capitalize">Rol: {{ $role->name }}</strong>

                        </div>
                    </div>
                    <div class="col-md-8">
                        <form action="{{ route('permission.update',$role->id) }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <p class="h5 pb-2">Permisos:</p>

                                <div class="row">
                                    @foreach ($permissions as $permission)
                                    <div class="col-md-3">
                                        <label class="text-capitalize ">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                            {{ ($roleName === 'superadmin') ? 'disabled' : '' }}>
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                @endforeach
                                </div>
                                <button onclick="return confirm('¿Estás seguro de que deseas actualizar este rol?')" type="submit"
                                class="btn btn-danger mt-2">Guardar cambios</button>
                            </div>
                        </form>

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
            var confirmed = confirm("¿Estás seguro de que deseas eliminar estos datos?");
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
@stop
