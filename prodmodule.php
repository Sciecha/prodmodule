<?php

if (!defined('_PS_VERSION_'))
    exit;

class ProdModule extends Module {

    private $templateFile;

    public function __construct()
    {
        $this->name = 'prodmodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'Ściecha';
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];

        $this->bootstrap = true;
        parent::__construct();

        $this->need_instance = 0;

        $this->displayName = $this->l('Display Products Module');
        $this->description = $this->l('Choose category and display products module');

        $this->templateFile = 'module:prodmodule/views/templates/hook/productlist.tpl';

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('displayHome')) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $output = '';
        $errors = array();

        if (Tools::isSubmit('submitCategory')) {
            $category = Tools::getValue('CATEGORY_PRODUCTLIST');
            if (!Validate::isInt($category) || $category <= 0) {
                $errors[] = $this->trans('The category ID is invalid. Please choose an existing category ID.', array(), 'Modules.Productsmodule.Admin');
            }

            if (isset($errors) && count($errors)) {
                $output = $this->displayError(implode('<br />', $errors));
            } else {
                Configuration::updateValue('CATEGORY_PRODUCTLIST', (int) $category);

                $output = $this->displayConfirmation($this->l('The settings have been updated.'));
            }
        }

        return $output.$this->renderForm();
    }

    public function  renderForm()
    {
        $categories = Category::getAllCategoriesName();

        $fields_form = array(
            'form' => array(
                'description' => $this->l('Choose category to list products of.'),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Category:'),
                        'name' => 'CATEGORY_PRODUCTLIST',
                        'required' => true,
                        'options' => array(
                            'query' => $categories,
                            'id' => 'id_category',
                            'name' => 'name'
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCategory';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues()
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'CATEGORY_PRODUCTLIST' => Tools::getValue('CATEGORY_PRODUCTLIST', (int) Configuration::get('CATEGORY_PRODUCTLIST'))
        );
    }

    protected function getProducts($category)
    {

        $category = new Category((int) $category);
        $category_products = $category->getProducts($this->context->language->id, 1, 100);

        return [
            'products' => $category_products,
            'category' => $category->getName()
        ];
    }

    public function hookDisplayHome()
    {
        $data = $this->getProducts(Configuration::get('CATEGORY_PRODUCTLIST'));
        $this->smarty->assign($data);
        return $this->display(__FILE__, 'productlist.tpl');
    }

    public function uninstall()
    {
        if (!parent::uninstall() || Configuration::deleteByName('CATEGORY_PRODUCTLIST')) {
            return false;
        }
        return true;
    }

}

