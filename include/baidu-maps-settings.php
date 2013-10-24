<?php

class Baidu_Maps_Settings {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_submenu_page(
			'edit.php?post_type=bmap',
			__( 'Baidu Maps Settings', 'baidu-maps' ),
			__( 'Baidu Maps Settings', 'baidu-maps' ),
			'manage_options',
			'baidu-maps-admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'baidu_maps_settings' );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( "Baidu Maps", 'baidu-maps' ); ?></h2>

			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'baidu_maps' );
				do_settings_sections( 'baidu-maps-settings' );
				submit_button();
				?>
			</form>
		</div>
	<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'baidu_maps',
			'baidu_maps_settings',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'baidu_maps_setting_general',
			__( 'Baidu Maps General Settings', 'baidu-maps' ),
			array( $this, 'print_section_info' ),
			'baidu-maps-settings'
		);

		add_settings_field(
			'api_key',
			__( 'Baidu Developers API Key', 'baidu-maps' ),
			array( $this, 'api_key_callback' ),
			'baidu-maps-settings',
			'baidu_maps_setting_general'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		if ( ! empty( $input['api_key'] ) )
			$input['api_key'] = sanitize_text_field( $input['api_key'] );

		return $input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		echo _e( 'Enter your settings below:', 'baidu-maps' );
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function api_key_callback() {
		printf(
			'<input type="text" id="api_key" name="baidu_maps_settings[api_key]" value="%s" style="width: 300px;"/>',
			esc_attr( $this->options['api_key'] )
		);
	}
}
