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
    $('.display_hiddenobject_vouchers_item').on('click', function(event) {
        let voucherForm = $('form[data-link-action="add-voucher"]');
        if (voucherForm.length == 0) {
            voucherForm = $('form#voucher');
        }
        if (voucherForm.length > 0) {
            const code = $(this).data('code');
            voucherForm.find('input[type="text"]').val(code);
            voucherForm.submit();
        }
    });
});