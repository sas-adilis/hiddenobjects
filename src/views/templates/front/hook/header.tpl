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

{addJsDef eaHiddenObjectUrl=$object.icon_url|escape:'quotes':'UTF-8'}
{addJsDef eaHiddenObjectLink=$object.icon_link|escape:'quotes':'UTF-8'}
{addJsDef eaHiddenObjectSize=$object.size|intval}
{addJsDef eaHiddenObjectUseEffect=$object.use_effect|intval}
{addJsDef eaHiddenObjectDisplay=$display|escape:'quotes':'UTF-8'}
{addJsDef eaHiddenObjectSelector=$selector|escape:'quotes':'UTF-8'}
{addJsDef eaFancyboxError={l s='An error occured. Please contact us if necessary at %s.' sprintf=$email_support mod='easterhiddenobjects'}|escape:'quotes':'UTF-8'}