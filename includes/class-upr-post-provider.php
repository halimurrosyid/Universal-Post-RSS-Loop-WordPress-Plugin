<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Post_Provider
 * Data Provider for Native WordPress Posts
 */
class UPR_Post_Provider extends Abstract_UPR_Provider {

	/**
	 * Get normalized items from WP_Query
	 *
	 * @param array $args Parameters for querying WP posts.
	 * @return UPR_Item[]
	 */
	public function get_items( array $args = array() ) {
		$defaults = array(
			'post_type'      => 'post',
			'category'       => '',
			'author'         => '',
			'tag'            => '',
			'limit'          => 6,
			'order'          => 'DESC',
			'orderby'        => 'date',
			'offset'         => 0,
			'include'        => '',
			'exclude'        => '',
		);

		$parsed_args = wp_parse_args( $args, $defaults );

		$query_args = array(
			'post_type'           => ! empty( $parsed_args['post_type'] ) ? $parsed_args['post_type'] : 'post',
			'posts_per_page'      => intval( $parsed_args['limit'] ),
			'order'               => strtoupper( $parsed_args['order'] ) === 'ASC' ? 'ASC' : 'DESC',
			'orderby'             => sanitize_text_field( $parsed_args['orderby'] ),
			'offset'              => intval( $parsed_args['offset'] ),
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
		);

		// Category filter
		if ( ! empty( $parsed_args['category'] ) ) {
			if ( is_numeric( $parsed_args['category'] ) ) {
				$query_args['cat'] = intval( $parsed_args['category'] );
			} else {
				$query_args['category_name'] = sanitize_text_field( $parsed_args['category'] );
			}
		}

		// Tag filter
		if ( ! empty( $parsed_args['tag'] ) ) {
			$query_args['tag'] = sanitize_text_field( $parsed_args['tag'] );
		}

		// Author filter
		if ( ! empty( $parsed_args['author'] ) ) {
			if ( is_numeric( $parsed_args['author'] ) ) {
				$query_args['author'] = intval( $parsed_args['author'] );
			} else {
				$query_args['author_name'] = sanitize_text_field( $parsed_args['author'] );
			}
		}

		// Include filter
		if ( ! empty( $parsed_args['include'] ) ) {
			$inc_ids = is_array( $parsed_args['include'] ) ? $parsed_args['include'] : explode( ',', $parsed_args['include'] );
			$query_args['post__in'] = array_map( 'intval', array_filter( $inc_ids ) );
		}

		// Exclude filter
		if ( ! empty( $parsed_args['exclude'] ) ) {
			$exc_ids = is_array( $parsed_args['exclude'] ) ? $parsed_args['exclude'] : explode( ',', $parsed_args['exclude'] );
			$query_args['post__not_in'] = array_map( 'intval', array_filter( $exc_ids ) );
		}

		$query = new WP_Query( $query_args );
		$items = array();

		if ( $query->have_posts() ) {
			$site_name = get_bloginfo( 'name' );
			$site_url  = home_url();

			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				// Get featured image URL
				$image_url = get_the_post_thumbnail_url( $post_id, 'large' );
				if ( ! $image_url ) {
					$image_url = '';
				}

				// Get categories
				$cats     = get_the_category( $post_id );
				$cat_name = ! empty( $cats ) ? $cats[0]->name : '';

				// Get excerpt
				$excerpt = get_the_excerpt();
				if ( empty( $excerpt ) ) {
					$excerpt = wp_trim_words( get_the_content(), 25, '...' );
				}

				$item_data = array(
					'id'          => 'wp_' . $post_id,
					'title'       => get_the_title(),
					'url'         => get_permalink(),
					'image'       => $image_url,
					'excerpt'     => $excerpt,
					'date'        => get_the_date(),
					'timestamp'   => get_post_time( 'U', true ),
					'author'      => get_the_author(),
					'category'    => $cat_name,
					'source_name' => $site_name,
					'source_url'  => $site_url,
					'is_external' => false,
				);

				$items[] = new UPR_Item( $item_data );
			}
			wp_reset_postdata();
		}

		return $items;
	}

	/**
	 * Get array of registered public post types for dropdowns
	 *
	 * @return array Array of post types (name => label)
	 */
	public static function get_public_post_types() {
		$types = get_post_types( array( 'public' => true ), 'objects' );
		$options = array();

		foreach ( $types as $type ) {
			if ( $type->name === 'attachment' ) {
				continue;
			}
			$options[ $type->name ] = $type->label . ' (' . $type->name . ')';
		}

		return $options;
	}

	/**
	 * Get array of registered post categories for dropdowns
	 *
	 * @return array Array of categories (slug => name)
	 */
	public static function get_categories_list() {
		$categories = get_categories( array( 'hide_empty' => false ) );
		$options    = array( '' => __( '-- All Categories --', 'universal-post-rss-loop' ) );

		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$options[ $cat->slug ] = $cat->name . ' (' . $cat->slug . ')';
			}
		}

		return $options;
	}
}
