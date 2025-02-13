<?php
/*===========================================================================================*
 * Plugin Name: AlterLang
 * Description: Helps dealing with several translated version of one page by allowing you to
 * select equivalent pages in different languages.
 * Version: 2.0.3
 * Author: JVCMS
 * Author URI: https://jvcms.fr
 *
 * SOME VOCABULARY :
 * A "language template" page => refers to page that as been choosen to act as if it was a 
 * template or a category for other sub-pages in a specific language. This kind of page MUST
 * be at the first level of the hierarchy.
 *
 * A "main language template" page => refers to the "language template" page that has been
 * choosen to represent the native language used by default on the entire website. ONLY the
 * children page of this "main language template" page will be able to select their
 * translations amongst pages belonging to other "language template" pages.
 *
 * A alternate / alternative page => refers to a page containing the translated version of
 * another page. 
 *===========================================================================================*/
 
$thisfile = basename( __FILE__ , ".php" ) ;

i18n_merge('alterlang') || i18n_merge('alterlang', 'en_US');

register_plugin
(
	$thisfile , 
	'AlterLang' , 	
	'2.0.4' , 	
	'JVCMS' , 
	'https://jvcms.fr' ,
	'Real Multilingual for Getsimple. Uniques URLs. Better SEO. Helps dealing with several translated version of one page by allowing you to select equivalent pages in different languages.' ,
	'pages' , 
	'alterlang_main'
) ;


/* INITIALIZING IMPORTANT PLUGIN's CONSTANTS */
define ( 'plugin_name' ,  $thisfile ) ;
define ( 'templates_path' , GSPLUGINPATH . plugin_name . '/templates/' ) ;
define ( 'datas_storage_dir_name' , GSDATAOTHERPATH . plugin_name . '/' ) ;
define ( 'json_extension' , '.json' ) ; 
define ( 'settings_file' , datas_storage_dir_name . 'settings' . json_extension ) ; 
define ( 'backup_settings_file' , datas_storage_dir_name . 'settings.backup' . json_extension ) ; 
define ( 'markup_name_prefix' , plugin_name . '-' ) ;
define ( 'markup_name_suffix' , '-choices' ) ;
define ( 'option_name_const_suffix' , '_option_name' ) ;
define ( 'option_name_prefix' , plugin_name . '_' ) ;

// XML namespaces
define( 'xmlns_xhtml' , 'http://www.w3.org/1999/xhtml' ) ;
define( 'xhtml_xsd' , 'http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd' ) ;
define( 'xmlns' , 'http://www.sitemaps.org/schemas/sitemap/0.9' ) ;
define( 'xmlns_xsd' , 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd' ) ;
define( 'xsi' , 'http://www.w3.org/2001/XMLSchema-instance' ) ;

// Standard options, add more if needed.
define( 'main_lang' , 'main_lang' ) ;
define( 'template_lang' , 'template_lang' ) ;
define( 'main_lang_children' , 'main_lang_children' ) ;
define( 'alternative_pages' , 'alternative_pages' ) ;
define( 'revert_config' , 'revert_config' ) ;

/* INITIALIZING IMPORTANT PLUGIN's GLOBAL VARIALBES */
global $settings ;
global $options_events ;
global $locales ;
global $locked ;
global $is_reverted ;

$is_reverted = false ;
$locked = true ;
$locales = include_once( GSPLUGINPATH . plugin_name . '/inc/locales.php') ;

// If needed, add more active options here.
$settings = array
				( 
					main_lang => null , 
					main_lang_children => array() ,
					template_lang => array() , 
					alternative_pages => array()
				) ;

  
/* REGISTERING & QUEUING SCRIPTS / STYLES */
register_style( 'alterlang-options-fields-style' , $SITEURL . 'plugins/' . plugin_name . '/css/options-fields.css' , '2.0' , 'screen' ) ;
register_script( 'alterlang-options-fields-script' , $SITEURL . 'plugins/' . plugin_name . '/js/options-fields.js' , '2.0' , FALSE ) ; 
 
// Backoffice only
queue_style( 'alterlang-options-fields-style'  , GSBACK ) ;
queue_script( 'alterlang-options-fields-script' , GSBACK ) ; 
 
 
/* REGISTERING HOOKS & FILTERS */
add_action( 'pages-sidebar', 'createSideMenu', array( $thisfile , i18n_r('alterlang/lang_Menu_Title') ) );
add_action( 'edit-extras' , 'print_alternative_pages' ) ;
add_action( 'changedata-save' , 'process_alternative_pages' ) ; 
add_action( 'pagecache-aftersave' , 'update_main_lang_children_list' ) ; 
add_action( 'changedata-updateslug' , 'propagate_page_slug_change' ) ;
add_action( 'page-delete' , 'page_is_deleted' ) ;

add_filter( 'sitemap' , 'alterlang_update_sitemap' ) ;


/* PLUGIN'S FUNCTIONS */
include_once( GSPLUGINPATH . plugin_name . '/utilities.php' ) ; 
include_once( GSPLUGINPATH . plugin_name . '/events.php' ) ;

/* Initializes the plugin with correct values, displays the settings options form. */
function alterlang_main()
{
	on_init() ;
	init_main_language() ;
	
	process_form() ;

	// Includes the HTML form containing settings options for the plugin.
	include templates_path . 'settings.php' ;	
}


/* Initializes the website's main language. */
function init_main_language()
{
	$main_lang = get_option( main_lang ) ;

	if( empty( $main_lang ) )
	{
		// if none was already set ( e.g. plugin first install ), we isolates the lowercase 
		// part from the xx_XX lang format, for example the 'fr' part of 'fr_FR'.
		$main_lang = current( explode( '_' , (string) get_site_lang() ) ) ;
	
		set_option( main_lang , $main_lang ) ;
	}
}


/* Detects if a page name or slug matches the current main language in settings. */
function is_like_lang( $page_ids , $lang )
{ 
	global $settings ;
	$page_ids = (array) $page_ids ;
	
	foreach( $page_ids as $page_id )
	{
		if( $lang === $page_id )
		{
			echo 'selected' ;
			return true ;
		}
	}
	
	return false ;
}

/* Detects if a page name or slug matches the current main language in settings. */
function get_lang( $lang )
{ 
	global $settings ;
	
	switch( $lang )
	{
		case main_lang:
			$lang = ( is_array( $settings[ $lang ] ) ) ?  key( $settings[ $lang ] ) : $settings[ $lang ] ;
			break;
			
		case template_lang:
			$lang = array_keys( $settings[ $lang ] ) ;
			break;
	}
	
	return $lang ;
}


/* Checks if a given page as the "main language template" page as parent. */
function main_lang_is_parent_page_for( $page_name )
{
	global $settings ;

	return in_array( $page_name , $settings[ main_lang_children ] ) ; 
}


/* Checks if the given page belongs to the main language page. */
function is_main_lang_page_children( $page_id )
{
	$alternative_pages = get_option( main_lang_children ) ;
	
	return in_array( $page_id , $alternative_pages ) ;
}


/* Returns the list all pages whose parent is the main lang page and the main lang page itself */
function get_main_lang_pages_list()
{
	global $settings ;
	
	$main_lang = get_lang( main_lang ) ;
	$template_langs = $settings[ main_lang_children ] ;
	array_push( $template_langs , $main_lang ) ;
	
	return implode( ',' , $template_langs ) ; 
}


/* Checks if the given page is part of language template pages. */
function is_template_lang_page( $page_id )
{
	$template_langs =  array_keys( get_option( template_lang ) ) ;
	
	return in_array( $page_id , $template_langs ) ;
}


/* Checks if the given page is part of alternative pages. */
function is_alternative_page( $page_id )
{
	$alternative_pages = get_option( alternative_pages ) ;
	
	foreach( $alternative_pages as $slug => $alternative_page )
	{
		if( array_search( $page_id , $alternative_page ) )
			return true ;
	}
	
	return false  ;
}


/* Returns a list of potential "main language template" pages ( based on if the have a parent 
 * page or not , since "language template" pages MUST be at the root level of the pages
 * hierarchy and thus, cannot have a parent page themselves ). */
function get_potential_main_language_pages()
{
	global $settings ;
	global $pagesArray ;
	
	$potential_pages = array() ;

	foreach( $pagesArray as $page_slug => $datas )
	{
		if( empty( $datas[ 'parent' ] ) )
		{
			$potential_pages[ $page_slug ] = $datas[ 'title' ] ;
		}
	}
	
	return $potential_pages ;
}


/* Returns a list of potential "language template" page. */
function get_potential_template_language_pages( $page_name = null )
{
	global $settings ;
	static $excluded_pages ;
	
	$excluded_pages = (array) $excluded_pages ;
	
	if( null !== $page_name )
	{
		array_push( $excluded_pages , $page_name ) ;
	}
	
	return array_diff_key( get_potential_main_language_pages() , $settings[ main_lang ] , $excluded_pages ) ;
}


/* Gets a list of available "language template" pages. */
function get_registered_template_language_pages()
{
	return has_at_least_one_child_page( array_keys( get_option( template_lang ) ) ) ; 
}


/* Checks if a page has alternative page for a given language. */
function has_alternative_page( $page_name , $language )
{
	global $settings ;
	$alternative_pages = get_option( alternative_pages ) ;
	
	return ( 
				isset( $alternative_pages[ $page_name ] ) 
				
					AND 
					
				isset( $alternative_pages[ $page_name ][ $language ] ) 
			) 
			
			?
				$alternative_pages[ $page_name ][ $language ]
			:
				false
			; 
}


/* Returns dropdown list of pages and Mark option elements as "selected" if necessary. */
function get_pages_dropdown_list( $page_name , $language )
{
	global $settings ;
	$alternative_pages = get_option( alternative_pages ) ;
	$dropdown = get_pages_menu_dropdown( $language , '' , 0 ) ;
		
	return ( false !== ( $alternative_page = has_alternative_page( $page_name , $language ) ) ) ?  
			
				preg_replace( '#(value="' . $alternative_page . '")#' , ' selected $1' , $dropdown )
						 
			:
				$dropdown
			;
}


/* Responsible for printing the entire plugin's fields / options on the edit.php page. */
function print_alternative_pages( $template_name = 'options_fields.php' )
{ 
	global $settings ;
	global $pagesArray ;
	
	on_init() ;
	
	// Creates the list of available "language template" pages.
	$languages = get_registered_template_language_pages() ;
	$pageSlug = ( isset( $_GET[ 'id' ] ) ) ? $_GET[ 'id' ] : null  ;
	$alternative_pages = get_option( alternative_pages ) ;
	
	// Includes the HTML form containing all fields and options.
	include templates_path . $template_name ;		
}


/* Process options submitted by edit.php. */
function process_alternative_pages()
{	
	global $url ;
	on_init() ;

	$alternative_pages = array() ;
	$languages = get_registered_template_language_pages() ;
	
	foreach( $languages as $language ) // Gets data from form for all existing alternative languages
	{
		$field_name = markup_name_prefix . 'page-' . $language ;
		if( isset( $_POST[ $field_name ] ) )
		{
			$alternative_pages[ $language ] = $_POST[ $field_name ] ; 
		}								
	}

	trigger_option_event( alternative_pages , 'pre-update' , $alternative_pages ) ;
	$page_id = ( empty( $_POST[ 'post-id' ] ) ) ? $url : $_POST[ 'post-id' ] ;

	// No action is required if it's random page
	if( is_main_lang_page_children( $page_id ) OR is_main_lang_page_children( $_POST[ 'post-parent' ] ) )
	{
		backup_old_settings() ;	
	
		set_page_options( alternative_pages , $page_id , $alternative_pages ) ;
		
		if( empty( $alternative_pages ) )
		{
			$pages = get_option( alternative_pages ) ;
			if( isset( $pages[ $page_id ] ) )
			{
				unset( $pages[ $page_id ] ) ;
				set_option( alternative_pages , $pages ) ;
			}
		}		
	}
}


/* Inserts a field that is related to another. */
function insert_relative_locale_field( $parent_field_name , $is_an_array = false , $suffix = markup_name_suffix )
{
	global $locales ;
	
	$name_attr = turns_option_into_name_attr( $parent_field_name , $is_an_array , $suffix ) ;
	return include templates_path . 'locales_list.php' ;
}


/* Checks if lang is a selected option element. */
function is_lang_selected( $locale , $field )
{
	global $settings ;
	$lang = ( is_array(  $settings[ $field ] ) ) ? current( $settings[ $field ] ) :  $settings[ $field ] ;
	return ( ( isset( $settings[ $field ] ) AND $locale === $lang ) ? 'selected' : '' ) ;
}

/* Updates plugin's configuration file and sitemap depending on what kind of page is deleted. */
function page_is_deleted()
{
	global $id ;
	global $is_deleted_flag ;
	global $deleted_lang_templates ;
	
	on_init() ;
	backup_old_settings();
	
	if( is_template_lang_page( $id ) ) // if the page is language template page.
	{
		$value = get_option( template_lang ) ;
		$deleted_lang_templates[ $id ] = $value[ $id ] ;
		$is_deleted_flag = true ;
		
		unset( $value[ $id ] ) ; // Deletes the language template page.
		
		set_option( template_lang , $value ) ;
		// Deletes main language pages fields containing alternative pages belonging to this template page. 
		trigger_option_event( template_lang , 'post-update' , $value ) ;
	}
	elseif( is_alternative_page( $id ) )
	{
		$alternative_pages = get_option( alternative_pages ) ;
		
		foreach( $alternative_pages as $slug => $alternative_page ) // Same as above
		{
			if( false !== ( $lang = array_search( $id , $alternative_page ) ) )
			{
				unset( $alternative_pages[ $slug ][ $lang ] ) ;
			}
		}
		
		set_option( alternative_pages , $alternative_pages ) ;
		generate_sitemap() ;
	}
	elseif( is_main_lang_page_children( $id ) ) // if the page belongs to the main language
	{
		$alternative_pages = get_option( alternative_pages ) ;
		
		if( isset( $alternative_pages[ $id ] ) )
		{
			unset( $alternative_pages[ $id ] ) ; // we delete it from the list
			set_option( alternative_pages , $alternative_pages ) ;
			generate_sitemap() ; // and we update the sitemap
		}
	}
	elseif( $id == get_lang( main_lang ) ) // if the main language page is deleted, we reset everything.
	{
		set_option( main_lang , array() ) ;
		set_option( template_lang , array() ) ;
		set_option( alternative_pages , array() ) ;
		
		generate_sitemap() ;
	}
}


/* Prints list of alternative pages to front. */
function print_alternative_pages_to_front( $link_class = 'link' , $page_id = null )
{
	global $SITEURL ;
	getPagesXmlValues(); 
	global $pagesArray ;
	global $id ;
	on_init() ;
	
	$page_id = ( null == $page_id ) ? $id : $page_id ;
	$alternative_pages = get_option( alternative_pages ) ;
	$template_langs = get_option( template_lang ) ;
	
	if( ! is_main_lang_page_children( $page_id ) )
	{	// Allows to have links from other languages as well when user is looking at an alternative page
		foreach( $alternative_pages as $slug => $alternative_page ) 
		{
			if( false !== array_search( $page_id , $alternative_page ) )
			{
				$old_id = $page_id ;
				$page_id = $slug ;
				$own_link = true ;	// + the link back to the original page belonging to the main language page	
			}				
		}
	}
	
	if( isset( $alternative_pages[ $page_id ] ) )	
	{
		$alternative_page = $alternative_pages[ $page_id ] ;

		foreach( $alternative_page as $lang => $page )
		{
			if( ( ! isset( $old_id ) OR ( isset( $old_id ) AND $old_id !== $page ) )  AND 'Y' !== $pagesArray[ $page ][ 'private' ] )
			{
				$link = $SITEURL . $page ;
				$locale = current( explode( '-' , (string) $template_langs[ $lang ] ) ) ;
				$html_content .= "<a class=\"$link_class $locale\" href=\"$link\">" . $locale . "</a>" ;
			}
		}
		
		if( isset( $own_link ) ) // building the link for the original page belonging to the main language page
		{
			$link = $SITEURL . $page_id ;
			$main_lang = current( explode( '-' , (string) current( get_option( main_lang ) ) ) ) ; 
			$html_content .= "<a class=\"$link_class $main_lang\" href=\"$link\">" . $main_lang . "</a>" ;
		}

		echo $html_content ;
	}
}

/* Creates a new sitemap containing alternative links for pages in different available languages. */
function alterlang_update_sitemap( $sitemap )
{
	global $SITEURL ;
	getPagesXmlValues(); 
	global $pagesArray ;
	on_init() ;
	$alternative_pages = get_option( alternative_pages ) ;
	$template_langs = get_option( template_lang ) ;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            

	// We create a new XML object in order to set the correct XHTML namespace reference.
	$new_sitemap = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns:xhtml="' . xmlns_xhtml . '"></urlset>');
	$new_sitemap -> addAttribute( 'xsi:schemaLocation' , xmlns . ' ' . xmlns_xsd . ' ' . xhtml_xsd . ' ' . xmlns_xhtml , xsi );
	$new_sitemap -> addAttribute( 'xmlns' , xmlns ) ;

	foreach( $sitemap -> children() as $name => $child )  // Each child is a 'url' node.
	{
		$url_item = $new_sitemap -> addChild( $name ) ; // Recreates each of them with the correct datas.
		
		foreach( $child -> children() as $nodeName => $node ) // For normal namespace.
		{
			$url_item -> addChild( $nodeName , $node ) ;
		}

		$slug = str_replace( $SITEURL , '' , (string) $child -> loc ) ;

		if( isset( $alternative_pages[ $slug ] ) )
		{
			foreach( $alternative_pages[ $slug ] as $lang => $alternative_page ) 
			{	
				if( ! empty( $template_langs[ $lang ] ) AND 'Y' !== $pagesArray[ $alternative_page ][ 'private' ] ) 
				{
					// Adds a alternate xhtml link for each translated version available.
					$link = $url_item -> addChild( 'link' ) ;
					$link -> addAttribute( 'rel' , 'alternate' ) ;
					$link -> addAttribute( 'hreflang' , $template_langs[ $lang ] ) ;
					$link -> addAttribute( 'href' , $SITEURL . $alternative_page ) ;
				}
			}
		}
	}	
	
	return $new_sitemap ;
}

/* If the slug of an alternative page is modified, the modification is propagated up to
 * all the other pages that uses it. */
function propagate_page_slug_change()
{
	global $existingurl ;
	global $url ;

	on_init() ;
	$alternative_pages = get_option( alternative_pages ) ;
	$template_langs = get_option( template_lang ) ;

	/* In case an alternative page's slug has changed */
	foreach( $alternative_pages as $slug => $alternative_page )
	{
		if( ! empty( ( $lang = array_search( $existingurl , $alternative_page ) ) ) )
		{
			$alternative_pages[ $slug ][ $lang ] = $url ;
		}
	}
	
	/* In case the slug of a page belonging to "main lang" page has changed */
	foreach( $alternative_pages as $slug => $alternative_page )
	{
		if( $slug === $existingurl )
		{
			$alternative_pages[ $url ] = $alternative_page ;
			unset( $alternative_pages[ $slug ] ) ;
		}
	}
	
	/* In case a template lang page's slug has changed */
	foreach( $template_langs as $slug => $lang )
	{
		if( $slug === $existingurl )
		{
			$template_langs[ $url ] = $lang ;
			unset( $template_langs[ $slug ] ) ;
		}
	}
	
	foreach( $alternative_pages as $slug => $alternative_page )
	{
		if( isset( $alternative_pages[ $slug ][ $existingurl ] ) )
		{
			$alternative_pages[ $slug ][ $url ] = $alternative_page[ $existingurl ] ;
			unset( $alternative_pages[ $slug ][ $existingurl ] ) ;
		}
	}
	
	backup_old_settings() ;
	set_option( alternative_pages , $alternative_pages ) ;
	set_option( template_lang , $template_langs ) ;
}
