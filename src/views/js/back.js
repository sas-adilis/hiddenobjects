/**
 * 2016 Adilis
 *
 * Make your shop interactive for Easter: hide objects and ask your customers to find them in order to win a
 * discount coupon. Make your brand stand out by offering an original game: a treasure hunt throughout your products.
 *
 *  @author    Adilis <support@adilis.fr>
 *  @copyright 2016 SAS Adilis
 *  @license   http://www.adilis.fr
 */

 versionCompare = function(left, right) {
    if (typeof left + typeof right != 'stringstring')
        return false;

    var a = left.split('.')
    ,   b = right.split('.')
    ,   i = 0, len = Math.max(a.length, b.length);

    for (; i < len; i++) {
        if ((a[i] && !b[i] && parseInt(a[i]) > 0) || (parseInt(a[i]) > parseInt(b[i]))) {
            return 1;
        } else if ((b[i] && !a[i] && parseInt(b[i]) > 0) || (parseInt(a[i]) < parseInt(b[i]))) {
            return -1;
        }
    }

    return 0;
}
var gift_product_search;


$(document).ready( function() {

	// Just to be cute
	$('#content form legend img').hide();

	if( $('#content form').length && typeof(hcContinueTyping)!='undefined' )
	{
    	if(versionCompare( _PS_VERSION_, '1.6' ) == -1) {
        	console.log($('#icon_selection, #cart_rule_toggler').closest('.margin-form'));
        	$('#icon_selection, #cart_rule_toggler').closest('.margin-form').removeClass('margin-form');
    	}

    	var iconDemonstration = $('#icon-demonstration');

		$("#only_products").ajaxChosen({
		    type: 'GET',
		    width: '90%',
		    keepTypingMsg: hcContinueTyping+" ...",
			lookingForMsg: hcLookingFor,
		    url: currentIndex+'&token='+token+'&ajax=1&action=searchProduct',
		    dataType: 'json'
		}, function (data) {
		    var results = [];
		    $.each(data, function (i, val) {
		        results.push({ value: val.id, text: val.name  });
		    });
			return results;
		},
		{
		    placeholder_text_multiple : hcStartTyping,
			no_results_text: hcNoResultFoundFor
		});

		//$("#only_products option").removeAttr('selected');

		$("#only_cms").ajaxChosen({
		    type: 'GET',
		    keepTypingMsg: hcContinueTyping+" ...",
			lookingForMsg: hcLookingFor,
		    url: currentIndex+'&token='+token+'&ajax=1&action=searchCms',
		    dataType: 'json'
		}, function (data) {
		    var results = [];
		    $.each(data, function (i, val) {
		        results.push({ value: val.id, text: val.name  });
		    });
			return results;
		},
		{
		    placeholder_text_multiple : hcStartTyping,
			 no_results_text: hcNoResultFoundFor
		});
		$("#only_cms option").removeAttr('selected');

		var restriction_siblings = versionCompare( _PS_VERSION_, '1.6' ) == -1 ? $('#restriction').parents('.margin-form').nextAll('.margin-form, label') :  $('#restriction').parents('.form-group').nextAll('.form-group');
		$('#restriction').on('change', function () {
			restriction_siblings.hide();
			switch( $(this).val() ) {
				case 'categories' :
				case 'categories_and_products' :
					if( versionCompare( _PS_VERSION_, '1.6' ) == -1 ) {
						$('#categories-treeview').parents('.margin-form').show();
						$('#categories-treeview').parents('.margin-form').prev().show();
					} else
						$('#only_categories').parents('.form-group').show();
					break;
				case 'products' :
					if( versionCompare( _PS_VERSION_, '1.6' ) == -1 ) {
						$('#only_products').parents('.margin-form').show();
						$('#only_products').parents('.margin-form').prev().show();
					} else
						$('#only_products').parents('.form-group').show();
					break;
				case 'cms' :
					if( versionCompare( _PS_VERSION_, '1.6' ) == -1 ) {
						$('#only_cms').parents('.margin-form').show();
						$('#only_cms').parents('.margin-form').prev().show();
					} else
						$('#only_cms').parents('.form-group').show();
					break;
				default:
			}
		}).trigger('change');


		$("#restriction").chosen({
			 no_results_text: hcNoResultFoundFor,
			 search_contains : true,
			 width: "90%"
		});

		$("#size").on('change', function() {
			 iconDemonstration.css({
    			 'width' : parseInt($(this).val()),
    			 'height' : parseInt($(this).val()),
			 });
		}).trigger('change');

        $('input[type=radio][name=icon]').change(function() {
           iconDemonstration.attr('src', $(this).data('icon-src'));
        });

        $('input[type=radio][name=use_effect]').change(function() {
            if(parseInt(this.value)==1)
                iconDemonstration.addClass('animated infinite tada');
            else
                iconDemonstration.removeClass('animated infinite tada');
        });

        $('input[type=radio][name=use_custom_cart_rule]').change(function() {
            if(this.value==1) {
                $('#cart_rule_toggler').hide();
                if( versionCompare( _PS_VERSION_, '1.6' ) == -1 ) {
    			    $('#custom_cart_rule_code').closest('.margin-form').prev('label').removeClass('hidden');
    			    $('#custom_cart_rule_code').closest('.margin-form').removeClass('hidden');
        		} else
        		{
    			    $('#custom_cart_rule_code').closest('.form-group').removeClass('hidden');
        		}
            }
            else {
                $('#cart_rule_toggler').show();
                if( versionCompare( _PS_VERSION_, '1.6' ) == -1 ) {
    			    $('#custom_cart_rule_code').closest('.margin-form').prev('label').addClass('hidden');
    			    $('#custom_cart_rule_code').closest('.margin-form').addClass('hidden');
        		} else
        		{
    			    $('#custom_cart_rule_code').closest('.form-group').addClass('hidden');
        		}
            }
        }).trigger('change');




$('#apply_discount_percent').click(function(){
	toggleApplyDiscount(true, false, true);
});
if ($('#apply_discount_percent').prop('checked'))
	toggleApplyDiscount(true, false, true);

$('#apply_discount_amount').click(function(){
	toggleApplyDiscount(false, true, true);
});
if ($('#apply_discount_amount').prop('checked'))
	toggleApplyDiscount(false, true, true);

$('#apply_discount_off').click(function(){
	toggleApplyDiscount(false, false, false);
});
if ($('#apply_discount_off').prop('checked'))
	toggleApplyDiscount(false, false, false);

$('#apply_discount_to_order').click(function(){
	toggleApplyDiscountTo();}
);
if ($('#apply_discount_to_order').prop('checked'))
	toggleApplyDiscountTo();

$('#apply_discount_to_product').click(function(){
	toggleApplyDiscountTo();}
);
if ($('#apply_discount_to_product').prop('checked'))
	toggleApplyDiscountTo();

$('#apply_discount_to_cheapest').click(function(){
	toggleApplyDiscountTo();}
);
if ($('#apply_discount_to_cheapest').prop('checked'))
	toggleApplyDiscountTo();

$('#free_gift_on').click(function(){
	toggleGiftProduct();}
);
$('#free_gift_off').click(function(){
	toggleGiftProduct();}
);
toggleGiftProduct();


$('#reductionProductFilter')
	.autocomplete(
			'ajax-tab.php', {
			minChars: 2,
			max: 50,
			width: 500,
			selectFirst: false,
			scroll: false,
			dataType: 'json',
			formatItem: function(data, i, max, value, term) {
				return value;
			},
			parse: function(data) {
				var mytab = new Array();
				for (var i = 0; i < data.length; i++)
					mytab[mytab.length] = { data: data[i], value: (data[i].reference + ' ' + data[i].name).trim() };
				return mytab;
			},
			extraParams: {
				controller: 'AdminCartRules',
				token: cartRulesToken,
				reductionProductFilter: 1
			}
		}
	)
	.result(function(event, data, formatted) {
		$('#reduction_product').val(data.id_product);
		$('#reductionProductFilter').val((data.reference + ' ' + data.name).trim());
	});

var date = new Date();
var hours = date.getHours();
if (hours < 10)
	hours = "0" + hours;
var mins = date.getMinutes();
if (mins < 10)
	mins = "0" + mins;
var secs = date.getSeconds();
if (secs < 10)
	secs = "0" + secs;

/*if(versionCompare( _PS_VERSION_, '1.6' ) == -1) {
    $('.datepicker').datetimepicker({
    	beforeShow: function (input, inst) {
            setTimeout(function () {
                inst.dpDiv.css({
                    'z-index': 1031
                });
            }, 0);
        },
    	prevText: '',
    	nextText: '',
    	dateFormat: 'yy-mm-dd',
    	// Define a custom regional settings in order to use PrestaShop translation tools
    	currentText: currentText,
    	closeText:closeText,
    	ampm: false,
    	amNames: ['AM', 'A'],
    	pmNames: ['PM', 'P']
    });    $('.datepicker').datetimepicker({
    	beforeShow: function (input, inst) {
            setTimeout(function () {
                inst.dpDiv.css({
                    'z-index': 1031
                });
            }, 0);
        },
    	prevText: '',
    	nextText: '',
    	dateFormat: 'yy-mm-dd',
    	// Define a custom regional settings in order to use PrestaShop translation tools
    	currentText: currentText,
    	closeText:closeText,
    	ampm: false,
    	amNames: ['AM', 'A'],
    	pmNames: ['PM', 'P'],
    	timeFormat: 'hh:mm:ss tt',
    	timeSuffix: '',
    	timeOnlyTitle: timeOnlyTitle,
    	timeText: timeText,
    	hourText: hourText,
    	minuteText: minuteText,
    });
} else {

}*/

$('#giftProductFilter').typeWatch({
	captureLength: 2,
	highlight: false,
	wait: 100,
	callback: function(){ searchProducts(); }
});

gift_product_search = $('#giftProductFilter').val();

	}

displayProductAttributes();
});


function searchProducts()
{
	if ($('#giftProductFilter').val() == gift_product_search)
		return;
	gift_product_search = $('#giftProductFilter').val();

	$.ajax({
		type: 'POST',
		headers: { "cache-control": "no-cache" },
		url: 'ajax-tab.php' + '?rand=' + new Date().getTime(),
		async: true,
		dataType: 'json',
		data: {
			controller: 'AdminCartRules',
			token: cartRulesToken,
			action: 'searchProducts',
			product_search: $('#giftProductFilter').val()
		},
		success : function(res)
		{
			var products_found = '';
			var attributes_html = '';
			stock = {};

			if (res.found)
			{
				$('#gift_products_err').hide();
				$('#gift_products_found').show();
				$.each(res.products, function() {
					products_found += '<option value="' + this.id_product + '">' + this.name + (this.combinations.length == 0 ? ' - ' + this.formatted_price : '') + '</option>';

					attributes_html += '<select class="id_product_attribute" id="ipa_' + this.id_product + '" name="ipa_' + this.id_product + '" style="display:none">';
					$.each(this.combinations, function() {
						attributes_html += '<option ' + (this.default_on == 1 ? 'selected="selected"' : '') + ' value="' + this.id_product_attribute + '">' + this.attributes + ' - ' + this.formatted_price + '</option>';
					});
					attributes_html += '</select>';
				});

				$('#gift_product_list #gift_product').html(products_found);
				$('#gift_attributes_list #gift_attributes_list_select').html(attributes_html);
				displayProductAttributes();
			}
			else
			{
				$('#products_found').hide();
				$('#products_err').html(res.notfound);
				$('#products_err').show();
			}
		}
	});
}


function displayProductAttributes()
{
	if ($('#ipa_' + $('#gift_product option:selected').val() + ' option').length === 0)
		$('#gift_attributes_list').hide();
	else
	{
		$('#gift_attributes_list').show();
		$('.id_product_attribute').hide();
		$('#ipa_' + $('#gift_product option:selected').val()).show();
	}
}

	function toggleApplyDiscount(percent, amount, apply_to)
{
	if (percent)
	{
		$('#apply_discount_percent_div').show(400);
		if ($('#apply_discount_to_product').prop('checked'))
			toggleApplyDiscountTo();
		$('#apply_discount_to_cheapest').show();
		$('*[for=apply_discount_to_cheapest]').show();
	}
	else
	{
		$('#apply_discount_percent_div').hide(200);
		$('#reduction_percent').val('0');
	}

	if (amount)
	{
		$('#apply_discount_amount_div').show(400);
		if ($('#apply_discount_to_product').prop('checked'))
			toggleApplyDiscountTo();
		$('#apply_discount_to_cheapest').hide();
		$('*[for=apply_discount_to_cheapest]').hide();
		$('#apply_discount_to_cheapest').prop('checked', false);
	}
	else
	{
		$('#apply_discount_amount_div').hide(200);
		$('#reduction_amount').val('0');

		if ($('#apply_discount_off').prop('checked'))
		{
			$('#apply_discount_to_product').prop('checked', false)
			toggleApplyDiscountTo();
		}
	}

	if (apply_to)
		$('#apply_discount_to_div').show(400);
	else
	{
		toggleApplyDiscountTo();
		$('#apply_discount_to_div').hide(200);
	}
}

function toggleApplyDiscountTo()
{
	if ($('#apply_discount_to_product').prop('checked'))
		$('#apply_discount_to_product_div').show(400);
	else
	{
		$('#apply_discount_to_product_div').hide(200);
		$('#reductionProductFilter').val('');
		if ($('#apply_discount_to_order').prop('checked'))
			$('#reduction_product').val('0');
		if ($('#apply_discount_to_cheapest').prop('checked'))
			$('#reduction_product').val('-1');
	}
}

function toggleGiftProduct()
{
	if ($('#free_gift_on').prop('checked'))
		$('#free_gift_div').show(400);
	else
	{
		$('#gift_product').val('0');
		$('#giftProductFilter').val('');
		$('#free_gift_div, #gift_products_found').hide(200);
	}
}