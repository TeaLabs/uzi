<?php
namespace Tea\Tests\Uzi\Providers;

trait RegexProviders
{
	public function wrapProvider()
	{
		$re = '([a-zA-Z_][a-zA-Z0-9_-]*|)';
		$reb = "\\{$re}\\";
		return [
			[ "/{$re}/u", "{$re}"],
			[ "+{$re}+", "+{$re}+"],
			[ "/{$re}/im", "/{$re}/im"],
			[ "#{$re}#im", "{$re}", '#', 'im'],
			[ "#{$re}#im", "#{$re}#im", '#', 'im'],
			[ "~{$re}~iADJ", "~{$re}~iADJ"],
			[ "+{$re}+iADJ", "+{$re}+iADJ"],
			[ "%{$re}%iADJ", "%{$re}%iADJ"],
			[ "[{$re}]iADJ", "[{$re}]iADJ", null, null,true],
			[ "({$re})iADJ", "({$re})iADJ", null, null,true],
			[ "<{$re}>iADJ", "<{$re}>iADJ", null, null,true],
			[ "{{$re}}iADJ", "{{$re}}iADJ", null, null,true],
			[ "{{$reb}}u", "{$reb}", '{}', null,true],
			[ "{{$re}}u", "{$re}", '{}', null, ['<{[','>}]']],
			[ "<{$reb}>i", "$reb", '<>', 'i', true],
			[ "<{$re}>i", "$re", '<>', 'i', ['<{[','>}]']],
		];
	}
}