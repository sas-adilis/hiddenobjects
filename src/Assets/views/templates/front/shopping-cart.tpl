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
    <div class="display_hiddenobject_vouchers">
        <div class="display_hiddenobject_vouchers_title">{l s='Objects you have found' mod='[[MODULENAME]]'}</div>
		{foreach $objects as $object}
            <div class="display_hiddenobject_vouchers_item" data-code="{$object.code|escape:'html':'UTF-8'}">
                <img src="{$object.icon_url|escape:'html':'UTF-8'}" alt="" width="24" height="24" />
                <span><strong class="voucher_name">{$object.code|escape:'html':'UTF-8'}</strong> - {$object.name|escape:'htmlall':'UTF-8'}</span>
                <button type="button" class="btn btn-primary button">{l s='Add' mod='[[MODULENAME]]'}</button>
            </div>
		{/foreach}
	</div>
{/if}