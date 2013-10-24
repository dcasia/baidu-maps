(function ($) {

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

		$('.marker-controls .delete_marker').live('click', function (e) {
			e.preventDefault();
			remove_marker($(this));
		});

		$('.color-picker-control').wpColorPicker();


	});

	function add_marker_image($el) {
		var $parent = $el.parent();
		var $inputField = $parent.find(".icon-input");

		console.log($parent);
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
		var marker_count = $('.markers').length;
		var html = '';
		var defaultBGColor = '#e93e6b';
		var defaultFGColor = '#ffffff';

		html += "<div class='markers'>";
		html += "<div class='marker-controls'>";
		html += "<a href='#'class='button choose_image'> Choose Image </a>";
		html += "<input class='icon-input' style='display: none;' type='text' name='" + prefix + 'icon' + '-' + marker_count + "' value='" + pluginUrl + 'icons/marker.png' + "' >";
		html += "<a href='#'class='button delete_marker'> Delete Marker </a>";
		html += "<div class='img_wrap'> <img src='" + pluginUrl + 'icons/marker.png' + "' width='32' height='32' ></div>";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_name'>";
		html += "<label> Marker Name </label>";
		html += "<input type='text' name='" + prefix + 'name' + '-' + marker_count + "' value='' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_description marker_row_default'>";
		html += "<label> Marker Description </label>";
		html += "<input type='text' name='" + prefix + 'description' + '-' + marker_count + "' value='' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_location'>";
		html += "<label> Latitude / Longitude </label>";
		html += "<input type='text' name='" + prefix + 'lat' + '-' + marker_count + "' value='' size='30' >";
		html += "<input type='text' name='" + prefix + 'lng' + '-' + marker_count + "' value='' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_color'>";
		html += "<label> Background Color </label>";
		html += "<input type='text' class='color-picker-control' name='" + prefix + 'bgcolor' + '-' + marker_count + "' value='" + defaultBGColor + "' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_color'>";
		html += "<label> Font Color </label>";
		html += "<input type='text' class='color-picker-control' name='" + prefix + 'fgcolor' + '-' + marker_count + "' value='" + defaultFGColor + "' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_is_open'>";
		html += "<label> Show Marker Details  </label>";
		html += "<input type='checkbox' name='" + prefix + 'isopen' + '-' + marker_count + "' />";
		html += "<span class='caption'>Check to always show marker details</span>";

		html += "</div>";

		var $marker = $(html);
		$('.marker-container').append($marker);

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
		var $parent = jQuery($el).parent().parent();
		$parent.css('position', 'relative');
		$parent.animate({
			left: '100px',
			opacity : 0
		}, function(){
			$parent.remove();
		});

		reorder_markers();
	}

	function reorder_markers() {

		$('.markers').each(function (index) {
			$(this).find('input').each(function () {
				var name = $(this).attr('name');
				var name_split = name.split('-');

				$(this).attr('name', name_split[0] + '-' + index);
			});
		});
	}

	function mapSearch() {
		var $locationCheckUrl = $('.location-check-url');
		var $locationCheckBtn = $('.location-check-button');

		var searchSettings = {
			onSearchComplete: function (w) {
				if (typeof(w.getPoi(0)) != 'undefined'){
					map.centerAndZoom(w.getPoi(0).point, 12);
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

		map.addEventListener("click", function (c) {
			var $locationCheckResults = $('.location-check-results');
			var $locationCheckResults_lat = $locationCheckResults.find('.lat');
			var $locationCheckResults_lng = $locationCheckResults.find('.lng');

			$locationCheckResults_lat.html(c.point.lat);
			$locationCheckResults_lng.html(c.point.lng);
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

		map.addEventListener("ondblclick", function () {
			$locationCheckZoom.val(map.getZoom());
		});

		$grabFromMap.on('click', function (e) {
			e.preventDefault();
			var point = map.getCenter();
			console.log(map.getCenter());
			var $lat = $(this).parent().find('input').eq(0).val(point.lat);
			var $lng = $(this).parent().find('input').eq(1).val(point.lng);

		});

		// Insert new point
		$locationInsert = $('.location-check-insert');
		$locationCheckResults = $('.location-check-results');

		$locationInsert.on('click', function (e) {
			e.preventDefault();
			var $marker = insert_marker();

			var lat = $locationCheckResults.find('.lat').text();
			var lng = $locationCheckResults.find('.lng').text();

			$marker.find('.marker_row_location input').eq(0).val(lat);
			$marker.find('.marker_row_location input').eq(1).val(lng);

		});

	}

})(jQuery)