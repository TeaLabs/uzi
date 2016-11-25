<?php
namespace Tea\Uzi\Tests;

use Tea\Uzi\Str;

class StrTest extends TestCase
{
	public function testCreate()
	{
		$str = Str::create('st');
		$this->assertInstanceOf(Str::class, $str);
	}
}