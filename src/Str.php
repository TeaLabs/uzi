<?php
namespace Tea\Uzi;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use Stringy\Stringy;
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
	 * Throws an InvalidArgumentException if the given value is an array
	 * or object without a __toString method (other than Str objects).
	 *
	 * @param  mixed  $value      The string value.
	 * @param  string $encoding The character encoding
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function __construct($value = '', $encoding = null)
	{
		if (is_array($value) || (is_object($value) && !method_exists($value, '__toString')))
			throw new InvalidArgumentException("Str objects can only be created from strings, ".
				"scalars (int, float, bool etc), other Str objects or objects that implement ".
				"the __toString method. ".(is_object($value)?get_class($value):'ARRAY')." given.");

		$this->str = (string) $value;
		$this->encoding = $encoding ?: \mb_internal_encoding();
	}

	/**
	 * Creates a new Str object from the given value and encoding.
	 * The given value is cast to a string prior to assignment, and if
	 * encoding is not specified, it defaults to mb_internal_encoding().
	 *
	 * Throws an InvalidArgumentException if the given value is an array
	 * or object without a __toString method (other than Str objects).
	 *
	 * @param  mixed  $value      The string value.
	 * @param  string $encoding The character encoding
	 * @return \Tea\Uzi\Str
	 * @throws \InvalidArgumentException
	 */
	public static function create($value = '', $encoding = null)
	{
		return new static($value, $encoding);
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
	 * Remove all space chars from string and replace them with a single instance
	 * the given delimiter. If a delimiter is not provided, a single space char is used.
	 * Space chars from the beginning and end of the string are trimmed.
	 *
	 * @param string $delimiter
	 * @return Tea\Uzi\Str
	*/
	public function compact($delimiter = ' ')
	{
		$patterns = ['/ +/u', "/^ +| +\$/u"];
		$replacements = [$delimiter, ''];

		return $this->pregReplace($patterns, $replacements);
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
	 * Ensures that the string ends with a single instance of $substring.
	 * If it doesn't, it's appended.
	 * Unlike ensureRight, all existing occurrences if the substring will
	 * be stripped to a single instance.
	 *
	 * @param  string  $substr  The substring to add if not present
	 * @return Tea\Uzi\Str
	 */
	public function finish($substr)
	{
		return new static($this->stripRight($substr).$substr, $this->encoding);
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
	 * Remove all whitespaces from string and replace them with a single instance
	 * the given delimiter. If a delimiter is not provided, a single space is used.
	 *
	 * @param string $delimiter
	 * @return Tea\Uzi\Str
	*/
	public function minify($delimiter = ' ')
	{
		return $this->regexReplace('\s+', $delimiter);
	}

	/**
	 * Replaces occurrences of $search in $str by $replacement.
	 *
	 * @param  string|array  $search      The needle to search for
	 * @param  string  $replacement The string to replace with
	 * @return Stringy Object with the resulting $str after the replacements
	 */
	public function replace($search, $replacement, $regex = false, $options = null, $limit = null, &$count = null)
	{
		if(!$regex)
			$search = $this->pregQuote($search);
		return $this->regexReplace($search, $replacement, $options, $limit, $count);
	}

	/**
	 * Replaces occurrences of the given pattern(s) in string by provided
	 * replacement(s). An alias for preg_replace().
	 *
	 * @param  string|array  $pattern     The regular expression pattern(s)
	 * @param  string|array  $replacements The string(s) to replace with
	 * @param  int           $limit       The maximum possible replacements for each pattern
	 * @param  int           $count       Variable filled with the number of replacements done.
	 * @return Tea\Uzi\Str
	 */
	public function pregMatch($pattern, $flags = 0, $offset = 0)
	{
		throw new RuntimeExpception("Method ".__METHOD__." not implemented.");
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
	 * Detect the string's character encoding.
	 *
	 * @param  string|array  $encodingList
	 * @param  bool          $strict
	 * @return string|false
	 */
	public function detectEncoding($encodingList = null, $strict = true)
	{
		return mb_detect_encoding($this->str, $encodingList, $strict);
	}

	/**
	 * Determine if the string encoding is safe to use with PCRE functions.
	 *
	 * @return bool
	 */
	public function isPregSupported()
	{
		if(isset($this->isPregSupported))
			return $this->isPregSupported;

		$encoding = $this->detectEncoding();
		if(!$encoding)
			$encoding = $this->encoding;

		$supported = $this->pregSupported();
		return $this->isPregSupported = (isset($supported[$encoding]) && $supported[$encoding]);
	}

	/**
	 * Get a list of encodings supported by PCRE.
	 *
	 * @return array
	 */
	public static function pregSupported()
	{
		return static::$pregSupported;
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
	 * @param  string|null   $modifiers   Modifiers
	 * @param  int           $limit       The maximum possible replacements for each pattern
	 * @param  int           $count       variable filled with the number of replacements done.
	 * @return Tea\Uzi\Str
	 */
	public function mregReplace($pattern, $replacement, $option = null)
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
	 * Perform a regular expression search and replace on the string using
	 * the appropriate regex method depending on the encoding.
	 * If string encoding is UTF-8 or ASCII, preg_replace will be used.
	 * Otherwise, mb_ereg_replace is used.
	 *
	 * When calling preg_replace, delimiters ('/') will be added to the pattern(s).
	 *
	 * If $option is provided, it is used as the option parameter when calling
	 * mb_ereg_replace and as modifiers when calling preg_replace.
	 *
	 * Note that parameters $limit and $count only work with preg_replace.
	 *
	 * @param  string|array  $pattern     The regular expression pattern
	 * @param  string|array  $replacement The replacement string(s)
	 * @param  string        $options     Option/modifiers
	 * @param  int           $limit       The maximum possible replacements.
	 * @param  int           $count       Filled with the number of replacements done
	 * @return Tea\Uzi\Str
	 */
	public function regexReplace($pattern, $replacement, $option = null, $limit = -1, &$count = null)
	{
		if(!$this->supportsEncoding(true))
			return $this->mregReplace($pattern, $replacement, $option);

		$modifiers = static::optionToModifiers($option, 'u');
		return $this->pregReplace($pattern, $replacement, $modifiers, $limit, $count);
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


}