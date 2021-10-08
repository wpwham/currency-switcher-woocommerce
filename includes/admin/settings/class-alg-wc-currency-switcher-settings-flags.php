<?php
/**
 * Currency Switcher - Flags Section Settings
 *
 * @version 2.15.0
 * @since   2.4.4
 * @author  Tom Anbinder
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Settings_Flags' ) ) :

class Alg_WC_Currency_Switcher_Settings_Flags extends Alg_WC_Currency_Switcher_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.4.4
	 * @since   2.4.4
	 */
	function __construct() {
		$this->id   = 'flags';
		$this->desc = __( 'Flags', 'currency-switcher-woocommerce' );
		parent::__construct();
		add_action( 'woocommerce_admin_field_alg_wselect', array( $this, 'output_alg_wselect' ) );
	}

	/**
	 * output_alg_wselect.
	 *
	 * @version 2.4.4
	 * @since   2.4.4
	 * @todo    check why it's loading so slow
	 */
	function output_alg_wselect( $value ) {
		$tooltip_html = ( isset( $value['desc_tip'] ) && '' != $value['desc_tip'] ) ? '<span class="woocommerce-help-tip" data-tip="' . $value['desc_tip'] . '"></span>' : '';
		$custom_attributes = array();
		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		$description  = ' <span class="description">' . $value['desc'] . '</span>';

		$option_value = WC_Admin_Settings::get_option( $value['id'], $value['default'] );

		?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo $tooltip_html; ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<select
					name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo implode( ' ', $custom_attributes ); ?>
					<?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
					>
					<?php
						foreach ( $value['options'] as $key => $val ) {
							?>
							<option value="<?php echo esc_attr( $key ); ?>" data-icon="<?php echo alg_get_country_flag_image_url( $key ); ?>" <?php

								if ( is_array( $option_value ) ) {
									selected( in_array( $key, $option_value ), true );
								} else {
									selected( $option_value, $key );
								}

							?>><?php echo $val ?></option>
							<?php
						}
					?>
				</select> <?php echo $description; ?>
			</td>
		</tr><?php
	}

	/**
	 * get_flags_settings.
	 *
	 * @version 2.15.0
	 * @since   2.4.4
	 */
	public static function get_flags_settings( $settings ) {
		$all_currencies = get_woocommerce_currencies();
		$settings = array_merge( $settings, array(
			array(
				'title'     => __( 'Country Flags & Icons', 'currency-switcher-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_wc_currency_switcher_flags_options',
			),
			array(
				'title'     => __( 'Flags', 'currency-switcher-woocommerce' ),
				'type'      => 'checkbox',
				'desc'      => '<strong>' . __( 'Enable section', 'currency-switcher-woocommerce' ) . '</strong>',
				'id'        => 'alg_wc_currency_switcher_flags_enabled',
				'default'   => 'no',
				'desc_tip'  => apply_filters( 'alg_wc_currency_switcher_plugin_option', sprintf(
					__( 'To enable flags, you will need %s plugin.', 'currency-switcher-woocommerce' ),
					'<a target="_blank" href="' . esc_url( 'https://wpwham.com/products/currency-switcher-for-woocommerce/?utm_source=settings_flags&utm_campaign=free&utm_medium=currency_switcher' ) . '">' .
						__( 'Currency Switcher for WooCommerce Pro', 'currency-switcher-woocommerce' ) . '</a>' ), 'settings' ),
				'custom_attributes' => apply_filters( 'alg_wc_currency_switcher_plugin_option', array( 'disabled' => 'disabled' ), 'settings' ),
			),
		) );
		$currency_countries = alg_get_currency_countries();
		$crypto_icons       = alg_get_crypto_icons();
//		$show_flags_in_settings = ( 'yes' === get_option( 'alg_currency_switcher_show_flags_in_admin_settings_enabled', 'no' ) );
		$show_flags_in_settings = false;
		foreach ( alg_get_enabled_currencies( true ) as $i => $currency ) {
			if ( '' != $currency ) {
				if ( false === ( $country_code = ( isset( $currency_countries[ $currency ][0] ) ? $currency_countries[ $currency ][0] : false ) ) ) {
					$country_code = ( isset( $crypto_icons[ $currency ] ) ? $crypto_icons[ $currency ] : 'no-flag' );
				}
				$settings = array_merge( $settings, array(
					array(
						'title'    => '#' . ( $i ) . ' [' . $currency . '] ' . $all_currencies[ $currency ],
						'id'       => 'alg_wc_currency_switcher_flags_' . $currency,
						'default'  => $country_code,
						'type'     => ( $show_flags_in_settings ? 'alg_wselect' : 'select' ),
						'options'  => array_merge(
							array( 'EU'      => __( 'European Union', 'currency-switcher-woocommerce' ) ),
							alg_get_countries(),
							array( 'global'  => __( 'World', 'currency-switcher-woocommerce' ) ),
							alg_get_crypto(),
							array( 'no-flag' => __( 'N/A', 'currency-switcher-woocommerce' ) )
						),
						'class'    => ( $show_flags_in_settings ? 'alg-wselect' : 'wc-enhanced-select' ),
						'css'      => ( $show_flags_in_settings ? 'display:none;' : '' ),
					),
				) );
			}
		}
		$settings = array_merge( $settings, array(
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_currency_switcher_flags_options',
			),
		) );
		return $settings;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Settings_Flags();
