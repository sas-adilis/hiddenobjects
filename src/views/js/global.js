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


$(document).ready( function() {
    $('.hiddenobject_ea_fancybox').fancybox();
    var cartFoundedObjects = $('.display_hiddenobject_vouchers');
    if(cartFoundedObjects.length) {
        if($('.order_delivery').length)
            $('.order_delivery').before(cartFoundedObjects);
        cartFoundedObjects.show();
    }
});