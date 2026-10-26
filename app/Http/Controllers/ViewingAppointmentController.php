<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Client;
use App\Models\Agent;
use App\Models\Property;
use App\Models\ViewingAppointment;
use App\Http\Requests\StoreViewingAppointmentRequest;
use App\Http\Requests\UpdateViewingAppointmentRequest;
use App\Services\RealEstateService;

class ViewingAppointmentController extends Controller
{
    public function __construct(
        private RealEstateService $service
    ) {}

    public function index()
    {
        $appointments = ViewingAppointment::select([
            'id',
            'client_id',
            'property_id',
            'agent_id',
            'viewing_date',
            'viewing_time',
            'viewing_status',
            'client_feedback',
        ])
        ->with([
            'client:id,client_name,client_email',
            'property:id,property_code,property_type,listing_price',
            'agent:id,agent_name',
        ])
        ->latest()
        ->paginate(10);

        return Inertia::render('viewing-appointments/index', [
            'appointments' => $appointments,
        ]);
    }

    public function create()
    {
        $clients = Client::select([
            'id',
            'client_name',
        ])
        ->orderBy('client_name')
        ->get();

        $properties = Property::select([
            'id',
            'property_code',
            'property_type',
        ])
        ->orderBy('property_code')
        ->get();

        $agents = Agent::select([
            'id',
            'agent_name',
        ])
        ->orderBy('agent_name')
        ->get();

        return Inertia::render('viewing-appointments/create', [
            'clients' => $clients,
            'properties' => $properties,
            'agents' => $agents,
        ]);
    }

    public function store(StoreViewingAppointmentRequest $request)
    {
        $this->service->createViewingAppointment($request->validated());

        return redirect()
            ->route('viewing-appointments.index')
            ->with('success', 'Viewing appointment created successfully.');
    }

    public function update(UpdateViewingAppointmentRequest $request, int $id)
    {
        $this->service->updateViewingAppointment($request->validated(), $id);

        return redirect()
            ->route('viewing-appointments.index')
            ->with('success', 'Viewing appointment updated successfully.');
    }
}
