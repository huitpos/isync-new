<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\FiscalPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FiscalPeriodController extends Controller
{
    public function index(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');

        return view('company.accounting.periods.index', [
            'company' => $company,
            'periods' => FiscalPeriod::where('company_id', $company->id)->orderByDesc('starts_on')->get(),
        ]);
    }

    public function store(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $data = $request->validate([
            'starts_on' => 'required|date',
            'ends_on' => 'required|date|after_or_equal:starts_on',
        ]);

        $overlaps = FiscalPeriod::where('company_id', $company->id)
            ->whereDate('starts_on', '<=', $data['ends_on'])
            ->whereDate('ends_on', '>=', $data['starts_on'])
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'starts_on' => 'This period overlaps an existing fiscal period.',
            ]);
        }

        FiscalPeriod::create([
            'company_id' => $company->id,
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
            'status' => 'open',
        ]);

        return redirect()
            ->route('company.fiscal-periods.index', ['companySlug' => $company->slug])
            ->with('success', 'Fiscal period added.');
    }

    public function close(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $period = $this->find($company->id, $id);
        $period->status = 'closed';
        $period->save();

        return redirect()
            ->route('company.fiscal-periods.index', ['companySlug' => $company->slug])
            ->with('success', 'Fiscal period closed. Journals in this range can no longer be posted or voided.');
    }

    public function reopen(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $period = $this->find($company->id, $id);
        $period->status = 'open';
        $period->save();

        return redirect()
            ->route('company.fiscal-periods.index', ['companySlug' => $company->slug])
            ->with('success', 'Fiscal period reopened.');
    }

    private function find(int $companyId, string $id): FiscalPeriod
    {
        return FiscalPeriod::where('company_id', $companyId)->findOrFail($id);
    }
}
