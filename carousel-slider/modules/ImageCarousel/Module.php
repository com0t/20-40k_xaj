<?php

namespace CarouselSlider\Modules\ImageCarousel;

use CarouselSlider\Supports\Validate;
use WP_Post;

defined( 'ABSPATH' ) || exit;

class Module {
	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'carousel_slider/save_slider', [ self::$instance, 'save_slider' ] );
			add_filter( 'carousel_slider/register_view', [ self::$instance, 'view' ] );

			// Add custom link to media gallery
			add_filter( "attachment_fields_to_edit", [ self::$instance, "attachment_fields_to_edit" ], 10, 2 );
			add_filter( "attachment_fields_to_save", [ self::$instance, "attachment_fields_to_save" ], 10, 2 );

			Admin::init();
		}

		return self::$instance;
	}

	/**
	 * Register view
	 *
	 * @param array $views
	 *
	 * @return array
	 */
	public function view( array $views ): array {
		$views['image-carousel']     = new View();
		$views['image-carousel-url'] = new UrlView();

		return $views;
	}

	/**
	 * Save slider info
	 *
	 * @param int $slider_id
	 */
	public function save_slider( int $slider_id ) {
		if ( isset( $_POST['_images_urls'] ) ) {
			$url      = $_POST['_images_urls']['url'];
			$title    = $_POST['_images_urls']['title'];
			$caption  = $_POST['_images_urls']['caption'];
			$alt      = $_POST['_images_urls']['alt'];
			$link_url = $_POST['_images_urls']['link_url'];

			$urls = array();

			for ( $i = 0; $i < count( $url ); $i ++ ) {
				$urls[] = array(
					'url'      => esc_url_raw( $url[ $i ] ),
					'title'    => sanitize_text_field( $title[ $i ] ),
					'caption'  => sanitize_text_field( $caption[ $i ] ),
					'alt'      => sanitize_text_field( $alt[ $i ] ),
					'link_url' => esc_url_raw( $link_url[ $i ] ),
				);
			}
			update_post_meta( $slider_id, '_images_urls', $urls );
		}
	}


	/**
	 * Adding our custom fields to the $form_fields array
	 *
	 * @param array $form_fields
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function attachment_fields_to_edit( array $form_fields, WP_Post $post ): array {
		$value = get_post_meta( $post->ID, "_carousel_slider_link_url", true );
		$field = [
			"label"      => __( "Link to URL", "carousel-slider" ),
			"input"      => "textarea",
			"value"      => $value,
			"extra_rows" => [
				'carouselSliderInfo' => __( '"Link to URL" only works on Carousel Slider for linking image to a custom url.', 'carousel-slider' ),
			]
		];

		$form_fields["carousel_slider_link_url"] = $field;

		return $form_fields;
	}

	/**
	 * Save custom field value
	 *
	 * @param array $post
	 * @param array $attachment
	 *
	 * @return array
	 */
	public function attachment_fields_to_save( array $post, array $attachment ): array {
		$slider_link_url = $attachment['carousel_slider_link_url'] ?? null;

		if ( Validate::url( $slider_link_url ) ) {
			update_post_meta( $post['ID'], '_carousel_slider_link_url', esc_url_raw( $slider_link_url ) );
		} else {
			delete_post_meta( $post['ID'], '_carousel_slider_link_url' );
		}

		return $post;
	}
}
