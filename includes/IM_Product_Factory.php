<?php
namespace Hellodev\InventoryManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Product Factory Class
 *
 * The WooCommerce Warehouses product factory creating the right product object.
 *
 * @class 		IM_Product_Factory
 * @package		Hellodev\InventoryManager
 * @category	Class
 * @author 		WooThemes, hellodev.us
 */
class IM_Product_Factory {

	/**
	 * get_product function.
	 *
	 * @param bool $the_product (default: false)
	 * @param array $args (default: array())
	 * @return WC_Product|bool false if the product cannot be loaded
	 */
	public function get_product( $the_product = false, $args = array() ) {
		$the_product = $this->get_product_object( $the_product );

		if ( ! $the_product ) {
			return false;
		}

		$classname = $this->get_product_class( $the_product, $args );

		if($classname == "\WC_Product_Variation") {
		    $classname = "Hellodev\InventoryManager\IM_Product_Variation";
		} else if($classname == "\WC_Product_Variable") {
		    $classname = "Hellodev\InventoryManager\IM_Product_Variable";
		} else if($classname == "\WC_Product_Simple") {
		    $classname = "Hellodev\InventoryManager\IM_Product_Simple";
		}

		if ( ! class_exists( $classname ) ) {
		    $classname = "\WC_Product_Simple";
		}

		return new $classname( $the_product, $args );
	}

	/**
	 * Create a WC coding standards compliant class name e.g. WC_Product_Type_Class instead of WC_Product_type-class.
	 * @param  string $product_type
	 * @return string|false
	 */
	private function get_classname_from_product_type( $product_type ) {
		return $product_type ? 'WC_Product_' . implode( '_', array_map( 'ucfirst', explode( '-', $product_type ) ) ) : false;
	}

	/**
	 * Get the product class name.
	 * @param  WP_Post $the_product
	 * @param  array $args (default: array())
	 * @return string
	 */
	private function get_product_class( $the_product, $args = array() ) {
		$product_id = absint( $the_product->ID );
		$post_type  = $the_product->post_type;

		if ( 'product' === $post_type ) {
			if ( isset( $args['product_type'] ) ) {
				$product_type = $args['product_type'];
			} else {
				$terms        = get_the_terms( $product_id, 'product_type' );
				$product_type = ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';
			}
		} elseif( 'product_variation' === $post_type ) {
			$product_type = 'variation';
		} else {
			$product_type = false;
		}

		$classname = $this->get_classname_from_product_type( $product_type );

		if(!empty($classname)) {
			$classname = "\\" . $classname;
		}

		// Filter classname so that the class can be overridden if extended.
		return apply_filters( 'woocommerce_product_class', $classname, $product_type, $post_type, $product_id );
	}

	/**
	 * Get the product object.
	 * @param  mixed $the_product
	 * @uses   WP_Post
	 * @return WP_Post|bool false on failure
	 */
	private function get_product_object( $the_product ) {
		if ( false === $the_product ) {
			$the_product = $GLOBALS['post'];
		} elseif ( is_numeric( $the_product ) ) {
			$the_product = get_post( $the_product );
		} elseif ( $the_product instanceof \WC_Product ) {
			$the_product = get_post( $the_product->id );
		} elseif ( ! ( $the_product instanceof \WP_Post ) ) {
			$the_product = false;
		}

		return apply_filters( 'woocommerce_product_object', $the_product );
	}
}
