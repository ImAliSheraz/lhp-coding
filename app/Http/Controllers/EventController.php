<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventVisualResource;
use App\Models\Event;
use App\Services\LocationResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Events/Index', [
            'filters' => [
                'status' => $request->status,
                'from' => $request->input('from', '2023-01-01'),
            ],
            'statuses' => ['draft', 'published', 'cancelled', 'sold_out'],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        [$events, $stats] = $this->loadListing($request);

        return response()->json([
            'data' => $events->items(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'total' => $events->total(),
            'stats' => $stats,
        ]);
    }

    public function visualOne(): Response
    {
        return $this->renderVisualPage('Events/VisualOne');
    }

    public function visualTwo(): Response
    {
        return $this->renderVisualPage('Events/VisualTwo');
    }

    public function visualData(Request $request): JsonResponse
    {
        $query = $this->visualQuery($request);

        if (Schema::hasTable('event_attendees')) {
            $query->withCount('attendees');
        }

        $events = $query
            ->paginate((int) $request->input('per_page', 24))
            ->withQueryString();

        return response()->json([
            'data' => EventVisualResource::collection($events->items())->resolve(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'total' => $events->total(),
        ]);
    }

    public function show(Event $event): Response
    {
        $event->load('user');

        if (Schema::hasTable('event_attendees')) {
            $event->loadCount('attendees');
        }

        return Inertia::render('Events/Show', [
            'event' => $event,
            'display' => (new EventVisualResource($event))->resolve(),
        ]);
    }

    private function renderVisualPage(string $page): Response
    {
        return Inertia::render($page, [
            'cities' => LocationResolver::cities(),
            'filters' => ['from' => '', 'to' => '', 'city' => ''],
        ]);
    }

    private function visualQuery(Request $request)
    {
        return Event::query()
            ->where('status', 'published')
            ->when($request->input('from'), function ($query, string $from): void {
                $query->where('created_time', '>=', strtotime($from));
            })
            ->when($request->input('to'), function ($query, string $to): void {
                $query->where('created_time', '<=', strtotime($to.' 23:59:59'));
            })
            ->when($request->input('city'), function ($query, string $city): void {
                LocationResolver::applyCityFilter($query, $city);
            })
            ->orderBy('created_time');
    }

    /**
     * @return array{0: LengthAwarePaginator, 1: array{ms: int, bytes: int}}
     */
    private function loadListing(Request $request): array
    {
        $start = microtime(true);

        $events = Event::with('user')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_time')
            ->paginate(50)
            ->withQueryString();

        $stats = [
            'ms' => (int) round((microtime(true) - $start) * 1000),
            'bytes' => strlen((string) json_encode($events->items())),
        ];

        return [$events, $stats];
    }
}
