<?php

/** @noinspection SpellCheckingInspection */
/** @noinspection PhpMissingFieldTypeInspection */

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

namespace Adilis\HiddenObjects\Classes;

class HiddenObject extends \ObjectModel
{
    public $id_shop;
    public $name;
    public $message_end;
    public $date_start;
    public $date_end;
    public $how_many = 1;
    public $renew = 'none';
    public $active = 0;
    public $icon = 1;
    public $size = 48;
    public $use_effect = false;
    public $appear_ratio = 10;
    public $restriction;
    public $restriction_value = [];
    public $use_custom_cart_rule = false;
    public $custom_cart_rule_code;
    public $cart_rule_date_to = 7;
    public $minimum_amount;
    public $minimum_amount_tax;
    public $minimum_amount_currency;
    public $minimum_amount_shipping;
    public $cart_rule_restriction = 0;
    public $free_shipping = false;
    public $reduction_percent;
    public $reduction_amount;
    public $reduction_tax;
    public $reduction_currency;
    public $reduction_product;
    public $gift_product;
    public $gift_product_attribute;
    public $date_add;
    public $date_upd;
    private $images = [];

    public static $definition = [
        'primary' => 'id_hiddenobject',
        'multishop' => false,
        'multilang' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'lang' => true, 'required' => true, 'size' => 128],
            'message_end' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true],
            'date_start' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_end' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'how_many' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true, 'length' => 3],
            'renew' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'length' => 25],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'icon' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true, 'length' => 3],
            'size' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true, 'length' => 3],
            'use_effect' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'appear_ratio' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true, 'length' => 6],
            'restriction' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 64],
            'restriction_value' => ['type' => self::TYPE_STRING],
            'use_custom_cart_rule' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'custom_cart_rule_code' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 254],
            'cart_rule_date_to' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true, 'size' => 6],
            'minimum_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'minimum_amount_tax' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'minimum_amount_currency' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'minimum_amount_shipping' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'cart_rule_restriction' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'free_shipping' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'reduction_percent' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPercentage'],
            'reduction_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'reduction_tax' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'reduction_currency' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'reduction_product' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'gift_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'gift_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ],
    ];

    public function __construct($id = null, $id_lang = null)
    {
        self::$definition['table'] = $this->getTable();
        parent::__construct($id);

        if ((int) $this->id) {
            $this->restriction_value = json_decode($this->restriction_value, true);
        }
    }

    public function add($auto_date = true, $null_values = true)
    {
        $this->restriction_value = json_encode($this->restriction_value);

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        $this->restriction_value = json_encode($this->restriction_value);

        return parent::update($null_values);
    }

    public function isFoundable(): bool
    {
        $context = \Context::getContext();
        $query = new \DbQuery();
        $query->select('a.id_hiddenobject');
        $query->from($this->getTable(), 'a');
        $query->where('a.id_hiddenobject = ' . (int) $this->id);
        $query->where('a.active = 1');
        $query->where('a.id_shop = ' . (int) $context->shop->id);
        $query->where(HOTools::buildOrWhere(['NOW() BETWEEN date_start AND date_end', 'date_start = date_end']));

        if (!HOTools::isInMaintenance($this->getPrefix())) {
            $current_remote_address = ip2long(\Tools::getRemoteAddr());
            $orWhere = ['b.ip_address="' . pSQL($current_remote_address) . '"'];
            if (\Validate::isLoadedObject($context->customer) && (int) $context->customer->id) {
                $orWhere[] = 'b.id_customer = ' . (int) $context->customer->id;
            }
            if ((int) $context->cookie->id_guest) {
                $orWhere[] = 'b.id_guest = ' . (int) $context->cookie->id_guest;
            }

            $query->leftJoin(
                $this->getTable() . '_founded',
                'b',
                'b.id_hiddenobject = a.id_hiddenobject AND b.is_test = 0 AND ' . HOTools::buildOrWhere($orWhere)
            );

            $query->where('b.id_hiddenobject IS NULL');
            $query->where('(' . $this->getRenewQuery() . ') < a.how_many');
        }

        return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query->build()) > 0;
    }

    private function getRenewQuery()
    {
        $query = new \DbQuery();
        $query->select('COUNT(id_hiddenobject)');
        $query->from($this->getTable() . '_founded', 'c');
        $query->where('c.id_hiddenobject = a.id_hiddenobject');
        $query->where('c.is_test = 0');
        $query->where('c.date > CASE
            WHEN a.renew = "daily" THEN DATE_SUB(NOW(), INTERVAL 1 DAY)
            WHEN a.renew = "weekly" THEN DATE_SUB(NOW(), INTERVAL 1 WEEK)
            WHEN a.renew = "monthly" THEN DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ELSE 0000-00-00
            END'
        );

        return $query->build();
    }
}
