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
	{if $input.name == 'how_many'}
		{assign var='value_text' value=$fields_value[$input.name]}
		<div class="col-lg-8">
			<div class="input-group-flex">
				<input type="text"
					   name="{$input.name}"
					   id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
					   value="{if isset($input.string_format) && $input.string_format}{$value_text|default|string_format:$input.string_format|escape:'html':'UTF-8'}{else}{$value_text|default|escape:'html':'UTF-8'}{/if}"
					   class="{if isset($input.class)}{$input.class}{/if}{if $input.type == 'tags'} tagify{/if}"
						{if isset($input.size)} size="{$input.size}"{/if}
						{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}
						{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}
						{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
						{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
						{if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
						{if isset($input.required) && $input.required } required="required" {/if}
						{if isset($input.placeholder) && $input.placeholder } placeholder="{$input.placeholder}"{/if}
				/>
				  <select name="renew" class=" fixed-width-xl" id="renew">
					  {foreach from=$renew_values item=renew_value}
						  <option {if $renew_value.value == $renew_value_selected}selected{/if} value="{$renew_value.value|escape:'html':'UTF-8'}">{$renew_value.label|escape:'html':'UTF-8'}</option>
					  {/foreach}
				  </select>
			</div>
			{if isset($input.desc) && !empty($input.desc)}
				<p class="help-block">
					{if is_array($input.desc)}
						{foreach $input.desc as $p}
							{if is_array($p)}
								<span id="{$p.id}">{$p.text}</span><br />
							{else}
								{$p}<br />
							{/if}
						{/foreach}
					{else}
						{$input.desc}
					{/if}
				</p>
			{/if}
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}