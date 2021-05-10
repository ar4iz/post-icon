(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).ready(function ($) {
		$(document).on('click', '#add_icon_to_posts', function () {

			var button_add = $(this);

			var parent_btn = button_add.parents('tr');

			var counts = $('input[name^="post_icon-settings"]').length / 3;

			// var rows = button_add.parents('tr').siblings('tr');
			var rows = '<tr><th scope="row">Select post</th><td><select id="icon-for-selected-post_'+counts+'" name="post_icon-settings[icon-for-selected-post_'+counts+']"><option value="">Select post</option><option value="1">Hello world!</option><option value="381">Fuck world</option><option value="383">Event test</option><option value="384">Portfolioi</option><option value="386">TestGal</option>Select post</select></td></tr><tr><th scope="row"><label for="icon_class">Dashicon class</label></th><td><input class="regular-text" type="text" id="icon_class_1" name="post_icon-settings[icon_class_'+counts+']" value=""><br><span class="description">Input dashicon class</span></td></tr><tr><th scope="row">Select Icon position</th><td><fieldset><label><input type="radio" name="post_icon-settings[icon-position_'+counts+']" value="icon_to_before">Before</label><br><label><input type="radio" name="post_icon-settings[icon-position_'+counts+']" value="icon_to_after">After</label><br></fieldset></td></tr><tr><th scope="row"><label for="title_icon_class_'+counts+'">Title Dashicon</label></th><td><h3><i class="dashicons "></i></h3></td></tr>';

			var data = {
				'action': 'add_field_setting',
				'counts': counts,
			}


			$.ajax({
				url: ajaxurl,
				data: data,
				type: 'POST',
				postType: 'json',
				beforeSend: function () {
					$(rows).insertBefore(parent_btn);
				},
				success: function (data) {
					console.log(data)
				}
			});


		})
	});

})( jQuery );
