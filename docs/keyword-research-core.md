# ExamTopicsBase: keyword research and page plan

Per-exam detail for all 834 exams: `docs/keyword-map-core.csv` (current title, new title, H1, description, keyword groups, what people also search).

## How the research was done

- **Source:** Google autocomplete, collected on 18 November 2026 for 524 of the 834 exams, with 8 query patterns each (for example "az-900", "az-900 practice", "az-900 study", "az-900 dumps"). It shows which phrases people really type.
- **Not available:** search volumes, competition and current rankings. That needs Search Console (your own data) or a paid tool (Ahrefs, Semrush). Google blocked further requests after about 520 exams, so the other 310 are marked "pattern-based" in the CSV. They use the same templates, validated on the sample.
- **Result:** 78% of the researched exams had at least one suggestion. The rest are niche exams with almost no search demand, so their pages rely on the exam code and name.

## What people search (share of researched exams where the phrase appears after the exam code)

| Phrase pattern | Share | Used? |
|---|---|---|
| dumps | 45% | **No** (policy and copyright risk, see below) |
| exam | 41% | Yes (title, text) |
| questions | 39% | Yes ("exam questions" in title) |
| exam questions | 34% | Yes (title, H1) |
| exam dumps | 33% | No |
| study guide | 26% | Yes (description, FAQ-style text) |
| exam cost | 23% | Information group (needs verified facts per vendor) |
| practice questions | 21% | Yes |
| certification | 20% | Yes |
| practice exam | 19% | Yes (secondary) |
| practice test | 18% | Yes (primary phrase, title) |
| study material, study guide pdf | 13% | Secondary |
| exam topics | 12% | Not targeted (it is also a competitor's brand) |
| sample questions | 10% | Free/sample group |
| exam schedule, exam prep, training, course | 8-9% | Information group |

## Keyword groups for every exam page

| Group | Phrases (code = the exam code, for example az-104) | Where used |
|---|---|---|
| Primary | `code practice test`, `code practice questions`, `code exam questions`, `code practice exam` | Title, H1, first paragraph |
| Free / sample | `code free practice test`, `code sample questions`, `code practice test pdf` | Description, free-demo link |
| Information | `code study guide`, `code exam objectives`, `code passing score`, `code exam cost`, `code syllabus` | Description, FAQ and guide sections |
| Long tail | `exam name practice test`, `vendor exam name exam questions`, `code exam name` | H1, body, internal links |
| Not targeted | `code dumps`, `code braindumps`, `code actual exam questions`, `code exam topics` | Listed in the CSV only |

## Page types

| Page | Target phrase | Title pattern (60 characters at most) |
|---|---|---|
| Home | IT certification practice tests | IT Certification Practice Tests & Questions \| ExamTopicsBase |
| Vendor page | `vendor` certification practice tests | `Vendor` Certification Practice Tests & Questions \| ExamTopicsBase |
| Exam page | `code` practice test | `CODE` Practice Test & Exam Questions \| ExamTopicsBase |
| Free sample page | free practice questions | keep as is |
| Blog post | one question per post | see the content plan |

Rules: the exam code always comes first in the title; description at most 158 characters and different for every exam; one H1 built from vendor, code and name; no keyword is repeated more than needed.

## The wording decision

"Dumps" is the single most-suggested phrase (45% of exams), so it is the biggest source of search demand. These pages deliberately do not use it, or "actual/real exam questions", "99.6% pass rate" or "100% guarantee":

- Google can demote pages with claims it cannot verify.
- Vendors such as Microsoft prohibit sharing exam content and take down sites that do.
- The same sites are the main target of copyright complaints, and a complaint can remove the whole domain from search.

If you want that demand, the lower-risk route is a separate page that explains what a "dump" is and why practice questions are safer, so that you rank for the search without making the claim.

## What changed in code

- `ExamKeywordPlan`: builds titles, descriptions and H1s from the exam code and name.
- `php artisan exams:apply-keyword-seo`: writes the text to every exam, 40 vendor pages and the home page settings, after saving a backup. `--dry-run` shows the change, `--restore=<file>` undoes it.
- Meta keywords tag removed; `og:type` fixed on exam pages; Course markup no longer names the vendor as provider; Product name no longer ends in "Dumps".

## Still open

- Exam facts (cost, passing score, duration) per vendor need verifying before they go into FAQs. Microsoft's 700 out of 1,000 passing mark is confirmed; other vendors differ.
- Body text still contains "guarantee", "real exam" and "99.6% pass" wording. It is not part of this change and needs your decision on the guarantee policy.
- Blog post titles are over 60 characters (all 12 sampled).
- Overlay records (`site_exam_overlays`) override exam titles for a site. If a title does not change after running the command, check the overlay.
