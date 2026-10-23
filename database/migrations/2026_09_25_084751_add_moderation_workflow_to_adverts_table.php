<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL enum columns aren't touched by Blueprint::change() without doctrine/dbal,
        // so widen it with a raw statement instead.
        DB::statement("ALTER TABLE adverts MODIFY COLUMN ad_status ENUM('active','disabled','banned','pending_review') NOT NULL");

        Schema::table('adverts', function (Blueprint $table) {
            $table->timestamp('resubmitted_at')->nullable()->after('sold_date');
        });

        Schema::create('advert_moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advert_id')->constrained('adverts')->cascadeOnDelete();
            // Null admin_id means the seller triggered this event (e.g. resubmitted).
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->enum('action', ['banned', 'resubmitted', 'approved', 'rejected']);
            $table->string('reason_category')->nullable();
            $table->text('reason_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_moderation_logs');

        Schema::table('adverts', function (Blueprint $table) {
            $table->dropColumn('resubmitted_at');
        });

        DB::statement("ALTER TABLE adverts MODIFY COLUMN ad_status ENUM('active','disabled','banned') NOT NULL");
    }
};
