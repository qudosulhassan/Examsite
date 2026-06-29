<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;
use App\Models\Exam;
use Illuminate\Support\Str;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $examsByVendor = [
            'microsoft' => [
                ['code' => 'AZ-900', 'name' => 'Microsoft Azure Fundamentals', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'AZ-104', 'name' => 'Microsoft Azure Administrator', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'AZ-305', 'name' => 'Designing Microsoft Azure Infrastructure Solutions', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
                ['code' => 'AZ-500', 'name' => 'Microsoft Azure Security Technologies', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
                ['code' => 'SC-900', 'name' => 'Microsoft Security, Compliance, and Identity Fundamentals', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
            ],
            'amazon-web-services-aws' => [
                ['code' => 'CLF-C02', 'name' => 'AWS Certified Cloud Practitioner', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'SAA-C03', 'name' => 'AWS Certified Solutions Architect - Associate', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'SAP-C02', 'name' => 'AWS Certified Solutions Architect - Professional', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
                ['code' => 'DVA-C02', 'name' => 'AWS Certified Developer - Associate', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'DOP-C02', 'name' => 'AWS Certified DevOps Engineer - Professional', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
            ],
            'google-cloud-platform-gcp' => [
                ['code' => 'GCP-ACE', 'name' => 'Associate Cloud Engineer', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'GCP-PCA', 'name' => 'Professional Cloud Architect', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
                ['code' => 'GCP-PDE', 'name' => 'Professional Data Engineer', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
                ['code' => 'GCP-PCDOE', 'name' => 'Professional Cloud DevOps Engineer', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
                ['code' => 'GCP-PCSE', 'name' => 'Professional Cloud Security Engineer', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
            ],
            'cisco' => [
                ['code' => '200-301', 'name' => 'Cisco Certified Network Associate (CCNA)', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => '350-401', 'name' => 'Implementing Cisco Enterprise Network Core Technologies (ENCOR)', 'difficulty' => 'Professional', 'type' => 'MultiSelect'],
                ['code' => '350-701', 'name' => 'Implementing and Operating Cisco Security Core Technologies (SCOR)', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
                ['code' => '200-901', 'name' => 'Developing Applications and Automating Workflows Using Cisco Platforms (DEVASC)', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => '350-901', 'name' => 'Developing Applications Using Cisco Platforms and APIs (DEVCOR)', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
            ],
            'comptia' => [
                ['code' => '220-1101', 'name' => 'CompTIA A+ Certification Exam: Core 1', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => '220-1102', 'name' => 'CompTIA A+ Certification Exam: Core 2', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'N10-008', 'name' => 'CompTIA Network+ Certification Exam', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'SY0-701', 'name' => 'CompTIA Security+ Certification Exam', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => 'XK0-005', 'name' => 'CompTIA Linux+ Certification Exam', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
            ]
        ];

        // Fetch all vendors to link them
        $vendors = Vendor::all();

        foreach ($vendors as $vendor) {
            $slug = $vendor->slug;
            
            // If we have custom exams defined, use them, otherwise generate default ones
            $exams = $examsByVendor[$slug] ?? [
                ['code' => strtoupper($slug) . '-101', 'name' => $vendor->name . ' Entry Exam', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => strtoupper($slug) . '-201', 'name' => $vendor->name . ' Associate Exam', 'difficulty' => 'Associate', 'type' => 'MultipleChoice'],
                ['code' => strtoupper($slug) . '-301', 'name' => $vendor->name . ' Professional Exam', 'difficulty' => 'Professional', 'type' => 'MultipleChoice'],
                ['code' => strtoupper($slug) . '-401', 'name' => $vendor->name . ' Advanced Exam', 'difficulty' => 'Professional', 'type' => 'MultiSelect'],
                ['code' => strtoupper($slug) . '-501', 'name' => $vendor->name . ' Expert Architect', 'difficulty' => 'Expert', 'type' => 'LabBased'],
            ];

            foreach ($exams as $index => $examData) {
                Exam::create([
                    'vendor_id' => $vendor->id,
                    'exam_code' => $examData['code'],
                    'exam_name' => $examData['name'],
                    'slug' => Str::slug($vendor->name . ' ' . $examData['code']),
                    'description' => "This study guide and practice engine contains verified questions and answers compiled by IT professionals to help you pass the {$examData['name']} ({$examData['code']}) certification exam on your first attempt.",
                    'topics' => ['Core Concepts', 'Implementation & Setup', 'Troubleshooting', 'Security Best Practices', 'Advanced Scenarios'],
                    'question_count' => 10, // We will seed 10 questions per exam
                    'passing_score' => 70,
                    'difficulty' => $examData['difficulty'],
                    'exam_type' => $examData['type'],
                    'price_pdf' => 19.99 + ($index * 5),
                    'price_engine' => 29.99 + ($index * 5),
                    'demo_pdf_filename' => Str::slug($examData['code']) . '-demo.pdf',
                    'full_pdf_filename' => Str::slug($examData['code']) . '-full.pdf',
                    'last_updated_at' => now()->subDays(rand(1, 30)),
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ]);
            }
        }
    }
}
