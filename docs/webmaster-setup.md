# Search Console, Bing Webmaster Tools and GA4 for azureexamshub.com

Do these once the owner is happy with the site. Indexing is already on.

## 1. Google Search Console

1. Go to https://search.google.com/search-console and sign in.
2. Add property, choose **Domain** and enter `azureexamshub.com`.
3. **Verify with DNS.** Google shows a TXT record. In hPanel open Domains, then DNS / Nameservers, then DNS Zone Editor for `azureexamshub.com`, add a **TXT** record: name `@`, value exactly what Google shows, TTL default. Back in Search Console, press Verify (DNS can take a few minutes up to a day).
4. **Submit the sitemap.** Indexing, then Sitemaps, enter `sitemap.xml`, Submit. It should read "Success" with about 145 discovered pages.
5. **Request indexing** (URL inspection box at the top, paste the URL, then Request indexing; there is a daily limit, so do these over a few days):
   - Home: `https://azureexamshub.com/`
   - The 7 tracks: `/tracks/azure-cloud`, `/tracks/security-identity`, `/tracks/microsoft-365`, `/tracks/power-platform`, `/tracks/dynamics-365`, `/tracks/data-ai`, `/tracks/more-exams`
   - 15 top exam guides: `/exams/az-900`, `/exams/az-104`, `/exams/az-305`, `/exams/az-400`, `/exams/az-700`, `/exams/sc-900`, `/exams/sc-200`, `/exams/sc-300`, `/exams/sc-100`, `/exams/pl-900`, `/exams/pl-300`, `/exams/dp-900`, `/exams/dp-600`, `/exams/ai-901`, `/exams/ms-102`
   - Then the five blog posts under `/blog/`.
6. Check weekly for the first month: Pages (indexing problems), Performance (queries and pages), and Experience.

## 2. Bing Webmaster Tools

1. Go to https://www.bing.com/webmasters and sign in.
2. Choose **Import** and sign in with the Google account that owns the Search Console property. Select `azureexamshub.com` and import. Bing copies the site and its sitemap.
3. Confirm the sitemap `https://azureexamshub.com/sitemap.xml` is listed and has been fetched. Optionally turn on IndexNow in Bing for faster updates.

## 3. Google Analytics 4

1. Go to https://analytics.google.com, Admin, **Create property**: name "AzureExamsHub", reporting time zone and currency (USD).
2. Create a **Web** data stream for `https://azureexamshub.com`. Copy the **Measurement ID** (looks like `G-XXXXXXXXXX`).
3. Put the ID in the site's `.env` as `GA4_MEASUREMENT_ID=G-XXXXXXXXXX` (the layout loads GA4 in production when this is set), then on the server run `php artisan config:clear && php artisan config:cache && php artisan pagecache:flush`.
4. Check the Realtime report while opening the site in another tab.
5. In Admin, Product links, link the **Search Console** property so queries appear next to behaviour.
6. Mark conversions later (for example the first checkout step) once payments are enabled.
7. Because the site has visitors worldwide, add a cookie consent banner before enabling analytics for EU/UK visitors, and describe analytics in the privacy policy.
