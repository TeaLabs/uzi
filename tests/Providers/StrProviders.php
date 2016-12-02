<?php
namespace Tea\Tests\Uzi\Providers;

use Tea\Uzi\Str;
use Tea\Tests\Uzi\Specimens\Object;
use Tea\Tests\Uzi\Specimens\Traversable;
use Tea\Tests\Uzi\Specimens\HasToString;

trait StrProviders
{

	public function asciiProvider()
	{
		return [
			['foo bar', 'fòô bàř'],
			[' TEST ', ' ŤÉŚŢ '],
			['f = z = 3', 'φ = ź = 3'],
			['perevirka', 'перевірка'],
			['lysaya gora', 'лысая гора'],
			['shchuka', 'щука'],
			['', '漢字'],
			['xin chao the gioi', 'xin chào thế giới'],
			['XIN CHAO THE GIOI', 'XIN CHÀO THẾ GIỚI'],
			['dam phat chet luon', 'đấm phát chết luôn'],
			[' ', ' '], // no-break space (U+00A0)
			['           ', '           '], // spaces U+2000 to U+200A
			[' ', ' '], // narrow no-break space (U+202F)
			[' ', ' '], // medium mathematical space (U+205F)
			[' ', '　'], // ideographic space (U+3000)
			['', '𐍉'], // some uncommon, unsupported character (U+10349)
			['𐍉', '𐍉', false],
		];
	}

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


	public function camelProvider()
	{
		return [
			['camelCase', 'CamelCase'],
			['camelCase', 'Camel-Case'],
			['camelCase', 'camel case'],
			['camelCase', 'camel -case'],
			['camelCase', 'camel - case'],
			['camelCase', 'camel_case'],
			['camelCTest', 'camel c test'],
			['stringWith1Number', 'string_with1number'],
			['stringWith22Numbers', 'string-with-2-2 numbers'],
			['1Camel2Case', '1camel2case'],
			['camelΣase', 'camel σase', 'UTF-8'],
			['στανιλCase', 'Στανιλ case', 'UTF-8'],
			['σamelCase', 'σamel  Case', 'UTF-8']
		];
	}

	public function compactProvider()
	{
		return [
			['foo + bar', '  foo   +   bar   '],
			["foo bar foo bar", "    \nfoo    bar   \nfoo   bar    \n "],
			['Ο συγγραφέας', '   Ο     συγγραφέας  ', ' ', 'UTF-8'],
			['', ' ', ' ', 'UTF-8'],
			['x', '          ', 'x', 'UTF-8']
		];
	}

	public function containsProvider()
	{
		return [
			// Single needle
			// 1. Pain strings
			[true, 'Str contains foo bar', 'foo bar'],
			[true, '12398!@(*%!@# @!%#*&^%',  ' @!%#*&^%'],
			[true, 'Ο συγγραφέας είπε', 'συγγραφέας', true,'UTF-8'],
			[true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'å´¥©', true, 'UTF-8'],
			[true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'å˚ ∆', true, 'UTF-8'],
			[true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'øœ¬', true, 'UTF-8'],
			[false, 'Str contains foo bar', 'Foo bar'],
			[false, 'Str contains foo bar', 'foobar'],
			[false, 'Str contains foo bar', 'foo bar '],
			[false, 'Ο συγγραφέας είπε', '  συγγραφέας ', true, 'UTF-8'],
			[false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', ' ßå˚', true, 'UTF-8'],
			[true, 'Str contains foo bar', 'Foo bar', false],
			[true, '12398!@(*%!@# @!%#*&^%',  ' @!%#*&^%', false],
			[true, 'Ο συγγραφέας είπε', 'ΣΥΓΓΡΑΦΈΑΣ', false, 'UTF-8'],
			[true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'Å´¥©', false, 'UTF-8'],
			[true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'Å˚ ∆', false, 'UTF-8'],
			[true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'ØŒ¬', false, 'UTF-8'],
			[false, 'Str contains foo bar', 'foobar', false],
			[false, 'Str contains foo bar', 'foo bar ', false],
			[false, 'Ο συγγραφέας είπε', '  συγγραφέας ', false, 'UTF-8'],
			[false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', ' ßÅ˚', false, 'UTF-8'],

			// 2. Str objects.
			[true, 'Str contains foo bar', new Str('foo bar')],
			[true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', new Str('øœ¬', 'UTF-8'), true, 'UTF-8'],
			[false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', new Str(' ßå˚', 'UTF-8'), true, 'UTF-8'],

			// 3. Objects implementing __toString()
			[true, 'Str contains foo bar', new HasToString('foo bar')],
			[true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', new HasToString('øœ¬', 'UTF-8'), true, 'UTF-8'],
			[false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', new HasToString(' ßå˚', 'UTF-8'), true, 'UTF-8'],

			// Multiple needles
			// 1. Arrays
			array(false, 'Str contains foo bar', array()),
			array(true, 'Str contains foo bar', array('foo', 'bar')),
			array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*', '&^%')),
			array(true, 'Ο συγγραφέας είπε', array('συγγρ', 'αφέας'), 'UTF-8'),
			array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å´¥', '©'), true, 'UTF-8'),
			array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å˚ ', '∆'), true, 'UTF-8'),
			array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('øœ', '¬'), true, 'UTF-8'),
			array(false, 'Str contains foo bar', array('Foo', 'Bar')),
			array(false, 'Str contains foo bar', array('foobar', 'bar ')),
			array(false, 'Str contains foo bar', array('foo bar ', '  foo')),
			array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', '  συγγραφ '), true, 'UTF-8'),
			array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßå˚', ' ß '), true, 'UTF-8'),
			array(true, 'Str contains foo bar', array('Foo bar', 'bar'), false),
			array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*&^%', '*&^%'), false),
			array(true, 'Ο συγγραφέας είπε', array('ΣΥΓΓΡΑΦΈΑΣ', 'ΑΦΈΑ'), false, 'UTF-8'),
			array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å´¥©', '¥©'), false, 'UTF-8'),
			array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å˚ ∆', ' ∆'), false, 'UTF-8'),
			array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('ØŒ¬', 'Œ'), false, 'UTF-8'),
			array(false, 'Str contains foo bar', array('foobar', 'none'), false),
			array(false, 'Str contains foo bar', array('foo bar ', ' ba '), false),
			array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', ' ραφέ '), false, 'UTF-8'),
			array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßÅ˚', ' Å˚ '), false, 'UTF-8'),

			// 2 .Traversable Objects
			array(true, 'Str contains foo bar', new Traversable(array('foo', 'bar')) ),
			array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', new Traversable(array('å´¥', '©')), true, 'UTF-8'),
			array(false, 'Ο συγγραφέας είπε', new Traversable(array('  συγγραφέας ', '  συγγραφ ')), true, 'UTF-8'),
			array(false, 'Str contains foo bar', new Traversable(array('foo bar ', ' ba ')), false),
		];
	}


	public function containsAnyProvider()
	{
		// Converts single needles to array
		return array_map(function ($array) {
			if(!is_array($array[2]) && !($array[2] instanceof Traversable))
				$array[2] = array($array[2]);

			return $array;
		}, $this->containsProvider());

	}

	public function containsErrorProvider()
	{
		return [
			['foo bar', new Object('foo')]
		];

	}

	public function endsWithProvider()
	{
		return [

			// Single needle
			// 1. Pain strings
			array(true, 'foo bars', 'o bars'),
			array(true, 'FOO bars', 'o bars', false),
			array(true, 'FOO bars', 'o BARs', false),
			array(true, 'FÒÔ bàřs', 'ô bàřs', false, 'UTF-8'),
			array(true, 'fòô bàřs', 'ô BÀŘs', false, 'UTF-8'),
			array(false, 'foo bar', 'foo'),
			array(false, 'foo bar', 'foo bars'),
			array(false, 'FOO bar', 'foo bars'),
			array(false, 'FOO bars', 'foo BARS'),
			array(false, 'FÒÔ bàřs', 'fòô bàřs', true, 'UTF-8'),
			array(false, 'fòô bàřs', 'BÀŘS', true, 'UTF-8'),
			// 2. Str objects
			array(true, 'foo bars', new Str('o bars')),
			array(true, 'FOO bars', new Str('o BARs'), false),
			array(true, 'FÒÔ bàřs', new Str('ô bàřs'), false, 'UTF-8'),
			array(true, 'FÒÔ BÀŘs', new Str('bàřs'), false, 'UTF-8'),
			array(false, 'foo bar', new Str('foo')),
			array(false, 'FOO bars', new Str('BARS')),
			array(false, 'FÒÔ BÀŘs', new Str('bàřs'), true, 'UTF-8'),
			// 3. Objects implementing __toString()
			array(true, 'foo bars', new HasToString('o bars')),
			array(true, 'FOO bars', new HasToString('o BARs'), false),
			array(true, 'FÒÔ bàřs', new HasToString('ô bàřs'), false, 'UTF-8'),
			array(true, 'FÒÔ BÀŘs', new HasToString('bàřs'), false, 'UTF-8'),
			array(false, 'foo bar', new HasToString('foo')),
			array(false, 'FOO bars', new HasToString('BARS')),
			array(false, 'FÒÔ BÀŘs', new HasToString('bàřs'), true, 'UTF-8'),
			// Multiple needles
			// 1. Arrays
			[true, 'foo bars', ['foo','o bars']],
			[true, 'FOO bars', ['o bars'], false],
			[true, 'FOO bars', ['b', 'o BARs'], false],
			[true, 'FÒÔ bàřs', ['bàř', 'ô bàřs'], false, 'UTF-8'],
			[true, 'fòô bàřs', ['ô BÀŘs'], false, 'UTF-8'],
			[false, 'foo bar', []],
			[false, 'foo bar', ['BAR', 'foo']],
			[false, 'foo bar', ['foo bars']],
			[false, 'FOO bar', ['foo bars']],
			[false, 'FOO bars', ['foo BARS']],
			[false, 'FÒÔ bàřs', ['fòô bàřs'], true, 'UTF-8'],
			[false, 'fòô bàřs', ['BÀŘS'], true, 'UTF-8'],
			[true, 'FÒÔ bàřs', ['bàř', 'ô bàřs'], false, 'UTF-8'],
			[true, 'fòô bàřs', ['ô BÀŘs'], false, 'UTF-8'],
			// 2 .Traversable Objects
			[false, 'FÒÔ bàřs', new Traversable('fòô bàřs'), true, 'UTF-8'],
			[false, 'fòô bàřs', new Traversable('BÀŘS'), true, 'UTF-8'],
			[true, 'FÒÔ bàřs', new Traversable(['bàř', 'ô bàřs']), false, 'UTF-8'],
			[true, 'fòô bàřs', new Traversable('ô BÀŘs'), false, 'UTF-8'],
		];
	}

	public function finishProvider()
	{
		return [
			['foo©', 'foo', '©'],
			['foo©', 'foo©©©', '©'],
			['foo~©©~', 'foo~©©', '~'],
			['/foo/bar/', '/foo/bar', '/'],
			['/foo/bar/', '/foo/bar', '/', false],
			['/foo/bar/', '/foo/bar//', '/'],
			['/foo/bar//', '/foo/bar//', '/', false],
			['xfooxbarxxx', 'xfooxbarxxxxx', 'xx'],
			['+-foo+bar++-', '+-foo+bar++-+-+-', '+-'],
		];
	}

	public function minifyProvider()
	{
		return [
			[' foo bar ', '   foo    bar   '],
			[" foo bar foo bar ", "  foo    bar   \n  foo   bar   "]
		];
	}


	public function trimProvider()
	{
		return [
			['foo   bar', '  foo   bar  '],
			[' foo   bar ', '  foo   bar  ', '', false, 1],
			['foo bar', 'xxfoo barx', 'x'],
			['xfoo x bar', 'xxxfoo x barx', 'x', false, 2],
			['fooxbarx', 'xxfooxbarxxx', 'xx', true],
			['xxfooxbarx', 'xxxxfooxbarxxx', 'xx', true, 1],
			['foo bar', "\n\t foo bar \n\t"],
			['fòô   bàř', '  fòô   bàř  '],
			[' foo bar ', "\n\t foo bar \n\t", "\n\t"],
			['fòô bàř', "\n\t fòô bàř \n\t", null, false, -1,'UTF-8'],
			['fòô', '  fòô  ', null, false, -1,'UTF-8']
		];
	}

	public function trimLeftProvider()
	{
		return [
			['foo   bar  ', '  foo   bar  '],
			[' foo   bar  ', '  foo   bar  ', '', false, 1],
			['foo barx', 'xxfoo barx', 'x'],
			['xfoo x barxx', 'xxxfoo x barxx', 'x', false, 2],
			['xfooxbarxx', 'xxxfooxbarxx', 'xx', true],
			['xxfooxbarx', 'xxxxfooxbarx', 'xx', true, 1],
			["foo bar \n\t", "\n\t foo bar \n\t"],
			['fòô   bàř  ', '  fòô   bàř  '],
			[" foo bar \n\t", "\n\t foo bar \n\t", "\n\t"],
			["fòô bàř \n\t", "\n\t fòô bàř \n\t", null, false, -1,'UTF-8'],
			['fòô  ', '  fòô  ', null, false, -1,'UTF-8']
		];
	}

	public function trimRightProvider()
	{
		return [
			['foo   bar', 'foo   bar  '],
			['  foo   bar ', '  foo   bar  ', '', false, 1],
			['xfoo bar', 'xfoo barxx', 'x'],
			['xfoo x barx', 'xfoo x barxxx', 'x', false, 2],
			['xfooxbar', 'xfooxbarxxxx', 'xx', true],
			['xxfooxbarxx', 'xxfooxbarxxxx', 'xx', true, 1],
			["\n\t foo bar", "\n\t foo bar \n\t"],
			['  fòô   bàř', '  fòô   bàř  '],
			["\n\t foo bar ", "\n\t foo bar \n\t", "\n\t"],
			["\n\t fòô bàř", "\n\t fòô bàř \n\t", null, false, -1,'UTF-8'],
			['  fòô', '  fòô  ', null, false, -1,'UTF-8']
		];
	}

}