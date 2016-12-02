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
 * Determine whether the mbstring module is loaded. If strict is false (the default),
 * checks whether a polyfill for mbstring exists.
 *
 * @param  bool   $strict
 * @return bool
 */
function mbstring_loaded($strict = false)
{
	static $extension, $polyfill;

	if(is_null($extension))
		$extension = extension_loaded('mbstring');

	if(is_null($polyfill))
		$polyfill = function_exists('mb_strlen');

	return ($extension || (!$strict && $polyfill));
}


/**
 * Determine whether a value can be casted to string. Returns true if value is a
 * scalar (String, Integer, Float, Boolean etc.), null or if it's an object that
 * implements the __toString() method. Otherwise, returns false.
 *
 * @param  mixed   $value
 * @return bool
 */
function can_str_cast($value)
{
	if(is_null($value) || is_scalar($value))
		return true;

	if(is_object($value) && method_exists($value, '__toString'))
		return true;

	return false;
}

/**
 * Determine whether a value is iterable and not a string.
 *
 * @param  mixed   $value
 * @return bool
 */
function is_iterable_not_str($value)
{
	return is_iterable($value) && ($value instanceof Stringy);
}

