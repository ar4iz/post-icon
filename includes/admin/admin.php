<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class IconToTitleAdmin {
	public $post_array = array();

	private static $_this;
	private $settings;

	/**
	 * IconToTitleAdmin constructor.
	 */
	function __construct() {

		// Add actions.
		add_action( 'admin_menu', 				array( &$this, 'admin_menu' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'load_dashicons_front_end') );

		add_action( 'admin_init',  array(&$this, 'post_icon_register_setting') );

		add_filter( 'the_title', array(&$this, 'new_title'), 10, 2 );

		$this->settings = (array) get_option( 'post_icon-settings' );

		add_action('wp_ajax_add_field_setting', array(&$this, 'add_field_setting'));
	}

	function admin_menu() {
		add_options_page(__('Post icon', 'icon_to_title'), __('Post icon', 'icon_to_title'), 'manage_options', 'icon_to_title', array(&$this, 'icon_title_content'), '90');
	}


	function load_dashicons_front_end() {
		wp_enqueue_style( 'dashicons' );
	}

	function new_title( $title, $post_id ) {

		$options = get_option( 'post_icon-settings' );
		foreach ( $options as $k => $option ) {
			if (!is_admin()) {
				if ($post_id == $option) {
					$key = array_search($option, $options);
					$str = $key;
					preg_match('!\d+!', $str, $matches);
					$inside_k = $matches[0];
					$opt_icon_class = $options["icon_class_$inside_k"];
					$opt_icon_position = $options["icon-position_$inside_k"];
					if (empty($opt_icon_position) || $opt_icon_position == 'icon_to_after') {
						$new_title = $title . '<i class="dashicons '.$opt_icon_class.'"></i>';
					} else {
						$new_title = '<i class="dashicons '.$opt_icon_class.'"></i>' . $title;
					}
				}
			} else {
				$new_title = $title;
			}
		}

		return $new_title;
	}

	function icon_title_content() {

		echo '<div class="wrap">
			<h1>My Page Settings</h1>
			<form method="post" action="options.php">';

				settings_fields( 'post_icon_settings' ); // settings group name
				do_settings_sections( 'post_icon-slug' ); // just a page slug
				submit_button();

		echo '</form></div>';
	}

	public function select_all_posts() {
		$post_array = array();

		$post_types_names = array('post', 'product');

		$post_types_arg = array(
			'public' => true,
			'_builtin' => false
		);
		$output = 'names';
		$operator = 'and';
		$post_types = get_post_types( $post_types_arg, $output, $operator );
		if (!empty($post_types)) {
			foreach ( $post_types as $k => $post_type ) {
				$post_types_names[] = $post_type;
			}
		}

		$post_args = array(
			'post_type' => $post_types_names,
			'numberposts' => -1,
			'order' => 'ASC'
		);
		$post_count = 0;
		$post_array[''] = 'Select post';
		$posts = get_posts($post_args);
		foreach( $posts as $post ){
			setup_postdata($post);
			$post_array[$post->ID] = $post->post_title;
		}

		wp_reset_postdata();

		return $post_array;

	}

	function post_icon_register_setting(){

		register_setting(
			'post_icon_settings', // settings group name
			'post_icon-settings', // option name
			array(&$this, 'sanitize_fields') // sanitization function
		);

		add_settings_section(
			'some_settings_section_id', // section ID
			'', // title (if needed)
			'', // callback function (if needed)
			'post_icon-slug' // page slug
		);

		$general_options = get_option('post_icon-settings');
		if (!empty($general_options)) {
			$k = 0;
			foreach ( $general_options as $key => $general_option ) {
				$str = $key;
				preg_match('!\d+!', $str, $matches);
				$k = $matches[0];
				$true_field_params = array(
					'type'      => 'select',
					'id'        => "icon-for-selected-post_$k",
					'desc'      => 'Select post',
					'vals'		=> $this->select_all_posts()
				);
				add_settings_field( "icon-for-selected-post_$k", 'Select post', array(&$this, 'true_option_display_settings'),
					'post_icon-slug', 'some_settings_section_id', $true_field_params );


				$true_field_params = array(
					'type'      => 'text',
					'id'        => "icon_class_$k",
					'desc'      => 'Input dashicon class',
					'label_for' => 'icon_class'
				);
				add_settings_field( "icon_class_$k", 'Dashicon class', array(&$this, 'true_option_display_settings'), 'post_icon-slug', 'some_settings_section_id', $true_field_params );


				$true_field_params = array(
					'id' => "icon-position_$k",
					'type' => 'radio',
					'vals' => array('icon_to_before' => 'Before', 'icon_to_after' => 'After')
				);
				add_settings_field( "icon-position_$k", 'Select Icon position', array(&$this, 'true_option_display_settings'),
					'post_icon-slug', // page slug
					'some_settings_section_id', // section ID
					$true_field_params );

				$true_field_params = array(
					'type'      => 'title_text',
					'id'        => "title_icon_class_$k",
					'desc'      => 'Title dashicon',
					'label_for' => "title_icon_class_$k",
					'value'     => array('title_for_id' => $k, 'class_for_icon' => ''),
				);
				add_settings_field( "title_icon_class_$k", 'Title Dashicon', array(&$this, 'true_option_display_settings'), 'post_icon-slug', 'some_settings_section_id', $true_field_params );

			}
		} else {
			$k = 0;
			$true_field_params = array(
				'type'      => 'select',
				'id'        => "icon-for-selected-post_$k",
				'desc'      => 'Select post',
				'vals'		=> $this->select_all_posts()
			);
			add_settings_field( "icon-for-selected-post_$k", 'Select post', array(&$this, 'true_option_display_settings'),
				'post_icon-slug', 'some_settings_section_id', $true_field_params );


			$true_field_params = array(
				'type'      => 'text',
				'id'        => "icon_class_$k",
				'desc'      => 'Input dashicon class',
				'label_for' => 'icon_class'
			);
			add_settings_field( "icon_class_$k", 'Dashicon class', array(&$this, 'true_option_display_settings'), 'post_icon-slug', 'some_settings_section_id', $true_field_params );


			$true_field_params = array(
				'id' => "icon-position_$k",
				'type' => 'radio',
				'vals' => array('icon_to_before' => 'Before', 'icon_to_after' => 'After')
			);
			add_settings_field( "icon-position_$k", 'Select Icon position', array(&$this, 'true_option_display_settings'),
				'post_icon-slug', // page slug
				'some_settings_section_id', // section ID
				$true_field_params );

			$true_field_params = array(
				'type'      => 'title_text',
				'id'        => "title_icon_class_$k",
				'desc'      => 'Input dashicon class',
				'label_for' => "title_icon_class_$k",
				'value'     => array('title_for_id' => $k, 'class_for_icon' => ''),
			);
			add_settings_field( "title_icon_class_$k", 'Title Dashicon', array(&$this, 'true_option_display_settings'), 'post_icon-slug', 'some_settings_section_id', $true_field_params );

		}

		$true_field_params = array(
			'type'      => 'button',
			'id'        => 'add_new_rows',
			'label_for' => 'add_new_rows'
		);
		add_settings_field( 'add_new_rows', 'Select Another Post', array(&$this, 'submit_callback'), 'post_icon-slug', 'some_settings_section_id', $true_field_params );

		$true_field_params = array(
			'type'      => 'link',
			'id'        => 'deactivate_plugin',
			'label_for' => 'deactivate_plugin'
		);
		add_settings_field( 'deactivate_plugin', 'Deactivate Plugin', array(&$this, 'click_callback'), 'post_icon-slug', 'some_settings_section_id', $true_field_params );

	}

	function true_option_display_settings($args) {
		extract( $args );

		$option_name = 'post_icon-settings';

		$o = get_option( $option_name );

		switch ( $type ) {
			case 'text':
				if (!empty($o)) {
					$o[$id] = esc_attr( stripslashes($o[$id]) );
					echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='$o[$id]' />";
				} else {
					echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='' />";
				}
				echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
				break;
			case 'select':
				echo "<select id='$id' name='" . $option_name . "[$id]'>";
				foreach($vals as $v=>$l){
					$selected = ($o[$id] == $v) ? "selected='selected'" : '';
					echo "<option value='$v' $selected>$l</option>";
				}
				echo ($desc != '') ? $desc : "";
				echo "</select>";
				break;
			case 'radio':
				echo "<fieldset>";
				foreach($vals as $v=>$l){
					if (!empty($o)) {
						$checked = ($o[$id] == $v) ? "checked='checked'" : '';
					} else {
						$checked = '';
					}

					echo "<label><input type='radio' name='" . $option_name . "[$id]' value='$v' $checked />$l</label><br />";
				}
				echo "</fieldset>";
				break;
			case 'title_text':
				if (!empty($o)) {
					$o[$id] = esc_attr( stripslashes($o[$id]) );
					foreach ( $value as $key => $item ) {
						if ($key == 'title_for_id') {
							$opt_icon_position = $o["icon-position_$item"];
							if (empty($opt_icon_position) || $opt_icon_position == 'icon_to_after') {
								echo '<h3>'.get_the_title($o["icon-for-selected-post_$item"]).'<i class="dashicons '.$o["icon_class_$item"].'"></i></h3>';
							} else {
								echo '<h3><i class="dashicons '.$o["icon_class_$item"].'"></i> '.get_the_title($o["icon-for-selected-post_$item"]).'</h3>';
							}


						}
					}
				} else {
					echo '<h3><i class="dashicons "></i></h3>';
				}
		}

	}

	public function set_value( $key, $value ) {
		$this->settings[ $key ] = $value;
		return $this;
	}

	public function get_value( $key, $default = '' ) {
		if ( isset( $this->settings[ $key ] ) ) {
			return $this->settings[ $key ];
		}
		return $default;
	}

	public function submit_callback($args) {
		$item = $args['item'];

		$is   = esc_attr( $this->get_value( $item ) );

		echo "<button type='button' id='add_icon_to_posts' class='button button-primary' $is name='icon_post[" . $item . "]' />Add new post</button>";
	}

	public function click_callback($args) {
		$item = $args['item'];

		$is   = esc_attr( $this->get_value( $item ) );

		echo '<a href="plugins.php?action=deactivate&amp;plugin=icon_to_title%2Ficon_to_title.php&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=dbc4b4013f" id="deactivate_plugin_btn" class="button button-delete">Deactivate Plugin</a>';
	}


	function add_field_setting() {

		$count = isset($_POST['counts']) ?? $_POST['counts'];
		$k = $count;

		$true_field_params = array(
			'type'      => 'select',
			'id'        => "icon-for-selected-post_$k",
			'desc'      => 'Select post',
			'vals'		=> $this->select_all_posts()
		);
		add_settings_field( "icon-for-selected-post_$k", 'Select post', array(&$this, 'true_option_display_settings'),
			'post_icon-slug', 'some_settings_section_id', $true_field_params );

		$true_field_params = array(
			'type'      => 'text',
			'id'        => "icon_class_$k",
			'desc'      => 'Input dashicon class',
			'label_for' => 'icon_class'
		);
		add_settings_field( "icon_class_$k", 'Dashicon class', array(&$this, 'true_option_display_settings'), 'post_icon-slug', 'some_settings_section_id', $true_field_params );

		$true_field_params = array(
			'id' => "icon-position_$k",
			'type' => 'radio',
			'vals' => array('icon_to_before' => 'Before', 'icon_to_after' => 'After')
		);
		add_settings_field( "icon-position_$k", 'Select Icon position', array(&$this->settings, 'true_option_display_settings'),
			'post_icon-slug', // page slug
			'some_settings_section_id', // section ID
			$true_field_params );

		$true_field_params = array(
			'type'      => 'title_text',
			'id'        => "title_icon_class_$k",
			'desc'      => 'Title dashicon',
			'label_for' => "title_icon_class_$k",
			'value'     => array('title_for_id' => $k, 'class_for_icon' => ''),
		);
		add_settings_field( "title_icon_class_$k", 'Title Dashicon', array(&$this, 'true_option_display_settings'), 'post_icon-slug', 'some_settings_section_id', $true_field_params );

		wp_die();
	}


}

// Get it started
$IconToTitleAdmin = new IconToTitleAdmin();
