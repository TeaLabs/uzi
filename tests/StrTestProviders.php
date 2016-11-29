<?php
namespace Tea\Tests\Uzi;

trait StrTestProviders
{

	public function beginProvider()
	{
		return [
			['© foo', '©©© foo', '©'],
			['~©©~foo', '©©~foo', '~'],
			['/foo/bar/', 'foo/bar/', '/'],
			['/foo/bar/', 'foo/bar/', '/', false],
			['/foo/bar/', '//foo/bar/', '/'],
			['\/foo\/bar/', '\/\/foo\/bar/', '\/'],
			['/foo/bar/', '/foo/bar/', '/', false],
			['//foo/bar/', '//foo/bar/', '/', false],
			['xxxfooxbarx', 'xxxxxfooxbarx', 'xx'],
			['+-+foo+bar-', '+-+-+-+foo+bar-', '+-'],
		];
	}

	public function compactProvider()
	{
		return [
			['foo + bar', '  foo   +   bar   '],
			["\nfoo bar \nfoo bar \n", "    \nfoo    bar   \nfoo   bar    \n "]
		];
	}

	public function finishProvider()
	{
		return [
			['/foo/bar/', '/foo/bar', '/'],
			['/foo/bar/', '/foo/bar/', '/'],
			['xxfooxbarxxx', 'xxfooxbarxxxxx', 'xx']
		];
	}

	public function minifyProvider()
	{
		return [
			[' foo bar ', '   foo    bar   '],
			[" foo bar foo bar ", "  foo    bar   \n  foo   bar   "]
		];
	}


	public function stripProvider()
	{
		return [
			['foo   bar', '  foo   bar  '],
			[' foo   bar ', '  foo   bar  ', '', 1],
			['foo bar', 'xxfoo barx', 'x'],
			['xfoo x bar', 'xxxfoo x barx', 'x', 2],
			['fooxbarx', 'xxfooxbarxxx', 'xx'],
		];
	}

	public function stripLeftProvider()
	{
		return [
			['foo   bar  ', '  foo   bar  '],
			[' foo   bar  ', '  foo   bar  ', '', 1],
			['foo barx', 'xxfoo barx', 'x'],
			['xfoo x barx', 'xxxfoo x barx', 'x', 2],
			['xfooxbarxx', 'xxxfooxbarxx', 'xx'],
		];
	}

	public function stripRightProvider()
	{
		return [
			['  foo   bar', '  foo   bar  '],
			['  foo   bar ', '  foo   bar  ', '', 1],
			['xxfoo bar', 'xxfoo barx', 'x'],
			['xfoo x barx', 'xfoo x barxxx', 'x', 2],
			['xxfooxbarx', 'xxfooxbarxxxxx', 'xx'],
		];
	}

}