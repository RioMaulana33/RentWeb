<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role_has_permissions')->delete();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $menuMaster = ['master', 'master-user', 'master-role', 'master-delivery','master-rentaloption', 'master-kota' ];
        $menuWebsite = ['website', 'setting','dashboard-penyewaan','dashboard-pembayaran','dashboard-laporan' ];
        $menuAdminKota =['website','dashboard-penyewaan','master-user'];
        $menuMobil =['kelola-mobil','kelola-mobil-detail','kelola-mobil-stok'];

        $permissionsByRole = [
            'admin' => ['dashboard', ...$menuMaster, ...$menuWebsite, ...$menuMobil],
            'admin-kota' => ['dashboard', ...$menuAdminKota, ...$menuMobil],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(function ($name) {
                $check = Permission::whereName($name)->first();

                if (!$check) {
                    return Permission::create([
                        'name' => $name,
                        'guard_name' => 'api',
                    ])->id;
                }

                return $check->id;
            })
            ->toArray();

        $permissionIdsByRole = [
            'admin' => $insertPermissions('admin'),
            'admin-kota' => $insertPermissions('admin-kota')
        ];

        foreach ($permissionIdsByRole as $role => $permissionIds) {
            $role = Role::whereName($role)->first();

            DB::table('role_has_permissions')
                ->insert(
                    collect($permissionIds)->map(fn ($id) => [
                        'role_id' => $role->id,
                        'permission_id' => $id
                    ])->toArray()
                );
        }
    }
}
