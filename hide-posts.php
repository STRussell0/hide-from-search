<?php
/*
Plugin Name: Hide Posts From Search
Plugin URI: 
Description: This plugin will hide posts from search results within your site search as well as giving an option to hide from search engines.
Version: 1.0
Author: Stephen Russell
Author URI:
*/

if (!defined('ABSPATH')) exit;

// Add menu page

function hps_add_admin_menu() {
    add_management_page(
        'Hide Posts Controls',     // Page title
        'Hide Posts Controls',           // Menu title
        'manage_options',               // Capability
        'hps_post_type_controls',       // Menu slug
        'hps_post_type_controls_html'   // Function that outputs the page HTML
    );
}

// Menu page options

function hps_post_type_controls_html() {
    // Handle form submission
    if (isset($_POST['submit'])) {
        check_admin_referer('hps_save_post_types');

        $selected_post_types = isset($_POST['hps_post_types']) ? $_POST['hps_post_types'] : [];
        $selected_post_types = array_map('sanitize_text_field', $selected_post_types);
        update_option('hps_enabled_post_types', $selected_post_types);
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Fetch all public post types
    $post_types = get_post_types(array('public' => true), 'objects');
    $enabled_post_types = get_option('hps_enabled_post_types', []);

    include 'includes/post-type-controls.php';
}

add_action('admin_menu', 'hps_add_admin_menu');

// Hook into the 'add_meta_boxes' action
add_action('add_meta_boxes', 'hps_add_custom_box');

// Hook into the 'save_post' action
add_action('save_post', 'hps_save_postdata');

// Adds a box to the main column on the Post and Page edit screens
function hps_add_custom_box() {
    $enabled_post_types = get_option('hps_enabled_post_types', []);
    if (empty($enabled_post_types)) return;

    foreach ($enabled_post_types as $type) {
        add_meta_box(
            'hps_sectionid',
            'Hide Posts Control',
            'hps_custom_box_html',
            $type,
            'side',
            'low'
        );
    }
}

// HTML for the meta box
function hps_custom_box_html($post) {
    // Use nonce for verification
    wp_nonce_field(plugin_basename(__FILE__), 'hps_nonce');

    // Use get_post_meta to retrieve an existing value from the database and use the value for the form
    $hide_site_search = get_post_meta($post->ID, 'hide_from_site_search', true);
    $hide_search_engines = get_post_meta($post->ID, 'hide_from_search_engines', true);

    include 'includes/meta-box-html.php';
}

// When the post is saved, save plugin data
function hps_save_postdata($post_id) {
    // Check if our nonce is set and verify it.
    if (!isset($_POST['hps_nonce']) || !wp_verify_nonce($_POST['hps_nonce'], plugin_basename(__FILE__)))
        return;

    // Check if the user has permission to edit the post.
    if (!current_user_can('edit_post', $post_id))
        return;

    // Save or delete the meta field
    $hide_site_search = (isset($_POST['hide_from_site_search']) && $_POST['hide_from_site_search'] === 'yes') ? 'yes' : 'no';
    update_post_meta($post_id, 'hide_from_site_search', $hide_site_search);

    $hide_search_engines = (isset($_POST['hide_from_search_engines']) && $_POST['hide_from_search_engines'] === 'yes') ? 'yes' : 'no';
    update_post_meta($post_id, 'hide_from_search_engines', $hide_search_engines);
}

// Hide from search functionality

function hps_modify_search_query($query) {
    if ($query->is_search() && !is_admin()) {
        $meta_query = $query->get('meta_query') ?: [];
        $meta_query[] = array(
            'key'     => 'hide_from_site_search',
            'value'   => 'yes',
            'compare' => '!=',
        );
        $query->set('meta_query', $meta_query);
    }
}

add_action('pre_get_posts', 'hps_modify_search_query');

function hps_add_noindex_meta_tag() {
    if (is_single() || is_page()) {
        global $post;
        if (get_post_meta($post->ID, 'hide_from_search_engines', true) === 'yes') {
            echo '<meta name="robots" content="noindex, follow">';
        }
    }
}

add_action('wp_head', 'hps_add_noindex_meta_tag');

?>