<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các role
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $itAdminRole = Role::where('slug', 'it-admin')->first();
        $fulfillmentStaffRole = Role::where('slug', 'fulfillment-staff')->first();
        $designerRole = Role::where('slug', 'designer')->first();
        $accountantRole = Role::where('slug', 'accountant')->first();
        $customerRole = Role::where('slug', 'customer')->first();
        $supportRole = Role::where('slug', 'support')->first();

        // Tạo user cho từng role
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $superAdminRole?->id,
            ],
            [
                'name' => 'IT Admin',
                'email' => 'itadmin@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $itAdminRole?->id,
            ],
            [
                'name' => 'Nhân viên kho 1',
                'email' => 'fulfillment1@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $fulfillmentStaffRole?->id,
            ],
            [
                'name' => 'Nhân viên kho 2',
                'email' => 'fulfillment2@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $fulfillmentStaffRole?->id,
            ],
            [
                'name' => 'Designer 1',
                'email' => 'designer1@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $designerRole?->id,
            ],
            [
                'name' => 'Designer 2',
                'email' => 'designer2@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $designerRole?->id,
            ],
            [
                'name' => 'Kế toán',
                'email' => 'accountant@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $accountantRole?->id,
            ],
            [
                'name' => 'Khách hàng 1',
                'email' => 'customer1@example.com',
                'password' => Hash::make('password123'),
                'role_id' => $customerRole?->id,
            ],
            [
                'name' => 'Khách hàng 2',
                'email' => 'customer2@example.com',
                'password' => Hash::make('password123'),
                'role_id' => $customerRole?->id,
            ],
            [
                'name' => 'Nhân viên CSKH 1',
                'email' => 'support1@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $supportRole?->id,
            ],
            [
                'name' => 'Nhân viên CSKH 2',
                'email' => 'support2@hm-fulfillment.com',
                'password' => Hash::make('password123'),
                'role_id' => $supportRole?->id,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
