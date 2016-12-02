<?php
namespace Tea\Tests\Uzi;

use Tea\Tests\Uzi\Providers\CommonProviders;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

	use CommonProviders;

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