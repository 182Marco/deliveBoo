<?php

use App\Reviews\RestaurantRating;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The anonymous "rates" become customer reviews: who wrote them, for which order, an optional text,
// the restaurateur's reply and the moderation of the platform admin. The aggregates are stored on the
// restaurant (written only by App\Reviews\RestaurantRating): arithmetic average, count and the
// Bayesian score used only to rank "Guest favourites". Old rows (no author) stay as anonymous votes.
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('rates', 'reviews');
        Schema::table('reviews', function (Blueprint $table) {
            $table->renameColumn('rate', 'rating');
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('restaurant_id')->constrained()->nullOnDelete();
            // one review per order
            $table->foreignId('order_id')->nullable()->unique()->after('user_id')->constrained()->nullOnDelete();
            $table->text('comment')->nullable()->after('rating');
            // only the latest review of each customer counts in the average of a restaurant
            $table->boolean('is_current')->default(true)->after('comment');
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            // moderation of the platform admin: hidden reviews are never public nor counted
            $table->timestamp('hidden_at')->nullable();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['restaurant_id', 'is_current', 'hidden_at']);
            $table->index(['restaurant_id', 'created_at']);
        });
        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('rating_avg', 3, 2)->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->decimal('rating_score', 4, 3)->nullable()->index();
        });

        RestaurantRating::refreshAll();
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropIndex(['rating_score']);
            $table->dropColumn(['rating_avg', 'rating_count', 'rating_score']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['order_id']);
            $table->dropForeign(['hidden_by']);
            $table->dropIndex(['restaurant_id', 'is_current', 'hidden_at']);
            $table->dropIndex(['restaurant_id', 'created_at']);
            $table->dropUnique(['order_id']);
            $table->dropColumn(['user_id', 'order_id', 'comment', 'is_current', 'reply', 'replied_at', 'hidden_at', 'hidden_by']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->renameColumn('rating', 'rate');
        });
        Schema::rename('reviews', 'rates');
    }
};
