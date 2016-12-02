<?php
namespace Tea\Tests\Uzi\Specimens;

class Object
{
	protected $value;

	public function __construct($value = null)
	{
		$this->value = $value;
	}

}