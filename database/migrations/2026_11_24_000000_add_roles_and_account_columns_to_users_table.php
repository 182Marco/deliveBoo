<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Customer accounts, Google sign-in and the platform backoffice (dev/PIANO_AUTH_CLIENTI.md, step 1.2):
// one users table with a role, nullable VAT number (customers have none) and password (Google-only
// accounts), the stable Google id, and the ban written by an admin.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // customer | restaurateur | admin (App\User::ROLES); never mass assignable
            $table->string('role', 20)->default('customer')->after('email')->index();
            $table->string('google_id')->nullable()->unique()->after('password');
            $table->string('avatar_url', 2048)->nullable()->after('avatar');
            $table->timestamp('banned_at')->nullable()->after('remember_token')->index();
            $table->string('ban_reason', 500)->nullable()->after('banned_at');
            $table->foreignId('banned_by')->nullable()->after('ban_reason')->constrained('users')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            // the UNIQUE indexes stay: several NULLs are allowed
            $table->bigInteger('vat_number')->nullable()->change();
            $table->string('password')->nullable()->change();
        });

        // every account created so far signed up from the partner hub
        DB::table('users')->update(['role' => 'restaurateur']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('banned_by');
            $table->dropIndex(['banned_at']);
            $table->dropColumn(['ban_reason', 'banned_at', 'avatar_url']);
            $table->dropUnique(['google_id']);
            $table->dropColumn('google_id');
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
        // vat_number and password stay nullable: accounts without them may exist by now
    }
};
