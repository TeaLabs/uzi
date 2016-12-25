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
		$data = array_map(function ($array) {
			if(!is_array($array[2]) && !($array[2] instanceof Traversable))
				$array[2] = array($array[2]);

			return $array;
		}, $this->containsProvider());


	}


	public function delimitProvider()
	{
		return array(
			array('test*case', 'testCase', '*'),
			array('test&case', 'Test-Case', '&'),
			array('test#case', 'test case', '#'),
			array('test**case', 'test -case', '**'),
			array('~!~test~!~case', '-test - case', '~!~'),
			array('test*case', 'test_case', '*'),
			array('test%c%test', '  test c test', '%'),
			array('test+u+case', 'TestUCase', '+'),
			array('test=c=c=test', 'TestCCTest', '='),
			array('string#>with1number', 'string_with1number', '#>'),
			array('1test2case', '1test2case', '*'),
			array('test ύα σase', 'test Σase', ' ύα ', 'UTF-8',),
			array('στανιλαcase', 'Στανιλ case', 'α', 'UTF-8',),
			array('σashΘcase', 'Σash  Case', 'Θ', 'UTF-8')
		);
	}

	public function endsWithProvider()
	{
		return [

			array(true, 'foo bars', 'o bars'),
			array(true, 'FOO bars', 'o bars', false),
			array(true, 'FOO bars', 'o BARs', false),
			array(true, 'FÒÔ bàřs', 'ô bàřs', false, 'UTF-8'),
			array(true, 'FÒÔ bàřs', Str::create('ô bàřs', 'UTF-8'), false, 'UTF-8'),
			array(true, 'fòô bàřs', 'ô BÀŘs', false, 'UTF-8'),
			array(true, 'fòô bàřs', Str::create('ô BÀŘs', 'UTF-8'), false, 'UTF-8'),
			array(false, 'foo bar', 'foo'),
			array(false, 'foo bar', 'foo bars'),
			array(false, 'FOO bar', 'foo bars'),
			array(false, 'FOO bars', 'foo BARS'),
			array(false, 'FÒÔ bàřs', 'fòô bàřs', true, 'UTF-8'),
			array(false, 'FÒÔ bàřs', Str::create('fòô bàřs', 'UTF-8'), true, 'UTF-8'),
			array(false, 'fòô bàřs', 'fòô BÀŘS', true, 'UTF-8'),
			[true, 'foo bars', ['barz', 'barx','xxx', 'foo', 'foo','o bars']],
			[true, 'FOO bars', ['barz', 'barx','xxx', 'foo', 'o bars'], false],
			[true, 'FOO bars', ['barz', 'barx','xxx', 'foo', 'b', 'o BARs'], false],
			[true, 'FÒÔ bàřs', ['barz', 'barx','xxx', 'foo', 'bàř', 'ô bàřs', 'dfsd'], false, 'UTF-8'],
			[true, 'fòô bàřs', ['barz', 'barx','xxx', 'foo', 'ô BÀŘs', 'dwedd'], false, 'UTF-8'],
			[true, 'fòô bàřs', ['barz', 'barx','xxx', 'foo', 'ô BÀŘs'], false, 'UTF-8'],
			[false, 'fòô bàřs', ['barz', 'barx','xxx', 'foo', 'ô BÀŘs'], true, 'UTF-8'],
			[false, 'foo bar', ['barz', 'barx','xxx', 'foo']],
			[false, 'foo bar', ['barz', 'barx','xxx', 'foo', 'BAR', 'foo']],
			[false, 'foo bar', ['barz', 'barx','xxx', 'foo', 'foo bars']],
			[false, 'FOO bar', ['barz', 'barx','xxx', 'foo', 'foo bars']],
			[false, 'FOO bars', ['barz', 'barx','xxx', 'foo', 'foo BARS']],
			[true, 'FÒÔ bàřs', ['barz', 'barx','xxx', 'foo', 'fòô bàřs'], false, 'UTF-8'],
			[false, 'FÒÔ bàřs', ['barz', 'barx','xxx', 'foo', 'fòô bàřs'], true, 'UTF-8'],
			[false, 'fòô bàřs', ['barz', 'barx','xxx', 'foo', 'BÀŘS'], true, 'UTF-8'],
			[true, 'FÒÔ bàřs', ['barz', 'barx','xxx', 'foo', 'bàř', 'ô bàřs'], false, 'UTF-8'],
			// 2 .Traversable Objects
			[false, 'FÒÔ bàřs', new Traversable('fòô bàřs'), true, 'UTF-8'],
			[false, 'fòô bàřs', new Traversable('BÀŘS'), true, 'UTF-8'],
			[true, 'FÒÔ bàřs', new Traversable(['bàř', 'ô bàřs']), false, 'UTF-8'],
			[true, 'fòô bàřs', new Traversable('ô BÀŘs'), false, 'UTF-8'],
		];
	}


	public function startsWithProvider()
	{
		return array(
			array(true, 'foo bars', 'foo bar'),
			array(true, 'FOO bars', 'foo bar', false),
			array(true, 'FOO bars', 'foo BAR', false),
			array(true, 'FÒÔ bàřs', 'fòô bàř', false, 'UTF-8'),
			array(true, 'fòô bàřs', 'fòô BÀŘ', false, 'UTF-8'),
			array(false, 'foo bar', 'bar'),
			array(false, 'foo bar', 'foo bars'),
			array(false, 'FOO bar', 'foo bars'),
			array(false, 'FOO bars', 'foo BAR'),
			array(false, 'FÒÔ bàřs', 'fòô bàř', true, 'UTF-8'),
			array(false, 'fòô bàřs', 'fòô BÀŘ', true, 'UTF-8'),


			array(true, 'foo bars', ['barz', 'barx', 'xxx', 'foo bar', 'foo']),
			array(true, 'FOO bars', ['barz', 'barx', 'xxx', 'foo BAR', 'fss'], false),
			array(true, 'FÒÔ bàřs', ['barz', 'barx', 'xxx', 'fòô bàř'], false, 'UTF-8'),
			array(true, 'fòô bàřs', ['fòô BÀŘ', 'sfsfs', 'sfsfsf'], false, 'UTF-8'),

			array(false, 'FOO bar', ['barz', 'barx', 'xxx', 'foo bars']),
			array(false, 'FOO bars', ['barz', 'barx', 'xxx', 'foo BAR']),
			array(false, 'FÒÔ bàřs', ['barz', 'barx', 'xxx', 'fòô bàř'], true, 'UTF-8'),
			array(false, 'fòô bàřs', ['barz', 'barx', 'xxx', 'fòô BÀŘ'], true, 'UTF-8'),

			);
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

	public function joinProvider()
	{
		return [
			['a/b/c', '/', ['a', 'b', 'c']],
			['a//b//c', '/', ['a', '/b/', 'c'], false],
			['a,,b,c,', ',', ['a', '','b', 'c', ''], false],
			[',a,,b,c,', ',', [ '', 'a', '','b', 'c', ''], false],
			[',a,,b,c,', ',', [ null, 'a', '','b', 'c', ''], false],
			[',,a,,b,c,', ',', [ ',', 'a', '','b', 'c', ''], false],
			['abc', '', [ '', 'a', '','b', 'c', ''], false],
			[' a  b c ', ' ', [ '', 'a', '','b', 'c', ''], false],
			['a/b/c', '/', ['a/', '/b/', '/c'], true],
			['/a/b/c/', '/', ['/a/', '/b', '//c/'], true],
			['//a/b/c//', '/', ['//a//', '/b', '//c//'], true],
			['/a/b/c/', '/', ['/', 'a', 'b', 'c', '/'], true],
			['/a/b/c/', '/', ['', 'a', 'b', 'c', ''], true],
			['/a/b/c/', '/', ['', '/a', 'b', 'c/', ''], true],
			['a/b/c', '/', ['a', '', 'b', 'c'], true],
			['a/b/c', '/', ['a', '/', '', 'b', 'c'], true],
		];
	}

	public function isProvider()
	{
		return [
			[true, '/', '/'],
			[false, '/', ' /'],
			[false, '/', '/a'],
			[true, 'foo/*', 'foo/bar/baz'],
			[false, 'foo/*', 'foo/bar/baz', false],
			[true, '*/foo', 'blah/baz/foo'],
			[true, 'foo/*', new HasToString('foo/bar/baz')],
			[false, '/Foo/Bar', '/foo/bar'],
			[true, '/Foo/Bar', '/foo/bar', false, false],
		];
	}

	public function isAnyProvider()
	{
		// Converts single needles to array
		$isData = array_map(function ($array) {
			$array[2] = array($array[2]);
			return $array;
		}, $this->isProvider());

		$data = [
			[true, 'foo/*', ['/', 'foo/']],
			[false, 'foo/*', ['/', 'foo/'], false],
			[true, 'foo/*', ['/', 'foobar', 'foo/bar']],
			[false, 'foo/bar/*', ['/', 'foobar', 'foo/bar']],

			[true, 'foo/*', new Traversable(['/', 'foo/'])],
			[true, 'foo/*', new Traversable(['/', 'foobar', 'foo/bar'])],
			[false, 'foo/bar/*', new Traversable(['/', 'foobar', 'foo/bar'])],
		];

		return array_merge($isData, $data);
	}


	public function isAllProvider()
	{
				// Converts single needles to array
		$isData = array_map(function ($array) {
			$array[2] = array($array[2]);
			return $array;
		}, $this->isProvider());

		$data = [
			[true, 'foo/*', ['foo/bar', 'foo/', 'foo/baz/12']],
			[true, 'foo*', ['foo', 'foobar', 'foo/bar']],
			[true, 'foo/bar/*', ['foo/bar/*', 'foo/bar/', 'foo/bar/barz']],

			[false, 'foo/*', ['foo/bar', 'foo/', 'foo/baz/12', 'foo']],
			[false, 'foo*', ['foo', 'foobar', 'fo', 'foo/bar']],
			[false, 'foo/bar/*', ['foo/bar/*', 'foo/bar', 'foo/bar/barz']],

			[true, 'foo/*', new Traversable(['foo/bar', 'foo/', 'foo/baz/12'])],
			[false, 'foo/*', new Traversable(['foo/bar', 'foo/', 'foo', 'foo/baz/12'])]
		];

		return array_merge($isData, $data);
	}


	public function matchesProvider()
	{
		return [
			[true, '/', '\/'],
			[false, '/', '\/a'],
			[true, 'foo/bar/baz', 'foo\/.*'],
			[true, 'blah/baz/foo', '.*\/foo'],
			[true, 'foo/bar/baz', new HasToString('baz$')],
			[false, '/Foo/Bar', '\/foo\/bar'],
			[true, '/Foo/Bar', '\/foo\/bar', false],
		];
	}


	public function replaceProvider()
	{
		return array(
			array('', '', '', ''),
			array('foo', '\s', '\s', 'foo'),
			array('foo bar', 'foo bar', '', ''),
			array('foo bar', 'foo bar', 'f(o)o', '$1'),
			array('\1 bar', 'foo bar', 'foo', '\1'),
			array('$5 bar', 'foo bar', 'foo', '$5'),
			array('bar', 'foo bar', 'foo ', ''),
			array('far bar', 'foo bar', 'foo', 'far'),
			array('bar bar', 'foo bar foo bar', 'foo ', ''),
			array('', '', '', ''),
			array('fòô', '\s', '\s', 'fòô'),
			array('fòô bàř', 'fòô bàř', '', ''),
			array('bàř', 'fòô bàř', 'fòô ', ''),
			array('far bàř', 'fòô bàř', 'fòô', 'far'),
			array('bàř bàř', 'fòô bàř fòô bàř', 'fòô ', ''),
			array('foofoo foofoo', 'foobar foobar', ['foo', 'bar'], ['bar', 'foo']),
		);
	}

	public function replaceFirstProvider()
	{
		$data = [
			['whobar foobar', 'foobar foobar', 'foo', 'who', 1],
			['(?:fooqux foobar', '(?:foobar foobar', 'bar', 'qux', 1],
			['fooqux foobar', 'foobar foobar', 'bar', 'qux', 1],
			['foo/qux? foo/bar?', 'foo/bar? foo/bar?', 'bar?', 'qux?', 1],
			['foo/bar foo/qux?', 'foo/bar foo/bar?', 'bar?', 'qux?', 1],
			['foo foobar', 'foobar foobar', 'bar', '', 1],
			['farbàř fòôbàř', 'fòôbàř fòôbàř', 'fòô', 'far', 1],
			['foobar foobar', 'foobar foobar', 'xxx', 'yyy', 0],
			['whobar boobar', 'foobar foobar', 'foo', ['who', 'boo'], 2],
			['whobar boobar', 'foobar foobar', 'foo', ['who', 'boo', 'zoo', 'doo'], 2],
			['foobar boobar foofoo', 'foobar foobar foofoo', 'foo', ['foo', 'boo'], 2],
			['foobar boobar zoobar', 'foobar foobar foofoofoo', 'foo', ['foo', 'boo', 'zoo', 'bar', ''], 5],
			['foobar boobar zoobar', 'foobar foobar foofoofoo', 'foo', ['foo', 'boo', 'zoo', '', 'bar'], 5],
			['foobar boobar foobar', 'foobar foobar foofoofoo', 'foo', ['foo', 'boo', 'foo', 'bar', ''], 5],
			['farbàř zòôbàř zòôbàř', 'fòôbàř fòôfòô fòôfòôbàř', 'fòô', ['far', 'zòô', 'bàř', '', 'zòô'], 5],
			['foobar foobar', 'foobar foobar', ['foo','bar'], ['bar', 'foo'], 2],
			['foobar foobar', 'foobar foobar', ['foo','bar' => 'bar'], ['bar', 'foo'], 2],
			['foofoo barbar', 'foobar foobar', ['foo','bar' => 'bar'], ['foo' => ['bar', 'bar'], ['foo', 'foo']], 4],
			['zoozar farboo', 'foobar foobar', ['foo','bar' => 'bar'], ['foo' => ['zoo', 'far'], ['zar', 'boo']], 4],
			['òà ôbàř bààřzbàřz', 'foobar foobar barbar', ['foo', 'bar'],[['ò', 'ô'], ['à', 'bàř', 'bààřz', 'bàřz' ]], 6],
			['who boobar', 'foobar foobar', ['foo', 'bar' => 'bar'], [['who', 'boo']], 3],
		];

		return $data;
	}

	public function replaceLastProvider()
	{
		$data = [
			['foobar whobar', 'foobar foobar', 'foo', 'who', 1],
			['(?:foobar fooqux', '(?:foobar foobar', 'bar', 'qux', 1],
			['foobar fooqux', 'foobar foobar', 'bar', 'qux', 1],
			['foo/bar? foo/qux?', 'foo/bar? foo/bar?', 'bar?', 'qux?', 1],
			['foo/qux? foo/bar', 'foo/bar? foo/bar', 'bar?', 'qux?', 1],
			['foobar foo', 'foobar foobar', 'bar', '', 1],
			['fòôbàř farbàř', 'fòôbàř fòôbàř', 'fòô', 'far', 1],
			['foobar foobar', 'foobar foobar', 'xxx', 'yyy', 0],

			['boobar whobar', 'foobar foobar', 'foo', ['who', 'boo'], 2],
			['boobar whobar', 'foobar foobar', 'foo', ['who', 'boo', 'zoo', 'doo'], 2],
			['foobar foobar boofoo', 'foobar foobar foofoo', 'foo', ['foo', 'boo'], 2],
			['bar barbar zooboofoo', 'foobar foobar foofoofoo', 'foo', ['foo', 'boo', 'zoo', 'bar', ''], 5],
			['barbar bar zooboofoo', 'foobar foobar foofoofoo', 'foo', ['foo', 'boo', 'zoo', '', 'bar'], 5],
			['bar barbar fooboofoo', 'foobar foobar foofoofoo', 'foo', ['foo', 'boo', 'foo', 'bar', ''], 5],
			['zòôbàř bàř zòôfarbàř', 'fòôbàř fòôfòô fòôfòôbàř', 'fòô', ['far', 'zòô', 'bàř', '', 'zòô'], 5],

			['foobar foobar', 'foobar foobar', ['bar','foo'], ['foo', 'bar'], 2],
			['foobar foobar', 'foobar foobar', ['bar' => 'bar','foo'], ['foo', 'bar'], 2],
			['barbar foofoo', 'foobar foobar', ['foo','bar' => 'bar'], ['foo' => ['bar', 'bar'], ['foo', 'foo']], 4],
			['farboo zoozar', 'foobar foobar', ['foo', 'bar'], [['zoo', 'far'], ['zar', 'boo']], 4],
			['ôbàřz òbààřz bàřà', 'foobar foobar barbar', ['foo', 'bar'],[['ò', 'ô'], ['à', 'bàř', 'bààřz', 'bàřz' ]], 6],
			['boobar who', 'foobar foobar', ['foo', 'bar'], [['who', 'boo']], 3],
		];

		return $data;
	}

	public function stripProvider()
	{
		return [
			['foo   bar', '  foo   bar  '],
			[' foo   bar ', '  foo   bar  ', null, 1],
			['foo bar', 'xxxxxfoo barxxx', 'x'],
			['xfoo x bar', 'xxxfoo x barx', 'x', 2],
			['fooxbarx', 'xxfooxbarxxx', 'xx'],
			['xxfooxbarx', 'xxxxfooxbarxxx', 'xx', 1],
			['foo bar', "\n\t foo bar \n\t"],
			['fòô   bàř', '  fòô   bàř  '],
			[" foo bar \t\n", "\n\t foo bar \t\n\n\t", "\n\t"],
			['fòô bàř', "\n\t fòô bàř \n\t"],
			['fòô', '  fòô  '],
			['foo bar', 'xxxXxfoo barXxx', 'x', -1, false],
			['Xxfoo barX', 'xxxXxfoo barXxx', 'x'],
			array('-foo-bar-cba', 'abc-foo-bar-cba', 'abc'),
			array('ac-foo-bar-', 'abcabcac-foo-bar-abcabc', 'abc'),
		];
	}

	public function stripLeftProvider()
	{
		return [
			['foo   bar  ', '  foo   bar  '],
			[' foo   bar  ', '  foo   bar  ', null, 1],
			['foo barxxx', 'xxxxxfoo barxxx', 'x'],
			['xfoo x barxx', 'xxxfoo x barxx', 'x', 2],
			['fooxbarxx', 'xxxxfooxbarxx', 'xx'],
			['xxfooxbarx', 'xxxxfooxbarx', 'xx', 1],
			["foo bar \n\t", "\n\t foo bar \n\t"],
			['fòô   bàř  ', '  fòô   bàř  '],
			["\t\n foo bar \n\t", "\n\t\t\n foo bar \n\t", "\n\t"],
			["fòô bàř \n\t", "\n\t fòô bàř \n\t"],
			['fòô  ', '  fòô  '],
			['foo barXx', 'xxxXxfoo barXx', 'x', -1, false],
			['Xxfoo barXx', 'xxxXxfoo barXx', 'x'],
			array('-foo-bar-abc', 'abc-foo-bar-abc', 'abc'),
			array('ac-foo-bar', 'abcabcac-foo-bar', 'abc'),
		];
	}


	public function stripRightProvider()
	{
		return [
			['  foo   bar', '  foo   bar  '],
			['  foo   bar ', '  foo   bar  ', null, 1],
			['xxfoo bar', 'xxfoo barxxxx', 'x'],
			['xfoo x barx', 'xfoo x barxxx', 'x', 2],
			['fooxbarx', 'fooxbarxxxxx', 'xx'],
			['fooxbarxx', 'fooxbarxxxx', 'xx', 1],
			["\n\t foo bar", "\n\t foo bar \n\t"],
			['  fòô   bàř', '  fòô   bàř  '],
			["\n\t foo bar \t\n", "\n\t foo bar \t\n\n\t", "\n\t"],
			["\n\t fòô bàř", "\n\t fòô bàř \n\t"],
			['  fòô', '  fòô  '],
			['foo bar', 'foo barxXx', 'x', -1, false],
			['foo barxX', 'foo barxXxx', 'x'],
			array('foo-bar-', 'foo-bar-abc', 'abc'),
			array('foo-bar-ac', 'foo-bar-acabcabc', 'abc'),
		];
	}



	public function trimProvider()
	{
		return array(
			array('foo   bar', '  foo   bar  '),
			array('foo bar', ' foo bar'),
			array('foo bar', 'foo bar '),
			array('foo bar', "\n\t foo bar \n\t"),
			array('fòô   bàř', '  fòô   bàř  '),
			array('fòô bàř', ' fòô bàř'),
			array('fòô bàř', 'fòô bàř '),
			array(' foo bar ', "\n\t foo bar \n\t", "\n\t"),
			array('fòô bàř', "\n\t fòô bàř \n\t"),
			array('fòô', ' fòô '), // narrow no-break space (U+202F)
			array('fòô', '  fòô  '), // medium mathematical space (U+205F)
			array('fòô', '           fòô'), // spaces U+2000 to U+200A
			array('fooxbar', 'xxfooxbarxxx', 'x'),
			array('fooxbarx', 'xxfooxbarxxx', 'x', 2),
			array('-foo-bar-', 'abc-foo-bar-cba', 'abc'),
			array('-foo-bar-', 'aBc-foo-bar-Cba', 'abc', -1, false),
			array('Bc-foo-bar-C', 'aBc-foo-bar-Cba', 'abc', -1, true),
			array('-foo-bar-', 'cabac-foo-bar-bacbac', 'abc'),
		);
	}


	public function trimLeftProvider()
	{
		return array(
			array('foo   bar  ', '  foo   bar  '),
			array('foo bar', ' foo bar'),
			array('foo bar ', 'foo bar '),
			array("foo bar \n\t", "\n\t foo bar \n\t"),
			array('fòô   bàř  ', '  fòô   bàř  '),
			array('fòô bàř', ' fòô bàř'),
			array('fòô bàř ', 'fòô bàř '),
			array('foo bar-', '--foo bar-', '-'),
			array('fòô bàř', 'òòfòô bàř', 'ò'),
			array("fòô bàř \n\t", "\n\t fòô bàř \n\t"),
			array('fòô ', ' fòô '), // narrow no-break space (U+202F)
			array('fòô  ', '  fòô  '), // medium mathematical space (U+205F)
			array('fòô', '           fòô'), // spaces U+2000 to U+200A
			array('-foo-bar', 'abc-foo-bar', 'abc'),
			array('-foo-bar', 'bcaCAB-foo-bar', 'abc', -1, false),
			array('CAB-foo-bar', 'bcaCAB-foo-bar', 'abc', -1, true),
		);
	}


	public function trimRightProvider()
	{
		return array(
			array('  foo   bar', '  foo   bar  '),
			array('foo bar', 'foo bar '),
			array(' foo bar', ' foo bar'),
			array("\n\t foo bar", "\n\t foo bar \n\t"),
			array('  fòô   bàř', '  fòô   bàř  '),
			array('fòô bàř', 'fòô bàř '),
			array(' fòô bàř', ' fòô bàř'),
			array('foo bar', 'foo bar--', '-'),
			array('fòô bàř', 'fòô bàřòò', 'ò', 'UTF-8'),
			array("\n\t fòô bàř", "\n\t fòô bàř \n\t", null, 'UTF-8'),
			array(' fòô', ' fòô ', null, 'UTF-8'), // narrow no-break space (U+202F)
			array('  fòô', '  fòô  ', null, 'UTF-8'), // medium mathematical space (U+205F)
			array('fòô', 'fòô           ', null, 'UTF-8'), // spaces U+2000 to U+200A
			array('foo-bar', 'foo-barccba', 'abc'),
			array('foo-bar-', 'foo-bar-CABbcacaa', 'abc', -1, false),
			array('foo-bar-CAB', 'foo-bar-CABcab', 'abc', -1, true),
		);
	}

	public function wordsProvider()
	{
		return array(
			array('Test foo bar', 'Test foo bar', 10),
			array('Test foo', 'Test foo bar', 2),
			array('Test foo>>>', 'Test foo bar', 2, '>>>'),
			array('Test...', 'Test foo bar', 1, '...'),
			array('Test fòô', 'Test fòô bàř', 2),
			array('Test...', 'Test fòô bàř', 1, '...'),
			array('Test fòô bàř', 'Test fòô bàř', 4, 'ϰϰ', 'UTF-8'),
			array('Test fòôϰϰ', 'Test fòô bàř', 2, 'ϰϰ'),
			array('What are your plans today?', 'What are your plans today?', 5, '...'),
			array('What are your...', 'What are your plans today?', 3, '...'),
		);
	}


	public function lcfirstProvider()
	{
		return array(
			array('test', 'Test'),
			array('test', 'test'),
			array('1a', '1a'),
			array('σ test', 'Σ test', 'UTF-8'),
			array(' Σ test', ' Σ test', 'UTF-8')
		);
	}


	public function lowerProvider()
	{
		return array(
			array('foo bar', 'FOO BAR'),
			array(' foo_bar ', ' FOO_bar '),
			array('fòô bàř', 'FÒÔ BÀŘ', 'UTF-8'),
			array(' fòô_bàř ', ' FÒÔ_bàř ', 'UTF-8'),
			array('αυτοκίνητο', 'ΑΥΤΟΚΊΝΗΤΟ', 'UTF-8'),
		);
	}


	public function titlecaseProvider()
	{
		return array(
			array('Foo Bar', 'foo bar'),
			array(' Foo_Bar ', ' foo_bar '),
			array('Fòô Bàř', 'fòô bàř', 'UTF-8'),
			array(' Fòô_Bàř ', ' fòô_bàř ', 'UTF-8'),
			array('Αυτοκίνητο Αυτοκίνητο', 'αυτοκίνητο αυτοκίνητο', 'UTF-8'),
		);
	}

	public function upperProvider()
	{
		return array(
			array('FOO BAR', 'foo bar'),
			array(' FOO_BAR ', ' FOO_bar '),
			array('FÒÔ BÀŘ', 'fòô bàř', 'UTF-8'),
			array(' FÒÔ_BÀŘ ', ' FÒÔ_bàř ', 'UTF-8'),
			array('ΑΥΤΟΚΊΝΗΤΟ', 'αυτοκίνητο', 'UTF-8'),
		);
	}

	public function ucfirstProvider()
	{
		return array(
			array('Test', 'Test'),
			array('Test', 'test'),
			array('1a', '1a'),
			array('Σ test', 'σ test', 'UTF-8'),
			array(' σ test', ' σ test', 'UTF-8')
		);
	}


	public function camelProvider()
	{
		return array(
			array('camelCase', 'CamelCase'),
			array('camelCase', 'Camel-Case'),
			array('camelCase', 'camel case'),
			array('camelCase', 'camel -case'),
			array('camelCase', 'camel - case'),
			array('camelCase', 'camel_case'),
			array('camelCTest', 'camel c test'),
			array('stringWith1Number', 'string_with1number'),
			array('stringWith22Numbers', 'string-with-2-2 numbers'),
			array('dataRate', 'data_rate'),
			array('backgroundColor', 'background-color'),
			array('yesWeCan', 'yes_we_can'),
			array('mozSomething', '-moz-something'),
			array('carSpeed', '_car_speed_'),
			array('serveHTTP', 'ServeHTTP'),
			array('1Camel2Case', '1camel2case'),
			array('camelΣase', 'camel σase', 'UTF-8'),
			array('στανιλCase', 'Στανιλ case', 'UTF-8'),
			array('σamelCase', 'σamel  Case', 'UTF-8'),
		);
	}


	public function slugifyProvider()
	{
		return array(
			array('foo-bar', ' foo  bar '),
			array('foo-bar', 'foo -.-"-...bar'),
			array('another-foo-bar', 'another..& foo -.-"-...bar'),
			array('foo-dbar', " Foo d'Bar "),
			array('a-string-with-dashes', 'A string-with-dashes'),
			array('using-strings-like-foo-bar', 'Using strings like fòô bàř'),
			array('numbers-1234', 'numbers 1234'),
			array('perevirka-ryadka', 'перевірка рядка'),
			array('bukvar-s-bukvoy-y', 'букварь с буквой ы'),
			array('podekhal-k-podezdu-moego-doma', 'подъехал к подъезду моего дома'),
			array('foo:bar:baz', 'Foo bar baz', ':'),
			array('a_string_with_underscores', 'A_string with_underscores', '_'),
			array('a_string_with_dashes', 'A string-with-dashes', '_'),
			array('a\string\with\dashes', 'A string-with-dashes', '\\'),
			array('an_odd_string', '--   An odd__   string-_', '_')
		);
	}


	public function snakeProvider()
	{
		return array(
			array('test_case', 'testCase'),
			array('test_case', 'Test-Case'),
			array('test_case', 'test case'),
			array('test_case', 'test -case'),
			array('_test_case', '-test - case'),
			array('test_case', 'test_case'),
			array('test_c_test', '  test c test'),
			array('test_u_case', 'TestUCase'),
			array('test_c_c_test', 'TestCCTest'),
			array('test-c-c-test', 'TestCCTest', '-'),
			array('string_with1number', 'string_with1number'),
			array('string_with_2_2_numbers', 'String-with_2_2 numbers'),
			array('1test2case', '1test2case'),
			array('yes_we_can', 'yesWeCan'),
			array('test_σase', 'test Σase', '_', 'UTF-8'),
			array('στανιλ_case', 'Στανιλ case', '_','UTF-8'),
			array('σash_case', 'Σash  Case', '_','UTF-8')
		);
	}

	public function titleizeProvider()
	{
		$ignore = array('at', 'by', 'for', 'in', 'of', 'on', 'out', 'to', 'the');

		return array(
			array('Title Case', 'TITLE CASE'),
			array('Testing The Method', 'testing the method'),
			array('Testing the Method', 'testing the method', $ignore),
			array('I Like to Watch Dvds at Home', 'i like to watch DVDs at home', $ignore),
			array('Θα Ήθελα Να Φύγει', '  Θα ήθελα να φύγει  ', [], 'UTF-8')
		);
	}


	public function studlyProvider()
	{
		return array(
			array('CamelCase', 'camelCase'),
			array('CamelCase', 'Camel-Case'),
			array('CamelCase', 'camel case'),
			array('CamelCase', 'camel -case'),
			array('CamelCase', 'camel - case'),
			array('CamelCase', 'camel_case'),
			array('CamelCTest', 'camel c test'),
			array('StringWith1Number', 'string_with1number'),
			array('StringWith22Numbers', 'string-with-2-2 numbers'),
			array('1Camel2Case', '1camel2case'),
			array('CamelΣase', 'camel σase', 'UTF-8'),
			array('ΣτανιλCase', 'στανιλ case', 'UTF-8'),
			array('ΣamelCase', 'Σamel  Case', 'UTF-8')
		);
	}


	/**
	 * Singular & Plural test data. Returns an array of sample words.
	 *
	 * @return array
	 */
	public function singularVsPluralWordsProvider()
	{
	    // In the format array('singular', 'plural')
		return array(
			array('', ''),
			array('Alias', 'Aliases'),
			array('alumnus', 'alumni'),
			array('analysis', 'analyses'),
			array('aquarium', 'aquaria'),
			array('arch', 'arches'),
			array('atlas', 'atlases'),
			array('axe', 'axes'),
			array('baby', 'babies'),
			array('bacillus', 'bacilli'),
			array('bacterium', 'bacteria'),
			array('bureau', 'bureaus'),
			array('bus', 'buses'),
			array('Bus', 'Buses'),
			array('cactus', 'cacti'),
			array('cafe', 'cafes'),
			array('calf', 'calves'),
			array('categoria', 'categorias'),
			array('chateau', 'chateaux'),
			array('cherry', 'cherries'),
			array('child', 'children'),
			array('church', 'churches'),
			array('circus', 'circuses'),
			array('city', 'cities'),
			array('cod', 'cod'),
			array('cookie', 'cookies'),
			array('copy', 'copies'),
			array('crisis', 'crises'),
			array('criterion', 'criteria'),
			array('curriculum', 'curricula'),
			array('curve', 'curves'),
			array('deer', 'deer'),
			array('demo', 'demos'),
			array('dictionary', 'dictionaries'),
			array('domino', 'dominoes'),
			array('dwarf', 'dwarves'),
			array('echo', 'echoes'),
			array('elf', 'elves'),
			array('emphasis', 'emphases'),
			array('family', 'families'),
			array('fax', 'faxes'),
			array('fish', 'fish'),
			array('flush', 'flushes'),
			array('fly', 'flies'),
			array('focus', 'foci'),
			array('foe', 'foes'),
			array('food_menu', 'food_menus'),
			array('FoodMenu', 'FoodMenus'),
			array('foot', 'feet'),
			array('fungus', 'fungi'),
			array('glove', 'gloves'),
			array('half', 'halves'),
			array('hero', 'heroes'),
			array('hippopotamus', 'hippopotami'),
			array('hoax', 'hoaxes'),
			array('house', 'houses'),
			array('human', 'humans'),
			array('identity', 'identities'),
			array('index', 'indices'),
			array('iris', 'irises'),
			array('kiss', 'kisses'),
			array('knife', 'knives'),
			array('leaf', 'leaves'),
			array('life', 'lives'),
			array('loaf', 'loaves'),
			array('man', 'men'),
			array('matrix', 'matrices'),
			array('matrix_row', 'matrix_rows'),
			array('medium', 'media'),
			array('memorandum', 'memoranda'),
			array('menu', 'menus'),
			array('Menu', 'Menus'),
			array('mess', 'messes'),
			array('moose', 'moose'),
			array('motto', 'mottoes'),
			array('mouse', 'mice'),
			array('neurosis', 'neuroses'),
			array('news', 'news'),
			array('NodeMedia', 'NodeMedia'),
			array('nucleus', 'nuclei'),
			array('oasis', 'oases'),
			array('octopus', 'octopuses'),
			array('pass', 'passes'),
			array('person', 'people'),
			array('plateau', 'plateaux'),
			array('potato', 'potatoes'),
			array('powerhouse', 'powerhouses'),
			array('quiz', 'quizzes'),
			array('radius', 'radii'),
			array('reflex', 'reflexes'),
			array('roof', 'roofs'),
			array('runner-up', 'runners-up'),
			array('scarf', 'scarves'),
			array('scratch', 'scratches'),
			array('series', 'series'),
			array('sheep', 'sheep'),
			array('shelf', 'shelves'),
			array('shoe', 'shoes'),
			array('son-in-law', 'sons-in-law'),
			array('species', 'species'),
			array('splash', 'splashes'),
			array('spy', 'spies'),
			array('stimulus', 'stimuli'),
			array('stitch', 'stitches'),
			array('story', 'stories'),
			array('syllabus', 'syllabi'),
			array('tax', 'taxes'),
			array('terminus', 'termini'),
			array('thesis', 'theses'),
			array('thief', 'thieves'),
			array('tomato', 'tomatoes'),
			array('tooth', 'teeth'),
			array('tornado', 'tornadoes'),
			array('try', 'tries'),
			array('vertex', 'vertices'),
			array('virus', 'viri'),
			array('volcano', 'volcanoes'),
			array('wash', 'washes'),
			array('watch', 'watches'),
			array('wave', 'waves'),
			array('wharf', 'wharves'),
			array('wife', 'wives'),
			array('woman', 'women'),
			);
	}


}