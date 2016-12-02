<?php
namespace Tea\Tests\Uzi\Specimens;

class HasToString
{
	protected $value;

	public function __construct($value = null)
	{
		$this->value = is_array($value) ? implode(' ', $value) : (string) $value;
	}

	public function __toString()
	{
		return (string) $this->value;
	}
}