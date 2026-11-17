@extends('adminlte::page')

@section('title', 'Scribe')
@section('content_header')
<h1>Scribe <small class="text-muted">Notas clínicas</small></h1>
@stop

@section('content')
<div class="container-fluid px-0" id="scribe-app">
    <div class="alert alert-warning"><i class="fas fa-user-shield mr-2"></i>No dictes el nombre completo, DNI u otros datos identificatorios del paciente si no son necesarios para la nota.</div>
    <div class="alert alert-info"><i class="fas fa-lock mr-2"></i>El audio se guarda primero en tu dispositivo y, si lo enviás, se conserva solo temporalmente en el servidor para transcribirlo. Duración máxima: 10 minutos.</div>
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">1. Turno asociado</h3></div>
                <div class="card-body">
                    <label for="appointment_id">Seleccioná un turno</label>
                    <select id="appointment_id" class="form-control">
                        <option value="">Elegir turno...</option>
                        @foreach($appointments as $appointment)
                            <option value="{{ $appointment->id }}">{{ $appointment->booking_date }} {{ $appointment->booking_time }} — Turno #{{ $appointment->id }}</option>
                        @endforeach
                    </select>
                    <hr>
                    <div class="text-center">
                        <button id="record" class="btn btn-danger mr-2" type="button"><i class="fas fa-microphone mr-1"></i>Grabar</button>
                        <button id="stop" class="btn btn-secondary" type="button" disabled><i class="fas fa-stop mr-1"></i>Detener</button>
                        <div id="timer" class="h4 mt-3 mb-0">00:00</div>
                    </div>
                    <div class="mt-3">
                        <label for="audio">O cargar una copia local</label>
                        <input id="audio" type="file" class="form-control-file" accept="audio/*,video/webm">
                        <small class="text-muted">Máximo 25 MB. La duración se valida en el servidor.</small>
                    </div>
                    <audio id="preview" class="w-100 mt-3" controls hidden></audio>
                    <div class="mt-3">
                        <a id="save-again" href="#" class="btn btn-outline-secondary btn-sm mr-1" hidden download><i class="fas fa-download mr-1"></i>Guardar de nuevo en este dispositivo</a>
                        <button id="retry" class="btn btn-outline-primary btn-sm" type="button" hidden><i class="fas fa-redo mr-1"></i>Reintentar</button>
                    </div>
                    <button id="transcribe" class="btn btn-primary btn-block mt-3" type="button" disabled><i class="fas fa-language mr-1"></i>Enviar a Groq</button>
                    <div id="progress" class="small text-muted mt-3" aria-live="polite"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">2. Revisar, organizar y confirmar</h3></div>
                <div class="card-body">
                    <div class="form-group"><label for="title">Título de la nota</label><input id="title" class="form-control" value="Nota clínica"></div>
                    <div class="form-group"><label for="transcription">Transcripción editable</label><textarea id="transcription" class="form-control" rows="10" placeholder="La transcripción aparecerá aquí para que puedas corregirla."></textarea></div>
                    <button id="organize" class="btn btn-success" type="button" disabled><i class="fas fa-file-medical mr-1"></i>Organizar con Groq</button>
                    <div id="organized-wrap" class="mt-4" hidden>
                        <div class="form-group"><label for="organized">Nota organizada editable</label><textarea id="organized" class="form-control" rows="12"></textarea></div>
                        <button id="confirm" class="btn btn-primary" type="button"><i class="fas fa-check mr-1"></i>Confirmar y guardar como documento</button>
                    </div>
                    <div id="result" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = '{{ csrf_token() }}';
    const routes = {
        transcribe: '{{ route('ai.scribe.transcribe') }}',
        notes: '{{ url('/suite-ia/scribe/notes') }}',
    };
    const maxSeconds = 600;
    const maxBytes = 25 * 1024 * 1024;
    const pollEveryMs = 2500;
    const maxPolls = 120;          // ~5 minutos y se corta
    const maxPollFailures = 3;     // fallos de red seguidos antes de abandonar

    let recorder = null, chunks = [], selectedFile = null, localUrl = null;
    let timerId = null, seconds = 0, noteId = null, pollToken = 0, previousAppointment = '';

    const $ = id => document.getElementById(id);
    const setProgress = text => { $('progress').textContent = text; };
    const supportsRecording = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && window.MediaRecorder);

    const updateButtons = () => {
        $('transcribe').disabled = !$('appointment_id').value || !selectedFile;
        $('organize').disabled = !noteId || !$('transcription').value.trim();
    };

    // ---- red / errores -------------------------------------------------
    async function api(url, options = {}) {
        let response;
        try {
            response = await fetch(url, Object.assign({}, options, {
                credentials: 'same-origin',
                headers: Object.assign({'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, options.headers || {}),
            }));
        } catch (e) {
            return {ok: false, status: 0, json: {message: 'Sin conexión con el servidor. Revisá tu red e intentá de nuevo.'}};
        }
        let json = {};
        try { json = await response.json(); } catch (e) { json = {}; }
        if (!response.ok && !json.message) {
            const byStatus = {
                401: 'Tu sesión terminó. Recargá la página e iniciá sesión.',
                403: 'No tenés permiso para esta acción.',
                404: 'No se encontró la nota solicitada.',
                413: 'El archivo es demasiado grande para el servidor.',
                419: 'La sesión expiró. Recargá la página e intentá de nuevo.',
                429: 'Demasiadas solicitudes. Esperá un momento.',
            };
            json.message = byStatus[response.status] || ('Error del servidor (' + response.status + ').');
        }
        return {ok: response.ok, status: response.status, json};
    }
    const jsonRequest = (method, body) => ({method, headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body)});

    // ---- estado de pantalla -------------------------------------------
    function clearNoteView() {
        pollToken++;                       // cancela cualquier sondeo en curso
        noteId = null;
        $('transcription').value = '';
        $('organized').value = '';
        $('organized-wrap').hidden = true;
        $('title').value = 'Nota clínica';
        $('result').innerHTML = '';
        $('confirm').disabled = false;
        $('retry').hidden = true;
    }
    function clearAudio() {
        if (recorder && recorder.state === 'recording') { recorder.onstop = null; try { recorder.stop(); } catch (e) {} }
        clearInterval(timerId); seconds = 0; $('timer').textContent = '00:00';
        $('record').disabled = !supportsRecording; $('stop').disabled = true;
        selectedFile = null; $('audio').value = '';
        if (localUrl) { URL.revokeObjectURL(localUrl); localUrl = null; }
        $('preview').removeAttribute('src'); $('preview').hidden = true; $('save-again').hidden = true;
    }

    const extensionFor = mime => mime.includes('ogg') ? 'ogg' : mime.includes('wav') ? 'wav' : mime.includes('mpeg') ? 'mp3' : mime.includes('mp4') ? 'm4a' : 'webm';
    const localName = () => 'scribe_' + new Date().toISOString().slice(0, 10) + '_' + new Date().toTimeString().slice(0, 5).replace(':', '')
        + ($('appointment_id').value ? '_turno-' + $('appointment_id').value : '') + '.' + extensionFor((selectedFile && selectedFile.type) || 'audio/webm');
    const showPreview = file => {
        selectedFile = file;
        if (localUrl) URL.revokeObjectURL(localUrl);
        localUrl = URL.createObjectURL(file);
        $('preview').src = localUrl; $('preview').hidden = false;
        $('save-again').href = localUrl; $('save-again').download = localName(); $('save-again').hidden = false;
        updateButtons();
    };
    const tick = () => {
        seconds++;
        $('timer').textContent = String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
        if (seconds >= maxSeconds) $('stop').click();
    };
    const micError = e => {
        switch (e && e.name) {
            case 'NotAllowedError': case 'SecurityError': return 'El navegador bloqueó el micrófono. Permitilo desde el ícono del candado de la barra de direcciones y volvé a intentar.';
            case 'NotFoundError': case 'OverconstrainedError': return 'No se encontró ningún micrófono conectado.';
            case 'NotReadableError': case 'AbortError': return 'No se pudo usar el micrófono (puede estar ocupado por otra aplicación).';
            default: return 'No se pudo acceder al micrófono. Podés cargar un archivo de audio local.';
        }
    };
    const pickMimeType = () => ['audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus', 'audio/mp4']
        .find(t => window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(t)) || '';

    // ---- selección de turno / archivo ---------------------------------
    if (!supportsRecording) {
        $('record').disabled = true;
        setProgress('Este navegador no permite grabar audio (o la página no es localhost/HTTPS). Podés cargar un archivo local.');
    }
    $('appointment_id').onchange = () => {
        const current = $('appointment_id').value;
        // Al cambiar de turno se limpia TODO lo del turno anterior (nota y audio) para no mezclar pacientes.
        if (previousAppointment && previousAppointment !== current) {
            clearNoteView(); clearAudio();
            setProgress('Cambiaste de turno: se limpió la nota y el audio anteriores.');
        }
        previousAppointment = current;
        updateButtons();
    };
    $('audio').onchange = e => {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > maxBytes) { setProgress('El archivo supera los 25 MB.'); $('audio').value = ''; return; }
        showPreview(file);
    };

    // ---- grabación ----------------------------------------------------
    $('record').onclick = async () => {
        if (!supportsRecording) return setProgress('Este navegador no permite grabar audio. Cargá un archivo local.');
        let stream;
        try { stream = await navigator.mediaDevices.getUserMedia({audio: true}); }
        catch (e) { return setProgress(micError(e)); }

        const mimeType = pickMimeType();
        try {
            recorder = new MediaRecorder(stream, mimeType ? {mimeType, audioBitsPerSecond: 32000} : {audioBitsPerSecond: 32000});
        } catch (e) {
            stream.getTracks().forEach(t => t.stop());
            return setProgress('No se pudo iniciar la grabación en este navegador. Cargá un archivo local.');
        }
        chunks = []; seconds = 0; $('timer').textContent = '00:00';
        recorder.ondataavailable = e => { if (e.data && e.data.size) chunks.push(e.data); };
        recorder.onerror = () => { setProgress('La grabación se interrumpió por un error del navegador.'); $('stop').click(); };
        recorder.onstop = () => {
            stream.getTracks().forEach(t => t.stop());
            if (!chunks.length) return setProgress('No se registró audio. Probá de nuevo.');
            const type = ((recorder && recorder.mimeType) || mimeType || 'audio/webm').split(';')[0];
            showPreview(new File(chunks, 'scribe-recording.' + extensionFor(type), {type}));
            $('save-again').click();
            setProgress('Grabación guardada en tu dispositivo. Podés enviarla o guardarla nuevamente.');
        };
        recorder.start();
        timerId = setInterval(tick, 1000); $('record').disabled = true; $('stop').disabled = false;
    };
    $('stop').onclick = () => {
        clearInterval(timerId);
        if (recorder && recorder.state === 'recording') recorder.stop();
        $('record').disabled = !supportsRecording; $('stop').disabled = true;
    };
    $('retry').onclick = () => { if (selectedFile) $('transcribe').click(); };

    // ---- transcripción ------------------------------------------------
    $('transcribe').onclick = async () => {
        if (!selectedFile || !$('appointment_id').value) return;
        if (selectedFile.size > maxBytes) return setProgress('El archivo supera los 25 MB.');
        clearNoteView();
        const data = new FormData();
        data.append('appointment_id', $('appointment_id').value);
        data.append('audio', selectedFile);
        $('transcribe').disabled = true; setProgress('Enviando audio a Groq...');
        const {ok, json} = await api(routes.transcribe, {method: 'POST', body: data});
        if (!ok) { setProgress(json.message || 'No se pudo enviar el audio.'); $('retry').hidden = false; return updateButtons(); }
        noteId = json.note_id;
        setProgress('Audio recibido. Groq está procesando la transcripción...');
        poll(++pollToken, 0, 0);
    };
    async function poll(token, attempt, failures) {
        if (token !== pollToken) return;
        if (attempt >= maxPolls) {
            setProgress('La transcripción está tardando demasiado. Verificá que el worker de colas esté corriendo y reintentá.');
            $('retry').hidden = false; return updateButtons();
        }
        const {ok, json: note} = await api(routes.notes + '/' + noteId);
        if (token !== pollToken) return;               // cambió el turno o se envió otro audio
        if (!ok) {
            if (failures + 1 >= maxPollFailures) {
                setProgress((note.message || 'No se pudo consultar el estado.') + ' Reintentá el envío.');
                $('retry').hidden = false; return updateButtons();
            }
            return setTimeout(() => poll(token, attempt + 1, failures + 1), pollEveryMs);
        }
        if (note.status === 'transcribed') {
            $('transcription').value = note.transcription || '';
            setProgress('Transcripción lista. Revisala y corregila antes de organizarla.');
            return updateButtons();
        }
        if (note.status === 'failed') {
            setProgress(note.error_message || 'La transcripción falló. Podés volver a cargar la copia local.');
            $('retry').hidden = false; return updateButtons();
        }
        setTimeout(() => poll(token, attempt + 1, 0), pollEveryMs);
    }
    $('transcription').oninput = updateButtons;

    // ---- organizar ----------------------------------------------------
    $('organize').onclick = async () => {
        const token = pollToken;
        $('organize').disabled = true; setProgress('Organizando la transcripción con Groq...');
        const {ok, json} = await api(routes.notes + '/' + noteId + '/organize',
            jsonRequest('POST', {title: $('title').value, transcription: $('transcription').value}));
        if (token !== pollToken) return;               // el usuario cambió de turno mientras tanto
        updateButtons();
        if (!ok) return setProgress(json.message || 'Groq no pudo organizar la nota. Podés reintentar.');
        $('organized').value = (json.note && json.note.organized_content) || '';
        $('organized-wrap').hidden = false;
        setProgress('Nota organizada. Revisala y editála antes de confirmar.');
    };

    // ---- confirmar y exportar -----------------------------------------
    function renderResult(exportOk, message) {
        const base = routes.notes + '/' + noteId;
        const box = $('result');
        box.innerHTML = '';
        const alert = document.createElement('div');
        alert.className = 'alert ' + (exportOk ? 'alert-success' : 'alert-warning');
        alert.textContent = exportOk ? 'Nota confirmada y vinculada al turno.' : (message || 'La nota quedó confirmada, pero no se pudieron generar los archivos.');
        box.appendChild(alert);
        const actions = document.createElement('div');
        actions.className = 'mt-2';
        if (exportOk) {
            [['txt', 'TXT', 'btn-outline-secondary', 'fa-file-alt'], ['docx', 'DOCX', 'btn-outline-primary', 'fa-file-word'], ['pdf', 'PDF', 'btn-outline-danger', 'fa-file-pdf']]
                .forEach(([fmt, label, cls, icon]) => {
                    const a = document.createElement('a');
                    a.className = 'btn btn-sm mr-2 ' + cls; a.href = base + '/download/' + fmt;
                    a.innerHTML = '<i class="fas ' + icon + ' mr-1"></i>' + label;
                    actions.appendChild(a);
                });
        } else {
            const b = document.createElement('button');
            b.type = 'button'; b.className = 'btn btn-sm btn-warning';
            b.innerHTML = '<i class="fas fa-redo mr-1"></i>Reintentar exportación';
            b.onclick = retryExport;
            actions.appendChild(b);
        }
        alert.appendChild(actions);
    }
    async function retryExport() {
        setProgress('Reintentando la generación de archivos...');
        const {ok, json} = await api(routes.notes + '/' + noteId + '/export', {method: 'POST'});
        if (!ok) { setProgress(json.message || 'No se pudo reintentar la exportación.'); return; }
        renderResult(json.export_ok === true, json.message);
        setProgress(json.export_ok ? 'Proceso finalizado.' : (json.message || 'La exportación volvió a fallar.'));
    }
    $('confirm').onclick = async () => {
        const token = pollToken;
        $('confirm').disabled = true; setProgress('Guardando la nota confirmada y generando los documentos...');
        const {ok, json} = await api(routes.notes + '/' + noteId + '/confirm',
            jsonRequest('POST', {title: $('title').value, organized_content: $('organized').value}));
        if (token !== pollToken) return;
        if (!ok) { setProgress(json.message || 'No se pudo confirmar la nota.'); $('confirm').disabled = false; return; }
        renderResult(json.export_ok === true, json.message);
        setProgress(json.export_ok ? 'Proceso finalizado.' : (json.message || 'La nota se confirmó, pero falló la exportación.'));
    };
});
</script>
@stop
