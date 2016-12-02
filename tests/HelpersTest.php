<?php
namespace Tea\Tests\Uzi;

use Tea\Uzi as U;
use Tea\Uzi\Str;

class HelpersTest extends TestCase
{

	/**
	 * @dataProvider createStrProvider()
	 */
	public function testStr($value, $encoding = false)
	{
		$result = U\str($value, $encoding);
		$this->assertStr($result);
		$this->assertEquals( (string) $value, $result);
	}

	/**
	 * @dataProvider createStrExceptionProvider()
	 * @expectedException TypeError
	 */
	public function testStrException($value, $encoding = false)
	{
		$result = U\str($value, $encoding);
		$this->fail('Expecting exception when the str() function'.
			' is passed a value that cannot be cast to string.');
	}

	public function testMbstringLoaded()
	{
		$result = U\mbstring_loaded();
		$this->assertInternalType('boolean', $result);
		$this->assertEquals( function_exists('mb_strlen'), $result);
	}

	public function testMbstringLoadedStrict()
	{
		$result = U\mbstring_loaded(true);
		$this->assertInternalType('boolean', $result);
		$this->assertEquals( extension_loaded('mbstring'), $result);
	}


	/**
	 * @dataProvider canStrCastProvider()
	 */
	public function testCanStrCast($expected, $value)
	{
		$result = U\can_str_cast($value);

		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);

		if($result){
			$this->assertEquals( $value, (string) $value );
		}

	}

}