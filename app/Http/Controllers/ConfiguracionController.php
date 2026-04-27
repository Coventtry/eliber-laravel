<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ConfiguracionController extends Controller
{
    private array $DEFAULTS = [
        'dias_prestamo'        => '14',
        'dias_alerta_previa'   => '4',
        'nombre_institucion'   => '',
        'logo_url'             => '',
    ];

    public function index(): Response
    {
        $instId      = tenantId();
        $institucion = Institucion::find($instId);

        $config = [];
        foreach ($this->DEFAULTS as $clave => $default) {
            $config[$clave] = Configuracion::get($instId, $clave, $default);
        }

        if (empty($config['nombre_institucion'])) {
            $config['nombre_institucion'] = $institucion->nombre ?? '';
        }

        return Inertia::render('Admin/Configuracion/Index', [
            'config' => $config,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_institucion' => 'nullable|string|max:200',
            'dias_prestamo'      => 'required|integer|min:1|max:365',
            'dias_alerta_previa' => 'required|integer|min:1|max:30',
            'logo'               => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
        ]);

        $instId      = tenantId();
        $institucion = Institucion::find($instId);

        Configuracion::set($instId, 'dias_prestamo',      (string) $data['dias_prestamo']);
        Configuracion::set($instId, 'dias_alerta_previa', (string) $data['dias_alerta_previa']);

        if (!empty($data['nombre_institucion'])) {
            Configuracion::set($instId, 'nombre_institucion', $data['nombre_institucion']);
            $institucion?->update(['nombre' => $data['nombre_institucion']]);
        }

        if ($request->hasFile('logo')) {
            $oldLogo = Configuracion::get($instId, 'logo_url');
            if ($oldLogo) {
                $file = str_replace(Storage::disk('public')->url(''), '', $oldLogo);
                Storage::disk('public')->delete($file);
            }
            $path    = $request->file('logo')->store('logos', 'public');
            $logoUrl = asset('storage/' . $path);
            Configuracion::set($instId, 'logo_url', $logoUrl);
        }

        return back()->with('success', 'Configuración guardada.');
    }
}
