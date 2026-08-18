<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Card Item Template (v2.0.7)
 *
 * Available variables:
 * @var UPR_Item $item Normalized item object.
 * @var array    $settings Display settings.
 */

$link_target = '';
if ( ! empty( $settings['open_new_tab'] ) || $item->is_external ) {
	$link_target = ' target="_blank" rel="noopener noreferrer"';
}

$link_behavior = ! empty( $settings['link_behavior'] ) ? $settings['link_behavior'] : 'card';
$is_card_link  = ( $link_behavior === 'card' );

$show_image       = ! empty( $settings['show_image'] ) && ! empty( $item->image );
$show_title       = ! empty( $settings['show_title'] ) && ! empty( $item->title );
$show_excerpt     = ! empty( $settings['show_excerpt'] ) && ! empty( $item->excerpt );
$show_read_time   = ! empty( $settings['show_read_time'] );
$show_social      = ! empty( $settings['show_social_share'] );

$show_meta     = ( ! empty( $settings['show_date'] ) && ! empty( $item->date ) ) ||
                 ( ! empty( $settings['show_author'] ) && ! empty( $item->author ) ) ||
                 ( ! empty( $settings['show_category'] ) && ! empty( $item->category ) ) ||
                 ( ! empty( $settings['show_source'] ) && ! empty( $item->source_name ) ) ||
                 $show_read_time;

$show_btn      = ! empty( $settings['show_read_more'] );

$title_tag          = ! empty( $settings['title_tag'] ) ? tag_escape( $settings['title_tag'] ) : 'h3';
$image_ratio        = ! empty( $settings['image_ratio'] ) ? esc_attr( $settings['image_ratio'] ) : '16:9';
$object_fit         = ! empty( $settings['object_fit'] ) ? esc_attr( $settings['object_fit'] ) : 'cover';

$card_style         = ! empty( $settings['card_style'] ) ? esc_attr( $settings['card_style'] ) : 'classic';
$button_style       = ! empty( $settings['button_style'] ) ? esc_attr( $settings['button_style'] ) : 'solid';
$button_width       = ! empty( $settings['button_width'] ) ? esc_attr( $settings['button_width'] ) : 'auto';
$border_radius      = ! empty( $settings['border_radius'] ) ? esc_attr( $settings['border_radius'] ) : 'medium';
$box_shadow         = ! empty( $settings['box_shadow'] ) ? esc_attr( $settings['box_shadow'] ) : 'soft';
$card_padding       = ! empty( $settings['card_padding'] ) ? esc_attr( $settings['card_padding'] ) : 'normal';
$title_font_size    = ! empty( $settings['title_font_size'] ) ? esc_attr( $settings['title_font_size'] ) : 'medium';
$excerpt_font_size  = ! empty( $settings['excerpt_font_size'] ) ? esc_attr( $settings['excerpt_font_size'] ) : 'medium';
$image_hover_effect = ! empty( $settings['image_hover_effect'] ) ? esc_attr( $settings['image_hover_effect'] ) : 'zoom';
$badge_position     = ! empty( $settings['badge_position'] ) ? esc_attr( $settings['badge_position'] ) : 'auto';

$ratio_class   = 'upr-ratio-' . str_replace( ':', '-', $image_ratio );

// Estimated Read Time Calculation (Avg 200 words/min)
$read_time_text = '';
if ( $show_read_time ) {
	$word_count = str_word_count( wp_strip_all_tags( $item->title . ' ' . $item->excerpt ) );
	$minutes    = max( 1, ceil( $word_count / 200 ) );
	$read_time_text = sprintf( __( '⏱️ %d min read', 'universal-post-rss-loop' ), $minutes );
}

// Font Family & Typography Controls
$font_family_preset = ! empty( $settings['font_family'] ) ? $settings['font_family'] : 'inherit';
$custom_font_family = ! empty( $settings['custom_font_family'] ) ? trim( $settings['custom_font_family'] ) : '';

$font_family_css = '';
if ( $font_family_preset === 'inter' ) {
	$font_family_css = "'Inter', system-ui, -apple-system, sans-serif";
} elseif ( $font_family_preset === 'roboto' ) {
	$font_family_css = "'Roboto', system-ui, -apple-system, sans-serif";
} elseif ( $font_family_preset === 'poppins' ) {
	$font_family_css = "'Poppins', system-ui, -apple-system, sans-serif";
} elseif ( $font_family_preset === 'playfair' ) {
	$font_family_css = "'Playfair Display', Georgia, serif";
} elseif ( $font_family_preset === 'monospace' ) {
	$font_family_css = "'Fira Code', Consolas, monospace";
} elseif ( $font_family_preset === 'custom' && ! empty( $custom_font_family ) ) {
	$font_family_css = $custom_font_family;
}

// Dynamic CSS Custom Properties & Styles
$inline_styles = array();
if ( ! empty( $font_family_css ) )                { $inline_styles[] = 'font-family:' . esc_attr( $font_family_css ); }
if ( ! empty( $settings['card_bg'] ) )           { $inline_styles[] = '--upr-card-bg:' . esc_attr( $settings['card_bg'] ); }
if ( ! empty( $settings['border_color'] ) )       { $inline_styles[] = '--upr-border-color:' . esc_attr( $settings['border_color'] ); }
if ( ! empty( $settings['title_color'] ) )        { $inline_styles[] = '--upr-title-color:' . esc_attr( $settings['title_color'] ); }
if ( ! empty( $settings['title_hover_color'] ) )  { $inline_styles[] = '--upr-title-hover-color:' . esc_attr( $settings['title_hover_color'] ); }
if ( ! empty( $settings['excerpt_color'] ) )      { $inline_styles[] = '--upr-excerpt-color:' . esc_attr( $settings['excerpt_color'] ); }
if ( ! empty( $settings['meta_color'] ) )         { $inline_styles[] = '--upr-meta-color:' . esc_attr( $settings['meta_color'] ); }
if ( ! empty( $settings['badge_bg'] ) )           { $inline_styles[] = '--upr-badge-bg:' . esc_attr( $settings['badge_bg'] ); }
if ( ! empty( $settings['badge_color'] ) )        { $inline_styles[] = '--upr-badge-color:' . esc_attr( $settings['badge_color'] ); }
if ( ! empty( $settings['button_bg'] ) )          { $inline_styles[] = '--upr-btn-bg:' . esc_attr( $settings['button_bg'] ); }
if ( ! empty( $settings['button_color'] ) )       { $inline_styles[] = '--upr-btn-color:' . esc_attr( $settings['button_color'] ); }
if ( ! empty( $settings['button_hover_bg'] ) )    { $inline_styles[] = '--upr-btn-hover-bg:' . esc_attr( $settings['button_hover_bg'] ); }

$style_attr = ! empty( $inline_styles ) ? ' style="' . esc_attr( implode( ';', $inline_styles ) ) . '"' : '';

// Custom Font Sizes (Overrides preset if provided)
$custom_title_size   = ! empty( $settings['custom_title_font_size'] ) ? esc_attr( trim( $settings['custom_title_font_size'] ) ) : '';
$custom_excerpt_size = ! empty( $settings['custom_excerpt_font_size'] ) ? esc_attr( trim( $settings['custom_excerpt_font_size'] ) ) : '';

$title_style_attr   = ! empty( $custom_title_size ) ? ' style="font-size:' . $custom_title_size . ';"' : '';
$excerpt_style_attr = ! empty( $custom_excerpt_size ) ? ' style="font-size:' . $custom_excerpt_size . ';"' : '';

// Truncation logic
$title_text = $item->title;
if ( ! empty( $settings['max_title_chars'] ) && intval( $settings['max_title_chars'] ) > 0 ) {
	$max_c = intval( $settings['max_title_chars'] );
	if ( mb_strlen( $title_text ) > $max_c ) {
		$title_text = mb_substr( $title_text, 0, $max_c ) . '...';
	}
}

$excerpt_text = $item->excerpt;
if ( ! empty( $settings['max_excerpt_chars'] ) && intval( $settings['max_excerpt_chars'] ) > 0 ) {
	$max_c = intval( $settings['max_excerpt_chars'] );
	if ( mb_strlen( $excerpt_text ) > $max_c ) {
		$excerpt_text = mb_substr( $excerpt_text, 0, $max_c ) . '...';
	}
}

// Badge Overlay Decision
$show_badge_overlay = ( $badge_position === 'overlay' ) || ( $badge_position === 'auto' && $card_style === 'modern' );

$item_classes = array(
	'upr-item',
	'upr-style-' . $card_style,
	'upr-radius-' . $border_radius,
	'upr-shadow-' . $box_shadow,
	'upr-padding-' . $card_padding,
	'upr-img-hover-' . $image_hover_effect,
);
if ( $is_card_link ) {
	$item_classes[] = 'upr-card-clickable';
}

$encoded_url   = rawurlencode( $item->url );
$encoded_title = rawurlencode( $item->title );
?>

<article class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>"<?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $is_card_link ? 'onclick="window.open(\'' . esc_url( $item->url ) . '\', \'' . ( ! empty( $link_target ) ? '_blank' : '_self' ) . '\')"' : ''; ?>>
	
	<?php if ( $show_image ) : ?>
		<div class="upr-item-image <?php echo esc_attr( $ratio_class ); ?>">
			<?php if ( in_array( $link_behavior, array( 'image', 'all' ), true ) ) : ?>
				<a href="<?php echo esc_url( $item->url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( $item->title ); ?>">
					<img src="<?php echo esc_url( $item->image ); ?>" alt="<?php echo esc_attr( $item->title ); ?>" style="object-fit: <?php echo esc_attr( $object_fit ); ?>;" loading="lazy" />
				</a>
			<?php else : ?>
				<img src="<?php echo esc_url( $item->image ); ?>" alt="<?php echo esc_attr( $item->title ); ?>" style="object-fit: <?php echo esc_attr( $object_fit ); ?>;" loading="lazy" />
			<?php endif; ?>

			<?php if ( $show_badge_overlay && $show_meta ) : ?>
				<div class="upr-image-badge-overlay">
					<?php if ( ! empty( $settings['show_source'] ) && ! empty( $item->source_name ) ) : ?>
						<span class="upr-meta-source"><?php echo esc_html( $item->source_name ); ?></span>
					<?php elseif ( ! empty( $settings['show_category'] ) && ! empty( $item->category ) ) : ?>
						<span class="upr-meta-category"><?php echo esc_html( $item->category ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="upr-item-content">
		
		<?php if ( $show_meta ) : ?>
			<div class="upr-item-meta">
				<?php if ( ( ! $show_badge_overlay || ! $show_image ) && ! empty( $settings['show_source'] ) && ! empty( $item->source_name ) ) : ?>
					<span class="upr-meta-source">
						<?php if ( ! empty( $item->source_url ) ) : ?>
							<a href="<?php echo esc_url( $item->source_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $item->source_name ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $item->source_name ); ?>
						<?php endif; ?>
					</span>
				<?php endif; ?>

				<?php if ( ! empty( $settings['show_category'] ) && ! empty( $item->category ) ) : ?>
					<span class="upr-meta-category"><?php echo esc_html( $item->category ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $settings['show_date'] ) && ! empty( $item->date ) ) : ?>
					<span class="upr-meta-date"><?php echo esc_html( $item->date ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $settings['show_author'] ) && ! empty( $item->author ) ) : ?>
					<span class="upr-meta-author"><?php echo esc_html( $item->author ); ?></span>
				<?php endif; ?>

				<?php if ( $show_read_time && ! empty( $read_time_text ) ) : ?>
					<span class="upr-meta-read-time"><?php echo esc_html( $read_time_text ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_title ) : ?>
			<<?php echo esc_html( $title_tag ); ?> class="upr-item-title upr-title-font-<?php echo esc_attr( $title_font_size ); ?>"<?php echo $title_style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php if ( in_array( $link_behavior, array( 'title', 'all', 'card' ), true ) ) : ?>
					<a href="<?php echo esc_url( $item->url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $title_text ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $title_text ); ?>
				<?php endif; ?>
			</<?php echo esc_html( $title_tag ); ?>>
		<?php endif; ?>

		<?php if ( $show_excerpt ) : ?>
			<div class="upr-item-excerpt upr-excerpt-font-<?php echo esc_attr( $excerpt_font_size ); ?>"<?php echo $excerpt_style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<p><?php echo esc_html( $excerpt_text ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $show_btn || $show_social ) : ?>
			<div class="upr-item-footer">
				<?php if ( $show_btn ) : ?>
					<div class="upr-item-action">
						<a href="<?php echo esc_url( $item->url ); ?>" class="upr-read-more upr-btn-<?php echo esc_attr( $button_style ); ?> upr-btn-width-<?php echo esc_attr( $button_width ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php echo esc_html( ! empty( $settings['read_more_text'] ) ? $settings['read_more_text'] : __( 'Read More', 'universal-post-rss-loop' ) ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( $show_social ) : ?>
					<div class="upr-social-share-wrap">
						<a href="https://api.whatsapp.com/send?text=<?php echo esc_attr( $encoded_title ); ?>%20<?php echo esc_attr( $encoded_url ); ?>" target="_blank" rel="noopener" class="upr-social-btn upr-share-wa" title="Share on WhatsApp">💬</a>
						<a href="https://twitter.com/intent/tweet?text=<?php echo esc_attr( $encoded_title ); ?>&url=<?php echo esc_attr( $encoded_url ); ?>" target="_blank" rel="noopener" class="upr-social-btn upr-share-tw" title="Share on X">🐦</a>
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $encoded_url ); ?>" target="_blank" rel="noopener" class="upr-social-btn upr-share-fb" title="Share on Facebook">📘</a>
						<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $encoded_url ); ?>" target="_blank" rel="noopener" class="upr-social-btn upr-share-li" title="Share on LinkedIn">💼</a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>
</article>
