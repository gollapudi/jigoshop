<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Product;

/**
 * Products service interface.
 *
 * @package Jigoshop\Service
 * @author Amadeusz Starzykiewicz
 */
interface ProductServiceInterface extends ServiceInterface
{
	/**
	 * Adds new type to managed types.
	 *
	 * @param $type string Unique type name.
	 * @param $class string Class name.
	 * @throws \Jigoshop\Exception When type already exists.
	 */
	public function addType($type, $class);

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return Product
	 */
	public function find($id);

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Product Item found.
	 */
	public function findForPost($post);

	/**
	 * Finds item specified by state.
	 *
	 * @param array $state State of the product to be found.
	 * @return Product|Product\Purchasable Item found.
	 */
	public function findForState(array $state);

	/**
	 * Finds items by trying to match their name.
	 *
	 * @param $name string Post name to match.
	 * @return array List of matched products.
	 */
	public function findLike($name);

	/**
	 * @return array List of products that are out of stock.
	 */
	public function findOutOfStock();

	/**
	 * @param $threshold int Threshold where to assume product is low in stock.
	 * @return array List of products that are low in stock.
	 */
	public function findLowStock($threshold);

	/**
	 * @param Product $product Product to find thumbnails for.
	 * @return array List of thumbnails attached to the product.
	 */
	public function getThumbnails(Product $product);
}
