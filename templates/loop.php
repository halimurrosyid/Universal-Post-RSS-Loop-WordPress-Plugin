<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loop Wrapper Template (v2.0.0)
 *
 * Available variables:
 * @var UPR_Item[] $items Array of normalized items.
 * @var array      $settings Render settings.
 */

$layout       = ! empty( $settings['layout'] ) ? esc_attr( $settings['layout'] ) : 'grid';
$columns      = ! empty( $settings['columns'] ) ? intval( $settings['columns'] ) : 3;
$columns      = min( max( $columns, 1 ), 6 );
$custom_html  = ! empty( $settings['custom_html'] ) ? $settings['custom_html'] : '';

$show_search  = ! empty( $settings['show_search_bar'] );
$show_filter  = ! empty( $settings['show_filter_tabs'] );
$paged_type   = ! empty( $settings['pagination_type'] ) ? $settings['pagination_type'] : 'none';
$per_page     = ! empty( $settings['items_per_page'] ) ? intval( $settings['items_per_page'] ) : 6;

$wrapper_classes = array(
	'upr-loop',
	'upr-layout-' . $layout,
	'upr-cols-' . $columns,
);

if ( ! empty( $settings['image_position'] ) && $layout !== 'custom' ) {
	$wrapper_classes[] = 'upr-img-pos-' . esc_attr( $settings['image_position'] );
}

// Collect unique categories for filter tabs
$categories_list = array();
if ( $show_filter && ! empty( $items ) ) {
	foreach ( $items as $it ) {
		if ( ! empty( $it->category ) && ! in_array( $it->category, $categories_list, true ) ) {
			$categories_list[] = trim( $it->category );
		}
	}
}
?>

<div class="upr-container" data-paged-type="<?php echo esc_attr( $paged_type ); ?>" data-per-page="<?php echo esc_attr( $per_page ); ?>">
	
	<?php if ( $show_search || $show_filter ) : ?>
		<div class="upr-top-controls">
			<?php if ( $show_search ) : ?>
				<div class="upr-live-search-wrap">
					<span class="upr-search-icon">🔍</span>
					<input type="text" class="upr-live-search-input" placeholder="<?php esc_attr_e( 'Search articles...', 'universal-post-rss-loop' ); ?>" />
				</div>
			<?php endif; ?>

			<?php if ( $show_filter && ! empty( $categories_list ) ) : ?>
				<div class="upr-filter-tabs">
					<button type="button" class="upr-filter-btn active" data-filter="all"><?php esc_html_e( 'All', 'universal-post-rss-loop' ); ?></button>
					<?php foreach ( $categories_list as $cat_name ) : ?>
						<button type="button" class="upr-filter-btn" data-filter="<?php echo esc_attr( sanitize_title( $cat_name ) ); ?>"><?php echo esc_html( $cat_name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
		<?php
		$index = 0;
		foreach ( $items as $item ) {
			$index++;
			$cat_slug = ! empty( $item->category ) ? sanitize_title( $item->category ) : '';
			$hidden_class = ( $paged_type !== 'none' && $index > $per_page ) ? ' upr-page-hidden' : '';

			echo '<div class="upr-item-wrapper' . esc_attr( $hidden_class ) . '" data-category="' . esc_attr( $cat_slug ) . '" data-search="' . esc_attr( mb_strtolower( $item->title . ' ' . $item->excerpt, 'UTF-8' ) ) . '">';
			if ( $layout === 'custom' && ! empty( $custom_html ) ) {
				echo UPR_Template::render_custom_html( $custom_html, $item, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo UPR_Template::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'item.php',
					array(
						'item'     => $item,
						'settings' => $settings,
					)
				);
			}
			echo '</div>';
		}
		?>
	</div>

	<?php if ( $paged_type === 'load_more' && count( $items ) > $per_page ) : ?>
		<div class="upr-pagination-wrap">
			<button type="button" class="upr-load-more-btn button"><?php esc_html_e( 'Load More Articles', 'universal-post-rss-loop' ); ?></button>
		</div>
	<?php elseif ( $paged_type === 'numeric' && count( $items ) > $per_page ) : ?>
		<div class="upr-numeric-pagination-wrap">
			<?php
			$total_pages = ceil( count( $items ) / $per_page );
			for ( $i = 1; $i <= $total_pages; $i++ ) {
				$active = $i === 1 ? ' active' : '';
				echo '<button type="button" class="upr-page-num' . esc_attr( $active ) . '" data-page="' . esc_attr( $i ) . '">' . esc_html( $i ) . '</button>';
			}
			?>
		</div>
	<?php endif; ?>

</div>
