<?php
namespace Tea\Uzi;

/**
*
*/
class Regex
{
	const DEFAULT_DELIMITER = '/';

	/**
	 * Quote special regular expression characters including the delimeter.
	 * If delimiter is not provided, the default delimiter (Regex::DEFAULT_DELIMITER)
	 * will be quoted.
	 * To prevent any delimiter from being escaped, pass false as the $delimiter
	 *
	 * @param  string       $value
	 * @param  string|false $delimiter
	 * @return string
	 */
	public static function quote($value, $delimiter = self::DEFAULT_DELIMITER)
	{
		$delimiter = $delimiter ?: null;
		return $value ? preg_quote($value, $delimiter) : $value;
	}

	/**
	 * Perform a regular expression search and replace on a string using the appropriate
	 * regex method depending on the encoding.
	 * If string encoding is UTF-8 or ASCII, preg_replace will be used. Otherwise,
	 * mb_ereg_replace is used if available.
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
	public function replace($pattern, $replacement, $subject, $option = null, $limit = -1, &$count = null)
	{
		if(!$this->supportsEncoding(true))
			return $this->mregReplace($pattern, $replacement, $option);

		$modifiers = static::optionToModifiers($option, 'u');
		return $this->pregReplace($pattern, $replacement, $modifiers, $limit, $count);
	}

	/**
	 * Quote special regular expression characters including the delimeter.
	 * Alias for Regex::quote()
	 *
	 * @see Regex::quote()
	 *
	 * @param  string       $value
	 * @param  string|false $delimiter
	 * @return string
	 */
	public static function sanitize($value, $delimiter = self::DEFAULT_DELIMITER)
	{
		return static::quote($value, $delimiter);
	}

	/**
	 * Safely remove the given delimiter from the regex pattern if any.
	 * If a delimiter is not provided, removes any possible regex delimiters.
	 * Ie: '/', '#', '~', '+' and '%' or '[]', '{}', '()' and '<>' if bracketStyle
	 * is true.
	 * If a modifiers variable is provided, it will be filled with the modifiers
	 * in the pattern or empty string if none is set.
	 *
	 * @param  string  $regex      The regex pattern
	 * @param  string  $delimiter  The delimiter. Defaults to '/'
	 * @return string
	 */
	public static function unwrap($regex, $delimiter = null, $bracketStyle = false, &$modifiers = null)
	{
		throw new \BadMethodCallException("Method ".__METHOD__." is not implemented.");


		$delimiters = '/#~+%';

		if(!$regex || strpos($delimiters, $regex[0]) !== false)
			return $regex;

		if( strpos('({[<', $regex[0]) !== false){
			if( $has_mbstring === null )
				$has_mbstring = function_exists('mb_substr');
			$end = $has_mbstring
					? mb_substr(rtrim($regex, 'uimsxeADSUXJ'), -1)
					: substr(rtrim($regex, 'uimsxeADSUXJ'), -1);
			if(strpos(')}]>', $end) !== false)
				return $regex;
		}

		return $delimiter.$regex.$delimiter.$modifiers;
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
	public static function wrap($regex, $delimiter = null, $modifiers = null, $bracketStyle = false)
	{
		if(is_array($regex)){
			$wrapped = [];

			foreach ($regex as $r)
				$wrapped[] = static::wrap($r, $delimiter, $modifiers, $bracketStyle);

			return $wrapped;
		}

		$regex_0 = mb_substr($regex, 0, 1);
		if(!$regex || strpos('/#~+%', $regex_0) !== false)
			return $regex;

		if($bracketStyle){
			$brackets = is_array($bracketStyle) ? $bracketStyle : ['<({[', '>)}]'];

			if(strpos($brackets[0], $regex_0) !== false)
				if(strpos($brackets[1], mb_substr(rtrim($regex, 'uimsxeADSUXJ'), -1)) !== false)
					return $regex;
		}

		if(strlen($delimiter) > 1)
			list($start, $end) = str_split($delimiter);
		else
			$start = $end = is_null($delimiter) ? static::DEFAULT_DELIMITER : $delimiter;

		if(is_null($modifiers)) $modifiers = 'u';


		// $regex = str_replace(['\\'.$start, "\\".$end], [$start, $end], $regex);
		// $regex = str_replace([$start, $end], ['\\'.$start, "\\".$end], $regex);

		return $start.$regex.$end.$modifiers;
	}

	/**
	 * Cast the none Str value(s) to Str instances.
	 *
	 * @param  mixed   $values
	 * @param  string  $encoding
	 * @param  bool    $iterable
	 * @return Tea\Uzi\Str
	 */
	protected static function castToStr($values, $encoding = null, $mapIterable = true)
	{
		if($values instanceof Str)
			return true;

		if(can_str_cast($value))
			return new Str($values, $encoding);

		if($mapIterable && is_iterable($values)){
			$instances = [];
			foreach ($values as $value){
				$instances[] = static::castToStr($values, $encoding, false);
			}
			return $instances;
		}

		return false;
	}
}

// 1.03 1.1