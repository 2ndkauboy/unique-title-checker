<?php

/*
 * Plugin Name: Unique Title Checker
 * Plugin URI: https://github.com/2ndkauboy/unique-title-checker
 * Description: Checks if the title of a post, page or custom post type is unique and warn the editor if not
 * Version: 1.2.2
 * Author: Bernhard Kau
 * Author URI: http://kau-boys.de
 * Textdomain: unique-title-checker
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */

add_action(
	'plugins_loaded',
	array( Unique_Title_Checker::get_instance(), 'plugin_setup' )
);

class Unique_Title_Checker {

	/**
	 * Plugin instance.
	 *
	 * @see   get_instance()
	 * @type  object
	 */
	protected static $instance = null;

	/**
	 * URL to this plugin's directory.
	 *
	 * @type  string
	 */
	public $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type  string
	 */
	public $plugin_path = '';

	/**
	 * The nonce action
	 *
	 * @type  string
	 */
	public $nonce_action = 'unique_title_check_nonce';

	/**
	 * The AJAX nonce
	 *
	 * @type  string
	 */
	public $ajax_nonce = '';

	/**
	 * The post title to be checked
	 *
	 * @type  string
	 */
	public $post_title = '';

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 */
	public static function get_instance() {

		null === self::$instance and self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook  plugins_loaded
	 * @return   void
	 */
	public function plugin_setup() {

		$this->plugin_url  = plugins_url( '/', __FILE__ );
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->load_language( 'unique-title-checker' );

		// enqueue the main JavaScript file
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// add the AJAX callback function
		add_action( 'wp_ajax_unique_title_check', array( $this, 'unique_title_check' ) );

		// check uniqueness, when post is edited
		add_filter( 'admin_notices', array( $this, 'uniqueness_admin_notice' ) );

		// generate the AJAX nonce
		$this->ajax_nonce = wp_create_nonce( $this->nonce_action );
	}

	/**
	 * Constructor.
	 * Intentionally left empty and public.
	 *
	 * @see    plugin_setup()
	 */
	public function __construct() {
	}


	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and front-end for example).
	 *
	 * @wp-hook init
	 *
	 * @param   string $domain The text domain for this plugin
	 *
	 * @return  void
	 */
	public function load_language( $domain ) {

		load_plugin_textdomain(
			$domain,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Add the JavaScript files to the admin pages
	 *
	 * @wp-hook admin_enqueue_scripts
	 *
	 * @return  void
	 */
	public function enqueue_scripts( $hook ) {
		// only enable it on new posts/pages/CPTs
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		// enqueue the script
		wp_enqueue_script( 'unique_title_checker', plugins_url( 'js/unique-title-checker.js', __FILE__ ), 'jquery', false, true );

		// add the nonce to the form
		wp_localize_script( 'unique_title_checker', 'unique_title_checker', array( 'nonce' => $this->ajax_nonce ) );
		wp_enqueue_script( 'unique_title_checker' );
	}

	/**
	 * Check the uniqueness and return the result
	 *
	 * @wp-hook wp_ajax_(action)
	 *
	 * @return  void
	 */
	public function unique_title_check() {
		// verify the ajax request
		check_ajax_referer( $this->nonce_action, 'ajax_nonce' );

		$response = $this->check_uniqueness( $_REQUEST );

		echo json_encode( $response );

		die();
	}

	/**
	 * Show an initial warning, if the title of a saved post is not unique
	 *
	 * @wp-hook admin_notices
	 */
	public function uniqueness_admin_notice() {
		global $post, $pagenow;

		// don't show an initial warning on a new post
		if ( 'post.php' != $pagenow ) {
			return;
		}

		// show no warning, when the title is empty
		if ( empty( $post->post_title ) ) {
			return;
		}

		// build the necessary args for the initial uniqueness check
		$args = array(
			'post__not_in' => array( $post->ID ),
			'post_type'    => $post->post_type,
			'post_title'   => $post->post_title,
		);

		$response = $this->check_uniqueness( $args );

		// don't show a message on init, if title is unique
		if ( 'error' != $response['status'] ) {
			return;
		}

		echo '<div id="unique-title-message" class="' . $response['status'] . '"><p>' . $response['message'] . '</p></div>';
	}

	/**
	 * @param array|string $args The WP_QUERY arguments array or query string
	 *
	 * @return array The status and message for the response
	 */
	public function check_uniqueness( $args ) {

		// use the posts_where hook to add thr filter for the post_title, as it is not available through WP_Query args
		add_filter( 'posts_where', array( $this, 'post_title_where' ), 10, 1 );

		// providing a filter to overwrite the search arguments
		$args = apply_filters( 'unique_title_checker_arguments', $args );

		if ( $post_type_object = get_post_type_object( $args['post_type'] ) ) {
			$post_type_singular_name = $post_type_object->labels->singular_name;
			$post_type_name          = $post_type_object->labels->name;
		} else {
			$post_type_singular_name = __( 'post', 'unique-title-checker' );
			$post_type_name = __( 'posts', 'unique-title-checker' );
		}

		// set post title to be checked
		$this->post_title = $args['post_title'];

		$query = new WP_Query( $args );
		$posts_count = $query->post_count;

		if ( empty( $posts_count ) ) {
			$response = array(
				'message' => __( 'The chosen title is unique.', 'unique-title-checker' ),
				'status'  => 'updated',
			);
		} else {
			$response = array(
				'message' => sprintf( _n( 'There is 1 %2$s with the same title!', 'There are %1$d other %3$s with the same title!', $posts_count, 'unique-title-checker' ), $posts_count, $post_type_singular_name, $post_type_name ),
				'status'  => 'error',
			);
		}

		// remove filter for post_title
		remove_filter( 'posts_where', array( $this, 'post_title_where' ), 10 );

		return $response;
	}

	/**
	 * Add the filter for the post_title to the WHERE clause
	 *
	 * @wp-hook wp_ajax_(action)
	 *
	 * @global wpdb     $wpdb     The data base object
	 *
	 * @param string    $where    The WHERE clause
	 *
	 * @return string The new WHERE clause
	 */
	public function post_title_where( $where ) {
		global $wpdb;

		return $where . " AND $wpdb->posts.post_title = '" . esc_sql( $this->post_title ) . "'";
	}

} // end class