$( document ).ready(function()
{
	var alternative_pages_container = $( '#alterlang-alternative-pages' ) ;
	var alternative_pages_section = $( '#alterlang-alternative-pages > fieldset' ) ;
	var is_removed = ( 0 <= alternative_pages_section . length ) ;
	var reg = new RegExp( "," , "g" ) ;
	var pages_list =  alternative_pages_container . data( 'main-lang-list' ) ;
	var current_page_id = alternative_pages_container . data( 'current-page-id' ) ;
	
	if( pages_list !== undefined )
		pages_list = pages_list . split( reg ) ;
	
	
	/* Retrieves the full list of pages belonging to the main lang page and compares it
	 * with the given value.
	 */
	function belongs_to_main_lang( page_id , pages_list )
	{			
		for( page in pages_list )
		{
			if( pages_list[ page ] === page_id )
				return true ;
		}
		
		return false ;
	}
	
	
	/* In order to prevent an option from choosing itself as an alternative page */
	if( undefined !== current_page_id )
	{
		$( 'select.pages-list option[value="' + current_page_id + '"]' ) . attr( 'disabled' , true ) ;
	}
	
	
	/* Makes the alternative pages section appear or disappear depending on what parent page is selected */
	$( 'select#post-parent' ) .off() .on ( 'change' , function()
	{
		if( ! belongs_to_main_lang( $( this ) . val() , pages_list ) ) 
		{
			alternative_pages_section . remove() ;
			is_removed = true ;
		}
		else if( is_removed ) 
		{
			alternative_pages_container . append( alternative_pages_section ) ;
			is_removed = false ;
		}
	}) ;
	
	
	/* Deletes a template lang page field. */
	$( 'button.alterlang-del-lang' ) . off() .on( 'click' , function ( e )
	{
		e. preventDefault() ;
		
		if( confirm( 'Attention, cela supprimera définitivement également toutes ses pages enfants de la liste des pages alternatives ainsi que partout où elles sont enregistrées comme telles.' ) )
		{
			$( this ) . closest( 'div.field-container' ) . remove() ;
			$( 'input[name="alterlang-save"]' ) . trigger( 'click' );
		}
	}) ;
	
	
	/* Forces user to select a locale for previously template lang page. */
	$( 'select[name^="alterlang-template-lang-choices[pages]"]' ) .off() .on ( 'change' , function()
	{
		if( $( this ) . val() !== '' ) 
		{
			$( this ) . next( 'select[name^="alterlang-template-lang-choices[locales]' ) . attr( 'required' , true ) ;
		}
		else
		{
			$( this ) . next( 'select[name^="alterlang-template-lang-choices[locales]' ) . removeAttr( 'required' ) ;
		}
	}) ;


	$( 'select#post-parent' ) . trigger( 'change' ) ;

});