<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();               // PR-2026-090
            $table->string('title')->nullable();
            $table->string('requestor');
            $table->string('department');
            $table->text('purpose')->nullable();
            $table->date('needed_by')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('urgency')->default('Low');      // Low / Medium / High
            $table->string('workflow_type')->default('sequential'); // sequential / parallel
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->string('status')->default('draft');     // draft / pending_approval / approved / rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
