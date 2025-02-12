<?php global $settings ; global $is_main_lang ; global $is_reverted ; ?>

<section id="<?php echo markup_name_prefix ?>settings-section">

	<?php if( $is_reverted ) : ?>
		<section class="notif-messages">
			<span>Ancienne configuration restaurée avec succès !</span>
		</section>
	<?php endif; ?>
	
	<form name="<?php echo markup_name_prefix ?>settings" action="load.php?id=<?php echo plugin_name ?>" method="post" >
		
		<section class="leftsec" id="<?php echo markup_name_prefix ?>main-lang-section">
		
			<div class="field-label">
			<div style="display:flex"><span style="background:black;color:white;font-size:x-large;margin: 7px;padding: 16px;">1</span><h4 style="border-bottom: 1px solid;margin: auto;margin-left: 7px;">FIRST LANGUAGE</h4></div>
			<br><br>
				<label>Quelle page parente représente la langue principale du site ? (mother language)</label>
				<span class="subtitle-explanation">( celle depuis laquelle les autres pages seront traduites )</span>
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
		<div style="display:flex"><span style="background:black;color:white;font-size:x-large;margin: 7px;padding: 16px;">2</span><h4 style="border-bottom: 1px solid;margin: auto;margin-left: 7px;">ALTERNATIVES</h4></div>
		<br><br>
			<div class="field-label">
				<label>Ajouter des templates linguistiques : (alternate languages : page and local)</label>
			</div>
			
			<?php do { ?>
			
				<?php include templates_path . 'template_langs.php' ;	?>
			
			<?php } while( is_not_last_item( $settings[ template_lang ] ) ) ; ?>
				
		</section>
		
		<?php endif ?>
		
		<section>
		
			<input type="submit" name="<?php echo markup_name_prefix ?>save" value="Sauvegarder" />
			
		</section>
		
	</form>
	
	<?php if( exists_previous_config() ) : ?>
		<p style="float: right;">Rétablir la précédente <a href="load.php?id=<?php echo plugin_name ?>&revert_config=yes" >configuration</a></p>
	<?php endif; ?>
		<br><br>
		<hr style="
    display: block;
    width: 100%;
    height: 50px;
    border: none;
">
		<h4 style="border-bottom: 1px solid;">PURPOSE</h4>
		<p>The purpose is to provide a real multilingual solution for Getsimple, uniques URLs (Better SEO), without the use of i18n, it is working with basic Getsimple CMS. It allows to add alternative pages to the sitemap. 
Use the following code in your theme to switch between lang : 
<code>&lt;?php print_alternative_pages_to_front(); ?&gt;</code> 
</p>

<h4 style="border-bottom: 1px solid;">SWITCH CODE EXAMPLE</h4>
<p>Here is an idea for switching lang with flag : 
<code>&lt;div class="box languageswitch"&gt;&lt;?php print_alternative_pages_to_front(); ?&gt;&lt;/div&gt;</code>
<br>which output this code 
<code>&lt;a class="link en" href="/home"&gt;en&lt;/a&gt;</code>
<br>("en" comes from the mother page "en" which stands for english slug (system page id))
<br>
<br>then it is styled with the following CSS (3 lines):
<pre>
.languageswitch .en {
    background: no-repeat url(EnglishFlag.svg); /* Flag image for EN language */
    color: transparent;
    width: 22px;
}
</pre>
<br>
Repeat this CSS for each lang flag you needed to the multilingual project.</p>

<h4 style="border-bottom: 1px solid;">PAGES STRUCTURE</h4>
<p>You have to organize your pages with the mother and children :</p>
<ul>
    <li><strong>FR</strong> <small>(FIRST LANGUAGE - mother page)</small>:
        <ul>
            <li>page fr 1</li>
            <li>page fr 2</li>
            <li>page fr N</li>
        </ul>
    </li>
    <li><strong>EN</strong> <small>(EN is alternate french in this example)</small>:
        <ul>
            <li>page en 1 (children page)</li>
            <li>page en 2 (children page)</li>
            <li>page en N (children page)</li>
        </ul>
    </li>
</ul>

<h4 style="border-bottom: 1px solid;">SITEMAP</h4>
<p>Better SEO : You have to install <a href="https://github.com/GetSimpleCMS-CE-plugins/plugin-MetaRobots/archive/refs/heads/main.zip">MetaRobots plugin</a> for printing alternate pages in the Sitemap with tags for search engine : <b>rel="alternate"</b> and <b>hreflang="en"</b></p>
<br><br>
<a href="https://jvcms.fr">Free Plugin Alterlang for Getsimple - by JVCMS</a>
</section>

<div class="clear"></div>