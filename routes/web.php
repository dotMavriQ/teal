<?php

use App\Livewire\Books\BookForm;
use App\Livewire\Books\BookImport;
use App\Livewire\Books\BookIndex;
use App\Livewire\Books\BookSettings;
use App\Livewire\Books\BookShow;
use App\Livewire\Books\MetadataEnrichment;
use App\Livewire\Books\ReadQueue;
use App\Livewire\Dashboard;
use App\Livewire\Movies\MovieForm;
use App\Livewire\Movies\MovieImport;
use App\Livewire\Movies\MovieIndex;
use App\Livewire\Movies\MovieMetadataEnrichment;
use App\Livewire\Movies\MovieSettings;
use App\Livewire\Movies\MovieShow;
use App\Livewire\Reading\ReadingIndex;
use App\Livewire\Watching\WatchingIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Watching category
    Route::get('watching', WatchingIndex::class)->name('watching.index');

    // Movies
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', MovieIndex::class)->name('index');
        Route::get('/create', MovieForm::class)->name('create');
        Route::get('/import', MovieImport::class)->name('import');
        Route::get('/settings', MovieSettings::class)->name('settings');
        Route::get('/settings/metadata', MovieMetadataEnrichment::class)->name('metadata');
        Route::get('/{movie}', MovieShow::class)->name('show');
        Route::get('/{movie}/edit', MovieForm::class)->name('edit');
    });

    // Reading category
    Route::get('reading', ReadingIndex::class)->name('reading.index');

    // Books
    Route::prefix('books')->name('books.')->group(function () {
        Route::get('/', BookIndex::class)->name('index');
        Route::get('/create', BookForm::class)->name('create');
        Route::get('/import', BookImport::class)->name('import');
        Route::get('/queue', ReadQueue::class)->name('queue');
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
