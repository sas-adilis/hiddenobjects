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

<div class="panel" id="icon_selection">
	<h3><i class="icon icon-info"></i> {l s='Choose your icon' mod='[[MODULENAME]]'}</h3>
	<ul class="list-inline">
    	{foreach from=$icons item=icon}
    	    <li class="text-center">
    	        <label for="icon_{$icon.id|intval}">
    	            <img src="{$icon.src|escape:'html':'UTF-8'}" alt="" width="64" height="64" />
    	        </label>
    	        <br/>
    	        <span class="badge">{l s='Icon' mod='[[MODULENAME]]'} #{$icon.id|intval}</span>
    	        <br/>
                <input type="radio" id="icon_{$icon.id|intval}" data-icon-src="{$icon.src|escape:'html':'UTF-8'}" name="icon" value="{$icon.id|intval}" {if $icon.selected}checked="checked"{/if} />
    	    </li>
        {/foreach}
	</ul>
</div>
<p class="help-block">{l s='All icons avaibale above are under licence free for commercial use, views author\'s link in file licences.txt in folder %s/views/icons/' sprintf=$module_name mod='[[MODULENAME]]'}</p>
<br/>
<br/>
{foreach from=$icons item=icon}
    {if $icon.selected}
        <img src="{$icon.src|escape:'html':'UTF-8'}" alt="" width="{$object->size|intval}" height="{$object->size|intval}" class="col-lg-offset-3{if $object->use_effect|intval} animated infinite tada{/if}" id="icon-demonstration"  />
    {/if}
{/foreach}
