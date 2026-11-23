<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Audit log of the platform backoffice (dev/PIANO_AUTH_CLIENTI.md): who did what to which account.
// Rows are only appended (App\AuditLog::record), never updated.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_audit_logs', function (Blueprint $table) {
            $table->id();
            // the admin who acted; kept as NULL if that account is ever deleted
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            // user.created | user.updated | user.banned | user.unbanned | admin.login
            $table->string('action', 40)->index();
            $table->string('target_type', 40)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            // changed fields (old → new), ban reason… never passwords
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_audit_logs');
    }
};
