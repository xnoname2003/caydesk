<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('Open');
            $table->foreignUuid('priority_id')->constrained('priorities');
            $table->foreignUuid('category_id')->constrained('categories');
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignUuid('assigned_agent_id')->nullable()->constrained('users');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority_id');
            $table->index('category_id');
            $table->index('assigned_agent_id');
            $table->index('created_by');
            $table->index('due_at');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
