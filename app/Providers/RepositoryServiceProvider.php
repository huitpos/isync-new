<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Interfaces\ClusterRepositoryInterface;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\CompanyRepositoryInterface;
use App\Repositories\Interfaces\BranchRepositoryInterface;
use App\Repositories\Interfaces\PosMachineRepositoryInterface;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use App\Repositories\Interfaces\DepartmentRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\SubcategoryRepositoryInterface;
use App\Repositories\Interfaces\UnitOfMeasurementRepositoryInterface;
use App\Repositories\Interfaces\PaymentTypeRepositoryInterface;
use App\Repositories\Interfaces\ChargeAccountRepositoryInterface;
use App\Repositories\Interfaces\BankRepositoryInterface;
use App\Repositories\Interfaces\DiscountTypeRepositoryInterface;
use App\Repositories\Interfaces\ItemTypeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;

use App\Repositories\ClusterRepository;
use App\Repositories\ClientRepository;
use App\Repositories\UserRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\BranchRepository;
use App\Repositories\PosMachineRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\SubcategoryRepository;
use App\Repositories\UnitOfMeasurementRepository;
use App\Repositories\PaymentTypeRepository;
use App\Repositories\ChargeAccountRepository;
use App\Repositories\BankRepository;
use App\Repositories\DiscountTypeRepository;
use App\Repositories\ItemTypeRepository;
use App\Repositories\ProductRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ClusterRepositoryInterface::class, ClusterRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(PosMachineRepositoryInterface::class, PosMachineRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SubcategoryRepositoryInterface::class, SubcategoryRepository::class);
        $this->app->bind(UnitOfMeasurementRepositoryInterface::class, UnitOfMeasurementRepository::class);
        $this->app->bind(PaymentTypeRepositoryInterface::class, PaymentTypeRepository::class);
        $this->app->bind(ChargeAccountRepositoryInterface::class, ChargeAccountRepository::class);
        $this->app->bind(BankRepositoryInterface::class, BankRepository::class);
        $this->app->bind(DiscountTypeRepositoryInterface::class, DiscountTypeRepository::class);
        $this->app->bind(ItemTypeRepositoryInterface::class, ItemTypeRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    }
}
