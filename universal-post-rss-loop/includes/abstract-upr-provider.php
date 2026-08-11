<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Class Abstract_UPR_Provider
 * Base data provider interface/class for WP Posts and RSS Feeds.
 */
abstract class Abstract_UPR_Provider {

	/**
	 * Fetch and normalize items into UPR_Item array
	 *
	 * @param array $args Query/Fetch parameters.
	 * @return UPR_Item[] Array of normalized items.
	 */
	abstract public function get_items( array $args = array() );
}
