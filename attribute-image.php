<?php
/**
 * Plugin Name: Attribute Image Tab for WooCommerce
 * Plugin URI: https://github.com/jsballarini/Attribute-Image-Tab-for-WooCommerce
 * Description: Adds a new tab to WooCommerce products with a custom image.
 * Version: 0.0.6
 * Author: Juliano Ballarini
 * Author URI: https://github.com/jsballarini
 * Text Domain: attribute-image-tab-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add HPOS support
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Check if WooCommerce is active
function attribute_image_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>' . 
                 esc_html__('Attribute Image Tab requires WooCommerce to be installed and active.', 'attribute-image-tab-for-woocommerce') . 
                 '</p></div>';
        });
        return false;
    }
    return true;
}

// Add custom field in product edit
function attribute_image_add_custom_fields() {
    add_action('woocommerce_product_data_tabs', function($tabs) {
        $tabs['attribute_image'] = [
            'label' => esc_html__('Additional Attributes', 'attribute-image-tab-for-woocommerce'),
            'target' => 'attribute_image_data',
            'class' => ['hide_if_grouped'],
        ];
        return $tabs;
    });

    add_action('woocommerce_product_data_panels', function() {
        echo '<div id="attribute_image_data" class="panel woocommerce_options_panel">';
        
        wp_nonce_field('attribute_image_save_data', 'attribute_image_nonce');
        
        woocommerce_wp_text_input([
            'id' => '_custom_tab_title',
            'label' => esc_html__('New Tab Title', 'attribute-image-tab-for-woocommerce'),
            'placeholder' => esc_html__('Enter the new tab title', 'attribute-image-tab-for-woocommerce'),
            'desc_tip' => true,
            'description' => esc_html__('This title will be displayed in the product\'s new tab.', 'attribute-image-tab-for-woocommerce')
        ]);

        echo '<div class="form-field attribute-image-field">';
        echo ' ' . esc_html__('Tab Image', 'attribute-image-tab-for-woocommerce') . ' ';
        echo '<div class="image-preview-wrapper">';
        echo '<div class="custom-img-container" style="max-width:100px;"></div>';
        echo '</div>';
        echo '<input type="hidden" name="_custom_tab_image" class="custom-tab-image-id" value="">';
        echo '<button type="button" class="upload_image_button button">' . 
             esc_html__('Upload/Select Image', 'attribute-image-tab-for-woocommerce') . '</button>';
        echo '<button type="button" class="remove_image_button button" style="display:none">' . 
             esc_html__('Remove Image', 'attribute-image-tab-for-woocommerce') . '</button>';
        echo '</div>';
        
        echo '</div>';
    });
}

// Save custom fields
function attribute_image_save_custom_fields($post_id) {
    // Check if our nonce is set and verify it
    if (!isset($_POST['attribute_image_nonce'])) {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST['attribute_image_nonce']));
    if (!wp_verify_nonce($nonce, 'attribute_image_save_data')) {
        return;
    }

    // Save custom tab title if set
    if (isset($_POST['_custom_tab_title'])) {
        $title = sanitize_text_field(wp_unslash($_POST['_custom_tab_title']));
        update_post_meta($post_id, '_custom_tab_title', $title);
    }

    // Save custom tab image if set
    if (isset($_POST['_custom_tab_image'])) {
        $image_id = absint(wp_unslash($_POST['_custom_tab_image']));
        update_post_meta($post_id, '_custom_tab_image', $image_id);
    }
}

// Add custom tab in frontend
function attribute_image_add_custom_tab($tabs) {
    global $post;
    
    $tab_title = get_post_meta($post->ID, '_custom_tab_title', true);
    if (empty($tab_title)) {
        $tab_title = esc_html__('Additional Information', 'attribute-image-tab-for-woocommerce');
    }
    
    $image_id = get_post_meta($post->ID, '_custom_tab_image', true);
    if ($image_id) {
        $tabs['attribute_image_tab'] = [
            'title' => esc_html($tab_title),
            'priority' => 50,
            'callback' => 'attribute_image_tab_content'
        ];
    }
    
    return $tabs;
}

// Custom tab content
function attribute_image_tab_content() {
    global $post;
    
    $image_id = get_post_meta($post->ID, '_custom_tab_image', true);
    if ($image_id) {
        $image = wp_get_attachment_image($image_id, 'full', false, [
            'class' => 'attribute-image-tab-img',
            'loading' => 'lazy'
        ]);
        echo '<div class="attribute-image-tab-content">';
        echo wp_kses_post($image);
        echo '</div>';
    }
}

// Add necessary scripts
function attribute_image_admin_scripts() {
    wp_enqueue_media();
    
    wp_enqueue_script(
        'attribute-image-admin',
        plugins_url('js/admin.js', __FILE__),
        ['jquery'],
        '0.0.6',
        true
    );
}

// Initialize plugin
function attribute_image_init() {
    if (!attribute_image_check_woocommerce()) {
        return;
    }
    
    add_action('admin_enqueue_scripts', 'attribute_image_admin_scripts');
    add_action('woocommerce_process_product_meta', 'attribute_image_save_custom_fields');
    add_filter('woocommerce_product_tabs', 'attribute_image_add_custom_tab');
    attribute_image_add_custom_fields();
}

add_action('init', 'attribute_image_init'); 
