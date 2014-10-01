<?php

namespace Jigoshop\Frontend;

use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Cart implements \Serializable
{
	/** @var string */
	private $id;
	private $items = array();
	private $total = 0.0;

	public function __construct($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string Cart ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Adds item to the cart.
	 *
	 * If item is already present - increases it's quantity.
	 *
	 * @param Product|Product\Purchasable $product Product to add to cart.
	 * @param $quantity int Quantity of products to add.
	 * @throws Exception On error.
	 */
	public function addItem(Product $product, $quantity)
	{
		if ($product === null || $product->getId() === 0) {
			throw new Exception(__('Product not found', 'jigoshop'));
		}

		if (!($product instanceof Product\Purchasable)) {
			throw new Exception(sprintf(__('Product of type "%s" cannot be added to cart', 'jigoshop'), $product->getType()));
		}

		if ($quantity <= 0) {
			throw new Exception(__('Quantity has to be positive number', 'jigoshop'));
		}

		if (isset($this->items[$product->getId()])) {
			$this->items[$product->getId()]['quantity'] += $quantity;
		} else {
			$this->items[$product->getId()] = array(
				'item' => $product->getState(),
				'quantity' => $quantity,
			);
		}

		$this->total += $quantity * $product->getPrice();
	}

	/**
	 * Removes item from cart.
	 *
	 * @param Product $product Product to remove from cart.
	 * @return bool Is item removed?
	 */
	public function removeItem(Product $product)
	{
		if (isset($this->items[$product->getId()])) {
			unset($this->items[$product->getId()]);
		}

		return true;
	}

	/**
	 * @return array List of items in the cart.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return float Current total value of the cart.
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize(array(
			'id' => $this->id,
			'items' => $this->items,
			'total' => $this->total,
		));
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->id = $data['id'];
		$this->items = $data['items'];
		$this->total = $data['total'];
	}
}
