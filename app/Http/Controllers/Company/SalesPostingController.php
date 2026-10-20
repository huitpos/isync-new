<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Branch;
use App\Services\Gl\AccountCatalog;
use App\Services\Gl\CogsJournal;
use App\Services\Gl\PosSalesJournal;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalesPostingController extends Controller
{
    public function __construct(
        private AccountCatalog $catalog,
        private PosSalesJournal $sales,
        private CogsJournal $cogs
    ) {
    }

    public function index(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $preview = null;
        $cogsAmount = null;
        $cogsExisting = null;

        if ($request->boolean('preview')) {
            $request->validate([
                'branch_id' => 'required|integer',
                'date' => 'required|date',
            ]);
            $branch = $this->branch($company->id, (int) $request->branch_id);
            try {
                $lines = $this->sales->lines($branch, $request->date);
                $accounts = Account::whereIn('id', collect($lines)->pluck('account_id'))->get()->keyBy('id');
                $preview = [
                    'lines' => $lines,
                    'accounts' => $accounts,
                    'existing' => $this->sales->existing($company->id, $branch->id, $request->date),
                ];
                $cogsAmount = $this->cogs->amount($branch->id, $request->date);
                $cogsExisting = $this->cogs->existing($company->id, $branch->id, $request->date);
            } catch (ValidationException $exception) {
                return redirect()
                    ->route('company.sales-posting.index', ['companySlug' => $company->slug])
                    ->withErrors($exception->errors())
                    ->withInput();
            }
        }

        return view('company.accounting.sales.index', [
            'company' => $company,
            'branches' => $company->branches()->orderBy('name')->get(),
            'enabled' => $this->catalog->enabled($company->id),
            'preview' => $preview,
            'cogsAmount' => $cogsAmount ?? null,
            'cogsExisting' => $cogsExisting ?? null,
        ]);
    }

    public function postSales(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $data = $request->validate([
            'branch_id' => 'required|integer',
            'date' => 'required|date',
        ]);
        $branch = $this->branch($company->id, (int) $data['branch_id']);
        $entry = $this->sales->post($branch, $data['date']);

        return redirect()
            ->route('company.journals.show', ['companySlug' => $company->slug, 'id' => $entry->id])
            ->with('success', 'Sales day posted.');
    }

    public function postCogs(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $data = $request->validate([
            'branch_id' => 'required|integer',
            'date' => 'required|date',
        ]);
        $branch = $this->branch($company->id, (int) $data['branch_id']);
        $entry = $this->cogs->post($branch, $data['date']);

        return redirect()
            ->route('company.journals.show', ['companySlug' => $company->slug, 'id' => $entry->id])
            ->with('success', 'COGS posted.');
    }

    private function branch(int $companyId, int $branchId): Branch
    {
        return Branch::where('company_id', $companyId)->findOrFail($branchId);
    }
}
