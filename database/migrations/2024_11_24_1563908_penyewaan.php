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
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('mobil_id')->nullable()->references('id')->on('mobil')->onDelete('cascade');
            $table->foreignId('kota_id')->nullable()->references('id')->on('kota')->onDelete('cascade');
            $table->foreignId('delivery_id')->nullable()->references('id')->on('delivery')->onDelete('cascade');
            $table->foreignId('rentaloptions_id')->nullable()->references('id')->on('rentaloptions')->onDelete('cascade');
            $table->string('kode_penyewaan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();    
            $table->time('jam_mulai')->nullable();
            $table->string('alamat_pengantaran')->nullable();
            $table->enum('status',['pending', 'aktif', 'selesai'])->default('pending');
            $table->double('total_biaya')->nullable();
            $table->decimal('denda', 12, 2)->default(0);

            // // // // // // // // // // // // // // // // // // // // // //
            $table->string('payment_status')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('midtrans_booking_code')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->timestamp('payment_time')->nullable();


            $table->timestamp('maintenance_end')->nullable(); 
            $table->timestamp('waktu_pengembalian_aktual')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
    {
        Schema::table('penyewaan', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_url', 
                'midtrans_booking_code',
                'midtrans_transaction_id',
                'payment_time'
            ]);
        });
    }
};

