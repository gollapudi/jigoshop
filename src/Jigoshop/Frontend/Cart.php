<?php

namespace Jigoshop\Frontend;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\Tax;
use WPAL\Wordpress;

class Cart
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var ProductServiceInterface  */
	private $productService;
	/** @var Tax */
	private $taxService;

	/** @var string */
	private $id;
	private $items = array();
	private $tax = array();
	private $total = 0.0;

	/**
	 * @param Wordpress $wp
	 * @param Options $options
	 * @param ProductServiceInterface $productService
	 * @param Tax $taxService
	 */
	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, Tax $taxService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->taxService = $taxService;

		foreach ($this->options->get('tax.classes') as $class) {
			$this->tax[$class['class']] = 0.0;
		}
	}

	/**
	 * @param string $id
	 * @param string $data
	 */
	public function initializeFor($id, $data = '')
	{
		$this->id = $id;

		if (!empty($data)) {
			$this->id = $data['id'];

			$items = unserialize($data['items']);
			if (is_array($items)) {
				foreach ($items as $item) {
					$product = $this->productService->findForState($item['item']);
					$this->total += $product->getPrice() * $item['quantity'];

					foreach ($product->getTaxClasses() as $class) {
						$this->tax[$class] = $this->taxService->get($product, $class);
					}

					$key = $this->getItemKey($product);
					$this->items[$key] = array(
						'item' => $product,
						'quantity' => $item['quantity'],
						'price' => $product->getPrice(),
					);
				}
			}
		}
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

		$key = $this->getItemKey($product);
		if (isset($this->items[$key])) {
			$this->items[$key]['quantity'] += $quantity;
		} else {
			foreach ($product->getTaxClasses() as $class) {
				$this->tax[$class] += $this->taxService->get($product, $class);
			}

			$tax = $this->taxService->calculate($product);
			$price = $product->getPrice();
			if ($this->options->get('tax.included')) {
				$price -= $tax;
			}

			$this->items[$key] = array(
				'item' => $product,
				'price' => $price,
				'tax' => $tax,
				'quantity' => $quantity,
			);
		}

		$this->total += $quantity * $product->getPrice();
	}

	/**
	 * Removes item from cart.
	 *
	 * @param string $key Item id to remove from cart.
	 * @return bool Is item removed?
	 */
	public function removeItem($key)
	{
		if (isset($this->items[$key])) {
			$this->total -= $this->items[$key]['price'] * $this->items[$key]['quantity'];
			unset($this->items[$key]);
		}

		return true;
	}

	public function getRemoveUrl($key)
	{
		return add_query_arg(array('action' => 'remove-item', 'item' => $key));
	}

	/**
	 * Updates quantity of selected item by it's key.
	 *
	 * @param $key string Item key in the cart.
	 * @param $quantity int Quantity to set.
	 * @throws Exception When product does not exists or quantity is not numeric.
	 */
	public function updateQuantity($key, $quantity)
	{
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop')); // TODO: Will be nice to get better error message
		}

		if (!is_numeric($quantity)) {
			throw new Exception(__('Quantity has to be numeric value', 'jigoshop'));
		}

		if ($quantity <= 0) {
			$this->removeItem($key);
			return;
		}

		$this->total -= $this->items[$key]['price'] * $this->items[$key]['quantity'];
		$this->total += $this->items[$key]['price'] * $quantity;
		$this->items[$key]['quantity'] = $quantity;
	}

	public function getItem($key)
	{
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop')); // TODO: Will be nice to get better error message
		}

		return $this->items[$key];
	}

	/**
	 * @return array List of items in the cart.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return bool Is the cart empty?
	 */
	public function isEmpty()
	{
		return empty($this->items);
	}

	/**
	 * @return float Current total value of the cart.
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Generates representation of current cart state.
	 *
	 * @return string the string representation of the cart or null
	 */
	public function getState()
	{
		return array(
			'id' => $this->id,
			'items' => serialize(array_map(function($item){
				/** @var $product Product */
				$product = $item['item'];
				return array(
					'item' => $product->getState(),
					'quantity' => $item['quantity'],
				);
			}, $this->items)),
		);
	}

	/**
	 * Returns unique key for product in the cart.
	 *
	 * @param $product Product|Product\Purchasable Product to get key for.
	 * @return string
	 */
	private function getItemKey($product)
	{
		return $product->getId();
	}
}