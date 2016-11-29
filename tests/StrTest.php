<?php
namespace Tea\Tests\Uzi;

use Tea\Uzi\Str;


class StrTest extends TestCase
{
	use StrTestProviders;

	/**
	 * @dataProvider beginProvider()
	 */
	public function testBegin($expected, $str, $substring = null, $trim = true, $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->begin($substring, $trim);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider compactProvider()
	 */
	public function _testCompact($expected, $str, $delimiter = ' ', $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->compact($delimiter);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider finishProvider()
	 */
	public function _testFinish($expected, $str, $substr = null, $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->finish($substr);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider minifyProvider()
	 */
	public function _testMinify($expected, $str, $delimiter = ' ', $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->minify($delimiter);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider stripProvider()
	 */
	public function _testStrip($expected, $str, $substr = null, $limit = -1, $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->strip($substr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider stripLeftProvider()
	 */
	public function _testStripLeft($expected, $str, $substr = null, $limit = -1, $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->stripLeft($substr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider stripLeftProvider()
	 */
	public function _testLStrip($expected, $str, $substr = null, $limit = -1, $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->lstrip($substr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider stripRightProvider()
	 */
	public function _testStripRight($expected, $str, $substr = null, $limit = -1, $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->stripRight($substr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider stripRightProvider()
	 */
	public function _testRStrip($expected, $str, $substr = null, $limit = -1, $encoding = null)
	{
		$str = Str::create($str, $encoding);
		$result = $str->rstrip($substr, $limit);
		$this->assertStr($result);
		$this->assertEquals($expected, $result);
	}
}