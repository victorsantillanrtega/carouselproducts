<?php
/**
* @author Victor Santillan <santillan-15@live.com>
* @copyright 2019-2025 Victor Santillan
* @license Propierty Victor Santillan
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class CarouselProducts extends Module implements WidgetInterface
{
    private $templateFile;

    public function __construct()
    {
        $this->name = 'carouselproducts';
        $this->author = 'Victor Santillan';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->ps_versions_compliancy = [
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        ];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Carousel of products', array(), 'Modules.CarouselProducts.Admin');
        $this->description = $this->trans('Displays a carousel of products in the central column in homepage.', array(), 'Modules.CarouselProducts.Admin');

        $this->templateFile = 'module:carouselproducts/views/templates/hook/carouselproducts.tpl';
    }

    public function install()
    {
        $this->_clearCache('*');

        Configuration::updateValue('CAROUSELPRODUCTS_NUMBER', 8);
        Configuration::updateValue('CAROUSELPRODUCTS_CATEGORY', (int) Context::getContext()->shop->getCategory());
        Configuration::updateValue('CAROUSELPRODUCTS_RANDOMIZE', false);
        Configuration::updateValue('CAROUSELPRODUCTS_NBRPRDROW', 4);
        Configuration::updateValue('CAROUSELPRODUCTS_CAROUSEL', true);
        Configuration::updateValue('CAROUSELPRODUCTS_INFINITE', true);
        Configuration::updateValue('CAROUSELPRODUCTS_SPEED', 300);
        Configuration::updateValue('CAROUSELPRODUCTS_TOSCROLL', 1);
        Configuration::updateValue('CAROUSELPRODUCTS_DOTS', true);
        Configuration::updateValue('CAROUSELPRODUCTS_CENTERMODE', false);
        Configuration::updateValue('CAROUSELPRODUCTS_AUTOPLAY', true);
        Configuration::updateValue('CAROUSELPRODUCTS_CLASSES', "col-xs-12 col-ms-4 col-md-4");
        Configuration::updateValue('CAROUSELPRODUCTS_XSSS', 1);
        Configuration::updateValue('CAROUSELPRODUCTS_XSSC',1);
        Configuration::updateValue('CAROUSELPRODUCTS_SMSS', 3);
        Configuration::updateValue('CAROUSELPRODUCTS_SMSC',1);
        Configuration::updateValue('CAROUSELPRODUCTS_MDSS', 4);
        Configuration::updateValue('CAROUSELPRODUCTS_MDSC',1);

        return parent::install()
            && $this->registerHook('addproduct')
            && $this->registerHook('updateproduct')
            && $this->registerHook('deleteproduct')
            && $this->registerHook('categoryUpdate')
            && $this->registerHook('displayHome')
            && $this->registerHook('displayOrderConfirmation2')
            && $this->registerHook('displayCrossSellingShoppingCart')
            && $this->registerHook('actionAdminGroupsControllerSaveAfter')
            && $this->registerHook('displayHeader')
        ;
    }

    public function uninstall()
    {
        $this->_clearCache('*');

        return parent::uninstall();
    }

    public function hookAddProduct($params)
    {
        $this->_clearCache('*');
    }

    public function hookUpdateProduct($params)
    {
        $this->_clearCache('*');
    }

    public function hookDeleteProduct($params)
    {
        $this->_clearCache('*');
    }

    public function hookCategoryUpdate($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionAdminGroupsControllerSaveAfter($params)
    {
        $this->_clearCache('*');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCss($this->_path.'views/css/'.$this->name.'.css');
        $this->context->controller->addCss($this->_path.'views/css/slick.css');
        $this->context->controller->addCss($this->_path.'views/css/slick-theme.css');
        $this->context->controller->addJs($this->_path.'views/js/slick.js');
        $this->context->controller->addJs($this->_path.'views/js/'.$this->name.'.js');
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
    }

    public function getContent()
    {
        $output = '';
        $errors = array();

        if (Tools::isSubmit('submitCarouselProducts')) {
            $nbr = Tools::getValue('CAROUSELPRODUCTS_NUMBER');
            if (!Validate::isInt($nbr) || $nbr <= 0) {
                $errors[] = $this->trans('The number of products is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $cat = Tools::getValue('CAROUSELPRODUCTS_CATEGORY');
            if (!Validate::isInt($cat) || $cat <= 0) {
                $errors[] = $this->trans('The category ID is invalid. Please choose an existing category ID.', array(), 'Modules.CarouselProducts.Admin');
            }

            $rand = Tools::getValue('CAROUSELPRODUCTS_RANDOMIZE');
            if (!Validate::isBool($rand)) {
                $errors[] = $this->trans('Invalid value for the "randomize" flag.', array(), 'Modules.CarouselProducts.Admin');
            }

            $nbrprdrow = Tools::getValue('CAROUSELPRODUCTS_NBRPRDROW');
            if(!Validate::isInt($nbrprdrow) || $nbrprdrow <= 0){
                $errors[] = $this->trans('The number of products per row/carousel is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $carousel = Tools::getValue('CAROUSELPRODUCTS_CAROUSEL');
            if (!Validate::isBool($carousel)) {
                $errors[] = $this->trans('Invalid value for the "carousel" flag.', array(), 'Modules.CarouselProducts.Admin');
            }

            $infinite = Tools::getValue('CAROUSELPRODUCTS_INFINITE');
            if (!Validate::isBool($infinite)) {
                $errors[] = $this->trans('Invalid value for the "infinite" flag.', array(), 'Modules.CarouselProducts.Admin');
            }

            $speed = Tools::getValue('CAROUSELPRODUCTS_SPEED');
            if(!Validate::isInt($speed) || $speed <= 0){
                $errors[] = $this->trans('The speed is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $toscroll = Tools::getValue('CAROUSELPRODUCTS_TOSCROLL');
            if(!Validate::isInt($toscroll) || $toscroll <= 0){
                $errors[] = $this->trans('The slides to scroll is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $dots = Tools::getValue('CAROUSELPRODUCTS_DOTS');
            if (!Validate::isBool($dots)) {
                $errors[] = $this->trans('Invalid value for the "dots" flag.', array(), 'Modules.CarouselProducts.Admin');
            }

            $centermode = Tools::getValue('CAROUSELPRODUCTS_CENTERMODE');
            if (!Validate::isBool($centermode)) {
                $errors[] = $this->trans('Invalid value for the "center mode" flag.', array(), 'Modules.CarouselProducts.Admin');
            }

            $autoplay = Tools::getValue('CAROUSELPRODUCTS_AUTOPLAY');
            if (!Validate::isBool($autoplay)) {
                $errors[] = $this->trans('Invalid value for the "auto play" flag.', array(), 'Modules.CarouselProducts.Admin');
            }

            $classes = Tools::getValue('CAROUSELPRODUCTS_CLASSES');
            if (!Validate::isString($classes)) {
                $errors[] = $this->trans('Invalid value for the "classes" must be string.',array(), 'Modules.CarouselProducts.Admin');
            }

            $xsss = Tools::getValue('CAROUSELPRODUCTS_XSSS');
            if(!Validate::isInt($xsss) || $xsss <= 0){
                $errors[] = $this->trans('The slides to show in extra small devices is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $xssc = Tools::getValue('CAROUSELPRODUCTS_XSSC');
            if(!Validate::isInt($xssc) || $xssc <= 0){
                $errors[] = $this->trans('The slides to scroll in extra small devices is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $smss = Tools::getValue('CAROUSELPRODUCTS_SMSS');
            if(!Validate::isInt($smss) || $smss <= 0){
                $errors[] = $this->trans('The slides to show in small decives is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $smsc = Tools::getValue('CAROUSELPRODUCTS_SMSC');
            if(!Validate::isInt($smsc) || $smsc <= 0){
                $errors[] = $this->trans('The slides to scroll in small devices is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $mdss = Tools::getValue('CAROUSELPRODUCTS_MDSS');
            if(!Validate::isInt($mdss) || $mdss <= 0){
                $errors[] = $this->trans('The slides to show in medium decives is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }

            $mdsc = Tools::getValue('CAROUSELPRODUCTS_MDSC');
            if(!Validate::isInt($mdsc) || $mdsc <= 0){
                $errors[] = $this->trans('The slides to scroll in medium devices is invalid. Please enter a positive number.', array(), 'Modules.CarouselProducts.Admin');
            }


            if (isset($errors) && count($errors)) {
                $output = $this->displayError(implode('<br />', $errors));
            } else {
                Configuration::updateValue('CAROUSELPRODUCTS_NUMBER', (int) $nbr);
                Configuration::updateValue('CAROUSELPRODUCTS_CATEGORY', (int) $cat);
                Configuration::updateValue('CAROUSELPRODUCTS_RANDOMIZE', (bool) $rand);
                Configuration::updateValue('CAROUSELPRODUCTS_NBRPRDROW', (int) $nbrprdrow);
                Configuration::updateValue('CAROUSELPRODUCTS_CAROUSEL', (bool) $carousel);
                Configuration::updateValue('CAROUSELPRODUCTS_INFINITE', (bool) $infinite);
                Configuration::updateValue('CAROUSELPRODUCTS_SPEED', (int) $speed);
                Configuration::updateValue('CAROUSELPRODUCTS_TOSCROLL', (int) $toscroll);
                Configuration::updateValue('CAROUSELPRODUCTS_DOTS', (bool) $dots);
                Configuration::updateValue('CAROUSELPRODUCTS_CENTERMODE', (bool) $centermode);
                Configuration::updateValue('CAROUSELPRODUCTS_AUTOPLAY', (bool) $autoplay);
                Configuration::updateValue('CAROUSELPRODUCTS_CLASSES', (string) $classes);
                Configuration::updateValue('CAROUSELPRODUCTS_XSSS', (int) $xsss);
                Configuration::updateValue('CAROUSELPRODUCTS_XSSC', (int) $xssc);
                Configuration::updateValue('CAROUSELPRODUCTS_SMSS', (int) $smss);
                Configuration::updateValue('CAROUSELPRODUCTS_SMSC', (int) $smsc);
                Configuration::updateValue('CAROUSELPRODUCTS_MDSS', (int) $mdss);
                Configuration::updateValue('CAROUSELPRODUCTS_MDSC', (int) $mdsc);

                $this->_clearCache('*');

                $output = $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
            }
        }

        return $output.$this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ),

                'description' => $this->trans('To add products in homepage, simply add them to the corresponding product category (default: "Home").', array(), 'Modules.CarouselProducts.Admin'),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Carousel', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_CAROUSEL',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Enable effect carousel.', array(), 'Modules.CarouselProducts.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', array(), 'Admin.Global'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', array(), 'Admin.Global'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Number of products to be displayed', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_NUMBER',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Set the number of products that you would like to display on homepage.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Number of product per row/carousel', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_NBRPRDROW',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Set the number of products in carousel or per row.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Category from which to pick products to be displayed', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_CATEGORY',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Choose the category ID of the products that you would like to display on homepage.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Randomly display featured products', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_RANDOMIZE',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Enable if you wish the products to be displayed randomly.', array(), 'Modules.CarouselProducts.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', array(), 'Admin.Global'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', array(), 'Admin.Global'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Speed', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_SPEED',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Animation speed in milliseconds.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Infinite', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_INFINITE',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Infinite loop sliding.', array(), 'Modules.CarouselProducts.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', array(), 'Admin.Global'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', array(), 'Admin.Global'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Slides to scroll', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_TOSCROLL',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Number of slides to scroll.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Dots', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_DOTS',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Show dot indicators.', array(), 'Modules.CarouselProducts.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', array(), 'Admin.Global'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', array(), 'Admin.Global'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Center mode', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_CENTERMODE',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Enables centered view with partial prev/next slides. Use with odd numbered slidesToShow counts.', array(), 'Modules.CarouselProducts.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', array(), 'Admin.Global'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', array(), 'Admin.Global'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Auto play', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_AUTOPLAY',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Enables Autoplay.', array(), 'Modules.CarouselProducts.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', array(), 'Admin.Global'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', array(), 'Admin.Global'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Classes contentainer product', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_CLASSES',
                        'class' => 'fixed-width-xl',
                        'desc' => $this->trans('In case that you dont carousel effect this classes set in container product.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Products in xs devices', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_XSSS',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Number of products to show in extra small devices.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Slides in xs devices', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_XSSC',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Number of slides to scroll in extra small devices.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Products in sm devices', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_SMSS',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Number of products to show in small devices.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Slides in sm devices', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_SMSC',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Number of slides to scroll in small devices.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Products in md devices', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_MDSS',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Number of products to show in medium devices.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Slides in md devices', array(), 'Modules.CarouselProducts.Admin'),
                        'name' => 'CAROUSELPRODUCTS_MDSC',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Number of slides to scroll in medium devices.', array(), 'Modules.CarouselProducts.Admin'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                ),
            ),
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCarouselProducts';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'CAROUSELPRODUCTS_NUMBER' => Tools::getValue('CAROUSELPRODUCTS_NUMBER', (int) Configuration::get('CAROUSELPRODUCTS_NUMBER')),
            'CAROUSELPRODUCTS_CATEGORY' => Tools::getValue('CAROUSELPRODUCTS_CATEGORY', (int) Configuration::get('CAROUSELPRODUCTS_CATEGORY')),
            'CAROUSELPRODUCTS_RANDOMIZE' => Tools::getValue('CAROUSELPRODUCTS_RANDOMIZE', (bool) Configuration::get('CAROUSELPRODUCTS_RANDOMIZE')),
            'CAROUSELPRODUCTS_NBRPRDROW' => Tools::getValue('CAROUSELPRODUCTS_NBRPRDROW', (int) Configuration::get('CAROUSELPRODUCTS_NBRPRDROW')),
            'CAROUSELPRODUCTS_CAROUSEL' => Tools::getValue('CAROUSELPRODUCTS_CAROUSEL', (bool) Configuration::get('CAROUSELPRODUCTS_CAROUSEL')),
            'CAROUSELPRODUCTS_INFINITE' => Tools::getValue('CAROUSELPRODUCTS_INFINITE', (bool) Configuration::get('CAROUSELPRODUCTS_INFINITE')),
            'CAROUSELPRODUCTS_DOTS' => Tools::getValue('CAROUSELPRODUCTS_DOTS', (bool) Configuration::get('CAROUSELPRODUCTS_DOTS')),
            'CAROUSELPRODUCTS_CENTERMODE' => Tools::getValue('CAROUSELPRODUCTS_CENTERMODE', (bool) Configuration::get('CAROUSELPRODUCTS_CENTERMODE')),
            'CAROUSELPRODUCTS_AUTOPLAY' => Tools::getValue('CAROUSELPRODUCTS_AUTOPLAY', (bool) Configuration::get('CAROUSELPRODUCTS_AUTOPLAY')),
            'CAROUSELPRODUCTS_SPEED' => Tools::getValue('CAROUSELPRODUCTS_SPEED', (int) Configuration::get('CAROUSELPRODUCTS_SPEED')),
            'CAROUSELPRODUCTS_TOSCROLL' => Tools::getValue('CAROUSELPRODUCTS_TOSCROLL', (int) Configuration::get('CAROUSELPRODUCTS_TOSCROLL')),
            'CAROUSELPRODUCTS_CLASSES' => Tools::getValue('CAROUSELPRODUCTS_CLASSES', (string) Configuration::get('CAROUSELPRODUCTS_CLASSES')),
            'CAROUSELPRODUCTS_XSSS' => Tools::getValue('CAROUSELPRODUCTS_XSSS', (int) Configuration::get('CAROUSELPRODUCTS_XSSS')),
            'CAROUSELPRODUCTS_XSSC' => Tools::getValue('CAROUSELPRODUCTS_XSSC', (int) Configuration::get('CAROUSELPRODUCTS_XSSC')),
            'CAROUSELPRODUCTS_SMSS' => Tools::getValue('CAROUSELPRODUCTS_SMSS', (int) Configuration::get('CAROUSELPRODUCTS_SMSS')),
            'CAROUSELPRODUCTS_SMSC' => Tools::getValue('CAROUSELPRODUCTS_SMSC', (int) Configuration::get('CAROUSELPRODUCTS_SMSC')),
            'CAROUSELPRODUCTS_MDSS' => Tools::getValue('CAROUSELPRODUCTS_MDSS', (int) Configuration::get('CAROUSELPRODUCTS_MDSS')),
            'CAROUSELPRODUCTS_MDSC' => Tools::getValue('CAROUSELPRODUCTS_MDSC', (int) Configuration::get('CAROUSELPRODUCTS_MDSC')),
        );
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('carouselproducts'))) {
            $variables = $this->getWidgetVariables($hookName, $configuration);

            if (empty($variables)) {
                return false;
            }

            $this->smarty->assign($variables);
        }

        return $this->fetch($this->templateFile, $this->getCacheId('carouselproducts'));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $products = $this->getProducts();

        $classes = ""; 
        if(!$this->getConfigFieldsValues()['CAROUSELPRODUCTS_CAROUSEL']){
            $classes = $this->getConfigFieldsValues()['CAROUSELPRODUCTS_CLASSES'];
        }

        if (!empty($products)) {
            return array(
                'products' => $products,
                'allProductsLink' => Context::getContext()->link->getCategoryLink($this->getConfigFieldsValues()['CAROUSELPRODUCTS_CATEGORY']),
                'number_products_row' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_NBRPRDROW'],
                'carousel' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_CAROUSEL'],
                'infinite' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_INFINITE'],
                'speed' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_SPEED'],
                'toscroll' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_TOSCROLL'],
                'dots' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_DOTS'],
                'centermode' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_CENTERMODE'],
                'autoplay' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_AUTOPLAY'],
                'classes' => $classes,
                'xsss' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_XSSS'],
                'xssc' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_XSSC'],
                'smss' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_SMSS'],
                'smsc' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_SMSC'],
                'mdss' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_MDSS'],
                'mdsc' => $this->getConfigFieldsValues()['CAROUSELPRODUCTS_MDSC'],
            );
        }
        return false;
    }

    protected function getProducts()
    {
        $category = new Category((int) Configuration::get('CAROUSELPRODUCTS_CATEGORY'));

        $searchProvider = new CategoryProductSearchProvider(
            $this->context->getTranslator(),
            $category
        );

        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();

        $nProducts = Configuration::get('CAROUSELPRODUCTS_NUMBER');
        if ($nProducts < 0) {
            $nProducts = 12;
        }

        $query->setResultsPerPage($nProducts)->setPage(1);

        if (Configuration::get('CAROUSELPRODUCTS_RANDOMIZE')) {
            $query->setSortOrder(SortOrder::random());
        } else {
            $query->setSortOrder(new SortOrder('product', 'position', 'asc'));
        }

        $result = $searchProvider->runQuery($context,$query);

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products_for_template = [];

        foreach ($result->getProducts() as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $products_for_template;
    }
}
