<?php global $settings ; global $is_main_lang ; global $is_reverted ; ?>

<section id="<?php echo markup_name_prefix ?>settings-section">

	<?php if( $is_reverted ) : ?>
		<section class="notif-messages">
			<span><?php echo i18n_r('alterlang/lang_Old_Configuration'); ?></span>
		</section>
	<?php endif; ?>
	
	<h3><?php echo i18n_r('alterlang/lang_Page_Title'); ?></h3>
	<p><?php echo i18n_r('alterlang/lang_Description'); ?></p>
	<hr>
	
	<form name="<?php echo markup_name_prefix ?>settings" action="load.php?id=<?php echo plugin_name ?>" method="post" >
		
		<section class="leftsec" id="<?php echo markup_name_prefix ?>main-lang-section">
		
			<div class="field-label">
			<div style="display:flex"><span style="background:black;color:white;font-size:x-large;margin: 7px;padding: 16px;">1</span><h4 style="border-bottom: 1px solid;margin: auto;margin-left: 7px;text-transform: uppercase;"><?php echo i18n_r('alterlang/lang_Base_Lang'); ?></h4></div>
			<br><br>
				<label><?php echo i18n_r('alterlang/lang_Which_Parent'); ?></label>
				<span class="subtitle-explanation">( <?php echo i18n_r('alterlang/lang_Translated_From'); ?> )</span>
			</div>
			
			<div class="field-container">
			
				<select name="<?php echo turns_option_into_name_attr( main_lang , false , markup_name_suffix . '[page]' ) ?>" >
				
					<?php foreach( get_potential_main_language_pages() as $page_slug => $page_title ) : ?>
						
						<option value="<?php echo $page_slug ?>" 
								<?php is_like_lang( $page_slug , get_lang( main_lang ) ) ?> >
						
							<?php echo $page_title ; ?>
							
						</option>
						
					<?php endforeach ?>
		
				</select>
				
				<?php $is_main_lang = true ; insert_relative_locale_field( main_lang , false , markup_name_suffix . '[locale]' ) ; $is_main_lang = false ; ?>
				
			</div>
			
		</section>
		
		<?php if( isset( $settings[ main_lang ] ) AND is_array( $settings[ main_lang ] ) ) : ?>
		
		<section class="rightsec" id="<?php echo markup_name_prefix ?>template-lang-section">
		<div style="display:flex"><span style="background:black;color:white;font-size:x-large;margin: 7px;padding: 16px;">2</span><h4 style="border-bottom: 1px solid;margin: auto;margin-left: 7px;text-transform: uppercase;"><?php echo i18n_r('alterlang/lang_Alt_Lang'); ?></h4></div>
		<br><br>
			<div class="field-label">
				<label><?php echo i18n_r('alterlang/lang_Add_Lang'); ?></label>
			</div>
			
			<?php do { ?>
			
				<?php include templates_path . 'template_langs.php' ;	?>
			
			<?php } while( is_not_last_item( $settings[ template_lang ] ) ) ; ?>
				
		</section>
		
		<?php endif ?>
		
		<section>
		
			<input style="padding:5px 12px;background-color:#009900!important" type="submit" name="<?php echo markup_name_prefix ?>save" value="<?php echo i18n_r('alterlang/lang_Save'); ?>" />
			
		</section>
		
	</form>
	
	<?php if( exists_previous_config() ) : ?>
		<p style="float: right;"><?php echo i18n_r('alterlang/lang_Restore'); ?> <a href="load.php?id=<?php echo plugin_name ?>&revert_config=yes" ><?php echo i18n_r('alterlang/lang_Configuration'); ?></a>.</p>
	<?php endif; ?>
		<br><br>
		<hr>
		
		<h3 style="text-transform: uppercase;"><?php echo i18n_r('alterlang/lang_About'); ?></h3>
		
		<h4 style="text-transform: uppercase;"><?php echo i18n_r('alterlang/lang_Purpose'); ?></h4>
		<p><?php echo i18n_r('alterlang/lang_Purpose_Text'); ?> : 
<code>&lt;?php print_alternative_pages_to_front(); ?&gt;</code> 
</p>

<h4 style="text-transform: uppercase;"><?php echo i18n_r('alterlang/lang_Switch_Lang'); ?></h4>
<p><?php echo i18n_r('alterlang/lang_Flag_Idea'); ?> :
<code>&lt;div class="box languageswitch"&gt;&lt;?php print_alternative_pages_to_front(); ?&gt;&lt;/div&gt;</code></p>
<p><?php echo i18n_r('alterlang/lang_Flag_Idea_Output'); ?> : 
<code>&lt;a class="link en" href="/home"&gt;en&lt;/a&gt;</code></p>
<p>(<?php echo i18n_r('alterlang/lang_EN_Meaning'); ?>)
<br>
<?php echo i18n_r('alterlang/lang_Then_Style'); ?> :</p>
<pre>
.languageswitch .en {
    background: no-repeat url(EnglishFlag.svg); /* Flag image for EN language */
    color: transparent;
    width: 22px;
}
</pre>
<p><?php echo i18n_r('alterlang/lang_Repeat_CSS'); ?></p>

<h4 style="text-transform: uppercase;"><?php echo i18n_r('alterlang/lang_Pages_Structure'); ?></h4>
<p><?php echo i18n_r('alterlang/lang_Pages_Structure_Text'); ?> :</p>
<ul>
    <li><strong>FR</strong> <small>(<?php echo i18n_r('alterlang/lang_First_Lang'); ?>)</small>:
        <ul>
            <li><?php echo i18n_r('alterlang/lang_Page'); ?> FR 1</li>
            <li><?php echo i18n_r('alterlang/lang_Page'); ?> FR 2</li>
            <li><?php echo i18n_r('alterlang/lang_Page'); ?> FR N</li>
        </ul>
    </li>
    <li><strong>EN</strong> <small>(<?php echo i18n_r('alterlang/lang_EN_Alt'); ?>)</small>:
        <ul>
            <li><?php echo i18n_r('alterlang/lang_Page'); ?> EN 1 (<?php echo i18n_r('alterlang/lang_Child_Page'); ?>)</li>
            <li><?php echo i18n_r('alterlang/lang_Page'); ?> EN 2 (<?php echo i18n_r('alterlang/lang_Child_Page'); ?>)</li>
            <li><?php echo i18n_r('alterlang/lang_Page'); ?> EN N (<?php echo i18n_r('alterlang/lang_Child_Page'); ?>)</li>
        </ul>
    </li>
</ul>

<h4 style="text-transform: uppercase;"><?php echo i18n_r('alterlang/lang_Sitemap'); ?></h4>
<p><?php echo i18n_r('alterlang/lang_Better_SEO'); ?> : <b style="color:#66CC33">rel="alternate"</b> <?php echo i18n_r('alterlang/lang_And'); ?> <b style="color:#66CC33">hreflang="en"</b></p>
<hr>
<a href="https://jvcms.fr">Free Plugin Alterlang for Getsimple CE - by JVCMS</a>
</section>

<div class="clear"></div>