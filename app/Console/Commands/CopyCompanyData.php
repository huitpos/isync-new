<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

use App\Models\PaymentType;
use App\Models\PaymentTypeField;
use App\Models\Department;
use App\Models\Category;
use App\Models\ChargeAccount;
use App\Models\DiscountType;
use App\Models\DiscountTypeFields;
use App\Models\ItemLocation;
use App\Models\ItemType;
use App\Models\PaymentTerm;
use App\Models\Product;
use App\Models\ProductDisposalReason;
use App\Models\Role;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\SupplierTerm;
use App\Models\UnitOfMeasurement;
use App\Models\UnitConversion;
use App\Models\Permission;

class CopyCompanyData extends Command
{

    // php artisan command:copy-company-data 6 11

    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:copy-company-data {param1} {param2?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fromCompanyId = $this->argument('param1');
        $toCompanyId = $this->argument('param2');

        //payment types - done
        //payment_type_fields - done
        //departments - done
        //categories - done
        //subcategories - done
        //item types - done
        //uom - done
        //discount types - done
        //suppliers - done
        //charge accounts -  done
        //payment terms - done
        //supplier terms - done
        //product disposal reasons - done
        //item locations - done
        //products
        
        Role::where('company_id', $toCompanyId)->delete();
        Product::where('company_id', $toCompanyId)->delete();
        ProductDisposalReason::where('company_id', $toCompanyId)->delete();
        PaymentTerm::where('company_id', $toCompanyId)->delete();
        ChargeAccount::where('company_id', $toCompanyId)->delete();
        DiscountType::where('company_id', $toCompanyId)->delete();
        UnitConversion::where('company_id', $toCompanyId)->delete();
        UnitOfMeasurement::where('company_id', $toCompanyId)->delete();
        Subcategory::where('company_id', $toCompanyId)->delete();
        Category::where('company_id', $toCompanyId)->delete();
        Department::where('company_id', $toCompanyId)->delete();
        PaymentType::where('company_id', $toCompanyId)->delete();
        SupplierTerm::where('company_id', $toCompanyId)->delete();
        Supplier::where('company_id', $toCompanyId)->delete();

        $roles = Role::where('company_id', $fromCompanyId)
            ->with('permissions')
            ->get();

        $newRoles = $roles->map(function($role) use ($toCompanyId) {
            $newRole = $role->replicate();
            $newRole->company_id = $toCompanyId;
            $newRole->save();

            // Save the old role ID for later use
            $newRole->old_id = $role->id;

            $permissions = Permission::whereIn('id', $role->permissions->pluck('id'))->pluck('name');
            $newRole->givePermissionTo($permissions);

            return $newRole;
        });
        

        $paymentTypes = PaymentType::where('company_id', $fromCompanyId)->get();
        $newPaymentTypes = $paymentTypes->map(function($paymentType) use ($toCompanyId) {
            $newPaymentType = $paymentType->replicate();
            $newPaymentType->company_id = $toCompanyId;
            $newPaymentType->logo = null;
            $newPaymentType->save();

            $newPaymentType->old_id = $paymentType->id;

            $paymentTypeFields = PaymentTypeField::where('payment_type_id', $paymentType->id)->get();
            $paymentTypeFields->map(function($paymentTypeField) use ($newPaymentType) {
                $newPaymentTypeField = $paymentTypeField->replicate();
            
                $newPaymentTypeField->payment_type_id = $newPaymentType->id;
            
                $newPaymentTypeField->save();
                return $newPaymentTypeField;
            });
            return $newPaymentType;
        });

        $departments = Department::where('company_id', $fromCompanyId)->get();

        $newDepartments = $departments->map(function($department) use ($toCompanyId) {
            $newDepartment = $department->replicate();
            $newDepartment->company_id = $toCompanyId;
            $newDepartment->save();

            $newDepartment->old_id = $department->id;

            return $newDepartment;
        });

        $supplierTerms = SupplierTerm::where('company_id', $fromCompanyId)->get();
        $newSupplierTerms = $supplierTerms->map(function($supplierTerm) use ($toCompanyId) {
            $newSupplierTerm = $supplierTerm->replicate();
            $newSupplierTerm->company_id = $toCompanyId;
            $newSupplierTerm->save();

            $newSupplierTerm->old_id = $supplierTerm->id;

            return $newSupplierTerm;
        });

        $suppliers = Supplier::where('company_id', $fromCompanyId)->get();
        $newSuppliers = $suppliers->map(function($supplier) use ($toCompanyId, $newSupplierTerms) {
            $newSupplier = $supplier->replicate();
            $newSupplier->company_id = $toCompanyId;
            $newSupplier->save();

            $newSupplier->old_id = $supplier->id;

            $newSupplierTerm = $newSupplierTerms->firstWhere('old_id', $supplier->supplier_term_id);
            $newSupplier->supplier_term_id = $newSupplierTerm->id;

            return $newSupplier;
        });

        $categories = Category::where('company_id', $fromCompanyId)->get();
        $newCategories = $categories->map(function($category) use ($toCompanyId, $newDepartments, $newSuppliers) {
            $newCategory = $category->replicate();
            $newCategory->company_id = $toCompanyId;

            $newDepartment = $newDepartments->firstWhere('old_id', $category->department_id);
            $newCategory->department_id = $newDepartment->id;

            $newCategory->image = null;

            $newCategory->save();

            $newCategory->old_id = $category->id;

            $categorySuppliers = DB::table('category_supplier')->where('category_id', $category->id)->get();
            $categorySuppliers->map(function($categorySupplier) use ($newCategory, $newSuppliers) {
                $newCategorySupplier = [
                    'category_id' => $newCategory->id,
                    'supplier_id' => $newSuppliers->firstWhere('old_id', $categorySupplier->supplier_id)->id,
                ];
                DB::table('category_supplier')->insert($newCategorySupplier);
                return $newCategorySupplier;
            });

            return $newCategory;
        });

        $subcategories = Subcategory::where('company_id', $fromCompanyId)->get();
        $newSubcategories = $subcategories->map(function($subcategory) use ($toCompanyId, $newCategories) {
            $newSubcategory = $subcategory->replicate();
            $newSubcategory->company_id = $toCompanyId;

            $newCategory = $newCategories->firstWhere('old_id', $subcategory->category_id);
            $newSubcategory->category_id = $newCategory->id;

            $newSubcategory->save();

            $newSubcategory->old_id = $subcategory->id;

            return $newSubcategory;
        });

        $itemTypes = ItemType::where('company_id', $fromCompanyId)->get();
        $newItemTypes = $itemTypes->map(function($itemType) use ($toCompanyId) {
            $newItemType = $itemType->replicate();
            $newItemType->company_id = $toCompanyId;
            $newItemType->save();

            $newItemType->old_id = $itemType->id;

            return $newItemType;
        });

        $uoms = UnitOfMeasurement::where('company_id', $fromCompanyId)->get();
        $newUoms = $uoms->map(function($uom) use ($toCompanyId) {
            $newUom = $uom->replicate();
            $newUom->company_id = $toCompanyId;
            $newUom->save();

            $newUom->old_id = $uom->id;

            return $newUom;
        });

        $unitConversions = UnitConversion::where('company_id', $fromCompanyId)->get();
        $newUnitConversions = $unitConversions->map(function($unitConversion) use ($toCompanyId, $newUoms) {
            $newUnitConversion = $unitConversion->replicate();
            $newUnitConversion->company_id = $toCompanyId;

            $newUom = $newUoms->firstWhere('old_id', $unitConversion->from_unit_id);
            $newUnitConversion->from_unit_id = $newUom->id;

            $newUom = $newUoms->firstWhere('old_id', $unitConversion->to_unit_id);
            $newUnitConversion->to_unit_id = $newUom->id;

            $newUnitConversion->save();

            $newUnitConversion->old_id = $unitConversion->id;

            return $newUnitConversion;
        });

        $discountTypes = DiscountType::where('company_id', $fromCompanyId)->get();
        $newDiscountTypes = $discountTypes->map(function($discountType) use ($toCompanyId) {
            $newDiscountType = $discountType->replicate();
            $newDiscountType->company_id = $toCompanyId;
            $newDiscountType->save();

            $newDiscountType->old_id = $discountType->id;

            $discountTypeFields = DiscountTypeFields::where('discount_type_id', $discountType->id)->get();
            $discountTypeFields->map(function($discountTypeField) use ($newDiscountType) {
                $newDiscountTypeField = $discountTypeField->replicate();
            
                $newDiscountTypeField->discount_type_id = $newDiscountType->id;
            
                $newDiscountTypeField->save();
                return $newDiscountTypeField;
            });

            return $newDiscountType;
        });

        $chargeAccounts = ChargeAccount::where('company_id', $fromCompanyId)->get();
        $newChargeAccounts = $chargeAccounts->map(function($chargeAccount) use ($toCompanyId) {
            $newChargeAccount = $chargeAccount->replicate();
            $newChargeAccount->company_id = $toCompanyId;
            $newChargeAccount->save();

            $newChargeAccount->old_id = $chargeAccount->id;

            return $newChargeAccount;
        });

        $paymentTerms = PaymentTerm::where('company_id', $fromCompanyId)->get();
        $newPaymentTerms = $paymentTerms->map(function($paymentTerm) use ($toCompanyId) {
            $newPaymentTerm = $paymentTerm->replicate();
            $newPaymentTerm->company_id = $toCompanyId;
            $newPaymentTerm->save();

            $newPaymentTerm->old_id = $paymentTerm->id;

            return $newPaymentTerm;
        });

        $productDisposalReasons = ProductDisposalReason::where('company_id', $fromCompanyId)->get();
        $newProductDisposalReasons = $productDisposalReasons->map(function($productDisposalReason) use ($toCompanyId) {
            $newProductDisposalReason = $productDisposalReason->replicate();
            $newProductDisposalReason->company_id = $toCompanyId;
            $newProductDisposalReason->save();

            $newProductDisposalReason->old_id = $productDisposalReason->id;

            return $newProductDisposalReason;
        });

        $itemLocations = ItemLocation::where('company_id', $fromCompanyId)->get();
        $newItemLocations = $itemLocations->map(function($itemLocation) use ($toCompanyId) {
            $newItemLocation = $itemLocation->replicate();
            $newItemLocation->company_id = $toCompanyId;
            $newItemLocation->save();

            $newItemLocation->old_id = $itemLocation->id;

            return $newItemLocation;
        });

        $products = Product::where('company_id', $fromCompanyId)->get();
        $newProducts = $products->map(function($product) use ($toCompanyId, $newDepartments, $newCategories, $newSubcategories, $newUoms) {
            $newProduct = $product->replicate();
            $newProduct->company_id = $toCompanyId;

            //department_id
            $newDepartment = $newDepartments->firstWhere('old_id', $product->department_id);
            $newProduct->department_id = $newDepartment->id;

            //category_id
            $newCategory = $newCategories->firstWhere('old_id', $product->category_id);
            $newProduct->category_id = $newCategory->id;

            //subcategory_id
            if ($product->subcategory_id) {
                $newSubcategory = $newSubcategories->firstWhere('old_id', $product->subcategory_id);
                $newProduct->subcategory_id = $newSubcategory->id;
            }

            //uom_id
            if ($product->uom_id) {
                $newUom = $newUoms->firstWhere('old_id', $product->uom_id);
                $newProduct->uom_id = $newUom->id;
            }

            //image null
            $newProduct->image = null;

            //delivery_uom_id
            if ($product->delivery_uom_id) {
                $newUom = $newUoms->firstWhere('old_id', $product->delivery_uom_id);
                $newProduct->delivery_uom_id = $newUom->id;
            }

            $newProduct->save();

            $newProduct->old_id = $product->id;

            return $newProduct;
        });

        //get all ids of $newProducts
        $newProductIds = $newProducts->pluck('id')->toArray();
        $rawProducts = DB::table('product_raw')->whereIn('product_id', $newProductIds)->get();
        $rawProducts->map(function($rawProduct) use ($newProducts) {
            $newCategorySupplier = [
                'product_id' => $newProducts->firstWhere('old_id', $rawProduct->product_id)->id,
                'raw_product_id' => $newProducts->firstWhere('old_id', $rawProduct->raw_product_id)->id,
            ];
            DB::table('product_raw')->insert($newCategorySupplier);
            return $newCategorySupplier;
        });

        $bundleProducts = DB::table('bundle_product')->whereIn('product_id', $newProductIds)->get();
        $bundleProducts->map(function($bundleProduct) use ($newProducts) {
            $newCategorySupplier = [
                'included_product_id' => $newProducts->firstWhere('old_id', $bundleProduct->included_product_id)->id,
            ];
            DB::table('bundle_product')->insert($newCategorySupplier);
            return $newCategorySupplier;
        });

        $itemLocationProducts = DB::table('item_location_product')->whereIn('product_id', $newProductIds)->get();
        $itemLocationProducts->map(function($bundleProduct) use ($newItemLocations, $newProducts) {
            $newCategorySupplier = [
                'item_location_id' => $newItemLocations->firstWhere('old_id', $bundleProduct->item_location_id)->id,
                'product_id' => $newProducts->firstWhere('old_id', $bundleProduct->product_id)->id,
            ];
            DB::table('item_location_product')->insert($newCategorySupplier);
            return $newCategorySupplier;
        });
    }
}