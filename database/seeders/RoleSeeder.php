<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Giám đốc, toàn quyền - Full',
            ],
            [
                'name' => 'IT Admin',
                'slug' => 'it-admin',
                'description' => 'Bộ phận IT - Quản lý hệ thống',
            ],
            [
                'name' => 'Fulfillment Staff',
                'slug' => 'fulfillment-staff',
                'description' => 'Nhân viên kho - Tạo/đóng gói đơn, quản lý tồn',
            ],
            [
                'name' => 'Designer',
                'slug' => 'designer',
                'description' => 'Thiết kế - Upload hình ảnh',
            ],
            [
                'name' => 'Accountant',
                'slug' => 'accountant',
                'description' => 'Kế toán - Báo cáo & đối soát',
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Khách hàng - Xem tồn kho, đơn hàng của mình',
            ],
            [
                'name' => 'Support',
                'slug' => 'support',
                'description' => 'CSKH - Chăm sóc đơn hàng',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
