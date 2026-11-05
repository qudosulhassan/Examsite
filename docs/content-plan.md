# AzureExamsHub: 90-day content plan (20 long-tail posts)

Goal: grow search visibility for exam-specific and path questions without publishing anything we cannot verify.
The five launch posts are already written (AZ-900 vs AZ-104, Azure path 2026, SC-200 4-week plan, certification renewal, MS-102 retirement).

## Rules for every post

1. **Verify on Microsoft Learn first.** Open the exam's study guide and its certification page, and note the date checked in the post. Facts that cannot be verified are left out.
2. **Use the data we already have.** Domains, weightings and skills for 54 exams are in `resources/seo-content/learn/{slug}.json`; build plans and comparisons from those.
3. **1,200–1,800 words**, one H1, question-style H2s where natural, a short FAQ at the end, sources linked at the bottom.
4. **Internal links:** the exam guide, the free practice questions page, the track page, and one or two related posts.
5. **Do not target "dumps" or real-exam-question keywords** (see `docs/seo-keyword-map.md`).
6. **Keep posts current.** Re-check retiring/renamed exams monthly; add "Updated on" when a post changes.
7. **Publish with the command:** add the HTML and an entry in `resources/seo-content/blog/posts.json`, then run `php artisan blog:publish-site-posts` on the server.

## The plan

Cadence: two posts a week for the first month, then one to two a week. Dates are targets counted from launch.

| # | Target week | Working title | Primary keyword | Links to | Must verify first |
|---|---|---|---|---|---|
| 1 | 1 | AZ-104 study plan: three weeks from the official outline | AZ-104 study plan | AZ-104 guide, free questions | AZ-104 study guide, certification page |
| 2 | 1 | AZ-305 or AZ-104 first? The prerequisite explained | AZ-305 prerequisites | AZ-104, AZ-305 | AZ-305 certification page (prerequisite) |
| 3 | 2 | SC-900 vs SC-200 vs SC-300: which security exam first | SC-900 vs SC-200 | SC-900, SC-200, SC-300 | Three study guides and certification pages |
| 4 | 2 | PL-900 vs PL-300: the Power Platform path | PL-900 vs PL-300 | PL-900, PL-300 | Both study guides |
| 5 | 3 | DP-900 or AI-901: data or AI fundamentals first? | DP-900 vs AI-901 | DP-900, AI-901 | Both study guides, roundup for AI-901 |
| 6 | 3 | Pass AZ-900 in two weeks: a day-by-day plan | AZ-900 study plan | AZ-900 guide | AZ-900 study guide |
| 7 | 4 | What is a scaled score? Microsoft's 700 out of 1,000 explained | Microsoft exam passing score | Several exam guides | Exam scoring page on Microsoft Learn |
| 8 | 4 | AZ-400 prerequisites in 2026 after the Azure Developer retirement | AZ-400 prerequisites | AZ-400, AZ-104 | DevOps Engineer Expert and Azure Developer pages |
| 9 | 5 | How to use Microsoft's free practice assessments with a question bank | Microsoft practice assessment | Free questions pages | Practice assessment pages |
| 10 | 5 | SC-300 study plan: four weeks for identity and access | SC-300 study plan | SC-300 | SC-300 study guide |
| 11 | 6 | SC-401 study plan: information protection with Microsoft Purview | SC-401 study plan | SC-401 | SC-401 study guide |
| 12 | 6 | MD-102 study plan: Intune and endpoint management | MD-102 study plan | MD-102 | MD-102 study guide |
| 13 | 7 | AI-103 vs AI-200: which Azure AI developer exam? | AI-103 vs AI-200 | AI-103, AI-200 | Both study guides, roundup status |
| 14 | 8 | DP-600 vs DP-700: Fabric analytics or data engineering | DP-600 vs DP-700 | DP-600, DP-700 | Both study guides |
| 15 | 8 | AZ-700 study plan: Azure networking domain by domain | AZ-700 study plan | AZ-700 | AZ-700 study guide |
| 16 | 9 | MB-800 study plan: Business Central functional consultant | MB-800 study plan | MB-800 | MB-800 study guide |
| 17 | 10 | GH-900 vs GH-300: starting with GitHub certifications | GH-900 vs GH-300 | GH-900, GH-300 | Both study guides |
| 18 | 11 | Microsoft certification changes in 2026: what retired and what replaced it | Microsoft certification retirements 2026 | Retired-exam pages | Credentials roundup (refresh monthly) |
| 19 | 12 | Online or test centre? How Microsoft exam delivery works | Microsoft exam online proctored | Several exam guides | Exam scheduling and policies pages |
| 20 | 13 | Retaking a Microsoft exam: rules, waiting times and a recovery plan | Microsoft exam retake policy | Several exam guides | Exam retake policy page (do not guess wait times) |

## Hub pages to add around the posts

- A "Microsoft certification paths" page linking the posts by role (admin, architect, security, data, AI, Power Platform).
- A "Retired and retiring exams" page that lists every retired exam on the site with its official replacement and date, updated monthly. This is the most link-worthy asset because candidates search for it.

## Measuring

Check Google Search Console monthly: queries per post, impressions on exam guide pages, and which exam codes bring traffic. Write the next posts for the codes that already earn impressions.
