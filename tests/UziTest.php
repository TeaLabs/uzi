<?php
namespace Tea\Tests\Uzi;

use Tea\Uzi\Uzi;

class UziTest extends TestCase
{
	public function testRandom()
	{
		$result = Uzi::random(25);
		$this->assertStr($result);
		$this->assertEquals(25, $result->length());
		$this->assertTrue($result->isAlphanumeric());
	}
}