<?php global $SITEURL ; if( isset( $settings[ main_lang ] ) AND is_array( $settings[ main_lang ] ) ) : ?>

	<section 	id="<?php echo markup_name_prefix ?>alternative-pages" 
				class="leftopt" 
				data-main-lang-list="<?php echo get_main_lang_pages_list() ?>"
				data-current-page-id="<?php echo $pageSlug ?>" >

		<fieldset>
		
			<label><?php echo i18n_r('alterlang/lang_Lang_Trans'); ?></label>
			
			<section>

				<label class="al-select" class="clearfix"><?php echo i18n_r('alterlang/lang_Alt_Pages'); ?> :</label>
				
				<?php foreach( $languages as $language ) : ?>
					
					<div>
					
						<select class="pages-list" 
								id="<?php echo markup_name_prefix ?>select-<?php echo $language ?>"
								name="<?php echo markup_name_prefix ?>page-<?php echo $language ?>" >
						
							<option value="" ><?php echo i18n_r('alterlang/lang_Version'); ?> [<?php echo $language ?>] ...</option>
		
							<?php echo get_pages_dropdown_list( $pageSlug , $language ) ; ?>

						</select>
						
						<?php if( false !== ( $alternative_page = has_alternative_page( $pageSlug , $language ) ) ) : ?>
							
							<p class="page-edit-link">
								
								<?php echo i18n_r('alterlang/lang_Edit'); ?> <a href="<?php echo $SITEURL ?>admin/edit.php?id=<?php echo $alternative_page ?>" target="_blank" ><?php echo i18n_r('alterlang/lang_Page'); ?></a>
								
							</p>
							
						<?php endif ; ?>
						
					</div>
					
				<?php endforeach ; ?> 
				
				<?php if( empty( $languages ) ) : ?>
					<p><?php echo i18n_r('alterlang/lang_No_Designated_Page'); ?> : <a href="load.php?id=<?php echo plugin_name ?>"><?php echo i18n_r('alterlang/lang_Configuration'); ?></a>.</p>
				<?php endif ; ?>
			
			</section>
			
		</fieldset>
		
	</section>

	<div class="clear"></div>

<?php endif ; ?>
