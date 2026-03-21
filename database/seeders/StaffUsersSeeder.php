<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;

class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff',   'guard_name' => 'web']);

        $users = [
            ['name'=>'Sanchu',  'email'=>'sanchu@aykaoriginals.com',  'pass'=>'sanchu123',  'role'=>'manager', 'dept'=>'Creative'],
            ['name'=>'Ashima',  'email'=>'ashima@aykaoriginals.com',  'pass'=>'ashima123',  'role'=>'staff',   'dept'=>'Bookings'],
            ['name'=>'Ananthu', 'email'=>'ananthu@aykaoriginals.com', 'pass'=>'ananthu123', 'role'=>'staff',   'dept'=>'Operations'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name'  => $u['name'], 'password' => Hash::make($u['pass'])]
            );
            $user->update(['password' => Hash::make($u['pass'])]);
            $user->syncRoles([$u['role']]);
            Employee::firstOrCreate(
                ['email' => $u['email']],
                ['user_id'=>$user->id,'name'=>$u['name'],'department'=>$u['dept'],'joining_date'=>now()->subMonths(rand(2,8)),'status'=>'Active']
            );
            $this->command->info("  Created: {$u['name']} / {$u['pass']}");
        }
    }
}