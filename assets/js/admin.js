(function ($) {

	var markerTypes = [
		marker1: {
			name: '',
			icon: '',
			description: '',
			fields: {

			}
		}
	]

	$(document).ready(function () {

		mapSearch();
		mapPointer();
		mapUtils();

		$('.insert_marker').live('click', function (e) {
			e.preventDefault();
			insert_marker($(this));
		});

		$('.marker-controls .choose_image').live('click', function (e) {
			e.preventDefault();
			add_marker_image($(this));
		});

		$('.delete_marker').live('click', function (e) {
			e.preventDefault();
			remove_marker($(this));
		});

		$('.color-picker-control').wpColorPicker();

	});

	function add_marker_image($el) {
		var $parent = $el.parent();
		var $inputField = $parent.find(".icon-input");

		tb_show('', 'media-upload.php?TB_iframe=true');

		window.send_to_editor = function (html) {
			var image_url = $('img', html).attr('src');
			$inputField.val(image_url);
			$parent.find(".img_wrap")
					.html('<img src="' + image_url + '" height="32" width="32" />');
			tb_remove();
		};
	}

	function insert_marker($el) {
		var prefix = 'baidu_maps_marker_meta_';
		var marker_count = $('.marker').length;
		var html = '';
		var defaultBGColor = '#e93e6b';
		var defaultFGColor = '#ffffff';

		html += "<div class='marker'>";
		html += "<div class='marker-tracker'>" + marker_count + "</div>";
		html += "<span class='delete_marker'>x</span>";

		html += "<div class='marker-details'>";
		html += "<div class='marker_row marker_row_default marker_row_name'>";
		html += "<input type='text' name='" + prefix + 'name' + '-' + marker_count + "' placeholder='Marker Name' value='' size='30' >";
		html += "</div>";
		html += "<div class='marker_row marker_row_location'>";
		html += "<input type='text' placeholder='Latitude' name='" + prefix + 'lat' + '-' + marker_count + "' value='' size='30' >";
		html += "<input type='text' placeholder='Longitude' name='" + prefix + 'lng' + '-' + marker_count + "' value='' size='30' >";
		html += "</div>";
		html += "</div>";

		html += "<div class='marker-type'>";
		html += "<ul class='marker-type_list'>";
		html += "<li class='marker-type_list-item'><a href='#' class='button'>1</a></li>";
		html += "<li class='marker-type_list-item'><a href='#' class='button'>2</a></li>";
		html += "<li class='marker-type_list-item'><a href='#' class='button'>3</a></li>";
		html += "<li class='marker-type_list-item'><a href='#' class='button'>4</a></li>";
		html += "</ul>";
		html += "</div>";

		html +="<div class='marker-setup'>";

		html +="<div class='type-placeholder'>Select a Marker Type</div>";

		html +="</div>";

		// html += "<div class='marker_row marker_row_description marker_row_default'>";
		// html += "<label> Marker Description </label>";
		// html += "<input type='text' name='" + prefix + 'description' + '-' + marker_count + "' value='' size='30' >";
		// html += "</div>";


		// html += "<div class='marker_row marker_row_default marker_row_color'>";
		// html += "<label> Background Color </label>";
		// html += "<input type='text' class='color-picker-control' name='" + prefix + 'bgcolor' + '-' + marker_count + "' value='" + defaultBGColor + "' size='30' >";
		// html += "</div>";

		// html += "<div class='marker_row marker_row_default marker_row_color'>";
		// html += "<label> Font Color </label>";
		// html += "<input type='text' class='color-picker-control' name='" + prefix + 'fgcolor' + '-' + marker_count + "' value='" + defaultFGColor + "' size='30' >";
		// html += "</div>";

		// html += "<div class='marker_row marker_row_default marker_row_is_open'>";
		// html += "<label> Show Marker Details  </label>";
		// html += "<input type='checkbox' name='" + prefix + 'isopen' + '-' + marker_count + "' />";
		// html += "<span class='caption'>Check to always show marker details</span>";
		// html += "</div>";

		html += "</div>";

		var $marker = $(html);
		$('.markers-container').append($marker);

		$('html,body').animate({
			scrollTop: $marker.offset().top - 100
		});

		$marker.css('background-color', '#fafad2');

		setTimeout(function () {
			$marker.animate({
				backgroundColor: 'transparent'
			});
		}, 1000)

		$('.color-picker-control').wpColorPicker();

		return $marker;
	}

	function remove_marker($el) {
		var $parent = jQuery($el).parent();
		$parent.css('position', 'relative');
		$parent.animate({
			left   : '100px',
			opacity: 0
		}, {
			complete: function () {
				$parent.remove();
				reorder_markers();
			}
		});


	}

	function reorder_markers() {

		$('.marker').each(function (index) {
			$(this).find('input').each(function () {
				var name = $(this).attr('name');
				if (typeof(name) != 'undefined') {
					var name_split = name.split('-');
					$(this).attr('name', name_split[0] + '-' + index);
				}
			});
		});
	}

	function centerHere(point, zoom) {

		if (zoom < 16) zoom = 16;
		map.centerAndZoom(point, zoom);
		$('.location-check-zoom').val(zoom);

		var myIcon = new BMap.Icon(pluginUrl + 'assets/icons/target.png', new BMap.Size(32, 32));
		var marker_icon = new BMap.Marker(point, {icon: myIcon});

		$('.BMap_Marker').remove();
		map.addOverlay(marker_icon);

		var $locationCheckResults = $('.location-check-results');
		var $locationCheckResults_lat = $locationCheckResults.find('.lat');
		var $locationCheckResults_lng = $locationCheckResults.find('.lng');

		$locationCheckResults_lat.html(point.lat);
		$locationCheckResults_lng.html(point.lng);

	}

	function mapSearch() {
		var $locationCheckUrl = $('.location-check-url');
		var $locationCheckBtn = $('.location-check-button');

		var searchSettings = {
			onSearchComplete: function (w) {

				if (typeof(w.getPoi(0)) != 'undefined') {
					var zoom = $('.location-check-zoom').val();
					centerHere(w.getPoi(0).point, zoom);
				}
			}
		}
		var search = new BMap.LocalSearch(map, searchSettings);

		$locationCheckBtn.on('click', function (e) {
			e.preventDefault();
			baiduMapSearchFunction();
		});

		$locationCheckUrl.keypress(function (e) {
			if (e.keyCode == 13) {
				$(this).trigger('enter');
				return false;
			}
		});
		$locationCheckUrl.on('enter', function (e) {
			baiduMapSearchFunction();
		});

		function baiduMapSearchFunction() {

			var searchString = $locationCheckUrl.val();
			if (searchString != '') {
				search.search(searchString);
			}
		}
	}

	function mapPointer() {
		map.addEventListener("mousemove", function (c) {
			// var point = map.pixelToPoint(new BMap.Pixel(e.clientX, e.clientY));
			$('.location-check-currc').find('.lat').html(c.point.lat);
			$('.location-check-currc').find('.lng').html(c.point.lng);
		});

		map.addEventListener("dblclick", function (c) {
			return;
		});

		var delay = 300;
		clicks = 0;
		timer = null;

		map.addEventListener("click", function (c) {

			clicks++;

			if (clicks == 1) {

				timer = setTimeout(function () {
					var $locationCheckResults = $('.location-check-results');
					var $locationCheckResults_lat = $locationCheckResults.find('.lat');
					var $locationCheckResults_lng = $locationCheckResults.find('.lng');

					$locationCheckResults_lat.html(c.point.lat);
					$locationCheckResults_lng.html(c.point.lng);

					var myIcon = new BMap.Icon(pluginUrl + 'assets/icons/target.png', new BMap.Size(32, 32));
					var marker_icon = new BMap.Marker(c.point, {icon: myIcon});
					$('.BMap_Marker').remove();
					map.addOverlay(marker_icon);

					clicks = 0;
				}, delay);

			} else {
				map.zoomIn();

				clearTimeout(timer);
				clicks = 0;
			}

		});
	}

	function mapUtils() {
		var $locationCheckZoom = $('.location-check-zoom');
		var $grabFromMap = $('.marker_row_location_grab');

		$locationCheckZoom.on('change', function () {
			var zoom = $(this).val();
			if (zoom > 0 && zoom < 20) {
				map.zoomTo(parseInt(zoom));
			}
		});

		$locationCheckZoom.keypress(function (e) {
			if (e.keyCode == 13) {
				$(this).trigger('enter');
				return false;
			}
		});
		$locationCheckZoom.on('enter', function (e) {
			var zoom = $(this).val();
			if (zoom > 0 && zoom < 20) {
				map.zoomTo(parseInt(zoom));
			}
		});

		map.addEventListener("ondblclick", function () {
			$locationCheckZoom.val(map.getZoom());
		});

		$grabFromMap.on('click', function (e) {
			e.preventDefault();
			var point = map.getCenter();
			var $lat = $(this).parent().find('input').eq(0).val(point.lat);
			var $lng = $(this).parent().find('input').eq(1).val(point.lng);

		});

		// Insert new point
		$locationInsert = $('.location-check-insert');
		$locationCenter = $('.location-check-center');
		$locationCheckResults = $('.location-check-results');

		$locationInsert.on('click', function (e) {
			e.preventDefault();
			var $marker = insert_marker();

			var lat = $locationCheckResults.find('.lat').text();
			var lng = $locationCheckResults.find('.lng').text();

			$marker.find('.marker_row_location input').eq(0).val(lat);
			$marker.find('.marker_row_location input').eq(1).val(lng);

		});

		$locationCenter.on('click', function (e) {
			e.preventDefault();

			var zoom = $('.location-check-zoom').val();
			var lat = $locationCheckResults.find('.lat').text();
			var lng = $locationCheckResults.find('.lng').text();

			$('#baidu_maps_meta_zoom').val(zoom);
			$('#baidu_maps_meta_center_lat').val(lat);
			$('#baidu_maps_meta_center_lng').val(lng);

		});

	}

})(jQuery)