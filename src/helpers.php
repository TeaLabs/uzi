<?php
namespace Tea\Uzi;

/**
 * Creates a Str object and assigns both str and encoding properties
 * the supplied values. $str is cast to a string prior to assignment, and if
 * $encoding is not specified, it defaults to mb_internal_encoding(). It
 * then returns the initialized object. Throws an InvalidArgumentException
 * if the first argument is an array or object without a __toString method.
 *
 * @param  mixed   $str      Value to modify, after being cast to string
 * @param  string  $encoding The character encoding
 * @return \Tea\Uzi\Str
 * @throws \InvalidArgumentException
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