<?php

class Storychief_ACF_Admin {
	const NONCE = 'storychief-acf-update-key';

	private static $initiated = false;
	private static $notices = array();

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}

		if ( isset( $_POST['_action'] ) && $_POST['_action'] == 'save-acf-mapping' ) {
			self::save_configuration();
		}
	}

	public static function init_hooks() {
		self::$initiated = true;

		add_action( 'admin_init', array( 'Storychief_ACF_Admin', 'admin_init' ) );
		add_action( 'admin_menu', array( 'Storychief_ACF_Admin', 'admin_menu' ) );
		add_action( 'admin_notices', array( 'Storychief_ACF_Admin', 'admin_notice' ) );

		add_filter( 'plugin_action_links', array( 'Storychief_ACF_Admin', 'plugin_action_links' ), 10, 2 );
	}

	public static function admin_init() {
		load_plugin_textdomain( 'storychief-acf' );
	}

	public static function admin_menu() {
		$hook = add_options_page( 'Storychief ACF', 'Storychief ACF', 'manage_options', 'storychief-acf', array(
			'Storychief_ACF_Admin',
			'display_configuration_page'
		) );

		add_action( "load-$hook", array( 'Storychief_ACF_Admin', 'admin_help' ) );
	}

	/**
	 * Add help to the Storychief ACF page
	 *
	 * @return false if not the Storychief ACF page
	 */
	public static function admin_help() {
		$current_screen = get_current_screen();
		// Screen Content
		if ( current_user_can( 'manage_options' ) ) {
			$current_screen->add_help_tab(
				array(
					'id'      => 'overview',
					'title'   => __( 'Overview', 'storychief-acf' ),
					'content' =>
						'<p><strong>' . esc_html__( 'Storychief ACF Configuration', 'storychief-acf' ) . '</strong></p>' .
						'<p>' . esc_html__( 'Storychief publishes posts, so you can focus on more important things.', 'storychief-acf' ) . '</p>' .
						'<p>' . esc_html__( 'Map your custom fields here.', 'storychief-acf' ) . '</p>',
				)
			);

			$current_screen->add_help_tab(
				array(
					'id'      => 'settings',
					'title'   => __( 'Settings', 'storychief-acf' ),
					'content' =>
						'<p><strong>' . esc_html__( 'Storychief ACF Configuration', 'storychief-acf' ) . '</strong></p>' .
						'<p><strong>' . esc_html__( 'Custom Fields', 'storychief-acf' ) . '</strong> - ' . esc_html__( 'Map your custom fields here.', 'storychief-acf' ) . '</p>',
				)
			);
		}

		// Help Sidebar
		$current_screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'For more information:', 'storychief-acf' ) . '</strong></p>' .
			'<p><a href="https://intercom.help/story-chief" target="_blank">' . esc_html__( 'Storychief FAQ', 'storychief-acf' ) . '</a></p>' .
			'<p><a href="https://intercom.help/story-chief" target="_blank">' . esc_html__( 'Storychief Support', 'storychief-acf' ) . '</a></p>'
		);
	}

	public static function save_configuration() {
		if ( function_exists( 'current_user_can' ) && ! current_user_can( 'manage_options' ) ) {
			die( __( 'Cheatin&#8217; uh?', 'storychief-acf' ) );
		}
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], self::NONCE ) ) {
			return false;
		}

		$data          = $_POST;
		$filtered_keys = array_filter( array_keys( $data ), function ( $key ) {
			return strpos( $key, '_' ) !== 0;
		} );
		$data          = array_intersect_key( $data, array_flip( $filtered_keys ) );

		Storychief_ACF::set_custom_field_mapping( $data );
		self::notice_config_saved();

		return true;
	}

	public static function settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=storychief-acf">' . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );

		return $links;
	}

	public static function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( plugin_dir_url( __FILE__ ) . '/storychief-acf.php' ) ) {
			$links[] = '<a href="' . esc_url( self::get_page_url() ) . '">' . esc_html__( 'Settings', 'storychief-acf' ) . '</a>';
		}

		return $links;
	}

	public static function get_page_url() {
		$args = array( 'page' => 'storychief-acf' );
		$url  = add_query_arg( $args, admin_url( 'options-general.php' ) );

		return $url;
	}

	private static function get_custom_fields_by_group() {
		if ( function_exists( 'acf_get_field_groups' ) && function_exists( 'acf_get_fields' ) ) {
			return self::get_acfs_pro();
		} else {
			return self::get_acfs_free();
		}
	}

	private static function get_acfs_pro() {
		$acf_fields = array();
		if ( $acf_field_groups = acf_get_field_groups() ) {
			foreach ( $acf_field_groups as $acf_field_group ) {
				$acf_fields[] = array(
					'id'     => $acf_field_group['key'],
					'title'  => $acf_field_group['title'],
					'fields' => acf_get_fields( $acf_field_group ),
				);
			}
		}

		return $acf_fields;
	}

	private static function get_acfs_free() {
		$acf_field_groups = get_posts( array(
			'numberposts'      => - 1,
			'post_type'        => 'acf',
			'orderby'          => 'menu_order title',
			'order'            => 'asc',
			'suppress_filters' => false,
		) );

		$acf_fields = array();

		if ( $acf_field_groups ) {
			foreach ( $acf_field_groups as $acf_field_group ) {
				$acf_fields[] = array(
					'id'         => $acf_field_group->post_name,
					'title'      => $acf_field_group->post_title,
					'fields'     => apply_filters( 'acf/field_group/get_fields', array(), $acf_field_group->ID ),
					'location'   => apply_filters( 'acf/field_group/get_location', array(), $acf_field_group->ID ),
					'options'    => apply_filters( 'acf/field_group/get_options', array(), $acf_field_group->ID ),
					'menu_order' => $acf_field_group->menu_order,
				);
			}
		}

		return $acf_fields;
	}

	public static function display_configuration_page() {
		$cf_definitions = Storychief_ACF::get_custom_field_definitions();
		$acf_fields     = self::get_custom_fields_by_group();
		$cf_mapping     = Storychief_ACF::get_custom_field_mapping();

		Storychief_ACF::view( 'config', compact( 'cf_definitions', 'acf_fields', 'cf_mapping' ) );
	}

	/*----------- NOTICES -----------*/
	public static function admin_notice() {
		if ( ! empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) {
				Storychief_ACF::view( 'notice', $notice );
			}

			self::$notices = array();
		}
	}

	public static function notice_undefined_error() {
		self::$notices[] = array(
			'type' => 'undefined',
		);
	}

	public static function notice_config_saved() {
		self::$notices[] = array(
			'type' => 'config-set',
		);
	}
}
