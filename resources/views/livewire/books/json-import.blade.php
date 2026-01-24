<div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Import Books from JSON</h2>

        @if($importStatus)
            <div class="mb-4 p-4 rounded-lg {{ str_contains($importStatus, 'Error') ? 'bg-red-50 border border-red-200 text-red-800' : 'bg-blue-50 border border-blue-200 text-blue-800' }}">
                {{ $importStatus }}
            </div>
        @endif

        <form wire:submit="import" class="space-y-4">
            <!-- File Upload -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                <input 
                    type="file" 
                    wire:model="file" 
                    accept=".json"
                    class="block w-full"
                    {{ $importing ? 'disabled' : '' }}
                >
                @error('file')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-2">Upload a JSON file with book data</p>
            </div>

            <!-- Options -->
            <div class="space-y-3">
                <label class="flex items-center space-x-2">
                    <input 
                        type="checkbox" 
                        wire:model="skipDuplicates"
                        class="rounded"
                        {{ $importing ? 'disabled' : '' }}
                    >
                    <span class="text-gray-700">Skip duplicate books</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button 
                    type="submit"
                    {{ !$file || $importing ? 'disabled' : '' }}
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ $importing ? 'Importing...' : 'Import Books' }}
                </button>
                <button 
                    type="button"
                    wire:click="resetForm"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                >
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Preview -->
    @if($preview && $preview->count() > 0)
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview (showing {{ $preview->count() }} of total)</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-700 font-semibold">Title</th>
                            <th class="px-4 py-2 text-left text-gray-700 font-semibold">Author</th>
                            <th class="px-4 py-2 text-left text-gray-700 font-semibold">ISBN13</th>
                            <th class="px-4 py-2 text-left text-gray-700 font-semibold">Rating</th>
                            <th class="px-4 py-2 text-left text-gray-700 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($preview as $book)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-900">{{ Str::limit($book['title'], 40) }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $book['author'] ? Str::limit($book['author'], 30) : '—' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $book['isbn13'] ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $book['rating'] ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ match($book['status']) {
                                        'read' => 'bg-green-100 text-green-800',
                                        'reading' => 'bg-blue-100 text-blue-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    } }}">
                                        {{ ucfirst(str_replace('_', ' ', $book['status'])) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
