<?php

namespace App\Http\Controllers;

use App\Models\ObraSocial;
use App\Models\Coseguro;
use App\Models\Service;
use Illuminate\Http\Request;

class ObraSocialController extends Controller
{
    public function index()
    {
        $obrasSociales = ObraSocial::orderBy('nombre')->get();
        return view('backend.obras_sociales.index', compact('obrasSociales'));
    }

    public function create()
    {
        return view('backend.obras_sociales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:255',
            'plan'       => 'nullable|string|max:100',
            'prestadora' => 'nullable|string|max:150',
            'activo'     => 'boolean',
        ]);

        ObraSocial::create([
            'nombre'     => $request->nombre,
            'plan'       => $request->plan,
            'prestadora' => $request->prestadora,
            'activo'     => $request->boolean('activo', true),
        ]);

        return redirect()->route('obras-sociales.index')
        ->with('success', 'Obra social creada correctamente.');
    }

    public function edit(ObraSocial $obraSocial)
    {
        $services  = Service::where('status', 1)->orderBy('title')->get();
        $coseguros = $obraSocial->coseguros()->with('service')->get();
        return view('backend.obras_sociales.edit', compact('obraSocial', 'services', 'coseguros'));
    }

    public function update(Request $request, ObraSocial $obraSocial)
    {
        $request->validate([
            'nombre'     => 'required|string|max:255',
            'plan'       => 'nullable|string|max:100',
            'prestadora' => 'nullable|string|max:150',
            'activo'     => 'boolean',
        ]);

        $obraSocial->update([
            'nombre'     => $request->nombre,
            'plan'       => $request->plan,
            'prestadora' => $request->prestadora,
            'activo'     => $request->boolean('activo', true),
        ]);

        return redirect()->route('obras-sociales.index')
        ->with('success', 'Obra social actualizada correctamente.');
    }

    public function destroy(ObraSocial $obraSocial)
    {
        $obraSocial->delete();
        return redirect()->route('obras-sociales.index')
        ->with('success', 'Obra social eliminada correctamente.');
    }

    // --- Coseguros ---

    public function storeCoseguro(Request $request, ObraSocial $obraSocial)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'monto'      => 'required|numeric|min:0',
        ]);

        // Evitar duplicado obra+servicio
        $existe = Coseguro::where('obra_social_id', $obraSocial->id)
        ->where('service_id', $request->service_id)
        ->exists();

        if ($existe) {
            return redirect()->back()
            ->with('error', 'Ya existe un coseguro para ese servicio en esta obra social.');
        }

        Coseguro::create([
            'obra_social_id' => $obraSocial->id,
            'service_id'     => $request->service_id,
            'monto'          => $request->monto,
            'activo'         => true,
        ]);

        return redirect()->back()
        ->with('success', 'Coseguro agregado correctamente.');
    }

    public function destroyCoseguro(ObraSocial $obraSocial, Coseguro $coseguro)
    {
        $coseguro->delete();
        return redirect()->back()
        ->with('success', 'Coseguro eliminado.');
    }

    // Para el select dinámico del formulario de turnos (mantiene compatibilidad)
    public function listar()
    {
        $obras = ObraSocial::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre', 'plan']);
        return response()->json($obras);
    }
}
