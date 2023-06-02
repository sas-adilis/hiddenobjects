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

{extends file="helpers/list/list_content.tpl"}
{block name="td_content"}
	{if $params.type == 'select'}
		{if isset($params.list) && is_array($params.list) && isset($tr.$key) && $tr.$key != ''}
			{$params.list[$tr.$key]|escape:'html':'UTF-8'}
		{else}
			{if isset($tr.$key) && $tr.$key != ''}
			{$tr.$key|escape:'html':'UTF-8'}
			{else}
			-
			{/if}
		{/if}
	{elseif $params.type == 'bool' && isset($params.activeVisu) && $params.activeVisu}
        {if $tr.$key}
			<i class="icon-check"></i>
		{else}
			<i class="icon-remove"></i>
		{/if}
    {else}
		{$smarty.block.parent}
	{/if}
{/block}