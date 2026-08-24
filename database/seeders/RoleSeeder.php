<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds for default system roles.
     */
    public function run(): void
    {
        $allPermissions = Permission::all();

        $rolesData = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full unrestricted access to all modules, financial data, and system configurations.',
                'is_system' => true,
                'status' => 'active',
                'permissions' => $allPermissions->pluck('id')->toArray(),
            ],
            [
                'name' => 'Accountant',
                'slug' => 'accountant',
                'description' => 'Full access to accounting duties, financial vouchers, sales, purchases, payments, expenses, and financial reports.',
                'is_system' => true,
                'status' => 'active',
                'permissions' => Permission::whereIn('module', ['Dashboard', 'Customers', 'Vendors', 'Products', 'Sales', 'Purchase', 'Payments', 'Expenses', 'Reports'])->pluck('id')->toArray(),
            ],
            [
                'name' => 'Sales',
                'slug' => 'sales',
                'description' => 'Focuses on customer management, sales invoicing, sales vouchers, and customer payment collections.',
                'is_system' => true,
                'status' => 'active',
                'permissions' => Permission::whereIn('module', ['Dashboard', 'Customers', 'Products', 'Sales', 'Payments'])->pluck('id')->toArray(),
            ],
            [
                'name' => 'Purchase',
                'slug' => 'purchase',
                'description' => 'Manages vendor relationships, purchase bills, purchase vouchers, and stock inventory.',
                'is_system' => true,
                'status' => 'active',
                'permissions' => Permission::whereIn('module', ['Dashboard', 'Vendors', 'Products', 'Purchase', 'Payments'])->pluck('id')->toArray(),
            ],
        ];

        $allowedSlugs = array_column($rolesData, 'slug');

        foreach ($rolesData as $roleInfo) {
            $permIds = $roleInfo['permissions'];
            unset($roleInfo['permissions']);

            $role = Role::updateOrCreate(
                ['slug' => $roleInfo['slug']],
                $roleInfo
            );

            $role->permissions()->sync($permIds);

            if ($role->slug === 'admin') {
                $adminUser = User::where('email', 'admin@gmail.com')->first() ?? User::first();
                if ($adminUser) {
                    $adminUser->roles()->syncWithoutDetaching([$role->id]);
                }
            }
        }

        // Clean up any roles other than the 4 main roles (Admin, Accountant, Sales, Purchase)
        $adminRole = Role::where('slug', 'admin')->first();
        $accountantRole = Role::where('slug', 'accountant')->first();
        $salesRole = Role::where('slug', 'sales')->first();
        $purchaseRole = Role::where('slug', 'purchase')->first();

        $obsoleteRoles = Role::whereNotIn('slug', $allowedSlugs)->get();
        foreach ($obsoleteRoles as $oldRole) {
            foreach ($oldRole->users as $user) {
                if (in_array($oldRole->slug, ['super-admin', 'manager'])) {
                    if ($adminRole) $user->roles()->syncWithoutDetaching([$adminRole->id]);
                } elseif (in_array($oldRole->slug, ['sales-executive'])) {
                    if ($salesRole) $user->roles()->syncWithoutDetaching([$salesRole->id]);
                } elseif (in_array($oldRole->slug, ['purchase-executive'])) {
                    if ($purchaseRole) $user->roles()->syncWithoutDetaching([$purchaseRole->id]);
                } else {
                    if ($accountantRole) $user->roles()->syncWithoutDetaching([$accountantRole->id]);
                }
            }

            $oldRole->permissions()->detach();
            $oldRole->users()->detach();
            $oldRole->delete();
        }
    }
}
