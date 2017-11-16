<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Template Loader
 *
 * @class       F9members_Template
 * @version     1.0.0
 * @package     F9members/Classes
 * @category    Class
 * @author      Fervidum
 */
class F9members_Template_Loader {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_filter( 'template_include',  array( __CLASS__, 'template_loader' ) );
	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. downloads looks for theme.
	 * overrides in /theme/downloads/ by default.
	 *
	 * For beginners, it also looks for a downloads.php template first. If the user adds.
	 * this to the theme (containing a downloads() inside) this will be used for all.
	 * downloads templates.
	 *
	 * @param mixed $template
	 * @return string
	 */
	public static function template_loader( $template ) {
		if ( is_embed() ) {
			return $template;
		}

		if ( $default_file = self::get_template_loader_default_file() ) {
			/**
			 * Filter hook to choose which files to find before F9members does it's own logic.
			 *
			 * @var array
			 */
			$search_files = self::get_template_loader_files( $default_file );
			$template     = locate_template( $search_files );

			if ( ! $template || MEMBERS_TEMPLATE_DEBUG_MODE ) {
				$template = F9members()->plugin_path() . '/templates/' . $default_file;
			}
		}

		return $template;
	}

	/**
	 * Get the default filename for a template.
	 *
	 * @return string
	 */
	private static function get_template_loader_default_file() {
		if ( is_page( f9members_get_page_id( 'f9members' ) ) ) {
			$default_file = 'members.php';
		} else {
			$default_file = '';
		}
		return $default_file;
	}

	/**
	 * Get an array of filenames to search for a given template.
	 *
	 * @param  string $default_file The default file name.
	 * @return string[]
	 */
	private static function get_template_loader_files( $default_file ) {
		$search_files   = apply_filters( 'f9members_template_loader_files', array(), $default_file );
		$search_files[] = 'members.php';

		$search_files[] = $default_file;
		$search_files[] = F9members()->template_path() . $default_file;

		return array_unique( $search_files );
	}
}

F9members_Template_Loader::init();
