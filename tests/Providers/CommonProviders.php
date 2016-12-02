<?php
namespace Tea\Tests\Uzi\Providers;

use Tea\Tests\Uzi\Specimens\Object;
use Tea\Tests\Uzi\Specimens\HasToString;

trait CommonProviders
{

	public function createStrProvider()
	{
		return [
			['foo'],
			[123],
			[13.44],
			[true],
			[false],
			[null],
			['foo', 'ASCII'],
			['fòô', 'UTF-8'],
			[new HasToString('foo')],
		];
	}

	public function createStrExceptionProvider()
	{
		return [
			[['foo', 'bar']],
			[new Object]
		];
	}

	public function canStrCastProvider()
	{
		return [
			[true, 'foo'],
			[true, 'fòô'],
			[true, 123],
			[true, 13.44],
			[true, true],
			[true, false],
			[true, null],
			[true, new HasToString('foo')],
			[false, ['foo']],
			[false, new Object],
		];
	}

}