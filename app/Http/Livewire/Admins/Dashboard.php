<?php

namespace App\Http\Livewire\Admins;

use App\Models\Appointment;
use App\Models\Beds;
use App\Models\Bill;
use App\Models\BirthReport;
use App\Models\Block;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hod;
use App\Models\OperationReport;
use App\Models\Patient;
use App\Models\RequestedAppointment;
use App\Models\Rooms;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Read pre-computed analytics from cache (generated nightly by GenerateDailyReport job).
        // Falls back to live queries only if the cache is cold (e.g. first boot).
        $report = Cache::remember('daily_report', now()->addHours(24), function () {
            return [
                'date'                 => today()->toDateString(),
                'new_patients_today'   => Patient::whereDate('created_at', today())->count(),
                'appointments_today'   => Appointment::whereDate('created_at', today())->count(),
                'revenue_today'        => Bill::whereDate('created_at', today())->where('payed', true)->sum('amount'),
                'pending_bills'        => Bill::where('payed', false)->count(),
                'pending_bills_amount' => Bill::where('payed', false)->sum('amount'),
                'monthly_revenue'      => Bill::whereMonth('created_at', now()->month)->where('payed', true)->sum('amount'),
                'monthly_patients'     => Patient::whereMonth('created_at', now()->month)->count(),
            ];
        });

        return view('livewire.admins.dashboard', [
            // Infrastructure counts
            'employees'            => Employee::count(),
            'appointments'         => Appointment::count(),
            'birthreports'         => BirthReport::count(),
            'operationreports'     => OperationReport::count(),
            'patients'             => Patient::count(),
            'hods'                 => Hod::count(),
            'blocks'               => Block::count(),
            'departments'          => Department::count(),
            'rooms'                => Rooms::count(),
            'beds'                 => Beds::count(),
            'subscribers'          => Subscriber::count(),
            'requestedAppointment' => RequestedAppointment::count(),

            // Today / monthly analytics (served from cache)
            'newPatientsToday'    => $report['new_patients_today'],
            'appointmentsToday'   => $report['appointments_today'],
            'revenueToday'        => $report['revenue_today'],
            'pendingBills'        => $report['pending_bills'],
            'pendingBillsAmount'  => $report['pending_bills_amount'],
            'monthlyRevenue'      => $report['monthly_revenue'],
            'monthlyPatients'     => $report['monthly_patients'],
        ])->layout('admins.layouts.app');
    }
}
