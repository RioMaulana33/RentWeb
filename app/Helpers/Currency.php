<?php

namespace App\Helpers;

use App\Models\Kurs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class Currency
{
    /**
     * Required base currency
     *
     * @var null
     */
    private $from = null;

    /**
     * Required target currency
     *
     * @var null
     */
    private $to = null;

    /**
     * @var float
     */
    private $amount = 1.00;

    /**
     * @var null
     */
    private $places = null;

    /**
     * @var null
     */
    private $date = "latest";

    public static function convert()
    {
        return new self();
    }

    /**
     * @param $currency
     *
     * @return $this
     */
    public function from(string $currency)
    {
        $this->from = strtolower($currency);
        return $this;
    }

    /**
     * @param $currency
     *
     * @return $this
     */
    public function to(string $currency)
    {
        $this->to = strtolower($currency);
        return $this;
    }

    /**
     * @param $places
     *
     * @return $this
     */
    public function round(int $places)
    {
        $this->places = $places;
        return $this;
    }

    /**
     * @param float $amount
     *
     * @return $this
     */
    public function amount(float $amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $date
     *
     * @return $this
     */
    public function date(string $date)
    {
        $this->date = $date;
        return $this;
    }

    public function get(): ?float
    {
        $from = strtoupper($this->from ?? '');
        $to = strtoupper($this->to ?? '');

        if ($from === $to) return $this->amount;

        if ($to == "IDR") {
            $kurs = Kurs::where('mata_uang', strtoupper($from))->orderBy('tanggal', 'DESC')->first();
            return $this->amount * $kurs->kurs_tengah;
        } else {
            $kurs = Kurs::where('mata_uang', strtoupper($to))->orderBy('tanggal', 'DESC')->first();
            return $this->amount / $kurs->kurs_tengah;
        }
    }

    // private function getKurs($code)
    // {
    //     $kurs = Kurs::where('mata_uang', strtoupper($code))->orderBy('tanggal', 'DESC')->first();
    //     if (!$kurs) {
    //         $this->date = Carbon::parse($this->date)->subDay()->format('Y-m-d');
    //         return $this->getKurs($code);
    //     };

    //     return $kurs;
    // }

    protected function getData()
    {
        $data = cache()->get("currency.{$this->date}.{$this->from}.{$this->to}", function () {
            return $this->getFromApi();
        });
        if ($data === null) {
            $data = cache()->get("currency.long.{$this->date}.{$this->from}.{$this->to}", null);
        }
        return $data;
    }

    protected function getFromApi()
    {
        $url = "https://cdn.jsdelivr.net/gh/fawazahmed0/currency-api@1/{$this->date}/currencies/{$this->from}/{$this->to}.min.json";
        $response = Http::get($url);
        if (!$response->successful()) {
            return null;
        }
        $key = "{$this->from}.{$this->to}";

        $data = $response->json();
        cache()->put("currency.{$this->date}.$key", $data, now()->addDay()->startOfDay());
        cache()->put("currency.long.{$this->date}.$key", $data, now()->addDays(10));

        return $data;
    }
}
