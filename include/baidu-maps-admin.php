<?php

class Baidu_Maps_Admin {

	public function __construct() {

		// Register Plugins Settings
		$settings_page = new Baidu_Maps_Settings();

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_map_details' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_marker_details' ) );

		add_filter( 'manage_edit-bmap_columns', array( $this, 'set_baidu_maps_custom_columns' ) );
		add_action( 'manage_bmap_posts_custom_column', array( $this, 'baidu_maps_custom_column' ), 10, 2 );

	}

	public function register_post_types() {
		$labels = array(
			'name'               => 'Baidu Maps',
			'singular_name'      => 'Baidu Map',
			'add_new'            => 'Add New Map',
			'add_new_item'       => 'Add New Map',
			'edit_item'          => 'Edit Map',
			'new_item'           => 'New Map',
			'all_items'          => 'All Maps',
			'view_item'          => 'View Map',
			'search_items'       => 'Search Maps',
			'not_found'          => 'No Maps found',
			'not_found_in_trash' => 'No Maps found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Baidu Maps'
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
			// 'menu_icon' => THEME_IMAGES_URI . 'icons/marker.png',
			'supports'           => array( 'title' )
		);

		register_post_type( 'bmap', $args );
	}

	public function create_meta_box() {
		add_meta_box( 'bmap-map-details', 'Map Details', array( $this, 'render_meta_box_map_details' ), 'bmap', 'normal', 'high' );
		add_meta_box( 'bmap-map-markers', 'Map Markers', array( $this, 'render_meta_box_map_markers' ), 'bmap', 'normal', 'high' );

	}

	private function populate_meta_box_map_details() {

		$prefix                  = 'baidu_maps_meta_';
		$baidu_meta_maps_details = array(
			array(
				'label' => 'Map Height',
				'desc'  => 'Enter the height in px',
				'id'    => $prefix . 'height',
				'type'  => 'text'
			),
			array(
				'label' => 'Map Width',
				'desc'  => 'Enter the width in px',
				'id'    => $prefix . 'width',
				'type'  => 'text'
			),
			array(
				'label' => 'Show full width',
				'desc'  => 'Select to set the map to full width',
				'id'    => $prefix . 'set_full_width',
				'type'  => 'checkbox'
			),
			array(
				'label' => 'Zoom',
				'desc'  => 'Enter the zoom of the map between (1 - 20)',
				'id'    => $prefix . 'zoom',
				'type'  => 'text'
			),
			array(
				'label' => 'Map Center (Latitude)',
				'desc'  => 'Enter the map centering latitude',
				'id'    => $prefix . 'center_lat',
				'type'  => 'text'
			),
			array(
				'label' => 'Map Center (Longitude)',
				'desc'  => 'Enter the map centering longitude',
				'id'    => $prefix . 'center_lng',
				'type'  => 'text'
			),
		);

		return $baidu_meta_maps_details;
	}

	private function populate_meta_box_marker_details() {
		global $baidu_maps_marker_fields;

		$prefix = 'baidu_maps_marker_meta_';

		$baidu_maps_marker_fields = array(
			array(
				'label' => 'Marker Name',
				'desc'  => 'Enter the name of the marker',
				'id'    => $prefix . 'name',
				'type'  => 'text'
			),
			array(
				'label' => 'Marker Latitude',
				'desc'  => 'Enter the latitude of the marker',
				'id'    => $prefix . 'lat',
				'type'  => 'text'
			),
			array(
				'label' => 'Marker Longitude',
				'desc'  => 'Enter the longitude of the marker',
				'id'    => $prefix . 'lng',
				'type'  => 'text'
			),
			array(
				'label' => 'Marker Icon',
				'desc'  => 'Upload a custom icon for the marker (16 x 16)',
				'id'    => $prefix . 'icon',
				'type'  => 'image'
			),
		);

		return $baidu_maps_marker_fields;
	}

	public function render_meta_box_map_details() {
		global $baidu_meta_maps_details, $post;

		$baidu_meta_maps_details = $this->populate_meta_box_map_details();

		wp_nonce_field( 'baidu_maps_meta_box_map_details_nonce', 'baidu_maps_meta_box_nonce' );

		$meta_box_description = "Enter your map details here ...";

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
					$html[] = "<input type='text' name='" . $field['id'] . "' id='" . $field['id'] . "' value='" . $meta . "' size='30'";
					$html[] = "<br>";
					$html[] = "<span class='description'>" . $field['description'] . "</span>";
					break;

				case 'checkbox':
					$checked = $meta ? "checked='checked'" : "";
					$html[]  = "<input type='checkbox' name='" . $field['id'] . "' id='" . $field['id'] . "'" . $checked . "/>";
					$html[]  = "<label for='" . $field['id'] . "'>" . $field['desc'] . "</label>";
					break;

				default:
					echo 'wut O_o ?';
					break;
			}
			$html[] = "</td>";
			$html[] = "</tr>";
		}

		$html[] = "</table>";


		echo implode( "\n", $html );
	}

	public function render_meta_box_map_markers() {
		global $baidu_maps_marker_fields, $post;

		$prefix = 'baidu_maps_marker_meta_';

		wp_nonce_field( 'baidu_maps_meta_box_marker_details_nonce', 'baidu_maps_meta_box_markers_nonce' );

		$html[] = "<p>";
		$html[] = "<a href='#' class='button insert_marker'> Add Marker </a>";
		$html[] = "</p>";

		$markers = get_post_meta( $post->ID, 'markers', true );

		$html[] = "<div class='marker-container'>";
		if ( is_array( $markers ) ) {
			foreach ( $markers as $marker_count => $marker ) {
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
				$html[] = "<a href='#'class='button choose_image'> Choose Image </a>";
				$html[] = "<input class='icon-input' style='display: none;' type='text' name='" . $prefix . 'icon' . '-' . $marker_count . "' value='" . $meta_icon . "' >";
				$html[] = "<a href='#'class='button delete_marker'> Delete Marker </a>";
				$html[] = "<div class='img_wrap'> <img src='" . $meta_icon . "' width='32' height='32' ></div>";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_name marker_row_default'>";
				$html[] = "<label> Marker Name </label>";
				$html[] = "<input type='text' name='" . $prefix . 'name' . '-' . $marker_count . "' value='" . $meta_name . "' size='30' >";
				$html[] = "</div>";
				$html[] = "<div class='marker_row marker_row_description marker_row_default'>";
				$html[] = "<label> Marker Description </label>";
				$html[] = "<input type='text' name='" . $prefix . 'description' . '-' . $marker_count . "' value='" . $meta_description . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_location'>";
				$html[] = "<label> Latitude / Longitude </label>";
				$html[] = "<input type='text' name='" . $prefix . 'lat' . '-' . $marker_count . "' value='" . $meta_lat . "' size='30' >";
				$html[] = "<input type='text' name='" . $prefix . 'lng' . '-' . $marker_count . "' value='" . $meta_lng . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_color'>";
				$html[] = "<label> Background Color </label>";
				$html[] = "<input type='text' class='color-picker-control' name='" . $prefix . 'bgcolor' . '-' . $marker_count . "' value='" . $meta_bgcolor . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_color'>";
				$html[] = "<label> Font Color </label>";
				$html[] = "<input type='text' class='color-picker-control' name='" . $prefix . 'fgcolor' . '-' . $marker_count . "' value='" . $meta_fgcolor . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_is_open'>";
				$html[] = "<label> Show Marker Details  </label>";
				$html[] = "<input type='checkbox' name='" . $prefix . 'isopen' . '-' . $marker_count . "' " . $checked_isopen . "/>";
				$html[] = "<span class='caption'> Check to always show marker details</span>";
				$html[] = "</div>";

				$html[] = "</div>";
			}
		}

		$html[] = "</div>";

		echo implode( "\n", $html );
	}

	public function save_meta_box_map_details( $post_id ) {


		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		if ( ! isset( $_POST['baidu_maps_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['baidu_maps_meta_box_nonce'], 'baidu_maps_meta_box_map_details_nonce' ) ) return;

		if ( ! current_user_can( 'edit_post' ) ) return;

		$baidu_meta_maps_details = $this->populate_meta_box_map_details();

		foreach ( $baidu_meta_maps_details as $field ) {
			$old = get_post_meta( $post_id, $field['id'], true );
			$new = $_POST[$field['id']];
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
		$columns['marker_count'] = __( 'Makrers', 'bmap' );
		$columns['shortcode']    = __( 'Shortcode', 'bmap' );
		$columns['geolocation']  = __( 'Geolocation', 'bmap' );

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
					echo "No Location Defined";
				}

				break;

		}
	}

}