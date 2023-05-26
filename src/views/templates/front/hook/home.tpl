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

{foreach from=$contests item=contest}
	{if isset($contest.images.home) && is_array($contest.images.home)}
        {if isset($contest.rules) && !empty($contest.rules)}
            <a class="hiddenobject_{$prefix|escape:'htmlall':'UTF-8'}_fancybox" href="#hiddenobject_{$prefix|escape:'htmlall':'UTF-8'}_rule_{$contest.id_hiddenobject|intval}">
        {/if}
        <img src="{$contest.images.home.src|escape:'htmlall':'UTF-8'}" alt="{$contest.name|escape:'htmlall':'UTF-8'}" class="img-responsive" />
		{if isset($contest.rules) && !empty($contest.rules)}
            </a>
        {/if}
        <br/>
	{/if}
{/foreach}