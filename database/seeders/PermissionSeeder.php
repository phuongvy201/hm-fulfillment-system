<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Wallet Management
            ['name' => 'View Wallet', 'slug' => 'wallet.view', 'group' => 'wallet', 'description' => 'Xem thông tin ví'],
            ['name' => 'Adjust Wallet', 'slug' => 'wallet.adjust', 'group' => 'wallet', 'description' => 'Điều chỉnh số dư ví'],

            // Credit Management
            ['name' => 'View Credit', 'slug' => 'credit.view', 'group' => 'credit', 'description' => 'Xem thông tin công nợ'],
            ['name' => 'Edit Credit', 'slug' => 'credit.edit', 'group' => 'credit', 'description' => 'Chỉnh sửa công nợ'],
            ['name' => 'Pay Credit from Wallet', 'slug' => 'credit.pay', 'group' => 'credit', 'description' => 'Thanh toán công nợ từ ví'],

            // Top-up Request Management
            ['name' => 'View Top-up Requests', 'slug' => 'top-up.view', 'group' => 'top-up', 'description' => 'Xem yêu cầu nạp tiền'],
            ['name' => 'Approve Top-up Request', 'slug' => 'top-up.approve', 'group' => 'top-up', 'description' => 'Duyệt yêu cầu nạp tiền'],
            ['name' => 'Reject Top-up Request', 'slug' => 'top-up.reject', 'group' => 'top-up', 'description' => 'Từ chối yêu cầu nạp tiền'],
            ['name' => 'Create Top-up Request', 'slug' => 'top-up.create', 'group' => 'top-up', 'description' => 'Tạo yêu cầu nạp tiền'],

            // Product Management
            ['name' => 'View Products', 'slug' => 'products.view', 'group' => 'products', 'description' => 'Xem sản phẩm'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'group' => 'products', 'description' => 'Tạo sản phẩm'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'group' => 'products', 'description' => 'Chỉnh sửa sản phẩm'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'group' => 'products', 'description' => 'Xóa sản phẩm'],
            ['name' => 'Manage Variants', 'slug' => 'products.variants', 'group' => 'products', 'description' => 'Quản lý variants'],
            ['name' => 'Set Prices', 'slug' => 'products.prices', 'group' => 'products', 'description' => 'Set giá sản phẩm'],
            ['name' => 'Set Printing Prices', 'slug' => 'products.printing-prices', 'group' => 'products', 'description' => 'Set giá in'],
            ['name' => 'Set User Custom Prices', 'slug' => 'products.user-prices', 'group' => 'products', 'description' => 'Set giá riêng cho user'],
            ['name' => 'Set Workshop Prices', 'slug' => 'products.workshop-prices', 'group' => 'products', 'description' => 'Set giá cho workshop'],

            // Workshop Management
            ['name' => 'View Workshops', 'slug' => 'workshops.view', 'group' => 'workshops', 'description' => 'Xem workshops'],
            ['name' => 'Create Workshops', 'slug' => 'workshops.create', 'group' => 'workshops', 'description' => 'Tạo workshop'],
            ['name' => 'Edit Workshops', 'slug' => 'workshops.edit', 'group' => 'workshops', 'description' => 'Chỉnh sửa workshop'],
            ['name' => 'Delete Workshops', 'slug' => 'workshops.delete', 'group' => 'workshops', 'description' => 'Xóa workshop'],

            // Pricing Tier Management
            ['name' => 'View Pricing Tiers', 'slug' => 'pricing-tiers.view', 'group' => 'pricing-tiers', 'description' => 'Xem pricing tiers'],
            ['name' => 'Create Pricing Tiers', 'slug' => 'pricing-tiers.create', 'group' => 'pricing-tiers', 'description' => 'Tạo pricing tier'],
            ['name' => 'Edit Pricing Tiers', 'slug' => 'pricing-tiers.edit', 'group' => 'pricing-tiers', 'description' => 'Chỉnh sửa pricing tier'],
            ['name' => 'Delete Pricing Tiers', 'slug' => 'pricing-tiers.delete', 'group' => 'pricing-tiers', 'description' => 'Xóa pricing tier'],
            ['name' => 'Manage User Pricing Tiers', 'slug' => 'pricing-tiers.users', 'group' => 'pricing-tiers', 'description' => 'Quản lý tier của user'],

            // Market Management
            ['name' => 'View Markets', 'slug' => 'markets.view', 'group' => 'markets', 'description' => 'Xem markets'],
            ['name' => 'Create Markets', 'slug' => 'markets.create', 'group' => 'markets', 'description' => 'Tạo market'],
            ['name' => 'Edit Markets', 'slug' => 'markets.edit', 'group' => 'markets', 'description' => 'Chỉnh sửa market'],
            ['name' => 'Delete Markets', 'slug' => 'markets.delete', 'group' => 'markets', 'description' => 'Xóa market'],

            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users', 'description' => 'Xem users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'group' => 'users', 'description' => 'Tạo user'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'group' => 'users', 'description' => 'Chỉnh sửa user'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'group' => 'users', 'description' => 'Xóa user'],

            // Order Management
            ['name' => 'View Orders', 'slug' => 'orders.view', 'group' => 'orders', 'description' => 'Xem orders'],
            ['name' => 'Create Orders', 'slug' => 'orders.create', 'group' => 'orders', 'description' => 'Tạo order'],
            ['name' => 'Edit Orders', 'slug' => 'orders.edit', 'group' => 'orders', 'description' => 'Chỉnh sửa order'],
            ['name' => 'Delete Orders', 'slug' => 'orders.delete', 'group' => 'orders', 'description' => 'Xóa order'],
            ['name' => 'Submit Orders', 'slug' => 'orders.submit', 'group' => 'orders', 'description' => 'Gửi order đến workshop'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Assign permissions to super-admin role (all permissions)
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::all();
            $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
        }

        // Assign permissions to it-admin role (view permissions + system management)
        $itAdminRole = Role::where('slug', 'it-admin')->first();
        if ($itAdminRole) {
            $itAdminPermissions = Permission::whereIn('slug', [
                'wallet.view',
                'credit.view',
                'top-up.view',
                'products.view',
                'workshops.view',
                'pricing-tiers.view',
                'markets.view',
                'users.view',
            ])->get();
            $itAdminRole->permissions()->sync($itAdminPermissions->pluck('id'));
        }

        // Assign permissions to fulfillment-staff role (warehouse operations)
        $fulfillmentStaffRole = Role::where('slug', 'fulfillment-staff')->first();
        if ($fulfillmentStaffRole) {
            $fulfillmentPermissions = Permission::whereIn('slug', [
                'products.view',
                'products.variants',
                'workshops.view',
            ])->get();
            $fulfillmentStaffRole->permissions()->sync($fulfillmentPermissions->pluck('id'));
        }

        // Assign permissions to designer role (product design)
        $designerRole = Role::where('slug', 'designer')->first();
        if ($designerRole) {
            $designerPermissions = Permission::whereIn('slug', [
                'products.view',
                'products.edit',
                'products.variants',
            ])->get();
            $designerRole->permissions()->sync($designerPermissions->pluck('id'));
        }

        // Assign permissions to accountant role (financial management)
        $accountantRole = Role::where('slug', 'accountant')->first();
        if ($accountantRole) {
            $accountantPermissions = Permission::whereIn('slug', [
                'wallet.view',
                'credit.view',
                'credit.edit',
                'credit.pay',
                'top-up.view',
                'top-up.approve',
                'top-up.reject',
                'products.view',
                'products.prices',
                'markets.view',
            ])->get();
            $accountantRole->permissions()->sync($accountantPermissions->pluck('id'));
        }

        // Assign permissions to customer role (customer access + order creation)
        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $customerPermissions = Permission::whereIn('slug', [
                'products.view',
                'orders.view',
                'orders.create',
                'top-up.create',
            ])->get();
            $customerRole->permissions()->sync($customerPermissions->pluck('id'));
        }

        // Assign permissions to support role (customer service)
        $supportRole = Role::where('slug', 'support')->first();
        if ($supportRole) {
            $supportPermissions = Permission::whereIn('slug', [
                'products.view',
                'top-up.view',
                'wallet.view',
                'credit.view',
                'users.view',
            ])->get();
            $supportRole->permissions()->sync($supportPermissions->pluck('id'));
        }
    }
}
