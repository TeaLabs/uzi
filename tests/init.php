<?php

if(!function_exists('topline')):

function topline($return = false)
{
	static $line = "\n";
	$value = $line;
	$line = "";
	if($return)
		return $value;

	echo $value;
}

endif;

if(!function_exists('format_buffer')):
function format_buffer($output)
{
	$output = rtrim($output, "\n");
	return $output."\n~~~~~~~~~\n";
}
endif;


if(!function_exists('buffer')):
function buffer($chunk_size = 0, $flags = PHP_OUTPUT_HANDLER_CLEANABLE ^ PHP_OUTPUT_HANDLER_REMOVABLE)
{
	ob_start('format_buffer', $chunk_size, $flags);
	ob_implicit_flush(false);
}
endif;

if(!function_exists('xbuffer')):
function xbuffer($return = false, $clean = true)
{
	if($clean)
		$buffer = ob_get_clean();
	else
		$buffer = ob_get_contents();

	$buffer = rtrim($buffer)."\n";

	if($return)
		return $buffer;

	echo $buffer;
}
endif;


if(!function_exists('dump'))
{
	function dump()
	{
		$items = func_get_args();
		buffer();
		// echo "\n";
		if(count($items) > 0 && ((is_string($items[0])) || (is_object($items[0]) && method_exists($items[0], '__toString'))))
			echo array_shift($items)." ";

		if(count($items)){
			echo "<<\n  ";
			buffer();
			call_user_func_array('var_dump', $items);
			$output = str_replace("\n", "\n  ", xbuffer(true));
			echo $output;
			echo ">>";
		}
		xbuffer();
	}
}

if(!function_exists('pprint')){
	function pprint($k, $v = NOTHING, $l=5)
	{
		static $started=false;
		$out = "";
		if(!$started){
			$out .= "\n";
			$started = true;
		}

		$out .= "\n";
		$pad = ($l - strlen($k)) > 1 ? str_repeat(' ', ($l - strlen($k))) : '';
		$out .= "{$k}{$pad} ";
		if($v !== NOTHING){
			$out .= ": ";
			$out .= is_scalar($v) ? var_export($v, true) : print_r($v, true);
			// var_dump($v);
		}
		$out .= "\n";
		// $out .= "\n";
		echo $out;
	}
}

echo "\n";
