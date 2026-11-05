# AzureExamsHub keyword map

Site: https://azureexamshub.com (single-vendor site: Microsoft exams). Audience: worldwide, English.
Each page type has **one primary keyword** so pages do not compete. An exam has three pages and each owns a different intent.

## Per exam (56 exams, `{CODE}` = e.g. AZ-900)

| Page | URL | Intent | Primary keyword | Secondary keywords |
|---|---|---|---|---|
| Guide | `/exams/{code}` | Learn what the exam is and how to prepare | `{CODE} exam guide` | `{CODE} study guide`, `{CODE} exam skills measured`, `{CODE} exam objectives`, `{CODE} study plan`, `{CODE} passing score` |
| Free questions | `/exams/{code}/free-practice-questions` | Test myself now | `{CODE} practice questions` | `{CODE} practice test`, `{CODE} sample questions`, `{CODE} exam questions with answers`, `{CODE} free practice test` |
| Plans | `/exams/{code}/plans` | Buy a full bank / engine | `{CODE} practice test` (commercial) | `{CODE} exam simulator`, `{CODE} practice exam PDF`, `{CODE} test engine`, `buy {CODE} practice test` |

Rules used in the code (`app/Support/SeoMeta.php`):

- Guide title: `{CODE} Exam Guide {year}: Skills & Study Plan | AzureExamsHub` (falls back to shorter forms for long names).
- Questions title: `{CODE} Practice Questions with Explanations | AzureExamsHub`.
- Plans title: `{CODE} Practice Test: PDF & Engine Plans | AzureExamsHub`.
- The three titles and the three descriptions are always different, and each page's H1 contains its own primary keyword.

## Tracks (`/tracks/{slug}`)

| Track | Primary keyword | Secondary keywords |
|---|---|---|
| Azure & Cloud | `Azure certification exams` | Azure exam practice tests, AZ-900 AZ-104 AZ-305 order, Azure certification path |
| Security & Identity | `Microsoft security certification exams` | SC-900 SC-200 SC-300 practice tests, Microsoft security certification path |
| Microsoft 365 | `Microsoft 365 certification exams` | MS-102 MD-102 MS-700 practice tests, Microsoft 365 administrator exams |
| Power Platform | `Power Platform certification exams` | PL-900 PL-300 practice tests, Power BI certification exam |
| Dynamics 365 | `Dynamics 365 certification exams` | MB-230 MB-310 MB-800 practice tests, Dynamics 365 exam guide |
| Data & AI | `Microsoft data and AI certification exams` | DP-900 DP-600 AI-901 practice tests, Fabric certification exams |
| More exams | `GitHub certification exams` (GH-*), `Microsoft AI business exams` (AB-*) | GH-900 GH-300 practice tests, AB-730 practice test |

## Site pages

| Page | Primary keyword | Secondary |
|---|---|---|
| Home `/` | `Microsoft certification practice tests` | Microsoft exam guides, Microsoft practice exams with explanations, AZ-900 practice test |
| Vendor `/vendors/microsoft` | `Microsoft certification exams` | Microsoft exam list, Microsoft certification practice tests |
| Test engine `/test-engine` | `Microsoft exam simulator` | Microsoft practice test engine, timed practice exam |
| Certifications `/certifications` | `Microsoft certifications list` | Microsoft certification exams A-Z |
| Free demo `/free-demo` | `free Microsoft practice test` | Microsoft practice test demo |
| Blog `/blog` | `Microsoft certification study tips` | Microsoft exam news |
| About / Contact / FAQ / Privacy / DMCA | brand + page name | (brand queries) |

## Keywords deliberately not targeted

- **"dumps" / "braindumps" / "[code] dumps alternative"**. Microsoft's exam policies and certification agreement prohibit using or sharing real exam content, and "dumps" content is what search engines and Microsoft take action against. The site avoids the word (`SITE_AVOID_DUMPS=true`) and targets *practice questions with explanations* and *exam guide* instead, which carries the same searcher intent without the risk.
- Exam price, duration and number of questions: Microsoft does not publish a fixed value on the study guide, so the pages omit them instead of guessing.

## Content source and honesty rules

- Domains, weightings, skills, audience and the "skills outline updated" date come from each exam's official Microsoft Learn study guide (`resources/seo-content/learn/{slug}.json`, fetched with a script, 54 of 56 exams).
- Exams Microsoft no longer lists (AZ-303, 62-193) or has retired (PL-400) keep their pages for visitors but are `noindex` and out of the sitemap; retiring exams (MS-102) show the retirement date.
- Anything that cannot be verified is left out.
