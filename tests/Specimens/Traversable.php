<?php
namespace Tea\Tests\Uzi\Specimens;

use ArrayIterator;
use IteratorAggregate;

class Traversable implements IteratorAggregate
{
	protected $items;

	public function __construct($items = null)
	{
		$this->items = (array) $items;
	}

	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}

}