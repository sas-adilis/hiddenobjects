<?php
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
function upgrade_module_1_1()
{
    $sql = [];
    $sql[]
        = 'ALTER TABLE `' . _DB_PREFIX_ . 'hiddenobjects_ea_founded`
        ADD `is_test` BOOL(1) NOT NULL DEFAULT "0" AFTER `ip_address`;';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return true;
}
