<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Penyewaan;

class RentalHelper
{
    public static function checkRentalStatus($penyewaan)
    {
        if ($penyewaan->status === 'pending') {
            $rentalDateTime = Carbon::parse($penyewaan->tanggal_mulai . ' ' . $penyewaan->jam_mulai);
            $currentTime = Carbon::now();

            if ($currentTime->gte($rentalDateTime)) {
                $penyewaan->update(['status' => 'aktif']);
                return true;
            }
        }
        return false;
    }
}