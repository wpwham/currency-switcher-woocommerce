/**
 * alg-wSelect.
 *
 * version 2.4.4
 * since   2.4.4
 */

// Initialize wSelect only if the browser doesn't support customizable select.
if ( ! CSS.supports || ! CSS.supports( 'appearance', 'base-select' ) ) {
	jQuery('select.alg-wselect').wSelect();
}
