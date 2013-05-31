<?php
/*
Plugin Name: WooCommerce Fixed Quantities
Plugin URI: 
Description: Add an attribute with the slug 'fixed-quantity' to your WooCommerce attributes to be able to sell products in batches of fixed quantities.

Version: 0.1.0
Requires at least: 3.3

Author: Pronamic
Author URI: http://pronamic.eu/

Text Domain: 
Domain Path: 

License: GPLv2

GitHub URI: https://github.com/pronamic/wp-woocommerce-fixed-quantities
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
	if ( $quantity > 1 )
		return;

	// Get the product's fixed quantity
	$qty = null;
	$terms = get_the_terms( $product_id, 'pa_fixed-quantity' );
	if ( is_array( $terms ) ) {
		$term = reset( $terms );

		$qty = $term->slug;
	}

	// When the item is in the cart and the retrieved fixed quantity is numeric, alter the cart item's quantity to the fixed quantity. 
	if ( isset( $GLOBALS['woocommerce']->cart->cart_contents[ $cart_item_key ] ) && is_array( $GLOBALS['woocommerce']->cart->cart_contents[ $cart_item_key ] ) &&
		is_numeric( $qty ) ) {

		$GLOBALS['woocommerce']->cart->cart_contents[ $cart_item_key ]['quantity'] += $qty - ($GLOBALS['woocommerce']->cart->cart_contents[ $cart_item_key ]['quantity'] % $qty);
	}
}

add_action( 'woocommerce_add_to_cart', 'fixed_quantities_woocommerce_add_to_cart_button', 10, 3 );

/**
 * When on a single post, see if we are using fixed quantities. If so, localize the
 * fixed quantity so the script can use it.
 */
function fixed_quantities_localize_fixed_quantity_product() {
	global $post;

	if ( $post == null )
		return;

	// Get the product's fixed quantity, return if it's not set
	$terms = get_the_terms( $post->ID, 'pa_fixed-quantity' );
	$term = null;
	if ( is_array( $terms ) ) {
		$term = reset( $terms );
	} else {
		return;
	}
	
	if ( ! is_numeric( $term->slug ) )
		return;
		
	// Get product's backorders setting
	$backorders = ( get_post_meta( $post->ID, '_backorders', true ) != 'no' ) ? true : false;
	
	// Get variations to get their stock
	$variations = get_children( array(
		'post_parent' => $post->ID,
		'numberposts' => -1,
		'post_type'   => 'product_variation'
	) );
	
	// Loop through variations and get their stock
	$stock = array();
	if ( is_array( $variations ) ) {
		foreach ( $variations as $variation ) {
			// Get post meta and store it in the stock array referenced by its variation ID
			$stock[ $variation->ID ] = get_post_meta( $variation->ID, '_stock', true );
		}
	}
	
	// Register and enqueue fixed quantities script. Enqueueing is done as late in 'wp_enqueue_scripts' as possible.
	wp_register_script(
		'fixed-quantity-script',
		plugins_url( 'fixed-quantity-script.js', __FILE__ ),
		array( 'jquery' ),
		null,
		true
	);

	add_action( 'wp_enqueue_scripts', 'fixed_quantities_enqueue', 99 );
	
	// Make fixed quantity, backorder setting and stock variables available to the script
	wp_localize_script(
		'fixed-quantity-script',
		'Fixed_Quantities',
		array(
			'step'       => $term->slug,
			'stock'      => $stock,
			'backorders' => $backorders
		)
	);
}

add_action( 'wp', 'fixed_quantities_localize_fixed_quantity_product' );

/**
 * On the cart page, multiple products could be available, therefore multiple fixed quantities
 * need to be loaded. If a product has no fixed quantity, the default qunatity box will be shown.
 */
function fixed_quantities_localize_fixed_quantities_cart() {
	// Cart contents
	$cart_contents = null;
	if ( isset( $GLOBALS['woocommerce'], $GLOBALS['woocommerce']->cart ) )
		$cart_contents = $GLOBALS['woocommerce']->cart->cart_contents;

	// Exit when no cart contents are available
	if ( ! is_array( $cart_contents ) || empty( $cart_contents ) )
		return;

	// Loop through the cart's products and place each product's post-meta in the fixed quantities array
	$fixed_quantities = array();
	foreach ( $cart_contents as $key => $product ) {
		// Get the fixed quantity term
		$term = reset( get_the_terms( $product['product_id'], 'pa_fixed-quantity' ) );
		
		// Get stock
		$stock = $product[ 'data' ]->stock;
		
		// Get backorders setting
		$backorders = ( get_post_meta( $product['product_id'], '_backorders', true ) != 'no' ) ? true : false;
		
		// Add values to array
		$fixed_quantities[ $key ] = array(
			'step'       => $term->slug,
			'stock'      => $stock,
			'backorders' => $backorders
		);
	}
	
	// Register and enqueue fixed quantities script. Enqueueing is done as late in 'wp_enqueue_scripts' as possible.
	wp_register_script(
		'fixed-quantity-script',
		plugins_url( 'fixed-quantity-script.js', __FILE__ ),
		array( 'jquery' ),
		null,
		true
	);

	add_action( 'wp_enqueue_scripts', 'fixed_quantities_enqueue', 99 );
	
	// Make fixed quantity, backorder setting and stock variables available to the script
	wp_localize_script(
		'fixed-quantity-script',
		'Fixed_Quantities_Cart',
		$fixed_quantities
	);
}

add_action( 'wp', 'fixed_quantities_localize_fixed_quantities_cart' );

/**
 * Enqueues fixed quantity scripts
 */
function fixed_quantities_enqueue() {
	wp_enqueue_script( 'fixed-quantity-script' );
}
