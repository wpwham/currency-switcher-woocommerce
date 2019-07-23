<?php
/**
 * WooCommerce Currency Switcher Widget
 *
 * The WooCommerce Currency Switcher Widget class.
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Tom Anbinder
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alg_Widget_Currency_Switcher' ) ) :

class Alg_Widget_Currency_Switcher extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$widget_ops = array(
			'classname'   => 'alg_widget_currency_switcher',
			'description' => __( 'WooCommerce Currency Switcher Widget', 'currency-switcher-woocommerce' ),
		);
		parent::__construct(
			'alg_widget_currency_switcher',
			__( 'WooCommerce Currency Switcher', 'currency-switcher-woocommerce' ),
			$widget_ops
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @param   array $args
	 * @param   array $instance
	 */
	function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		if ( 'yes' === get_option( 'alg_wc_currency_switcher_enabled', 'yes' ) ) {
			switch ( $instance['switcher_type'] ) {
				case 'link_list':
					echo alg_currency_select_link_list();
					break;
				case 'radio_list':
					echo alg_currency_select_radio_list();
					break;
				default:
					echo alg_currency_select_drop_down_list();
					break;
			}
		} else {
			echo __( 'Currency Switcher not enabled!', 'currency-switcher-woocommerce' );
		}
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @param   array $instance The widget options
	 */
	function form( $instance ) {
		$title         = ! empty( $instance['title'] )         ? $instance['title']         : '';
		$switcher_type = ! empty( $instance['switcher_type'] ) ? $instance['switcher_type'] : 'drop_down';
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'switcher_type' ); ?>"><?php _e( 'Type:' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'switcher_type' ); ?>" name="<?php echo $this->get_field_name( 'switcher_type' ); ?>">
			<option value="drop_down" <?php  selected( $switcher_type, 'drop_down' ); ?>><?php  echo __( 'Drop down', 'currency-switcher-woocommerce' ); ?>
			<option value="radio_list" <?php selected( $switcher_type, 'radio_list' ); ?>><?php echo __( 'Radio list', 'currency-switcher-woocommerce' ); ?>
			<option value="link_list" <?php  selected( $switcher_type, 'link_list' ); ?>><?php  echo __( 'Link list', 'currency-switcher-woocommerce' ); ?>
		</select>
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @param   array $new_instance The new options
	 * @param   array $old_instance The previous options
	 */
	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']         = ( ! empty( $new_instance['title'] ) )         ? strip_tags( $new_instance['title'] ) : '';
		$instance['switcher_type'] = ( ! empty( $new_instance['switcher_type'] ) ) ? $new_instance['switcher_type']       : 'drop_down';
		return $instance;
	}
}

endif;

// register Alg_Widget_Currency_Switcher widget
if ( ! function_exists( 'register_alg_widget_currency_switcher' ) ) {
	/**
	 * register_alg_widget_currency_switcher.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function register_alg_widget_currency_switcher() {
		register_widget( 'Alg_Widget_Currency_Switcher' );
	}
}
add_action( 'widgets_init', 'register_alg_widget_currency_switcher' );
