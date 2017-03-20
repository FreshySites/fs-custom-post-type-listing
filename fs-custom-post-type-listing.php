<?php
/**
 * Plugin Name: FS Custom Post Type Listing
 * Description: Creates new custom post type for LISTINGS and new listing taxonomy CATEGORIES and TAGS.
 * Version: 1.0
 * Author: FreshySites
 * Author URI: https://freshysites.com
 * License: GPL2
 **/

// Create a new category called 'Category' for our listings.
if( ! function_exists('fs_define_listing_category_taxonomy')) :
    function fs_define_listing_category_taxonomy()
    {
        $labels = array( // these labels change the text in the WordPress dashboard to match your taxonomy name
            'name' => 'Categories',
            'singular_name' => 'Category',
            'search_items'  => 'Search Categories',
            'all_items'     => 'All Categories',
            'parent_item'   => 'Parent Category:',
            'edit_item'     => 'Edit Category:',
            'update_item'   => 'Update Category',
            'add_new_item'  => 'Add New Category',
            'new_item_name' => 'New Categories Name',
            'menu_name'     => 'Categories',
            'view_item'     => 'View Categories'
        );

        $args = array(
            'labels'       => $labels, //reference to the labels array above
            'hierarchical' => true, // whether a new instance of this taxonomy can have a parent
            'query_var'    => true // whether you can use query variables in the URL to access the new post types
        );

        // Tell WordPress about our new taxonomy and assign it to a post type
        register_taxonomy( 'listing_category', 'listing', $args );
    }
endif;

// Call our new taxonomy function
add_action('init', 'fs_define_listing_category_taxonomy');

// Add tags to our listings
if( ! function_exists('fs_define_listing_tag_taxonomy')) :
    function fs_define_listing_tag_taxonomy()
    {
        $labels = array( // these labels change the text in the WordPress dashboard to match your taxonomy name
            'name'          => 'Tags',
            'singular_name' => 'Tag',
            'search_items'  => 'Search Tags',
            'popular_items' => ( 'Popular Tags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'all_items'     => 'All Tags',
            'edit_item'     => 'Edit Tag:',
            'update_item'   => 'Update Tag',
            'add_new_item'  => 'Add New Tag',
            'new_item_name' => 'New Tag Name',
            'menu_name'     => 'Tags',
            'view_item'     => 'View Tags'
        );

        $args = array(
            'labels'       => $labels, //reference to the labels array above
            'hierarchical' => false, // whether a new instance of this taxonomy can have a parent
            'query_var'    => true // whether you can use query variables in the URL to access the new post types
        );

        // Tell WordPress about our new taxonomy and assign it to a post type
        register_taxonomy( 'listing_tag', 'listing', $args );

}
endif;

// Call our new taxonomy function
add_action('init', 'fs_define_listing_tag_taxonomy');

// Create our new post type
if( ! function_exists('fs_register_listings')) :
    function fs_register_listings()
    {
        $labels = array( // these labels change the text in the WordPress dashboard and other places to match your custom post type name
            'name'               => 'Listings',
            'singular_name'      => 'Listing',
            'add_new'            => 'Add New Listing',
            'add_new_item'       => 'Add New Listing',
            'edit_item'          => 'Edit Listing',
            'new item'           => 'New Listing',
            'all_items'          => 'All Listings',
            'view_item'          => 'View Listing',
            'search_items'       => 'Search Listings',
            'not_found'          => 'No listings found',
            'not_found_in_trash' => 'No listings found in Trash',
            'menu_name'          => 'Listings'
        );

        $args = array(
            'labels'      => $labels, // reference to the labels array above
            'public'      => true, // whether the post type is available in the admin dashboard or front-end of site
            'taxonomies'  => array( 'Category', 'Tag'), // currently set to the Category taxonomy we created in the function above. You could leave blank for none or
            'rewrite'     => array( 'slug' => 'listing'), // base URL to use for your post type
            'hierarchical'=> false, // whether a new instance of this post type can have a parent. 'page-attributes' must be added to the supports array below for this to work.
            'has_archive' => false, // enables archive page for post type. Copy page template from theme and rename archive-listing.php
            'supports'    => array( // this array defines what meta boxes appear when adding/editing the post type
                'title',
                'editor',
                'thumbnail',
                'custom-fields',
                'comments',
                'excerpt',
                'revisions'
            ),
            'menu_icon' => 'dashicons-location', // sets the icon to display in the menu
            'menu_position' => 5, // position in the menu; the higher the number, the lower the position
        );

        // Tell WordPress about our new post type
        register_post_type( 'listing', $args );

    }

endif;

// Call our new post type function
add_action( 'init', 'fs_register_listings' );

// Add the Divi Page Builder to the new post type
function my_et_builder_post_types( $post_types ) {
    $post_types[] = 'listing';

    return $post_types;
}
add_filter( 'et_builder_post_types', 'my_et_builder_post_types' );

/*
 * REGISTER STYLES
 * This function and following action can be removed if you copy the single-project.php file from
 * the parent Divi folder, place it in your child-theme folder, and rename it single-listing.php
 * (or single-[INSERT YOUR POST TYPE].PHP)
 */
 /* 
 function register_fs_custom_post_type_listing_styles() {

    wp_register_style('fs-custom-post-type-listing-css', plugin_dir_url( __FILE__ ) . 'css/style.css', false, '1.0.0' );
    wp_enqueue_style( 'fs-custom-post-type-listing-css' );

}
add_action( 'wp_enqueue_scripts', 'register_fs_custom_post_type_listing_styles' );
*/

// Force dedicated template
function include_template_function( $template_path ) {
    if ( get_post_type() == 'listing' ) {
        if ( is_single() ) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ( $theme_file = locate_template( array ( 'single-listing.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . 'single-listing.php';
            }
        }
    }
    return $template_path;
}
add_filter( 'template_include', 'include_template_function', 1 );

// This is required for pretty links to custom post types to work
function fs_listing_cpt_install() {

    fs_define_listing_category_taxonomy();
    fs_define_listing_tag_taxonomy();
    fs_register_listings();
    flush_rewrite_rules();

}

// When the plugin is deactivated/activated, run the pretty link function above
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'fs_listing_cpt_install' );