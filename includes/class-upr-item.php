<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Item
 * Represents a normalized content item (WP Post or RSS Item)
 */
class UPR_Item {

	public $id = '';
	public $title = '';
	public $url = '';
	public $image = '';
	public $excerpt = '';
	public $date = '';
	public $timestamp = 0;
	public $author = '';
	public $category = '';
	public $source_name = '';
	public $source_url = '';
	public $is_external = false;

	/**
	 * Constructor
	 *
	 * @param array $data Array of item attributes.
	 */
	public function __construct( array $data = array() ) {
		foreach ( $data as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Export as array
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'id'          => $this->id,
			'title'       => $this->title,
			'url'         => $this->url,
			'image'       => $this->image,
			'excerpt'     => $this->excerpt,
			'date'        => $this->date,
			'timestamp'   => $this->timestamp,
			'author'      => $this->author,
			'category'    => $this->category,
			'source_name' => $this->source_name,
			'source_url'  => $this->source_url,
			'is_external' => $this->is_external,
		);
	}
}
