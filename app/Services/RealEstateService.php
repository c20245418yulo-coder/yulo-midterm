<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Client;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Models\ViewingAppointment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RealEstateService
{
    /*
    |--------------------------------------------------------------------------
    | CLIENTS
    |--------------------------------------------------------------------------
    */

    public function getPaginatedClients(
        int $perPage = 10,
        ?string $search = null
    ): LengthAwarePaginator {
        return Client::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                        ->orWhere('client_email', 'like', "%{$search}%")
                        ->orWhere('client_phone', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createClient(array $data): Client
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['client_email'])) {
                $data['client_email'] = trim($data['client_email']);
            }

            return Client::create($data);
        });
    }

    public function updateClient(array $data, int $id): Client
    {
        return DB::transaction(function () use ($data, $id) {
            $client = Client::findOrFail($id);

            if (isset($data['client_email'])) {
                $data['client_email'] = trim($data['client_email']);
            }

            $client->update($data);

            return $client->refresh();
        });
    }

    public function deleteClient(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return (bool) Client::findOrFail($id)->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | AGENTS
    |--------------------------------------------------------------------------
    */

    public function getPaginatedAgents(
        int $perPage = 10,
        ?string $search = null
    ): LengthAwarePaginator {
        return Agent::query()
            ->when($search, function ($query, $search) {
                $query->where(
                    'agent_name',
                    'like',
                    "%{$search}%"
                );
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createAgent(array $data): Agent
    {
        return DB::transaction(function () use ($data) {
            return Agent::create($data);
        });
    }

    public function updateAgent(array $data, int $id): Agent
    {
        return DB::transaction(function () use ($data, $id) {
            $agent = Agent::findOrFail($id);
            $agent->update($data);

            return $agent->refresh();
        });
    }

    public function deleteAgent(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return (bool) Agent::findOrFail($id)->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PROPERTIES
    |--------------------------------------------------------------------------
    */

    public function getPaginatedProperties(
        int $perPage = 10,
        ?string $search = null
    ): LengthAwarePaginator {
        return Property::with('agent')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'property_code',
                        'like',
                        "%{$search}%"
                    )->orWhere(
                        'property_type',
                        'like',
                        "%{$search}%"
                    );
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createProperty(array $data): Property
    {
        return DB::transaction(function () use ($data) {
            $data['property_code'] = strtoupper(
                trim($data['property_code'])
            );

            return Property::create($data);
        });
    }

    public function updateProperty(array $data, int $id): Property
    {
        return DB::transaction(function () use ($data, $id) {
            $property = Property::findOrFail($id);

            if (isset($data['property_code'])) {
                $data['property_code'] = strtoupper(
                    trim($data['property_code'])
                );
            }

            $property->update($data);

            return $property->refresh();
        });
    }

    public function deleteProperty(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return (bool) Property::findOrFail($id)->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | VIEWING APPOINTMENTS
    |--------------------------------------------------------------------------
    */

    public function getPaginatedAppointments(
        int $perPage = 10,
        ?string $search = null
    ): LengthAwarePaginator {
        return ViewingAppointment::with([
                'client',
                'property',
                'agent',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'viewing_status',
                        'like',
                        "%{$search}%"
                    )->orWhere(
                        'viewing_date',
                        'like',
                        "%{$search}%"
                    )->orWhereHas(
                        'client',
                        function ($clientQuery) use ($search) {
                            $clientQuery->where(
                                'client_name',
                                'like',
                                "%{$search}%"
                            );
                        }
                    )->orWhereHas(
                        'property',
                        function ($propertyQuery) use ($search) {
                            $propertyQuery->where(
                                'property_code',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
                });
            })
            ->latest('viewing_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createViewingAppointment(
        array $data
    ): ViewingAppointment {
        return DB::transaction(function () use ($data) {
            $this->checkAgentSchedule($data);

            $appointment = ViewingAppointment::create($data);

            Property::where(
                'id',
                $appointment->property_id
            )->increment('viewing_count');

            return $appointment;
        });
    }

    public function updateViewingAppointment(
        array $data,
        int $id
    ): ViewingAppointment {
        return DB::transaction(function () use ($data, $id) {
            $appointment = ViewingAppointment::findOrFail($id);

            $scheduleData = array_merge(
                $appointment->only([
                    'agent_id',
                    'property_id',
                    'viewing_date',
                    'viewing_time',
                ]),
                $data
            );

            $this->checkAgentSchedule($scheduleData, $id);

            $oldPropertyId = $appointment->property_id;
            $newPropertyId = $scheduleData['property_id'];

            $appointment->update($data);

            if ((int) $oldPropertyId !== (int) $newPropertyId) {
                Property::where('id', $oldPropertyId)
                    ->where('viewing_count', '>', 0)
                    ->decrement('viewing_count');

                Property::where('id', $newPropertyId)
                    ->increment('viewing_count');
            }

            return $appointment->refresh();
        });
    }

    public function deleteViewingAppointment(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $appointment = ViewingAppointment::findOrFail($id);

            Property::where('id', $appointment->property_id)
                ->where('viewing_count', '>', 0)
                ->decrement('viewing_count');

            return (bool) $appointment->delete();
        });
    }

    private function checkAgentSchedule(
        array $data,
        ?int $ignoreId = null
    ): void {
        $query = ViewingAppointment::where(
                'agent_id',
                $data['agent_id']
            )
            ->where('viewing_date', $data['viewing_date'])
            ->where('viewing_time', $data['viewing_time']);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'viewing_time' =>
                    'This agent already has a viewing scheduled at this date and time.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROPERTY INQUIRIES
    |--------------------------------------------------------------------------
    */

    public function getPaginatedInquiries(
        int $perPage = 10,
        ?string $search = null
    ): LengthAwarePaginator {
        return PropertyInquiry::with(['client', 'property'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'inquiry_message',
                        'like',
                        "%{$search}%"
                    )->orWhereHas(
                        'client',
                        function ($clientQuery) use ($search) {
                            $clientQuery->where(
                                'client_name',
                                'like',
                                "%{$search}%"
                            );
                        }
                    )->orWhereHas(
                        'property',
                        function ($propertyQuery) use ($search) {
                            $propertyQuery->where(
                                'property_code',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createInquiry(array $data): PropertyInquiry
    {
        return DB::transaction(function () use ($data) {
            return PropertyInquiry::create($data);
        });
    }

    public function updateInquiry(
        array $data,
        int $id
    ): PropertyInquiry {
        return DB::transaction(function () use ($data, $id) {
            $inquiry = PropertyInquiry::findOrFail($id);
            $inquiry->update($data);

            return $inquiry->refresh();
        });
    }

    public function deleteInquiry(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return (bool) PropertyInquiry::findOrFail($id)->delete();
        });
    }
}
