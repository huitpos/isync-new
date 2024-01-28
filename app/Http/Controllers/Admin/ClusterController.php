<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiService;

use App\Models\Company;
use App\Models\Cluster;

use App\Repositories\Interfaces\ClusterRepositoryInterface;
use App\Repositories\Interfaces\CompanyRepositoryInterface;

class ClusterController extends Controller
{
    protected $apiService;
    protected $clusterRepository;
    protected $companyRepository;

    public function __construct(
        ApiService $apiService,
        ClusterRepositoryInterface $clusterRepository,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->apiService = $apiService;
        $this->clusterRepository = $clusterRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clusters = $this->clusterRepository->all();
        return view('admin.clusters.index', [
            'clusters' => $clusters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = $this->companyRepository->all();
        return view('admin.clusters.create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
        ]);

        if ($this->clusterRepository->create($request->all())) {
            return redirect()->route('admin.clusters.index')->with('success', 'Data has been stored successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($clusterId)
    {
        $cluster = $this->clusterRepository->find($clusterId);
        $companies = $this->companyRepository->all();

        if (!$cluster) {
            return abort(404, 'Cluster not found');
        }

        return view('admin.clusters.edit', [
            'cluster' => $cluster,
            'companies' => $companies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $clusterId)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
        ]);

        if ($this->clusterRepository->update($clusterId, $request->all())) {
            return redirect()->route('admin.clusters.index')->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
