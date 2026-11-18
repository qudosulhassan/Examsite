<?php

namespace App\Support;

use App\Models\Exam;

/**
 * Links from an exam page to the vendor's own pages. Every address below returned a normal page (HTTP 200) when it was
 * checked on 18 November 2026; vendors whose sites refused the check are left out rather than guessed.
 * For Microsoft exams with verified data, the exact Microsoft Learn study guide is linked.
 */
class OfficialSources
{
    /** vendor slug => official certification page */
    public const VENDOR_PAGES = [
        'microsoft'                       => 'https://learn.microsoft.com/en-us/credentials/browse/',
        'google-cloud-platform-gcp'       => 'https://cloud.google.com/learn/certification',
        'cisco'                           => 'https://learningnetwork.cisco.com/s/certifications',
        'comptia'                         => 'https://www.comptia.org/en-us/certifications/',
        'salesforce'                      => 'https://trailhead.salesforce.com/credentials',
        'oracle'                          => 'https://education.oracle.com/certification',
        'vmware'                          => 'https://www.vmware.com/learning/certification.html',
        'isaca'                           => 'https://www.isaca.org/credentialing',
        'isc2'                            => 'https://www.isc2.org/certifications',
        'lpi-linux-professional-institute' => 'https://www.lpi.org/our-certifications/',
        'amazon'                          => 'https://aws.amazon.com/certification/',
        'sap'                             => 'https://learning.sap.com/certifications',
        'apics'                           => 'https://www.ascm.org/learning-development/certifications-credentials/',
        'cwnp'                            => 'https://www.cwnp.com/certifications/',
        'giac'                            => 'https://www.giac.org/certifications',
        'peoplecert'                      => 'https://www.peoplecert.org/',
        'palo-alto-networks'              => 'https://www.paloaltonetworks.com/services/education/certification',
        'pegasystems'                     => 'https://academy.pega.com/',
        'python-institute'                => 'https://pythoninstitute.org/',
        'splunk'                          => 'https://www.splunk.com/en_us/training/certification.html',
        'the-open-group'                  => 'https://www.opengroup.org/certifications',
        'juniper'                         => 'https://www.juniper.net/us/en/training/certification.html',
        'fortinet'                        => 'https://www.fortinet.com/training-certification',
        'checkpoint'                      => 'https://www.checkpoint.com/services/training/certification-program/',
        'isqi'                            => 'https://isqi.org/',
        'nutanix'                         => 'https://www.nutanix.com/support-services/training-certification',
    ];

    /** @return array<int, array{label:string,url:string}> */
    public static function for(Exam $exam): array
    {
        $links = [];
        $vendor = $exam->vendor;
        $slug = (string) ($vendor->slug ?? '');
        $name = (string) ($vendor->name ?? 'vendor');
        $code = trim((string) $exam->exam_code);

        if ($slug === 'microsoft') {
            $guide = ExamGuideContent::load((string) $exam->slug);
            if (!empty($guide['url'])) {
                $links[] = ['label' => "Microsoft Learn study guide for {$code}", 'url' => $guide['url']];
            }
        }
        if (isset(self::VENDOR_PAGES[$slug])) {
            $links[] = ['label' => "Official {$name} certification pages", 'url' => self::VENDOR_PAGES[$slug]];
        }

        return $links;
    }
}
