@extends('adminlte::page')

@section('title', 'Researcher')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
<div>
<h1 class="mb-1">Researcher</h1>
<p class="text-muted mb-0">Agente de investigación médica</p>
</div>
<a href="{{ route('ai.suite.index') }}" class="btn btn-default mt-2 mt-md-0"><i class="fas fa-arrow-left mr-2"></i>Volver a Suite de IA</a>
</div>
@stop

@section('content')
<div class="container-fluid px-0 researcher-page">
<div class="card mb-0 researcher-card">
<div class="card-header bg-white d-flex align-items-center">
<span class="researcher-avatar mr-3"><i class="fas fa-search"></i></span>
<div>
<h2 class="card-title font-weight-bold mb-1">Researcher</h2>
<small class="text-muted">Investigación médica basada en evidencia</small>
</div>
</div>
<div class="card-body p-0 d-flex flex-column">
<div class="researcher-messages flex-grow-1" id="researcher-messages" aria-live="polite">
<div class="researcher-message researcher-message-agent">
<strong class="d-block mb-1">Researcher</strong>
Hola, soy Researcher. Podés iniciar una consulta sobre evidencia científica médica.
</div>
</div>
<form class="researcher-composer border-top p-3" id="researcher-form" action="{{ route('ai.researcher.ask') }}" method="POST">
@csrf
<label class="sr-only" for="researcher-input">Mensaje para Researcher</label>
<div class="input-group">
<input type="text" class="form-control" id="researcher-input" placeholder="Escribí tu consulta..." autocomplete="off">
<div class="input-group-append">
<button class="btn btn-primary px-4" id="researcher-submit" type="submit"><i class="fas fa-paper-plane mr-2"></i><span>Enviar</span></button>
</div>
</div>
</form>
</div>
</div>
</div>
@stop

@section('css')
<style>
.researcher-card { border: 1px solid #e2e6ea; box-shadow: 0 2px 8px rgba(0, 0, 0, .04); min-height: calc(100vh - 235px); }
.researcher-card .card-header { padding: 1rem 1.15rem; }
.researcher-avatar { align-items: center; background: #e8f1ff; border-radius: 50%; color: #007bff; display: inline-flex; height: 42px; justify-content: center; width: 42px; }
.researcher-messages { background: #f8f9fa; min-height: 330px; overflow-y: auto; padding: 1.5rem; }
.researcher-empty-state { color: #adb5bd; margin: 5rem auto; }
.researcher-empty-state > i { font-size: 2.5rem; }
.researcher-message { border-radius: .5rem; line-height: 1.5; margin-bottom: 1rem; max-width: 78%; padding: .75rem 1rem; }
.researcher-message-user { background: #007bff; color: white; margin-left: auto; }
.researcher-message-agent { background: white; border: 1px solid #e2e6ea; color: #343a40; margin-right: auto; }
.researcher-message-error { background: #fff3f3; border: 1px solid #f1b0b7; color: #842029; margin-right: auto; }
.researcher-response { line-height: 1.65; }
.researcher-response h1, .researcher-response h2, .researcher-response h3 { color: #212529; font-size: 1.08rem; font-weight: 700; margin: 1rem 0 .45rem; }
.researcher-response h1:first-child, .researcher-response h2:first-child, .researcher-response h3:first-child { margin-top: 0; }
.researcher-response p { margin-bottom: .7rem; }
.researcher-response ul, .researcher-response ol { margin-bottom: .8rem; padding-left: 1.35rem; }
.researcher-response li { margin-bottom: .25rem; }
.researcher-response blockquote { border-left: 3px solid #007bff; color: #6c757d; margin: .8rem 0; padding-left: .85rem; }
.researcher-response code { background: #f1f3f5; border-radius: .2rem; padding: .1rem .25rem; }
.researcher-response pre { background: #f1f3f5; border-radius: .35rem; overflow-x: auto; padding: .75rem; }
.researcher-response table { border-collapse: collapse; margin: .8rem 0; width: 100%; }
.researcher-response th, .researcher-response td { border: 1px solid #dee2e6; padding: .45rem .6rem; text-align: left; }
.researcher-response th { background: #f8f9fa; font-weight: 700; }
.researcher-response a { color: #0056b3; }
.researcher-loading { color: #6c757d; font-style: italic; }
.researcher-composer { background: white; }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('researcher-form');
    const input = document.getElementById('researcher-input');
    const messages = document.getElementById('researcher-messages');
    const submit = document.getElementById('researcher-submit');

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const text = input.value.trim();
        if (!text) return;

                          appendMessage(text, 'user');
        input.value = '';
    input.disabled = true;
    submit.disabled = true;
    submit.querySelector('span').textContent = 'Consultando...';
    const loading = appendMessage('Researcher está consultando evidencia médica...', 'agent', true);
    messages.scrollTop = messages.scrollHeight;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            },
            body: JSON.stringify({ message: text }),
        });
        const data = await response.json();

        loading.remove();
        if (!response.ok) {
            throw new Error(data.message || 'No se pudo obtener una respuesta.');
        }

        appendMessage(data.answer_html || data.answer, 'agent', false, Boolean(data.answer_html));
    } catch (error) {
        loading.remove();
        appendMessage(error.message || 'Ocurrió un error al consultar Researcher.', 'error');
    } finally {
        input.disabled = false;
        submit.disabled = false;
        submit.querySelector('span').textContent = 'Enviar';
    input.focus();
    messages.scrollTop = messages.scrollHeight;
    }
    });

    function appendMessage(text, type, isLoading = false, isHtml = false) {
        const message = document.createElement('div');
        message.className = 'researcher-message ' + (type === 'user'
        ? 'researcher-message-user'
        : type === 'error' ? 'researcher-message-error' : 'researcher-message-agent');
        if (type !== 'user') {
            const author = document.createElement('strong');
            author.className = 'd-block mb-1';
author.textContent = type === 'error' ? 'Error' : 'Researcher';
message.appendChild(author);
        }
        const content = document.createElement(isHtml ? 'div' : 'span');
        content.className = isHtml ? 'researcher-response' : '';
if (isHtml) content.innerHTML = text;
else content.textContent = text;
if (isLoading) content.classList.add('researcher-loading');
message.appendChild(content);
messages.appendChild(message);
return message;
    }
});
</script>
@stop
