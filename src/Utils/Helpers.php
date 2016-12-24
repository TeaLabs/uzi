<?php
namespace Tea\Uzi\Utils;

use Closure;
use ArrayAccess;
use InvalidArgumentException;
/**
*
*/
class Helpers
{
		/**
	 * Determine whether the mbstring module is loaded. If strict is false (the default),
	 * checks whether a polyfill for mbstring exists.
	 *
	 * @param  bool   $strict
	 * @return bool
	 */
	public static function mbstringLoaded($strict = false)
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
	public static function isStringable($value)
	{
		return is_string($value)
				|| is_null($value)
				|| (is_object($value) && method_exists($value, '__toString'))
				|| is_scalar($value);
	}

	/**
	 * Determine whether a value is iterable and not a string.
	 *
	 * @param  mixed   $value
	 * @return bool
	 */
	public static function isNoneStringIterable($value)
	{
		return is_array($value) || (is_iterable($value) && !static::isStringable($value));
	}

	public static function implodeIterable($iterable, $withKeys = false, $glue = null, $prefix = '[', $suffix = ']')
	{
		$results = [];
		foreach ($iterable as $key => $value) {
			$value = static::isNoneStringIterable($value)
					? static::implodeIterable($value, $withKeys, $glue, $prefix, $suffix)
					: (string) $value;
			$results[] = $withKeys ? "{$key} => {$value}" : $value;
		}

		if(is_null($glue)) $glue = ', ';
		return $prefix.join($glue, $results).$suffix;
	}

	public static function iterableToArray($iterable)
	{
		if(is_array($iterable))
			return $iterable;

		// if(!is_iterable($iterable)){
		// 	$type = is_object($iterable) ? get_class($iterable) : gettype($iterable);
		// 	throw new InvalidArgumentException("Iterable expected. {$type} given.");
		// 	return;
		// }

		$results = [];
		foreach ($iterable as $key => $value) {
			$results[$key] = $value;
		}
		return $results;
	}

	public static function toArray($object)
	{
		if(Helpers::isStringable($object) || !is_iterable($object))
			return (array) $object;

		return static::iterableToArray($object);
	}

	public static function toIterable($object)
	{
		if(static::isStringable($object) || !is_iterable($object))
			$object = (array) $object;

		return $object;
	}

	public static function isArrayAccessible($object)
	{
		return is_array($object) || $object instanceof ArrayAccess;
	}

	public static function value($object)
	{
		return $object instanceof Closure ? $object() : $object;
	}

	public static function iterFirst($array, callable $callback = null, $default = null)
	{
		if (is_null($callback)) {
			if (empty($array)) {
				return static::value($default);
			}

			foreach ($array as $item) {
				return $item;
			}
		}

		foreach ($array as $key => $value) {
			if (call_user_func($callback, $value, $key)) {
				return $value;
			}
		}

		return static::value($default);
	}

	public static function type($value)
	{
		return is_object($object) ? get_class($value) : gettype($value);
	}
}