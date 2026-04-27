<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminOffice = Office::where('name', 'Office of Student Affairs and Services (OSAS)')->first();
        $presidentOffice = Office::where('name', 'Office of the College President')->first();

        User::updateOrCreate(
            ['student_id' => 'ADMIN001'],
            [
                'name' => 'OSAS Director',
                'email' => null,
                'office_id' => $adminOffice?->id,
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['student_id' => 'PRES001'],
            [
                'name' => 'College President',
                'email' => null,
                'office_id' => $presidentOffice?->id,
                'role' => 'president',
                'password' => Hash::make('password'),
            ]
        );

        $staffAccounts = [
            [
                'student_id' => 'GUID001',
                'name' => 'Guidance Staff',
                'office' => 'Guidance Office',
            ],
            [
                'student_id' => 'CLINIC001',
                'name' => 'Medical-Dental Clinic Staff',
                'office' => 'Medical-Dental Clinic',
            ],
            [
                'student_id' => 'LIB001',
                'name' => 'Library Staff',
                'office' => 'Library',
            ],
            [
                'student_id' => 'SEC001',
                'name' => 'Security and Safety Staff',
                'office' => 'Security and Safety Office',
            ],
            [
                'student_id' => 'EQUIP001',
                'name' => 'Equipment and Facilities Staff',
                'office' => 'Equipment and Facilities',
            ],
            [
                'student_id' => 'SSG001',
                'name' => 'SSG Staff',
                'office' => 'Supreme Student Government (SSG)',
            ],
            [
                'student_id' => 'REG001',
                'name' => 'Registrar Staff',
                'office' => 'Registrar',
            ],
            [
                'student_id' => 'SPORTS001',
                'name' => 'PE/Sports Staff',
                'office' => 'PE/Sports',
            ],
            [
                'student_id' => 'CANTEEN001',
                'name' => 'Canteen Staff',
                'office' => 'Canteen',
            ],
            [
                'student_id' => 'PROG001',
                'name' => 'Program Head Staff',
                'office' => 'Program Head',
            ],
            [
                'student_id' => 'OSAS001',
                'name' => 'OSAS Staff',
                'office' => 'Office of Student Affairs and Services (OSAS)',
            ],
            [
                'student_id' => 'VPAA001',
                'name' => 'VPAA Staff',
                'office' => 'Office of the Vice President for Academic Affairs',
            ],
            [
                'student_id' => 'VPA001',
                'name' => 'VPA Staff',
                'office' => 'Office of the Vice President for Administration',
            ],
        ];

        foreach ($staffAccounts as $staffAccount) {
            $office = Office::where('name', $staffAccount['office'])->first();

            User::updateOrCreate(
                ['student_id' => $staffAccount['student_id']],
                [
                    'name' => $staffAccount['name'],
                    'email' => null,
                    'office_id' => $office?->id,
                    'role' => 'staff',
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}