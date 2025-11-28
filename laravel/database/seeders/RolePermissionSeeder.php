<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Flight management
            'view flights',
            'create flights',
            'edit flights',
            'delete flights',
            
            // Booking management
            'view bookings',
            'create bookings',
            'edit bookings',
            'delete bookings',
            'cancel bookings',
            
            // Ticket management
            'view tickets',
            'create tickets',
            'edit tickets',
            'delete tickets',
            'print tickets',
            
            // Report management
            'view reports',
            'export reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view users',
            'view flights',
            'create flights',
            'edit flights',
            'view bookings',
            'create bookings',
            'edit bookings',
            'cancel bookings',
            'view tickets',
            'create tickets',
            'edit tickets',
            'print tickets',
            'view reports',
            'export reports',
        ]);

        $agentRole = Role::create(['name' => 'agent']);
        $agentRole->givePermissionTo([
            'view flights',
            'view bookings',
            'create bookings',
            'edit bookings',
            'view tickets',
            'create tickets',
            'print tickets',
        ]);

        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'view flights',
            'view bookings',
            'create bookings',
            'view tickets',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
