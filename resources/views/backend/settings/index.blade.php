@extends('adminlte::page')

@section('title', 'Settings')

@section('content_header')

    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Configuración</li>
                </ol>
            </div>
        </div>
    </div>

@stop

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>{{ session('success') }}</strong>
        </div>
    @endif

    <!-- Main content -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                @if ($setting->dark_logo)
                    <div class="card card-primary card-outline bg-dark">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="w-50 mx-auto"
                                    src="{{ asset('public/uploads/images/logo/' . $setting->dark_logo) }}" alt="">
                            </div>
                        </div>
                    </div>
                @endif
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @if ($setting->logo)
                                <img class="w-50 mx-auto" src="{{ asset('public/uploads/images/logo/' . $setting->logo) }}"
                                    alt="">
                                <hr>
                            @endif
                            <span class="text-muted text-center text-dark h4">{{ $setting->bname }}</span> <br>
                        </div>

                    </div>

                </div>


            </div>
            <!-- /.col -->
            <div class="col-md-9">
                @if (count($errors) > 0)
                    <div class="alert alert-dismissable alert-danger mt-3">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>ERROR!</strong>Hubo un error con el ingreso de datos<br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card">
                    <form action="{{ route('setting.update', $setting->id) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#business" data-toggle="tab">Información
                                        del negocio</a></li>
                                <li class="nav-item"><a class="nav-link" href="#social" data-toggle="tab">Redes sociales</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#header" data-toggle="tab">Cabezera</a></li>
                                <li class="nav-item"><a class="nav-link" href="#footer" data-toggle="tab">Pié de página</a></li>
                                {{-- <li class="nav-item"><a class="nav-link" href="#smtp" data-toggle="tab">SMTP</a>
                                </li> --}}
                                <li class="nav-item"><a class="nav-link" href="#seo" data-toggle="tab">SEO</a></li>
                                <li class="nav-item"><a class="nav-link" href="#obras-sociales" data-toggle="tab">Obras Sociales</a></li>
                                {{-- <li class="nav-item"><a class="nav-link" href="#social-login" data-toggle="tab">Social
                                        login</a></li>
                                <li class="nav-item"><a class="nav-link" href="#security" data-toggle="tab">Advance</a>
                                </li> --}}

                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="business">
                                    <input type="hidden" class="form-control" name="id" id="inputName" value="">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Nombre de la clínica</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control @error('bname') is-invalid @enderror"
                                                name="bname" id="inputName" placeholder="Business Name"
                                                value="{{ $setting->bname }}">
                                            @error('bname')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror" name="email"
                                                id="inputEmail" placeholder="Email" value="{{ $setting->email }}">
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Moneda</label>
                                        <div class="col-sm-10">
                                            <input type="text"
                                                class="form-control @error('currency') is-invalid @enderror" name="currency"
                                                id="inputEmail" placeholder="USD" value="{{ $setting->currency }}">
                                                <small class="text-muted">Ejemplo: USD (use la abreviación) </small>
                                            @error('currency')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Teléfono</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2" name="phone"
                                                placeholder="Contact Number" value="{{ $setting->phone }}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">WhatsApp</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2" name="whatsapp"
                                                placeholder="WhatsApp Number" value="{{ $setting->whatsapp }}">
                                            <small>Ingresar número de whatsapp con código sin espacios. Ex:
                                                919865322154</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Website
                                            Logo</label>
                                        <div class="col-sm-10">
                                            <input type="file" class="form-control" id="inputName2"
                                                placeholder="Profiel Picture" name="logo">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Dirección</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="Address" value="{{ $setting->address }}" name="address">
                                            <small>La dirección será visible en la página de contacto</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Google
                                            Maps</label>
                                        <div class="col-sm-10">
                                            <small>Ingrese y ajuste el código de google maps para iframe, ajustado;</small>
                                            <textarea name="map" id="" class="form-control" cols="30" rows="8">{{ $setting->map }}</textarea>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.Social -->
                                <div class="tab-pane" id="social">
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Facebook</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="Facebook" value="{{ $setting->social['facebook'] ?? '' }}"
                                                name="social[facebook]">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Instagram</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="Instagram" value="{{ $setting->social['instagram'] ?? '' }}"
                                                name="social[instagram]">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Twitter (X)</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="twitter" value="{{ $setting->social['twitter'] ?? '' }}"
                                                name="social[twitter]">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Linkedin</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="linkedin" value="{{ $setting->social['linkedin'] ?? '' }}"
                                                name="social[linkedin]">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Youtube</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="Youtube" value="{{ $setting->social['youtube'] ?? '' }}"
                                                name="social[youtube]">
                                        </div>
                                    </div>
                                </div>
                                <!-- /.Social ends-->
                                <!-- google analytics -->
                                <div class="tab-pane" id="header">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Cabezera</label>
                                        <div class="col-sm-10">
                                            <small>CSS personalizado, para la cabezera</small>
                                            <textarea name="header" id="" class="form-control" cols="30" rows="20">{{ $setting->header }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="footer">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Pié de página</label>
                                        <div class="col-sm-10">
                                            <small>Añadir JS personalizado para el pié de página</small>
                                            <textarea name="footer" id="" class="form-control" cols="30" rows="20">{{ $setting->footer }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="security">
                                    <div class="tab-pane" id="advance">
                                        <h3>Google Captcha</h3>
                                        <div class="form-group row pt-3">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Google Captcha Site
                                                Key</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="captcha[site_key]" value="">
                                                <input type="text" class="form-control" id="inputName2"
                                                    name="captcha[site_key]" placeholder="Captcha Site Key"
                                                    value="{{ $setting->captcha['site_key'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Google Captcha Secret
                                                Key</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="captcha[secret_key]" value="">
                                                <input type="text" class="form-control" id="inputName2"
                                                    name="captcha[secret_key]" placeholder="Captcha Secret Key"
                                                    value="{{ $setting->captcha['secret_key'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group pb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="captcha[login_form]" value="0">
                                                <input type="checkbox" class="custom-control-input" id="login_form"
                                                    name="captcha[login_form]" value="1"
                                                    {{ $setting->captcha['login_form'] ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="login_form">Login</label>
                                                <small>&nbsp;&nbsp;Habilita o desabilita el captcha</small>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-switch pb-3">
                                                <input type="hidden" name="captcha[contact_form]" value="0">
                                                <input type="checkbox" class="custom-control-input" id="contact_form"
                                                    name="captcha[contact_form]" value="1"
                                                    {{ $setting->captcha['contact_form'] ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="contact_form">Contacto</label>
                                                <small>&nbsp;&nbsp;Habilita o deshabilita el captcha</small>
                                            </div>
                                        </div>
                                        <hr>
                                        <h3>One Signal - Push</h3>
                                        <div class="form-group row pt-3">
                                            <label for="inputName2" class="col-sm-3 col-form-label">One Signal Site
                                                Key</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="onesignal[site_key]" value="">
                                                <input type="text" class="form-control" id="inputName2"
                                                    name="onesignal[site_key]" placeholder="One Signal Site Key"
                                                    value="{{ $setting->onesignal['site_key'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">One Signal Secret
                                                Key</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="onesignal[secret_key]" value="">
                                                <input type="text" class="form-control" id="inputName2"
                                                    name="onesignal[secret_key]" placeholder="One Signal Secret Key"
                                                    value="{{ $setting->onesignal['secret_key'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">One Signal Script</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="onesignal[script]" value="">
                                                <textarea class="form-control" name="onesignal[script]" id="" cols="30" rows="8">{{ $setting->onesignal['script'] ?? '' }}</textarea>

                                            </div>
                                        </div>
                                        <div class="form-group pb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="onesignal[status]" value="0">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="push_notification" name="onesignal[status]" value="1"
                                                    {{ $setting->onesignal['status'] ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="push_notification">Habilitar o deshabilitar notificaciones
                                                    Push Notifications</label>
                                            </div>
                                        </div>

                                        <hr>
                                        <h3>Registro de usuarios</h3>
                                        <div class="form-group pb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="user_registration" name="user_registration" value="1"
                                                    {{ $setting->user_registration ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="user_registration">Disable/Enable
                                                    </label> (<span class="text-danger">New User Registration</span>)
                                            </div>
                                        </div>

                                        <hr>
                                        <h3>Modo de mantenimiento</h3>
                                        <div class="form-group pb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="maintenance_mode" value="0">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="maintenance_mode" name="maintenance_mode" value="1"
                                                    {{ $setting->maintenance_mode ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="maintenance_mode">Habilitar/Deshabilitar
                                                    </label> (<span class="text-danger">Mostrar que el sitio está bajo mantenimiento</span>)
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- google analytics ends-->
                                <!-- google map -->
                                <div class="tab-pane" id="smtp">
                                    <h3 class="mb-1">SMTP</h3>
                                    <small>Email para notificaciones</small>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Mail Host</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[host]" value="">
                                            <input type="text" class="form-control" id="inputName2" name="smtp[host]"
                                                placeholder="smtp.gmail.com" value="{{ $setting->smtp['host'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Mail Host</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[port]" value="">
                                            <input type="text" class="form-control" id="inputName2" name="smtp[port]"
                                                placeholder="465" value="{{ $setting->smtp['port'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Mail Encryption</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[encryption]" value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="smtp[encryption]" placeholder="TLS/SSL"
                                                value="{{ $setting->smtp['encryption'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Usuario</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[user_name]" value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="smtp[user_name]" placeholder="youremail@gmail.com"
                                                value="{{ $setting->smtp['user_name'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Contraseña</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[password]" value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="smtp[password]" placeholder="password"
                                                value="{{ $setting->smtp['password'] ?? '' }}">
                                        </div>
                                    </div>

                                </div>

                                <div class="tab-pane" id="seo">
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Título de la página web</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2" name="meta_title"
                                                placeholder="Site SEO Title" value="{{ $setting->meta_title }}">
                                            <small>Título para búsquedas de Google</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Keywords</label>
                                        <div class="col-sm-10">
                                            <textarea name="meta_keywords" class="form-control"
                                                placeholder="Keyword1, keyword2, keyword3"
                                                cols="30" rows="2">{{ $setting->meta_keywords }}</textarea>
                                            <small>SEO keywords</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Descripción del sitio</label>
                                        <div class="col-sm-10">
                                            <textarea name="meta_description" class="form-control"
                                                placeholder="Website description..."
                                                cols="30" rows="3">{{ $setting->meta_description }}</textarea>
                                            <small>Descripción para SEO</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB: Obras Sociales --}}
                                <div class="tab-pane" id="obras-sociales">
                                    <h5 class="mb-3">Gestión de Obras Sociales</h5>

                                    <div id="obras-alert" class="alert d-none"></div>

                                    <div class="input-group mb-4" style="max-width: 450px;">
                                        <input type="text" id="nueva-obra" class="form-control" placeholder="Nombre de la obra social">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" id="btn-agregar-obra">
                                                <i class="fas fa-plus"></i> Agregar
                                            </button>
                                        </div>
                                    </div>

                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th style="width: 160px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="obras-tbody">
                                            <tr>
                                                <td colspan="3" class="text-center">
                                                    <span class="spinner-border spinner-border-sm"></span> Cargando...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- social login --}}

                                <div class="tab-pane" id="social-login">
                                    <h3>Login Google</h3>
                                    <div class="form-group row pt-3">
                                        <label for="clientId" class="col-sm-2 col-form-label">Client Id</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[google][client_id]" value="">
                                            <input type="text" class="form-control" id="clientId"
                                                name="social_login[google][client_id]" placeholder="Google client id"
                                                value="{{ $setting->social_login['google']['client_id'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="SecretId" class="col-sm-2 col-form-label">Secret Key</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[google][secret_key]" value="">
                                            <input type="text" class="form-control" id="SecretId"
                                                name="social_login[google][secret_key]" placeholder="Google secret key"
                                                value="{{ $setting->social_login['google']['secret_key'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="form-group row pt-3">
                                        <label for="SecretId" class="col-sm-2 col-form-label">Callback Url</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[google][callback_url]"
                                                value="">
                                            <input type="text" class="form-control" id="SecretId"
                                                name="social_login[google][callback_url]"
                                                placeholder="https://www.yoursite.com/google/callback"
                                                value="{{ $setting->social_login['google']['callback_url'] ?? '' }}">
                                            <small>Ex: https://www.yoursite.com/google/callback</small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch pb-3">
                                            <input type="hidden" name="social_login[google][status]" value="0">
                                            <input type="checkbox" class="custom-control-input" id="google_login_status"
                                                name="social_login[google][status]" value="1"
                                                {{ $setting->social_login['google']['status'] ?? false ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="google_login_status">Disable/Enable
                                            </label>
                                        </div>
                                    </div>

                                    <hr>
                                    <h3>Login Facebook</h3>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Client Id</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[facebook][client_id]"
                                                value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="social_login[facebook][client_id]" placeholder="Facebook client id"
                                                value="{{ $setting->social_login['facebook']['client_id'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Secret
                                            Key</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[facebook][secret_key]"
                                                value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="social_login[facebook][secret_key]"
                                                placeholder="Facebook secret Key"
                                                value="{{ $setting->social_login['facebook']['secret_key'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="SecretId" class="col-sm-2 col-form-label">Callback Url</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[facebook][callback_url]"
                                                value="">
                                            <input type="text" class="form-control" id="SecretId"
                                                name="social_login[facebook][callback_url]"
                                                placeholder="https://www.yoursite.com/facebook/callback"
                                                value="{{ $setting->social_login['facebook']['callback_url'] ?? '' }}">
                                            <small>Ex: https://www.yoursite.com/facebook/callback</small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="social_login[facebook][status]" value="0">
                                            <input type="checkbox" class="custom-control-input"
                                                id="facebook_login_status" name="social_login[facebook][status]"
                                                value="1"
                                                {{ $setting->social_login['facebook']['status'] ?? false ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="facebook_login_status">Disable/Enable</label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                        <!-- /.tab-content -->
                        <div class="form-group row">
                            <div class="offset-sm-2 col-sm-10">
                                <button onclick="return confirm('Are you sure you want to update settings?');"
                                    type="submit" class="btn btn-danger text-left">Actualizar</button>
                            </div>
                        </div>
                    </form>
                </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- change password-->

            <!-- change password ends--->
        </div>
        <!-- /.col -->

    </div>

    </section>

@stop



@section('js')
    {{-- <script>
        $(document).ready(function() {
            // Add click event listener to each nav-link
            $('.nav-link').on('click', function() {
                // Store the clicked tab's href in local storage
                localStorage.setItem('activeTab', $(this).attr('href'));
            });

            // Check if there is an active tab stored in local storage
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                // Remove 'active' class from all nav-links and tab-panes
                $('.nav-link').removeClass('active');
                $('.tab-pane').removeClass('active');

                // Add 'active' class to the stored tab and corresponding tab-pane
                $('a[href="' + activeTab + '"]').addClass('active');
                $(activeTab).addClass('active');
            } else {
                // If no active tab is found in local storage, select the tab with id 'business'
                $('a[href="#business"]').addClass('active');
                $('#business').addClass('active');
            }
        });

        $(document).ready(function() {
            $(".alert").delay(6000).slideUp(300);
        });
    </script> --}}

    <script>
    $(document).ready(function () {
        const csrfToken = '{{ csrf_token() }}';
        const baseUrl = "{{ url('obras-sociales') }}";
        $('a[href="#obras-sociales"]').on('shown.bs.tab', function () {
            cargarObras();
        });

        function cargarObras() {
            $.get(baseUrl, function (data) {
                const tbody = $('#obras-tbody');
                tbody.empty();
                if (data.length === 0) {
                    tbody.append('<tr><td colspan="3" class="text-center text-muted">No hay obras sociales.</td></tr>');
                    return;
                }
                data.forEach(function (obra, i) {
                    tbody.append(`
                        <tr id="fila-${obra.id}">
                            <td>${i + 1}</td>
                            <td>${obra.nombre}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-editar"
                                    data-id="${obra.id}" data-nombre="${obra.nombre}">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${obra.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            });
        }

        function mostrarAlerta(mensaje, tipo = 'success') {
            const a = $('#obras-alert');
            a.removeClass('d-none alert-success alert-danger').addClass('alert-' + tipo).text(mensaje);
            setTimeout(() => a.addClass('d-none'), 4000);
        }

        // Agregar
        $('#btn-agregar-obra').on('click', function () {
            const nombre = $('#nueva-obra').val().trim();
            if (!nombre) return;
            $.ajax({
                url: baseUrl, method: 'POST',
                data: { nombre, _token: csrfToken },
                success: function (res) {
                    $('#nueva-obra').val('');
                    mostrarAlerta(res.message);
                    cargarObras();
                },
                error: function (xhr) {
                    mostrarAlerta(xhr.responseJSON?.errors?.nombre?.[0] || 'Error al agregar.', 'danger');
                }
            });
        });

        // Abrir modal editar
        $(document).on('click', '.btn-editar', function () {
            $('#edit-obra-id').val($(this).data('id'));
            $('#edit-obra-nombre').val($(this).data('nombre'));
            $('#modalEditarObra').modal('show');
        });

        // Guardar edición
        $('#btn-guardar-edicion').on('click', function () {
            const id = $('#edit-obra-id').val();
            const nombre = $('#edit-obra-nombre').val().trim();
            if (!nombre) return;
            $.ajax({
                url: `${baseUrl}/${id}`, method: 'PUT',
                data: { nombre, _token: csrfToken },
                success: function (res) {
                    $('#modalEditarObra').modal('hide');
                    mostrarAlerta(res.message);
                    cargarObras();
                },
                error: function (xhr) {
                    mostrarAlerta(xhr.responseJSON?.errors?.nombre?.[0] || 'Error al actualizar.', 'danger');
                }
            });
        });

        // Eliminar
        $(document).on('click', '.btn-eliminar', function () {
            if (!confirm('¿Eliminar esta obra social?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: `${baseUrl}/${id}`, method: 'DELETE',
                data: { _token: csrfToken },
                success: function (res) {
                    $(`#fila-${id}`).remove();
                    mostrarAlerta(res.message);
                },
                error: function () { mostrarAlerta('Error al eliminar.', 'danger'); }
            });
        });
    });
    </script>
@stop

