# TEAL Project Intelligence

This file contains critical architectural and troubleshooting insights for AI assistants working on the TEAL project.

## 🏗 Infrastructure & Hosting (CRITICAL)

### **Subpath Hosting on Production (Traefik with stripprefix)**
TEAL is hosted at `https://dotmavriq.life/teal` behind a proxy (Traefik) using the `stripprefix` middleware.
- **The Problem:** When hosted on a subpath, Laravel needs to know its base URL to generate correct internal and external links. Livewire specifically needs an external `update_path` to call its AJAX endpoints.
- **The Solution:** 
    1. **Force Root URL:** In `AppServiceProvider::boot()`, call `URL::forceRootUrl(config('app.url'))` when in production. This ensures `asset()`, `route()`, etc., include the `/teal` prefix.
    2. **Livewire Update Path:** In `config/livewire.php`, set `update_path` to `/teal/livewire/update`.
    3. **Trust Proxies:** App must trust headers like `X-FORWARDED-PREFIX` (already in `AppServiceProvider`).
- **Result:**
    - `@livewireScripts` generates `/teal/livewire/livewire.js`.
    - Traefik receives `/teal/livewire/livewire.js`, strips `/teal`, and passes `/livewire/livewire.js` to the app.
    - The app (now knowing its root is `/teal`) matches the request and serves the script. SUCCESS.

### **Deployment Strategy**
There is **no autodeployment** via GitHub Actions.
- `.github/workflows/tests.yml` only runs tests.
- Changes made in this environment must be pushed to GitHub and then manually pulled/restarted on the production host.

---

## 🧹 Data Management & Cleaning

### **Goodreads CSV Imports**
Goodreads CSV exports often contain Excel-style ISBN artifacts (e.g., `="1234567890"`).
- **The Solution:** Import services (`GoodReadsImportService.php` and `JsonImportService.php`) use `cleanIsbn()` which applies `preg_replace('/[^0-9X]/i', '', $isbn)`. This successfully strips quotes, equals signs, and spaces while preserving valid ISBN-10/13 formats (including the 'X' check digit).

### **Character Encoding (Accents)**
The database and import logic support UTF-8 (accents like `ö`, `Ž`, `Ã`). 
- **The Problem:** Artifacts like `Ã¶` or `&rsquo;` are typically remnants of older imports or post-conversion JSON artifacts.
- **Recommendation:** If users report "ugly characters," check if the raw import source (e.g., `goodreads.txt`) was encoded in ISO-8859-1 before conversion to UTF-8.

---

## 🛠 Troubleshooting Toolbar Failures
If the search or sorting is non-responsive in production:
1. Verify `AppServiceProvider::boot()` has the `Livewire::setUpdateRoute` and `Livewire::setScriptRoute` calls.
2. Ensure `config/livewire.php` has the `/teal` prefix for its paths.
3. Run `php artisan optimize:clear` to ensure cached routes are refreshed.

---

## 🧪 Verification
Always check `tests/Feature/GoodReadsImportArtifactsTest.php` to ensure character and ISBN cleaning logic remains intact during refactoring.
