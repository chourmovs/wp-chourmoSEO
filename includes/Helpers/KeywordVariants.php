<?php
/**
 * Keyword variant generation and matching.
 *
 * @package BreznGEO
 */

namespace BreznGEO\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates locale-aware keyword variants and provides matching utilities.
 */
class KeywordVariants {

	/**
	 * Generate keyword variants based on locale.
	 *
	 * @param string $keyword The keyword to generate variants for.
	 * @param string $locale  WordPress locale (e.g. 'en_US', 'de_DE').
	 * @return string[] Array of unique lowercase variants including the original.
	 */
	public static function generate( string $keyword, string $locale = '' ): array {
		$keyword = trim( $keyword );
		if ( '' === $keyword ) {
			return array();
		}

		if ( '' === $locale ) {
			$locale = function_exists( 'get_locale' ) ? get_locale() : 'en_US';
		}

		$lower    = mb_strtolower( $keyword );
		$variants = array( $lower );

		// Universal: compound variants (space ↔ hyphen).
		if ( mb_strpos( $lower, ' ' ) !== false ) {
			$variants[] = str_replace( ' ', '-', $lower );
		} elseif ( mb_strpos( $lower, '-' ) !== false ) {
			$variants[] = str_replace( '-', ' ', $lower );
		}

		// Universal: trailing-s.
		if ( mb_substr( $lower, -1 ) !== 's' ) {
			$variants[] = $lower . 's';
		}

		$lang = mb_strtolower( mb_substr( $locale, 0, 2 ) );

		$suffixes = self::get_suffixes( $lang );
		foreach ( $suffixes as $suffix ) {
			$variants[] = $lower . $suffix;
		}

		return array_values( array_unique( $variants ) );
	}

	/**
	 * Check if a keyword (or any of its variants) is present in text.
	 *
	 * @param string   $keyword  The keyword.
	 * @param string   $text     The text to search in.
	 * @param string[] $variants Pre-generated variants from generate().
	 * @return bool
	 */
	public static function keyword_present( string $keyword, string $text, array $variants ): bool {
		$text_lower = mb_strtolower( $text );

		foreach ( $variants as $variant ) {
			if ( mb_strpos( $text_lower, $variant ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count occurrences of a keyword (including variants) in text.
	 *
	 * @param string   $text     The text to search in.
	 * @param string[] $variants Pre-generated variants from generate().
	 * @return int Total count across all variants (no double-counting of overlapping matches).
	 */
	public static function count_occurrences( string $text, array $variants ): int {
		$text_lower = mb_strtolower( $text );
		$count      = 0;

		foreach ( $variants as $variant ) {
			$count += mb_substr_count( $text_lower, $variant );
		}

		return $count;
	}

	/**
	 * Get locale-specific suffixes.
	 *
	 * @param string $lang Two-letter language code.
	 * @return string[]
	 */
	private static function get_suffixes( string $lang ): array {
		switch ( $lang ) {
			case 'en':
				return array( 'es', 'ing', 'ed' );
			case 'de':
				return array( 'er', 'en', 'e' );
			default:
				return array();
		}
	}
}
