/**
 * Shared types for the plugin scripts.
 */

/**
 * The settings passed to the scripts by `wp_localize_script()`.
 *
 * Both values are strings, as `wp_localize_script()` casts everything it is given to a
 * string. The boolean `false` of `only_unique_error` therefore arrives as an empty string.
 */
export interface UniqueTitleCheckerSettings {
	nonce: string;
	only_unique_error: string;
}

/**
 * The response of the `unique_title_check` AJAX action.
 */
export interface UniqueTitleCheckResponse {
	message: string;
	status: 'updated' | 'error';
}

declare global {
	interface Window {
		ajaxurl: string;
		unique_title_checker: UniqueTitleCheckerSettings;
	}
}
