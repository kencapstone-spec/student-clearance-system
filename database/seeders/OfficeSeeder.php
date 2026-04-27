<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            [
                'name' => 'Guidance Office',
                'group' => 'Student Services',
                'sort_order' => 1,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Medical-Dental Clinic',
                'group' => 'Student Services',
                'sort_order' => 2,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Library',
                'group' => 'Student Services',
                'sort_order' => 3,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Security and Safety Office',
                'group' => 'Student Services',
                'sort_order' => 4,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Equipment and Facilities',
                'group' => 'Student Services',
                'sort_order' => 5,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Supreme Student Government (SSG)',
                'group' => 'Student Services',
                'sort_order' => 6,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Registrar',
                'group' => 'Student Services',
                'sort_order' => 7,
                'is_final_approver' => false,
            ],
            [
                'name' => 'PE/Sports',
                'group' => 'Student Services',
                'sort_order' => 8,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Canteen',
                'group' => 'Student Services',
                'sort_order' => 9,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Program Head',
                'group' => 'Offices and Administration',
                'sort_order' => 10,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Office of Student Affairs and Services (OSAS)',
                'group' => 'Offices and Administration',
                'sort_order' => 11,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Office of the Vice President for Academic Affairs',
                'group' => 'Offices and Administration',
                'sort_order' => 12,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Office of the Vice President for Administration',
                'group' => 'Offices and Administration',
                'sort_order' => 13,
                'is_final_approver' => false,
            ],
            [
                'name' => 'Office of the College President',
                'group' => 'Offices and Administration',
                'sort_order' => 14,
                'is_final_approver' => true,
            ],
        ];

        foreach ($offices as $office) {
            Office::updateOrCreate(
                ['name' => $office['name']],
                [
                    'group' => $office['group'],
                    'sort_order' => $office['sort_order'],
                    'is_final_approver' => $office['is_final_approver'],
                ]
            );
        }
    }
}