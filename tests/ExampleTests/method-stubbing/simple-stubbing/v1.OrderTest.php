<?php


/**
 * An item that is going to make me rich.
 */
interface Item
{
	/**
	 * @return money
	 */
	public function getPrice();
}

/**
 * A customer's cart that will contain items that are going to make me rich.
 */
class ShoppingCart
{
	private $items = array();

	/**
	 * Adds an item to the customer's order
	 * @param Item $item
	 */
	public function addItem(Item $item)
	{
		$this->items[] = $item;
	}

	/**
	 * Returns the current sub total of the customer's order
	 * @return money
	 */
	public function getSubTotal()
	{
		$total = 0;

		foreach ($this->items as $item)
		{
			$total += $item->getPrice();
		}

		return $total;
	}
}


class ShoppingCartTest extends PHPUnit_Framework_TestCase
{
	private $shoppingCart;

	private $item1;

	private $item2;

	private $item3;

	public function setUp()
	{
		$this->item1 = Phake::mock('Item');
		$this->item2 = Phake::mock('Item');
		$this->item3 = Phake::mock('Item');

		Phake::when($this->item1)->getPrice()->thenReturn(100);
		Phake::when($this->item2)->getPrice()->thenReturn(200);
		Phake::when($this->item3)->getPrice()->thenReturn(300);

		$this->shoppingCart = new ShoppingCart();
		$this->shoppingCart->addItem($this->item1);
		$this->shoppingCart->addItem($this->item2);
		$this->shoppingCart->addItem($this->item3);
	}

	public function testGetSub()
	{
		$this->assertEquals(600, $this->shoppingCart->getSubTotal());
	}
}
?>