<?php
/*
Plugin Name: WP Canvas - Image Widget
Plugin URI: http://webplantmedia.com/starter-themes/wordpresscanvas/features/widgets/wordpress-canvas-widgets/
Description: Add image to any widget area.
Author: Chris Baldelomar
Author URI: http://webplantmedia.com/
Version: 1.1
License: GPLv2 or later
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPC_IMAGE_WIDGET_VERSION', '1.1' );

function wpc_image_widget_enqueue_admin_scripts() {
	$screen = get_current_screen();

	if ( 'widgets' == $screen->id ) {
		wp_deregister_style( 'wpc-widgets-admin-style' );
		wp_deregister_script( 'wpc-widgets-admin-js' );

		wp_register_style( 'wpc-widgets-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), WPC_IMAGE_WIDGET_VERSION, 'all' );
		wp_enqueue_style( 'wpc-widgets-admin-style' );

		wp_enqueue_media();
		wp_register_script( 'wpc-widgets-admin-js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array ( 'jquery' ), WPC_IMAGE_WIDGET_VERSION, true );
		wp_enqueue_script( 'wpc-widgets-admin-js' );
	}
}
add_action('admin_enqueue_scripts', 'wpc_image_widget_enqueue_admin_scripts' );

function wpc_image_widget_customize_enqueue() {
	wp_deregister_style( 'wpc-widgets-admin-style' );
	wp_deregister_script( 'wpc-widgets-admin-js' );

	wp_register_style( 'wpc-widgets-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), WPC_IMAGE_WIDGET_VERSION, 'all' );
	wp_enqueue_style( 'wpc-widgets-admin-style' );

	wp_enqueue_media();
	wp_register_script( 'wpc-widgets-admin-js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array ( 'jquery' ), WPC_IMAGE_WIDGET_VERSION, true );
	wp_enqueue_script( 'wpc-widgets-admin-js' );
}
add_action( 'customize_controls_enqueue_scripts', 'wpc_image_widget_customize_enqueue' );

function wpc_image_widget_widgets_init() {
	register_widget('WPC_Image_Widget');
}
add_action('widgets_init', 'wpc_image_widget_widgets_init');

class WPC_Image_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'description' => __('An image for your sidebar and footer') );
		parent::__construct( 'wpc_image', __('WPC Image', 'wpc_widgets' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		echo $before_widget;

		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( $title )
			echo $before_title . esc_html( $title ) . $after_title;

		if ( '' != $instance['img_url'] ) {

			$output = '<img src="' . esc_attr( $instance['img_url'] ) .'" ';
			if ( '' != $instance['alt_text'] )
				$output .= 'alt="' . esc_attr( $instance['alt_text'] ) .'" ';
			if ( '' != $instance['img_title'] )
				$output .= 'title="' . esc_attr( $instance['img_title'] ) .'" ';
			$output .= '/>';

			if ( '' != $instance['link'] )
				$output = '<a class="thumbnail-link image-hover" href="' . esc_attr( $instance['link'] ) . '">' . $output . '</a>';

			$allowed_html = array(
				'a' => array(
					'href' => array(),
					'title' => array(),
					'target' => array(),
				),
				'br' => array(),
				'em' => array(),
				'strong' => array(),
			);
			if ( '' != $instance['caption'] )
				$output = $output . '<p class="sidebar-caption">' . wp_kses( $instance['caption'], $allowed_html ) . '</p>';

			echo '<div class="wpc-widgets-image-container">' . do_shortcode( $output ) . '</div>';
		}

		echo "\n" . $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['img_url'] = esc_url( $new_instance['img_url'], null, 'display' );
		$instance['alt_text'] = strip_tags( $new_instance['alt_text'] );
		$instance['img_title'] = strip_tags( $new_instance['img_title'] );
		$instance['caption'] = $new_instance['caption'];
		$instance['link'] = esc_url( $new_instance['link'], null, 'display' );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance,
			array(
				'title' => '',
				'img_url' => '',
				'alt_text' => '',
				'img_title' => '',
				'caption' => '',
				'img_width' => '',
				'img_height' => '',
				'link' => ''
			));

		$title = esc_attr( $instance['title'] );
		$img_url = esc_url( $instance['img_url'], null, 'display' );
		$imagestyle = '';

		if ( empty( $img_url ) )
			$imagestyle = ' style="display:none"';

		$alt_text = esc_attr( $instance['alt_text'] );
		$img_title = esc_attr( $instance['img_title'] );
		$caption = esc_attr( $instance['caption'] );

		$link = esc_url( $instance['link'], null, 'display' );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo esc_html__( 'Widget title:', 'wpc_widgets' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<div class="wpc-widgets-image-field">
			<input class="widefat" id="<?php echo $this->get_field_id( 'img_url' ); ?>" name="<?php echo $this->get_field_name( 'img_url' ); ?>" type="text" value="<?php echo $img_url; ?>" />
			<a class="wpc-widgets-image-upload button inline-button" data-target="#<?php echo $this->get_field_id( 'img_url' ); ?>" data-preview=".wpc-widgets-preview-image" data-frame="select" data-state="wpc_widgets_insert_single" data-fetch="url" data-title="Insert Image" data-button="Insert" data-class="media-frame wpc-widgets-custom-uploader" title="Add Media">Add Media</a>
			<a class="button wpc-widgets-delete-image" data-target="#<?php echo $this->get_field_id( 'img_url' ); ?>" data-preview=".wpc-widgets-preview-image">Delete</a>
			<div class="wpc-widgets-preview-image"<?php echo $imagestyle; ?>><img src="<?php echo esc_attr( $img_url ); ?>" /></div>
		</div>
		<p>
			<label for="<?php echo $this->get_field_id( 'alt_text' ); ?>"><?php echo esc_html__( 'Alternate text:', 'wpc_widgets' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'alt_text' ); ?>" name="<?php echo $this->get_field_name( 'alt_text' ); ?>" type="text" value="<?php echo $alt_text; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'img_title' ); ?>"><?php echo esc_html__( 'Image title:', 'wpc_widgets' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'img_title' ); ?>" name="<?php echo $this->get_field_name( 'img_title' ); ?>" type="text" value="<?php echo $img_title; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'caption' ); ?>"><?php echo esc_html__( 'Caption:', 'wpc_widgets' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'caption' ); ?>" name="<?php echo $this->get_field_name( 'caption' ); ?>" type="text" value="<?php echo $caption; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php echo esc_html__( 'Link URL (when the image is clicked):', 'wpc_widgets' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" type="text" value="<?php echo $link; ?>" />
			</label>
		</p>
		<?php
	}
}
