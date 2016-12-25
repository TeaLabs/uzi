<?php
namespace Tea\Uzi;

use Stringy\Stringy;

/**
 * Creates a new Str object from the given value and encoding.
 * The given value is cast to a string prior to assignment, and if
 * encoding is not specified, it defaults to mb_internal_encoding().
 *
 * Throws a TypeError if the given value is an array or object without
 * a __toString method.
 *
 * @see    Tea\UziStr::create()
 * @param  mixed   $str      Value to modify, after being cast to string
 * @param  string  $encoding The character encoding
 * @return \Tea\Uzi\Str
 * @throws TypeError
 */
function str($str = '', $encoding = null)
{
	return new Str($str, $encoding);
}


/**
* Generate a alpha-numeric string.
*
* @param  int  $length
*
* @return \Tea\Uzi\Str
*/
function random_str($length = 16)
{
	return Uzi::random($length);
}
