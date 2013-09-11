(function($){

	$(document).ready(function() {

		$('.insert_marker').live('click', function(e){
			e.preventDefault();
			insert_marker($(this));
		});

		$('.marker-controls .choose_image').live('click', function(e){
			e.preventDefault();
			add_marker_image($(this));
		});

		$('.marker-controls .delete_marker').live('click', function(e){
			e.preventDefault();
			remove_marker($(this));
		});

		$('.color-picker-control').wpColorPicker();
		
	});

	function add_marker_image($el){
		var $parent = $el.parent();
        var $inputField = $parent.find(".icon-input");

        console.log($parent);
		tb_show('', 'media-upload.php?TB_iframe=true');

		window.send_to_editor = function(html) {
			var image_url = $('img',html).attr('src');
			$inputField.val(image_url);
			$parent.find(".img_wrap")
			.html('<img src="' + image_url + '" height="32" width="32" />');
			tb_remove();
		};
	}

	function insert_marker($el){
		var prefix = 'baidu_maps_marker_meta_';
		var marker_count = $('.markers').length;
		var html = '';
		var defaultBGColor = '#e93e6b';
		var defaultFGColor = '#ffffff';

		html+= "<div class='markers'>";
		html+= "<div class='marker-controls'>";
		html+= "<a href='#'class='button choose_image'> Choose Image </a>";
		html+= "<input class='icon-input' style='display: none;' type='text' name='" + prefix + 'icon' + '-' + marker_count + "' value='' >";
		html+= "<a href='#'class='button delete_marker'> Delete Marker </a>";
		html+= "<div class='img_wrap'> <img src='' width='32' height='32' ></div>";
		html+= "</div>";

		html+= "<div class='marker_row marker_row_default marker_row_name'>";
		html+= "<label> Marker Name </label>";
		html+= "<input type='text' name='" + prefix + 'name' + '-' + marker_count + "' value='' size='30' >";
		html+= "</div>";
		html+= "<div class='marker_row marker_row_location'>";
		html+= "<label> Latitude / Longitude </label>";
		html+= "<input type='text' name='" + prefix + 'lat' + '-' + marker_count + "' value='' size='30' >";
		html+= "<input type='text' name='" + prefix + 'lng' + '-' + marker_count + "' value='' size='30' >";
		html+= "</div>";

		html+= "<div class='marker_row marker_row_default marker_row_color'>";
		html+= "<label> Background Color </label>";
		html+= "<input type='text' class='color-picker-control' name='" + prefix + 'bgcolor' + '-' + marker_count + "' value='" + defaultBGColor + "' size='30' >";
		html+= "</div>";

		html+= "<div class='marker_row marker_row_default marker_row_color'>";
		html+= "<label> Font Color </label>";
		html+= "<input type='text' class='color-picker-control' name='" + prefix + 'fgcolor' + '-' + marker_count + "' value='" + defaultFGColor + "' size='30' >";
		html+= "</div>";

		html+= "<div class='marker_row marker_row_default marker_row_is_open'>";
		html+= "<label> Show Marker Details  </label>";
		html+= "<input type='checkbox' name='" + prefix + 'isopen' + '-' + marker_count + "' />"; 
		html+= "<span class='caption'> Check to always show marker details</span>";

		html+= "</div>";

		$('.marker-container').append(html);
		$('.color-picker-control').wpColorPicker();
	}

	function remove_marker($el) {
		var parent = jQuery($el).parent().parent();
		parent.remove();

		reorder_markers();
	}

	function reorder_markers(){

		$('.markers').each(function(index) {
			$(this).find('input').each(function(){
				var name = $(this).attr('name');
				var name_split = name.split('-');

				$(this).attr('name', name_split[0] + '-' + index);
			});
		});
	}

}) (jQuery)