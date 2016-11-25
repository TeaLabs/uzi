<?php
namespace Tea\Uzi;

use Stringy\Stringy;

class Str extends Stringy
{
	/**
	 * Prefix a string with a single instance of a given value.
	 *
	 * @param  string  $string
	 * @param  string  $prefix
	 * @return string
	 */
	public function begin($substr)
	{
		return $prefix.static::lstrip($string, $prefix);
	}


	/**
	 * Determine if the string contains a given substring(s).
	 *
	 * @param  string|array  $needles
	 * @param  bool          $caseSensitive
	 * @return bool
	 */
	public function contains($needles, $caseSensitive = true)
	{
		if (empty($needles))
			return false;

		foreach ((array) $needles as $needle)
			if (parent::contains($needle, $caseSensitive))
				return true;
		return false;
	}

	/**
	 * Determine if the string contains any of the given substrings.
	 *
	 * @param  string|array  $needles
	 * @param  bool          $caseSensitive
	 * @return bool
	 */
	public function containsAny($needles, $caseSensitive = true)
	{
		return $this->contains($needles, $caseSensitive);
	}

	/**
	 * Returns true if the string ends with any of the given $needles,
	 * false otherwise. By default, the comparison is case-sensitive,
	 * but can be made insensitive by setting $caseSensitive to false.
	 *
	 * @param  string $substring     The substring to look for
	 * @param  bool   $caseSensitive Whether or not to enforce case-sensitivity
	 * @return bool
	 */
	public function endsWith($needles, $caseSensitive = true)
	{
		foreach ((array) $needles as $needle)
			if (parent::endsWith($needle, $caseSensitive))
				return true;
		return false;
	}

	/**
	 * Strip a substring from the beginning and end of the string.
	 * If value is not provided, beginning or/and trialling whitespaces are striped.
	 *
	 * Unlike trim which removes a set of characters, this method removes occurrences
	 * that match the entire substring
	 *
	 * @param  string  	$substr
	 * @return Str
	 */
	public function strip($substr = null, $limit = -1)
	{
		$substr = $substr ? '(?:'.preg_quote($substr).')' : '[:space:]';
		$substr .= $limit > 0 ? '{1,'.$limit.'}' : '+';

		return $this->regexReplace("^$substr|$substr+\$", '');
	}

	/**
	 * Strip a substring from the beginning of a string.
	 * You can set the max number of occurrences to be stripped by providing the limit.
	 * By default, all occurrences are stripped.
	 *
	 * @param  string  	$string
	 * @param  string  	$value
	 * @param  int  	$limit
	 * @return string
	 */
	public static function lstrip($string, $value = ' ', $limit = -1)
	{
		return static::strip($string, $value, $limit, static::STRIP_LEFT);
	}

	/**
	 * Strip a substring from the end of a string.
	 * You can set the max number of occurrences to be stripped by providing the limit.
	 * By default, all occurrences are stripped.
	 *
	 * @param  string  	$string
	 * @param  string  	$value
	 * @param  int  	$limit
	 * @return string
	 */
	public static function rstrip($string, $value = ' ', $limit = -1)
	{
		return static::strip($string, $value, $limit, static::STRIP_RIGHT);
	}


	/**
	 * Convert a value to studly caps case.
	 *
	 * @return string
	 */
	public function toStudly()
	{
		return $this->camelize()->upperCaseFirst();
	}

}