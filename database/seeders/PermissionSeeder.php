<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds for permissions across core ERP modules.
     */
    public function run(): void
    {
        $permissions = [
            // 1. Dashboard
            ['module' => 'Dashboard', 'name' => 'View Dashboard', 'slug' => 'dashboard.view', 'action' => 'view', 'description' => 'Access executive overview dashboard'],

            // 2. Customers
            ['module' => 'Customers', 'name' => 'View Customers', 'slug' => 'customers.view', 'action' => 'view', 'description' => 'View customer ledger and list'],
            ['module' => 'Customers', 'name' => 'Create Customer', 'slug' => 'customers.create', 'action' => 'create', 'description' => 'Add new customer account'],
            ['module' => 'Customers', 'name' => 'Edit Customer', 'slug' => 'customers.edit', 'action' => 'edit', 'description' => 'Update customer information'],
            ['module' => 'Customers', 'name' => 'Delete Customer', 'slug' => 'customers.delete', 'action' => 'delete', 'description' => 'Delete customer record'],
            ['module' => 'Customers', 'name' => 'Export Customers', 'slug' => 'customers.export', 'action' => 'export', 'description' => 'Export customer records'],
            ['module' => 'Customers', 'name' => 'Import Customers', 'slug' => 'customers.import', 'action' => 'import', 'description' => 'Import bulk customer data'],

            // 3. Vendors
            ['module' => 'Vendors', 'name' => 'View Vendors', 'slug' => 'vendors.view', 'action' => 'view', 'description' => 'View vendor list & ledger'],
            ['module' => 'Vendors', 'name' => 'Create Vendor', 'slug' => 'vendors.create', 'action' => 'create', 'description' => 'Add new supplier/vendor'],
            ['module' => 'Vendors', 'name' => 'Edit Vendor', 'slug' => 'vendors.edit', 'action' => 'edit', 'description' => 'Update vendor details'],
            ['module' => 'Vendors', 'name' => 'Delete Vendor', 'slug' => 'vendors.delete', 'action' => 'delete', 'description' => 'Remove vendor account'],
            ['module' => 'Vendors', 'name' => 'Export Vendors', 'slug' => 'vendors.export', 'action' => 'export', 'description' => 'Export supplier records'],

            // 4. Products & Inventory
            ['module' => 'Products', 'name' => 'View Products', 'slug' => 'products.view', 'action' => 'view', 'description' => 'View products and stock summary'],
            ['module' => 'Products', 'name' => 'Create Product', 'slug' => 'products.create', 'action' => 'create', 'description' => 'Add new product item'],
            ['module' => 'Products', 'name' => 'Edit Product', 'slug' => 'products.edit', 'action' => 'edit', 'description' => 'Modify product item prices & stock'],
            ['module' => 'Products', 'name' => 'Delete Product', 'slug' => 'products.delete', 'action' => 'delete', 'description' => 'Remove product item'],
            ['module' => 'Products', 'name' => 'Import Products', 'slug' => 'products.import', 'action' => 'import', 'description' => 'Import stock catalog'],
            ['module' => 'Products', 'name' => 'Export Products', 'slug' => 'products.export', 'action' => 'export', 'description' => 'Export inventory summary'],

            // 5. Sales & Invoicing
            ['module' => 'Sales', 'name' => 'View Sales', 'slug' => 'sales.view', 'action' => 'view', 'description' => 'View sales invoices & orders'],
            ['module' => 'Sales', 'name' => 'Create Sale', 'slug' => 'sales.create', 'action' => 'create', 'description' => 'Generate sales invoice'],
            ['module' => 'Sales', 'name' => 'Edit Sale', 'slug' => 'sales.edit', 'action' => 'edit', 'description' => 'Modify sales invoice'],
            ['module' => 'Sales', 'name' => 'Delete Sale', 'slug' => 'sales.delete', 'action' => 'delete', 'description' => 'Delete invoice voucher'],
            ['module' => 'Sales', 'name' => 'Approve Sale', 'slug' => 'sales.approve', 'action' => 'approve', 'description' => 'Approve sales invoice'],
            ['module' => 'Sales', 'name' => 'Cancel Sale', 'slug' => 'sales.cancel', 'action' => 'cancel', 'description' => 'Void or cancel invoice'],
            ['module' => 'Sales', 'name' => 'Print Sale', 'slug' => 'sales.print', 'action' => 'print', 'description' => 'Print invoice PDF'],
            ['module' => 'Sales', 'name' => 'Export Sales', 'slug' => 'sales.export', 'action' => 'export', 'description' => 'Export sales register'],

            // 6. Purchase & Bills
            ['module' => 'Purchase', 'name' => 'View Purchases', 'slug' => 'purchase.view', 'action' => 'view', 'description' => 'View purchase bills and orders'],
            ['module' => 'Purchase', 'name' => 'Create Purchase', 'slug' => 'purchase.create', 'action' => 'create', 'description' => 'Create purchase order or bill'],
            ['module' => 'Purchase', 'name' => 'Edit Purchase', 'slug' => 'purchase.edit', 'action' => 'edit', 'description' => 'Edit purchase voucher'],
            ['module' => 'Purchase', 'name' => 'Delete Purchase', 'slug' => 'purchase.delete', 'action' => 'delete', 'description' => 'Delete purchase entry'],
            ['module' => 'Purchase', 'name' => 'Approve Purchase', 'slug' => 'purchase.approve', 'action' => 'approve', 'description' => 'Authorize purchase bill'],
            ['module' => 'Purchase', 'name' => 'Cancel Purchase', 'slug' => 'purchase.cancel', 'action' => 'cancel', 'description' => 'Cancel purchase order'],
            ['module' => 'Purchase', 'name' => 'Print Purchase', 'slug' => 'purchase.print', 'action' => 'print', 'description' => 'Print purchase document'],
            ['module' => 'Purchase', 'name' => 'Export Purchases', 'slug' => 'purchase.export', 'action' => 'export', 'description' => 'Export purchase report'],

            // 7. Customer Payments & Banking
            ['module' => 'Payments', 'name' => 'View Payments', 'slug' => 'payments.view', 'action' => 'view', 'description' => 'View payment vouchers'],
            ['module' => 'Payments', 'name' => 'Receive Payment', 'slug' => 'payments.create', 'action' => 'create', 'description' => 'Record payment receipt'],
            ['module' => 'Payments', 'name' => 'Edit Payment', 'slug' => 'payments.edit', 'action' => 'edit', 'description' => 'Edit payment entry'],
            ['module' => 'Payments', 'name' => 'Delete Payment', 'slug' => 'payments.delete', 'action' => 'delete', 'description' => 'Remove payment voucher'],
            ['module' => 'Payments', 'name' => 'Approve Payment', 'slug' => 'payments.approve', 'action' => 'approve', 'description' => 'Approve payment transaction'],

            // 8. Expenses
            ['module' => 'Expenses', 'name' => 'View Expenses', 'slug' => 'expenses.view', 'action' => 'view', 'description' => 'View expense claims & ledger'],
            ['module' => 'Expenses', 'name' => 'Create Expense', 'slug' => 'expenses.create', 'action' => 'create', 'description' => 'Record new expense claim'],
            ['module' => 'Expenses', 'name' => 'Edit Expense', 'slug' => 'expenses.edit', 'action' => 'edit', 'description' => 'Edit expense voucher'],
            ['module' => 'Expenses', 'name' => 'Delete Expense', 'slug' => 'expenses.delete', 'action' => 'delete', 'description' => 'Delete expense record'],
            ['module' => 'Expenses', 'name' => 'Approve Expense', 'slug' => 'expenses.approve', 'action' => 'approve', 'description' => 'Approve expense reimbursement'],

            // 9. Financial Reports
            ['module' => 'Reports', 'name' => 'View Reports', 'slug' => 'reports.view', 'action' => 'view', 'description' => 'View P&L, Balance Sheet, GST reports'],
            ['module' => 'Reports', 'name' => 'Export Reports', 'slug' => 'reports.export', 'action' => 'export', 'description' => 'Export financial statements to Excel/CSV'],
            ['module' => 'Reports', 'name' => 'Print Reports', 'slug' => 'reports.print', 'action' => 'print', 'description' => 'Print official financial reports'],

            // 10. User Management
            ['module' => 'Users', 'name' => 'View Users', 'slug' => 'users.view', 'action' => 'view', 'description' => 'View system user accounts'],
            ['module' => 'Users', 'name' => 'Create User', 'slug' => 'users.create', 'action' => 'create', 'description' => 'Add new user account'],
            ['module' => 'Users', 'name' => 'Edit User', 'slug' => 'users.edit', 'action' => 'edit', 'description' => 'Edit user account details'],
            ['module' => 'Users', 'name' => 'Delete User', 'slug' => 'users.delete', 'action' => 'delete', 'description' => 'Remove user account'],
            ['module' => 'Users', 'name' => 'Change User Status', 'slug' => 'users.status', 'action' => 'status', 'description' => 'Activate or suspend user account'],

            // 11. Role & Permission Management
            ['module' => 'Roles', 'name' => 'View Roles', 'slug' => 'roles.view', 'action' => 'view', 'description' => 'View system roles & permission matrix'],
            ['module' => 'Roles', 'name' => 'Create Role', 'slug' => 'roles.create', 'action' => 'create', 'description' => 'Create custom system role'],
            ['module' => 'Roles', 'name' => 'Edit Role', 'slug' => 'roles.edit', 'action' => 'edit', 'description' => 'Modify role details'],
            ['module' => 'Roles', 'name' => 'Delete Role', 'slug' => 'roles.delete', 'action' => 'delete', 'description' => 'Delete custom role'],
            ['module' => 'Roles', 'name' => 'Assign Permissions', 'slug' => 'roles.assign', 'action' => 'assign', 'description' => 'Assign or update permissions on roles'],

            // 12. System Settings
            ['module' => 'Settings', 'name' => 'View Settings', 'slug' => 'settings.view', 'action' => 'view', 'description' => 'View company & system configuration'],
            ['module' => 'Settings', 'name' => 'Edit Settings', 'slug' => 'settings.edit', 'action' => 'edit', 'description' => 'Modify system settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                $perm
            );
        }
    }
}
