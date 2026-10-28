@extends('adminlte::page')

@section('title', 'Consultant')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
<div>
<h1 class="mb-1">Consultant</h1>
<p class="text-muted mb-0">Análisis documental médico y comparación de estudios</p>
</div>
<a href="{{ route('ai.suite.index') }}" class="btn btn-default mt-2 mt-md-0"><i class="fas fa-arrow-left mr-2"></i>Volver a Suite de IA</a>
</div>
@stop

@section('content')
<div class="container-fluid px-0 consultant-page">
<div class="row">
<div class="col-12 col-xl-4">
<div class="card consultant-card">
<div class="card-header bg-white"><h2 class="card-title font-weight-bold"><i class="fas fa-user-injured text-primary mr-2"></i>Documentos</h2></div>
<div class="card-body">
<div class="form-group">
<label for="patient-select">Paciente autorizado</label>
<select class="form-control" id="patient-select">
<option value="">Seleccioná un paciente...</option>
@foreach ($patients as $patient)
<option value="{{ $patient['key'] }}">{{ $patient['name'] }} — {{ $patient['email'] }}</option>
@endforeach
</select>
<small class="form-text text-muted">Solo se muestran pacientes vinculados a turnos que podés consultar.</small>
</div>
<div class="d-flex justify-content-between align-items-center border-top pt-3 mb-2">
<label class="mb-0">Seleccioná el estudio principal</label>
<span class="small text-muted">Uno para analizar · dos para comparar</span>
</div>
<div id="studies-list" class="consultant-studies-list">
<p class="text-muted small mb-0">Elegí un paciente para cargar sus documentos.</p>
</div>
<div class="form-group mt-3">
<label for="analysis-type">¿Qué querés hacer?</label>
<select class="form-control" id="analysis-type">
<option value="document_analysis">Analizar este estudio</option>
<option value="comparison">Compararlo con otro estudio</option>
<option value="extraction">Extraer sus resultados</option>
</select>
<small class="form-text text-muted" id="comparison-help">Para comparar, seleccioná exactamente dos estudios: primero el principal y luego el de comparación.</small>
</div>
<div class="form-group">
<label for="consultant-question">Instrucción opcional</label>
<textarea class="form-control" id="consultant-question" rows="3" maxlength="2000" placeholder="Ej.: ¿Qué cambios observables hay entre ambos estudios?"></textarea>
</div>
<button type="button" class="btn btn-primary btn-block" id="analyze-button" disabled><i class="fas fa-microscope mr-2"></i><span id="analyze-label">Analizar estudio</span></button>
<div id="consultant-status" class="small text-muted mt-2" aria-live="polite"></div>
</div>
</div>
</div>
<div class="col-12 col-xl-8">
<div class="card consultant-card">
<div class="card-header bg-white"><h2 class="card-title font-weight-bold"><i class="fas fa-file-medical-alt text-primary mr-2"></i>Resultado</h2></div>
<div class="card-body" id="consultant-result">
<div class="consultant-empty-state text-center text-muted py-5"><i class="fas fa-clipboard-list d-block mb-3"></i><p class="mb-0">Seleccioná uno o más documentos para comenzar el análisis.</p></div>
</div>
</div>
</div>
</div>
</div>
@stop

@section('css')
<style>
.consultant-card { border: 1px solid #e2e6ea; box-shadow: 0 2px 8px rgba(0, 0, 0, .04); }
.consultant-card .card-header { padding: 1rem 1.15rem; }
.consultant-studies-list { max-height: 330px; overflow-y: auto; }
.consultant-study { background: #f8f9fa; border: 1px solid #e2e6ea; border-radius: .35rem; margin-bottom: .5rem; padding: .65rem .75rem; }
.consultant-study:hover { background: #f0f5ff; }
.consultant-empty-state i { color: #c7ced6; font-size: 2.5rem; }
.consultant-section { border-bottom: 1px solid #e9ecef; padding-bottom: 1rem; margin-bottom: 1rem; }
.consultant-section:last-child { border-bottom: 0; }
.consultant-section h3 { color: #343a40; font-size: 1.1rem; font-weight: 700; }
.consultant-table { font-size: .9rem; }
.consultant-table th { background: #f8f9fa; white-space: nowrap; }
.consultant-warnings { background: #fff8e1; border-left: 3px solid #ffc107; padding: .75rem 1rem; }
#consultant-chart-wrap { min-height: 280px; position: relative; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const patient = document.getElementById('patient-select');
    const studiesList = document.getElementById('studies-list');
    const analyze = document.getElementById('analyze-button');
    const status = document.getElementById('consultant-status');
    const result = document.getElementById('consultant-result');
    let chart = null;

    patient.addEventListener('change', async function () {
        studiesList.innerHTML = '<p class="text-muted small">Cargando estudios...</p>';
    analyze.disabled = true;
    if (!patient.value) {
        studiesList.innerHTML = '<p class="text-muted small">Elegí un paciente para cargar sus documentos.</p>';
    return;
    }
    try {
        const response = await fetch(`{{ route('ai.consultant.studies') }}?patient_key=${encodeURIComponent(patient.value)}`, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'No se pudieron cargar los estudios.');
        studiesList.innerHTML = data.studies.length ? data.studies.map(study => `
        <label class="consultant-study d-flex align-items-start">
        <input type="checkbox" class="study-checkbox mt-1 mr-2" value="${escapeHtml(String(study.id))}">
        <span><strong>${escapeHtml(study.name)}</strong><small class="d-block text-muted">${escapeHtml(study.file_type.toUpperCase())} · ${escapeHtml(study.date || study.appointment_date || 'Fecha no disponible')}</small></span>
        </label>`).join('') : '<p class="text-muted small">Este paciente no tiene estudios cargados.</p>';
    syncButtons();
    } catch (error) {
        studiesList.innerHTML = `<p class="text-danger small">${escapeHtml(error.message)}</p>`;
    }
    });

    studiesList.addEventListener('change', syncButtons);
    document.getElementById('analysis-type').addEventListener('change', syncButtons);

    analyze.addEventListener('click', async function () {
        const selected = [...document.querySelectorAll('.study-checkbox:checked')];
        const studyIds = selected.map(box => Number(box.value));
        const mode = document.getElementById('analysis-type').value;
        if (!patient.value || !studyIds.length || (mode === 'comparison' && studyIds.length !== 2)) return;
        analyze.disabled = true;
        status.textContent = 'Extrayendo documentos y preparando el análisis...';
    result.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><p>Consultant está analizando los documentos seleccionados.</p></div>';
    try {
        const response = await fetch('{{ route('ai.consultant.analyze') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                     body: JSON.stringify({ patient_key: patient.value, study_ids: studyIds, analysis_type: mode, question: document.getElementById('consultant-question').value.trim() })
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'No se pudo completar el análisis.');
        renderAnalysis(data.analysis);
        status.textContent = 'Análisis completado.';
    } catch (error) {
        result.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(error.message)}</div>`;
        status.textContent = '';
    } finally { analyze.disabled = false; }
    });

    function syncButtons() {
        const checked = document.querySelectorAll('.study-checkbox:checked');
        const mode = document.getElementById('analysis-type').value;
        const comparison = mode === 'comparison';
checked.forEach((box, index) => { box.disabled = !box.checked && comparison && checked.length >= 2; });
analyze.disabled = comparison ? checked.length !== 2 : checked.length !== 1;
document.getElementById('analyze-label').textContent = comparison ? 'Comparar estudios' : (mode === 'extraction' ? 'Extraer resultados' : 'Analizar estudio');
document.getElementById('comparison-help').classList.toggle('text-primary', comparison);
    }

    function renderAnalysis(data) {
        if (chart) { chart.destroy(); chart = null; }
        const findings = data.findings || [];
        const comparisons = data.comparisons || [];
        const documents = data.documents_reviewed || [];
        const warnings = data.warnings || [];
        result.innerHTML = `
        <section class="consultant-section"><h3><i class="fas fa-comment-medical text-primary mr-2"></i>Resumen</h3><p>${escapeHtml(data.message || 'Sin resumen disponible.')}</p></section>
        <section class="consultant-section"><h3><i class="fas fa-file-alt text-primary mr-2"></i>Documentos analizados</h3>${renderDocuments(documents)}</section>
        <section class="consultant-section"><h3><i class="fas fa-list-ol text-primary mr-2"></i>Datos extraídos</h3>${renderFindings(findings)}</section>
        <section class="consultant-section"><h3><i class="fas fa-exchange-alt text-primary mr-2"></i>Comparaciones</h3>${renderComparisons(comparisons)}</section>
        <section class="consultant-section"><h3><i class="fas fa-chart-line text-primary mr-2"></i>Evolución</h3><div id="consultant-chart-wrap">${data.chart && data.chart.available ? '<canvas id="consultant-chart"></canvas>' : `<p class="text-muted">${escapeHtml(data.chart?.reason_unavailable || 'No hay datos suficientes para graficar.')}</p>`}</div></section>
        ${warnings.length ? `<section class="consultant-warnings"><strong>Advertencias</strong><ul class="mb-0 mt-2">${warnings.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul></section>` : ''}`;
        if (data.chart && data.chart.available) renderChart(data.chart);
    }

    function renderDocuments(items) { return items.length ? '<ul class="mb-0">' + items.map(item => `<li><strong>${escapeHtml(item.name)}</strong> · ${escapeHtml(item.document_type)} · ${escapeHtml(item.date || 'Fecha no disponible')}</li>`).join('') + '</ul>' : '<p class="text-muted mb-0">Sin documentos identificados.</p>'; }
    function renderFindings(items) { return items.length ? `<div class="table-responsive"><table class="table table-sm table-bordered consultant-table"><thead><tr><th>Parámetro</th><th>Valor</th><th>Fecha</th><th>Rango</th><th>Fuente</th></tr></thead><tbody>${items.map(item => `<tr><td>${escapeHtml(item.parameter)}</td><td>${escapeHtml(item.value)} ${escapeHtml(item.unit)}</td><td>${escapeHtml(item.date)}</td><td>${escapeHtml(item.reference_range || 'No informado')}</td><td>${escapeHtml(item.source_document)}${item.page_reference ? ' · pág. ' + escapeHtml(item.page_reference) : ''}</td></tr>`).join('')}</tbody></table></div>` : '<p class="text-muted mb-0">No se extrajeron mediciones verificables.</p>'; }
    function renderComparisons(items) { return items.length ? `<div class="table-responsive"><table class="table table-sm table-bordered consultant-table"><thead><tr><th>Parámetro</th><th>Anterior</th><th>Posterior</th><th>Comparable</th><th>Observación</th></tr></thead><tbody>${items.map(item => `<tr><td>${escapeHtml(item.parameter)}</td><td>${escapeHtml(item.earlier_value)} ${escapeHtml(item.earlier_unit)}<br><small>${escapeHtml(item.earlier_date)}</small></td><td>${escapeHtml(item.later_value)} ${escapeHtml(item.later_unit)}<br><small>${escapeHtml(item.later_date)}</small></td><td>${item.comparable ? 'Sí' : 'No'}</td><td>${escapeHtml(item.observation || item.difference)}</td></tr>`).join('')}</tbody></table></div>` : '<p class="text-muted mb-0">No hay comparaciones válidas disponibles.</p>'; }
    function renderChart(info) { chart = new Chart(document.getElementById('consultant-chart'), { type: info.type === 'bar' ? 'bar' : 'line', data: { labels: info.data_points.map(point => point.date), datasets: [{ label: `${info.parameter}${info.unit ? ' (' + info.unit + ')' : ''}`, data: info.data_points.map(point => point.value), borderColor: '#007bff', backgroundColor: 'rgba(0,123,255,.18)', tension: .25 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: info.title || 'Evolución' } }, scales: { y: { title: { display: true, text: info.y_axis_label || info.unit || 'Valor' } }, x: { title: { display: true, text: info.x_axis_label || 'Fecha' } } } } }); }
    function escapeHtml(value) { const div = document.createElement('div'); div.textContent = value ?? ''; return div.innerHTML; }
});
</script>
@stop
