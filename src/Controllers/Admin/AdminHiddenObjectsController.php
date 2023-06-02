<?php
/**
 * 2016 Adilis
 *
 * Make your shop interactive for Christmas time: hide objects and ask your customers to find them in order to win a
 * discount coupon. Make your brand stand out by offering an original game: a treasure hunt throughout your products.
 *
 *  @author    Adilis <support@adilis.fr>
 *  @copyright 2016 SAS Adilis
 *  @license   http://www.adilis.fr
 */

namespace Adilis\HiddenObjects\Controllers\Admin;

use Adilis\HiddenObjects\Classes\HiddenObject;
use Adilis\HiddenObjects\Classes\HOTools;
use Adilis\HiddenObjects\Classes\ModuleHiddenObjects;

class AdminHiddenObjectsController extends \ModuleAdminController
{
    public $prefix = '';
    public $module_table = '';

    public $show_page_header_toolbar = true;
    public $multishop_context = \ShopCore::CONTEXT_SHOP;

    /**
     * @var array|array[]
     */
    private $results_fields_list;

    /** @var ModuleHiddenObjects */
    public $module;

    /** @var HiddenObject|null Instantiation of the class associated with the AdminController */
    protected $object;

    public function __construct()
    {
        $this->identifier = 'id_hiddenobject';
        $this->list_id = 'hiddenobject';

        $this->controller_type = 'moduleadmin';

        $controller_name = get_class($this);
        if (strpos($controller_name, 'Controller')) {
            $controller_name = \Tools::substr($controller_name, 0, -10);
        }

        $this->id = \Tab::getIdFromClassName($controller_name);
        $tab = new \Tab($this->id);
        if (!$tab->module) {
            throw new \PrestaShopException('Admin tab ' . get_class($this) . ' is not a module tab');
        }
        $this->module = \Module::getInstanceByName($tab->module);
        if (!$this->module->id) {
            throw new \PrestaShopException("Module {$tab->module} not found");
        }

        \AdminController::__construct();

        $this->override_folder = 'hiddenobjects';
        $this->tpl_folder = 'hiddenobjects/';
        $this->table = 'hiddenobjects_' . \Tools::strtolower($this->module->getPrefix());
        $this->className = $this->module->getClassName();
        $this->bootstrap = true;
        $this->lang = true;
        $this->specificConfirmDelete = false;

        $this->bulk_actions = [
            'enableSelection' => [
                'text' => $this->l('Enable selection'),
                'icon' => 'icon-power-off text-success',
            ],
            'disableSelection' => [
                'text' => $this->l('Disable selection'),
                'icon' => 'icon-power-off text-danger',
            ],
            'divider' => ['text' => 'divider'],
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ],
        ];

        $restriction_array = [];
        foreach ($this->module->getRestrictionsValues() as $restriction) {
            $restriction_array[$restriction['value']] = $restriction['label'];
        }

        $this->fields_options = [
            'general' => [
                'title' => $this->l('Options'),
                'fields' => [
                    $this->module->getPrefix() . 'HIDDENOBJECTS_SELECTOR' => [
                        'title' => $this->l('Jquery selector for experts'),
                        'type' => 'text',
                    ],
                    $this->module->getPrefix() . 'HIDDENOBJECTS_TEST' => [
                        'title' => $this->l('Active test mode'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                        'desc' => $this->l('Lets you see the icon even though you have already found'),
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];

        $this->fields_list = [
            'id_hiddenobject' => ['title' => $this->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'],
            'icon' => ['title' => $this->l('Icon'), 'callback' => 'getIconForList', 'class' => 'fixed-width-xs', 'align' => 'center'],
            'name' => ['title' => $this->l('Name')],
            'date_start' => ['title' => $this->l('Date from'), 'filter_key' => 'a!date_start', 'type' => 'datetime'],
            'date_end' => ['title' => $this->l('Date to'), 'filter_key' => 'a!date_end', 'type' => 'datetime'],
            'how_many' => ['title' => $this->l('How many'), 'callback' => 'printHowMany', 'filter_key' => 'a!how_many', 'align' => 'center'],
            'restriction' => ['title' => $this->l('Restriction on'), 'type' => 'select', 'list' => $restriction_array, 'filter_key' => 'a!restriction'],
            'active' => ['title' => $this->l('Active'), 'active' => 'status', 'type' => 'bool', 'class' => 'fixed-width-xs', 'align' => 'center', 'orderby' => false],
        ];

        $this->results_fields_list = [
            'id_hiddenobject_founded' => ['title' => $this->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'],
            'customer' => ['title' => $this->l('Founded by'), 'havingFilter' => true],
            'cart_rule_code' => ['title' => $this->l('Cart rule'), 'havingFilter' => true],
            'date' => ['title' => $this->l('Date'), 'type' => 'datetime'],
            'is_test' => ['title' => $this->l('Is a test ?'), 'type' => 'bool', 'align' => 'center', 'activeVisu' => true],
            'ip_address' => ['title' => $this->l('IP Address'), 'callback' => 'returnIP', 'search' => false],
        ];

        $this->actions = ['edit', 'delete'];

        if (\Shop::isFeatureActive() && \Shop::getContext() == \ShopCore::CONTEXT_SHOP) {
            $this->_where .= 'AND a.`id_shop`= ' . (int) \Context::getContext()->shop->id;
        }
    }

    public function printHowMany($echo, $tr)
    {
        $renew = $tr['renew'] == 'none' ? '' : ' ' . $this->module->getRenewLabelByValue($tr['renew']);

        return \Tools::strtolower($echo . $renew);
    }

    public function getIconForList($echo, $tr): string
    {
        return '<img src="' . $this->module->getIconsPath() . '64/icon-' . (int) $tr['icon'] . '.png" width="32" height="32" />';
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->context->controller->addJs($this->module->getLocalPath() . '/views/js/back.js');
        $this->context->controller->addCss($this->module->getLocalPath() . '/views/css/back.css');
        $this->addJqueryPlugin(['typewatch', 'autocomplete']);
    }

    public function initPageHeaderToolbar()
    {
        switch ($this->display) {
            case 'edit':
            case 'add':
                $this->page_header_toolbar_btn['back-to-list'] = [
                    'href' => self::$currentIndex . '&token=' . $this->token,
                    'desc' => $this->l('Back to list', null, null, false),
                    'icon' => 'process-icon-back',
                ];

                break;
            default:
                $this->page_header_toolbar_btn['new'] = [
                    'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                    'desc' => $this->l('Add new', null, null, false),
                    'icon' => 'process-icon-new',
                ];
        }
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        if (
            \Tools::isSubmit('submitAddhiddenobjects_' . \Tools::strtolower($this->module->getPrefix()))
            || \Tools::isSubmit('submitAddhiddenobjects_' . \Tools::strtolower($this->module->getPrefix()) . 'AndStay')
        ) {
            if (!(int) \Tools::getValue('free_gift')) {
                $_POST['gift_product'] = 0;
            }
            if ($id_product = (int) \Tools::getValue('gift_product')) {
                $_POST['gift_product_attribute'] = (int) \Tools::getValue('ipa_' . $id_product);
            }
        } elseif (\Tools::getIsset('submitReset' . $this->list_id)) {
            parent::processResetFilters($this->list_id);
        }
        parent::postProcess();
    }

    public function renderOptions(): string
    {
        return parent::renderOptions() . $this->renderAbout();
    }

    public function processFilter()
    {
        if ($this->display == 'add' || $this->display == 'edit') {
            $this->fields_list = $this->results_fields_list;
        }
        parent::processFilter();
    }

    /**
     * @throws \PrestaShopException
     */
    public function renderResults()
    {
        if (!\Validate::isLoadedObject($this->object)) {
            return '';
        }

        self::$currentIndex .= '&' . $this->identifier . '=' . (int) $this->object->id . '&update' . $this->table;
        $this->toolbar_title = $this->l('Objects founded');
        $this->table .= '_founded';
        $this->lang = false;
        $this->identifier .= '_founded';
        $this->actions = $this->bulk_actions = [];
        $this->list_no_link = true;
        $this->_select =
            'cr.code as cart_rule_code,
            IF(c.id_customer IS NULL,
                CONCAT("' . pSQL($this->l('Guest')) . ' #", a.`id_guest`),
                CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`)
            ) AS `customer`';
        $this->toolbar_btn = [];
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON (a.id_customer=c.id_customer)';
        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON (a.id_cart_rule=cr.id_cart_rule)';
        $this->_where = 'AND a.id_hiddenobject = ' . (int) $this->object->id;
        $this->_orderBy = 'a.date';
        $this->_orderWay = 'DESC';

        $this->fields_list = $this->results_fields_list;
        $this->list_simple_header = false;

        return $this->renderList();
    }

    public function returnIP($echo)
    {
        return long2ip($echo);
    }

    public function renderForm()
    {
        if (!HOTools::isShopContext()) {
            $this->warnings[] = $this->l('Please, select a shop before create or edit a game');

            return $this->renderAbout();
        }

        if ($this->object->id && $this->object->id_shop != $this->context->shop->id) {
            $this->warnings[] = $this->l('This game has been created in another shop');

            return $this->renderAbout();
        }

        if (!$this->object->id) {
            $this->fields_value['date_start'] = date('Y-m-d H:i:s');
            $this->fields_value['date_end'] = date('Y-m-d H:i:s');
        }

        $this->fields_value['search_results'] = '<div id="search_results"></div>';
        $this->context->smarty->assign(
            [
                'languages' => \Language::getLanguages(false),
                'id_lang_default' => (int) \Configuration::get('PS_LANG_DEFAULT'),
                'renew_values' => $this->module->getRenewValues(),
                'renew_value_selected' => $this->object->renew,
            ]
        );

        $only_products = $only_cms = [];

        if (\Tools::getValue('only_products')) {
            $this->object->only_products = \Tools::getValue('only_products');
        }

        if (\Tools::getValue('only_cms')) {
            $this->object->only_cms = \Tools::getValue('only_cms');
        }

        if (\Tools::getValue('only_categories')) {
            $this->object->only_categories = \Tools::getValue('only_categories');
        }

        if ($this->object->id) {
            switch ($this->object->restriction) {
                case 'products':
                    if (is_array($this->object->restriction_value)) {
                        foreach ($this->object->restriction_value as $id_product) {
                            $product = new \Product($id_product, false, $this->context->cookie->id_lang);
                            if (\Validate::isLoadedObject($product)) {
                                $product_name = $product->reference ?
                                $product->reference . ' : ' . $product->name : $product->name;
                                $only_products[] = ['id_product' => $product->id, 'name' => $product_name];
                            }
                        }
                    }
                    $this->object->{'only_products[]'} = $this->object->restriction_value;
                    break;
                case 'cms':
                    if (is_array($this->object->restriction_value)) {
                        foreach ($this->object->restriction_value as $id_cms) {
                            $cms = new \CMS($id_cms, $this->context->cookie->id_lang);
                            if (\Validate::isLoadedObject($cms)) {
                                $only_cms[] = ['id_cms' => $cms->id, 'name' => $cms->meta_title];
                            }
                        }
                    }
                    $this->object->{'only_cms[]'} = $this->object->restriction_value;
                    break;
            }
        }

        $selected_categories = [];
        if (\Validate::isLoadedObject($this->object)) {
            if ($this->object->restriction == 'categories' || $this->object->restriction == 'categories_and_products') {
                $selected_categories = $this->object->restriction_value;
            }
        }

        $this->fields_value['icon_selection'] = $this->renderIconSelection();
        $this->fields_value['cart_rule'] = $this->renderCartRule();

        $this->multiple_fieldsets = true;

        $this->fields_form[]['form'] = [
            'legend' => [
                'title' => '<i class="icon-cogs"></i> ' . $this->l('Setup'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'required' => true,
                    'lang' => true,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Message when user found object'),
                    'name' => 'message_end',
                    'autoload_rte' => true,
                    'lang' => true,
                    'hint' => $this->l('Leave empty for default text'),
                    'desc' => $this->l('Use tag {code} in order to display cart rule code for user. Leave empty to use default message  '),
                ],
                [
                    'type' => 'datetime',
                    'label' => $this->l('Start date'),
                    'desc' => $this->l('Game date start'),
                    'name' => 'date_start',
                    'id' => 'date_start',
                    'required' => true,
                ],
                [
                    'type' => 'datetime',
                    'label' => $this->l('End date'),
                    'desc' => $this->l('Game date end. Set same date for unlimited'),
                    'name' => 'date_end',
                    'id' => 'date_end',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'size' => 5,
                    'col' => 2,
                    'label' => $this->l('How many'),
                    'desc' => $this->l('Choose how many gift you want to hide'),
                    'name' => 'how_many',
                    'id' => 'how_many',
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'required' => true,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
            ],
        ];
        $this->fields_form[]['form'] = [
            'legend' => [
                'title' => '<i class="icon-paint-brush"></i> ' . $this->l('Icon'),
            ],
            'input' => [
                [
                    'type' => 'free',
                    'name' => 'icon_selection',
                    'col' => 12,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Icon size'),
                    'name' => 'size',
                    'id' => 'size',
                    'options' => [
                        'query' => [
                            ['value' => 16, 'label' => '16 x 16'],
                            ['value' => 20, 'label' => '20 x 20'],
                            ['value' => 24, 'label' => '24 x 24'],
                            ['value' => 32, 'label' => '32 x 32'],
                            ['value' => 48, 'label' => '48 x 48'],
                            ['value' => 64, 'label' => '64 x 64'],
                        ],
                        'id' => 'value',
                        'name' => 'label',
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Add an effect on the icon'),
                    'name' => 'use_effect',
                    'required' => true,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'use_effect_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'use_effect_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
            ],
        ];

        $this->fields_form[]['form'] = [
            'legend' => [
                'title' => '<i class="icon-eye-slash"></i> ' . $this->l('Display'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Appear'),
                    'name' => 'appear_ratio',
                    'col' => 4,
                    'id' => 'appear_ratio',
                    'prefix' => $this->l('1 chance of'),
                    'desc' => $this->l('Choose the probability of the icon appearing. Ex: If you decide to set the probability to 1 in 10, this means that for every 10 times the page is displayed, the icon will appear approximately 1 time.'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Restriction'),
                    'name' => 'restriction',
                    'id' => 'restriction',
                    'options' => [
                        'query' => $this->module->getRestrictionsValues(),
                        'id' => 'value',
                        'name' => 'label',
                    ],
                ],
                $this->getCategoriesField($selected_categories),
                [
                    'type' => 'select',
                    'label' => $this->l('Only on products'),
                    'name' => 'only_products[]',
                    'desc' => $this->l('Select products and this element will appear only on selected products'),
                    'id' => 'only_products',
                    'class' => 'chosen',
                    'multiple' => true,
                    'options' => [
                        'query' => self::getProducts(),
                        'id' => 'id_product',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Only on cms pages'),
                    'name' => 'only_cms[]',
                    'desc' => $this->l('Select cms and this element will appear only on selected cms pages'),
                    'id' => 'only_cms',
                    'class' => 'chosen',
                    'multiple' => true,
                    'options' => [
                        'query' => self::getCMSPages(),
                        'id' => 'id_cms',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_form[]['form'] = [
            'legend' => [
                'title' => '<i class="icon-tag"></i> ' . $this->l('Cart rule'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'class' => 't',
                    'label' => $this->l('Use existing cart rule'),
                    'name' => 'use_custom_cart_rule',
                    'required' => true,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'use_custom_cart_rule_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'use_custom_cart_rule_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Code'),
                    'name' => 'custom_cart_rule_code',
                    'id' => 'custom_cart_rule_code',
                    'col' => 3,
                ],
                [
                    'type' => 'free',
                    'name' => 'cart_rule',
                    'col' => 12,
                ],
            ],
        ];

        end($this->fields_form);
        $key = key($this->fields_form);
        $this->fields_form[$key]['form']['buttons'] = [['title' => $this->l('Save and stay'),
            'class' => 'pull-right',
            'icon' => 'process-icon-save',
            'type' => 'submit',
            'name' => 'submitAddhiddenobjects_' . \Tools::strtolower($this->module->getPrefix()) . 'AndStay',
        ]];

        $this->renderFormInTabs($this->fields_form);
        $this->multiple_fieldsets = false;

        $string = parent::renderForm();
        $string .= $this->renderResults();
        $string .= $this->renderAbout();

        return $string;
    }

    public function renderFormInTabs($fields_form)
    {
        $this->fields_form = [];
        foreach ($fields_form as $key => $fieldset) {
            if ($key == 0) {
                $this->fields_form['legend'] = $fieldset['form']['legend'];
                $this->fields_form['tabs'] = [];
                $this->fields_form['input'] = [];
            }
            $this->fields_form['tabs']['tab' . $key] = $fieldset['form']['legend']['title'];
            foreach ($fieldset['form']['input'] as $input) {
                $input['tab'] = 'tab' . $key;
                $this->fields_form['input'][] = $input;
            }
            if (array_key_exists('submit', $fieldset['form'])) {
                $this->fields_form['submit'] = $fieldset['form']['submit'];
            }

            if (array_key_exists('buttons', $fieldset['form'])) {
                $this->fields_form['buttons'] = $fieldset['form']['buttons'];
            }
        }
    }

    public function renderAbout()
    {
        $this->context->smarty->assign(
            [
                'moduleversion' => $this->module->version,
                'module_dir' => $this->module->getPathUri(),
                'psversion' => _PS_VERSION_,
                'phpversion' => PHP_VERSION,
                'iso_code' => \Language::getIsoById($this->context->cookie->id_lang),
            ]
        );

        return $this->context->smarty->fetch($this->module->getLocalPath() . '/views/templates/admin/about.tpl');
    }

    public function renderIconSelection()
    {
        $images = glob($this->module->getIconsDir() . '64/icon-{*.gif,*.jpg,*.png}', GLOB_BRACE);
        natsort($images);
        $id_icon_selected = (int) \Tools::getValue('icon', $this->object->icon);
        $id_icon_selected_default = false;
        $icons = [];
        foreach ($images as $image) {
            preg_match('/icon-([0-9]+)\.[png|gif|jpg]/i', basename($image), $matches);
            $is_selected = false;
            if ((int) $id_icon_selected) {
                if ((int) $id_icon_selected == (int) $matches[1]) {
                    $is_selected = true;
                }
            } else {
                if (!$id_icon_selected_default) {
                    $id_icon_selected_default = true;
                    $is_selected = true;
                }
            }
            $icons[] = [
                'src' => $this->module->getIconsPath() . '64/' . basename($image),
                'id' => (int) $matches[1],
                'selected' => $is_selected,
            ];
        }
        $this->context->smarty->assign(
            [
                'icons' => $icons,
                'module_name' => $this->module->getName(),
                'object' => $this->object,
            ]
        );

        return $this->context->smarty->fetch($this->module->getLocalPath() . '/views/templates/admin/icon-selection.tpl');
    }

    public function renderCartRule()
    {
        $currencies = \Currency::getCurrencies(false, true, true);

        $gift_product_filter = '';
        if (\Validate::isUnsignedId($this->object->gift_product)
            && ($product = new \Product($this->object->gift_product, false, $this->context->language->id))
            && \Validate::isLoadedObject($product)) {
            $gift_product_filter = (!empty($product->reference) ? $product->reference : $product->name);
        }

        $reduction_product_filter = '';
        if (\Validate::isUnsignedId($this->object->reduction_product)
            && ($product = new \Product($this->object->reduction_product, false, $this->context->language->id))
            && \Validate::isLoadedObject($product)) {
            $reduction_product_filter = (!empty($product->reference) ? $product->reference : $product->name);
        }

        $gift_product_select = '';
        $gift_product_attribute_select = '';
        if ((int) $this->object->gift_product) {
            $search_products = $this->searchProducts($gift_product_filter);
            if (isset($search_products['products']) && is_array($search_products['products'])) {
                foreach ($search_products['products'] as $p) {
                    $gift_product_select .= '
                    <option value="' . $p['id_product'] . '" '
                    . ($p['id_product'] == $this->object->gift_product ? 'selected="selected"' : '') . '>
                        ' . $p['name'] . (count($p['combinations']) == 0 ? ' - ' . $p['formatted_price'] : '') . '
                    </option>';

                    if (count($p['combinations'])) {
                        $gift_product_attribute_select .= '<select class="control-form id_product_attribute" id="ipa_'
                        . $p['id_product'] . '" name="ipa_' . $p['id_product'] . '">';
                        foreach ($p['combinations'] as $c) {
                            $is_selected = $c['id_product_attribute'] == $this->object->gift_product_attribute;
                            $gift_product_attribute_select .= ' <option ' . ($is_selected ? 'selected="selected"' : '')
                            . ' value="' . $c['id_product_attribute'] . '">' . $c['attributes'] . ' - ' . $c['formatted_price'] . '
                            </option>';
                        }
                        $gift_product_attribute_select .= '</select>';
                    }
                }
            }
        }

        $this->context->smarty->assign(
            [
                'giftProductFilter' => $gift_product_filter,
                'gift_product_select' => $gift_product_select,
                'gift_product_attribute_select' => $gift_product_attribute_select,
                'reductionProductFilter' => $reduction_product_filter,
                'currencies' => $currencies,
                'currentObject' => $this->object,
                'cartRulesToken' => \Tools::getAdminTokenLite('AdminCartRules'),
                'currentTab' => $this,
                'defaultCurrency' => (int) \Configuration::get('PS_CURRENCY_DEFAULT'),
                'ps_version' => _PS_VERSION_,
            ]
        );

        return $this->context->smarty->fetch($this->module->getLocalPath() . '/views/templates/admin/cart-rule.tpl');
    }

    protected function _childValidation()
    {
        if ((int) \Tools::getValue('minimum_amount') < 0) {
            $this->errors[] = $this->l('The minimum amount cannot be lower than zero.');
        }
        if ((int) \Tools::getValue('cart_rule_date_to') <= 0) {
            $this->errors[] = $this->l('The validaty of cart rule must be greater than zero.');
        }
        if ((float) \Tools::getValue('reduction_percent') < 0 || (float) \Tools::getValue('reduction_percent') > 100) {
            $this->errors[] = $this->l('Reduction percentage must be between 0% and 100%');
        }
        if ((int) \Tools::getValue('reduction_amount') < 0) {
            $this->errors[] = $this->l('Reduction amount cannot be lower than zero.');
        }
        $custom_cart_rule_code = \Tools::getValue('custom_cart_rule_code');
        if ($custom_cart_rule_code && !(int) \CartRule::getIdByCode($custom_cart_rule_code)) {
            $this->errors[] = sprintf($this->l('This cart rule code %d not exists'), $custom_cart_rule_code);
        }
        if (
            !$custom_cart_rule_code
            && \Tools::getValue('apply_discount') == 'off'
            && !\Tools::getValue('free_shipping') && !\Tools::getValue('free_gift')
        ) {
            $this->errors[] = $this->l('An action is required for this cart rule.');
        }

        switch (\Tools::getValue('restriction')) {
            case 'categories':
            case 'categories_and_products':
                if (!\Tools::getValue('only_categories')) {
                    $this->errors[] = $this->l('You must set at least one category.');
                }
                break;
            case 'products':
                if (!\Tools::getValue('only_products')) {
                    $this->errors[] = $this->l('You must set at least one product.');
                }
                break;
            case 'cms':
                if (!\Tools::getValue('only_cms')) {
                    $this->errors[] = $this->l('You must set at least one cms page.');
                }
                break;
            default:
                break;
        }
    }

    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);
        $object->id_shop = $this->context->shop->id;

        switch (\Tools::getValue('restriction')) {
            case 'categories':
            case 'categories_and_products':
                $object->restriction_value = \Tools::getValue('only_categories', []);
                break;
            case 'products':
                $object->restriction_value = \Tools::getValue('only_products', []);
                break;
            case 'cms':
                $object->restriction_value = \Tools::getValue('only_cms', []);
                break;
            default:
                $object->restriction_value = [];
                break;
        }
        $object->cart_rule_restriction = (bool) \Tools::getValue('cart_rule_restriction');
    }

    protected function searchProducts($search)
    {
        if ($products = \Product::searchByName((int) $this->context->language->id, $search)) {
            foreach ($products as &$product) {
                $combinations = [];
                $productObj = new \Product((int) $product['id_product'], false, (int) $this->context->language->id);
                $attributes = $productObj->getAttributesGroups((int) $this->context->language->id);
                $product['formatted_price'] = \Tools::displayPrice(
                    \Tools::convertPrice($product['price_tax_incl'], $this->context->currency),
                    $this->context->currency
                );

                foreach ($attributes as $att) {
                    if (!isset($combinations[$att['id_product_attribute']]['attributes'])) {
                        $combinations[$att['id_product_attribute']]['attributes'] = '';
                    }
                    $combinations[$att['id_product_attribute']]['attributes'] .= $att['attribute_name'] . ' - ';
                    $combinations[$att['id_product_attribute']]['id_product_attribute'] = $att['id_product_attribute'];
                    $combinations[$att['id_product_attribute']]['default_on'] = $att['default_on'];
                    if (!isset($combinations[$att['id_product_attribute']]['price'])) {
                        $price_tax_incl = \Product::getPriceStatic(
                            (int) $product['id_product'],
                            true,
                            $att['id_product_attribute']
                        );
                        $combinations[$att['id_product_attribute']]['formatted_price'] = \Tools::displayPrice(
                            \Tools::convertPrice($price_tax_incl, $this->context->currency),
                            $this->context->currency
                        );
                    }
                }

                foreach ($combinations as &$combination) {
                    $combination['attributes'] = rtrim($combination['attributes'], ' - ');
                }
                $product['combinations'] = $combinations;
            }

            return [
                'products' => $products,
                'found' => true,
            ];
        } else {
            return ['found' => false, 'notfound' => $this->l('No product has been found.')];
        }
    }

    public function getCategoriesField($selected_categories = [])
    {
        $root_category = \Category::getRootCategory();

        return [
            'type' => 'categories',
            'label' => $this->l('Only for categories'),
            'name' => 'only_categories',
            'desc' => $this->l('Select categories and this element will appear only on selected categories'),
            'tree' => [
                'id' => 'only_categories',
                'title' => $this->l('Available categories'),
                'disabled_categories' => [],
                'selected_categories' => $selected_categories,
                'root_category' => $root_category->id,
                'use_checkbox' => true,
            ],
        ];
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return $this->module->l($string, 'adminhiddenobjectscontroller');
    }

    private function getCMSPages()
    {
        $context = \Context::getContext();
        $pages = \CMS::getCMSPages($context->cookie->id_lang, null, true, $context->shop->id);
        usort($pages, function ($a, $b) {
            return strcmp($a['meta_title'], $b['meta_title']);
        });

        foreach ($pages as &$page) {
            $page['name'] = $page['meta_title'] . ' (ID: ' . $page['id_cms'] . ')';
        }

        return $pages;
    }

    private function getProducts()
    {
        $context = \Context::getContext();
        $products = \Product::getProducts($context->cookie->id_lang, 0, 0, 'name', 'asc', false, true, $context);
        foreach ($products as &$product) {
            $product['name'] = $product['name'] . ' (ID: ' . $product['id_product'] . ')';
        }

        return $products;
    }
}
