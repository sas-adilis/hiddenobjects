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

<script type='text/javascript'>
    var eaHiddenObjectUrl= '{$object.icon_url|escape:'quotes':'UTF-8'}';
    var eaHiddenObjectLink= '{$object.icon_link|escape:'quotes':'UTF-8'}';
    var eaHiddenObjectSize= {$object.size|intval};
    var eaHiddenObjectUseEffect= {$object.use_effect|intval};
    var eaHiddenObjectDisplay= '{$display|escape:'quotes':'UTF-8'}';
    var eaHiddenObjectSelector= '{$selector|escape:'quotes':'UTF-8'}';
    var eaFancyboxError= '{l s='An error occured. Please contact us if necessary at %s.' mod='easterhiddenobjects' sprintf=$email_support|escape:'quotes':'UTF-8'}';
</script>