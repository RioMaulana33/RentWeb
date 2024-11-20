<?php

namespace App\Helpers;

use App\Models\Account;

class AccountHelper
{
    public static function penjualan()
    {
        $debit = Account::where('kode', '111.819.001.01.11')->first();
        $kredit = Account::where('kode', '423.119.001.01.01')->first();
        $piutang = Account::where('kode', '115.321.001.01.11')->first();
        $piutang_ppn = Account::where('kode', '115.791.099.99.99')->first();

        return compact('debit', 'kredit', 'piutang', 'piutang_ppn');
    }

    public static function pembelian()
    {
        $debit = Account::where('kode', '117.199.001.01.01')->first();
        $kredit = Account::where('kode', '111.819.001.01.11')->first();
        $hutang = Account::where('kode', '212.121.001.01.01')->first();
        $hutang_ppn = Account::where('kode', '219.961.006.01.01')->first();

        return compact('debit', 'kredit', 'hutang', 'hutang_ppn');
    }
}
