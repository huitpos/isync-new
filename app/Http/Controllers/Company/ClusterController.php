<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiService;

use App\Repositories\Interfaces\ClusterRepositoryInterface;

class ClusterController extends Controller
{
    protected $apiService;
    protected $clusterRepository;

    public function __construct(ApiService $apiService, ClusterRepositoryInterface $clusterRepository)
    {
        $this->apiService = $apiService;
        $this->clusterRepository = $clusterRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        $clusters = $this->clusterRepository->get(['company_id' => $company->id]);

        return view('company.clusters.index', [
            'clusters' => $clusters,
            'company' => $company,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.clusters.create', [
            'clusters' => $response['data'] ?? null,
            'company' => $company,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
        ]);

        if ($this->clusterRepository->create($request->all())) {
            return redirect()->route('company.clusters.index', ['companySlug' => $company->slug])->with('success', 'Data has been stored successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // dd("here");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, int $id)
    {
        $company = $request->attributes->get('company');

        $cluster = $this->clusterRepository->find($id);

        if (!$cluster) {
            return abort(404, 'Company not found');
        }

        return view('company.clusters.edit', [
            'cluster' => $cluster,
            'company' => $company,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, int $id)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
        ]);

        if ($this->clusterRepository->update($id, $request->all())) {
            return redirect()->route('company.clusters.index', ['companySlug' => $company->slug])->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $companySlug, int $id)
    {
        if ($this->clusterRepository->delete($id)) {
            return redirect()->route('company.clusters.index', ['companySlug' => $companySlug])->with('success', 'Data has been deleted successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }
}
