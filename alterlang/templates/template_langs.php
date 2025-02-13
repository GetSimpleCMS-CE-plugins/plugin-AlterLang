<div class="field-container">
	<select name="<?php echo turns_option_into_name_attr( template_lang , true , markup_name_suffix . '[pages]' ) ?>" >
	
		<option value=""><?php echo i18n_r('alterlang/lang_Choose_Page'); ?></option>
	
		<?php foreach( get_potential_template_language_pages() as $page_slug => $page_title ) : ?>
			
				<option value="<?php echo $page_slug ?>"
						
						<?php is_like_lang( $page_slug , key( $settings[ template_lang ] ) ) ?> >
						
					<?php echo $page_title ; ?>
					
				</option>
			
		<?php endforeach ?>
		
	</select>
	
	<?php insert_relative_locale_field( template_lang , true , markup_name_suffix . '[locales]'  ) ?>
	
	<?php if( false == is_not_last_item( $settings[ template_lang ] , false ) ) : ?>
	
		<!--<button class="<?php echo markup_name_prefix ?>add-lang" >+</button>-->
		
	<?php else : ?>
	
		<button class="<?php echo markup_name_prefix ?>del-lang" ><?php echo i18n_r('alterlang/lang_Delete'); ?></button>
		
	<?php endif ?>
</div>