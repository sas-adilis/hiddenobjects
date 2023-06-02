<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

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

namespace Adilis\HiddenObjects\Controllers\Front;

use Adilis\HiddenObjects\Classes\HiddenObject;
use Adilis\HiddenObjects\Classes\HOTools;
use Adilis\HiddenObjects\Classes\ModuleHiddenObjects;

/**
 * @property false $display_header
 * @property false $display_footer
 */
class HiddenObjectsFoundModuleFrontController extends \ModuleFrontController
{
    public $id_cart_rule;
    public $cart_rule_code;
    /** @var HiddenObject */
    private $hiddenobject;
    /** @var ModuleHiddenObjects */
    public $module;

    public function init()
    {
        $this->display_header = false;
        $this->display_footer = false;

        $founded_token = \Tools::getValue('token');
        $id_hiddenobject = (int) \Tools::getValue('id');

        $token = \Tools::encrypt((int) $id_hiddenobject . '|' . date('YmdH') . '|' . $this->module->id);
        if (
            $token != $founded_token
            || !$id_hiddenobject
        ) {
            $this->redirect_after = '404';
            $this->redirect();
        }

        $class_name = $this->module->getClassName();
        $this->hiddenobject = new $class_name($id_hiddenobject);
        if (!\Validate::isLoadedObject($this->hiddenobject)) {
            $this->redirect_after = '404';
            $this->redirect();
        }

        if (!HOTools::isInMaintenance($this->module->getPrefix()) && !$this->hiddenobject->isFoundable()) {
            $this->redirect_after = '404';
            $this->redirect();
        }
    }

    /**
     * @throws \PrestaShopException
     */
    public function initContent()
    {
        if ($this->hiddenobject->use_custom_cart_rule) {
            $this->cart_rule_code = $this->hiddenobject->custom_cart_rule_code;
            $this->id_cart_rule = (int) \CartRule::getIdByCode($this->hiddenobject->custom_cart_rule_code);
        } else {
            $cart_rule_code = $this->module->getPrefix() . 'HO' . $this->hiddenobject->id . '-' . date('ymdHi') . \Tools::passwdGen(3);
            $this->cart_rule_code = \Tools::strtoupper($cart_rule_code);
            $this->id_cart_rule = (int) $this->generateCartRule();
        }

        if (!$this->id_cart_rule || !$this->processSaveResult()) {
            throw new \PrestaShopException($this->l('Can not create carte rule and save result'));
        }

        $icon_url = $this->module->generateIconSize(64, $this->hiddenobject->icon);

        if ($this->hiddenobject->message_end) {
            $this->hiddenobject->message_end = str_replace(
                '{code}',
                $this->cart_rule_code,
                $this->hiddenobject->message_end[$this->context->cookie->id_lang]
            );
        }

        $this->context->smarty->assign([
            'is_in_maintenance' => HOTools::isInMaintenance($this->module->getPrefix()),
            'cart_rule_code' => $this->cart_rule_code,
            'icon_url' => $icon_url,
            'hiddenobject' => $this->hiddenobject,
        ]);

        echo $this->context->smarty->fetch($this->getTemplate('founded.tpl'));
        exit;
    }

    public function processSaveResult(): bool
    {
        $datas_to_insert = [
            'id_hiddenobject' => (int) $this->hiddenobject->id,
            'id_guest' => (int) $this->context->cookie->id_guest,
            'id_cart_rule' => (int) $this->id_cart_rule,
            'ip_address' => \Tools::getRemoteAddr() ? ip2long(\Tools::getRemoteAddr()) : '',
            'is_test' => HOTools::isInMaintenance($this->module->getPrefix()) ? 1 : 0,
            'date' => date('Y-m-d H:i:s'),
        ];

        if (isset($this->context->customer) && \Validate::isLoadedObject($this->context->customer)) {
            $datas_to_insert['id_customer'] = (int) $this->context->customer->id;
        }

        return \Db::getInstance()->insert(
            'hiddenobjects_' . \Tools::strtolower($this->module->getPrefix()) . '_founded',
            $datas_to_insert
        );
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function generateCartRule(): bool
    {
        $cart_rule = new \CartRule();
        if (isset($this->context->customer) && \Validate::isLoadedObject($this->context->customer)) {
            $cart_rule->id_customer = (int) $this->context->customer->id;
        }
        $cart_rule->date_from = date('Y-m-d H:i:s');
        $cart_rule->date_to = date('Y-m-d H:i:s', strtotime('+' . (int) $this->hiddenobject->cart_rule_date_to . ' days'));
        $cart_rule->name = $this->hiddenobject->name;
        $cart_rule->code = $this->cart_rule_code;
        $cart_rule->minimum_amount = (float) $this->hiddenobject->minimum_amount;
        $cart_rule->minimum_amount_tax = (bool) $this->hiddenobject->minimum_amount_tax;
        $cart_rule->minimum_amount_currency = (int) $this->hiddenobject->minimum_amount_currency;
        $cart_rule->minimum_amount_shipping = (bool) $this->hiddenobject->minimum_amount_shipping;
        $cart_rule->cart_rule_restriction = (bool) $this->hiddenobject->cart_rule_restriction;
        $cart_rule->free_shipping = (bool) $this->hiddenobject->free_shipping;
        $cart_rule->reduction_percent = (float) $this->hiddenobject->reduction_percent;
        $cart_rule->reduction_amount = (float) $this->hiddenobject->reduction_amount;
        $cart_rule->reduction_tax = (bool) $this->hiddenobject->reduction_tax;
        $cart_rule->reduction_currency = (int) $this->hiddenobject->reduction_currency;
        $cart_rule->reduction_product = (int) $this->hiddenobject->reduction_product;
        $cart_rule->gift_product = (int) $this->hiddenobject->gift_product;
        $cart_rule->gift_product_attribute = (int) $this->hiddenobject->gift_product_attribute;

        if ($cart_rule->add()) {
            $this->saveCartRuleRestrictions((int) $cart_rule->id);

            return $cart_rule->id;
        }

        return false;
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    protected function saveCartRuleRestrictions($id_cart_rule)
    {
        if ((bool) $this->hiddenobject->cart_rule_restriction && (int) $id_cart_rule) {
            $query = new \DbQuery();
            $query->select('cr.id_cart_rule');
            $query->from('cart_rule', 'cr');
            $query->where('cr.date_to < "' . pSQL($this->hiddenobject->cart_rule_date_to) . '"');
            $query->where('cr.date_from < "' . pSQL($this->hiddenobject->cart_rule_date_to) . '"');
            $query->where('cr.quantity > 0');
            $query->where('cr.active = 1');
            $cart_rules_to_combine = \Db::getInstance()->executeS($query);

            if (!count($cart_rules_to_combine)) {
                return;
            }

            $values = [];
            foreach ($cart_rules_to_combine as $cart_rule_to_combine) {
                $values[] = [
                    'id_cart_rule_1' => (int) $id_cart_rule,
                    'id_cart_rule_2' => (int) $cart_rule_to_combine['id_cart_rule'],
                ];
            }
            \Db::getInstance()->insert('cart_rule_combination', $values);
        }
    }

    protected function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true): string
    {
        return $this->module->l($string, 'view', $class, $addslashes, $htmlentities);
    }

    public function getTemplate($template)
    {
        if (\Tools::file_exists_cache(_PS_THEME_DIR_ . 'modules/' . $this->module->name . '/' . $template)) {
            return _PS_THEME_DIR_ . 'modules/' . $this->module->name . '/' . $template;
        } elseif (
            \Tools::file_exists_cache(_PS_THEME_DIR_ . 'modules/' . $this->module->name . '/views/templates/front/' . $template)
        ) {
            return _PS_THEME_DIR_ . 'modules/' . $this->module->name . '/views/templates/front/' . $template;
        } elseif (\Tools::file_exists_cache(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/front/' . $template)) {
            return _PS_MODULE_DIR_ . $this->module->name . '/views/templates/front/' . $template;
        }

        return false;
    }
}
