<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // ISBN and identifiers
            $table->string('asin')->nullable()->after('isbn13')->index();

            // Ratings and metrics from source
            $table->decimal('avg_rating', 3, 2)->nullable()->after('rating')->comment('Average rating from source (e.g., Goodreads)');
            $table->integer('num_ratings')->nullable()->after('avg_rating')->comment('Total number of ratings from source');

            // Publication details
            $table->string('date_pub')->nullable()->after('published_date')->comment('Original publication date from source');
            $table->string('date_pub_edition')->nullable()->after('date_pub')->comment('Specific edition publication date');

            // Reading tracking
            $table->date('date_started')->nullable()->change();
            $table->date('date_finished')->nullable()->change();
            $table->date('date_added')->nullable()->after('date_finished')->comment('When the book was added to the library');

            // Shelving and categorization
            $table->text('shelves')->nullable()->after('date_added')->comment('Comma-separated list of shelves/categories');

            // Reading activity and reviews
            $table->text('review')->nullable()->after('notes')->comment('Full review text');
            $table->integer('comments')->nullable()->after('review')->comment('Number of comments on review');
            $table->integer('votes')->nullable()->after('comments')->comment('Number of upvotes/helpful votes');

            // Ownership
            $table->boolean('owned')->default(false)->after('votes')->comment('Whether the user owns a physical copy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'asin',
                'avg_rating',
                'num_ratings',
                'date_pub',
                'date_pub_edition',
                'date_added',
                'shelves',
                'review',
                'comments',
                'votes',
                'owned',
            ]);
        });
    }
};
