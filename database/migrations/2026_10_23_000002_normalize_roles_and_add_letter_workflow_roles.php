<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Keep existing role identities stable while making their names suitable
     * for the new letter approval-chain configuration.
     */
    public function up(): void
    {
        $now = now();

        foreach ($this->canonicalRoles() as $id => $name) {
            DB::table('roles')
                ->where('id', $id)
                ->update(['nama' => $name, 'updated_at' => $now]);
        }

        foreach ($this->workflowRoles() as $id => $name) {
            $roleAtId = DB::table('roles')->where('id', $id)->value('nama');

            if ($roleAtId !== null) {
                if ($roleAtId !== $name) {
                    throw new RuntimeException("Role ID {$id} is already used by {$roleAtId}; cannot safely create {$name}.");
                }

                continue;
            }

            if (DB::table('roles')->where('nama', $name)->exists()) {
                throw new RuntimeException("Role {$name} already exists under a different ID; resolve the duplicate before migrating.");
            }

            DB::table('roles')->insert([
                'id' => $id,
                'nama' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $now = now();

        foreach ($this->legacyRoles() as $id => $name) {
            DB::table('roles')
                ->where('id', $id)
                ->where('nama', $this->canonicalRoles()[$id])
                ->update(['nama' => $name, 'updated_at' => $now]);
        }

        foreach ($this->workflowRoles() as $id => $name) {
            DB::table('roles')->where('id', $id)->where('nama', $name)->delete();
        }
    }

    /** @return array<int, string> */
    private function canonicalRoles(): array
    {
        return [
            1 => 'MAHASISWA',
            2 => 'RESEPSIONIS',
            3 => 'DEKAN',
            4 => 'WD1',
            5 => 'KTU',
            6 => 'AKADEMIK',
            7 => 'BAGIAN LAYANAN',
            8 => 'SUPER ADMIN',
            9 => 'ADMIN TTPS',
        ];
    }

    /** @return array<int, string> */
    private function workflowRoles(): array
    {
        return [
            10 => 'WD2',
            11 => 'WD3',
            12 => 'PRODI',
        ];
    }

    /** @return array<int, string> */
    private function legacyRoles(): array
    {
        return [
            1 => 'Mahasiswa',
            2 => 'Resepsionis',
            3 => 'Dekan',
            4 => 'WD',
            5 => 'TU',
            6 => 'Bagian Akademik',
            7 => 'Bagian Layanan',
            8 => 'Super Admin',
            9 => 'Admin TTPS',
        ];
    }
};
