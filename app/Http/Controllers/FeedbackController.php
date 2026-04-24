<?php

namespace App\Http\Controllers;

use App\Models\FeedbackCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function index(): Response
    {
        $institucionId = auth()->user()->institucion_id;

        $cards = FeedbackCard::where('institucion_id', $institucionId)
            ->with('autor:id,nombre,usuario')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($c) => [
                'id'          => $c->id,
                'titulo'      => $c->titulo,
                'descripcion' => $c->descripcion,
                'tags'        => $c->tags ?? [],
                'prioridad'   => $c->prioridad,
                'columna'     => $c->columna,
                'creado_por'  => $c->autor ? [
                    'id'     => $c->autor->id,
                    'nombre' => $c->autor->nombre,
                ] : null,
                'created_at'  => $c->created_at->toDateString(),
            ]);

        return Inertia::render('Admin/Feedback/Index', [
            'cards' => $cards,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:50',
            'prioridad'   => 'required|in:low,medium,high,urgent',
            'columna'     => 'nullable|in:backlog,in_progress,completed,published',
        ]);

        FeedbackCard::create([
            ...$data,
            'columna'       => $data['columna'] ?? 'backlog',
            'creado_por'    => auth()->id(),
            'institucion_id' => auth()->user()->institucion_id,
        ]);

        return back()->with('success', 'Tarjeta creada.');
    }

    public function update(Request $request, FeedbackCard $feedbackCard): RedirectResponse
    {
        $this->authorize_institucion($feedbackCard);

        $data = $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:50',
            'prioridad'   => 'required|in:low,medium,high,urgent',
        ]);

        $feedbackCard->update($data);

        return back()->with('success', 'Tarjeta actualizada.');
    }

    public function mover(Request $request, FeedbackCard $feedbackCard): RedirectResponse
    {
        $this->authorize_institucion($feedbackCard);

        $request->validate([
            'columna' => 'required|in:backlog,in_progress,completed,published',
        ]);

        $feedbackCard->update(['columna' => $request->columna]);

        return back();
    }

    public function destroy(FeedbackCard $feedbackCard): RedirectResponse
    {
        $this->authorize_institucion($feedbackCard);
        $feedbackCard->delete();

        return back()->with('success', 'Tarjeta eliminada.');
    }

    private function authorize_institucion(FeedbackCard $card): void
    {
        abort_if($card->institucion_id !== auth()->user()->institucion_id, 403);
    }
}
