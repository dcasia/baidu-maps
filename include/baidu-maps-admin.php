<?php

class Baidu_Maps_Admin {

	public function __construct( $plugin_url ) {

		// Register Plugins Settings
		$settings_page = new Baidu_Maps_Settings();

		// Create the custom post-type and the meta boxes
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_map_details' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_marker_details' ) );

		// Modify wp_list_table with new columns
		add_filter( 'manage_edit-bmap_columns', array( $this, 'set_baidu_maps_custom_columns' ) );
		add_action( 'manage_bmap_posts_custom_column', array( $this, 'baidu_maps_custom_column' ), 10, 2 );

		add_action( 'wp_ajax_get_bmap_coordinates_from_link', array( $this, 'get_bmap_coordinates_from_link' ) );

		$this->plugin_url = $plugin_url;
	}

	/**
	 * Register the custom post-type (bmap)
	 *
	 */
	public function register_post_types() {
		$labels = array(
			'name'               => __( 'Baidu Maps', 'baidu-maps' ),
			'singular_name'      => __( 'Baidu Map', 'baidu-maps' ),
			'add_new'            => __( 'Add New Map', 'baidu-maps' ),
			'add_new_item'       => __( 'Add New Map', 'baidu-maps' ),
			'edit_item'          => __( 'Edit Map', 'baidu-maps' ),
			'new_item'           => __( 'New Map', 'baidu-maps' ),
			'all_items'          => __( 'All Maps', 'baidu-maps' ),
			'view_item'          => __( 'View Map', 'baidu-maps' ),
			'search_items'       => __( 'Search Maps', 'baidu-maps' ),
			'not_found'          => __( 'No Maps found', 'baidu-maps' ),
			'not_found_in_trash' => __( 'No Maps found in Trash', 'baidu-maps' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Baidu Maps', 'baidu-maps' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'baidu-map' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 100,
			'menu_icon'          => $this->plugin_url . 'icons/menu-icon.png',
			'supports'           => array( 'title' )
		);

		register_post_type( 'bmap', $args );
	}

	/**
	 *
	 */
	public function create_meta_box() {
		add_meta_box( 'bmap-map-details', __( 'Map Settings', 'baidu-maps' ), array( $this, 'render_meta_box_map_details' ), 'bmap', 'side', 'high' );
		add_meta_box( 'bmap-map-markers', __( 'Map Markers', 'baidu-maps' ), array( $this, 'render_meta_box_map_markers' ), 'bmap', 'normal', 'low' );
		add_meta_box( 'bmap-map-location-check', __( 'Location Finder (Search in chinese only) ', 'baidu-maps' ), array( $this, 'render_meta_box_map_location_check' ), 'bmap', 'normal', 'high' );
		add_meta_box( 'bmap-map-branding', 'Digital Creative', array( $this, 'render_meta_box_branding' ), 'bmap', 'side', 'high' );

	}

	/**
	 * Populates an array of form fields to use with the bmap custom post-type
	 *
	 * @return the array of form fields
	 */
	private function populate_meta_box_map_details() {

		$prefix = 'baidu_maps_meta_';

		$baidu_meta_maps_details = array(
			array(
				'label' => __( 'Map Height', 'baidu-maps' ) . ' (px)',
				'desc'  => __( 'Enter the height in px', 'baidu-maps' ),
				'id'    => $prefix . 'height',
				'type'  => 'text'
			),
			array(
				'label' => __( 'Map Width', 'baidu-maps' ) . ' (px)',
				'desc'  => __( 'Enter the width in px', 'baidu-maps' ),
				'id'    => $prefix . 'width',
				'type'  => 'text'
			),
			array(
				'label' => __( 'Show full width', 'baidu-maps' ),
				'desc'  => __( 'Select to set the map to full width', 'baidu-maps' ),
				'id'    => $prefix . 'set_full_width',
				'type'  => 'checkbox'
			),
			array(
				'label' => __( 'Zoom', 'baidu-maps' ),
				'desc'  => __( 'Enter the zoom of the map between (1 - 20)', 'baidu-maps' ),
				'id'    => $prefix . 'zoom',
				'type'  => 'text'
			),
			array(
				'label' => __( 'Map (Latitude)', 'baidu-maps' ),
				'desc'  => __( 'Enter the map centering latitude', 'baidu-maps' ),
				'id'    => $prefix . 'center_lat',
				'type'  => 'text'
			),
			array(
				'label' => __( 'Map (Longitude)', 'baidu-maps' ),
				'desc'  => __( 'Enter the map centering longitude', 'baidu-maps' ),
				'id'    => $prefix . 'center_lng',
				'type'  => 'text'
			),
		);

		return $baidu_meta_maps_details;
	}

	/**
	 * Render the map details meta box
	 *
	 */
	public function render_meta_box_map_details() {
		global $baidu_meta_maps_details, $post;

		$baidu_meta_maps_details = $this->populate_meta_box_map_details();

		wp_nonce_field( 'baidu_maps_meta_box_map_details_nonce', 'baidu_maps_meta_box_nonce' );

		$meta_box_description = __( "Enter your map details here ...", 'baidu-maps' );

		$html[] = "<p>";
		$html[] = $meta_box_description;
		$html[] = "</p>";


		$html[] = "<table class='form-table'>";

		foreach ( $baidu_meta_maps_details as $field ) {
			$meta = get_post_meta( $post->ID, $field['id'], true );

			$html[] = "<tr>";
			$html[] = "<th> <label for='" . $field['id'] . "'>" . $field['label'] . "</label></th>";
			$html[] = "<td>";
			switch ( $field['type'] ) {
				case 'text':
					$html[] = "<input type='text' name='" . $field['id'] . "' id='" . $field['id'] . "' value='" . $meta . "' size='10'>";
					$html[] = "<br>";
					$html[] = "<span class='description'>" . $field['description'] . "</span>";
					break;

				case 'checkbox':
					$checked = $meta ? "checked='checked'" : "";
					$html[]  = "<input type='checkbox' name='" . $field['id'] . "' id='" . $field['id'] . "'" . $checked . "/>";
					$html[]  = "<label for='" . $field['id'] . "'>" . $field['desc'] . "</label>";
					break;

				default:
					break;
			}
			$html[] = "</td>";
			$html[] = "</tr>";
		}

		$html[] = "</table>";


		echo implode( "\n", $html );
	}

	/**
	 * Render the marker details meta box
	 *
	 */
	public function render_meta_box_map_markers() {
		global $post;

		$prefix = 'baidu_maps_marker_meta_';

		wp_nonce_field( 'baidu_maps_meta_box_marker_details_nonce', 'baidu_maps_meta_box_markers_nonce' );

		$html[] = "<p>";
		$html[] = "<a href='#' class='button insert_marker'>" . __( "Add Marker", 'baidu-maps' ) . "</a>";
		$html[] = "</p>";

		$markers = get_post_meta( $post->ID, 'markers', true );

		$html[] = "<div class='marker-container'>";

		if ( is_array( $markers ) ) {
			foreach ( $markers as $marker_count => $marker ) {
				if ( empty( $marker ) ) continue;
				$html[] = "<div class='markers'>";

				$meta_name        = $marker[$prefix . 'name' . '-' . $marker_count];
				$meta_description = $marker[$prefix . 'description' . '-' . $marker_count];
				$meta_lat         = $marker[$prefix . 'lat' . '-' . $marker_count];
				$meta_lng         = $marker[$prefix . 'lng' . '-' . $marker_count];
				$meta_icon        = $marker[$prefix . 'icon' . '-' . $marker_count];
				$meta_bgcolor     = $marker[$prefix . 'bgcolor' . '-' . $marker_count];
				$meta_fgcolor     = $marker[$prefix . 'fgcolor' . '-' . $marker_count];
				$meta_isopen      = $marker[$prefix . 'isopen' . '-' . $marker_count];
				$checked_isopen   = $meta_isopen ? "checked='checked'" : "";


				$html[] = "<div class='marker-controls'>";
				$html[] = "<a href='#'class='button choose_image'>" . __( 'Upload Marker', 'baidu-maps' ) . "</a>";
				$html[] = "<input class='icon-input' style='display: none;' type='text' name='" . $prefix . 'icon' . '-' . $marker_count . "' value='" . $meta_icon . "' >";
				$html[] = "<a href='#'class='button delete_marker'>" . __( 'Delete Marker', 'baidu-maps' ) . "</a>";
				$html[] = "<div class='img_wrap'> <img src='" . $meta_icon . "' width='32' height='32' ></div>";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_name marker_row_default'>";
				$html[] = "<label>" . __( "Marker Name", 'baidu-maps' ) . "</label>";
				$html[] = "<input type='text' name='" . $prefix . 'name' . '-' . $marker_count . "' value='" . $meta_name . "' size='30' >";
				$html[] = "</div>";
				$html[] = "<div class='marker_row marker_row_description marker_row_default'>";
				$html[] = "<label>" . __( "Marker Description", 'baidu-maps' ) . "</label>";
				$html[] = "<input type='text' name='" . $prefix . 'description' . '-' . $marker_count . "' value='" . $meta_description . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_location'>";
				$html[] = "<label>" . __( "Latitude / Longitude", 'baidu-maps' ) . "</label>";
				$html[] = "<input type='text' name='" . $prefix . 'lat' . '-' . $marker_count . "' value='" . $meta_lat . "' size='30' >";
				$html[] = "<input type='text' name='" . $prefix . 'lng' . '-' . $marker_count . "' value='" . $meta_lng . "' size='30' >";
				$html[] = "<br>";
				$html[] = "<button class='button marker_row_location_grab'>Grab From Map</button>";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_color'>";
				$html[] = "<label>" . __( "Background Color", 'baidu-maps' ) . "</label>";
				$html[] = "<input type='text' class='color-picker-control' name='" . $prefix . 'bgcolor' . '-' . $marker_count . "' value='" . $meta_bgcolor . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_color'>";
				$html[] = "<label>" . __( "Font Color", 'baidu-maps' ) . "</label>";
				$html[] = "<input type='text' class='color-picker-control' name='" . $prefix . 'fgcolor' . '-' . $marker_count . "' value='" . $meta_fgcolor . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_is_open'>";
				$html[] = "<label>" . __( "Show Marker Details", 'baidu-maps' ) . "</label>";
				$html[] = "<input type='checkbox' name='" . $prefix . 'isopen' . '-' . $marker_count . "' " . $checked_isopen . "/>";
				$html[] = "<span class='caption'>" . __( "Check to always show marker details", 'baidu-maps' ) . "</span>";
				$html[] = "</div>";

				$html[] = "</div>";
			}
		}

		$html[] = "</div>";

		echo implode( "\n", $html );
	}

	public function render_meta_box_map_location_check() {
		global $post;

		$baidu_maps_api = new Baidu_Maps_API();

		$id  = 'admin-map-element';
		$map = $baidu_maps_api->createMapElement( $id, '0', '300', TURE );

//		$default_lat  = '39.915';
//		$default_lng  = '116.404';
//		$default_zoom = '13';

		$default_lat = get_post_meta( $post->ID, 'baidu_maps_meta_center_lat', true );
		if ( empty( $default_lat ) ) $default_lat = '39.915';
		$default_lng = get_post_meta( $post->ID, 'baidu_maps_meta_center_lng', true );
		if ( empty( $default_lng ) ) $default_lng = '116.404';
		$default_zoom = get_post_meta( $post->ID, 'baidu_maps_meta_zoom', true );
		if ( empty( $default_zoom ) ) $default_zoom = '13';


		$baidu_maps_api->createMap( $id, $default_zoom, $default_lat, $default_lng );


		$html[] = "<div class='location-check-box'>";

		$html[] = "<div class='location-check-search'>";
		$html[] = "<p><label>" . __( 'Search for location', 'baidu-maps' ) . "</label>";
		$html[] = "<input type='text' class='location-check-url' />";
		$html[] = "<button class='location-check-button button'>" . __( 'Search', 'baidu-maps' ) . "</button>";
		$html[] = "</p>";

		$html[] = "<p><label>" . __( 'Select the zoom level', 'baidu-maps' ) . "</label>";
		$html[] = "<input type='number' min='1' max='19' value='" . $default_zoom . "' class='location-check-zoom' />";
		$html[] = "</p>";
		$html[] = "</div>";

		$html[] = "<div class='location-check-details'>";
		$html[] = "<table class='location-check-results widefat'>";
		$html[] = "<tr><th>" . __( 'Latitude :', 'baidu-maps' ) . " </th><td class='lat'>" . $default_lat . "</td></tr>";
		$html[] = "<tr><th>" . __( 'Longitude :', 'baidu-maps' ) . " </th><td class='lng'>" . $default_lng . "</td></tr>";
		$html[] = "<tr>";
		$html[] = "<td><button class='button location-check-insert'>" . __( 'Add as marker', 'baidu-maps' ) . "</button></td>";
		$html[] = "<td><button class='button location-check-center'>" . __( 'Set as map center', 'baidu-maps' ) . "</button></td>";
		$html[] = "</tr>";
		$html[] = "</table>";
		$html[] = "</div>";

		$html[] = "<br>";
		$html[] = $map;
		$html[] = "<p class='location-check-currc'> " . __( 'Current Coordinates (lat, lng)  :', 'baidu-maps' ) . "  <span class='lat'>0.0</span>, <span class='lng'>0.0</span> </p>";
		$html[] = "</div>";


		echo implode( "\n", $html );
	}

	public function render_meta_box_branding( $post_id ) {

		$html[] = "<p>" . __( 'Baidu maps plugin developed in Shanghai by', 'baidu-maps' ) . "</p>";
		$html[] = "<a href='http://www.digitalcreative.asia'><img class='logo' src='{$this->plugin_url}icons/dc_asia_logo.png'></a>";
		$html[] = "<div class='bottom'><a href='http://www.digitalcreative.asia' class='website'>www.digitalcreative.asia</a></div>";

		echo implode( "\n", $html );
	}


	public function save_meta_box_map_details( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		if ( ! isset( $_POST['baidu_maps_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['baidu_maps_meta_box_nonce'], 'baidu_maps_meta_box_map_details_nonce' ) ) return;

		if ( ! current_user_can( 'edit_post' ) ) return;

		$baidu_meta_maps_details = $this->populate_meta_box_map_details();

		foreach ( $baidu_meta_maps_details as $field ) {
			$old = get_post_meta( $post_id, $field['id'], true );
			$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : null;
			if ( $new && $new != $old ) {
				update_post_meta( $post_id, $field['id'], $new );
			}
			elseif ( '' == $new && $old ) {
				delete_post_meta( $post_id, $field['id'], $old );
			}
		}
	}

	public function save_meta_box_marker_details( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		if ( ! isset( $_POST['baidu_maps_meta_box_markers_nonce'] ) || ! wp_verify_nonce( $_POST['baidu_maps_meta_box_markers_nonce'], 'baidu_maps_meta_box_marker_details_nonce' ) ) return;

		if ( ! current_user_can( 'edit_post' ) ) return;

		$prefix  = 'baidu_maps_marker_meta_';
		$markers = array( array() );


		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, $prefix ) === 0 ) {
				$strs_marker  = explode( '-', $key );
				$marker_count = $strs_marker[1];

				$markers[$marker_count][$key] = $value;
			}
		}


		foreach ( $markers as $key => $value ) {
			$old = get_post_meta( $post_id, 'markers', true );
			$new = $markers;
			if ( $new && $new != $old ) {
				update_post_meta( $post_id, 'markers', $new );
			}
			elseif ( '' == $new && $old ) {
				delete_post_meta( $post_id, 'markers', $old );
			}
		}


	}

	public function set_baidu_maps_custom_columns( $columns ) {
		unset( $columns['date'] );
		$columns['marker_count'] = __( 'Makrers', 'baidu-maps' );
		$columns['shortcode']    = __( 'Shortcode', 'baidu-maps' );
		$columns['geolocation']  = __( 'Geolocation', 'baidu-maps' );

		return $columns;
	}

	public function baidu_maps_custom_column( $column, $post_id ) {
		switch ( $column ) {

			case 'marker_count' :
				echo sizeof( get_post_meta( $post_id, 'markers', true ) );
				break;

			case 'shortcode' :
				echo '[bmap id="' . get_the_ID( $post_id ) . '"]';
				break;
			case 'geolocation' :
				$lat = get_post_meta( $post_id, 'baidu_maps_meta_center_lat', true );
				$lng = get_post_meta( $post_id, 'baidu_maps_meta_center_lng', true );

				if ( $lat && $lng ) {
					echo $lat;
					echo ' , ';
					echo $lng;
				}
				else {
					echo _e( "No Location Defined", 'baidu-maps' );
				}

				break;

		}
	}

	public function get_bmap_coordinates_from_link() {
		$link = $_POST['link'];

		$baidu_maps_api = new Baidu_Maps_API();
		echo $json = json_encode( $baidu_maps_api->get_bmap_coordinates_from_link( $link ) );

		die();
	}

}