/**
 * alg-wSelect - Conditionally loads wSelect library and initializes it.
 *
 * This script checks if the browser supports native select styling ('appearance: base').
 * If not, it dynamically loads wSelect.css and wSelect.min.js, then initializes
 * wSelect on elements with the 'alg-wselect' class.
 * Dependency URLs are provided via wp_localize_script under the 'alg_wselect_deps' object.
 *
 * @version 2.4.4
 * @since   2.4.4
 */
jQuery(document).ready(function($) {

	// Check if the browser supports 'appearance: base' for select elements
	if ( typeof CSS === 'undefined' || !CSS.supports || !CSS.supports('appearance', 'base-select') ) {

		// Check if the localized data is available
		if (typeof alg_wselect_deps === 'undefined') {
			console.error('alg-wSelect: Dependency URLs (alg_wselect_deps) not found.');
			return;
		}

		var loadCSS = function( url ) {
			var link = document.createElement('link');
			link.rel = 'stylesheet';
			link.href = url + '?ver=' + alg_wselect_deps.version;
			link.type = 'text/css';
			document.head.appendChild(link);
		};

		var loadScript = function( url, callback ) {
				var script = document.createElement('script');
				script.type = 'text/javascript';
				script.src = url + '?ver=' + alg_wselect_deps.version;
				if (callback) {
					script.onload = callback;
					script.onerror = function() {
						console.error('alg-wSelect: Failed to load script:', script.src);
					}
				}
				document.body.appendChild(script);
		};

		loadCSS( alg_wselect_deps.css_url );

		loadScript(alg_wselect_deps.lib_js_url, function() {
				$('select.alg-wselect').wSelect();
		} );

	}
});
