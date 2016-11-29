<?php
namespace Tea\Tests\Uzi;

use Tea\Uzi\Regex;

class RegexTest extends TestCase
{
	use RegexTestProviders;

	/**
	 * @dataProvider wrapProvider()
	 */
	public function testWrap($expected, $regex, $delimiter = null, $modifiers = null, $bracketStyle = false)
	{
		for ($i=0; $i < 1; $i++) {
			$actual = Regex::wrap($regex, $delimiter, $modifiers, $bracketStyle);
		}

		$this->assertEquals($expected, $actual);
	}

}