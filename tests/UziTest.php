<?php
namespace Tea\Tests\Uzi;

use Tea\Uzi\Uzi;

class UziTest extends TestCase
{
	/**
	 * @dataProvider canStrCastProvider()
	 */
	public function testCanCast($expected, $value)
	{
		$result = Uzi::canCast($value);

		$this->assertInternalType('boolean', $result);
		$this->assertEquals($expected, $result);

		if($result){
			$this->assertEquals( $value, (string) $value );
		}

	}
}