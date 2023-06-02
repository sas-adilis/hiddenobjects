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

namespace Adilis\HiddenObjects\Classes;

use Adilis\HiddenObjects\Sql\TableInstaller;

/**
 * @property $module_name
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
class ModuleHiddenObjects extends \Module
{
    public static $contests = false;
    public $is_in_maintenance = '';
    private $controller_name;
    public $class_name;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->controller_name = 'Admin' . $this->getPrefix() . 'HiddenObjects';
        $this->class_name = $this->getPrefix() . 'HiddenObjects';
        $this->version = '2.0.0';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = $this->getModuleKey();
        $this->ps_versions_compliancy = ['min' => '1.6.1', 'max' => _PS_VERSION_];
        $this->author = 'Adilis';
        $this->tab = 'pricing_promotion';
        $this->name = $this->getName();
        $this->displayName = $this->getDisplayName();
        $this->description = $this->getDescription();

        require_once _PS_MODULE_DIR_ . $this->getName() . '/classes/' . $this->getPrefix() . 'HiddenObject.php';

        parent::__construct();

        $folders_need_permissions = [
            $this->getIconsDir() . '64/',
            $this->getIconsDir() . '48/',
            $this->getIconsDir() . '32/',
            $this->getIconsDir() . '24/',
            $this->getIconsDir() . '20/',
            $this->getIconsDir() . '16/',
        ];

        foreach ($folders_need_permissions as $folder) {
            if (!is_dir($folder) || !is_writable($folder)) {
                $this->warning = sprintf(
                    $this->l('Folder %1$s is not available for writing, please check permissions before continuing'),
                    $folder
                );
            }
        }
    }

    public function install(): bool
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            $jquery_selectors = [
                "#center_column img[src*='-cart']",
                "#center_column img[src*='-small']",
                "#center_column img[src*='-medium']",
                "#center_column img[src*='-home']",
                "#center_column img[src*='-large']",
                "#center_column img[src*='img/p/']",
            ];
        } else {
            $jquery_selectors = [
                '#content-wrapper .product-miniature .thumbnail-container img',
            ];
        }

        \Configuration::updateValue($this->getPrefix() . 'HIDDENOBJECTS_VERSION', $this->version);
        \Configuration::updateValue($this->getPrefix() . 'HIDDENOBJECTS_SELECTOR', implode(', ', $jquery_selectors));
        \Configuration::updateValue($this->getPrefix() . 'HIDDENOBJECTS_TEST', 0);

        return
            parent::install()
            && (new TableInstaller())->setTable($this->getTable())->install()
            && $this->copyAssets()
            && $this->installTab()
            && $this->registerHook('header')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayShoppingCartFooter');
    }

    public function uninstall(): bool
    {
        \Configuration::deleteByName($this->getPrefix() . 'HIDDENOBJECTS_VERSION');
        \Configuration::deleteByName($this->getPrefix() . 'HIDDENOBJECTS_SELECTOR');
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
        foreach (\Language::getLanguages(false) as $l) {
            $tab->name[$l['id_lang']] = $l['iso_code'] == 'fr' ? $this->getFrenchTabName() : $this->getDefaultTabName();
        }
        $tab->class_name = $this->controller_name;
        $tab->module = $this->name;
        $tab->id_parent = -1;

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
        if ((int) $id_hook) {
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
        $src_file = $this->getIconsDir() . '64/icon-' . $icon . '.png';
        $src_dist = $this->getIconsDir() . $size . '/icon-' . $icon . '.png';
        $url_dist = $this->getIconsPath() . $size . '/icon-' . $icon . '.png';
        $base_path = \Tools::getShopDomainSsl(true);

        if (!file_exists($src_dist)) {
            if (
                !is_dir($this->getIconsDir() . $size)
                || !is_writable($this->getIconsDir() . $size)
                || !\ImageManager::resize($src_file, $src_dist, $size, $size, 'png')
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
        $id_shop = (int) \Context::getContext()->shop->id;
        $id_lang = (int) \Context::getContext()->cookie->id_lang;

        $query = new \DbQuery();
        $query->select('a.*, b.*');
        $query->from($this->getTable(), 'a');
        $query->innerJoin($this->getLangTable(), 'b', 'a.id_hiddenobject = b.id_hiddenobject');
        $query->where('a.id_shop = ' . $id_shop);
        $query->where('b.id_lang = ' . $id_lang);
        $query->where('a.active = 1');
        $query->where(HOTools::buildOrWhere(['NOW() BETWEEN date_start AND date_end', 'date_start = date_end']));
        $query->where('(' . $this->getRenewQuery() . ') < a.how_many');
        $contests = \Db::getInstance()->executeS($query);

        if (!$contests) {
            return false;
        }

        return $contests;
    }

    /**
     * @throws \PrestaShopException
     */
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
        $query->where('a.id_shop = ' . (int) $context->shop->id);
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
                $this->getTable() . '_founded',
                'b',
                'b.id_hiddenobject = a.id_hiddenobject AND b.is_test = 0 AND ' . HOTools::buildOrWhere($orWhere)
            );

            $query->where('b.id_hiddenobject IS NULL');
            $query->where('(' . $this->getRenewQuery() . ') < a.how_many');
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
                        $product_cat = \Product::getProductCategories((int) \Tools::getValue('id_product'));
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
        $id_shop = (int) \Context::getContext()->shop->id;
        $id_lang = (int) \Context::getContext()->cookie->id_lang;
        $id_customer = (int) \Context::getContext()->cookie->id_customer;
        $current_remote_address = ip2long(\Tools::getRemoteAddr());

        $query = new \DbQuery();
        $query->select('b.icon, cr.code, crl.name');
        $query->from($this->getTable() . '_founded', 'a');
        $query->innerJoin($this->getTable(), 'b', 'a.id_hiddenobject = b.id_hiddenobject');
        $query->innerJoin('cart_rule', 'cr', 'a.id_cart_rule = cr.id_cart_rule');
        $query->innerJoin('cart_rule_lang', 'crl', 'a.id_cart_rule = crl.id_cart_rule AND crl.id_lang = ' . (int) $id_lang);

        if ($id_customer) {
            $orWhere = ['a.id_customer=' . (int) $id_customer, 'ip_address="' . pSQL($current_remote_address) . '"'];
            $query->where(HOTools::buildOrWhere($orWhere));
        } else {
            $query->where('ip_address="' . pSQL($current_remote_address) . '"');
        }
        $query->where('NOW() BETWEEN cr.date_from AND cr.date_to');
        $objects = \Db::getInstance()->executeS($query);
        if (!$objects) {
            return false;
        }

        return $objects;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (\Tools::getValue('module_name') == $this->getName()) {
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
        $this->context->controller->addJS($this->getLocalPath() . 'views/js/global.js');
        $this->context->controller->addCSS($this->getLocalPath() . 'views/css/global.css');

        $object = $this->areYouLucky();
        if (!isset($object['id_hiddenobject']) || !(int) $object['id_hiddenobject']) {
            return '';
        }

        $this->context->controller->addJS($this->getLocalPath() . 'views/js/front.js');
        $this->context->controller->addJS($this->getLocalPath() . 'assets/js/front.' . \Tools::strtolower($this->getPrefix()) . '.js');
        $this->context->controller->addCSS($this->getLocalPath() . 'views/css/front.css');
        $token = \Tools::encrypt((int) $object['id_hiddenobject'] . '|' . date('YmdH') . '|' . $this->id);

        $icon_url = $this->generateIconSize((int) $object['size'], (int) $object['icon']);
        if (!$icon_url) {
            return false;
        }

        $icon_link = $this->context->link->getModuleLink(
            $this->name,
            'found',
            ['id' => (int) $object['id_hiddenobject'], 'token' => $token]
        );

        \Media::addJsDef([
            \Tools::strtolower($this->getPrefix()) . 'HiddenObjectUrl' => $icon_url,
            \Tools::strtolower($this->getPrefix()) . 'HiddenObjectSize' => (int) $object['size'],
            \Tools::strtolower($this->getPrefix()) . 'HiddenObjectLink' => $icon_link,
            \Tools::strtolower($this->getPrefix()) . 'HiddenObjectUseEffect' => (int) $object['use_effect'],
            \Tools::strtolower($this->getPrefix()) . 'HiddenObjectSelector' => \Configuration::get($this->getPrefix() . 'HIDDENOBJECTS_SELECTOR'),
            'hiddenObjectFancyboxError' => $this->l('An error occured. Please contact us if necessary.'),
        ]);
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

        return $this->display($this->getFileName(), 'views/templates/front/shopping-cart.tpl');
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

    public function getRenewValues(): array
    {
        return [
            ['value' => 'none', 'label' => ''],
            ['value' => 'daily', 'label' => $this->l('Per day', 'modulehiddenobjects')],
            ['value' => 'weekly', 'label' => $this->l('Per week', 'modulehiddenobjects')],
            ['value' => 'monthly', 'label' => $this->l('Per month', 'modulehiddenobjects')],
        ];
    }

    public function getRenewLabelByValue(string $v): string
    {
        foreach ($this->getRenewValues() as $value) {
            if ($value['value'] == $v) {
                return $value['label'];
            }
        }
    }

    public function getLangTable(): string
    {
        return $this->getTable() . '_lang';
    }

    public function getAssetsPath(): string
    {
        return $this->getLocalPath() . 'vendor/adilis/hiddenobjects/src/Assets/';
    }

    public function copyAssets(): bool
    {
        if (
            (is_dir($this->getLocalPath() . 'views/') && !\Tools::deleteDirectory($this->getLocalPath() . 'views/'))
            || (is_dir($this->getLocalPath() . 'img/') && !\Tools::deleteDirectory($this->getLocalPath() . 'img/'))
            || (is_dir($this->getLocalPath() . 'translations/') && !\Tools::deleteDirectory($this->getLocalPath() . 'translations/'))
        ) {
            return false;
        }

        \Tools::recurseCopy($this->getAssetsPath() . 'views/', $this->getLocalPath() . 'views/');
        \Tools::recurseCopy($this->getAssetsPath() . 'translations/', $this->getLocalPath() . 'translations/');

        $files = \Tools::scandir(
            $this->getLocalPath(),
            'tpl',
            'views',
            true
        );
        $files = array_merge($files, \Tools::scandir(
            $this->getLocalPath(),
            'php',
            'translations',
            true)
        );

        foreach ($files as $file) {
            $content = \Tools::file_get_contents($this->getLocalPath() . $file);
            $content = str_replace('[[MODULENAME]]', $this->name, $content);
            @file_put_contents($this->getLocalPath() . $file, $content);
        }
        
        return true;
    }

    public function getIconsPath(): string
    {
        return $this->getPathUri() . 'assets/icons/';
    }

    public function getIconsDir(): string
    {
        return $this->getLocalPath() . 'assets/icons/';
    }

    public function getClassName(): string
    {
        return $this->getPrefix() . 'HiddenObject';
    }

    public function getDisplayName(): string
    {
        return sprintf($this->l('Hidden objects game : %s'), $this->getTheme());
    }

    public function getDescription(): string
    {
        return sprintf($this->l('Hide objects on your shop for %s'), $this->getTheme());
    }

    public function getDefaultTabName(): string
    {
        return sprintf('Hidden objects game : %s', $this->getTheme());
    }

    public function getFrenchTabName(): string
    {
        return sprintf('Jeu des objets cachés : %s', $this->getTheme());
    }

    protected static function loadUpgradeVersionList($module_name, $module_version, $registered_version)
    {
        $list = [];

        $upgrade_path = _PS_MODULE_DIR_ . '' . $module_name . '/vendor/adilis/hiddenobjects/src/Upgrades/';
        // Check if folder exist and it could be read
        if (file_exists($upgrade_path) && ($files = scandir($upgrade_path, SCANDIR_SORT_NONE))) {
            // Read each file name
            foreach ($files as $file) {
                if (!in_array($file, ['.', '..', '.svn', 'index.php']) && preg_match('/\.php$/', $file)) {
                    $tab = explode('-', $file);

                    if (!isset($tab[1])) {
                        continue;
                    }

                    $file_version = basename($tab[1], '.php');
                    // Compare version, if minor than actual, we need to upgrade the module
                    if (count($tab) == 2 &&
                        (\Tools::version_compare($file_version, $module_version, '<=') &&
                            \Tools::version_compare($file_version, $registered_version, '>'))) {
                        $list[] = [
                            'file' => $upgrade_path . $file,
                            'version' => $file_version,
                            'upgrade_function' => [
                                'upgrade_module_' . str_replace('.', '_', $file_version),
                                'upgradeModule' . str_replace('.', '', $file_version), ],
                        ];
                    }
                }
            }
        }

        // No files upgrade, then upgrade succeed
        if (count($list) == 0) {
            static::$modules_cache[$module_name]['upgrade']['success'] = true;
            \Module::upgradeModuleVersion($module_name, $module_version);
        }

        usort($list, 'ps_module_version_sort');

        // Set the list to module cache
        static::$modules_cache[$module_name]['upgrade']['upgrade_file_left'] = $list;
        static::$modules_cache[$module_name]['upgrade']['available_upgrade'] = count($list);

        return (bool) count($list);
    }
}
