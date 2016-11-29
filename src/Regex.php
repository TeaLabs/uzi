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
				$wrapped[] = static::pregDelimit($r, $delimiter, $modifiers, $bracketStyle);

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
}

// 1.03 1.1