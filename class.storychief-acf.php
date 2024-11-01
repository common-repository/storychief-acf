<?php

use Storychief\ImageUploader;

class Storychief_ACF {

	private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
			self::$initiated = true;

			add_action( 'storychief_after_test_action', array( 'Storychief_ACF', 'save_custom_fields_definitions' ) );
			add_action( 'storychief_after_publish_action', array( 'Storychief_ACF', 'save_custom_fields_values' ) );
		}
	}

	public static function save_custom_fields_definitions( $payload ) {
		$custom_field_definitions = isset( $payload['custom_fields']['data'] ) ? $payload['custom_fields']['data'] : array();
		self::set_custom_field_definitions( $custom_field_definitions );
	}

	public static function save_custom_fields_values( $payload ) {
		$post_ID    = $payload['external_id'];
		$cf_mapping = Storychief_ACF::get_custom_field_mapping();

		foreach ( $payload['custom_fields'] as $field ) {

			if ( isset( $cf_mapping[ $field['key'] ] ) && isset( $field['value'] ) ) {
				$field_key        = $cf_mapping[ $field['key'] ];
				$field_definition = self::getFieldDefinition( $field_key );

				switch ( $field_definition['type'] ) {
					case 'image':
						$post     = get_post( $post_ID );
						$uploader = new ImageUploader( $field['value'], '', $post );
						$uploader->save();
						$value = attachment_url_to_postid( $uploader->url );
						break;
					case 'select':
					case 'checkbox':
					case 'relationship':
						$value = explode( ',', $field['value'] );
						break;
					case 'taxonomy':
						$slugs = explode(',', $field['value']);
						$value = self::convertTaxonomySlugsToIds($field_definition, $slugs);
						break;
					default:
						$value = $field['value'];
						break;
				}

				update_field( $field_key, $value, $post_ID );
			}
		}
	}

	/**
	 * Display a view
	 *
	 * @param $name
	 * @param array $args
	 */
	public static function view( $name, array $args = array() ) {
		$args = apply_filters( 'storychief_view_arguments', $args, $name );
		foreach ( $args as $key => $val ) {
			$$key = $val;
		}

		load_plugin_textdomain( 'storychief-acf' );
		$file = STORYCHIEF_ACF__PLUGIN_DIR . 'views/' . $name . '.php';
		include( $file );
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation() {
		self::remove_custom_field_definitions();
		self::remove_custom_field_mapping();
	}

	private static function getFieldDefinition( $field_key ) {
		if ( function_exists( 'acf_get_field' ) ) { // pro
			return acf_get_field( $field_key );
		} else { // free
			return apply_filters( 'acf/load_field', array(), $field_key );
		}
	}

	public static function get_custom_field_definitions() {
		return get_option( 'storychief_acf_definitions' );
	}

	public static function set_custom_field_definitions( $key ) {
		update_option( 'storychief_acf_definitions', $key );
	}

	public static function remove_custom_field_definitions() {
		delete_option( 'storychief_acf_definitions' );
	}

	public static function get_custom_field_mapping() {
		return get_option( 'storychief_acf_mapping' );
	}

	public static function set_custom_field_mapping( $key ) {
		update_option( 'storychief_acf_mapping', $key );
	}

	public static function remove_custom_field_mapping() {
		delete_option( 'storychief_acf_mapping' );
	}

	private static function convertTaxonomySlugsToIds( $field_definition, $slugs ) {
		$termIds = array();
		foreach ( $slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, $field_definition['taxonomy'] );
			if ( $term ) {
				$termIds[] = $term->term_id;
			}
		}

		return $termIds;
	}

}
