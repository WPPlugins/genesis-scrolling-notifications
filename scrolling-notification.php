<?php 
/*
Plugin Name: Genesis Scrolling Notification 
Plugin URI: http://wpzombies.com/genesis-scrolling-notifications
Description: A simple way to display a notification bar to your users.
Version: 1.0
Author: Juan Rangel
Author URI: http://wpzombies.com/
*/

/**
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

class Scrolling_Notification {

	public $options;


	/**
	 * Our constructor method. 
	 * 
	 */
	public function __construct() {

		// prepare our options variable
		$this->options = get_option( 'sn_options' );

		$this->display_notifcation_location();
		$this->register_notification_type();

		// register settings
		add_action( 'admin_init', array( $this, 'register_settings_and_fields') );

		// Enqueue styles and scripts for the frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'sn_load_scripts') );

		// Enqueue styles and scripts for the admin/backend
		add_action( 'admin_enqueue_scripts', array( $this, 'sn_admin_scripts') );
		
	}


	/**
	* Load Scripts and Styles the front end 
	*
	*/
	public function sn_load_scripts() {

		// enqueue styles
		wp_enqueue_style( 'sn-front-end', plugins_url( 'genesis-scrolling-notification/css/main.css' ) );

		// enqueue scripts
		wp_enqueue_script( 'quovolver', plugins_url( 'genesis-scrolling-notification/js/jquery.quovolver.js' ), array( 'jquery' ) );
		wp_enqueue_script( 'main', plugins_url( 'genesis-scrolling-notification/js/main.js' ), array( 'quovolver' ) );

	}


	/** 
	* Load Scripts and Styles for the admin 
	*
	*/
	public function sn_admin_scripts() {
		
	    wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script('wp-color-picker');

		wp_enqueue_script( 'admin', plugins_url( 'genesis-scrolling-notification/js/admin.js' ), array( 'jquery', 'wp-color-picker' ) );

	}


	/**
	* Register our notification post type 
	*
	*/
	public function register_notification_type() {

		$labels = array(
			'name'               => _x( 'Notifications', 'post type general name', 'scrolling_notification' ),
			'singular_name'      => _x( 'Notification', 'post type singular name', 'scrolling_notification' ),
			'menu_name'          => _x( 'Notifications', 'admin menu', 'scrolling_notification' ),
			'name_admin_bar'     => _x( 'Notification', 'add new on admin bar', 'scrolling_notification' ),
			'add_new'            => _x( 'Add New', 'Notification', 'scrolling_notification' ),
			'add_new_item'       => __( 'Add New Notification', 'scrolling_notification' ),
			'new_item'           => __( 'New Notification', 'scrolling_notification' ),
			'edit_item'          => __( 'Edit Notification', 'scrolling_notification' ),
			'view_item'          => __( 'View Notification', 'scrolling_notification' ),
			'all_items'          => __( 'All Notifications', 'scrolling_notification' ),
			'search_items'       => __( 'Search Notifications', 'scrolling_notification' ),
			'parent_item_colon'  => __( 'Parent Notifications:', 'scrolling_notification' ),
			'not_found'          => __( 'No Notifications found.', 'scrolling_notification' ),
			'not_found_in_trash' => __( 'No Notifications found in Trash.', 'scrolling_notification' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'menu_icon'          => 'dashicons-tag',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'notification' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail' )
		);

		register_post_type( 'notification', $args );
	}

	
	/*
	* Register a new query and output the query our notifications
	*
	*/
	public function display_notifcation() {

		if ( 1 == $this->options['home'] ) {
			if ( is_singular() ) 
				return;
		}

		$query = new WP_Query( array(
			'post_type' => 'notification',
			'posts_per_page' => 5,
		) );


		if ( $query->have_posts() ) { ?>
	
			<div class="sn-notification" style="background:<?php echo $this->options['bg']; ?>;">
				<div class="wrap">
					<?php while( $query->have_posts() ) : $query->the_post(); ?>
					<div class="notification-post"><?php the_content(); ?></div>
					<?php endwhile; ?>
				</div>
			</div>
		<?php }
		wp_reset_postdata();
	}


	/**
	* Adds our menu page 
	*
	*/
	public function add_menu_page() {

		add_options_page( 
			'Scrolling Notification', 'Scrolling Notification', 'administrator', __FILE__, array( 'Scrolling_Notification', 'sn_display_options_page' ) );

	}


	/**
	* Registers our settings 
	*
	*/
	public function register_settings_and_fields() {

		add_settings_section( 
			'sn_main_section', 
			'Main Settings', 
			array( $this, 'sn_main_section_cb' ), 
			__FILE__ 
		);
		
		add_settings_field( 
			'sn_homepage', 
			'Homepage Only: ', 
			array( $this, 'sn_homepage_setting'), 
			__FILE__ , 
			'sn_main_section' 
		);

		add_settings_field( 
			'sn_bg_color', 
			'Notification Background Color: ', 
			array( $this, 'sn_background_setting'), 
			__FILE__,
			'sn_main_section'
		);

		add_settings_field( 
			'sn_location', 
			'Notification Location: ', 
			array( $this, 'sn_location_setting'), 
			__FILE__ , 
			'sn_main_section' 
		);

		register_setting( 
			'sn_main_section', 
			'sn_options' 
		);

	}


	/**
	* prepare our options page form for our fields. 
	*
	*/
	function sn_display_options_page() { ?>

		<div class="wrap">
			<h2>Scrolling Notification Options</h2>
			<form method="post" action="options.php" enctype="multipart/form-data" >

				<?php settings_fields( 'sn_main_section' ); ?>
				
				<?php do_settings_sections( __FILE__ ); ?>

				<?php submit_button(); ?>
			
			</form>
		</div>
	

	<?php }


	/**
	* Outputs the function for the settings section
	*
	*/
	function sn_main_section_cb() {
		echo 'Here are some basic settings to customzie the look of your notification bar.';
	}

	
	/**
	* Outputs the location setting field
	*
	*/
	public function sn_location_setting() { ?>

		<select id="sn_location" name="sn_options[location]">
			<option value="above" <?php selected( $this->options['location'], 'above' ); ?> >Above Header</option>
			<option value="below" <?php selected( $this->options['location'], 'below' ); ?> >Below Header</option>
		</select>

	<?php }


	/**
	* Outputs the background color settings field
	*
	*/
	public function sn_background_setting() { ?>

		<input name="sn_options[bg]" id="sn-bg-color" value="<?php  echo $this->options['bg'] ?>"/>

	<?php }


	/**
	* Check to see where the user wants to output the notification bar 
	*
	*/
	public function display_notifcation_location() {

		if ( 'above' == $this->options['location'] ) {	

			add_action( 'genesis_before_header', array( $this, 'display_notifcation' ) );
		
		} else {
		
			add_action( 'genesis_after_header', array( $this, 'display_notifcation' ) );
		
		}
	}


	/**
	* Checks to see if the user wants to limit the display to the homepage 
	*
	*/
	public function sn_homepage_setting() { ?>

		<input type="checkbox" name="sn_options[home]" id="sn-homepage-setting" value="1" <?php checked( 1, $this->options['home'] ); ?>/><small>Check if you would like to display the notificatio on the homepage.</small>

	<?php }

}


add_action( 'admin_menu', 'sn_add_page' );
function sn_add_page() {
	Scrolling_Notification::add_menu_page();
}

add_action( 'init', 'sn_init' );
function sn_init() {
	new Scrolling_Notification();
}

