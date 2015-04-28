<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class Image_Text_Widget extends WP_Widget {

	private $itw_defaults = array();
	private $itw_image_positions = array();
	private $itw_text_types = array();
	private $itw_link_types = array();
	private $itw_link_targets = array();
	private $itw_aligns = array();
	private $itw_pages = array();
	private $itw_image_sizes = array();

	public function __construct() {
		parent::__construct(
			'Image_Text_Widget', __( 'Image & Text Widget', 'image-text-widget' ), array(
			'description' => __( 'Displays image and text', 'image-text-widget' )
			)
		);

		$this->itw_defaults = array(
			'title'				 => '',
			'image_id'			 => 0,
			'image_align'		 => 'none',
			'image_position'	 => 'above_text',
			'text_align'		 => 'none',
			'text'				 => '',
			'text_type'			 => 'plain',
			'link_type'			 => 'custom',
			'link_target'		 => 'same',
			'link_page_id'		 => 0,
			'link_custom_url'	 => '',
			'size'				 => 'thumbnail',
			'size_custom_width'	 => 220,
			'size_custom_height' => 140,
			'responsive'		 => true
		);

		$this->itw_image_positions = array(
			'above_text' => __( 'Above the text', 'image-text-widget' ),
			'below_text' => __( 'Below the text', 'image-text-widget' )
		);

		$this->itw_text_types = array(
			'plain'	 => __( 'plain', 'image-text-widget' ),
			'autobr' => __( 'auto br', 'image-text-widget' ),
			'html'	 => __( 'HTML', 'image-text-widget' )
		);

		$this->itw_link_types = array(
			'none'	 => __( 'none', 'image-text-widget' ),
			'custom' => __( 'custom', 'image-text-widget' ),
			'page'	 => __( 'page', 'image-text-widget' )
		);

		$this->itw_link_targets = array(
			'same'	 => __( 'same window', 'image-text-widget' ),
			'new'	 => __( 'new window', 'image-text-widget' )
		);

		$this->itw_aligns = array(
			'none'		 => __( 'none', 'image-text-widget' ),
			'left'		 => __( 'left', 'image-text-widget' ),
			'center'	 => __( 'center', 'image-text-widget' ),
			'right'		 => __( 'right', 'image-text-widget' ),
			'justify'	 => __( 'justify', 'image-text-widget' )
		);

		$this->itw_pages = get_pages(
			array(
				'sort_column'	 => 'post_title',
				'sort_order'	 => 'asc',
				'number'		 => 0
			)
		);

		$this->itw_image_sizes = array_merge( array( 'full', 'custom' ), get_intermediate_image_sizes() );
		sort( $this->itw_image_sizes, SORT_STRING );
	}

	public function widget( $args, $instance ) {
		$title = '';

		if ( $instance['title'] !== '' ) {
			$title = $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		if ( $instance['text_type'] === 'plain' )
			$instance['text'] = $instance['text'];
		elseif ( $instance['text_type'] === 'autobr' )
			$instance['text'] = nl2br( $instance['text'] );
		elseif ( $instance['text_type'] === 'html' )
			$instance['text'] = html_entity_decode( $instance['text'], ENT_QUOTES, 'UTF-8' );

		if ( $instance['size'] !== 'custom' )
			$image = wp_get_attachment_image_src( $instance['image_id'], $instance['size'], false );
		else
			$image = wp_get_attachment_image_src( $instance['image_id'], array( $instance['size_custom_width'], $instance['size_custom_height'] ), false );

		if ( $instance['link_type'] === 'page' ) {

			// WPML support for pages
			if ( function_exists( 'icl_object_id' ) ) {
				$page_id = icl_object_id( $instance['link_page_id'], 'page', false );
			} else {
				$page_id = $instance['link_page_id'];
			}
			$href = get_permalink( $page_id );
		} elseif ( $instance['link_type'] === 'custom' )
			$href = $instance['link_custom_url'];
		else
			$href = '';

		if ( $instance['text_align'] === 'left' )
			$text_align = ' style="text-align: left; display: block;"';
		elseif ( $instance['text_align'] === 'center' )
			$text_align = ' style="text-align: center; display: block;"';
		elseif ( $instance['text_align'] === 'right' )
			$text_align = ' style="text-align: right; display: block;"';
		elseif ( $instance['text_align'] === 'justify' )
			$text_align = ' style="text-align: justify; display: block;"';
		else
			$text_align = '';

		if ( $instance['image_align'] === 'left' )
			$image_align = ' style="float: left;"';
		elseif ( $instance['image_align'] === 'center' )
			$image_align = ' style="margin-left: auto; margin-right: auto; display: block;"';
		elseif ( $instance['image_align'] === 'right' )
			$image_align = ' style="float: right;"';
		else
			$image_align = '';

		$text = apply_filters( 'itw_widget_text', $instance['text'] );
		$image = apply_filters( 'itw_widget_image', $image, $instance );
		$image_position = apply_filters( 'itw_widget_image_position', $instance['image_position'], $instance );
		$href = apply_filters( 'itw_widget_link', $href, $instance );
		$text_align = apply_filters( 'itw_widget_text_align', $text_align, $instance );
		$image_align = apply_filters( 'itw_widget_align_image', $image_align, $instance );
		$width = apply_filters( 'itw_widget_width', ($instance['responsive'] === false ? $image[1] : '100%' ), $instance );
		$height = apply_filters( 'itw_widget_height', ($instance['responsive'] === false ? $image[2] : 'auto' ), $instance );
		$post = apply_filters( 'itw_widget_post', get_post( $instance['image_id'] ), $instance );
		$image_title = apply_filters( 'itw_widget_image_title', (isset( $post->post_title ) ? $post->post_title : '' ), $instance );
		$alt = apply_filters( 'itw_widget_image_alt', (string) get_post_meta( $instance['image_id'], '_wp_attachment_image_alt', true ), $instance );

		$html = $args['before_widget'] . $title;
		$html .= '<div class="widget-content">';
		if ( $image_position === 'above_text' ) {
			$html .= ($href !== '' ? '<a href="' . $href . '"' . ($instance['link_target'] === 'new' ? ' target="_blank"' : '') . '>' : '') . '<img class="image-text-widget-image" src="' . $image[0] . '" width="' . $width . '" height="' . $height . '" title="' . $image_title . '" alt="' . $alt . '"' . $image_align . ' />' . ($href !== '' ? '</a>' : '');
			$html .= '<div class="image-text-widget-text"' . $text_align . '>' . $text . '</div>';
		} elseif ( $image_position === 'below_text' ) {
			$html .= '<div class="image-text-widget-text"' . $text_align . '>' . $text . '</div>';
			$html .= ($href !== '' ? '<a href="' . $href . '"' . ($instance['link_target'] === 'new' ? ' target="_blank"' : '') . '>' : '') . '<img class="image-text-widget-image" src="' . $image[0] . '" width="' . $width . '" height="' . $height . '" title="' . $image_title . '" alt="' . $alt . '"' . $image_align . ' />' . ($href !== '' ? '</a>' : '');
		}
		$html .= '</div>';
		$html .= $args['after_widget'];

		echo apply_filters( 'itw_widget_html', $html, $instance );
	}

	public function form( $instance ) {
		$image_id = (int) (isset( $instance['image_id'] ) ? $instance['image_id'] : $this->itw_defaults['image_id']);
		$image = array();

		if ( $image_id !== 0 )
			$image = wp_get_attachment_image_src( $image_id, 'thumbnail', false );
		else
			$image[0] = '';
		
		if ( ! $image )
			$image = wp_get_attachment_image_src( $image_id, 'full', false );

		$html = '
		<div class="image-text-widget">
			<p class="label">' . __( 'Title', 'image-text-widget' ) . '</p>
			<input id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( isset( $instance['title'] ) ? $instance['title'] : $this->itw_defaults['title'] ) . '" />
			<p class="label">' . __( 'Image', 'image-text-widget' ) . '</p>
			<div>
				<div class="itw-image-buttons">
					<input class="itw_upload_image_id" type="hidden" name="' . $this->get_field_name( 'image_id' ) . '" value="' . $image_id . '" />
					<input class="itw_upload_image_button button button-secondary" type="button" value="' . __( 'Select image', 'image-text-widget' ) . '" />
					<input class="itw_turn_off_image_button button button-secondary" type="button" value="' . __( 'Remove image', 'image-text-widget' ) . '" ' . disabled( 0, $image_id, false ) . ' />
					<span class="itw-spinner"></span>
				</div>
				<div class="itw-image-preview">
					' . ($image[0] !== '' ? '<img src="' . $image[0] . '" alt="" />' : '<img src="" alt="" style="display: none;" />') . '
				</div>
			</div>
			<input id="' . $this->get_field_id( 'responsive' ) . '" type="checkbox" name="' . $this->get_field_name( 'responsive' ) . '" value="" ' . checked( true, (isset( $instance['responsive'] ) ? $instance['responsive'] : $this->itw_defaults['responsive'] ), false ) . ' /> <label for="' . $this->get_field_id( 'responsive' ) . '">' . __( 'Responsive', 'image-text-widget' ) . '</label>
			<p class="label">' . __( 'Text', 'image-text-widget' ) . '</p>
			<textarea id="' . $this->get_field_id( 'text' ) . '" name="' . $this->get_field_name( 'text' ) . '">' . (isset( $instance['text'] ) ? $instance['text'] : $this->itw_defaults['text']) . '</textarea>
			<p class="label">' . __( 'Text type', 'image-text-widget' ) . '</p>
			<select id="' . $this->get_field_id( 'text_type' ) . '" name="' . $this->get_field_name( 'text_type' ) . '">';

		foreach ( $this->itw_text_types as $id => $text_type ) {
			$html .= '
				<option value="' . esc_attr( $id ) . '" ' . selected( $id, (isset( $instance['text_type'] ) ? $instance['text_type'] : $this->itw_defaults['text_type'] ), false ) . '>' . $text_type . '</option>';
		}

		$html .= '
			</select>
			<p class="label">' . __( 'Link type', 'image-text-widget' ) . '</p>
			<div>
				<select class="itw-link-type" id="' . $this->get_field_id( 'link_type' ) . '" name="' . $this->get_field_name( 'link_type' ) . '">';

		$link_type = (isset( $instance['link_type'] ) ? $instance['link_type'] : $this->itw_defaults['link_type']);

		foreach ( $this->itw_link_types as $id => $type ) {
			$html .= '
					<option value="' . esc_attr( $id ) . '" ' . selected( $id, $link_type, false ) . '>' . $type . '</option>';
		}

		$html .= '
				</select>
				<div class="itw-link-pages"' . ($link_type === 'page' ? '' : ' style="display: none;"') . '>
					<p class="label">' . __( 'Link page', 'image-text-widget' ) . '</p>
					<select id="' . $this->get_field_id( 'link_page_id' ) . '" name="' . $this->get_field_name( 'link_page_id' ) . '">';

		foreach ( $this->itw_pages as $page ) {
			// multilanguage support for pages
			if ( function_exists( 'icl_object_id' ) ) {

				global $sitepress; // , $polylang

				if ( isset( $sitepress ) ) {
					// WPML
					$page_id = icl_object_id( $page->ID, 'page', false, $sitepress->get_default_language() );
				} /* elseif (isset($polylang)) {
				  // Polylang
				  $page_id = icl_object_id($page->ID, 'page', false, $polylang->pll_default_language());
				  } */ else {
					$page_id = $page->ID;
				}
			} else {
				$page_id = $page->ID;
			}
			$html .= '
						<option value="' . esc_attr( $page_id ) . '" ' . selected( $page_id, (isset( $instance['link_page_id'] ) ? $instance['link_page_id'] : $this->itw_defaults['link_page_id'] ), false ) . '>' . esc_attr( get_the_title( $page_id ) ) . '</option>';
		}

		$html .= '
					</select>
				</div>
				<div class="itw-link-custom"' . ($link_type === 'custom' ? '' : ' style="display: none;"') . '>
					<p class="label">' . __( 'Custom URL', 'image-text-widget' ) . '</p>
					<input id="' . $this->get_field_id( 'link_custom_url' ) . '" name="' . $this->get_field_name( 'link_custom_url' ) . '" type="text" value="' . esc_attr( isset( $instance['link_custom_url'] ) ? $instance['link_custom_url'] : $this->itw_defaults['link_custom_url'] ) . '" />
				</div>
			</div>
			<p class="label">' . __( 'Link target', 'image-text-widget' ) . '</p>
			<select id="' . $this->get_field_id( 'link_target' ) . '" name="' . $this->get_field_name( 'link_target' ) . '">';

		foreach ( $this->itw_link_targets as $id => $link_target ) {
			$html .= '
				<option value="' . esc_attr( $id ) . '" ' . selected( $id, (isset( $instance['link_target'] ) ? $instance['link_target'] : $this->itw_defaults['link_target'] ), false ) . '>' . $link_target . '</option>';
		}

		$html .= '
			</select>
			<p class="label">' . __( 'Size', 'image-text-widget' ) . '</p>
			<div>
				<select class="itw-size-type" id="' . $this->get_field_id( 'size' ) . '" name="' . $this->get_field_name( 'size' ) . '">';

		$size_type = (isset( $instance['size'] ) ? $instance['size'] : $this->itw_defaults['size']);

		foreach ( $this->itw_image_sizes as $size ) {
			$html .= '
					<option value="' . esc_attr( $size ) . '" ' . selected( $size, $size_type, false ) . '>' . $size . '</option>';
		}

		$html .= '
				</select>
				<div class="itw-custom-size"' . ($size_type !== 'custom' ? ' style="display: none;"' : '') . '>
					<p class="label">' . __( 'Custom width', 'image-text-widget' ) . '</p>
					<input id="' . $this->get_field_id( 'size_custom_width' ) . '" type="text" name="' . $this->get_field_name( 'size_custom_width' ) . '" value="' . (isset( $instance['size_custom_width'] ) ? $instance['size_custom_width'] : $this->itw_defaults['size_custom_width']) . '" />
					<p class="label">' . __( 'Custom height', 'image-text-widget' ) . '</p>
					<input id="' . $this->get_field_id( 'size_custom_height' ) . '" type="text" name="' . $this->get_field_name( 'size_custom_height' ) . '" value="' . (isset( $instance['size_custom_height'] ) ? $instance['size_custom_height'] : $this->itw_defaults['size_custom_height']) . '" />
				</div>
			</div>';

		$html .= '
			<p class="label">' . __( 'Image position', 'image-text-widget' ) . '</p>
			<select id="' . $this->get_field_id( 'image_position' ) . '" name="' . $this->get_field_name( 'image_position' ) . '">';

		foreach ( $this->itw_image_positions as $id => $image_position ) {
			$html .= '
				<option value="' . esc_attr( $id ) . '" ' . selected( $id, (isset( $instance['image_position'] ) ? $instance['image_position'] : $this->itw_defaults['image_position'] ), false ) . '>' . $image_position . '</option>';
		}

		$html .= '
			</select>
			<p class="label">' . __( 'Image align', 'image-text-widget' ) . '</p>
			<select id="' . $this->get_field_id( 'image_align' ) . '" name="' . $this->get_field_name( 'image_align' ) . '">';

		foreach ( $this->itw_aligns as $id => $image_align ) {
			if ( $id != 'justify' )
				$html .= '
				<option value="' . esc_attr( $id ) . '" ' . selected( $id, (isset( $instance['image_align'] ) ? $instance['image_align'] : $this->itw_defaults['image_align'] ), false ) . '>' . $image_align . '</option>';
		}

		$html .= '
			</select>
			<p class="label">' . __( 'Text align', 'image-text-widget' ) . '</p>
			<select id="' . $this->get_field_id( 'text_align' ) . '" name="' . $this->get_field_name( 'text_align' ) . '">';

		foreach ( $this->itw_aligns as $id => $text_align ) {
			$html .= '
				<option value="' . esc_attr( $id ) . '" ' . selected( $id, (isset( $instance['text_align'] ) ? $instance['text_align'] : $this->itw_defaults['text_align'] ), false ) . '>' . $text_align . '</option>';
		}

		$html .= '
			</select>
		
		</div>';

		echo $html;
	}

	public function update( $new_instance, $old_instance ) {
		// checkboxes
		$old_instance['responsive'] = (isset( $new_instance['responsive'] ) ? true : false);

		// image
		$old_instance['image_id'] = (int) (isset( $new_instance['image_id'] ) ? $new_instance['image_id'] : $this->itw_defaults['image_id']);

		// image position
		$old_instance['image_position'] = (isset( $new_instance['image_position'] ) && in_array( $new_instance['image_position'], array_keys( $this->itw_image_positions ), true ) ? $new_instance['image_position'] : $this->itw_defaults['image_position']);

		// title
		$old_instance['title'] = sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : $this->itw_defaults['title'] );

		// text
		$old_instance['text'] = esc_textarea( isset( $new_instance['text'] ) ? $new_instance['text'] : $this->itw_defaults['text'] );

		// text type
		$old_instance['text_type'] = (isset( $new_instance['text_type'] ) && in_array( $new_instance['text_type'], array_keys( $this->itw_text_types ), true ) ? $new_instance['text_type'] : $this->itw_defaults['text_type']);

		// link type
		$old_instance['link_type'] = (isset( $new_instance['link_type'] ) && in_array( $new_instance['link_type'], array_keys( $this->itw_link_types ), true ) ? $new_instance['link_type'] : $this->itw_defaults['link_type']);

		if ( $old_instance['link_type'] === 'custom' ) {
			$old_instance['link_custom_url'] = esc_url( isset( $new_instance['link_custom_url'] ) ? $new_instance['link_custom_url'] : $this->itw_defaults['link_custom_url'] );
			$old_instance['link_page_id'] = $this->itw_defaults['link_page_id'];
		} elseif ( $old_instance['link_type'] === 'page' ) {
			$old_instance['link_page_id'] = (int) (isset( $new_instance['link_page_id'] ) ? $new_instance['link_page_id'] : $this->itw_defaults['link_page_id']);
			$old_instance['link_custom_url'] = $this->itw_defaults['link_custom_url'];
		} elseif ( $old_instance['link_type'] === 'none' ) {
			$old_instance['link_page_id'] = $this->itw_defaults['link_page_id'];
			$old_instance['link_custom_url'] = $this->itw_defaults['link_custom_url'];
		}

		// link target
		$old_instance['link_target'] = (isset( $new_instance['link_target'] ) && in_array( $new_instance['link_target'], array_keys( $this->itw_link_targets ), true ) ? $new_instance['link_target'] : $this->itw_defaults['link_target']);

		// size
		$old_instance['size'] = (isset( $new_instance['size'] ) && in_array( $new_instance['size'], $this->itw_image_sizes, true ) ? $new_instance['size'] : $this->itw_defaults['size']);

		if ( $old_instance['size'] === 'custom' ) {
			$old_instance['size_custom_width'] = (int) (isset( $new_instance['size_custom_width'] ) ? $new_instance['size_custom_width'] : $this->itw_defaults['size_custom_width']);
			$old_instance['size_custom_height'] = (int) (isset( $new_instance['size_custom_height'] ) ? $new_instance['size_custom_height'] : $this->itw_defaults['size_custom_height']);
		} else {
			$old_instance['size_custom_width'] = $this->itw_defaults['size_custom_width'];
			$old_instance['size_custom_height'] = $this->itw_defaults['size_custom_height'];
		}

		// image align
		$old_instance['image_align'] = (isset( $new_instance['image_align'] ) && in_array( $new_instance['image_align'], array_keys( $this->itw_aligns ), true ) ? $new_instance['image_align'] : $this->itw_defaults['image_align']);

		// text align
		$old_instance['text_align'] = (isset( $new_instance['text_align'] ) && in_array( $new_instance['text_align'], array_keys( $this->itw_aligns ), true ) ? $new_instance['text_align'] : $this->itw_defaults['text_align']);

		return $old_instance;
	}

}
