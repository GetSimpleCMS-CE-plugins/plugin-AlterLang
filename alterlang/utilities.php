<?php 
/*===========================================================================================
 * THIS FILE STORES ALL THE USEFUL FUNCTIONS FOR THIS PLUGIN.
 * They are kept apart because they are not involved in the plugin's 
 * business logic itself and could be use for various purposes. 
 */


/* Indicates if a given page has at least one child page. */
function has_at_least_one_child_page( array $parent_pages )
{
	getPagesXmlValues() ;
	global $pagesArray ;

	// Reduces the multi-dimensional pages array to a one, parent-filtered, dimension array.
	$parent_filtered_pages = array_unique( array_column( $pagesArray , 'parent' ) ) ;
	
	return  array_intersect( $parent_pages , $parent_filtered_pages ) ;
}


/* On first installation. */
function on_init()
{
	global $settings ;
	global $is_reverted ;
	
	if( isset( $_GET[ 'revert_config' ] ) AND $_GET[ 'revert_config' ] === 'yes' )
	{
		$_GET[ 'revert_config' ]= 'no' ; // Important, prevents an infinite loop.
		revert_previous_config() ;
		trigger_option_event( revert_config , 'post_revert' ) ; // regenerate sitemap
		
		$is_reverted = true ;
	}
	
	create_plugin_data_storage_directory() ;
	
	load_settings() ;
}


/* Creates directory where plugin's datas will be stored. */
function create_plugin_data_storage_directory()
{
	if( ! is_dir( datas_storage_dir_name ) AND ( false === mkdir( datas_storage_dir_name ) ) )
	{
		throw new \Exception( 'Impossible de créer le dossier de stockage des données.' ) ;
	}		
}


/* Loads settings from disk if exists. */
function load_settings()
{
	global $settings ;
	global $locked ;
	
	if( is_file( settings_file ) AND false !== ( $result = file_get_contents( settings_file ) ) )
	{
		$settings = convert_json_data_to_php( $result ) ;
		$locked = false ; // prevents undesired write access to the config file.
	}
}


/* Backs up old settings before saving the new ones, in case we need to revert it. */
function backup_old_settings()
{	
	if( is_file( settings_file ) )
		return copy( settings_file , backup_settings_file ) ;
}


/* Checks if a previous configuration file has been saved for backup. */
function exists_previous_config()
{
	return is_file( backup_settings_file ) ;
}

/* Allows admin to sitch back to a previous configuration in case of a problem or bug. */
function revert_previous_config()
{
	if( false === copy( backup_settings_file , settings_file ) )
	{
		throw new \Exception( 'Une erreur est survenue : impossible de restaurer la configuration.' ) ;
	}
}


/* Persists a given setting option on the disk. */
function persist_changes()
{
	global $settings ;
	global $locked ;
	
	if( ! $locked AND false === file_put_contents( settings_file , convert_php_to_json_data( $settings ) ) ) 
	{
		throw new \Exception( 'Impossible d\'écrire dans le fichier de configuration' ) ;
	}
}


/* Retrieves a given option's value. */
function get_option( $option_name )
{
	global $settings ;
	
	if( ! array_key_exists( $option_name , $settings ) )
	{
		set_option( $option_name ) ;
	}

	return $settings[ $option_name ] ;
}


/* Sets or updates a given option's value. */
function set_option( $option_name , $option_value = null )
{
	global $settings ;
	
	$option_value = ( false === $option_value OR null === $option_value ) ? array() : $option_value ; 
	
	$settings[ $option_name ] = $option_value ;
	
	persist_changes() ;
}


/* Sets or updates a given option's value. */
function set_page_options( $option_name , $page_name , $page_values = null )
{
	global $settings ;
	
	if( false !== $page_values )
	{
		$settings[ $option_name ][ $page_name ] = $page_values ;
	}
	else
	{
		unset( $settings[ $option_name ][ $page_name ] ) ;
	}

	persist_changes() ;
}


/* Converts PHP array to JSON datas for storage. */
function convert_php_to_json_data( $datas = array() )
{
	if( ! is_array( $datas ) )
		$datas = array( $datas ) ;
	
	return json_encode( $datas , JSON_PRETTY_PRINT ) ; 
}


/* Converts JSON datas to PHP array. */
function convert_json_data_to_php( $datas )
{
	return json_decode( $datas , true ) ; 
}


/* Processes POST datas ( load.php ) and saves them. */
function process_form()
{
	global $settings ;
	global $locked ;
	
	if( isset( $_POST[ markup_name_prefix . 'save' ] ) )
	{
		$locked = false ;
		unset( $_POST[ markup_name_prefix . 'save' ] ) ;
		
		backup_old_settings() ; //Allows to switch back to old config in case of a problem.
		
		foreach( $_POST as $field_name => $value )
		{	
			if( preg_match( '#^' . markup_name_prefix . '#' , $field_name ) )
			{
				$option_name =  turns_name_attr_into_option( $field_name ) ;
				trigger_option_event( $option_name , 'pre-update' , $value ) ;
				set_option( $option_name , $value ) ;
				trigger_option_event( $option_name , 'post-update' , $value ) ;
			}
		}
	}
}


/* Cleans values depending on their type. */
function sanitize_value( $value )
{
	if( is_array( $value ) )
	{
		$value = array_diff( $value , array( '' ) ) ;
		$value = array_unique( $value ) ;
		
		if( empty( $value ) )
		return array() ;
	}
	
	if( empty( $value ) )
		return false ;
		
	
	return $value ;
}


/* Turns option's name into a standard name attribute value. */
function turns_option_into_name_attr( $option_name , $is_an_array = false , $suffix = markup_name_suffix )
{
	return markup_name_prefix . str_replace( '_' , '-' , $option_name ) . $suffix . ( ( $is_an_array ) ? '[]' : '' ) ;
}


/* Turns option's name into a standard name attribute value. */
function turns_name_attr_into_option( $name_attr , $suffix = markup_name_suffix )
{
	$name_attr = str_replace( markup_name_prefix , '' , $name_attr ) ;
	$name_attr = str_replace( $suffix , '' , $name_attr ) ;
	
	return str_replace( '-' , '_' , $name_attr) ;
}


/* Checks if we have reached last array key. */
function is_not_last_item( array &$datas , $move_cursor = true )
{
	$current = current( $datas ) ;
	
	if( $move_cursor )
		next( $datas ) ;
	
	return (  ( false !== $current ) ? $current : false ) ;
}


/* Helps to execute specific functions on options changes. */
function trigger_option_event( $option_name , $event_name , &$option_value = null )
{
	global $options_events ;
	
	if( isset( $options_events[ $option_name ] ) AND isset( $options_events[ $option_name ][ $event_name ] ) )
	{
		foreach( $options_events[ $option_name ][ $event_name ] as $func )
		{
			$func( $option_value ) ;
		}
	}
	
	return $option_value ;
}