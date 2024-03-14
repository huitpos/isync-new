<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataTables\Company\PurchaseDeliveriesDataTable;

use App\Models\PurchaseDelivery;

class PurchaseDeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PurchaseDeliveriesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        return $dataTable->with([
            'status' => $request->query('status', null),
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.purchaseDeliveries.index', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $pd = PurchaseDelivery::with(['purchaseOrder'])->findOrFail($id);

        return view('company.purchaseDeliveries.show', [
            'company' => $company,
            'pd' => $pd,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $id)
    {
        $pd = PurchaseDelivery::findOrFail($id);

        $status = $request->input('status');
        $pd->status = $status;
        $pd->action_by = auth()->user()->id;
        $pd->save();

        if ($status == 'rejected') {
            foreach ($pd->items as $item) {
                $poItem = $item->purchaseOrderItem;
                $poItem->balance = $poItem->balance + $item->qty;
                $poItem->save();
            }
        } else {
            foreach ($pd->items as $item) {
                $product = $item->product;
                $product->cost = $item->unit_price;

                $srp = $product->markup_type == 'percentage' ? $item->unit_price + ($item->unit_price * ($product->markup / 100)) : $item->unit_price + $product->markup;

                $product->srp = $srp;
                $product->save();
            }
        }

        return redirect()->back()->with('success', 'Purchase delivery status updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
