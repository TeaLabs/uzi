<?php
namespace Tea\Uzi;

use Stringy\Stringy;
use Tea\Regex\Regex;
use Tea\Regex\Modifiers;
use Tea\Uzi\Utils\Helpers;
use Tea\Uzi\Utils\Pluralizer;
use Tea\Contracts\General\Sliceable;
use Tea\Contracts\Regex\RegularExpression as RegularExpressionContract;

class Str extends Stringy
{

	/**
	 * Ensures that the string begins with a single instance of a substring.
	 * Unless $strip is given and is false, all existing occurrences of the
	 * substring at the beginning of the string will be stripped. Otherwise,
	 * will just call {@see \Stringy\Stringy::ensureLeft()} and return it's
	 * return value.
	 *
	 * @uses \Tea\Uzi\Str::stripLeft()       when $strip is TRUE
	 * @uses \Stringy\Stringy::ensureLeft()  when $strip is FALSE.
	 *
	 * @param  string  $substring
	 * @param  bool    $strip
	 * @param  int     $stripLimit
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function begin($substring, $strip = true, $stripLimit = -1)
	{
		if(!$strip)
			return $this->ensureLeft($substring);

		$str = $this->stripLeft($substring, $stripLimit);
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
	 * @param  string $delimiter
	 *
	 * @return \Tea\Uzi\Str
	*/
	public function compact($delimiter = ' ')
	{
		$regex = $this->newRegex(['\s+', "(?:\A\s+)|(?:\s+\z)"]);
		return $this->getNew($regex->replace([$delimiter, ''], $this)->result());
	}

	/**
	 * Returns TRUE if the string ends with any of the given needles, FALSE
	 * otherwise. By default, the comparison is case-sensitive, but can be made
	 * insensitive by setting $caseSensitive to FALSE.
	 *
	 * @param  string|array|iterable  $needles
	 * @param  bool                   $caseSensitive
	 * @return bool
	 */
	public function endsWith($needles, $caseSensitive = false)
	{
		$needles = Regex::quote($needles);

		if(is_array($needles))
			$needles = join('\z)|(?:', $needles);

		return $this->newRegex(
						"(?:{$needles}\z)",
						($caseSensitive ? '' : Modifiers::CASELESS)
					)->matches($this);
	}


	/**
	 * Ensures that the string ends with a single instance of a substring.
	 * Unless $strip is given and is false, all existing occurrences of the
	 * substring at the end of the string will be stripped. Otherwise, will
	 * just call {@see \Stringy\Stringy::ensureRight()} and return it's return
	 * value.
	 *
	 * @uses \Tea\Uzi\Str::stripRight()       when $strip is TRUE.
	 * @uses \Stringy\Stringy::ensureRight()  when $strip is FALSE.
	 *
	 * @param  string  $substring
	 * @param  bool    $strip
	 * @param  int     $stripLimit
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function finish($substring, $strip = true, $stripLimit = -1)
	{
		if(!$strip)
			return $this->ensureRight($substring);

		$str = $this->stripRight($substring, $stripLimit);
		$str->str = $str->str . $substring;

		return $str;
	}


	/**
	 * Return a formatted string.
	 *
	 * @param  string  $format
	 * @param  array   $placeholders
	 * @param  mixed   $default
	 * @return string
	 */
	public function _format($format, $placeholders = [], $default = '')
	{
		$placeholders = (array) $placeholders;
		$matches = [];
		if(!preg_match_all('/\{([^{}]*)\}+/u', $format, $matches))
			return sprintf($format, ...array_values($placeholders));

		ksort($placeholders, SORT_NATURAL);
		$placeholders['__default__'] = $default;
		$positions = array_flip(array_keys($placeholders));

		$type_specifiers = '/([sducoxXbgGeEfF])$/u';
		$replacements = [];

		foreach ($matches[1] as $match) {

			list($name, $options) = array_pad(explode(':', $match, 2), 2, '');
			if(!preg_match($type_specifiers, $options))
				$options .= 's';

			$position = array_key_exists($name, $positions)
			? $positions[$name]+1 : $positions['__default__']+1;

			$pattern = '%'.$position.'$'.$options;
			$replacements['{'.$match.'}'] = $pattern;
		}

		$format = str_replace(array_keys($replacements), array_values($replacements), $format);

		return sprintf($format, ...array_values($placeholders));
	}

	/**
	 * Join provided pieces with the Str. If $glueOnce is FALSE or not provided,
	 * this method calls implode() with the Str as $glue and $pieces as $pieces.
	 * If $gluleOnce is passed as TRUE, the Str will first be stripped from both
	 * ends of the pieces to ensure only one instance of Str exists between the
	 * pieces.
	 *
	 * For example:
	 *   Str::create('/')->join(['/a', '/b/', '/c']); // returns "/a//b///c"
	 *   Str::create('/')->join(['/a', 'b/', '/c'], true); // returns "/a/b/c"
	 *
	 * @param  iterable   $pieces
	 * @param  bool       $glueOnce
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function join($pieces, $glueOnce = false)
	{
		$glue = $this->str;

		if(!$glueOnce || $glue == ""){
			return $this->getNew(implode($glue, Helpers::iterableToArray($pieces)));
		}

		$result = null;

		foreach ($pieces as $piece) {

			if(is_null($result)){
				$result = $this->getNew($piece);
			}
			else{
				$piece = $piece == "" || $piece instanceof self ? $piece : $this->getNew($piece);
				$result = $result->finish($glue);
				$result->str = $result->str . ($piece == "" ? "" : $piece->stripLeft($glue));
			}
		}

		return is_null($result) ? $this->getNew('') : $this->getNew($result);
	}

	/**
	 * Determine if the string is a pattern matching the given value.
	 *
	 * Asterisks (*) in the string are translated into zero-or-more regular expression
	 * wildcards to make it convenient to check if the values starts with the given
	 * pattern such as "library/*", making any string check convenient. This can be
	 * disabled by setting wildcards to false.
	 *
	 * By default, the comparison is case-sensitive, but can be made insensitive
	 * by setting $caseSensitive to false.
	 *
	 * @param  string  $value
	 * @param  bool    $wildcards
	 * @param  bool    $caseSensitive
	 *
	 * @return bool
	 */
	public function is($value, $wildcards = true, $caseSensitive = true)
	{
		if($this->str == $value)
			return true;

		return $this->regexPatternForIs($wildcards, $caseSensitive)->matches($value);
	}


	/**
	 * Determine if the string is a pattern matching all the given values.
	 *
	 * Asterisks (*) in the string are translated into zero-or-more regular expression
	 * wildcards to make it convenient to check if the values starts with the given
	 * pattern such as "library/*", making any string check convenient. This can be
	 * disabled by setting wildcards to false.
	 *
	 * By default, the comparison is case-sensitive, but can be made insensitive
	 * by setting $caseSensitive to false.
	 *
	 * @param  iterable  $values
	 * @param  bool      $wildcards
	 * @param  bool      $caseSensitive
	 *
	 * @return bool
	 */
	public function isAll($values, $wildcards = true, $caseSensitive = true)
	{
		$regex = $this->regexPatternForIs($wildcards, $caseSensitive);

		return count($regex->filter(Helpers::toArray($values), true)) === 0;
	}



	/**
	 * Determine if the string is a pattern matching any of the given values.
	 *
	 * Asterisks (*) in the string are translated into zero-or-more regular expression
	 * wildcards to make it convenient to check if the values starts with the given
	 * pattern such as "library/*", making any string check convenient. This can be
	 * disabled by setting wildcards to false.
	 *
	 * By default, the comparison is case-sensitive, but can be made insensitive
	 * by setting $caseSensitive to false.
	 *
	 * @param  iterable  $values
	 * @param  bool      $wildcards
	 * @param  bool      $caseSensitive
	 *
	 * @return bool
	 */
	public function isAny($values, $wildcards = true, $caseSensitive = true)
	{
		$regex = $this->regexPatternForIs($wildcards, $caseSensitive);

		return count($regex->filter(Helpers::toArray($values))) > 0;
	}

	/**
	 * Determine if the string matches the given regex pattern.
	 *
	 * Unless the case-less modifier is set on the pattern, the comparison is
	 * case-sensitive. This can be changed by passing $caseSensitive to FALSE.
	 *
	 * @param  string|\Tea\Contracts\Regex\RegularExpression  $pattern
	 * @param  bool                                           $caseSensitive
	 *
	 * @return bool
	 */
	public function matches($pattern, $caseSensitive = true)
	{
		$regex = $this->toRegex($pattern, ($caseSensitive ? "" : Modifiers::CASELESS ));

		return $regex->matches($this);
	}


	/**
	 * Get the plural form of the string if it's an English word.
	 *
	 * @param  int  $count
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function plural($count = 2)
	{
		return $this->getNew(Pluralizer::plural($this->str, $count));
	}


	/**
	 * Replaces occurrences of search in string by replacement.
	 *
	 * @uses str_replace()
	 *
	 * @param  string|array|\Tea\Contracts\Regex\RegularExpression  $search
	 * @param  string|array  $replacement
	 * @param  int           $limit
	 * @param  int           &$count
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function replace($search, $replacement, &$count = 0)
	{
		return $this->getNew(str_replace($search, $replacement, $this->str, $count));
	}

	/**
	 * Replace the first occurrences of $search in the string with $replacement.
	 * This method returns a new Str with the first occurrences of search in Str
	 * replaced with the given replacement value(s).
	 *
	 * If search and replacement are arrays, then replaceFirst() takes a value
	 * from each array and uses them to search and replace on Str. If replacement
	 * has fewer values than search, then an empty string is used for the rest of
	 * replacement values.
	 *
	 * If search is an array and replacement is a string, then this replacement
	 * string is used for every value of search. But ff search is a string and
	 * replacement an array, occurrences of search in Str will be replaced
	 * sequentially with the values in replacement.
	 * The first occurrence of search will be replaced with the 1st element in the
	 * replacement array, the 2nd occurrence with the 2nd element and so on. Till
	 * the last element in replacement array or till there are no more occurrences
	 * of search in the new Str (whichever happens first). The same happens when
	 * search is an array and replacement is an array of arrays.
	 *
	 * @param  string|array  $search
	 * @param  string|array  $replace
	 * @param  string        $subject
	 * @param  string        $encoding
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function replaceFirst($search, $replacement, &$count = 0)
	{
		if(is_array($search))
			$str = $this->replaceFirstArray($search, $replacement, $this->str, $this->encoding, $count);
		else
			$str = $this->replaceFirstString($search, $replacement, $this->str, $this->encoding, $count);
		return $this->getNew($str);
	}

	/**
	 * @todo Use regex split for replace methods.
	*/
	protected function replaceFirstRegex($search, $replace, $subject, $encoding, &$count)
	{
		$pattern = Regex::quote($search);
		$regex = $this->newRegex("((?:".$pattern."))");

		if(!is_array($replace))
			$replace = [$replace];
		else
			$replace = array_values($replace);

		$numReps = count($replace);
		$limit = count($replace)+1;
		$chunks = $regex->split($subject, $limit, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		$rk = 0;
		$new = "";
		foreach ($chunks as $key => $chunk) {
			if($rk < $numReps && $chunk === (string) $search){
				$new .= $replace[$rk];
				++$rk;
				++$count;
			}
			else{
				$new .= $chunk;
			}

		}

		// $rep = jsObject($replace);
		// $chks = jsObject($chunks);
		// $sch = jsObject($search);
		// hr();
		// echo "\n Subject: `{$subject}` Limit: {$limit}\n Search: {$sch}\n Replace: {$rep}\n Chuncks: {$chks}\n";

		return $new;
	}

	/**
	 * Replace the first occurrences of each $search in $subject with $replace.
	 * If $replace is an array, occurrences of each $search entry will be
	 * replaced sequentially with the values in $replace.
	 *
	 * @param  array         $search
	 * @param  string|array  $replace
	 * @param  string        $subject
	 * @param  string        $encoding
	 *
	 * @return string
	 */
	protected function replaceFirstArray(array $search, $replace, $subject, $encoding, &$count)
	{
		if($repArray = is_array($replace) && $numReps = count($replace)){
			$replace = array_values($replace);
		}

		foreach (array_values($search) as $key => $value) {
			$replacement = !$repArray ? $replace : ($key >= $numReps ? '' : $replace[$key]);
			$subject = $this->replaceFirstString($value, $replacement, $subject, $encoding, $count);
		}

		return $subject;
	}

	/**
	 * Replace the occurrences of $search in $subject with $replace. If $replace
	 * is an array, occurrences of $search will be replaced sequentially with
	 * the values in $replace.
	 *
	 * @param  string        $search
	 * @param  string|array  $replace
	 * @param  string        $subject
	 * @param  string        $encoding
	 *
	 * @return string
	 */
	protected function replaceFirstString($search, $replace, $subject, $encoding, &$count)
	{
		if(is_array($replace))
			return $this->replaceFirstSequentially($search, $replace, $subject, $encoding, $count);
		else
			return $this->replaceFirstOne($search, $replace, $subject, $encoding, $count);
	}

	/**
	 * Replace the occurrences of $search in $subject sequentially with $replace.
	 *
	 * @param  string  $search
	 * @param  array   $replace
	 * @param  string  $subject
	 * @param  string  $encoding
	 *
	 * @return string
	 */
	protected function replaceFirstSequentially($search, array $replace, $subject, $encoding, &$count)
	{
		$offset = 0;
		$lenSearch = mb_strlen($search, $encoding);
		foreach ($replace as $value) {
			$subject = $this->replaceFirstOne($search, $value, $subject, $encoding, $count, $offset, $lenSearch);
			if($offset === false)
				break;
		}
		return $subject;
	}

	/**
	 * Replace the first occurrence of $search in $subject with $replace.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @param  string  $encoding
	 * @param  int     &$offset
	 *
	 * @return string
	 */
	protected function replaceFirstOne($search, $replace, $subject, $encoding, &$count, &$offset = 0, $lenSearch = null)
	{
		$offset = mb_strpos($subject, $search, $offset, $encoding);

		if ($offset === false)
			return $subject;


		$lenSearch = is_null($lenSearch) ? mb_strlen($search, $encoding) : $lenSearch;

		$before = mb_substr($subject, 0, $offset, $encoding);
		$after = mb_substr($subject, $offset+$lenSearch, null, $encoding);

		$offset += mb_strlen($replace, $encoding);
		++$count;

		return $before.$replace.$after;
	}

	/**
	 * Replace the last occurrences of $search in the string with $replacement.
	 * This method returns a new Str with the last occurrences of search in Str
	 * replaced with the given replacement value(s).
	 *
	 * If search and replacement are arrays, then replaceFirst() takes a value
	 * from each array and uses them to search and replace on Str. If replacement
	 * has fewer values than search, then an empty string is used for the rest of
	 * replacement values.
	 *
	 * If search is an array and replacement is a string, then this replacement
	 * string is used for every value of search. But ff search is a string and
	 * replacement an array, occurrences of search in Str will be replaced
	 * sequentially with the values in replacement.
	 * The last occurrence of search will be replaced with the 1st element in the
	 * replacement array, the 2nd last occurrence with the 2nd element and so on.
	 * Till the last element in replacement array or till there are no more
	 * occurrences of search in the new Str (whichever happens first). The same
	 * happens when search is an array and replacement is an array of arrays.
	 *
	 * @param  string|array  $search
	 * @param  string|array  $replace
	 * @param  string        $subject
	 * @param  string        $encoding
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function replaceLast($search, $replacement, &$count = 0)
	{
		if(is_array($search))
			$str = $this->replaceLastArray($search, $replacement, $this->str, $this->encoding, $count);
		else
			$str = $this->replaceLastString($search, $replacement, $this->str, $this->encoding, $count);

		return $this->getNew($str);
	}


	/**
	 * Replace the first occurrences of each $search in $subject with $replace.
	 * If $replace is an array, occurrences of each $search entry will be
	 * replaced sequentially with the values in $replace.
	 *
	 * @param  array         $search
	 * @param  string|array  $replace
	 * @param  string        $subject
	 * @param  string        $encoding
	 *
	 * @return string
	 */
	protected function replaceLastArray(array $search, $replace, $subject, $encoding, &$count)
	{
		if($repArray = is_array($replace) && $numReps = count($replace)){
			$replace = array_values($replace);
		}

		foreach (array_values($search) as $key => $value) {
			$replacement = !$repArray ? $replace : ($key >= $numReps ? '' : $replace[$key]);
			$subject = $this->replaceLastString($value, $replacement, $subject, $encoding, $count);
		}

		return $subject;
	}

	/**
	 * Replace the occurrences of $search in $subject with $replace. If $replace
	 * is an array, occurrences of $search will be replaced sequentially with
	 * the values in $replace.
	 *
	 * @param  string        $search
	 * @param  string|array  $replace
	 * @param  string        $subject
	 * @param  string        $encoding
	 *
	 * @return string
	 */
	protected function replaceLastString($search, $replace, $subject, $encoding, &$count)
	{
		if(is_array($replace))
			return $this->replaceLastSequentially($search, $replace, $subject, $encoding, $count);
		else
			return $this->replaceLastOne($search, $replace, $subject, $encoding, $count);
	}

	/**
	 * Replace the occurrences of $search in $subject sequentially with $replace.
	 *
	 * @param  string  $search
	 * @param  array   $replace
	 * @param  string  $subject
	 * @param  string  $encoding
	 *
	 * @return string
	 */
	protected function replaceLastSequentially($search, array $replace, $subject, $encoding, &$count)
	{
		$offset = null;
		$lenSearch = mb_strlen($search, $encoding);

		foreach ($replace as $value) {
			$subject = $this->replaceLastOne($search, $value, $subject, $encoding, $count, $offset, $lenSearch);
			if($offset === false)
				break;
		}
		return $subject;
	}


	/**
	 * Replace the first occurrence of $search in $subject with $replace.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @param  string  $encoding
	 * @param  int     &$offset
	 *
	 * @return string
	 */
	protected function replaceLastOne($search, $replace, $subject, $encoding, &$count, &$offset = null, $lenSearch = null)
	{
		$target = mb_substr($subject, 0, $offset, $encoding);
		$position = mb_strrpos($target, $search, 0, $encoding);

		if ($position === false){
			$offset = false;
			return $subject;
		}

		$lenSearch = is_null($lenSearch) ? mb_strlen($search, $encoding) : $lenSearch;

		$start = mb_substr($subject, 0, $position, $encoding);
		$end = $replace.mb_substr($subject, $position+$lenSearch, null, $encoding);

		$offset = -mb_strlen($end, $encoding);
		++$count;

		return $start.$end;
	}


	/**
	 * Get the singular form of the string if it's an English word.
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function singular()
	{
		return $this->getNew(Pluralizer::singular($this->str));
	}

	/**
	 * Returns TRUE if the string starts with any of the given needles, FALSE
	 * otherwise. By default, the comparison is case-sensitive, but can be made
	 * insensitive by setting $caseSensitive to FALSE.
	 *
	 * @param  string|array|iterable  $needles
	 * @param  bool                   $caseSensitive
	 *
	 * @return bool
	 */
	public function startsWith($needles, $caseSensitive = false)
	{
		$needles = Regex::quote($needles);

		if(is_array($needles))
			$needles = join(')|(?:\A', $needles);

		return $this->newRegex(
						"(?:\A{$needles})",
						($caseSensitive ? '' : Modifiers::CASELESS)
					)->matches($this);
	}

	/**
	 * Strip a substring from the beginning and end of the string.
	 * If $substring is empty or not provided, whitespaces on both ends of the
	 * string will be removed.
	 *
	 * If limit is provided, it will set the number of occurrences to be striped.
	 * If not, all occurrences will be striped.
	 *
	 * @param  string  $substring
	 * @param  int     $limit
	 * @param  bool    $caseSensitive
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function strip($substring = null, $limit = -1, $caseSensitive = true)
	{
		$pattern = $this->regexPatternForStrip($substring, $limit);

		$regex = $this->newRegex(
							"(?:\A{$pattern})|(?:{$pattern}\z)",
							($caseSensitive ? '' : Modifiers::CASELESS)
						);

		return $this->getNew($regex->replace("", $this));
	}

	/**
	 * Strip a substring from the beginning of the string. If $substring is empty
	 * or not provided, whitespaces at the beginning of the string will be removed.
	 *
	 * If limit is provided, it will set the number of occurrences to be striped.
	 * If not, all occurrences will be striped.
	 *
	 * @param  string  $substring
	 * @param  int     $limit
	 * @param  bool    $caseSensitive
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function stripLeft($substring = null, $limit = -1, $caseSensitive = true)
	{
		$pattern = $this->regexPatternForStrip($substring, $limit);

		$regex = $this->newRegex(
							"(?:\A{$pattern})",
							($caseSensitive ? '' : Modifiers::CASELESS)
						);

		return $this->getNew($regex->replace("", $this));
	}


	/**
	 * Strip a substring from the end of the string. If $substring is empty
	 * or not provided, whitespaces at the end of the string will be removed.
	 *
	 * If limit is provided, it will set the number of occurrences to be striped.
	 * If not, all occurrences will be striped.
	 *
	 * @param  string  $substring
	 * @param  int     $limit
	 * @param  bool    $caseSensitive
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function stripRight($substring = null, $limit = -1, $caseSensitive = true)
	{
		$pattern = $this->regexPatternForStrip($substring, $limit);

		$regex = $this->newRegex(
							"(?:{$pattern}\z)",
							($caseSensitive ? '' : Modifiers::CASELESS)
						);

		return $this->getNew($regex->replace("", $this));
	}

	/**
	 * Truncate the string to the given number of words. If $end is provided and
	 * the string is truncated, the value will be added to the end of the
	 * truncated words.
	 *
	 * @param  int     $words
	 * @param  string  $end
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function words($words = 100, $end = '')
	{
		$matches = $this->newRegex('^\s*+(?:\S++\s*+){1,'.$words.'}')->match($this);

		$trancated = isset($matches[0]) ? $this->getNew($matches[0]) : null;

		if (is_null($trancated) || $this->length() === $trancated->length()){
			return $this->getNew();
		}

		$trancated = $trancated->trimRight();
		$trancated->str = $trancated->str . $end;

		return $trancated;
	}



/***Case Conversions***/

	/**
	 * Converts all characters in the string to lower case.
	 * Alias for {@see \Stringy\Stringy::toLowerCase()}
	 *
	 * @uses  \Stringy\Stringy::toLowerCase()
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function lower()
	{
		return $this->toLowerCase();
	}

	/**
	 * Converts the first character of the string to lower case.
	 * Alias for {@see \Stringy\Stringy::lowerCaseFirst()}
	 *
	 * @uses  \Stringy\Stringy::toLowerCase()
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function lcfirst()
	{
		return $this->lowerCaseFirst();
	}

	/**
	 * Converts the first character of each word in the string to upper case.
	 * Alias for {@see \Stringy\Stringy::toTitleCase()}
	 *
	 * @uses  \Stringy\Stringy::toTitleCase()
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function titlecase()
	{
		return $this->toTitleCase();
	}


	/**
	 * Converts all characters in the string to upper case.
	 * Alias for {@see \Stringy\Stringy::toUpperCase()}
	 *
	 * @uses  \Stringy\Stringy::toUpperCase()
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function upper()
	{
		return $this->toUpperCase();
	}

	/**
	 * Converts the first character of the string to upper case.
	 * Alias for {@see \Stringy\Stringy::upperCaseFirst()}
	 *
	 * @uses  \Stringy\Stringy::upperCaseFirst()
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function ucfirst()
	{
		return $this->upperCaseFirst();
	}

	/**
	 * Converts the first character of each word in the string to upper case.
	 * Alias for {@see \Tea\Uzi\Str::toTitleCase()}.
	 *
	 * @uses \Tea\Uzi\Str::toTitleCase()
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function ucwords()
	{
		return $this->toTitleCase();
	}

	/**
	 * Convert the string to camel case.
	 * Alias for {@see \Stringy\Stringy::camelize()}
	 *
	 * @uses  \Stringy\Stringy::camelize()
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function camel()
	{
		return $this->camelize();
	}

	/**
	 * Convert the string to snake case.
	 *
	 * @uses  \Stringy\Stringy::delimit()
	 *
	 * @param  string  $delimiter
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function snake($delimiter = '_')
	{
		return $this->delimit($delimiter);
	}

	/**
	 * Convert the string to studly caps case.
	 * Alias for {@see \Stringy\Stringy::upperCamelize()}
	 *
	 * @uses  \Stringy\Stringy::upperCamelize()
	 *
	 * @return \Tea\Uzi\Str
	 */
	public function studly()
	{
		return $this->upperCamelize();
	}

/***End Case Conversions***/

	/**
	 * Get the appropriate regex pattern for stripping a string
	 */
	protected function regexPatternForStrip($substring, $limit)
	{
		$pattern = $substring ? Regex::quote($substring) : '\s';
		return  '(?:'.'(?:'.$pattern.')'. ($limit > 0 ? '{1,'.$limit.'}' : '+').')';
	}

	/**
	 * Build the string pattern used for $this->is() and $this->isAny() matches
	 *
	 * @param  bool $wildcards
	 * @param  bool $caseSensitive
	 * @return string
	 */
	protected function regexPatternForIs($wildcards, $caseSensitive)
	{
		$pattern = Regex::quote($this->str);

		if($wildcards)
			$pattern = str_replace('\*', '.*', $pattern);

		return $this->newRegex('\A'. $pattern . '\z', ($caseSensitive ?'': Modifiers::CASELESS));
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
	 * @return \Tea\Uzi\Str
	 */
	public function wrap($substring, $trim = true)
	{
		$str = $trim ? $this->trim($substring, true) : $this->str;
		return new static( $substring.$str.$substring, $this->encoding);
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

/* Alias Methods */


/* End Alias Methods */

	protected function toRegex($regex, $modifiers = '')
	{
		$regex = $regex instanceof RegularExpressionContract ? $regex : Regex::from($regex);
		$regex->addModifiers($modifiers.Modifiers::UTF8);
		return $regex;
	}

	protected function newRegex($pattern, $modifiers = '', $delimiter = null)
	{
		return Regex::create($pattern, $modifiers.Modifiers::UTF8, $delimiter);
	}

	protected function getNew($value = null, $encoding = null)
	{
		if(is_null($value) && func_num_args() === 0)
			$value = $this->str;
		return new static($value, ($encoding ?: $this->encoding));
	}

	/**
	 * Returns the replacements for the ascii() method.
	 *
	 * @return array An array of replacements.
	 */
	protected function charsArray()
	{
		static $charsArray;
		if (isset($charsArray)) return $charsArray;

		return $charsArray = array(
			'0'    => array('°', '₀', '۰'),
			'1'    => array('¹', '₁', '۱'),
			'2'    => array('²', '₂', '۲'),
			'3'    => array('³', '₃', '۳'),
			'4'    => array('⁴', '₄', '۴', '٤'),
			'5'    => array('⁵', '₅', '۵', '٥'),
			'6'    => array('⁶', '₆', '۶', '٦'),
			'7'    => array('⁷', '₇', '۷'),
			'8'    => array('⁸', '₈', '۸'),
			'9'    => array('⁹', '₉', '۹'),
			'a'    => array('à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ',
				'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å',
				'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ',
				'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά',
				'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ',
				'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا'),
			'b'    => array('б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'),
			'c'    => array('ç', 'ć', 'č', 'ĉ', 'ċ'),
			'd'    => array('ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ',
				'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'),
			'e'    => array('é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ',
				'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ',
				'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э',
				'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ'),
			'f'    => array('ф', 'φ', 'ف', 'ƒ', 'ფ'),
			'g'    => array('ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ'),
			'h'    => array('ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'),
			'i'    => array('í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į',
				'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ',
				'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ',
				'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი',
				'इ', 'ی'),
			'j'    => array('ĵ', 'ј', 'Ј', 'ჯ', 'ج'),
			'k'    => array('ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ',
				'ک'),
			'l'    => array('ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'),
			'm'    => array('м', 'μ', 'م', 'မ', 'მ'),
			'n'    => array('ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န',
				'ნ'),
			'o'    => array('ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ',
				'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő',
				'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό',
				'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ'),
			'p'    => array('п', 'π', 'ပ', 'პ', 'پ'),
			'q'    => array('ყ'),
			'r'    => array('ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'),
			's'    => array('ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ',
				'ſ', 'ს'),
			't'    => array('ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ',
				'თ', 'ტ'),
			'u'    => array('ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ',
				'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ',
				'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ'),
			'v'    => array('в', 'ვ', 'ϐ'),
			'w'    => array('ŵ', 'ω', 'ώ', 'ဝ', 'ွ'),
			'x'    => array('χ', 'ξ'),
			'y'    => array('ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ',
				'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'),
			'z'    => array('ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'),
			'aa'   => array('ع', 'आ', 'آ'),
			'ae'   => array('ä', 'æ', 'ǽ'),
			'ai'   => array('ऐ'),
			'at'   => array('@'),
			'ch'   => array('ч', 'ჩ', 'ჭ', 'چ'),
			'dj'   => array('ђ', 'đ'),
			'dz'   => array('џ', 'ძ'),
			'ei'   => array('ऍ'),
			'gh'   => array('غ', 'ღ'),
			'ii'   => array('ई'),
			'ij'   => array('ĳ'),
			'kh'   => array('х', 'خ', 'ხ'),
			'lj'   => array('љ'),
			'nj'   => array('њ'),
			'oe'   => array('ö', 'œ', 'ؤ'),
			'oi'   => array('ऑ'),
			'oii'  => array('ऒ'),
			'ps'   => array('ψ'),
			'sh'   => array('ш', 'შ', 'ش'),
			'shch' => array('щ'),
			'ss'   => array('ß'),
			'sx'   => array('ŝ'),
			'th'   => array('þ', 'ϑ', 'ث', 'ذ', 'ظ'),
			'ts'   => array('ц', 'ც', 'წ'),
			'ue'   => array('ü'),
			'uu'   => array('ऊ'),
			'ya'   => array('я'),
			'yu'   => array('ю'),
			'zh'   => array('ж', 'ჟ', 'ژ'),
			'(c)'  => array('©'),
			'A'    => array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ',
				'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą',
				'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ',
				'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ',
				'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ'),
			'B'    => array('Б', 'Β', 'ब'),
			'C'    => array('Ç','Ć', 'Č', 'Ĉ', 'Ċ'),
			'D'    => array('Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'),
			'E'    => array('É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ',
				'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ',
				'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э',
				'Є', 'Ə'),
			'F'    => array('Ф', 'Φ'),
			'G'    => array('Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'),
			'H'    => array('Η', 'Ή', 'Ħ'),
			'I'    => array('Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į',
				'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ',
				'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ'),
			'K'    => array('К', 'Κ'),
			'L'    => array('Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'),
			'M'    => array('М', 'Μ'),
			'N'    => array('Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'),
			'O'    => array('Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ',
				'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő',
				'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ',
				'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ'),
			'P'    => array('П', 'Π'),
			'R'    => array('Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'),
			'S'    => array('Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'),
			'T'    => array('Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'),
			'U'    => array('Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ',
				'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ',
				'Ǘ', 'Ǚ', 'Ǜ'),
			'V'    => array('В'),
			'W'    => array('Ω', 'Ώ', 'Ŵ'),
			'X'    => array('Χ', 'Ξ'),
			'Y'    => array('Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ',
				'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'),
			'Z'    => array('Ź', 'Ž', 'Ż', 'З', 'Ζ'),
			'AE'   => array('Ä', 'Æ', 'Ǽ'),
			'CH'   => array('Ч'),
			'DJ'   => array('Ђ'),
			'DZ'   => array('Џ'),
			'GX'   => array('Ĝ'),
			'HX'   => array('Ĥ'),
			'IJ'   => array('Ĳ'),
			'JX'   => array('Ĵ'),
			'KH'   => array('Х'),
			'LJ'   => array('Љ'),
			'NJ'   => array('Њ'),
			'OE'   => array('Ö', 'Œ'),
			'PS'   => array('Ψ'),
			'SH'   => array('Ш'),
			'SHCH' => array('Щ'),
			'SS'   => array('ẞ'),
			'TH'   => array('Þ'),
			'TS'   => array('Ц'),
			'UE'   => array('Ü'),
			'YA'   => array('Я'),
			'YU'   => array('Ю'),
			'ZH'   => array('Ж'),
			' '    => array("\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81",
				"\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84",
				"\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87",
				"\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A",
				"\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"),
			);
	}

}