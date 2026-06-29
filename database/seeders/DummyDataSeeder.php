<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add Vendors
        $aws = Vendor::updateOrCreate(
            ['slug' => 'amazon'],
            [
                'name' => 'Amazon (AWS)',
                'logo_path' => 'https://upload.wikimedia.org/wikipedia/commons/9/93/Amazon_Web_Services_Logo.svg',
                'description' => 'Amazon Web Services certifications.',
                'category' => 'Cloud',
                'is_active' => true,
                'sort_order' => 1
            ]
        );

        $microsoft = Vendor::updateOrCreate(
            ['slug' => 'microsoft'],
            [
                'name' => 'Microsoft',
                'logo_path' => null,
                'description' => 'Microsoft Azure and 365 certifications.',
                'category' => 'Cloud',
                'is_active' => true,
                'sort_order' => 2
            ]
        );

        // Add Exams
        $awsExam = Exam::updateOrCreate(
            ['exam_code' => 'CLF-C02'],
            [
                'vendor_id' => $aws->id,
                'exam_name' => 'AWS Certified Cloud Practitioner',
                'slug' => 'aws-clf-c02',
                'description' => 'Validates overall understanding of the AWS Cloud platform.',
                'price_pdf' => 19.99,
                'price_engine' => 29.99,
                'difficulty' => 'Associate',
                'is_active' => true,
            ]
        );

        $azureExam = Exam::updateOrCreate(
            ['exam_code' => 'AZ-900'],
            [
                'vendor_id' => $microsoft->id,
                'exam_name' => 'Microsoft Azure Fundamentals',
                'slug' => 'microsoft-az-900',
                'description' => 'Prove your knowledge of cloud concepts, Azure services, workloads, security, and privacy in Azure.',
                'price_pdf' => 19.99,
                'price_engine' => 29.99,
                'difficulty' => 'Associate',
                'is_active' => true,
            ]
        );

        // Add Questions for AWS Exam
        if ($awsExam->questions()->count() == 0) {
            Question::create([
                'exam_id' => $awsExam->id,
                'question_text' => 'Which AWS service is designed to run containerized applications without needing to manage servers or clusters of EC2 instances?',
                'option_a' => 'Amazon Elastic Compute Cloud (EC2)',
                'option_b' => 'AWS Fargate',
                'option_c' => 'Amazon Elastic Block Store (EBS)',
                'option_d' => 'AWS Lambda',
                'correct_option' => 'B',
                'explanation' => 'AWS Fargate is a serverless compute engine for containers that works with both Amazon ECS and Amazon EKS.',
                'topic' => 'Compute',
                'is_active' => true,
            ]);

            Question::create([
                'exam_id' => $awsExam->id,
                'question_text' => 'Which service can be used to track user activity and API usage across AWS accounts?',
                'option_a' => 'Amazon CloudWatch',
                'option_b' => 'AWS CloudTrail',
                'option_c' => 'AWS Config',
                'option_d' => 'AWS IAM',
                'correct_option' => 'B',
                'explanation' => 'AWS CloudTrail enables governance, compliance, operational auditing, and risk auditing of your AWS account.',
                'topic' => 'Security',
                'is_active' => true,
            ]);
            
            $awsExam->update(['question_count' => 2]);
        }

        // Add Questions for Azure Exam
        if ($azureExam->questions()->count() == 0) {
            Question::create([
                'exam_id' => $azureExam->id,
                'question_text' => 'Which Azure resource allows you to group multiple resources together to manage them as a single logical entity?',
                'option_a' => 'Azure Active Directory',
                'option_b' => 'Resource Group',
                'option_c' => 'Management Group',
                'option_d' => 'Azure Subscription',
                'correct_option' => 'B',
                'explanation' => 'A Resource Group is a container that holds related resources for an Azure solution.',
                'topic' => 'Management',
                'is_active' => true,
            ]);

            Question::create([
                'exam_id' => $azureExam->id,
                'question_text' => 'Which of the following is considered an OpEx (Operational Expenditure) cost model?',
                'option_a' => 'Purchasing a physical server for a datacenter.',
                'option_b' => 'Paying monthly for virtual machines hosted in Azure.',
                'option_c' => 'Buying software licenses upfront for 5 years.',
                'option_d' => 'Building a new cooling system for an on-premises datacenter.',
                'correct_option' => 'B',
                'explanation' => 'OpEx refers to ongoing costs for running a product, business, or system on a day-to-day basis.',
                'topic' => 'Cloud Concepts',
                'is_active' => true,
            ]);
            
            $azureExam->update(['question_count' => 2]);
        }
    }
}
