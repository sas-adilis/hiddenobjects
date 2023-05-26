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

<script type="text/javascript">
	var cartRulesToken = '{$cartRulesToken|escape:'quotes':'UTF-8'}';
	var currentFormTab = '{if isset($smarty.post.currentFormTab)}{$smarty.post.currentFormTab|escape:'quotes':'UTF-8'}{else}informations{/if}';
	var currentText = '{l s='Now' mod='easterhiddenobjects' js=1}';
	var closeText = '{l s='Done' mod='easterhiddenobjects' js=1}';
	{if version_compare($ps_version,'1.6','>=')}
    	var timeOnlyTitle = '{l s='Choose Time' mod='easterhiddenobjects' js=1}';
    	var timeText = '{l s='Time' mod='easterhiddenobjects' js=1}';
    	var hourText = '{l s='Hour' mod='easterhiddenobjects' js=1}';
    	var minuteText = '{l s='Minute' mod='easterhiddenobjects' js=1}';
	{/if}

	var languages = new Array();
	{foreach from=$languages item=language key=k}
		languages[{$k}] = {
			id_lang: {$language.id_lang|intval},
			iso_code: '{$language.iso_code|escape:'quotes':'UTF-8'}',
			name: '{$language.name|escape:'quotes':'UTF-8'}'
		};
	{/foreach}
	displayFlags(languages, {$id_lang_default|intval});
</script>
<div id="cart_rule_toggler">
    <div class="panel">
        <h3><i class="icon-random"></i> {l s='Conditions' mod='easterhiddenobjects'}</h3>
        <div class="form-group">
        	<label class="control-label col-lg-3">
        		<span class="label-tooltip" data-toggle="tooltip" title="{l s='The default period is one month.' mod='easterhiddenobjects'}">
        			{l s='Valid' mod='easterhiddenobjects'}
        		</span>
        	</label>
        	<div class="col-lg-9">
        		<div class="row">
        			<div class="col-lg-6">
        				<div class="input-group">
        					<input type="text" class="input-small" name="cart_rule_date_to" value="{$currentTab->getFieldValue($currentObject, 'cart_rule_date_to')|intval}" />
        					<span class="input-group-addon">{l s='days' mod='easterhiddenobjects'}</span>
        				</div>
        			</div>
        		</div>
        	</div>
        </div>

        <div class="form-group">
        	<label class="control-label col-lg-3">
        		<span class="label-tooltip" data-toggle="tooltip" title="{l s='You can choose a minimum amount for the cart either with or without the taxes and shipping.' mod='easterhiddenobjects'}">
        			{l s='Minimum amount' mod='easterhiddenobjects'}
        		</span>
        	</label>
        	<div class="col-lg-9">
        		<div class="row">
        			<div class="col-lg-3">
        				<input type="text" name="minimum_amount" value="{$currentTab->getFieldValue($currentObject, 'minimum_amount')|floatval}" />
        			</div>
        			<div class="col-lg-2">
        				<select name="minimum_amount_currency">
        				{foreach from=$currencies item='currency'}
        					<option value="{$currency.id_currency|intval}"
        					{if $currentTab->getFieldValue($currentObject, 'minimum_amount_currency') == $currency.id_currency
        						|| (!$currentTab->getFieldValue($currentObject, 'minimum_amount_currency') && $currency.id_currency == $defaultCurrency)}
        						selected="selected"
        					{/if}
        					>
        						{$currency.iso_code|escape:'html':'UTF-8'}
        					</option>
        				{/foreach}
        				</select>
        			</div>
        			<div class="col-lg-3">
        				<select name="minimum_amount_tax">
        					<option value="0" {if $currentTab->getFieldValue($currentObject, 'minimum_amount_tax') == 0}selected="selected"{/if}>{l s='Tax excluded' mod='easterhiddenobjects'}</option>
        					<option value="1" {if $currentTab->getFieldValue($currentObject, 'minimum_amount_tax') == 1}selected="selected"{/if}>{l s='Tax included' mod='easterhiddenobjects'}</option>
        				</select>
        			</div>
        			<div class="col-lg-4">
        				<select name="minimum_amount_shipping">
        					<option value="0" {if $currentTab->getFieldValue($currentObject, 'minimum_amount_shipping') == 0}selected="selected"{/if}>{l s='Shipping excluded' mod='easterhiddenobjects'}</option>
        					<option value="1" {if $currentTab->getFieldValue($currentObject, 'minimum_amount_shipping') == 1}selected="selected"{/if}>{l s='Shipping included' mod='easterhiddenobjects'}</option>
        				</select>
        			</div>
        		</div>
        	</div>
        </div>
        <p class="checkbox col-lg-offset-3">
				<label>
					<input type="checkbox" id="cart_rule_restriction" name="cart_rule_restriction" value="1" {if $currentTab->getFieldValue($currentObject, 'cart_rule_restriction')|intval}checked="checked"{/if} />
					{l s='Compatibility with other cart rules' mod='easterhiddenobjects'}
				</label>
			</p>
    </div>

    <div class="panel">
        <h3><i class="icon-wrench"></i> {l s='Actions' mod='easterhiddenobjects'}</h3>
        <div class="form-group">
        	<label class="control-label  col-lg-3">{l s='Free shipping' mod='easterhiddenobjects'}</label>
        	<div class="col-lg-9">
        		<span class="switch prestashop-switch fixed-width-lg">
        			<input type="radio" name="free_shipping" id="free_shipping_on" value="1" {if $currentTab->getFieldValue($currentObject, 'free_shipping')|intval}checked="checked"{/if} />
        			<label class="t" for="free_shipping_on">
        				{l s='Yes' mod='easterhiddenobjects'}
        			</label>
        			<input type="radio" name="free_shipping" id="free_shipping_off" value="0" {if !$currentTab->getFieldValue($currentObject, 'free_shipping')|intval}checked="checked"{/if} />
        			<label class="t" for="free_shipping_off">
        				{l s='No' mod='easterhiddenobjects'}
        			</label>
        			<a class="slide-button btn"></a>
        		</span>
        	</div>
        </div>

        <div class="form-group">
        	<label class="control-label col-lg-3">{l s='Apply a discount' mod='easterhiddenobjects'}</label>
        	<div class="col-lg-9">
        		<div class="radio">
        			<label for="apply_discount_percent">
        				<input type="radio" name="apply_discount" id="apply_discount_percent" value="percent" {if $currentTab->getFieldValue($currentObject, 'reduction_percent')|floatval > 0}checked="checked"{/if} />
        				{l s='Percent (%)' mod='easterhiddenobjects'}
        			</label>
        		</div>
        		<div class="radio">
        			<label for="apply_discount_amount">
        				<input type="radio" name="apply_discount" id="apply_discount_amount" value="amount" {if $currentTab->getFieldValue($currentObject, 'reduction_amount')|floatval > 0}checked="checked"{/if} />
        				{l s='Amount' mod='easterhiddenobjects'}
        			</label>
        		</div>
        		<div class="radio">
        			<label for="apply_discount_off">
        				<input type="radio" name="apply_discount" id="apply_discount_off" value="off" {if !$currentTab->getFieldValue($currentObject, 'reduction_amount')|floatval > 0 && !$currentTab->getFieldValue($currentObject, 'reduction_percent')|floatval > 0}checked="checked"{/if} />
        				<i class="icon-remove color_danger"></i> {l s='None' mod='easterhiddenobjects'}
        			</label>
        		</div>
        	</div>
        </div>

        <div id="apply_discount_percent_div" class="form-group">
        	<label class="control-label col-lg-3">{l s='Value' mod='easterhiddenobjects'}</label>
        	<div class="col-lg-9">
        		<div class="input-group col-lg-2">
        			<span class="input-group-addon">%</span>
        			<input type="text" id="reduction_percent" class="input-mini" name="reduction_percent" value="{$currentTab->getFieldValue($currentObject, 'reduction_percent')|floatval}" />
        		</div>
        		<span class="help-block"><i class="icon-warning-sign"></i> {l s='Does not apply to the shipping costs' mod='easterhiddenobjects'}</span>
        	</div>
        </div>

        <div id="apply_discount_amount_div" class="form-group">
        	<label class="control-label col-lg-3">{l s='Amount' mod='easterhiddenobjects'}</label>
        	<div class="col-lg-7">
        		<div class="row">
        			<div class="col-lg-4">
        				<input type="text" id="reduction_amount" name="reduction_amount" value="{$currentTab->getFieldValue($currentObject, 'reduction_amount')|floatval}" onchange="this.value = this.value.replace(/,/g, '.');" />
        			</div>
        			<div class="col-lg-4">
        				<select name="reduction_currency" >
        				{foreach from=$currencies item='currency'}
                            <option value="{$currency.id_currency|intval}" {if $currentTab->getFieldValue($currentObject, 'reduction_currency') == $currency.id_currency || (!$currentTab->getFieldValue($currentObject, 'reduction_currency') && $currency.id_currency == $defaultCurrency)}selected="selected"{/if}>{$currency.iso_code|escape:'html':'UTF-8'}</option>
                        {/foreach}
        				</select>
        			</div>
        			<div class="col-lg-4">
        				<select name="reduction_tax" >
        					<option value="0" {if $currentTab->getFieldValue($currentObject, 'reduction_tax') == 0}selected="selected"{/if}>{l s='Tax excluded' mod='easterhiddenobjects'}</option>
        					<option value="1" {if $currentTab->getFieldValue($currentObject, 'reduction_tax') == 1}selected="selected"{/if}>{l s='Tax included' mod='easterhiddenobjects'}</option>
        				</select>
        			</div>
        		</div>
        	</div>
        </div>

        <div id="apply_discount_to_div" class="form-group">
        	<label class="control-label col-lg-3">{l s='Apply a discount to' mod='easterhiddenobjects'}</label>
        	<div class="col-lg-7">
        		<p class="radio">
        			<label for="apply_discount_to_order">
        				<input type="radio" name="apply_discount_to" id="apply_discount_to_order" value="order"{if $currentTab->getFieldValue($currentObject, 'reduction_product')|intval == 0} checked="checked"{/if} />
        				 {l s='Order (without shipping)' mod='easterhiddenobjects'}
        			</label>
        		</p>
        		<p class="radio">
        			<label for="apply_discount_to_product">
        				<input type="radio" name="apply_discount_to" id="apply_discount_to_product" value="specific"{if $currentTab->getFieldValue($currentObject, 'reduction_product')|intval > 0} checked="checked"{/if} />
        				{l s='Specific product' mod='easterhiddenobjects'}
        			</label>
        		</p>
        		<p class="radio">
        			<label for="apply_discount_to_cheapest">
        				<input type="radio" name="apply_discount_to" id="apply_discount_to_cheapest" value="cheapest"{if $currentTab->getFieldValue($currentObject, 'reduction_product')|intval == -1} checked="checked"{/if} />
        				 {l s='Cheapest product' mod='easterhiddenobjects'}
        			</label>
        		</p>
        	</div>
        </div>

        <div id="apply_discount_to_product_div" class="form-group">
        	<label class="control-label col-lg-3">{l s='Product' mod='easterhiddenobjects'}</label>
        	<div class="col-lg-9">
        		<div class="input-group col-lg-5">
        			<input type="text" id="reductionProductFilter" name="reductionProductFilter" value="{$reductionProductFilter|escape:'html':'UTF-8'}" />
        			<input type="hidden" id="reduction_product" name="reduction_product" value="{$currentTab->getFieldValue($currentObject, 'reduction_product')|intval}" />
        			<span class="input-group-addon"><i class="icon-search"></i></span>
        		</div>
        	</div>
        </div>

        <div class="form-group">
        	<label class="control-label col-lg-3">{l s='Send a free gift' mod='easterhiddenobjects'}</label>
        	<div class="col-lg-9">
        		<span class="switch prestashop-switch fixed-width-lg">
        			<input type="radio" name="free_gift" id="free_gift_on" value="1" {if $currentTab->getFieldValue($currentObject, 'gift_product')|intval}checked="checked"{/if} />
        			<label class="t" for="free_gift_on">
        				{l s='Yes' mod='easterhiddenobjects'}
        			</label>
        			<input type="radio" name="free_gift" id="free_gift_off" value="0" {if !$currentTab->getFieldValue($currentObject, 'gift_product')|intval}checked="checked"{/if} />
        			<label class="t" for="free_gift_off">
        				{l s='No' mod='easterhiddenobjects'}
        			</label>
        			<a class="slide-button btn"></a>
        		</span>
        	</div>
        </div>

        <div id="free_gift_div" class="form-group">
        	<label class="control-label col-lg-3">{l s='Search a product' mod='easterhiddenobjects'}</label>
        	<div class="col-lg-9">
        		<div class="input-group col-lg-5">
        			<input type="text" id="giftProductFilter" value="{$giftProductFilter|escape:'html':'UTF-8'}" />
        			<span class="input-group-addon"><i class="icon-search"></i></span>
        		</div>
        	</div>
        </div>

        <div id="gift_products_found" {if $gift_product_select == ''}style="display:none"{/if}>
        	<div id="gift_product_list" class="form-group">
        		<label class="control-label col-lg-3">{l s='Matching products' mod='easterhiddenobjects'}</label>
        		<div class="col-lg-5">
        			<select name="gift_product" id="gift_product" onclick="displayProductAttributes();" class="control-form">
        				{$gift_product_select} {* HTML ouput, no escape necessary *}
        			</select>
        		</div>
        	</div>
        	<div id="gift_attributes_list" class="form-group">
        		<label class="control-label col-lg-3">{l s='Available combinations' mod='easterhiddenobjects'}</label>
        		<div class="col-lg-5" id="gift_attributes_list_select">
        			{$gift_product_attribute_select} {* HTML ouput, no escape necessary *}
        		</div>
        	</div>
        </div>
        <div id="gift_products_err" class="alert alert-warning" style="display:none"></div>
    </div>
</div>