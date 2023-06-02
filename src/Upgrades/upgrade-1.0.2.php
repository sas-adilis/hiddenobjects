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
function upgrade_module_1_0_2($module)
{
    $sql = [];

    $indexes = Db::getInstance()->ExecuteS(
        'SHOW INDEX FROM `' . _DB_PREFIX_ . 'hiddenobjects_' . $module->getPrefix() . '_founded`
        WHERE `column_name` = "ip_address"'
    );
    foreach ($indexes as $index) {
        if ($index['Key_name'] == 'id_hiddenobject') {
            $sql[] = 'ALTER TABLE ' . _DB_PREFIX_ . 'hiddenobjects_' . $module->getPrefix() . '_founded DROP INDEX id_hiddenobject;';
        }
        if ($index['Key_name'] == 'id_hiddenobject_2') {
            $sql[] = 'ALTER TABLE ' . _DB_PREFIX_ . 'hiddenobjects_' . $module->getPrefix() . '_founded DROP INDEX id_hiddenobject_2;';
        }
    }

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return true;
}
