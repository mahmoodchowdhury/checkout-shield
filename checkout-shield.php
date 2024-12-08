<?php
/*
 * Plugin Name: Checkout Shield: Block orders with spam email addresses.
 * Plugin URI: https://github.com/mahmoodchowdhury/checkout-shield
 * Description: A lightweight eCommerce plugin that blocks specific email addresses during checkout. Admins can manage blocked emails via the WordPress admin panel.
 * Version: 1.0.0
 * Author: Mahmood Chowdhury
 * Author URI: https://mahmoodchowdhury.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: checkout-shield
 * Domain Path: /languages
 */

// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Hook to add settings page under WooCommerce settings
add_action( 'admin_menu', 'checkout_shield_menu' );

function checkout_shield_menu() {
    add_submenu_page(
        'woocommerce',                  // Parent menu slug
        'Checkout Shield',              // Page title
        'Checkout Shield',              // Menu title
        'manage_options',               // Capability
        'checkout-shield',              // Menu slug
        'checkout_shield_settings_page' // Callback function to display settings page
    );
}

// Settings page for the plugin
function checkout_shield_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Checkout Shield', 'checkout-shield' ); ?></h1>
        
        <form method="post" action="options.php">
            <?php
            settings_fields( 'checkout_shield_group' );
            do_settings_sections( 'checkout-shield' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Blocked Email Addresses', 'checkout-shield' ); ?></th>
                    <td>
                        <textarea name="blocked_emails" rows="10" cols="50" class="large-text"><?php echo esc_textarea( get_option( 'blocked_emails' ) ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Enter each email address on a new line.', 'checkout-shield' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register plugin settings
add_action( 'admin_init', 'checkout_shield_settings' );

function checkout_shield_settings() {
    register_setting( 'checkout_shield_group', 'blocked_emails' );
}

// Hook to filter the email during checkout
add_filter( 'woocommerce_customer_get_billing_email', 'block_email_on_checkout', 10, 2 );

function block_email_on_checkout( $email, $customer ) {
    // Get blocked emails from settings
    $blocked_emails = explode( "\n", get_option( 'blocked_emails', '' ) );
    $blocked_emails = array_map( 'trim', $blocked_emails );

    // Check if the email is in the blocked list
    if ( in_array( $email, $blocked_emails ) ) {
        // Add an error notice only during checkout
        if ( is_checkout() && function_exists( 'wc_add_notice' ) ) {
            wc_add_notice( __( 'This email address is blocked. Please use a different one.', 'checkout-shield' ), 'error' );
        }

        // Prevent order creation by invalidating the email
        return ''; // Returns an empty email, effectively blocking the order
    }

    return $email;
}

