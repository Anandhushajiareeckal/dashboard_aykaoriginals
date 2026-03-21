<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, TalentModel, Brand, Crew, Employee, Project, Invoice, Expense, Meeting};
use Spatie\Permission\Models\{Role, Permission};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles & Permissions ──────────────────────────────
        $admin   = Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $staff   = Role::firstOrCreate(['name' => 'staff',   'guard_name' => 'web']);

        $perms = [
            'manage-models', 'manage-brands', 'manage-projects',
            'manage-crew', 'manage-employees', 'manage-invoices',
            'manage-meetings', 'view-reports',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin->givePermissionTo($perms);
        $manager->givePermissionTo(['manage-models', 'manage-brands', 'manage-projects', 'manage-crew', 'manage-meetings', 'view-reports']);
        $staff->givePermissionTo(['manage-models', 'manage-projects', 'manage-meetings']);

        // ── Admin User ───────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'admin@aykaoriginals.com'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );
        $user->assignRole('admin');

        // ── Brands ───────────────────────────────────────────
        $brandsData = [
            ['name' => 'Dior Arabia',      'location' => 'Dubai',      'contact_name' => 'Claire Martin', 'contact_designation' => 'Comms Director',    'email' => 'claire@dior.com'],
            ['name' => 'Chalhoub Group',   'location' => 'Dubai',      'contact_name' => 'Khalid Hassan', 'contact_designation' => 'Marketing VP',       'email' => 'k.hassan@chalhoub.com'],
            ['name' => 'Level Shoes',      'location' => 'Dubai Mall', 'contact_name' => 'Sana Mirza',    'contact_designation' => 'Brand Manager',      'email' => 'sana@levelshoes.com'],
            ['name' => 'Ounass',           'location' => 'Dubai',      'contact_name' => 'James Park',    'contact_designation' => 'Creative Director',  'email' => 'james@ounass.com'],
            ['name' => 'Conde Nast Arabia','location' => 'Dubai',      'contact_name' => 'Sofia Larsson', 'contact_designation' => 'Editor',             'email' => 'sofia@cnast.com'],
        ];
        foreach ($brandsData as $b) {
            Brand::firstOrCreate(['name' => $b['name']], $b);
        }

        // ── Talent Models ─────────────────────────────────────
        $modelsData = [
            ['name' => 'Layla Hassan',    'age' => 24, 'height' => '174cm', 'location' => 'Dubai',     'categories' => ['Fashion', 'Editorial'], 'status' => 'Active',   'is_inhouse' => true,  'email' => 'layla@email.com',  'phone' => '+971501234567', 'about' => 'Dubai-based fashion model with 5 years experience.'],
            ['name' => 'Sara Al-Turki',   'age' => 22, 'height' => '170cm', 'location' => 'Riyadh',    'categories' => ['Commercial'],           'status' => 'Active',   'is_inhouse' => false, 'email' => 'sara@email.com'],
            ['name' => 'Nour Khalil',     'age' => 26, 'height' => '178cm', 'location' => 'Beirut',    'categories' => ['Runway', 'Fashion'],    'status' => 'Active',   'is_inhouse' => true,  'email' => 'nour@email.com'],
            ['name' => 'Fatima Al-Sayed', 'age' => 23, 'height' => '172cm', 'location' => 'Abu Dhabi', 'categories' => ['Editorial'],            'status' => 'On Leave', 'is_inhouse' => true,  'email' => 'fatima@email.com'],
            ['name' => 'Mia Chen',        'age' => 25, 'height' => '176cm', 'location' => 'Dubai',     'categories' => ['Commercial', 'Fashion'],'status' => 'Active',   'is_inhouse' => false, 'email' => 'mia@email.com'],
        ];
        foreach ($modelsData as $m) {
            TalentModel::firstOrCreate(['name' => $m['name']], $m);
        }

        // ── Crew ─────────────────────────────────────────────
        $crewData = [
            ['name' => 'Ahmed Tariq', 'role' => 'Photographer',  'location' => 'Dubai', 'status' => 'Available', 'email' => 'ahmed@crew.com'],
            ['name' => 'Priya Nair',  'role' => 'Makeup Artist', 'location' => 'Dubai', 'status' => 'On Project','email' => 'priya@crew.com'],
            ['name' => 'David Osei',  'role' => 'Videographer',  'location' => 'Dubai', 'status' => 'Available', 'email' => 'david@crew.com'],
            ['name' => 'Luna Vasquez','role' => 'Stylist',        'location' => 'Dubai', 'status' => 'On Project','email' => 'luna@crew.com'],
        ];
        foreach ($crewData as $c) {
            Crew::firstOrCreate(['name' => $c['name']], $c);
        }

        // ── Employees ────────────────────────────────────────
        $empData = [
            ['name' => 'Aisha Khan',  'department' => 'Management', 'email' => 'aisha@aykaoriginals.com', 'salary' => 28000, 'joining_date' => '2022-01-10', 'status' => 'Active'],
            ['name' => 'Omar Farooq', 'department' => 'Bookings',   'email' => 'omar@aykaoriginals.com',  'salary' => 18000, 'joining_date' => '2022-03-15', 'status' => 'Active'],
            ['name' => 'Hana Lee',    'department' => 'Finance',    'email' => 'hana@aykaoriginals.com',  'salary' => 14000, 'joining_date' => '2023-06-01', 'status' => 'Active'],
            ['name' => 'Rania Bou',   'department' => 'Creative',   'email' => 'rania@aykaoriginals.com', 'salary' => 16000, 'joining_date' => '2022-10-20', 'status' => 'Active'],
        ];
        foreach ($empData as $e) {
            Employee::firstOrCreate(['name' => $e['name']], $e);
        }

        // ── Projects ─────────────────────────────────────────
        $brand1 = Brand::where('name', 'Dior Arabia')->first();
        $brand2 = Brand::where('name', 'Chalhoub Group')->first();
        $brand3 = Brand::where('name', 'Level Shoes')->first();

        $projectsData = [
            ['title' => 'Summer Campaign 2025', 'brand_id' => $brand1->id, 'category' => 'Fashion',   'budget' => 45000, 'start_date' => '2025-03-01', 'end_date' => '2025-04-15', 'status' => 'Active',    'progress' => 60],
            ['title' => 'Ramadan Lookbook',      'brand_id' => $brand2->id, 'category' => 'Editorial', 'budget' => 28000, 'start_date' => '2025-03-15', 'end_date' => '2025-04-05', 'status' => 'Planning',  'progress' => 15],
            ['title' => 'Brand Identity Shoot',  'brand_id' => $brand3->id, 'category' => 'Commercial','budget' => 15000, 'start_date' => '2025-02-20', 'end_date' => '2025-03-28', 'status' => 'Review',    'progress' => 85],
        ];
        foreach ($projectsData as $p) {
            Project::firstOrCreate(['title' => $p['title']], $p);
        }

        // ── Invoices ─────────────────────────────────────────
        // Valid enum values: Draft | Sent | Paid | Overdue | Cancelled
        $project1 = Project::where('title', 'Summer Campaign 2025')->first();

        $invoicesData = [
            [
                'invoice_number' => 'INV-2031',
                'brand_id'       => $brand2->id,
                'project_id'     => $project1->id,
                'amount'         => 28000,
                'tax'            => 5,
                'total'          => 29400,
                'status'         => 'Sent',      // was 'Pending' — not a valid enum value
                'due_date'       => '2025-04-05',
            ],
            [
                'invoice_number' => 'INV-2030',
                'brand_id'       => $brand1->id,
                'project_id'     => $project1->id,
                'amount'         => 22500,
                'tax'            => 5,
                'total'          => 23625,
                'status'         => 'Paid',
                'due_date'       => '2025-03-15',
                'paid_date'      => '2025-03-14',
            ],
            [
                'invoice_number' => 'INV-2029',
                'brand_id'       => $brand3->id,
                'project_id'     => null,
                'amount'         => 15000,
                'tax'            => 5,
                'total'          => 15750,
                'status'         => 'Overdue',
                'due_date'       => '2025-03-10',
            ],
        ];
        foreach ($invoicesData as $i) {
            Invoice::firstOrCreate(['invoice_number' => $i['invoice_number']], $i);
        }

        // ── Expenses ─────────────────────────────────────────
        Expense::firstOrCreate(
            ['description' => 'Studio Rental - Mar'],
            ['category' => 'Operations', 'amount' => 8500, 'expense_date' => '2025-03-01']
        );
        Expense::firstOrCreate(
            ['description' => 'Crew Payments'],
            ['category' => 'Payroll', 'amount' => 22000, 'expense_date' => '2025-03-15']
        );
        Expense::firstOrCreate(
            ['description' => 'Equipment Hire'],
            ['category' => 'Equipment', 'amount' => 4200, 'expense_date' => '2025-03-10']
        );

        // ── Meetings ─────────────────────────────────────────
        $emp = Employee::first();
        Meeting::firstOrCreate(
            ['title' => 'Brand Deck Review'],
            ['brand_id' => $brand2->id, 'employee_id' => $emp->id, 'meeting_at' => '2025-03-21 14:00:00', 'duration_minutes' => 60,  'mode' => 'Online']
        );
        Meeting::firstOrCreate(
            ['title' => 'Pre-shoot Walkthrough'],
            ['brand_id' => $brand1->id, 'employee_id' => $emp->id, 'meeting_at' => '2025-03-22 10:00:00', 'duration_minutes' => 90,  'mode' => 'In-person']
        );
        Meeting::firstOrCreate(
            ['title' => 'Contract Renewal'],
            ['brand_id' => $brand3->id, 'employee_id' => $emp->id, 'meeting_at' => '2025-03-25 16:00:00', 'duration_minutes' => 60,  'mode' => 'Online']
        );

        $this->command->info('');
        $this->command->info('  ✅  Ayka Originals seeded successfully!');
        $this->command->info('  Login: admin@aykaoriginals.com / password');
        $this->command->info('');
    }
}