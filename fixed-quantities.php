<?php
/*
Plugin Name: Fixed Quantities
Plugin URI: 
Description: Add an attribute with the slug 'fixed-quantity' to your WooCommerce attributes to be able to sell products in batches of fixed quantities.

Version: 0.1.0
Requires at least: 3.3

Author: Pronamic
Author URI: http://pronamic.eu/

Text Domain: 
Domain Path: 

License: GPLv2

GitHub URI: https://github.com/pronamic/wp-parcelware
*/

/**
 * Called on WooCommerce's 'woocommerce_add_to_cart' action. Checks if the quantity of a product is
 * one, if it is and it's a bundled product, that means the product was added via the add to cart button
 * on the product list page and its quantity needs to be set to the predefined quantity.
 *
 * @param string $cart_item_key
 * @param int $product_id
 * @param int $quantity
 */
function fixed_quantities_woocommerce_add_to_cart_button( $cart_item_key, $product_id, $quantity ) {
	if( $quantity > 1 )
		return;

	$term = reset( get_the_terms( $product_id, 'pa_fixed-quantity' ) );
	$qty = $term->slug;

	if( isset( $GLOBALS[ 'woocommerce' ]->cart->cart_contents[ $cart_item_key ] ) && is_array( $GLOBALS[ 'woocommerce' ]->cart->cart_contents[ $cart_item_key ] ) &&
		is_numeric( $qty) ) {

		$GLOBALS[ 'woocommerce' ]->cart->cart_contents[ $cart_item_key ][ 'quantity' ] = $qty;
	}
}
add_action('woocommerce_add_to_cart', 'fixed_quantities_woocommerce_add_to_cart_button', 10, 3);

/**
 * When on a single post, see if we are using fixed quantities. If so, localize the
 * fixed quantity so the script can use it.
 */
function fixed_quantities_localize_fixed_quantity_product() {
	global $post;

	if( $post == null )
		return;

	$term = reset(get_the_terms($post->ID, 'pa_fixed-quantity'));

	if( ! is_numeric( $term->slug ) )
		return;
	
	wp_register_script( 'fixed-quantity-script', plugins_url('', __FILE__) . '/fixed-quantity-script.js', array( 'jquery' ), null, true );
	wp_localize_script( 'fixed-quantity-script', 'fixed_quantity', array( 'fixed_quantity' => $term->slug ) );

	add_action('wp_enqueue_scripts', 'fixed_quantities_enqueue', 99);
}
add_action( 'wp', 'fixed_quantities_localize_fixed_quantity_product' );

/**
 * On the cart page, multiple products could be available, therefore we need
 * to load multiple fixed quantities. If a product has no fixed quantity, one
 * is passed as default substitute value.
 */
function fixed_quantities_localize_fixed_quantities_cart() {
	$cart_contents = $GLOBALS[ 'woocommerce' ]->cart->cart_contents;

	if( ! is_array( $cart_contents ) || empty( $cart_contents ) )
		return;

	$fixed_quantities = array();

	foreach( $cart_contents as $key => $product ) {

		$term = reset(get_the_terms($product['product_id'], 'pa_fixed-quantity'));

		if( ! is_numeric( $term->slug ) )
			$fixed_quantities[ $key ] = 1;
		else
			$fixed_quantities[ $key ] = $term->slug;
	}
	
	wp_register_script( 'fixed-quantity-script', plugins_url('', __FILE__) . '/fixed-quantity-script.js', array( 'jquery' ), null, true );
	wp_localize_script( 'fixed-quantity-script', 'fixed_quantities', array( 'fixed_quantities' => $fixed_quantities ) );

	add_action('wp_enqueue_scripts', 'fixed_quantities_enqueue', 99);
}
add_action( 'wp', 'fixed_quantities_localize_fixed_quantities_cart' );

/**
 * Enqueue fixed quantity scripts
 */
function fixed_quantities_enqueue() {
	wp_enqueue_script( 'fixed-quantity-script' );
}