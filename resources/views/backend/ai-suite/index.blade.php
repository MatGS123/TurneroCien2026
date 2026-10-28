@extends('adminlte::page')

@section('title', 'Suite de IA')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
<div>
<h1 class="mb-1">Suite de IA</h1>
<p class="text-muted mb-0">Seleccioná un agente para conocer sus capacidades.</p>
</div>
</div>
@stop

@section('content')
<div class="container-fluid px-0 ai-suite-page">
<div class="row align-items-stretch">
<div class="col-12 col-lg-3 mb-3 mb-lg-0">
<section class="card h-100 mb-0" aria-labelledby="ai-agents-title">
<div class="card-header bg-white border-bottom">
<h2 id="ai-agents-title" class="card-title font-weight-bold mb-0">Agentes</h2>
</div>
<div class="card-body p-2">
<div class="ai-agent-list" role="list">
@foreach ($agents as $agent)
<button type="button"
class="ai-agent-item btn btn-block text-left d-flex align-items-center {{ !$agent['available'] ? 'ai-agent-item-muted' : '' }}"
data-agent-id="{{ $agent['id'] }}"
data-action-url="{{ $agent['id'] === 'consultant' ? url('/suite-ia/consultant') : url('/suite-ia/researcher') }}"
role="listitem"
aria-pressed="false">
<span class="ai-agent-icon mr-3" aria-hidden="true"><i class="{{ $agent['icon'] }}"></i></span>
<span class="flex-grow-1">
<span class="d-block font-weight-bold">{{ $agent['name'] }}</span>
@unless ($agent['available'])
<small class="text-muted">Próximamente</small>
@endunless
</span>
<i class="fas fa-chevron-right text-muted small" aria-hidden="true"></i>
</button>
@endforeach
</div>
</div>
</section>
</div>

<div class="col-12 col-lg-4 mb-3 mb-lg-0">
<section class="card h-100 mb-0" aria-labelledby="ai-agent-detail-title">
<div class="card-header bg-white border-bottom">
<h2 id="ai-agent-detail-title" class="card-title font-weight-bold mb-0">Descripción</h2>
</div>
<div class="card-body d-flex align-items-center justify-content-center text-center" id="ai-agent-detail" aria-live="polite">
<div class="ai-empty-state">
<span class="ai-empty-icon"><i class="fas fa-robot"></i></span>
<p class="text-muted mb-0">Seleccioná un agente para ver su descripción.</p>
</div>
</div>
</section>
</div>

<div class="col-12 col-lg-5">
<section class="card h-100 mb-0" aria-labelledby="ai-agent-action-title">
<div class="card-header bg-white border-bottom">
<h2 id="ai-agent-action-title" class="card-title font-weight-bold mb-0">Acción</h2>
</div>
<div class="card-body d-flex align-items-center justify-content-center text-center" id="ai-agent-action" aria-live="polite">
<div class="ai-empty-state">
<span class="ai-empty-icon"><i class="fas fa-bolt"></i></span>
<p class="text-muted mb-0">La acción del agente aparecerá acá.</p>
</div>
</div>
</section>
</div>
</div>
</div>
@stop

@section('css')
<style>
.ai-suite-page .card { border: 1px solid #e2e6ea; box-shadow: 0 2px 8px rgba(0, 0, 0, .04); }
.ai-suite-page .card-header { padding: 1rem 1.15rem; }
.ai-suite-page .card-body { min-height: 300px; }
.ai-agent-item { border: 0; border-radius: .35rem; color: #343a40; margin-bottom: .35rem; padding: .8rem .75rem; transition: background-color .15s ease, box-shadow .15s ease; }
.ai-agent-item:hover, .ai-agent-item:focus { background-color: #f0f5ff; box-shadow: inset 3px 0 0 #007bff; outline: none; }
.ai-agent-item.is-selected { background-color: #e8f1ff; box-shadow: inset 3px 0 0 #007bff; color: #0056b3; }
.ai-agent-item-muted { color: #6c757d; }
.ai-agent-icon { align-items: center; background: #f1f4f8; border-radius: 50%; color: #6c757d; display: inline-flex; height: 38px; justify-content: center; width: 38px; }
.is-selected .ai-agent-icon { background: #d6e7ff; color: #007bff; }
.ai-empty-state { max-width: 280px; }
.ai-empty-icon { color: #c7ced6; display: block; font-size: 2.5rem; margin-bottom: 1rem; }
.ai-agent-description-icon { color: #007bff; font-size: 2.25rem; margin-bottom: 1rem; }
.ai-agent-description h3 { font-size: 1.35rem; }
.ai-agent-action-label { color: #6c757d; font-size: .9rem; }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const agents = @json($agents);
    const detail = document.getElementById('ai-agent-detail');
    const action = document.getElementById('ai-agent-action');
    const buttons = document.querySelectorAll('[data-agent-id]');

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            const agent = agents.find(function (item) {
                return item.id === button.dataset.agentId;
            });

            if (!agent) return;

            buttons.forEach(function (item) {
                const selected = item === button;
                item.classList.toggle('is-selected', selected);
                item.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });

            detail.innerHTML = `
            <div class="ai-agent-description">
            <div class="ai-agent-description-icon"><i class="${agent.icon}"></i></div>
            <h3 class="font-weight-bold mb-3">${agent.name}</h3>
            <p class="text-muted mb-0">${agent.description}</p>
            </div>`;

            const actionUrl = button.dataset.actionUrl;

            action.innerHTML = agent.available
            ? `<div><p class="ai-agent-action-label mb-3">Accedé al espacio de trabajo de este agente.</p><a href="${actionUrl}" class="btn btn-primary btn-lg px-5"> <i class="fas fa-arrow-right mr-2"></i>Acceder</a></div>`
            : `<div><p class="ai-agent-action-label mb-3">Este agente estará disponible próximamente.</p><button type="button" class="btn btn-secondary btn-lg px-5" disabled><i class="fas fa-clock mr-2"></i>Próximamente</button></div>`;
        });
    });
});
</script>
@stop
s
