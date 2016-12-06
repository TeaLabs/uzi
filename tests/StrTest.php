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
	public function testCreate($value, $encoding = false)
	{
		$result = Str::create($value, $encoding);
		$this->assertStr($result);
		$this->assertEquals( (string) $value, $result);
	}

	/**
	 * @dataProvider createStrExceptionProvider()
	 * @expectedException TypeError
	 */
	public function testCreateException($value, $encoding = false)
	{
		$result = Str::create($value, $encoding);
		$this->fail('Expecting exception when the Str instance '.
			'from a value that cannot be cast to string.');
	}


	/**
	 * @dataProvider asciiProvider()
	 */
	public function testAscii($expected, $value, $removeUnsupported = true)
	{
		$str = Str::create($value);
		$result = $str->ascii($removeUnsupported);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($value, $str);
	}


	/**
	 * @dataProvider beginProvider()
	 */
	public function testBegin($expected, $value, $substring = null, $trim = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->begin($substring, $trim);
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
	 * @dataProvider containsProvider()
	 */
	public function testContains($expected, $haystack, $needles, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($haystack, $encoding);
		$result = $str->contains($needles, $caseSensitive);
		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($haystack, $str);
	}

	/**
	 * @dataProvider containsAnyProvider()
	 */
	public function testContainsAny($expected, $haystack, $needles,	$caseSensitive = true, $encoding = null)
	{
		$str = Str::create($haystack, $encoding);
		$result = $str->containsAny($needles, $caseSensitive);
		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($haystack, $str);
	}

	/**
	 * @dataProvider containsErrorProvider()
	 * @expectedException TypeError
	 */
	public function testContainsError($haystack, $needles, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($haystack, $encoding);
		$result = $str->contains($needles, $caseSensitive);
		$this->fail('Expecting error when the needles argument can\'t '.
			'be cast to string and is not Traversable.');
	}


	/**
	 * @dataProvider endsWithProvider()
	 */
	public function testEndsWith($expected, $value, $needles, $caseSensitive = true, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->endsWith($needles, $caseSensitive);
		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
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
		$this->assertEquals($str, $value);
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
	 * @dataProvider trimProvider()
	 */
	public function testTrim($expected, $value, $chars = null, $wholeStr = false, $limit = -1, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->trim($chars, $wholeStr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}

	/**
	 * @dataProvider trimLeftProvider()
	 */
	public function testTrimLeft($expected, $value, $chars = null, $wholeStr = false, $limit = -1, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->trimLeft($chars, $wholeStr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}

	/**
	 * @dataProvider trimLeftProvider()
	 */
	public function testLtrim($expected, $value, $chars = null, $wholeStr = false, $limit = -1, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->ltrim($chars, $wholeStr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}

	/**
	 * @dataProvider trimRightProvider()
	 */
	public function testTrimRight($expected, $value, $chars = null, $wholeStr = false, $limit = -1, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->trimRight($chars, $wholeStr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}

	/**
	 * @dataProvider trimRightProvider()
	 */
	public function testRtrim($expected, $value, $chars = null, $wholeStr = false, $limit = -1, $encoding = null)
	{
		$str = Str::create($value, $encoding);
		$result = $str->rtrim($chars, $wholeStr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
		$this->assertEquals($str, $value);
	}


	public function testRegexReplace()
	{
		# code...
	}

	public function testReplace()
	{
		# code...
	}
}