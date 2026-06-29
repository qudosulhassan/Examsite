<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Question;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exams = Exam::all();

        $questionPool = [
            [
                'text' => 'Which of the following is a primary design pattern used to achieve high availability in a cloud infrastructure deployment?',
                'a' => 'Deploying resources across multiple Availability Zones or Regions',
                'b' => 'Implementing vertical scaling on a single virtual machine instance',
                'c' => 'Using a single database host with local replication',
                'd' => 'Setting up a static IP address mapping directly to the client network interface',
                'correct' => 'A',
                'explanation' => 'High availability is achieved by eliminating single points of failure. Deploying services across multiple isolated Availability Zones ensures that even if one zone experiences an outage, the other zones can continue to serve incoming requests.'
            ],
            [
                'text' => 'Your development team needs to deploy an application container stack with auto-recovery, secret management, and minimal overhead. Which service tier should you recommend?',
                'a' => 'Serverless Container Engine / Managed Kubernetes Service',
                'b' => 'Bare-metal virtual machine servers with manual cron jobs',
                'c' => 'Shared web hosting control panel account with ftp transfer',
                'd' => 'A static content delivery network distribution',
                'correct' => 'A',
                'explanation' => 'Managed container engines (like AWS ECS/Fargate, Azure Container Apps, or GCP Cloud Run) provide native container orchestration, automatic scaling, secret integration, and auto-healing capabilities with zero server provisioning overhead.'
            ],
            [
                'text' => 'When configuring network security rules, what is the best practice regarding default inbound traffic policies?',
                'a' => 'Deny all inbound traffic by default and explicitly allow only necessary protocols and ports (Least Privilege)',
                'b' => 'Allow all traffic from the internet and monitor logs for unusual patterns',
                'c' => 'Permit only HTTP traffic on port 80 and block HTTPS on port 443',
                'd' => 'Allow inbound SSH and RDP traffic globally for administrative access ease',
                'correct' => 'A',
                'explanation' => 'The principle of least privilege states that only authorized access should be granted. By defaulting to deny all inbound traffic, you reduce the attack surface. Only specific, verified ports/sources should be whitelisted.'
            ],
            [
                'text' => 'A company is planning to migrate a legacy MySQL database to the cloud. They require automatic patching, backups, and point-in-time recovery. Which database service model fits best?',
                'a' => 'Managed Relational Database Service (RDS / Cloud SQL / Azure Database for MySQL)',
                'b' => 'Hosting MySQL inside a self-managed Virtual Machine with local disks',
                'c' => 'Migrating the database engine to a NoSQL Key-Value Store',
                'd' => 'Utilizing a static object storage bucket to hold SQL file backups',
                'correct' => 'A',
                'explanation' => 'Fully managed relational database services automate administration tasks like OS patching, software upgrades, daily backups, replication, high availability clustering, and point-in-time recovery, freeing the team from database maintenance.'
            ],
            [
                'text' => 'Which HTTP response status code should be returned if a client requests a resource that they are authenticated for, but do not possess the required permissions to access?',
                'a' => '403 Forbidden',
                'b' => '401 Unauthorized',
                'c' => '404 Not Found',
                'd' => '500 Internal Server Error',
                'correct' => 'A',
                'explanation' => 'A 401 Unauthorized code means the user is not authenticated. A 403 Forbidden code means the user is authenticated but does not have the permissions required to access the requested resource.'
            ],
            [
                'text' => 'Under what circumstances would you select a NoSQL database over a traditional Relational Database Management System (RDBMS)?',
                'a' => 'When storing large volumes of unstructured or semi-structured data with flexible schemas and scaling out horizontally is required',
                'b' => 'When multi-table complex transactions requiring strict ACID compliance are the main system focus',
                'c' => 'When the data structure is fixed and will never change over the application lifecycle',
                'd' => 'When reporting engines require heavy SQL JOIN operations across dozens of tables',
                'correct' => 'A',
                'explanation' => 'NoSQL databases are designed for schema flexibility, horizontal scalability (sharding), and high-throughput read/writes of unstructured/semi-structured data (like JSON documents, key-values, or graphs).'
            ],
            [
                'text' => 'What is the primary role of a Load Balancer in a multi-tier web application architecture?',
                'a' => 'Distributing incoming web traffic across multiple backend servers to ensure availability and performance',
                'b' => 'Encrypting hard disks on target application server nodes',
                'c' => 'Compiling client side JavaScript files and serving them directly to browsers',
                'd' => 'Managing user session state inside a centralized relational database',
                'correct' => 'A',
                'explanation' => 'Load Balancers sit in front of application instances and distribute client requests evenly to prevent overload, handle health checks, and bypass failed instances, improving overall system resilience.'
            ],
            [
                'text' => 'In asymmetric encryption systems, which key is used by the sender to encrypt data, and which key is used by the recipient to decrypt it?',
                'a' => 'Sender encrypts with Recipients Public Key; Recipient decrypts with Recipients Private Key',
                'b' => 'Sender encrypts with Senders Private Key; Recipient decrypts with Senders Public Key',
                'c' => 'Sender encrypts with Recipients Private Key; Recipient decrypts with Recipients Public Key',
                'd' => 'Both parties use a shared symmetric password key that is changed periodically',
                'correct' => 'A',
                'explanation' => 'To send secure data in an asymmetric system, the sender encrypts the payload with the recipient\'s public key (which is freely available). Only the recipient holds the corresponding private key required to decrypt the payload.'
            ],
            [
                'text' => 'Which of the following describes the concept of "Infrastructure as Code" (IaC)?',
                'a' => 'Managing and provisioning computing infrastructure through machine-readable definition files rather than manual interactive tools',
                'b' => 'Writing source code for a web server directly inside a command-line interface',
                'c' => 'Using drag-and-drop web consoles to configure firewall ports and subnets',
                'd' => 'Documenting hardware setups in a text file for the operations team to execute manually',
                'correct' => 'A',
                'explanation' => 'Infrastructure as Code (IaC) is the practice of defining infrastructure components (VMs, networks, databases, policies) in configuration files (like Terraform, CloudFormation, or Ansible), allowing setups to be versioned, audited, and reproduced automatically.'
            ],
            [
                'text' => 'What is the main objective of setting up a Content Delivery Network (CDN) for a globally accessed website?',
                'a' => 'Caching static content at edge locations closer to users to reduce latency and server load',
                'b' => 'Running backend database queries in memory to increase throughput',
                'c' => 'Preventing all security breaches and blocking malicious bots globally',
                'd' => 'Compressing source code files on the local developer machine before deployment',
                'correct' => 'A',
                'explanation' => 'CDNs replicate and cache static assets (images, CSS, JS, videos) at edge servers globally. When a user requests an asset, it is fetched from the nearest geographical server, reducing loading latency and original server traffic.'
            ]
        ];

        foreach ($exams as $exam) {
            foreach ($questionPool as $index => $q) {
                // Personalize the question slightly based on the exam code
                $questionText = str_replace(
                    ['a cloud infrastructure deployment', 'an application container stack', 'network security rules', 'a legacy MySQL database', 'a multi-tier web application architecture', 'a globally accessed website'],
                    ["a {$exam->exam_code} deployment", "a {$exam->exam_code} application stack", "{$exam->exam_code} security rules", "a {$exam->exam_code} database migration", "a {$exam->exam_code} load balancing tier", "a {$exam->exam_code} CDN setup"],
                    $q['text']
                );

                Question::create([
                    'exam_id' => $exam->id,
                    'question_text' => $questionText,
                    'option_a' => $q['a'],
                    'option_b' => $q['b'],
                    'option_c' => $q['c'],
                    'option_d' => $q['d'],
                    'correct_option' => $q['correct'],
                    'explanation' => $q['explanation'],
                    'image_filename' => null,
                    'topic' => $exam->topics[($index % count($exam->topics))],
                    'is_active' => true,
                ]);
            }
        }
    }
}
