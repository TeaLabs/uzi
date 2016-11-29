<?php
namespace Tea\Tests\Uzi;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	/**
	 * Asserts that a variable is of a Str instance.
	 *
	 * @param mixed $actual
	 */
	public function assertStr($actual)
	{
		$this->assertInstanceOf('Tea\Uzi\Str', $actual);
	}

}