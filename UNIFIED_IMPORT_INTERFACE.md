# Unified Book Import Interface

## What's Now Available

A single, unified import interface at `/books/import` that supports both **CSV** and **JSON** formats with an intuitive mobile-first design.

## Key Features

### 1. **Format Selection**
- Click-to-switch between CSV and JSON formats
- No page reload needed
- Icons indicate selected format
- Mobile-optimized button layout (2-column grid)

### 2. **Format-Specific Guidance**
Each format shows:
- **CSV Format:**
  - Supported columns from GoodReads export
  - Step-by-step instructions (Export from GoodReads → Settings → Download)
  - How duplicate detection works

- **JSON Format:**
  - Complete field reference with types
  - Example JSON structure
  - Same duplicate handling explanation

### 3. **Smart File Upload**
- Automatic file type validation based on selected format
- Only accepts `.csv` or `.txt` for CSV mode
- Only accepts `.json` for JSON mode
- Max 10MB file size
- Clear file picker with format name

### 4. **Duplicate Handling**
- Single toggle works for both formats: "Skip duplicate books"
- Detection uses: ISBN13 → ISBN → ASIN → Title+Author
- Prevents importing same book twice
- Works identically for CSV and JSON

### 5. **Import Results**
Shows:
- **Imported count** (green)
- **Skipped count** (yellow)
- **Errors count** (red)
- First 5 errors displayed with full list available
- For JSON imports: async status with spinner
- For CSV imports: immediate results + cover fetch queue status

### 6. **Cover Fetching**
- Automatically queued after import
- Shows "Fetching X book covers in background"
- Takes minutes (with random delays to avoid throttling)
- JSON imports also trigger async cover fetch

### 7. **Mobile-First Design**
- 2-column format selector on small screens
- Full-width upload area
- Stacked results cards on mobile
- Responsive table preview
- Touch-friendly buttons with proper spacing
- Vertical button layout on mobile, horizontal on desktop

## UI/UX Highlights

### Format Selector
```
┌─────────────┬─────────────┐
│   CSV       │   JSON      │
│  Format     │  Format     │
│  GoodReads  │  Custom     │
└─────────────┴─────────────┘
```

### Specs Section
- Clear heading matching selected format
- Formatted code blocks for references
- Organized information with visual hierarchy
- Max 80-char lines for readability on mobile

### Upload Area
- Dashed border indicates drop zone
- File input styled as button
- Clear label and format indication
- Checkbox for duplicate skipping

### Results
- Color-coded status cards
- Number badges for quick scanning
- Error list with truncation
- Action buttons (View Books / Import More)

## Implementation

### Component: `BookImport`
- Location: `app/Livewire/Books/BookImport.php`
- Handles both CSV and JSON via format property
- Dynamic validation rules based on format
- Integrates with both import services

### Services Used
- `GoodReadsImportService` - CSV parsing and import
- `JsonImportService` - JSON parsing and import
- `ImportFromJson` - Async job for JSON imports
- `FetchBookCover` - Cover downloading job

### View: `book-import.blade.php`
- Single responsive template
- Conditional sections by format and state
- Mobile-first responsive grid layouts
- Tailwind styling throughout

## Workflow

### CSV Import (Synchronous)
1. Select "CSV Format"
2. View CSV specs + GoodReads export instructions
3. Upload CSV file
4. Preview first 10 books
5. Click "Import Books"
6. See results immediately
7. Covers fetch in background
8. Option to "Import More"

### JSON Import (Asynchronous)
1. Select "JSON Format"
2. View JSON specs + field reference
3. Upload JSON file
4. Preview first 10 books
5. Click "Import Books"
6. See "Importing in background" message
7. Job queued with random delays
8. Check back later for results
9. Covers also fetch async with delays

## File Locations

- Component: [app/Livewire/Books/BookImport.php](app/Livewire/Books/BookImport.php)
- View: [resources/views/livewire/books/book-import.blade.php](resources/views/livewire/books/book-import.blade.php)
- Route: `GET /books/import`

## Previous Separate Routes (Now Unified)

- ✓ `/books/import` → CSV import (now unified)
- ✓ `/books/import-json` → JSON import (now merged)

## Next Steps

1. Test CSV import from actual GoodReads export
2. Test JSON import with custom data
3. Verify cover fetching works for both
4. Check mobile responsiveness on actual devices
5. Monitor performance with large imports (1000+ books)

## Example Usage

### Via CSV
1. Visit http://localhost:8000/books/import
2. Format already set to "CSV"
3. Export from GoodReads
4. Upload file
5. Done!

### Via JSON
1. Visit http://localhost:8000/books/import
2. Click "JSON Format" button
3. Use provided example structure
4. Upload file
5. Done!

Both formats:
- Support same metadata fields
- Skip duplicates automatically
- Fetch covers in parallel
- Show clear results with error details
