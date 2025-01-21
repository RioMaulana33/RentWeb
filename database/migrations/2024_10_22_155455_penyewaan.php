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
        Schema::create('penyewaan', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('kode_penyewaan')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('mobil_id')->nullable()->references('id')->on('mobil')->onDelete('cascade');
            $table->foreignId('kota_id')->nullable()->references('id')->on('kota')->onDelete('cascade');
            $table->foreignId('delivery_id')->nullable()->refrences('id')->on('delivery')->onDelete('cascade');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();    
            $table->string('jam_mulai')->nullable();
            $table->string('rental_option')->nullable();
            $table->enum('status',['pending', 'aktif', 'selesai'])->default('pending');
            $table->double('total_biaya')->nullable();
            $table->string('alamat_pengantaran')->nullable();
            $table->timestamp('waktu_pengembalian_aktual')->nullable();
            $table->decimal('denda', 12, 2)->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewaan');
    }
};

