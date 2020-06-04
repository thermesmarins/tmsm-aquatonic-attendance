<?php

/**
 * Provides the markup for a select field
 *
 * @link       http://slushman.com
 * @since      1.0.0
 *
 * @package    Now_Hiring
 * @subpackage Now_Hiring/admin/partials
 */

if ( ! empty( $atts['label'] ) ) {

	?><label for="<?php echo esc_attr( $atts['id'] ); ?>"><?php esc_html_e( $atts['label'], 'employees' ); ?>: </label><?php

}

?><select
	aria-label="<?php esc_attr( _e( $atts['aria'], 'tmsm-aquatonic-attendance' ) ); ?>"
	class="<?php echo esc_attr( $atts['class'] ); ?>"
	id="<?php echo esc_attr( $atts['id'] ); ?>"
	name="<?php echo esc_attr( $atts['name'] ); ?>"><?php

if ( ! empty( $atts['blank'] ) ) {

	?><option value><?php esc_html_e( $atts['blank'], 'tmsm-aquatonic-attendance' ); ?></option><?php

}

foreach ( $atts['selections'] as $selection ) {

	if ( is_array( $selection ) ) {

		$label = $selection['label'];
		$value = $selection['value'];

	} else {

		$label = strtolower( $selection );
		$value = strtolower( $selection );

	}

	?><option
		value="<?php echo esc_attr( $value ); ?>" <?php
		selected( $atts['value'], $value ); ?>><?php

		esc_html_e( $label, 'tmsm-aquatonic-attendance' );

	?></option><?php

} // foreach

?></select>
<span class="description"><?php esc_html_e( $atts['description'], 'tmsm-aquatonic-attendance' ); ?></span>
</label>
