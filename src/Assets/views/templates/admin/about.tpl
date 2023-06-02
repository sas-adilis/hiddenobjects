{**
 * 2016 Adilis
 *
 * Make your shop interactive for Easter: hide objects and ask your customers to find them in order to win a
 * discount coupon. Make your brand stand out by offering an original game: a treasure hunt throughout your products.
 *
 *  @author    Adilis <support@adilis.fr>
 *  @copyright 2016 SAS Adilis
 *  @license   http://www.adilis.fr
 *}

<div class="panel" id="about_module">
	<h3><i class="icon icon-info"></i> {l s='About this module' mod='[[MODULENAME]]'}</h3>
	<div class="row clearfix">
		<div class="col-md-12 col-lg-4">
			<img src="..{$module_dir|escape:'htmlall':'UTF-8'|escape:'htmlall':'UTF-8'}assets/module-teaser-{if $iso_code!='fr'}en{else}fr{/if}.jpg" alt="{l s='Adilis, web agency' mod='[[MODULENAME]]'}" height="219" width="600" style="max-width: 100%; height: auto"/>
		</div>
		<div class="col-md-6 col-lg-3 col-lg-offset-1">
			<p>
			<h4>&raquo; {l s='The Author' mod='[[MODULENAME]]'} :</h4>
			<img src="..{$module_dir|escape:'htmlall':'UTF-8'|escape:'htmlall':'UTF-8'}views/img/admin/logo-adilis.gif" alt="{l s='Adilis, web agency' mod='[[MODULENAME]]'}" height="54" width="125" style="max-width: 100%; height: auto"/>
			</p>
		</div>
		<div class="col-md-6 col-lg-4">
			<p>
			<h4>&raquo; {l s='The Module' mod='[[MODULENAME]]'} :</h4>
			<ul class="list-unstyled">
				<li>{l s='Module version' mod='[[MODULENAME]]'} : {$moduleversion|escape:'htmlall':'UTF-8'}</a></li>
				<li>{l s='Prestashop version' mod='[[MODULENAME]]'} : {$psversion|escape:'htmlall':'UTF-8'}</a></li>
				<li>{l s='Php version' mod='[[MODULENAME]]'} : {$phpversion|escape:'htmlall':'UTF-8'}</a></li>
				<li><a href="..{$module_dir|escape:'htmlall':'UTF-8'|escape:'htmlall':'UTF-8'}readme_en.pdf" target="_blank">{l s='English Documentation' mod='[[MODULENAME]]'}</a></li>
				<li><a href="..{$module_dir|escape:'htmlall':'UTF-8'|escape:'htmlall':'UTF-8'}readme_fr.pdf" target="_blank">{l s='French Documentation' mod='[[MODULENAME]]'}</a></li>
			</ul>
			</p>
		</div>

	</div>
</div>