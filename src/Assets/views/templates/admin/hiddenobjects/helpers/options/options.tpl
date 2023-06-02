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

{extends file="helpers/options/options.tpl"}
{block name="input"}
	{if $field['type'] == 'test_ip'}
		<div class="col-lg-9">
			<div class="row">
				<div class="col-lg-8">
					<input type="text"{if isset($field['id'])} id="{$field['id']|intval}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field['value']|escape:'html':'UTF-8'}" />
				</div>
				<div class="col-lg-1">
					<button type="button" class="btn btn-default" onclick="addRemoteAddr();"><i class="icon-plus"></i> {l s='Add my IP' mod='easterhiddenobjects'}</button>
				</div>
			</div>
			{if !empty($field['desc'])}
		    <p class="help-block">{$field['desc']|escape:'html':'UTF-8'}</p>
            {/if}
		</div>
		<script type="text/javascript">
			function addRemoteAddr()
			{
				var length = $('input[name={$key|escape:'htmlall':'UTF-8'}]').attr('value').length;
				if (length > 0)
					$('input[name={$key|escape:'htmlall':'UTF-8'}]').attr('value',$('input[name={$key|escape:'htmlall':'UTF-8'}]').attr('value') +',{Tools::getRemoteAddr()|escape:'htmlall':'UTF-8'}');
				else
					$('input[name={$key|escape:'htmlall':'UTF-8'}]').attr('value','{Tools::getRemoteAddr()|escape:'htmlall':'UTF-8'}');
			}
		</script>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}