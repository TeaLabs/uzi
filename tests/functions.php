<?php

function jsFormat($object, $flags = 0)
{
	return json_encode($object, $flags|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}

function jsObject($object, $flags = 0)
{
	$flags = is_array($object) || is_object($object) ? $flags|JSON_FORCE_OBJECT : $flags;
	return str_replace("\n", "\n   ", jsFormat($object, $flags));
}

function hr($style = null, $size = null, $return = false)
{
	$line = "\n".str_repeat(($style ?: '-'), ($size ?: 80));
	if($return) return $line;
	echo $line;
}