<?php
/**
 * Plugin Name: QuantityPro for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/custom-quantitypro-for-woocommerce/
 * Description: Add customizable plus and minus buttons to WooCommerce product quantity selectors
 * Version: 1.0.0
 * Author: Bonny Elangbam
 * Author URI: https://profile.wordpress.org/bonnyelangbam
 * License: GPLv3 or later
 * Requires at least: 4.6
 * Requires PHP: 7.2
 * Tested up to: 6.8
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Declare HPOS compatibility early
add_action('before_woocommerce_init', function () {
	if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) {
		Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
	}
});

class QuantityPro_WooCommerce
{
	private static $instance = null;

	public function __construct()
	{
		add_action('woocommerce_before_quantity_input_field', array($this, 'qpro_display_quantity_minus'));
		add_action('woocommerce_after_quantity_input_field', array($this, 'qpro_display_quantity_plus'));
		add_action('wp_enqueue_scripts', array($this, 'qpro_add_cart_quantity_plus_minus'));
	}

	public function qpro_display_quantity_plus()
	{
		echo '<button type="button" class="plus" name="plus">+</button>';
	}

	public function qpro_display_quantity_minus()
	{
		echo '<button type="button" class="minus" name="minus">−</button>';
	}

	public function qpro_add_cart_quantity_plus_minus()
	{
		if (!is_product() && !is_cart()) {
			return;
		}

		wp_register_style('quantitypro-for-woocommerce', plugins_url('quantitypro.css', __FILE__), array(), '1.0.0');
		wp_enqueue_style('quantitypro-for-woocommerce');

		wc_enqueue_js(
			"$(document).on( 'click', 'button.plus, button.minus', function(e) {
				e.preventDefault();
				e.stopPropagation();
				var qty_input = $(this).closest('.quantity').find('input.qty');
				var current_qty = parseFloat(qty_input.val());
				var max_qty = parseFloat(qty_input.attr('max')) || 999;
				var min_qty = parseFloat(qty_input.attr('min')) || 1;
				var step = parseFloat(qty_input.attr('step')) || 1;

				if ($(this).hasClass('plus')) {
					if (current_qty < max_qty) {
						current_qty = current_qty + step;
						if (current_qty > max_qty) {
							current_qty = max_qty;
						}
						qty_input.val(current_qty);
						qty_input.change();
					}
				} else if ($(this).hasClass('minus')) {
					if (current_qty > min_qty) {
						current_qty = current_qty - step;
						if (current_qty < min_qty) {
							current_qty = min_qty;
						}
						qty_input.val(current_qty);
						qty_input.change();
					}
				}
				return false;
			});"
		);
	}

	public static function get_instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

QuantityPro_WooCommerce::get_instance();
