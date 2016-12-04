<?php
namespace Tea\Tests\Uzi\Misc;

use Tea\Uzi\Str;
use Tea\Uzi\Regex;
use PHPUnit\Framework\TestCase;

class RegexTest extends TestCase
{
	//preg_match 4.92 - 5.27
	//ereg_match

	/**
	 * @dataProvider patternSubject()
	 */
	public function testPregMatch($pattern, $subject, $flags = 0, $offset = 0)
	{
		// $imax = 2000000;
		$imax = 50000;

		// $pgm = microtime(true);
		// for ($i=0; $i < $imax; $i++) {
		// 	$matches = [];
		// 	$rpgm = preg_match('/'.$pattern.'/u', $subject, $matches);
		// }
		// $pgm = microtime(true) - $pgm;
		$matches = null;
		$usubject = trim(json_encode($subject), '"');
		$pg = microtime(true);
		for ($i=0; $i < $imax; $i++) {
			$rpg = preg_match('/'.$pattern.'/u', $usubject);
		}
		$pg = microtime(true) - $pg;


		$mb = microtime(true);
		for ($i=0; $i < $imax; $i++) {
			$rmb = mb_ereg_match($pattern, $usubject, 'msz');
		}
		$mb = microtime(true) - $mb;

		// $spg = microtime(true);
		// for ($i=0; $i < $imax; $i++) {
		// 	$rspg = preg_match(new Str('/'.$pattern.'/u'), $subject);
		// }
		// $spg = microtime(true) - $spg;


		$decs = 2;

		$scr = function($v){
			return strtoupper(($v ? 'ok' : 'err'));
		};

		$res = function($n, $r) use ($decs, $scr){
			return number_format($n, $decs).' - '.$scr($r);
		};

		$trc = function($v, $max = 16, $end = '...'){
			$vlen = mb_strlen($v);
			if($vlen <= $max)
				return $v;
			$elen = mb_strlen($end);
			$len = $vlen - $elen;
			return mb_substr($v, 0, $len).$end;
		};

		$data = [
			// $trc($pattern),
			$trc($subject, 100),
			// mb_detect_encoding($usubject),
			utf8_decode($usubject),
			// json_encode($subject. " ". $usubject),
			// $res($pgm, $rpgm),
			// 'preg_match: ' . $res($pg, $rpg),
			// $res($spg, $rspg),
			// 'mbregex: '. $res($mb, $rmb)
		];

		echo "\n".implode(" >> ", $data)."\n";
		// echo "\n".str_repeat('-', 80)."\n";
		// dump('Matching', ['pattern' => $pattern, 'subject' => $subject, 'result' => $result, 'matches' => $matches]);
		// echo "\n";
	}


	public function patternSubject()
	{
		return [
			// ['^FOO\s+bar', 'Foo Bar'],
			// ['^FOO\s+bar', 'foo BAR'],
			// ['^FOO\s+bar', 'FOO bar'],
			['^ ^ FOO\s*bar', 'FOO bar'],
			['[\x{0600}-\x{06FF}]', 'بيتر هو صبي.'],
			['[\x{0590}-\x{05FF}]', 'פיטר הוא ילד.'],
			// ["[بوه]+", 'بيتر هو صبي.'],
			["(הוא\s)+", 'פיטר הואילדהוא.הוא .'],
			["(ऑ-ऑ)+", 'ђ ऍ ऑ-ऑ غ بيتر ऑ љ ऑ-ऑ ღǼ'],
			["(\sऑ-ऑ\s)+", 'ђ ऍ ऑ-ऑ غ بيتر ऑ љ ऑ-ऑ ღǼ ऑ-ऑऑ-ऑ'],
			// ["[هوהוא]+", 'ђ ऍ ऑ-ऑ غ بيتر ऑ љهوऑ-ऑ הואღǼ ऑ-ऑऑ-ऑ'],
		];
	}


	/**
	 * @dataProvider patternSubject()
	 */
	public function _testPregMatchAll($pattern, $subject, $flags = 0, $offset = 0)
	{
		$matches = [];
		for ($i=0; $i < 500000; $i++) {
			$result = preg_match_all( Regex::wrap($pattern), $subject, $matches);
		}

		echo "\n".str_repeat('-', 80)."\n";
		dump('Matching', ['pattern' => $pattern, 'subject' => $subject, 'result' => $result, 'matches' => $matches]);
		echo "\n";
	}

	public function pregMatchProvider()
	{
		return [

		];
	}

}