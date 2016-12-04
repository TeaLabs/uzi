<?php
namespace Tea\Uzi;

use ArrayAccess;
use ArrayIterator;
use Countable;
use TypeError;
use Stringy\Stringy;
use Tea\Regex\Regex;
use IteratorAggregate;
use OutOfBoundsException;
use InvalidArgumentException;
use Tea\Contracts\General\Sliceable;

class Str extends Stringy implements Sliceable
{
	/**
	 * Encodings that can work without the mbstring module.
	 *
	 * @var array
	*/
	protected static $supportedEncodings = ['UTF-8' => true, 'ASCII' => true];

	/**
	 * @var array
	*/
	protected static $availableMbStringFuncs = [];

	/**
	 * @var bool
	*/
	protected $isSupportedEncoding;

	/**
	 * Initializes a Str object from the given value and encoding. The given
	 * value is cast to a string prior to assignment, and if encoding is not
	 * specified, it defaults to mb_internal_encoding().
	 *
	 * Throws a TypeError if the given value is an array or object without
	 * a __toString method.
	 *
	 * @param  mixed  $value      The string value.
	 * @param  string $encoding The character encoding
	 * @return void
	 * @throws TypeError
	 */
	public function __construct($value = '', $encoding = null)
	{
		if (!can_str_cast($value))
			throw new TypeError("Str objects can only be created from strings, ".
				"scalars (int, float, bool etc), other Str objects or objects that implement ".
				"the __toString method. "
				.( is_object($value) ? get_class($value) : gettype($value) )
				." given.");

		$this->str = (string) $value;
		$this->encoding = $encoding ?: \mb_internal_encoding();
	}

	/**
	 * Creates a new Str object from the given value and encoding.
	 * The given value is cast to a string prior to assignment, and if
	 * encoding is not specified, it defaults to mb_internal_encoding().
	 *
	 * Throws a TypeError if the given value is an array or object without
	 * a __toString method.
	 *
	 * @param  mixed  $value      The string value.
	 * @param  string $encoding The character encoding
	 * @return \Tea\Uzi\Str
	 * @throws TypeError
	 */
	public static function create($value = '', $encoding = null)
	{
		return new static($value, $encoding);
	}

	/**
	 * Returns an ASCII version of the string. A set of non-ASCII characters are
	 * replaced with their closest ASCII counterparts, and the rest are removed
	 * unless instructed otherwise.
	 *
	 * @param  bool    $removeUnsupported Whether or not to remove the
	 *                                    unsupported characters
	 * @return Tea\Uzi\Str
	 */
	public function ascii($removeUnsupported = true)
	{
		return $this->toAscii($removeUnsupported);
	}

	/**
	 * Get the camel case value of the string.
	 *
	 * @return Tea\Uzi\Str
	 */
	public function camel()
	{
		return $this->camelize();
	}

	/**
	 * Ensures that the string begins with a single instance of a substring.
	 * Unless $trim is given and is false, all existing occurrences of the
	 * substring at the beginning of the string will be trimmed.
	 *
	 * Calls $this->ensureLeft() if $trim is false.
	 *
	 * @see Str::ensureLeft()
	 *
	 * @param  string  $substring The substring to add if not present
	 * @param  bool    $trim      Whether to trim the substring or not.
	 * @return Tea\Uzi\Str
	 */
	public function begin($substring, $trim = true)
	{
		if(!$trim)
			return $this->ensureLeft($substring);

		$str = $this->trimLeft($substring, true);
		$str->str = $substring . $str->str;

		return $str;
	}

	/**
	 * Trims the string and replaces consecutive whitespace characters with a
	 * single space. This includes tabs and newline characters, as well as
	 * multibyte whitespace such as the thin space and ideographic space.
	 *
	 * Allows an optional delimiter which if provided will be used instead of
	 * single spaces.
	 *
	 * @param string $delimiter
	 * @return Tea\Uzi\Str
	*/
	public function compact($delimiter = ' ')
	{
		return $this->regexReplace(['\s+', "^\s+|\s+\$"], [$delimiter, '']);
	}


	/**
	 * Returns true if the string contains any $needles, false otherwise. By
	 * default the comparison is case-sensitive, but can be made insensitive by
	 * setting $caseSensitive to false.
	 *
	 * @param  string|iterable|mixed  $needles       Substring(s) to look for
	 * @param  bool    $caseSensitive   Whether or not to enforce case-sensitivity
	 * @return bool
	 */
	public function contains($needles, $caseSensitive = true)
	{
		$needles = $this->strToIterableOrIterable($needles, true, __METHOD__, 'needles');

		foreach ($needles as $needle)
			if (parent::contains($needle, $caseSensitive))
				return true;

		return false;
	}

	/**
	 * Returns true if the string ends with any of the given string(s), false otherwise.
	 * By default, the comparison is case-sensitive, but can be made insensitive
	 * by setting $caseSensitive to false.
	 *
	 * @param  string|iterable|mixed  $needles       Substring(s) to look for
	 * @param  bool      $caseSensitive       Whether or not to enforce case-sensitivity
	 * @return bool
	 */
	public function endsWith($needles, $caseSensitive = true)
	{
		$needles = $this->strToIterableOrIterable($needles, true, __METHOD__, 'needles');

		foreach ($needles as $needle)
			if (parent::endsWith($needle, $caseSensitive))
				return true;

		return false;
	}

	/**
	 * Ensures that the string ends with a single instance of a substring.
	 * Unless $trim is given and is false, all existing occurrences of the
	 * substring at the end of the string will be trimmed.
	 *
	 * Calls $this->ensureRight() if $trim is false.
	 *
	 * @see Str::ensureLeft()
	 *
	 * @param  string  $substring The substring to add if not present
	 * @param  bool    $trim      Whether to trim the substring or not.
	 * @return Tea\Uzi\Str
	 */
	public function finish($substring, $trim = true)
	{
		if(!$trim)
			return $this->ensureRight($substring);

		$str = $this->trimRight($substring, true);
		$str->str = $str->str.$substring;

		return $str;
	}

	/**
	 * Determine if the str is a pattern matching any of the given value(s).
	 *
	 * By default, the comparison is case-sensitive, but can be made insensitive
	 * by setting $caseSensitive to false.
	 *
	 * Also, asterisks (*) in str are translated into zero-or-more regular expression
	 * wildcards to make it convenient to check if the values starts with the given
	 * pattern such as "library/*", making any string check convenient. This can be
	 * disabled by setting $wildcards to false.
	 *
	 * @param  string|iterable|mixed    $value Value(s) to match against str
	 * @param  bool    $caseSensitive Whether or not to enforce case-sensitivity
	 * @param  bool    $wildcards Whether or not to enforce case-sensitivity
	 * @param  string  $value
	 * @return bool
	 */
	public static function is($values, $caseSensitive = true, $wildcards = true)
	{
		$values = $this->strToIterableOrIterable($values, true, __METHOD__, 'values');

		$pattern = Regex::quote($this->str);
		if($wildcards)
			$pattern = str_replace('\*', '.*', $pattern);

		foreach ($values as $value) {
			if ($this->str == $value)
				return true;

		}



		$pattern = preg_quote($pattern, '#');

		// Asterisks are translated into zero-or-more regular expression wildcards
		// to make it convenient to check if the strings starts with the given
		// pattern such as "library/*", making any string check convenient.
		$pattern = str_replace('\*', '.*', $pattern);

		return (bool) preg_match('#^'.$pattern.'\z#u', $value);
	}

	/**
	 * Strip characters a or substring from the beginning of the string.
	 *
	 * @see    Str::trimLeft()
	 *
	 * @param  string  	$chars    String of characters to strip.
	 * @param  bool  	$wholeStr Whether or not to match the entire string instead.
	 * @param  int  	$limit    The number of occurrences to be striped.
	 * @return Tea\Uzi\Str
	 */
	public function ltrim($chars = null, $wholeStr = false, $limit = -1)
	{
		return $this->trimLeft($chars, $wholeStr, $limit);
	}


	/**
	 * Returns true if $str matches the supplied pattern, false otherwise.
	 *
	 * @param  string $pattern Regex pattern to match against
	 * @return bool   Whether or not $str matches the pattern
	 */
	public function matches($patterns, $caseSensitive = true, $wildcards = true)
	{
		// $patterns =



		$regexEncoding = $this->regexEncoding();
		$this->regexEncoding($this->encoding);

		$match = \mb_ereg_match($pattern, $this->str);
		$this->regexEncoding($regexEncoding);

		return $match;
	}


	/**
	 * Perform a regular expression search and replace on the string.
	 *
	 * @uses Tea\Regex\Regex::replace()
	 *
	 * @param  string|array  $pattern     The regular expression pattern
	 * @param  string|array  $replacement The replacement string(s)
	 * @param  int           $limit       The maximum possible replacements.
	 * @param  int           $count       Filled with the number of replacements done
	 * @return Tea\Uzi\Str
	 */
	public function regexReplace($pattern, $replacement, $limit = -1, &$count = null)
	{
		if(is_string($limit)) $limit = -1;
		$str = Regex::replace(Regex::safeWrap($pattern), $replacement, $this, $limit, $count);
		return new static($str, $this->encoding);
	}

	/**
	 * Replaces occurrences of search in string by $replacement.
	 *
	 * @uses Str::regexReplace()
	 *
	 * @param  string|array  $search      The needle to search for
	 * @param  string  $replacement The string to replace with
	 * @param  int           $limit       The maximum possible replacements.
	 * @param  int           $count       Filled with the number of replacements done
	 * @return Tea\Uzi\Str
	 */
	public function replace($search, $replacement, $limit = -1, &$count = null)
	{
		return $this->regexReplace(Regex::quote($search), $replacement, $limit, $count);
	}

	/**
	 * Strip characters a or substring from the end of the string.
	 *
	 * @see    Str::trimRight()
	 *
	 * @param  string  	$chars    String of characters to strip.
	 * @param  bool  	$wholeStr Whether or not to match the entire string instead.
	 * @param  int  	$limit    The number of occurrences to be striped.
	 * @return Tea\Uzi\Str
	 */
	public function rtrim($chars = null, $wholeStr = false, $limit = -1)
	{
		return $this->trimRight($chars, $wholeStr, $limit);
	}

	/**
	 * Strip characters a or substring from the start and end of the string.
	 * If $chars is empty or not provided, whitespaces on both ends of the
	 * string will be removed.
	 *
	 * If $wholeStr is omitted or is false, occurrences of any of the characters
	 * in the given $char string will be removed. If true, only the occurrences
	 * that match the entire $chars string will be removed.
	 *
	 * If a limit is provided, it will set the number of occurrences to be striped.
	 * If not, all occurrences will be striped.
	 *
	 * @param  string  	$chars    String of characters to strip.
	 * @param  bool  	$wholeStr Whether or not to match the entire string instead.
	 * @param  int  	$limit    The number of occurrences to be striped.
	 * @return Tea\Uzi\Str
	 */
	public function trim($chars = null, $wholeStr = false, $limit = -1)
	{
		$pattern = $this->getTrimRegexPattern($chars, $wholeStr, $limit);

		return $this->regexReplace("^{$pattern}|{$pattern}\$", '');
	}


	/**
	 * Strip characters a or substring from the beginning of the string.
	 * If $chars is empty or not provided, beginning whitespaces are removed.
	 *
	 * If $wholeStr is omitted or is false, occurrences of any of the characters
	 * in the given $char string will be removed. If true, only the occurrences
	 * that match the entire $chars string will be removed.
	 *
	 * If a limit is provided, it will set the number of occurrences to be striped.
	 * If not, all occurrences will be striped.
	 *
	 * @param  string  	$chars    String of characters to strip.
	 * @param  bool  	$wholeStr Whether or not to match the entire string instead.
	 * @param  int  	$limit    The number of occurrences to be striped.
	 * @return Tea\Uzi\Str
	 */
	public function trimLeft($chars = null, $wholeStr = false, $limit =-1)
	{
		$pattern = $this->getTrimRegexPattern($chars, $wholeStr, $limit);

		return $this->regexReplace("^{$pattern}", '');
	}

	/**
	 * Strip characters a or substring from the end of the string.
	 * If $chars is empty or not provided, trailing whitespaces are removed.
	 *
	 * If $wholeStr is omitted or is false, occurrences of any of the characters
	 * in the given $char string will be removed. If true, only the occurrences
	 * that match the entire $chars string will be removed.
	 *
	 * If a limit is provided, it will set the number of occurrences to be striped.
	 * If not, all occurrences will be striped.
	 *
	 * @param  string  	$chars    String of characters to strip.
	 * @param  bool  	$wholeStr Whether or not to match the entire string instead.
	 * @param  int  	$limit    The number of occurrences to be striped.
	 * @return Tea\Uzi\Str
	 */
	public function trimRight($chars = null, $wholeStr = false, $limit =-1)
	{
		$pattern = $this->getTrimRegexPattern($chars, $wholeStr, $limit);

		return $this->regexReplace("{$pattern}\$", '');
	}

	/**
	 * Get the appropriate regex pattern for trimming a string
	 */
	protected function getTrimRegexPattern($chars, $wholeStr, $limit)
	{
		$chars = Regex::quote($chars);

		if($chars)
			if($wholeStr)
				$pattern = '(?:'.$chars.')';
			else
				$pattern = '['.$chars.']';
		else
			$pattern = '\s';

		return $pattern . ($limit > 0 ? '{1,'.$limit.'}' : '+');
	}

	/**
	 * Wraps the string with a single instance of the given substring.
	 * Unless $trim is given and is false, all existing occurrences of the
	 * substring at the start and end of the string will be trimmed. Otherwise,
	 * the string will be wrapped without checking.
	 *
	 * @param  string  $substring The substring to add to both sides
	 * @param  bool    $trim      Whether to first trim off the substring.
	 * @return Tea\Uzi\Str
	 */
	public function surround($substring, $trim = true)
	{
		$str = $trim ? $this->trim($substring, true) : $this->str;
		return new static( $substring.$str.$substring, $this->encoding);
	}

	/**
	 * Wraps the string with a single instance of the given substring.
	 * Alias for Str::surround()
	 *
	 * @see Str::surround()
	 * @uses Str::surround()
	 *
	 * @param  string  $substring The substring to add to both sides
	 * @param  bool    $trim      Whether to first trim off the substring.
	 * @return Tea\Uzi\Str
	 */
	public function wrap($substring, $trim = true)
	{
		return $this->surround($substring, $trim);
	}

	/**
	 * Extract a slice of the collection as specified by the offset and length.
	 *
	 * If offset is non-negative, the sequence will start at that offset in the
	 * collection. If offset is negative, the sequence will start that far from
	 * the end of the collection.
	 *
	 * If length is given and is positive, then the sequence will have up to that
	 * many elements in it. If the collection is shorter than the length, then only
	 * the available elements will be present. If length is given and is negative
	 * then the sequence will stop that many elements from the end of the collection.
	 * If it is omitted, then the sequence will have everything from offset up until
	 * the end of the collection.
	 *
	 * @param  int   $offset
	 * @param  int   $length
	 * @param  bool  $preserveKeys
	 * @return mixed
	 */
	public function slice($offset, $length = null, $preserveKeys = false)
	{
		throw new \BadMethodCallException("Method ".__METHOD__." Not Implemented.");
	}

	/**
	 * Get the element at the given index. If the given index is a string in the format:
	 * "offset:length", "offset:" or ":length" (where "offset" and "length" are integers),
	 * a slice equivalent to calling $this->slice($offset, $length); is returned.
	 *
	 * @param  mixed $index
	 * @return mixed
	 */
	public function offsetGet($index)
	{
		throw new \BadMethodCallException("Method ".__METHOD__." Not Implemented.");
	}

	/**
	 * Replaces occurrences of the given pattern(s) in string with provided
	 * replacement(s). An alias for mb_ereg_replace() with a fallback to preg_replace
	 * if the mbstring module is not installed.
	 *
	 * @param  string|array  $pattern     The regular expression pattern(s)
	 * @param  string|array  $replacement The string(s) to replace with
	 * @param  string|array  $subject     The string(s) to match
	 * @param  string|null   $option      Options
	 * @param  int           $limit       The maximum possible replacements for each pattern
	 * @param  int           $count       variable filled with the number of replacements done.
	 * @return Tea\Uzi\Str
	 */
	protected function mbregexReplace($pattern, $replacement, $subject, $option = null, $limit = -1, &$count = null)
	{
		if(!mbstring_loaded(true)){
			$modifiers = static::optionToModifiers($option, 'u');
			return $this->pregReplace($pattern, $replacement, $modifiers);
		}

		if(is_array($pattern)){
			$map = [];
			$rep_arr = is_array($replacement);
			$replacement = array_values($replacement);
			foreach (array_values($pattern) as $k => $p) {
				if($rep_arr)
					$map[$p] = isset($replacement[$k]) ? $replacement[$k] : '';
				else
					$map[$p] = $replacement;
			}
		}
		else{
			$map = [$pattern => $replacement];
		}

		if(is_null($option)) $option = 'msr';

		$regexEncoding = $this->regexEncoding();
		$this->regexEncoding($this->encoding);

		$result = $this->str;
		foreach ($map as $p => $r) {
			$result = mb_ereg_replace($p, $r, $result, $option);
		}

		$this->regexEncoding($regexEncoding);

		return new static($result, $this->encoding);
	}

	/**
	 * Replaces occurrences of the given pattern(s) in string with provided
	 * replacement(s). An alias for preg_replace() but ensures the encoding
	 * is supported by preg_replace().
	 *
	 * @param  string|array  $pattern     The regular expression pattern(s)
	 * @param  string|array  $replacement The string(s) to replace with
	 * @param  string|null   $modifiers   Modifiers
	 * @param  int           $limit       The maximum possible replacements for each pattern
	 * @param  int           $count       variable filled with the number of replacements done.
	 * @return Tea\Uzi\Str
	 */
	public function pregReplace($pattern, $replacement, $modifiers = null, $limit = -1, &$count = null)
	{
		if(!$this->supportsEncoding())
			return;

		$pattern = Regex::wrap($pattern, null, $modifiers);

		// $pattern = static::pregDelimit($pattern, '/', $modifiers);

		$result = preg_replace($pattern, $replacement, $this->str, $limit, $count);

		return static::create($result, $this->encoding);
	}

	/**
	 * Returns true if $str matches the supplied pattern, false otherwise.
	 *
	 * @param  string $pattern Regex pattern to match against
	 * @return bool   Whether or not $str matches the pattern
	 */
	public function regexMatches($pattern, $option = true, $wildcards = true)
	{
		// $patterns =



		$regexEncoding = $this->regexEncoding();
		$this->regexEncoding($this->encoding);

		$match = \mb_ereg_match($pattern, $this->str);
		$this->regexEncoding($regexEncoding);

		return $match;
	}


	/**
	 * Safely wrap the given regex pattern(s) with the a delimiter and add modifiers
	 * if none is set.
	 *
	 * To add bracket style delimiters, pass a delimiter with both the opening
	 * and closing characters eg. '<>', '{}'. For normal ones use a single
	 * character eg: /','#','%'.
	 *
	 * If $bracketStyle is provided and is true, will check for bracket style
	 * delimiters ie: '[]', '{}', '()', '<>'. Use this when the wrapping a
	 * pattern that might contain bracket style delimiters. But use with care
	 * as the pattern might contain non-quoted brackets.
	 * To be safe, you can provide the bracket delimiter(s) that should be checked
	 * as an array. This will avoid checking through all possible bracket style
	 * delimiters. Eg: if $bracketStyle = ['{<', '}>'], will only check for '{}' and
	 * '<>' delimiters.
	 *
	 * @param  string|array $regex         The regex pattern(s)
	 * @param  string       $delimiter     The delimiter. Defaults to '/'
	 * @param  string       $modifiers     The modifiers. Defaults to 'u'
	 * @param  bool|array   $bracketStyle  Whether to check for bracket delimiters. Defaults to false
	 * @return string|array
	 */
	// public static function pregDelimit($regex, $delimiter = null, $modifiers = null, $bracketStyle = false)
	// {
	// 	if(is_array($regex)){
	// 		$wrapped = [];

	// 		foreach ($regex as $r)
	// 			$wrapped[] = static::pregDelimit($r, $delimiter, $modifiers, $bracketStyle);

	// 		return $wrapped;
	// 	}

	// 	$regex_0 = mb_substr($regex, 0, 1);
	// 	if(!$regex || strpos('/#~+%', $regex_0) !== false)
	// 		return $regex;

	// 	if($bracketStyle){
	// 		$brackets = is_array($bracketStyle) ? $bracketStyle : ['<({[', '>)}]'];

	// 		if(strpos($brackets[0], $regex_0) !== false)
	// 			if(strpos($brackets[1], mb_substr(rtrim($regex, 'uimsxeADSUXJ'), -1)) !== false)
	// 				return $regex;
	// 	}

	// 	if(strlen($delimiter) > 1)
	// 		list($start, $finish) = str_split($delimiter);
	// 	else
	// 		$start = $finish = is_null($delimiter) ? '/' : $delimiter;

	// 	if(is_null($modifiers)) $modifiers = 'u';

	// 	$regex = str_replace("\\{$delimiter}", "{$delimiter}", $regex);
	// 	$regex = str_replace($delimiter, "\\{$delimiter}", $regex);

	// 	return $start.$regex.$finish.$modifiers;
	// }

	/**
	 * Returns true if $str matches the supplied pattern, false otherwise.
	 *
	 * @param  string $pattern Regex pattern to match against
	 * @return bool   Whether or not $str matches the pattern
	 */
	protected function matchesPattern($pattern)
	{
		$regexEncoding = $this->regexEncoding();
		$this->regexEncoding($this->encoding);

		$match = \mb_ereg_match($pattern, $this->str);
		$this->regexEncoding($regexEncoding);

		return $match;
	}

	/**
	 * Alias for mb_regex_encoding which default to a noop if the mbstring
	 * module is not installed.
	 */
	protected function regexEncoding()
	{
		if (mbstring_loaded(true))
			return call_user_func_array('mb_regex_encoding', func_get_args());
	}

	/**
	 * Determine if the current string encoding is supported out of the box
	 * without the need for the mbsting module.
	 *
	 * @param  bool $silent Whether or not to throw an exception if the current
	 *                      string encoding is not supported
	 * @return bool
	 * @throws Tea\Uzi\UnexpectedEncodingException
	 */
	protected function supportsEncoding($silent = false)
	{
		if(!isset($this->isSupportedEncoding)){
			$this->isSupportedEncoding = isset(static::$supportedEncodings[$this->encoding])
				? static::$supportedEncodings[$this->encoding] : false;
		}

		if($this->isSupportedEncoding || $silent)
			return $this->isSupportedEncoding;

		$supported = array_keys(static::$supportedEncodings);
		$last = count($supported) > 1
				? '" and "'. array_pop($supported) : array_pop($supported);
		$supported = implode(', "', $supported);
		throw new UnexpectedEncodingException("The mbstring module is required to work with "
			."\"{$this->encoding}\" encoded strings. ".
			 "Otherwise, only \"{$supported}{$last}\" encoded strings are supported.");
	}

	/**
	 * Determine if the given function exists. Used for checking mbstring specific functions.
	 */
	protected function mbfuncExists($func)
	{
		if(!isset(static::$availableMbStringFuncs[$func]))
			static::$availableMbStringFuncs[$func] = function_exists($func);

		return static::$availableMbStringFuncs[$func];
	}

	/**
	 * Determine if the given function exists. Used for checking mbstring specific functions.
	 */
	protected static function optionToModifiers($option, $add='', $remove = null)
	{
		if(!$option)
			return $add;

		$option =str_replace( array_merge(['r', 'p'], (array)$remove, (array)$add), '', $option);
		return $option.$add;
	}

	/**
	 * Get a valid iterable string(s) from the given value.
	 *
	 * @param  mixed   $value
	 * @param  bool    $strict
	 * @param  string  $method
	 * @param  string  $argName
	 * @return array|Traversable
	 * @throws TypeError
	 */
	protected function strToIterableOrIterable($value, $strict = true, $method = null, $argName = null)
	{
		if(can_str_cast($value))
			return [$value];

		if(is_iterable($value))
			return $value;

		if(!$strict)
			return (array) $value;

		$method = $method ?: '';
		$argName = $argName ?: '';
		$type = ucfirst(is_object($value) ? get_class($value) : gettype($value));

		throw new TypeError("Str method \"{$method}\" argument \"{$argName}\":"
			." Accepts values that can be cast to string (see Tea\Uzi\can_str_cast()),"
			." arrays or Traversable objects. \"{$type}\" given.");
	}


}