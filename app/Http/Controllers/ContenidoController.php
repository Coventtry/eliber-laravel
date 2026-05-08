<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FooterLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContenidoController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $institucion = $user->institucion;

        $faqs = Faq::where('institucion_id', $user->institucion_id)
            ->orderBy('orden')->orderBy('id')
            ->get(['id', 'pregunta', 'respuesta', 'orden', 'activa']);

        $footerLinks = FooterLink::where('institucion_id', $user->institucion_id)
            ->orderBy('orden')->orderBy('id')
            ->get(['id', 'label', 'url', 'orden']);

        return Inertia::render('Admin/Contenido/Index', [
            'faqs' => $faqs,
            'footerLinks' => $footerLinks,
            'anuncio' => [
                'texto' => $institucion->anuncio_texto ?? '',
                'estilo' => $institucion->anuncio_estilo ?? 'info',
                'activo' => (bool) ($institucion->anuncio_activo ?? false),
            ],
        ]);
    }

    // ── FAQs ─────────────────────────────────────────────────────────────────

    public function storeFaq(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pregunta' => 'required|string|max:255',
            'respuesta' => 'required|string|max:3000',
            'orden' => 'nullable|integer|min:0',
        ]);

        Faq::create([...$data, 'institucion_id' => auth()->user()->institucion_id]);

        return back()->with('success', 'Pregunta creada.');
    }

    public function updateFaq(Request $request, Faq $faq): RedirectResponse
    {
        $this->authorize_tenant($faq->institucion_id);

        $data = $request->validate([
            'pregunta' => 'required|string|max:255',
            'respuesta' => 'required|string|max:3000',
            'orden' => 'nullable|integer|min:0',
            'activa' => 'boolean',
        ]);

        $faq->update($data);

        return back()->with('success', 'Pregunta actualizada.');
    }

    public function destroyFaq(Faq $faq): RedirectResponse
    {
        $this->authorize_tenant($faq->institucion_id);
        $faq->delete();

        return back()->with('success', 'Pregunta eliminada.');
    }

    // ── Footer links ──────────────────────────────────────────────────────────

    public function storeFooterLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => 'required|string|max:100',
            'url' => 'required|string|max:500',
            'orden' => 'nullable|integer|min:0',
        ]);

        FooterLink::create([...$data, 'institucion_id' => auth()->user()->institucion_id]);

        return back()->with('success', 'Enlace creado.');
    }

    public function updateFooterLink(Request $request, FooterLink $footerLink): RedirectResponse
    {
        $this->authorize_tenant($footerLink->institucion_id);

        $data = $request->validate([
            'label' => 'required|string|max:100',
            'url' => 'required|string|max:500',
            'orden' => 'nullable|integer|min:0',
        ]);

        $footerLink->update($data);

        return back()->with('success', 'Enlace actualizado.');
    }

    public function destroyFooterLink(FooterLink $footerLink): RedirectResponse
    {
        $this->authorize_tenant($footerLink->institucion_id);
        $footerLink->delete();

        return back()->with('success', 'Enlace eliminado.');
    }

    // ── Anuncio ───────────────────────────────────────────────────────────────

    public function updateAnuncio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'texto' => 'nullable|string|max:500',
            'estilo' => 'required|in:warning,danger,info,success',
            'activo' => 'boolean',
        ]);

        auth()->user()->institucion->update([
            'anuncio_texto' => $data['texto'] ?? null,
            'anuncio_estilo' => $data['estilo'],
            'anuncio_activo' => $data['activo'] ?? false,
        ]);

        return back()->with('success', 'Anuncio actualizado.');
    }

    private function authorize_tenant(int $institucionId): void
    {
        abort_if($institucionId !== auth()->user()->institucion_id, 403);
    }
}
