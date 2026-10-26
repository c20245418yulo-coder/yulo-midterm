<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Agent;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Services\RealEstateService;

class PropertyController extends Controller
{
    public function __construct(
        private RealEstateService $service
    ) {}

    public function index(Request $request)
    {
        $properties = $this->service->getPaginatedProperties(
            10,
            $request->input('search')
        );

        return Inertia::render('properties/index', [
            'properties' => $properties,
            'filters' => [
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function create()
    {
        $agents = Agent::select([
            'id',
            'agent_name',
        ])
        ->orderBy('agent_name')
        ->get();

        return Inertia::render('properties/create', [
            'agents' => $agents,
        ]);
    }

    public function store(StorePropertyRequest $request)
    {
        $this->service->createProperty($request->validated());

        return redirect()
            ->route('properties.index')
            ->with('success', 'Property created successfully.');
    }

    public function update(UpdatePropertyRequest $request, int $id)
    {
        $this->service->updateProperty(
            $request->validated(),
            $id
        );

        return redirect()
            ->route('properties.index')
            ->with('success', 'Property updated successfully.');
    }
}
