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

{if $contests|count}
    {foreach from=$contests item=contest}
        <div class="hiddenobject_{$prefix|escape:'htmlall':'UTF-8'}_rules" style="display:none">
        	{if isset($contest.rules) && !empty($contest.rules)}
                <div id="hiddenobject_{$prefix|escape:'htmlall':'UTF-8'}_rule_{$contest.id_hiddenobject|intval}">
                    {$contest.rules} {* HTML ouput, no escape necessary *}
                </div>
        	{/if}
        </div>
    {/foreach}
{/if}