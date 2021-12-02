<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       catchplugins.com
 * @since      1.0.0
 *
 * @package    Catch_Instagram_Feed_Gallery_Widget
 * @subpackage Catch_Instagram_Feed_Gallery_Widget/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Catch_Instagram_Feed_Gallery_Widget
 * @subpackage Catch_Instagram_Feed_Gallery_Widget/admin
 * @author     Catch Plugins <info@catchplugins.com>
 */
class Catch_Instagram_Feed_Gallery_Widget_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Catch_Instagram_Feed_Gallery_Widget_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Catch_Instagram_Feed_Gallery_Widget_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name . '-common', plugin_dir_url( __FILE__ ) . 'css/catch-instagram-feed-gallery-widget-admin.css', array(), $this->version, 'all' );
		global $pagenow;
		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow || 'widgets.php' === $pagenow || 'customize.php' === $pagenow ) {
			// editing a page
			wp_enqueue_style( $this->plugin_name . '-add-shortcode', plugin_dir_url( __FILE__ ) . 'css/catch-instagram-feed-gallery-widget-admin-add-shortcode.css', array(), $this->version, 'all' );
		}
		if ( isset( $_GET['page'] ) && 'social-gallery-and-widget' == $_GET['page'] ) {
			wp_enqueue_style( $this->plugin_name . '-dashboard', plugin_dir_url( __FILE__ ) . 'css/admin-dashboard.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Catch_Instagram_Feed_Gallery_Widget_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Catch_Instagram_Feed_Gallery_Widget_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( isset( $_GET['page'] ) && 'social-gallery-and-widget' === $_GET['page'] ) {
			// Register the script
			wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/catch-instagram-feed-gallery-widget-admin.js', array( 'jquery' ), $this->version, false );
			
			// Localize the script with new data
			$cifgw_object = array(
				'page_url' => esc_js( admin_url( 'admin.php?page=social-gallery-and-widget' ))
			);
			wp_localize_script( $this->plugin_name, 'cifgw_object', $cifgw_object );
			
			// Enqueued script with localized data.
			wp_enqueue_script( $this->plugin_name );
			wp_enqueue_script( $this->plugin_name . '-match-height', plugin_dir_url( __FILE__ ) . 'js/jquery.matchHeight.min.js', array( 'jquery' ), $this->version, false );
		}
		global $pagenow;
		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow || 'widgets.php' === $pagenow || 'customize.php' === $pagenow ) {
			wp_enqueue_script( $this->plugin_name . '-widget', plugin_dir_url( __FILE__ ) . 'js/catch-instagram-feed-gallery-widget-admin-widget.js', array( 'jquery' ), $this->version, false );

		}
	}

	/**
	 * Social Gallery and Widget: action_links
	 * Social Gallery and Widget Settings Link function callback
	 *
	 * @param arrray $links Link url.
	 *
	 * @param arrray $file File name.
	 */
	public function action_links( $links, $file ) {
		if ( $file === $this->plugin_name . '/' . $this->plugin_name . '.php' ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=social-gallery-and-widget' ) ) . '">' . esc_html__( 'Settings', 'catch-instagram-feed-gallery-widget' ) . '</a>';

			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Social Gallery and Widget: add_plugin_settings_menu
	 * add Social Gallery and Widget to menu
	 */
	public function add_plugin_settings_menu() {
		$catch_instagram_feed_gallery_widget_svg = CIFGW_URL . 'admin/images/icon.svg';
		add_menu_page(
			esc_html__( 'Social Gallery and Widget', 'catch-instagram-feed-gallery-widget' ),
			esc_html__( 'Social Gallery and Widget', 'catch-instagram-feed-gallery-widget' ),
			'manage_options',
			'social-gallery-and-widget',
			array( $this, 'settings_page' ),
			$catch_instagram_feed_gallery_widget_svg,
			'99.01564'
		);
	}

	/**
	 * Social Gallery and Widget: catch_web_tools_settings_page
	 * Social Gallery and Widget Setting function
	 */
	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		require plugin_dir_path( __FILE__ ) . 'partials/catch-instagram-feed-gallery-widget-admin-display.php';
	}

	function activation_redirect() {
		if ( get_option( 'catch_instagram_feed_gallery_widget_activation_redirect', false ) ) {
			delete_option( 'catch_instagram_feed_gallery_widget_activation_redirect' );
			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_redirect( menu_page_url( 'social-gallery-and-widget', false ) );
			}
		}
	}

	function add_settings_errors() {
		settings_errors();
	}

	function catch_instagram_feed_gallery_widget_admin_ajax_welcome_panel() {
		if ( check_ajax_referer( 'catch-instagram-feed-gallery-widget-welcome-nonce', 'welcomenonce', false ) ) {
			if ( empty( $_POST['visible'] ) ) {
				update_option( 'catch_instagram_feed_gallery_widget_dismiss', true );
			}
		}
		wp_die();
	}

	function update_data( $access_token ) {
		// Verify the nonce before proceeding.
		$input['access_token'] = sanitize_text_field( $access_token );

		$url = 'https://graph.instagram.com/me';

		$url = add_query_arg(
			array(
				'fields'       => 'id,username',
				'access_token' => $input['access_token'],
			),
			$url
		);

		$get = wp_remote_get( $url );

		$message = '';
		$type    = '';

		if ( is_array( $get ) && ! is_wp_error( $get ) ) {
			$response = wp_remote_retrieve_body( $get );
			$json     = json_decode( $response, true );
			if ( isset( $json['username'] ) && isset( $json['id'] ) ) {
				$input['username'] = wp_kses_post( $json['username'] );
				$input['user_id']  = sanitize_text_field( $json['id'] );

				if ( update_option( 'catch_instagram_feed_gallery_widget_options', $input ) ) {
					delete_transient( 'catch_insta_json' );
					$message  = esc_html__( 'Options saved successfully.', 'catch-instagram-feed-gallery-widget' );
						$type = 'notice-success';
				} else {
					$message = esc_html__( 'Error! There was a problem.', 'catch-instagram-feed-gallery-widget' );
					$type    = 'notice-error';

				}
			} else {
				// #ERROR
				$message = esc_html__( 'Error, please try again.', 'catch-instagram-feed-gallery-widget' );
				$type    = 'notice-error';
			}
		} else {
			// #ERROR
			$message = esc_html__( 'Error, please try again.', 'catch-instagram-feed-gallery-widget' );
			$type    = 'notice-error';
		}
		return array(
			'message' => $message,
			'type'    => $type,
		);
	}

	function catch_instagram_reset() {

		if ( 'instagram' === $_POST['reset'] && check_ajax_referer( 'catch-instagram-reset-nonce', 'instagram_security', false ) ) {

			$options             = catch_instagram_feed_gallery_widget_get_options();
			$new                 = $options;
			$new['username']     = '';
			$new['user_id']      = '';
			$new['access_token'] = '';

		} else {

			$success = false;
			$message = esc_html__( 'Invalid Nonce!', 'catch-instagram-feed-gallery-widget' );

		}

		if ( $new !== $options ) {

			if ( update_option( 'catch_instagram_feed_gallery_widget_options', $new ) ) {

				$success = true;
				$message = esc_html__( 'Logged out successfully.', 'catch-instagram-feed-gallery-widget' );

			} else {

				$success = false;
				$message = esc_html__( 'Error! There was a problem.', 'catch-instagram-feed-gallery-widget' );

			}
		}

		$response = array(
			'status'  => $success,
			'message' => $message,
		);

		echo wp_json_encode( $response );
		wp_die();

	}

	function refresh_access_token() {
		$options      = catch_instagram_feed_gallery_widget_get_options();
		$access_token = $options['access_token'];
		$url          = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $access_token;

		// $url           = 'https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=' . CLIENT_SECRET . '&access_token=' . SHORT_LIVED_ACCESS_TOKEN;

		// * Get Long Lived Access Token : Valid for 60 days
		// * Method Type: GET
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		// execute!
		$response = curl_exec( $ch );

		// close the connection, release resources used
		curl_close( $ch );

		// do anything you want with your response
		$result = json_decode( $response, true );

		$new_access_token = '';
		$new_expires_in   = '';

		if ( isset( $result['access_token'] ) ) {
			$new_access_token = $result['access_token'];
		}
		if ( isset( $result['expires_in'] ) ) {
			$new_expires_in = $result['expires_in'];
		}

		if ( '' !== $new_access_token && '' !== $new_expires_in ) {

			$options['access_token'] = sanitize_text_field( $new_access_token );
			$options['expires_in']   = time() + sanitize_text_field( $new_expires_in );

			if ( update_option( 'catch_instagram_feed_gallery_widget_options', $options ) ) {

				// delete_transient( 'catch_insta_json' );
				$message = esc_html__( 'Options saved successfully.', 'catch-instagram-feed-gallery-widget' );
				$type    = 'notice-success';

			} else {

				$message = esc_html__( 'Error! There was a problem.', 'catch-instagram-feed-gallery-widget' );
				$type    = 'notice-error';

			}

			return array(
				'message' => $message,
				'type'    => $type,
			);

		}

	}

	function add_plugin_meta_links( $meta_fields, $file ) {
		if ( CIFGW_BASENAME == $file ) {
			$meta_fields[] = "<a href='https://catchplugins.com/support-forum/forum/catch-instagram-feed-gallery-widget/' target='_blank'>Support Forum</a>";
			$meta_fields[] = "<a href='https://wordpress.org/support/plugin/catch-instagram-feed-gallery-widget/reviews#new-post' target='_blank' title='Rate'>
			        <i class='ct-rate-stars'>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. '</i></a>';

			$stars_color = '#ffb900';

			echo '<style>'
				. '.ct-rate-stars{display:inline-block;color:' . esc_html( $stars_color ) . ';position:relative;top:3px;}'
				. '.ct-rate-stars svg{fill:' . esc_html( $stars_color ) . ';}'
				. '.ct-rate-stars svg:hover{fill:' . esc_html( $stars_color ) . '}'
				. '.ct-rate-stars svg:hover ~ svg{fill:none;}'
				. '</style>';
		}

		return $meta_fields;
	}
}
