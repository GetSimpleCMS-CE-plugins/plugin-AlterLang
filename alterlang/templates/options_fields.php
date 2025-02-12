<?php global $SITEURL ; if( isset( $settings[ main_lang ] ) AND is_array( $settings[ main_lang ] ) ) : ?>

	<section 	id="<?php echo markup_name_prefix ?>alternative-pages" 
				class="leftopt" 
				data-main-lang-list="<?php echo get_main_lang_pages_list() ?>"
				data-current-page-id="<?php echo $pageSlug ?>" >

		<fieldset>
		
			<label>Langues & traductions</label>
			
			<section>

				<label class="al-select" class="clearfix">Sélection des pages alternatives :</label>
				
				<?php foreach( $languages as $language ) : ?>
					
					<div>
					
						<select class="pages-list" 
								id="<?php echo markup_name_prefix ?>select-<?php echo $language ?>"
								name="<?php echo markup_name_prefix ?>page-<?php echo $language ?>" >
						
							<option value="" >Version [<?php echo $language ?>] ...</option>
		
							<?php echo get_pages_dropdown_list( $pageSlug , $language ) ; ?>

						</select>
						
						<?php if( false !== ( $alternative_page = has_alternative_page( $pageSlug , $language ) ) ) : ?>
							
							<p class="page-edit-link">
								
								Éditer la <a href="<?php echo $SITEURL ?>admin/edit.php?id=<?php echo $alternative_page ?>" target="_blank" >page</a>
								
							</p>
							
						<?php endif ; ?>
						
					</div>
					
				<?php endforeach ; ?> 
				
				<?php if( empty( $languages ) ) : ?>
					<p>Vous n'avez encore désigné aucune page en tant que catégorie linguistique ou bien elles n'ont pas de pages enfants: <a href="load.php?id=<?php echo plugin_name ?>">configurer</a>.</p>
				<?php endif ; ?>
			
			</section>
			
		</fieldset>
		
	</section>

	<div class="clear"></div>

<?php endif ; ?>

