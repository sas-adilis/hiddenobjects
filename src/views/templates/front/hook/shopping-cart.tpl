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

{if is_array($objects) && $objects|count}
    <div class="display_hiddenobject_vouchers" style="display:none;">
        <div class="display_hiddenobject_vouchers_title">{l s='Objects you have found' mod='easterhiddenobjects'}</div>
		{foreach $objects as $object}
            <form action="{if $opc}{$link->getPageLink('order-opc.php', true)|escape:'html':'UTF-8'}{else}{$link->getPageLink('order.php', true)|escape:'html':'UTF-8'}{/if}" method="post" id="voucher">
                <input type="hidden" name="discount_name" value="{$object.code|escape:'html':'UTF-8'}" />
                <img src="{$object.icon_url|escape:'html':'UTF-8'}" alt="" width="24" height="24" />
                {if $object.code != ''}<strong class="voucher_name" data-code="{$object.code|escape:'html':'UTF-8'}">{$object.code|escape:'html':'UTF-8'}</strong> - {/if}{$object.name|escape:'htmlall':'UTF-8'}
                <input type="hidden" name="submitDiscount" /><input type="submit" name="submitAddDiscount" value="{l s='Add' mod='easterhiddenobjects'}" class="btn button" />
            </form>
		{/foreach}
	</div>
{/if}