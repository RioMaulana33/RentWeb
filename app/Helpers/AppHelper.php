<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;

class AppHelper
{
	static function paginateCollection($collection, $perPage, $pageName = 'page', $fragment = null)
	{
		$currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($pageName);
		$currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage);
		parse_str(request()->getQueryString(), $query);
		unset($query[$pageName]);
		$paginator = new \Illuminate\Pagination\LengthAwarePaginator(
			$currentPageItems,
			$collection->count(),
			$perPage,
			$currentPage,
			[
				'pageName' => $pageName,
				'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
				'query' => $query,
				'fragment' => $fragment
			]
		);

		return $paginator;
	}

	static function addDaysExcludeWeekend($date, $addDays)
	{
		$holidays = [];

		$MyDateCarbon = Carbon::parse($date);
		$MyDateCarbon->addWeekdays($addDays);

		for ($i = 1; $i <= $addDays; $i++) {
			if (in_array(Carbon::parse($date)->addWeekdays($i)->toDateString(), $holidays)) {
				$MyDateCarbon->addDay();
			}
		}

		return $MyDateCarbon;
	}
	static function angkaToBulan($bulan)
	{
		$bulans = array(
			1 => 'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);

		return $bulans[(int)$bulan];
	}
	static function parsePhone($number)
	{
		$number = str_replace('-', '', $number);
		$number = str_replace('_', '', $number);
		$number = preg_replace('/[a-z]/i', '', $number);
		preg_match_all('!\d+!', $number, $no);
		$no = $no[0][0];
		// $no = str_replace(" ","",$no[0]);
		// $no = str_replace("'","",$no);
		// $no = str_replace("\"","",$no);
		// $no = str_replace("-","",$no);
		// $no = str_replace("(","",$no);
		// $no = str_replace("*","",$no);
		// $no = str_replace("^","",$no);
		// $no = str_replace(")","",$no);
		// $no = str_replace(".","",$no);
		// $no = str_replace(",","",$no);
		// $no = str_replace("/","",$no);
		// $no = str_replace("?","",$no);

		$phone = null;


		// 0 TO 62
		//     if(substr(trim($no), 0, 2)=='62'){
		//     $phone = trim($no);
		//     }elseif(substr(trim($no), 0, 1)=='0'){
		//     $phone = '62'.substr(trim($no), 1);
		// }elseif(substr(trim($no), 0, 1)=='+'){
		//     $phone = substr(trim($no), 1);
		// }

		// 62 TO 0
		if (substr(trim($no), 0, 1) == '0') {
			$phone = trim($no);
		} elseif (substr(trim($no), 0, 2) == '62') {
			$phone = '0' . substr(trim($no), 2);
		} elseif (substr(trim($no), 0, 3) == '+62') {
			$phone = '0' . substr(trim($no), 3);
		}

		return $phone;
	}

	static function randomColor($awal = 'rgb(', $akhir = ')')
	{
		$rgbColor = [];

		foreach (array('r', 'g', 'b') as $color) {
			$rgbColor[$color] = mt_rand(0, 255);
		}

		return $awal . implode(",", $rgbColor) . $akhir;
	}

	static function generateOTP($n)
	{
		$generator = "1357902468";
		$result = "";

		for ($i = 1; $i <= $n; $i++) {
			$result .= substr($generator, (rand() % (strlen($generator))), 1);
		}

		return $result;
	}


	static function cached_asset($path, $bustQuery = false)
	{
		$realPath = public_path($path);
		if (!file_exists($realPath)) {
			throw new LogicException("File not found at [{$realPath}]");
		}
		$timestamp = filemtime($realPath);

		if (!$bustQuery) {
			$extension = pathinfo($realPath, PATHINFO_EXTENSION);
			$stripped = substr($path, 0, - (strlen($extension) + 1));
			$path = implode('.', array($stripped, $timestamp, $extension));
		} else {
			$path  .= '?' . $timestamp;
		}
		return asset($path);
	}

	static function BulanToRomawi($date)
	{
		$array_bln = array(1 => "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII");
		$bln = $array_bln[$date];
		return $bln;
	}

	static function tanggal_indo($tanggal, $cetak_hari = false)
	{
		$hari = array(
			'',
			'Senin',
			'Selasa',
			'Rabu',
			'Kamis',
			'Jumat',
			'Sabtu',
			'Minggu'
		);

		$bulan = array(
			'', //0
			'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$split 	  = explode('-', $tanggal);
		$tgl_indo = $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];

		if ($cetak_hari) {
			$num = date('N', strtotime($tanggal));
			return $hari[$num] . ', ' . $tgl_indo;
		}
		return $tgl_indo;
	}

	static function tgl_indo($tgl)
	{
		$tanggal = substr($tgl, 8, 2);
		switch (substr($tgl, 5, 2)) {
			case '01':
				$bulan = "Januari";
				break;
			case '02':
				$bulan = "Februari";
				break;
			case '03':
				$bulan = "Maret";
				break;
			case '04':
				$bulan = "April";
				break;
			case '05':
				$bulan = "Mei";
				break;
			case '06':
				$bulan = "Juni";
				break;
			case '07':
				$bulan = "Juli";
				break;
			case '08':
				$bulan = "Agustus";
				break;
			case '09':
				$bulan = "September";
				break;
			case '10':
				$bulan = "Oktober";
				break;
			case '11':
				$bulan = "November";
				break;
			case '12':
				$bulan = "Desember";
				break;
		}

		$tahun = substr($tgl, 0, 4);
		return $tanggal . ' ' . $bulan . ' ' . $tahun;
	}

	static function bulan_tahun($tgl)
	{
		$tanggal = substr($tgl, 8, 2);
		switch (substr($tgl, 5, 2)) {
			case '01':
				$bulan = "Januari";
				break;
			case '02':
				$bulan = "Februari";
				break;
			case '03':
				$bulan = "Maret";
				break;
			case '04':
				$bulan = "April";
				break;
			case '05':
				$bulan = "Mei";
				break;
			case '06':
				$bulan = "Juni";
				break;
			case '07':
				$bulan = "Juli";
				break;
			case '08':
				$bulan = "Agustus";
				break;
			case '09':
				$bulan = "September";
				break;
			case '10':
				$bulan = "Oktober";
				break;
			case '11':
				$bulan = "November";
				break;
			case '12':
				$bulan = "Desember";
				break;
		}

		$tahun = substr($tgl, 0, 4);
		return $bulan . ' ' . $tahun;
	}

	static function hari_indo($tanggal)
	{
		$hari = array(
			1 =>    'Senin',
			'Selasa',
			'Rabu',
			'Kamis',
			'Jumat',
			'Sabtu',
			'Minggu'
		);
		$num = date('N', strtotime($tanggal));
		return $hari[$num];
	}

	static function tanpa_tahun($tanggal, $cetak_hari = false)
	{
		$hari = array(
			1 =>    'Senin',
			'Selasa',
			'Rabu',
			'Kamis',
			'Jumat',
			'Sabtu',
			'Minggu'
		);

		$bulan = array(
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$split 	  = explode('-', $tanggal);
		$tgl_indo = $split[2] . ' ' . $bulan[(int)$split[1]];

		if ($cetak_hari) {
			$num = date('N', strtotime($tanggal));
			return $hari[$num] . ', ' . $tgl_indo;
		}
		return $tgl_indo;
	}

	static function TglToText($tgl)
	{
		$split 	  = explode('-', $tgl);
		$bulan = array(
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$hari = self::hari_indo($tgl);
		$tgl_indo = self::AngkaToText($split[2]);
		$bulan = $bulan[(int)$split[1]];
		$tahun = self::AngkaToText($split[0]);
		return ucfirst($hari) . " tanggal " . ucwords($tgl_indo) . " bulan " . ucfirst($bulan) . " tahun " . ucwords($tahun);
	}

	static function AngkaToText($nilai)
	{
		$nilai = abs($nilai);
		$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($nilai < 12) {
			$temp = "" . $huruf[$nilai];
		} else if ($nilai < 20) {
			$temp = self::AngkaToText($nilai - 10) . " belas";
		} else if ($nilai < 100) {
			$temp = self::AngkaToText($nilai / 10) . " puluh " . self::AngkaToText($nilai % 10);
		} else if ($nilai < 200) {
			$temp = " seratus " . self::AngkaToText($nilai - 100);
		} else if ($nilai < 1000) {
			$temp = self::AngkaToText($nilai / 100) . " ratus " . self::AngkaToText($nilai % 100);
		} else if ($nilai < 2000) {
			$temp = " seribu " . self::AngkaToText($nilai - 1000);
		} else if ($nilai < 1000000) {
			$temp = self::AngkaToText($nilai / 1000) . " ribu " . self::AngkaToText($nilai % 1000);
		} else if ($nilai < 1000000000) {
			$temp = self::AngkaToText($nilai / 1000000) . " juta " . self::AngkaToText($nilai % 1000000);
		} else if ($nilai < 1000000000000) {
			$temp = self::AngkaToText($nilai / 1000000000) . " milyar " . self::AngkaToText(fmod($nilai, 1000000000));
		} else if ($nilai < 1000000000000000) {
			$temp = self::AngkaToText($nilai / 1000000000000) . " trilyun " . self::AngkaToText(fmod($nilai, 1000000000000));
		}
		return ucwords($temp);
	}

	static function seo($s)
	{
		$c = array(' ');
		$d = array('-', '/', '\\', ',', '.', '#', ':', ';', '\'', '"', '[', ']', '{', '}', ')', '(', '|', '`', '~', '!', '@', '%', '$', '^', '&', '*', '=', '?', '+');
		$s = str_replace($d, '', $s); // Hilangkan karakter yang telah disebutkan di array $d
		$s = strtolower(str_replace($c, '-', $s)); // Ganti spasi dengan tanda - dan ubah hurufnya menjadi kecil semua

		return $s;
	}

	static function filename($s)
	{
		$c = array(' ');
		$d = array('-', '/', '\\', ',', '.', '#', ':', ';', '\'', '"', '[', ']', '{', '}', ')', '(', '|', '`', '~', '!', '@', '%', '$', '^', '&', '*', '=', '?', '+');
		$s = str_replace($d, '', $s); // Hilangkan karakter yang telah disebutkan di array $d
		$s = strtoupper(str_replace($c, '_', $s)); // Ganti spasi dengan tanda - dan ubah hurufnya menjadi kecil semua

		return $s;
	}

	static function generateRandomToken()
	{
		return self::generateRandomCharHex(8) . '-' . self::generateRandomCharHex(4) . '-' . self::generateRandomCharHex(4) . '-' . self::generateRandomCharHex(4) . '-' . self::generateRandomCharHex(12);
	}

	static function generateRandomCharHex($a)
	{
		$char = 'ABCDEF1234567890';
		$str = '';

		for ($i = 0; $i < $a; $i++) {
			$pos = rand(0, strlen($char) - 1);
			$str .= $char[$pos];
		}

		return $str;
	}

	static function generateForgotToken()
	{
		$code = '';
		do {
			$code = strtolower(self::generateRandomCharHex(8) . '-' . self::generateRandomCharHex(4) . '-' . self::generateRandomCharHex(4) . '-' . self::generateRandomCharHex(4) . '-' . self::generateRandomCharHex(12));
			$cek = DB::table('password_resets')->where('token', $code)->count();
		} while (0 < $cek);
		return $code;
	}

	static function rupiah($a, $b = 0)
	{
		return "Rp. " . number_format($a, $b, ',', '.');
	}

	static function unrupiah($a)
	{
		$a = str_replace('Rp. ', '', $a);
		$a = str_replace('.', '', $a);
		$a = str_replace(',', '.', $a);

		return $a;
	}
}
