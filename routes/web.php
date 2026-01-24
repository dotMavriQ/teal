<?php

use App\Livewire\Dashboard;
use App\Livewire\Books\BookIndex;
use App\Livewire\Books\BookShow;
use App\Livewire\Books\BookForm;
use App\Livewire\Books\BookImport;
use App\Livewire\Books\JsonImport;
use App\Livewire\Books\BookSettings;
use App\Livewire\Books\MetadataEnrichment;
use App\Livewire\Reading\ReadingIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Reading category
    Route::get('reading', ReadingIndex::class)->name('reading.index');

    // Books
    Route::prefix('books')->name('books.')->group(function () {
        Route::get('/', BookIndex::class)->name('index');
        Route::get('/create', BookForm::class)->name('create');
        Route::get('/import', BookImport::class)->name('import');
        Route::get('/import-json', JsonImport::class)->name('import-json');
        Route::get('/settings', BookSettings::class)->name('settings');
        Route::get('/settings/metadata', MetadataEnrichment::class)->name('metadata');
        Route::get('/{book}', BookShow::class)->name('show');
        Route::get('/{book}/edit', BookForm::class)->name('edit');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
