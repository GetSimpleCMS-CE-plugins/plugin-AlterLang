<?php
/*===========================================================================================
 * THIS FILE STORES ALL EVENTS DRIVEN ACTIONS.
 * They are kept apart and are specific to this plugin business logic.
 */

global $is_deleted_flag ;
global $deleted_lang_templates ;

$is_deleted_flag = false ;
$deleted_lang_templates = array() ;
$options_events = array
( 
	main_lang => array( 'pre-update' =>  array( 'pre_update_main_lang' ) ,
						'post-update' => array( 'post_update_main_lang_children' ) ) , 
						
	template_lang => array( 'pre-update' => array( 'pre_update_template_lang' , 'check_if_deleted_template_lang' ) ,
							'post-update' => array(  'delete_related_alternative_pages' , 'regenerate_sitemap' ) ) , 
	
	alternative_pages => array( 'pre-update' => array( 'pre_update_alternative_page' ) ) , 
	
	revert_config => array( 'post_revert' => array( 'regenerate_sitemap' ) ) 
) ;


/* Combines the values from the two different html fields into one php array for the main language option */
function pre_update_main_lang( &$option_value )
{
	$option_value = array_combine( (array) $option_value[ 'page' ] , (array) $option_value[ 'locale' ] ) ;
	$option_value = sanitize_value( $option_value ) ;
}


/* Combines the values from the two different html fields into one php array for the language template pages option */
function pre_update_template_lang( &$option_value )
{
	$option_value = array_combine( $option_value[ 'pages' ] , $option_value[ 'locales' ] ) ;
	$option_value = sanitize_value( $option_value ) ;
}


/* Processes alternative pages values accordingly by deleting empty fields ( when a page is saved from edit.php ). */
function pre_update_alternative_page( &$option_value )
{
	$option_value = sanitize_value( $option_value ) ;
}


/* Updates the children list for the "main language template" page. */
function post_update_main_lang_children( &$option_value )
{
	global $pagesArray ;
	
	$children_list = array() ;
	$exclude_pages = array() ;
	$pages_list = $pagesArray ;

	$key = ( is_array( $option_value ) ) ? key( $option_value ) : $option_value ;
	unset( $pages_list[ $key  ] ) ;
	
	do // Filters the entire list of pages to check which one belongs to the main language page.
	{ // We use a double loop in order to deal with multi-level pages hierarchy.
		foreach( $pages_list as $name => $page )
		{
			if( empty( $page[ 'parent' ] ) OR in_array( $page[ 'parent' ] , $exclude_pages  ) )
			{
				$exclude_pages[] = $name ;
				unset( $pages_list[ $name ] ) ;
			}
			elseif( $page[ 'parent' ] === $key  OR in_array( $page[ 'parent' ] , $children_list ) )
			{
				$children_list[] = $name ;
				unset( $pages_list[ $name ] ) ;
			}
		}
		
	} while( ! empty( $pages_list ) ) ;
	
	set_option( main_lang_children , $children_list ) ;
}


/* Updates the main language page children list after a new page is created. */
function update_main_lang_children_list()
{
	$main_lang = get_option( main_lang ) ;
	trigger_option_event( main_lang , 'post-update' , $main_lang ) ;	
}


/* Detects if a language template page has been deleted */
function check_if_deleted_template_lang( &$option_value )
{
	global $is_deleted_flag ;
	global $deleted_lang_templates ;
	$template_langs = get_option( template_lang ) ;

	if( count( $option_value ) < count( $template_langs ) )
	{		
		$is_deleted_flag = true ;
		$deleted_lang_templates = array_diff( $template_langs , $option_value ) ;
	}
}


/* Deletes alternatives pages related to a previously deleted language template page. */
function delete_related_alternative_pages()
{
	global $is_deleted_flag ;
	global $deleted_lang_templates ; // array has been built in the function above e.g array( 'fr' => 'fr-FR' )
	$alternative_pages = get_option( alternative_pages ) ;

	if( true === $is_deleted_flag )
	{
		foreach( $alternative_pages as $slug => $alternative_page )
		{
			foreach( $deleted_lang_templates as $page => $language )
			{
				if( isset( $alternative_page[ $page ] ) )
					unset( $alternative_pages[ $slug ][ $page ] ) ;
			}
			// if a main language page has no translation left after the operation, we delete it from the list
			// because we don't want empty values.
			if( empty( $alternative_pages[ $slug ] ) )
				unset( $alternative_pages[ $slug ] ) ;
		}
		
		$is_deleted_flag = false ;
		$deleted_lang_templates = array() ;

		set_option( alternative_pages , $alternative_pages ) ;
	}
}


/* Forces to regenerate sitemap. */
function regenerate_sitemap()
{
	generate_sitemap() ;
}
