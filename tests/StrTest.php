<?php
namespace Tea\Tests\Uzi;

use Tea\Uzi\Str;

use Tea\Tests\Uzi\Providers\StrProviders;

class StrTest extends TestCase
{
	use StrProviders;


	/**
	 * @dataProvider createStrProvider()
	 */
	public function _testCreate($value, $encoding = false)
	{
		$result = Str::create($value, $encoding);
		$this->assertStr($result);
		$this->assertEquals( (string) $value, $result);
	}


	/**
	 * @dataProvider beginProvider()
	 */
	public function testBegin($expected, $value, $substring = null, $strip = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->begin($substring, $strip);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider compactProvider()
	 */
	public function testCompact($expected, $value, $delimiter = ' ', $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->compact($delimiter);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}

	/**
	 * @dataProvider endsWithProvider()
	 */
	public function testEndsWith($expected, $value, $needles, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		for ($i=0; $i < 1; $i++) {
			$result = $str->endsWith($needles, $caseSensitive);
		}

		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider finishProvider()
	 */
	public function testFinish($expected, $value, $substr = null, $trim = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->finish($substr, $trim);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider isProvider()
	 */
	public function testIs($expected, $value, $search, $wildcards=true, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->is($search, $wildcards, $caseSensitive);
		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}

	/**
	 * @dataProvider isAnyProvider()
	 */
	public function testIsAny($expected, $value, $search, $wildcards=true, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->isAny($search, $wildcards, $caseSensitive);
		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}

	/**
	 * @dataProvider isAllProvider()
	 */
	public function testIsAll($expected, $value, $search, $wildcards=true, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->isAll($search, $wildcards, $caseSensitive);
		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}


	/**
	 * @dataProvider matchesProvider()
	 */
	public function testMatches($expected, $value, $pattern, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->matches($pattern, $caseSensitive);
		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}

	/**
	 * @dataProvider replaceProvider()
	 */
	public function testReplace($expected, $value, $search, $replacement, $limit = -1, $expectedCount = null, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->replace($search, $replacement, $count);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);

		if(!is_null($expectedCount)){
			$this->assertEquals($expectedCount, $count);
		}

		$this->assertEquals($value, $str);
	}

	/**
	 * @dataProvider replaceFirstProvider()
	 */
	public function testReplaceFirst($expected, $value, $search, $replace, $expectedCount = null, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		for ($i=0; $i < 1; $i++) {
			$count = 0;
			$result = $str->replaceFirst($search, $replace, $count);
		}

		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);

		if(!is_null($expectedCount))
			$this->assertEquals($expectedCount, $count);
	}

	/**
	 * @dataProvider replaceLastProvider()
	 */
	public function testReplaceLast($expected, $value, $search, $replace, $expectedCount = null, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		for ($i=0; $i < 1; $i++) {
			$count = 0;
			$result = $str->replaceLast($search, $replace, $count);
		}

		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);

		if(!is_null($expectedCount))
			$this->assertEquals($expectedCount, $count);
	}




	/**
	 * @dataProvider startsWithProvider()
	 */
	public function testStartsWith($expected, $value, $needles, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		for ($i=0; $i < 1; $i++) {
			$result = $str->startsWith($needles, $caseSensitive);
		}
		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider stripProvider()
	 */
	public function testStrip($expected, $value, $substring = null, $limit = -1, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->strip($substring, $limit, $caseSensitive);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}

	/**
	 * @dataProvider stripLeftProvider()
	 */
	public function testStripLeft($expected, $value, $substring = null, $limit = -1, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->stripLeft($substring, $limit, $caseSensitive);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}

	/**
	 * @dataProvider stripRightProvider()
	 */
	public function testStripRight($expected, $value, $substring = null, $limit = -1, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->stripRight($substring, $limit, $caseSensitive);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}

	/**
	 * @dataProvider wordsProvider()
	 */
	public function testWords($expected, $value, $limit = 100, $end = '', $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->words($limit, $end);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}

/***Case Conversion Tests***/

	/**
	 * @dataProvider lowerProvider()
	 */
	public function testLower($expected, $value, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->lower();
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider lcfirstProvider()
	 */
	public function testLCFirst($expected, $value, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->lcfirst();
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider titlecaseProvider()
	 */
	public function testTitlecase($expected, $value, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->titleCase();
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}

	/**
	 * @dataProvider titlecaseProvider()
	 */
	public function testUCWords($expected, $value, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->ucwords();
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider upperProvider()
	 */
	public function testUpper($expected, $value, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->upper();
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}

	/**
	 * @dataProvider ucfirstProvider()
	 */
	public function testUCFirst($expected, $value, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->ucfirst();
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider camelProvider()
	 */
	public function testCamel($expected, $value, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->camel();
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider slugifyProvider()
	 */
	public function testSlugify($expected, $value, $separator = '-', $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->slugify($separator);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider snakeProvider()
	 */
	public function testSnake($expected, $value, $delimiter = '_', $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->snake($delimiter);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider studlyProvider()
	 */
	public function testStudly($expected, $value, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->studly();
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}

	/**
	 * @dataProvider singularVsPluralWordsProvider()
	 */
	public function testPlural($singular, $plural)
	{
		$str = Str::create($singular);
		$result = $str->plural();
		$this->assertStr($result);
		$this->assertEquals($plural, $result);
		$this->assertEquals($singular, $str);
	}

	/**
	 * @dataProvider singularVsPluralWordsProvider()
	 */
	public function testSingular($singular, $plural)
	{
		$str = Str::create($plural);
		$result = $str->singular();
		$this->assertStr($result);
		$this->assertEquals($singular, $result);
		$this->assertEquals($plural, $str);
	}

}