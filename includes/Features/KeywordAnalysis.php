<?php
/**
 * Keyword analysis checks for SEO optimization.
 *
 * @package BreznGEO
 */

namespace BreznGEO\Features;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BreznGEO\Helpers\KeywordVariants;

/**
 * Runs keyword presence and density checks against post content.
 */
class KeywordAnalysis {

	/**
	 * Extract structured content data from HTML and post context.
	 *
	 * @param string $html    Post content HTML.
	 * @param int    $post_id Post ID for meta lookups.
	 * @return array{title: string, headings: string[], paragraphs: string[], images: array, slug: string, excerpt: string, meta_description: string, word_count: int, plain_text: string}
	 */
	public static function extract_content_data( string $html, int $post_id = 0 ): array {
		$title = '';
		if ( $post_id > 0 ) {
			$title = get_the_title( $post_id );
		}

		$headings = array();
		if ( preg_match_all( '/<h[2-6][^>]*>(.*?)<\/h[2-6]>/si', $html, $matches ) ) {
			$headings = array_map( 'wp_strip_all_tags', $matches[1] );
		}

		$paragraphs = array();
		if ( preg_match_all( '/<p[^>]*>(.*?)<\/p>/si', $html, $matches ) ) {
			$paragraphs = array_map( 'wp_strip_all_tags', $matches[1] );
			$paragraphs = array_values(
				array_filter(
					$paragraphs,
					function ( $p ) {
						return '' !== trim( $p );
					}
				)
			);
		}

		$images = array();
		if ( preg_match_all( '/<img[^>]+>/si', $html, $img_matches ) ) {
			foreach ( $img_matches[0] as $img_tag ) {
				$alt       = '';
				$img_title = '';
				$caption   = '';
				if ( preg_match( '/alt=["\']([^"\']*)["\']/', $img_tag, $alt_m ) ) {
					$alt = $alt_m[1];
				}
				if ( preg_match( '/title=["\']([^"\']*)["\']/', $img_tag, $title_m ) ) {
					$img_title = $title_m[1];
				}
				$images[] = array(
					'alt'     => $alt,
					'title'   => $img_title,
					'caption' => $caption,
				);
			}
		}

		$slug = '';
		if ( $post_id > 0 ) {
			$permalink = get_permalink( $post_id );
			if ( $permalink ) {
				$path = wp_parse_url( $permalink, PHP_URL_PATH );
				$slug = $path ? trim( $path, '/' ) : '';
			}
		}

		$excerpt = '';
		if ( $post_id > 0 && function_exists( 'get_the_excerpt' ) ) {
			$excerpt = get_the_excerpt( $post_id );
		}

		$meta_description = '';
		if ( $post_id > 0 ) {
			$meta_description = get_post_meta( $post_id, '_brezngeo_meta_description', true );
		}

		$plain_text = wp_strip_all_tags( $html );
		$word_count = str_word_count( $plain_text );

		return array(
			'title'            => $title,
			'headings'         => $headings,
			'paragraphs'       => $paragraphs,
			'images'           => $images,
			'slug'             => $slug,
			'excerpt'          => $excerpt,
			'meta_description' => $meta_description,
			'word_count'       => $word_count,
			'plain_text'       => $plain_text,
		);
	}

	/**
	 * Run all keyword checks.
	 *
	 * @param string $keyword    The keyword to check.
	 * @param array  $data       Content data from extract_content_data().
	 * @param array  $thresholds Settings: target_density, min_occurrences.
	 * @param bool   $is_primary Whether this is the primary keyword.
	 * @param string $locale     Locale for variant generation.
	 * @return array[] Array of check results.
	 */
	public static function analyze( string $keyword, array $data, array $thresholds = array(), bool $is_primary = true, string $locale = '' ): array {
		$defaults   = array(
			'target_density'  => 1.5,
			'min_occurrences' => 3,
			'density_margin'  => 0.5,
		);
		$thresholds = array_merge( $defaults, $thresholds );

		if ( ! $is_primary ) {
			$thresholds['min_occurrences'] = $thresholds['min_occurrences_secondary'] ?? 1;
		}

		$variants = KeywordVariants::generate( $keyword, $locale );

		$checks = array(
			self::check_title( $keyword, $data, $variants ),
			self::check_headings( $keyword, $data, $variants ),
			self::check_density( $keyword, $data, $variants, $thresholds ),
			self::check_image_alts( $keyword, $data, $variants ),
			self::check_meta_description( $keyword, $data, $variants ),
			self::check_slug( $keyword, $data, $variants ),
			self::check_first_paragraph( $keyword, $data, $variants ),
			self::check_last_paragraph( $keyword, $data, $variants ),
			self::check_image_title_caption( $keyword, $data, $variants ),
			self::check_excerpt( $keyword, $data, $variants ),
		);

		return $checks;
	}

	/**
	 * Check 1: Keyword in title.
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_title( string $keyword, array $data, array $variants ): array {
		$found = KeywordVariants::keyword_present( $keyword, $data['title'] ?? '', $variants );
		return array(
			'id'      => 'title',
			'label'   => __( 'Title', 'brezngeo' ),
			'status'  => $found ? 'pass' : 'fail',
			'message' => $found
				? __( 'Keyword found in title.', 'brezngeo' )
				: __( 'Keyword not found in title.', 'brezngeo' ),
		);
	}

	/**
	 * Check 2: Keyword in headings (H2-H6).
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_headings( string $keyword, array $data, array $variants ): array {
		$headings = $data['headings'] ?? array();
		$found    = false;
		foreach ( $headings as $heading ) {
			if ( KeywordVariants::keyword_present( $keyword, $heading, $variants ) ) {
				$found = true;
				break;
			}
		}
		return array(
			'id'      => 'headings',
			'label'   => __( 'Headings', 'brezngeo' ),
			'status'  => $found ? 'pass' : 'fail',
			'message' => $found
				? __( 'Keyword found in subheading.', 'brezngeo' )
				: __( 'Keyword not found in any H2-H6.', 'brezngeo' ),
		);
	}

	/**
	 * Check 3: Keyword density.
	 *
	 * @param string   $keyword    The keyword.
	 * @param array    $data       Content data.
	 * @param string[] $variants   Keyword variants.
	 * @param array    $thresholds Density thresholds.
	 * @return array Check result.
	 */
	public static function check_density( string $keyword, array $data, array $variants, array $thresholds ): array {
		$plain      = $data['plain_text'] ?? '';
		$word_count = $data['word_count'] ?? 0;
		$count      = KeywordVariants::count_occurrences( $plain, $variants );

		if ( 0 === $word_count || 0 === $count ) {
			return array(
				'id'      => 'density',
				'label'   => __( 'Keyword Density', 'brezngeo' ),
				'status'  => 'fail',
				'message' => __( 'Keyword not found in content.', 'brezngeo' ),
				'details' => array(
					'count'   => $count,
					'density' => 0,
				),
			);
		}

		$density = ( $count / $word_count ) * 100;
		$target  = (float) $thresholds['target_density'];
		$margin  = (float) $thresholds['density_margin'];
		$diff    = abs( $density - $target );

		if ( $diff <= $margin ) {
			$status = 'pass';
		} elseif ( $density > 0 ) {
			$status = 'warn';
		} else {
			$status = 'fail';
		}

		return array(
			'id'      => 'density',
			'label'   => __( 'Keyword Density', 'brezngeo' ),
			'status'  => $status,
			/* translators: 1: actual density percentage, 2: target density percentage */
			'message' => sprintf( __( '%1$.1f%% (target: %2$.1f%%)', 'brezngeo' ), $density, $target ),
			'details' => array(
				'count'   => $count,
				'density' => round( $density, 2 ),
			),
		);
	}

	/**
	 * Check 4: Keyword in image alt texts.
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_image_alts( string $keyword, array $data, array $variants ): array {
		$images = $data['images'] ?? array();

		if ( empty( $images ) ) {
			return array(
				'id'      => 'image_alts',
				'label'   => __( 'Image Alt Texts', 'brezngeo' ),
				'status'  => 'warn',
				'message' => __( 'No images found.', 'brezngeo' ),
			);
		}

		foreach ( $images as $img ) {
			if ( KeywordVariants::keyword_present( $keyword, $img['alt'] ?? '', $variants ) ) {
				return array(
					'id'      => 'image_alts',
					'label'   => __( 'Image Alt Texts', 'brezngeo' ),
					'status'  => 'pass',
					'message' => __( 'Keyword found in image alt text.', 'brezngeo' ),
				);
			}
		}

		return array(
			'id'      => 'image_alts',
			'label'   => __( 'Image Alt Texts', 'brezngeo' ),
			'status'  => 'fail',
			'message' => __( 'No image contains keyword in alt text.', 'brezngeo' ),
		);
	}

	/**
	 * Check 5: Keyword in meta description.
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_meta_description( string $keyword, array $data, array $variants ): array {
		$meta = $data['meta_description'] ?? '';

		if ( '' === $meta ) {
			return array(
				'id'      => 'meta_description',
				'label'   => __( 'Meta Description', 'brezngeo' ),
				'status'  => 'warn',
				'message' => __( 'Meta description is empty.', 'brezngeo' ),
			);
		}

		$found = KeywordVariants::keyword_present( $keyword, $meta, $variants );
		return array(
			'id'      => 'meta_description',
			'label'   => __( 'Meta Description', 'brezngeo' ),
			'status'  => $found ? 'pass' : 'fail',
			'message' => $found
				? __( 'Keyword found in meta description.', 'brezngeo' )
				: __( 'Keyword not found in meta description.', 'brezngeo' ),
		);
	}

	/**
	 * Check 6: Keyword in URL/slug.
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_slug( string $keyword, array $data, array $variants ): array {
		$slug  = mb_strtolower( $data['slug'] ?? '' );
		$found = KeywordVariants::keyword_present( $keyword, $slug, $variants );

		return array(
			'id'      => 'slug',
			'label'   => __( 'URL / Slug', 'brezngeo' ),
			'status'  => $found ? 'pass' : 'fail',
			'message' => $found
				? __( 'Keyword found in URL.', 'brezngeo' )
				: __( 'Keyword not found in URL.', 'brezngeo' ),
		);
	}

	/**
	 * Check 7: Keyword in first paragraph.
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_first_paragraph( string $keyword, array $data, array $variants ): array {
		$paragraphs = $data['paragraphs'] ?? array();

		if ( empty( $paragraphs ) ) {
			return array(
				'id'      => 'first_paragraph',
				'label'   => __( 'First Paragraph', 'brezngeo' ),
				'status'  => 'fail',
				'message' => __( 'No paragraphs found.', 'brezngeo' ),
			);
		}

		$found = KeywordVariants::keyword_present( $keyword, $paragraphs[0], $variants );
		return array(
			'id'      => 'first_paragraph',
			'label'   => __( 'First Paragraph', 'brezngeo' ),
			'status'  => $found ? 'pass' : 'fail',
			'message' => $found
				? __( 'Keyword found in first paragraph.', 'brezngeo' )
				: __( 'Keyword not found in first paragraph.', 'brezngeo' ),
		);
	}

	/**
	 * Check 8: Keyword in last paragraph.
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_last_paragraph( string $keyword, array $data, array $variants ): array {
		$paragraphs = $data['paragraphs'] ?? array();

		if ( empty( $paragraphs ) ) {
			return array(
				'id'      => 'last_paragraph',
				'label'   => __( 'Last Paragraph', 'brezngeo' ),
				'status'  => 'fail',
				'message' => __( 'No paragraphs found.', 'brezngeo' ),
			);
		}

		$last  = end( $paragraphs );
		$found = KeywordVariants::keyword_present( $keyword, $last, $variants );
		return array(
			'id'      => 'last_paragraph',
			'label'   => __( 'Last Paragraph', 'brezngeo' ),
			'status'  => $found ? 'pass' : 'fail',
			'message' => $found
				? __( 'Keyword found in last paragraph.', 'brezngeo' )
				: __( 'Keyword not found in last paragraph.', 'brezngeo' ),
		);
	}

	/**
	 * Check 9: Keyword in image title or caption.
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_image_title_caption( string $keyword, array $data, array $variants ): array {
		$images = $data['images'] ?? array();

		if ( empty( $images ) ) {
			return array(
				'id'      => 'image_title_caption',
				'label'   => __( 'Image Title/Caption', 'brezngeo' ),
				'status'  => 'warn',
				'message' => __( 'No images found.', 'brezngeo' ),
			);
		}

		foreach ( $images as $img ) {
			$title_text   = $img['title'] ?? '';
			$caption_text = $img['caption'] ?? '';
			if ( KeywordVariants::keyword_present( $keyword, $title_text, $variants )
				|| KeywordVariants::keyword_present( $keyword, $caption_text, $variants ) ) {
				return array(
					'id'      => 'image_title_caption',
					'label'   => __( 'Image Title/Caption', 'brezngeo' ),
					'status'  => 'pass',
					'message' => __( 'Keyword found in image title or caption.', 'brezngeo' ),
				);
			}
		}

		return array(
			'id'      => 'image_title_caption',
			'label'   => __( 'Image Title/Caption', 'brezngeo' ),
			'status'  => 'fail',
			'message' => __( 'Keyword not found in any image title or caption.', 'brezngeo' ),
		);
	}

	/**
	 * Check 10: Keyword in excerpt.
	 *
	 * @param string   $keyword  The keyword.
	 * @param array    $data     Content data.
	 * @param string[] $variants Keyword variants.
	 * @return array Check result.
	 */
	public static function check_excerpt( string $keyword, array $data, array $variants ): array {
		$excerpt = $data['excerpt'] ?? '';

		if ( '' === trim( $excerpt ) ) {
			return array(
				'id'      => 'excerpt',
				'label'   => __( 'Excerpt', 'brezngeo' ),
				'status'  => 'warn',
				'message' => __( 'Excerpt is empty.', 'brezngeo' ),
			);
		}

		$found = KeywordVariants::keyword_present( $keyword, $excerpt, $variants );
		return array(
			'id'      => 'excerpt',
			'label'   => __( 'Excerpt', 'brezngeo' ),
			'status'  => $found ? 'pass' : 'fail',
			'message' => $found
				? __( 'Keyword found in excerpt.', 'brezngeo' )
				: __( 'Keyword not found in excerpt.', 'brezngeo' ),
		);
	}
}
