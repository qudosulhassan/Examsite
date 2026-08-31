<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Certification;
use App\Models\Vendor;
use App\Models\Exam;
use Illuminate\Support\Str;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        $cisco = Vendor::where('slug', 'cisco')->first();
        if ($cisco) {
            $ccna = Certification::create([
                'vendor_id' => $cisco->id,
                'name' => 'Cisco Certified Network Associate (CCNA)',
                'slug' => 'cisco-certified-network-associate-ccna',
                'description' => 'The CCNA certification validates your skills and knowledge in network fundamentals, network access, IP connectivity, IP services, security fundamentals, and automation and programmability.',
                'is_active' => true,
                'sort_order' => 10,
            ]);

            $ccnp = Certification::create([
                'vendor_id' => $cisco->id,
                'name' => 'Cisco Certified Network Professional (CCNP)',
                'slug' => 'cisco-certified-network-professional-ccnp',
                'description' => 'The CCNP certification validates your ability to plan, implement, verify and troubleshoot local and wide-area enterprise networks.',
                'is_active' => true,
                'sort_order' => 20,
            ]);

            $exam301 = Exam::where('exam_code', '200-301')->first();
            if ($exam301) {
                $exam301->certifications()->attach($ccna->id);
            }
        }

        $microsoft = Vendor::where('slug', 'microsoft')->first();
        if ($microsoft) {
            $azure = Certification::create([
                'vendor_id' => $microsoft->id,
                'name' => 'Microsoft Certified: Azure Fundamentals',
                'slug' => 'microsoft-certified-azure-fundamentals',
                'description' => 'Prove that you understand cloud concepts, core Azure services, Azure pricing and support, and the fundamentals of cloud security, privacy, compliance, and trust.',
                'is_active' => true,
                'sort_order' => 10,
            ]);

            $examAz900 = Exam::where('exam_code', 'AZ-900')->first();
            if ($examAz900) {
                $examAz900->certifications()->attach($azure->id);
            }
        }
    }
}
