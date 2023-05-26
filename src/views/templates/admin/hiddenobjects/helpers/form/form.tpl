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

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'file_lang'}
		<div class="row file_lang">
			{foreach from=$languages item=language}
				{if $languages|count > 1}
					<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang|intval != $defaultFormLanguage|intval}style="display:none"{/if}>
				{/if}
				<div class="col-lg-6">
					{if isset($fields[0]['form']['images'])}
					<img src="{$image_baseurl|escape:'html':'UTF-8'}{$fields[0]['form']['images'][$language.id_lang|intval]}" class="img-thumbnail" />
					{/if}
					<div class="dummyfile input-group">
						<input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" type="file" name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="hidden hide-file-upload" />
						<span class="input-group-addon"><i class="icon-file"></i></span>
						<input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}-name" type="text" class="disabled" name="filename" readonly />
						<span class="input-group-btn">
							<button id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
								<i class="icon-folder-open"></i> {l s='Choose a file' mod='easterhiddenobjects'}
							</button>
						</span>
					</div>
					{if !empty($input.desc)}
					    <p class="help-block">{$input.desc|escape:'html':'UTF-8'}</p>
                    {/if}
					{if is_array($fields_value[$input.name]) && isset($fields_value[$input.name][$language.id_lang])}
					{assign var='file' value=$fields_value[$input.name][$language.id_lang]}
					{if is_array($file) && $file.src}
						<div class="form-group">
							<div class="col-lg-12">
								<div>
									<img src="{$file.src|escape:'html':'UTF-8'}" alt="" class="imgm img-thumbnail">
									{if isset($file.size)}<p>{l s='File size' mod='easterhiddenobjects'} {$file.size|escape:'htmlall':'UTF-8'}</p>{/if}
									{if isset($input.delete_url)}
									<p>
										<a class="btn btn-default" href="{$input.delete_url|escape:'html':'UTF-8'}&id_lang={$language.id_lang|intval}">
											<i class="icon-trash"></i> {l s='Delete' mod='easterhiddenobjects'}
										</a>
									</p>
									{/if}
								</div>
							</div>
						</div>
					{/if}
					{/if}
				</div>
				{if $languages|count > 1}
					<div class="col-lg-2">
						<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
							{$language.iso_code|escape:'htmlall':'UTF-8'}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							{foreach from=$languages item=lang}
							<li><a href="javascript:hideOtherLanguage({$lang.id_lang|intval});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
							{/foreach}
						</ul>
					</div>
				{/if}
				{if $languages|count > 1}
					</div>
				{/if}
				<script>
				$(document).ready(function(){
					$('#{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}-selectbutton').click(function(e){
						$('#{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}').trigger('click');
					});
					$('#{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}').change(function(e){
						var val = $(this).val();
						var file = val.split(/[\\/]/);
						$('#{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}-name').val(file[file.length-1]);
					});
				});
			</script>
			{/foreach}
		</div>
	{elseif $input.type == 'file'}
		{if isset($input.display_image) && $input.display_image}
			{if isset($fields_value[$input.name].image) && $fields_value[$input.name].image}
				<div id="image">
					{$fields_value[$input.name].image|escape:'html':'UTF-8'}
					<p align="center">{l s='File size' mod='easterhiddenobjects'} {$fields_value[$input.name].size|floatval}{l s='kb' mod='easterhiddenobjects'}</p>
					<a href="{$current|escape:'html':'UTF-8'}&{$identifier|escape:'url':'UTF-8'}={$form_id|intval}&token={$token|escape:'UTF-8'}&deleteImage=1">
						<img src="../img/admin/delete.gif" alt="{l s='Delete' mod='easterhiddenobjects'}" /> {l s='Delete' mod='easterhiddenobjects'}
					</a>
				</div><br />
			{/if}
		{/if}

		{if isset($input.lang) AND $input.lang}
			<div class="translatable clearfix">
				{foreach $languages as $language}
					<div class="lang_{$language.id_lang|intval}" id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" style="display:{if $language.id_lang|intval == $defaultFormLanguage|intval}block{else}none{/if}; float: left;">
						{if isset($input.display_image) && $input.display_image}
							{if isset($fields_value[$input.name][$language.id_lang].src) && $fields_value[$input.name][$language.id_lang].src}
								<div>
									<img src="{$fields_value[$input.name][$language.id_lang].src|escape:'html':'UTF-8'}" alt="" class="imgm img-thumbnail">
									<p align="center">{l s='File size' mod='easterhiddenobjects'} {$fields_value[$input.name][$language.id_lang].size|intval}{l s='kb' mod='easterhiddenobjects'}</p>
									{if isset($input.delete_url)}
										<p>
											<a class="btn btn-default" href="{$input.delete_url|escape:'html':'UTF-8'}&id_lang={$language.id_lang|intval}">
												<i class="icon-trash"></i> {l s='Delete' mod='easterhiddenobjects'}
											</a>
										</p>
									{/if}
								</div><br />
							{/if}
						{/if}
						<input type="file" name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" {if isset($input.id)}id="{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}"{/if} />
					</div>
				{/foreach}
			</div><br />
			{if !empty($input.desc)}
			    <p class="margin-form help-block">{$input.desc|escape:'html':'UTF-8'}</p>
            {/if}
		{else}
			<input type="file" name="{$input.name|escape:'htmlall':'UTF-8'}" {if isset($input.id)}id="{$input.id|escape:'htmlall':'UTF-8'}"{/if} />
			{if !empty($input.desc)}
			    <p class="help-block">{$input.desc|escape:'html':'UTF-8'}</p>
            {/if}
		{/if}
		{if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint|escape:'htmlall':'UTF-8'}<span class="hint-pointer">&nbsp;</span></span>{/if}
	{elseif $input.type == 'html'}
		{if isset($input.html_content)}
			<label class="col-lg-3"></label><div class="col-lg-9">{$input.html_content|escape:'html':'UTF-8'}</div>
		{else}
			<label class="col-lg-3"></label><div class="col-lg-9">{$input.name|escape:'htmlall':'UTF-8'}</div>
		{/if}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
{block name="input"}
    {if $input.type == 'text'}
        {if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
    		{if isset($input.prefix) && $input.prefix}{$input.prefix|escape:'htmlall':'UTF-8'}{/if}
    		{$smarty.block.parent}
            {if isset($input.suffix) && $input.suffix}{$input.suffix|escape:'htmlall':'UTF-8'}{/if}
        {else}
    		{$smarty.block.parent}
        {/if}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}