<?php
/*
Plugin Name: WP Canvas - Image Widget
Plugin URI: http://webplantmedia.com/starter-themes/wordpresscanvas/features/widgets/wordpress-canvas-widgets/
Description: Add image to any widget area.
Author: Chris Baldelomar
Author URI: http://webplantmedia.com/
Version: 1.4
License: GPLv2 or later
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPC_IMAGE_WIDGET_VERSION', '1.4' );

function wpc_image_widget_enqueue_admin_scripts( $hook ) {
	if ( $hook == 'post-new.php' || $hook == 'post.php' || $hook == 'widgets.php' ) {
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
		$text_align = isset( $instance['text_align'] ) ? $instance['text_align'] : '';

		$d_style = '';
		if ( ! empty( $text_align ) ) {
			$text_align = $this->sanitize_text_align( $text_align );
			$d_style = ' style="text-align:'.$text_align.';"';
		}

		if ( $title )
			echo $before_title . esc_html( $title ) . $after_title;

		$output = '';

		if ( '' != $instance['img_url'] ) {

			$output .= '<img src="' . esc_url( $instance['img_url'] ) .'" ';
			if ( '' != $instance['img_2x_url'] )
				$output .= 'srcset="' . esc_url( $instance['img_url'] ) . ' 1x, ' . esc_url( $instance['img_2x_url'] ) . ' 2x" ';
			if ( '' != $instance['alt_text'] )
				$output .= 'alt="' . esc_attr( $instance['alt_text'] ) .'" ';
			if ( '' != $instance['img_title'] )
				$output .= 'title="' . esc_attr( $instance['img_title'] ) .'" ';
			$output .= '/>';

			if ( '' != $instance['link'] )
				$output = '<a class="thumbnail-link image-hover" style="text-align:center;" href="' . esc_url( $instance['link'] ) . '">' . $output . '</a>';

			$output = '<div style="text-align:center;">' . $output . '</div>';

			if ( '' != $instance['caption'] )
				$output = $output . '<div class="sidebar-caption"'.$d_style.'>' . wpautop( $instance['caption'] ) . '</div>';

			echo '<div class="wpc-widgets-image-container">' . $output . '</div>';
		}

		echo "\n" . $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['img_url'] = esc_url_raw( $new_instance['img_url'] );
		$instance['img_2x_url'] = esc_url_raw( $new_instance['img_2x_url'] );
		$instance['alt_text'] = sanitize_text_field( $new_instance['alt_text'] );
		$instance['img_title'] = sanitize_text_field( $new_instance['img_title'] );
		$instance['caption'] = wp_kses_post( $new_instance['caption'] );
		$instance['text_align'] = $this->sanitize_text_align( $new_instance['text_align'] );
		$instance['link'] = esc_url_raw( $new_instance['link'] );

		return $instance;
	}

	function sanitize_text_align( $text_align ) {
		$whitelist = array( 'left', 'center', 'right' );
		if ( ! in_array( $text_align, $whitelist ) )
			$text_align = 'center';

		return $text_align;
	}

	function form( $instance ) {
		$title = $instance['title'];
		$img_url = $instance['img_url'];
		$img_2x_url = $instance['img_2x_url'];
		$imagestyle = '';

		if ( empty( $img_url ) )
			$imagestyle = ' style="display:none"';

		$alt_text = $instance['alt_text'];
		$img_title = $instance['img_title'];
		$caption = $instance['caption'];
		$text_align = isset( $instance['text_align'] ) ? $this->sanitize_text_align( $instance['text_align'] ) : 'center';

		$link = $instance['link'];

		?>
		<div class="wpc-image-wrapper">
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo esc_html__( 'Widget title:', 'wpc_widgets' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				</label>
			</p>
			<div class="wpc-widgets-image-field">
				<label for="<?php echo $this->get_field_id( 'img_url' ); ?>"><?php echo esc_html__( 'Image URL:', 'wpc_widgets' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'img_url' ); ?>" name="<?php echo $this->get_field_name( 'img_url' ); ?>" type="text" value="<?php echo esc_url( $img_url ); ?>" />
				</label>
				<a class="wpc-widgets-image-upload button inline-button" data-target="#<?php echo $this->get_field_id( 'img_url' ); ?>" data-preview=".wpc-widgets-preview-image" data-frame="select" data-state="wpc_widgets_insert_single" data-fetch="url" data-title="Insert Image" data-button="Insert" data-class="media-frame wpc-widgets-custom-uploader" title="Add Media">Add Media</a>
				<a class="button wpc-widgets-delete-image" data-target="#<?php echo $this->get_field_id( 'img_url' ); ?>" data-preview=".wpc-widgets-preview-image">Delete</a>
				<div class="wpc-widgets-preview-image"<?php echo $imagestyle; ?>><img src="<?php echo esc_url( $img_url ); ?>" /></div>
			</div>
			<div class="wpc-widgets-image-field">
				<label for="<?php echo $this->get_field_id( 'img_2x_url' ); ?>"><?php echo esc_html__( 'Image 2x URL (Retina Displays):', 'wpc_widgets' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'img_2x_url' ); ?>" name="<?php echo $this->get_field_name( 'img_2x_url' ); ?>" type="text" value="<?php echo esc_url( $img_2x_url ); ?>" />
				</label>
				<a class="wpc-widgets-image-upload button inline-button" data-target="#<?php echo $this->get_field_id( 'img_2x_url' ); ?>" data-preview=".wpc-widgets-preview-image" data-frame="select" data-state="wpc_widgets_insert_single" data-fetch="url" data-title="Insert Image" data-button="Insert" data-class="media-frame wpc-widgets-custom-uploader" title="Add Media">Add Media</a>
				<a class="button wpc-widgets-delete-image" data-target="#<?php echo $this->get_field_id( 'img_2x_url' ); ?>" data-preview=".wpc-widgets-preview-image">Delete</a>
				<div class="wpc-widgets-preview-image"<?php echo $imagestyle; ?>><img src="<?php echo esc_url( $img_2x_url ); ?>" /></div>
			</div>
			<p>
				<label for="<?php echo $this->get_field_id( 'alt_text' ); ?>"><?php echo esc_html__( 'Alternate text:', 'wpc_widgets' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'alt_text' ); ?>" name="<?php echo $this->get_field_name( 'alt_text' ); ?>" type="text" value="<?php echo esc_attr( $alt_text ); ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'img_title' ); ?>"><?php echo esc_html__( 'Image title:', 'wpc_widgets' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'img_title' ); ?>" name="<?php echo $this->get_field_name( 'img_title' ); ?>" type="text" value="<?php echo esc_attr( $img_title ); ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'caption' ); ?>"><?php echo esc_html__( 'Caption:', 'wpc_widgets' ); ?>
				<textarea class="widefat" rows="4" cols="20" id="<?php echo $this->get_field_id('caption'); ?>" name="<?php echo $this->get_field_name('caption'); ?>"><?php echo esc_textarea( $caption ); ?></textarea>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('text_align'); ?>"><?php _e('Text Align:'); ?></label>
				<select id="<?php echo $this->get_field_id('text_align'); ?>" name="<?php echo $this->get_field_name('text_align'); ?>">
					<option value="left"<?php selected( $text_align, 'left' ); ?>>Left</option>';
					<option value="center"<?php selected( $text_align, 'center' ); ?>>Center</option>';
					<option value="right"<?php selected( $text_align, 'right' ); ?>>Right</option>';
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php echo esc_html__( 'Link URL (when the image is clicked):', 'wpc_widgets' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" type="text" value="<?php echo esc_url( $link ); ?>" />
				</label>
			</p>
		</div>
		<?php
	}
}
