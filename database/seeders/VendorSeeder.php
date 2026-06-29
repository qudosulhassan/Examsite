<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            ['name' => 'Microsoft', 'category' => 'Cloud', 'description' => 'Microsoft Azure, Microsoft 365, and role-based certifications.'],
            ['name' => 'Amazon Web Services (AWS)', 'category' => 'Cloud', 'description' => 'Industry-leading cloud platform certifications.'],
            ['name' => 'Google Cloud Platform (GCP)', 'category' => 'Cloud', 'description' => 'Google Cloud engineering and architecture credentials.'],
            ['name' => 'Cisco', 'category' => 'Networking', 'description' => 'Routing, switching, CCNA, CCNP, and CCIE pathways.'],
            ['name' => 'CompTIA', 'category' => 'Security', 'description' => 'Foundational IT skills including A+, Network+, Security+, and Linux+.'],
            ['name' => 'Salesforce', 'category' => 'Other', 'description' => 'Salesforce administration, development, and consulting.'],
            ['name' => 'Oracle', 'category' => 'Database', 'description' => 'Database administration, SQL, PL/SQL, and Java certification.'],
            ['name' => 'Red Hat', 'category' => 'DevOps', 'description' => 'Enterprise Linux, OpenShift, and automation capabilities.'],
            ['name' => 'VMware', 'category' => 'Networking', 'description' => 'Virtualization, vSphere, and cloud infrastructure management.'],
            ['name' => 'Project Management Institute (PMI)', 'category' => 'ITSM', 'description' => 'Project management certificates like PMP and CAPM.'],
            ['name' => 'ISACA', 'category' => 'Security', 'description' => 'Information security audit and governance like CISA and CISM.'],
            ['name' => 'ISC2', 'category' => 'Security', 'description' => 'Cybersecurity leadership certifications like CISSP and CCSP.'],
            ['name' => 'AXELOS (ITIL)', 'category' => 'ITSM', 'description' => 'IT Service Management framework methodologies.'],
            ['name' => 'Scrum.org', 'category' => 'Other', 'description' => 'Agile and Scrum framework certifications like PSM I.'],
            ['name' => 'HashiCorp', 'category' => 'DevOps', 'description' => 'Terraform, Vault, Consul, and Nomad infrastructure tools.'],
            ['name' => 'Citrix', 'category' => 'Networking', 'description' => 'Application delivery, workspace, and virtualization solutions.'],
            ['name' => 'LPI (Linux Professional Institute)', 'category' => 'Other', 'description' => 'Open-source and Linux system administration certifications.'],
        ];

        foreach ($vendors as $index => $vendor) {
            Vendor::create([
                'name' => $vendor['name'],
                'slug' => Str::slug($vendor['name']),
                'logo_path' => null,
                'description' => $vendor['description'],
                'category' => $vendor['category'],
                'exam_count' => 5, // We will seed 5 exams per vendor
                'is_active' => true,
                'sort_order' => ($index + 1) * 10,
            ]);
        }
    }
}
