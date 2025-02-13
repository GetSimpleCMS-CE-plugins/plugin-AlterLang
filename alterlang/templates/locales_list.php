<?php global $settings ; global $is_main_lang; ?>

<select name="<?php echo $name_attr ?>" >
	
		<?php if( false == is_not_last_item( $settings[ template_lang ] , false ) AND ! $is_main_lang ) : ?>
		
			<option value=""><?php echo i18n_r('alterlang/lang_Choose_Local'); ?></option>
			
		<?php endif ; ?>

		<?php foreach( $locales as $locale ) : ?>
			
				<option value="<?php echo $locale ?>" 
						<?php echo is_lang_selected( $locale , $parent_field_name ) ; ?> >
						
					<?php echo $locale ; ?>
					
				</option>
			
		<?php endforeach ?>
			
</select>