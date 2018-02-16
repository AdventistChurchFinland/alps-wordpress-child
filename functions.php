<?php

require_once(__DIR__.'/lib/AlpsRemotePostsWidget.php');
require_once(__DIR__.'/lib/AlpsRSSWidget.php');
require_once(__DIR__.'/lib/AdventistfiImageParser.php');

function alps_wordpress_child_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
  
    if( is_front_page() ) {
	    wp_enqueue_script( 'alps_wordpress_child_js', get_stylesheet_directory_uri() . '/assets/scripts/alps-adventistfi.js', null, null, true );
	  }

}
add_action( 'wp_enqueue_scripts', 'alps_wordpress_child_enqueue_styles', 110 );


add_action( 'widgets_init', function() {
	register_widget( 'Alps_Remote_Posts_Widget' );
	register_widget( 'Alps_RSS_Widget' );
});


?>