<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE modulos MODIFY COLUMN tipo_contenido ENUM('video','documento','pdf','ppt','texto','imagen','evaluacion') NOT NULL");
        }
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 32);
            $table->string('status', 24)->default('processing')->index();
            $table->string('source', 24)->default('upload');
            $table->string('original_name');
            $table->string('mime_type', 160);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum', 64)->index();
            $table->string('transformation_profile', 80)->default('original');
            $table->text('external_url')->nullable();
            $table->json('metadata')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('unreferenced_at')->nullable()->index();
            $table->timestamps();

            $table->index(['checksum', 'transformation_profile', 'status'], 'media_assets_dedup_idx');
        });

        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('disk', 64);
            $table->string('path');
            $table->string('mime_type', 160);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum', 64);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['media_asset_id', 'type']);
            $table->unique(['disk', 'path']);
        });

        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->morphs('attachable');
            $table->string('collection', 32);
            $table->boolean('active')->default(false)->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id', 'collection', 'active'], 'media_attachment_current_idx');
        });

        Schema::create('media_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('purpose', 32);
            $table->string('status', 24)->default('created')->index();
            $table->string('original_name');
            $table->string('declared_mime_type', 160)->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('chunk_size')->default(8388608);
            $table->unsignedInteger('total_parts');
            $table->json('received_parts')->nullable();
            $table->string('temp_disk', 64)->default('media_temp');
            $table->string('temp_path');
            $table->string('provider_upload_id')->nullable();
            $table->string('provider_path')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_uploads');
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('media_variants');
        Schema::dropIfExists('media_assets');
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE modulos MODIFY COLUMN tipo_contenido ENUM('video','pdf','ppt','texto','imagen','evaluacion') NOT NULL");
        }
    }
};
