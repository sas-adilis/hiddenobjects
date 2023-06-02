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

namespace Adilis\HiddenObjects\Sql;

class TableInstaller
{
    private $table = '';

    public function setTable($table): TableInstaller
    {
        $this->table = $table;

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function install(): bool
    {
        if ($this->getTable() == '') {
            return false;
        }

        $sql = [];
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->getTable() . '` (
            `id_hiddenobject` int(11) NOT NULL AUTO_INCREMENT,
            `id_shop` int(11) unsigned NOT NULL,
            `date_start` datetime NOT NULL,
            `date_end` datetime NOT NULL,
            `how_many` int(3) unsigned NOT NULL DEFAULT \'1\',
            `renew` enum(\'none\',\'daily\',\'weekly\') NOT NULL DEFAULT \'none\',
            `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
            `icon` int(3) NOT NULL DEFAULT \'1\',
            `size` int(3) unsigned NOT NULL DEFAULT \'48\',
            `use_effect` tinyint(1) NOT NULL DEFAULT \'0\',
            `appear_ratio` int(3) NOT NULL DEFAULT \'10\',
            `restriction` enum(
                \'none\',
                \'homepage\',
                \'categories\',
                \'categories_and_products\',
                \'products\',
                \'cms\'
            ) DEFAULT \'none\',
            `restriction_value` text,
            `use_custom_cart_rule` tinyint(1) NOT NULL DEFAULT \'0\',
            `custom_cart_rule_code` varchar(254) DEFAULT NULL,
            `cart_rule_date_to` int(6) NOT NULL DEFAULT \'7\',
            `minimum_amount` decimal(17,2) NOT NULL DEFAULT \'0.00\',
            `minimum_amount_tax` tinyint(1) NOT NULL DEFAULT \'0\',
            `minimum_amount_currency` int(10) unsigned NOT NULL DEFAULT \'0\',
            `minimum_amount_shipping` tinyint(1) NOT NULL DEFAULT \'0\',
            `cart_rule_restriction` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
            `free_shipping` tinyint(1) NOT NULL DEFAULT \'0\',
            `reduction_percent` decimal(5,2) NOT NULL DEFAULT \'0.00\',
            `reduction_amount` decimal(17,2) NOT NULL DEFAULT \'0.00\',
            `reduction_tax` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
            `reduction_currency` int(10) unsigned NOT NULL DEFAULT \'0\',
            `reduction_product` int(10) NOT NULL DEFAULT \'0\',
            `gift_product` int(10) unsigned NOT NULL DEFAULT \'0\',
            `gift_product_attribute` int(10) unsigned NOT NULL DEFAULT \'0\',
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_hiddenobject`),
            KEY `id_shop` (`id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->getTable() . '_founded` (
          `id_hiddenobject_founded` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `id_hiddenobject` int(11) NOT NULL,
          `id_guest` int(11) unsigned NOT NULL,
          `id_customer` int(11) unsigned NOT NULL,
          `id_cart_rule` int(11) unsigned NOT NULL,
          `ip_address` bigint(20) DEFAULT NULL,
          `is_test` tinyint(1) NOT NULL DEFAULT \'0\',
          `date` datetime NOT NULL,
          PRIMARY KEY (`id_hiddenobject_founded`),
          KEY `id_hiddenobject` (`id_hiddenobject`),
          KEY `id_guest` (`id_guest`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->getTable() . '_lang` (
          `id_hiddenobject` int(10) unsigned NOT NULL,
          `id_lang` int(10) unsigned NOT NULL,
          `name` varchar(128) NOT NULL DEFAULT \'\',
          `message_end` text,
          PRIMARY KEY (`id_hiddenobject`,`id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($sql as $query) {
            if (!\Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }
}
