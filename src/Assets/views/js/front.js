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

jQuery.fn.random = function() {
    var randomIndex = Math.floor(Math.random() * this.length);
    return jQuery(this[randomIndex]);
};

function displayHiddenObject(hiddenObjectSelector, hiddenObjectUrl, hiddenObjectSize, hiddenObjectLink, hiddenObjectUseEffect) {
    const available_positions = new Array( 'lt', 'lb', 'tr', 'tb' );

    const available_elements = $(hiddenObjectSelector);
    const selected_element = available_elements.random();

    if (selected_element.css('position') == 'static') {
        selected_element.css('position', 'relative');
    }

    let icon = $('<img class="hidden-object-icon" src="'+hiddenObjectUrl+'" width="'+hiddenObjectSize+'" height="'+hiddenObjectSize+'" />');
    const position = available_positions[Math.floor(Math.random()*available_positions.length)];

    const selected_element_wrapper = $('<div class="hidden-object-icon-wrapper"></div>');
    selected_element.wrap(selected_element_wrapper);

    icon.insertAfter(selected_element);

    let inset =  '0';
    switch(position) {
        case 'lt' : inset = '0 auto 0 auto'; break;
        case 'lb' : inset = '0 auto auto auto'; break;
        case 'br' : inset = 'auto auto auto 0'; break;
        case 'bl' : inset = 'auto 0 auto auto'; break;
    }
    icon.css({'inset' : inset, 'display' : 'block'});

    if (hiddenObjectUseEffect) {
        icon.addClass('hidden-icon-animated hidden-icon-infinite hidden-icon-tada');
    }

    icon.on('click', function(event) {
        event.stopPropagation();
        $.fancybox({
            href : hiddenObjectLink,
            type: 'ajax',
            minHeight : 0,
            tpl : {
                wrap : '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner fancybox-hiddenobjects"></div></div></div></div>',
                error : '<div class="alert alert-danger">'+hiddenObjectFancyboxError+'</div>',
            },
            afterClose: function() {
                icon.parent().addClass('disabled');
                icon.remove();
            }
        });
        return false;
    });
}