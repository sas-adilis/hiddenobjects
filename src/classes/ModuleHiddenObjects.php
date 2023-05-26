<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

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

/**
 * @property $module_name
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class ModuleHiddenObjects extends \Module implements ModuleHiddenObjectsInterface
{
    public static $contests = false;
    public $is_in_maintenance = '';
    private $controller_name;
    public $class_name;

    private $icons_path;
    private $icons_dir;
    private $uploads_dir;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->controller_name = 'Admin' . $this->getPrefix() . 'HiddenObjects';
        $this->class_name = $this->getPrefix() . 'HiddenObjects';
        $this->version = '1.1.1';
        $this->need_instance = 0;
        $this->bootstrap = true;

        /*if (!class_exists('HiddenObject')) {
            require_once _PS_MODULE_DIR_ . $this->module_name . '/classes/HiddenObject.php';
        }
        require_once _PS_MODULE_DIR_ . $this->module_name . '/classes/' . $this->prefix . 'HiddenObject.php';*/

        parent::__construct();

        $this->icons_path = $this->getPathUri() . 'views/img/icons/';
        $this->icons_dir = $this->getLocalPath() . 'views/img/icons/';
        $this->uploads_dir = $this->getLocalPath() . 'views/img/uploads/';

        $folders_need_permissions = [
            $this->uploads_dir,
            $this->icons_dir . '64/',
            $this->icons_dir . '48/',
            $this->icons_dir . '32/',
            $this->icons_dir . '24/',
            $this->icons_dir . '20/',
            $this->icons_dir . '16/',
        ];

        foreach ($folders_need_permissions as $folder) {
            if (!is_dir($folder) || !is_writable($folder)) {
                $this->warning = sprintf(
                    $this->l('Folder %1$s is not available for writing, please check permissions before continuing'),
                    $folder
                );
            }
        }

        $this->jquery_selectors = [
            "#center_column img[src*='-cart']",
            "#center_column img[src*='-small']",
            "#center_column img[src*='-medium']",
            "#center_column img[src*='-home']",
            "#center_column img[src*='-large']",
            "#center_column img[src*='img/p/']",
        ];
    }

    public function install(): bool
    {
        require_once _PS_MODULE_DIR_ . $this->module_name . '/sql/install.php';

        \Configuration::updateValue($this->getPrefix() . 'HIDDENOBJECTS_VERSION', $this->version);
        \Configuration::updateValue($this->getPrefix() . 'HIDDENOBJECTS_SELECTOR', implode(', ', $this->jquery_selectors));
        \Configuration::updateValue($this->getPrefix() . 'HIDDENOBJECTS_DISPLAY', 'front');
        \Configuration::updateValue($this->getPrefix() . 'HIDDENOBJECTS_TEST', 0);

        return
            parent::install()
            && $this->installTab()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayHome')
            && $this->registerHook('displayShoppingCartFooter')
            && $this->setModuleinFirst('displayHome');
    }


    public function uninstall(): bool
    {
        \Configuration::deleteByName($this->getPrefix() . 'HIDDENOBJECTS_VERSION');
        \Configuration::deleteByName($this->getPrefix() . 'HIDDENOBJECTS_SELECTOR');
        \Configuration::deleteByName($this->getPrefix() . 'HIDDENOBJECTS_DISPLAY');
        \Configuration::deleteByName($this->getPrefix() . 'HIDDENOBJECTS_TEST');
        return parent::uninstall() && $this->uninstallTab();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installTab(): bool
    {
        $id_tab = \Tab::getIdFromClassName($this->controller_name);
        $tab = new \Tab($id_tab);
        $tab->name = [];
        foreach (\Language::getLanguages(false) as $lang) {
            if (array_key_exists($lang['iso_code'], $this->module_display_name)) {
                $tab->name[$lang['id_lang']] = $this->module_display_name[$lang['iso_code']];
            } else {
                $tab->name[$lang['id_lang']] = $this->module_display_name['others'];
            }
        }
        $tab->class_name = $this->controller_name;
        $tab->module = $this->name;
        $tab->id_parent = \Tab::getIdFromClassName('AdminPriceRule');
        return $tab->save();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstallTab(): bool
    {
        if ($id_tab = \Tab::getIdFromClassName($this->controller_name)) {
            $tab = new \Tab($id_tab);
            if (\Validate::isLoadedObject($tab) && !$tab->delete()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function setModuleInFirst($hook_name): bool
    {
        $id_hook = \Hook::getIdByName($hook_name);
        if ((int)$id_hook) {
            return $this->updatePosition($id_hook, 0, 1);
        }
        return true;
    }

    public function getContent()
    {
        \Tools::redirectAdmin($this->context->link->getAdminLink($this->controller_name));
    }

    public function generateIconSize($size, $icon)
    {
        $src_file = $this->icons_dir . '64/icon-' . $icon . '.png';
        $src_dist = $this->icons_dir . $size . '/icon-' . $icon . '.png';
        $url_dist = $this->icons_path . $size . '/icon-' . $icon . '.png';
        $base_path = \Tools::getShopDomainSsl(true);

        if (!file_exists($src_dist)) {
            if (
                !is_dir($this->icons_dir . $size) ||
                !is_writable($this->icons_dir . $size) ||
                !\ImageManager::resize($src_file, $src_dist, $size, $size, 'png')
            ) {
                return false;
            }
        }

        return $base_path . $url_dist;
    }

    /**
     * @throws \PrestaShopException
     */
    public function getContests()
    {
        $id_shop = (int)\Context::getContext()->shop->id;
        $id_lang = (int)\Context::getContext()->cookie->id_lang;

        $query = new \DbQuery();
        $query->select('a.*, b.*');
        $query->from($this->getTable(), 'a');
        $query->innerJoin($this->getLangTable(), 'b', 'a.id_hiddenobject = b.id_hiddenobject');
        $query->where('a.id_shop = ' . $id_shop);
        $query->where('b.id_lang = ' . $id_lang);
        $query->where('a.active = 1');
        $query->where(HOTools::buildOrWhere(['NOW() BETWEEN date_start AND date_end', 'date_start = date_end']));
        $query->where('('.$this->getRenewQuery().') < a.how_many');
        $contests = \Db::getInstance()->executeS($query);

        if (!$contests) {
            return false;
        }

        $class_name = $this->class_name;
        $hoInstance = new $class_name();
        foreach ($contests as &$contest) {
            $hoInstance->id = $contest['id_hiddenobject'];
            $contest['images'] = $hoInstance->getImages($id_lang);
        }

        return $contests;
    }

    /**
     * @throws \PrestaShopException
     */
    private function getRenewQuery() {
        $query = new \DbQuery();
        $query->select('COUNT(id_hiddenobject)');
        $query->from($this->getTable(), 'c');
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

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function areYouLucky()
    {
        $context = \Context::getContext();
        $current_controller = get_class($context->controller);
        $query = new \DbQuery();
        $query->select('a.*');
        $query->from($this->getTable(), 'a');
        $query->where('a.active = 1');
        $query->where('a.id_shop = ' . (int)$context->shop->id);
        $query->where(HOTools::buildOrWhere(['NOW() BETWEEN date_start AND date_end', 'date_start = date_end']));

        $orWhere = ['restriction = "none"'];
        switch ($current_controller) {
            case 'IndexController':
                $orWhere[] = 'restriction = "homepage"';
                break;
            case 'CategoryController':
                $orWhere[] = 'restriction = "categories"';
                $orWhere[] = 'restriction = "categories_and_products"';
                break;
            case 'ProductController':
                $orWhere[] = 'restriction = "products"';
                $orWhere[] = 'restriction = "categories_and_products"';
                break;
            case 'CmsController':
                $orWhere[] = 'restriction = "cms"';
                break;
        }
        $query->where(HOTools::buildOrWhere($orWhere));

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
                $this->getTable().'_founded',
                'b',
                'b.id_hiddenobject = a.id_hiddenobject AND b.is_test = 0 AND ' . HOTools::buildOrWhere($orWhere)
            );

            $query->where('b.id_hiddenobject IS NULL');
            $query->where('('.$this->getRenewQuery().') < a.how_many');
        }

        $objects = \Db::getInstance()->executeS($query);
        if (!count($objects)) {
            return $objects;
        }

        foreach ($objects as $key => $object) {
            $object['restriction_value'] = json_decode($object['restriction_value'], true);
            switch ($object['restriction']) {
                case 'categories':
                    if (!in_array(\Tools::getValue('id_category'), $object['restriction_value'])) {
                        unset($objects[$key]);
                        break;
                    }
                    break;
                case 'products':
                    if (!in_array(\Tools::getValue('id_product'), $object['restriction_value'])) {
                        unset($objects[$key]);
                        break;
                    }
                    break;
                case 'cms':
                    if (!in_array(\Tools::getValue('id_cms'), $object['restriction_value'])) {
                        unset($objects[$key]);
                        break;
                    }
                    break;
                case 'categories_and_products':
                    if ($current_controller == 'CategoryController') {
                        if (!in_array(\Tools::getValue('id_category'), $object['restriction_value'])) {
                            unset($objects[$key]);
                            break;
                        }
                    } else {
                        $product_cat = \Product::getProductCategories((int)\Tools::getValue('id_product'));
                        if (!count(array_intersect($product_cat, $object['restriction_value']))) {
                            unset($objects[$key]);
                            break;
                        }
                    }
                    break;
            }
        }

        if (!count($objects)) {
            return $objects;
        } else {
            return $objects[array_rand($objects)];
        }
    }

    public function getFoundedObjects()
    {
        $id_shop = (int) Context::getContext()->shop->id;
        $id_lang = (int) Context::getContext()->cookie->id_lang;
        $id_customer = (int) Context::getContext()->cookie->id_customer;
        $current_remote_address = ip2long(Tools::getRemoteAddr());

        $objects = Db::getInstance()->executeS(
            'SELECT b.icon, cr.code, crl.name FROM ' . _DB_PREFIX_ . $this->module_table . '_founded a
            INNER JOIN ' . _DB_PREFIX_ . $this->module_table . ' b
                ON a.id_hiddenobject = b.id_hiddenobject
            INNER JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON a.id_cart_rule = cr.id_cart_rule
            INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_lang crl
                ON a.id_cart_rule = crl.id_cart_rule AND crl.id_lang = ' . (int) $id_lang . '
            WHERE b.id_shop="' . (int) $id_shop . '" AND b.active=1 ' .
            ($id_customer ?
                'AND (a.id_customer=' . (int) $id_customer . ' OR ip_address="' . pSQL($current_remote_address) . '")' :
                'AND ip_address="' . pSQL($current_remote_address) . '"'
            ) . '
            AND (NOW() BETWEEN cr.date_from AND cr.date_to)'
        );

        if (!$objects) {
            return false;
        }

        return $objects;
    }

    public function hookBackOfficeHeader()
    {
        if (\Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJqueryPlugin('fancybox');
        $this->context->controller->addJS($this->_path . 'views/js/global.js');
        $this->context->controller->addCSS($this->_path . 'views/css/global.css');

        $object = $this->areYouLucky();
        if (!isset($object['id_hiddenobject']) || !(int) $object['id_hiddenobject']) {
            return '';
        }

        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        $token = \Tools::encrypt((int) $object['id_hiddenobject'] . '|' . date('YmdH') . '|' . $this->id);

        $object['icon_url'] = $this->generateIconSize((int) $object['size'], (int) $object['icon']);
        if (!$object['icon_url']) {
            return false;
        }

        $object['icon_link'] = $this->context->link->getModuleLink(
            $this->name,
            'found',
            ['id' => (int)$object['id_hiddenobject'], 'token' => $token,]
        );

        $this->context->smarty->assign([
            'object' => $object,
            'display' => \Configuration::get($this->getPrefix() . 'HIDDENOBJECTS_DISPLAY'),
            'selector' => \Configuration::get($this->getPrefix() . 'HIDDENOBJECTS_SELECTOR'),
            'email_support' => \Configuration::get('PS_SHOP_EMAIL'),
        ]);

        return $this->display($this->getFileName(), 'views/templates/front/hook/header.tpl');
    }

    /**
     * @throws \PrestaShopException
     */
    public function hookDisplayRightColumn()
    {
        return $this->hookDisplayLeftColumn();
    }

    /**
     * @throws \PrestaShopException
     */
    public function hookDisplayLeftColumn()
    {
        $this->assignSmartyVariables();
        return $this->display($this->getFileName(), 'views/templates/front/hook/column.tpl');
    }

    private function getFileName(): string
    {
        return $this->local_path . $this->name . '.php';
    }

    /**
     * @throws \PrestaShopException
     */
    private function assignSmartyVariables()
    {
        if (!self::$contests) {
            self::$contests = $this->getContests();
        }
        if (!self::$contests) {
            return;
        }

        $this->smarty->assign([
            'prefix' => \Tools::strtolower($this->getPrefix()),
            'contests' => self::$contests,
        ]);
    }

    /**
     * @throws \PrestaShopException
     */
    public function hookDisplayHome()
    {
        $this->assignSmartyVariables();
        return $this->display($this->getFileName(), 'views/templates/front/hook/home.tpl');
    }

    /**
     * @throws \PrestaShopException
     */
    public function hookDisplayFooter()
    {
        $this->assignSmartyVariables();
        return $this->display($this->getFileName(), 'views/templates/front/hook/footer.tpl');
    }

    public function hookDisplayShoppingCartFooter()
    {
        $objects = $this->getFoundedObjects();
        if (!$objects) {
            return '';
        }

        foreach ($objects as &$object) {
            $object['icon_url'] = $this->generateIconSize(24, (int) $object['icon']);
            if (!$object['icon_url']) {
                return false;
            }
        }

        $this->smarty->assign('objects', $objects);
        return $this->display($this->getFileName(), 'views/templates/front/hook/shopping-cart.tpl');
    }

    public function getPrefix(): string
    {
        return '';
    }

    public function getRestrictionsValues(): array
    {
        return [
            ['value' => 'none', 'label' => $this->l('No restriction', 'modulehiddenobjects')],
            ['value' => 'homepage', 'label' => $this->l('Homepage only', 'modulehiddenobjects')],
            ['value' => 'categories', 'label' => $this->l('On some categories', 'modulehiddenobjects')],
            ['value' => 'categories_and_products', 'label' => $this->l('On some categories and their products', 'modulehiddenobjects')],
            ['value' => 'products', 'label' => $this->l('On some products', 'modulehiddenobjects')],
            ['value' => 'cms', 'label' => $this->l('On some cms', 'modulehiddenobjects')],
        ];
    }

    public function getRenewValues(): array {
        return [
            ['value' => 'none', 'label' => $this->l('Do not renew', 'modulehiddenobjects')],
            ['value' => 'daily', 'label' => $this->l('Every day', 'modulehiddenobjects')],
            ['value' => 'weekly', 'label' => $this->l('Every week', 'modulehiddenobjects')],
            ['value' => 'monthly', 'label' => $this->l('Every month', 'modulehiddenobjects')],
        ];
    }

    public function getTable(): string
    {
        return '';
    }

    public function getLangTable(): string
    {
        return $this->getTable().'_lang';
    }
}
