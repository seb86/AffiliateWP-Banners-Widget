<?php
/**
 * Display affiliate banner widget for Affiliate WP.
 *
 * @author 		Sebastien Dumont
 * @category 	Widgets
 * @package 	AffiliateWP Banners Widget/Widgets
 * @version 	1.0.0
 * @extends 	WP_Widget
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Widget - AffiliateWP - Affiliate Banners
 */
class AffiliateWP_Affiliate_Banners extends WP_Widget {

	public $widget_cssclass;
	public $widget_description;
	public $widget_id;
	public $widget_name;
	public $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass 		= 'affiliatewp widget_banner';
		$this->widget_description 	= sprintf( __( 'Display a banner of your choice that links to your %s referral link.', 'affiliatewp-banners-widget' ), 'AffiliateWP' );
		$this->widget_id 			= 'affiliate_wp_banner_widget';
		$this->widget_name 			= 'AffiliateWP ' . __( 'Affiliate Banner', 'affiliatewp-banners-widget' );

		$this->settings = array(
			'title' 			=> array(
				'type' 			=> 'text',
				'std' 			=> 'AffiliateWP',
				'label' 		=> __( 'Title', 'affiliatewp-banners-widget' )
			),
			'affiliate_url' 	=> array(
				'type' 			=> 'text',
				'label' 		=> __( 'Affiliate URL', 'affiliatewp-banners-widget' )
			),
			'window' 			=> array(
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Open link in a new window ?', 'affiliatewp-banners-widget' )
			),
			'size' 				=> array(
				'type' 			=> 'select',
				'options' 		=> array(
					'125x125' 					=> '125x125', 
					'234x60' 					=> '234x60', 
					'300x250' 					=> '300x250', 
					'300x250-get-started' 		=> '300x250 - ' . __( 'Get Started', 'affiliatewp-banners-widget' ) . ' - ' . __( 'Red Ad', 'affiliatewp-banners-widget' ), 
					'300x250-get-started-white' => '300x250 - ' . __( 'Get Started', 'affiliatewp-banners-widget' ) . ' - ' . __( 'White Ad', 'affiliatewp-banners-widget' ), 
					'300x600-white' 			=> '300x600', 
					'468x60' 					=> '468x60', 
					'728x90' 					=> '728x90'
				),
				'label' => __( 'Size', 'affiliatewp-banners-widget')
			),
		);

		$widget_ops = array(
			'classname'   => $this->widget_cssclass,
			'description' => $this->widget_description
		);

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

		parent::__construct(
			$this->widget_id, // Base ID
			$this->widget_name, // Name,
			$widget_ops // Args
		);
	}

	/**
	 * Return the widget slug.
	 *
	 * @return  Plugin slug variable.
	 */
	public function get_widget_slug() {
		return $this->widget_id;
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @access public
	 *
	 * @param array $args 		Widgets arguments.
	 * @param array $instance 	Saved values from database.
	 */
	public function widget( $args, $instance ) {
		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if( !is_array( $cache ) )
			$cache = array();

		if( !isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->get_widget_slug();

		if( isset( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];

		ob_start();
		extract( $args, EXTR_SKIP );

		// these are the widget options
		$title         = $instance['title'];
		$affiliate_url = $instance['affiliate_url'];
		$window        = $instance['window'];
		$size          = $instance['size'];

		echo $args['before_widget'];

		// Display Ad
		if ( $size != '' ) {
			echo '<div class="widget-text affiliatewp">';
			// if the title is set
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			// Window
			$open_in = ( $window != '' ) ? '_blank': '_parent';
			$alt_text = __( 'The best affiliate marketing plugin for WordPress', 'affiliatewp-banners-widget' );
			echo '<a href="' . $affiliate_url . '" target="' . $open_in . '"><img src="' . AFFILIATEWP_BANNERS_WIDGET_ASSETS . '' . $size . '.png" alt="' . $alt_text . '" /></a>';
			echo '</div>';
		}
		echo $args['after_widget'];

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}

	/**
	 * Cache the widget
	 */
	public function cache_widget( $args, $content ) {
		$cache[ $args['widget_id'] ] = $content;

		wp_cache_set( $this->widget_id, $cache, 'widget' );
	}

	/**
	 * Flush the cache
	 * @return [type]
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array new_instance - The new instance of values to be generated via the update.
	 * @param array old_instance - The previous instance of values before the update.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] 			= ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['affiliate_url'] 	= ( !empty( $new_instance['affiliate_url'] ) ) ? strip_tags( $new_instance['affiliate_url'] ) : 'http://affiliatewp.com/?ref=35';
		$instance['window'] 		= strip_tags( $new_instance['window'] );
		$instance['size'] 			= strip_tags( $new_instance['size'] );

		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Generates the administration form for the widget.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance
		);

		$title 			= esc_attr( $instance['title'] );
		$affiliate_url 	= esc_attr( $instance['affiliate_url'] );
		$window 		= esc_attr( $instance['window'] );
		$size 			= esc_attr( $instance['size'] );

		if( ! $this->settings )
			return;

		foreach( $this->settings as $key => $setting ) {

			$value   = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];

			switch ( $setting['type'] ) {
				case "text" :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
					</p>
					<?php
				break;
				case "number" :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="number" step="<?php echo esc_attr( $setting['step'] ); ?>" min="<?php echo esc_attr( $setting['min'] ); ?>" max="<?php echo esc_attr( $setting['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
					</p>
					<?php
				break;
				case "select" :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>">
							<?php foreach ( $setting['options'] as $option_key => $option_value ) : ?>
								<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, $value ); ?>><?php echo esc_html( $option_value ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<?php
				break;
				case "checkbox" :
					?>
					<p>
						<input id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="checkbox" value="1" <?php checked( $value, 1 ); ?> />
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
					</p>
					<?php
				break;
			}
		}
	}

}

register_widget('AffiliateWP_Affiliate_Banners');

?>