# TEAL Project Intelligence

This file contains critical architectural and troubleshooting insights for AI assistants working on the TEAL project.

## 🏗 Infrastructure & Hosting (CRITICAL)

### **Subpath Hosting on Production (Traefik with stripprefix)**
TEAL is hosted at `https://dotmavriq.life/teal` behind a proxy (Traefik) using the `stripprefix` middleware.
- **The Problem:** Traefik strips `/teal` *before* the request reaches the app. If the app listens on `/teal/livewire/update`, it will fail because it only sees `/livewire/update`.
- **The Solution:** 
    1. **Internal Routes:** Must be registered WITHOUT the `/teal` prefix in `AppServiceProvider::boot()` (e.g., `/livewire/update` and `/livewire/livewire.js`).
    2. **Frontend Paths:** Must include the `/teal` prefix in `config/livewire.php` (`update_path` and `asset_url`).
    - **Result:** Browser calls `/teal/livewire/update` -> Traefik strips `/teal` -> App receives `/livewire/update` -> SUCCESS.
- **Related Config:** 
    - `AppServiceProvider::boot()` uses `Livewire::setUpdateRoute` and `Livewire::setScriptRoute`.
    - `config/livewire.php` has `update_path` and `asset_url`.
    - `AppServiceProvider` also trusts proxies, including `HEADER_X_FORWARDED_PREFIX`.

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
