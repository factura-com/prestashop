<?php
/**
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Facturapuntocom extends Module
{
    public $keyapi;
    public $secretkey;
    public $serie;
    public $days;
    public $urlapi;
    public $urlapi40;
    public $urlapi_dev;
    public $urlapi40_dev;
    public $urlpub;
    public $urlpub_dev;
    public $encabezado;
    public $color_fields;
    public $send_mail;
    public $u_cfdi;
    public $checkbox_dev;

    protected static $factura_fields = array(
        'FACTURA_KEYAPI',
        'FACTURA_SECRETAPI',
        'FACTURA_SERIE',
        'FACTURA_DAYS',
        'FACTURA_ENCABEZADO',
        'FACTURA_COLORS',
        'FACTURA_SENDEMAIL',
        'FACTURA_USOCFDI',
        'FACTURA_SANDBOX',
    );

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'facturapuntocom';
        $this->tab = 'billing_invoicing';
        $this->version = '4.0.0';
        $this->author = 'Factura.com';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Factura.com');
        $this->description = $this->l('This module allows you to create your invoices through the service of factura.com');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the plugin? Your invoices are secure on factura.com but you might not be able to keep your configuration.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $config = Configuration::getMultiple(array('FACTURA_KEYAPI', 'FACTURA_SECRETAPI',
        'FACTURA_SERIE', 'FACTURA_DAYS', 'FACTURA_ENCABEZADO', 'FACTURA_COLORS', 'FACTURA_SENDEMAIL', 'FACTURA_USOCFDI','FACTURA_SANDBOX',));

        if (isset($config['FACTURA_KEYAPI'])) {
            $this->keyapi = $config['FACTURA_KEYAPI'];
        }
        if (isset($config['FACTURA_SECRETAPI'])) {
            $this->keysecret = $config['FACTURA_SECRETAPI'];
        }
        if (isset($config['FACTURA_SERIE'])) {
            $this->serie = $config['FACTURA_SERIE'];
        }
        if (isset($config['FACTURA_ENCABEZADO'])) {
            $this->encabezado = $config['FACTURA_ENCABEZADO'];
        }
        if (isset($config['FACTURA_COLORS'])) {
            $this->color_fields = $config['FACTURA_COLORS'];
        }
        if (isset($config['FACTURA_SENDEMAIL'])) {
            $this->send_mail = $config['FACTURA_SENDEMAIL'];
        }
        if (isset($config['FACTURA_DAYS'])) {
            $this->days = $config['FACTURA_DAYS'];
        }
        if (isset($config['FACTURA_USOCFDI'])) {
            $this->u_cfdi = $config['FACTURA_USOCFDI'];
        }
        if (isset($config['FACTURA_SANDBOX'])) {
            $this->checkbox_dev = $config['FACTURA_SANDBOX'];
        }

        //urls_producción
        $this->urlapi = 'https://api.factura.com/v1/';
        $this->urlapi40 = 'https://api.factura.com/v4/cfdi40/';
        $this->urlpub = 'https://api.factura.com/publica/cfdi40/';

        //urls_sandbox
        $this->urlapi_dev = 'https://sandbox.factura.com/api/v1/';
        $this->urlapi40_dev = 'https://sandbox.factura.com/api/v4/cfdi40/';
        $this->urlpub_dev = 'https://sandbox.factura.com/api/publica/cfdi40/';
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('FACTURAPUNTOCOM_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('displayFooter') &&
            $this->installTab();
    }

    public function uninstall()
    {
        Configuration::deleteByName('FACTURAPUNTOCOM_LIVE_MODE');

        return parent::uninstall() &&
            $this->uninstallTab();
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all)
            && $this->installTab()
        ;
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all)
            && $this->uninstallTab()
        ;
    }

    private function installTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminFactura');
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = 'AdminFactura';
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Factura.com', array(), 'Modules.Facturapuntocom.Admin', $lang['locale']);
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('SELL');
        $tab->module = $this->name;
        $tab->icon = 'extension';

        return $tab->save();
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminFacturaController');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
    
        if (Tools::isSubmit('submit' . $this->name)) {
            $errors = $this->postProcess();
    
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $output .= $this->displayError($error);
                }
            } else {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
    
        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
    
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
    
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
    
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
    
        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $uso_cfdi = array(
            array(
              'id_option' => 'G01',
              'name' => $this->l('Adquisición de mercancias'),
            ),
            array(
              'id_option' => 'G02',
              'name' => $this->l('Devoluciones, descuentos o bonificaciones'),
            ),
            array(
              'id_option' => 'G03',
              'name' => $this->l('Gastos en general'),
            ),
            array(
              'id_option' => 'I01',
              'name' => $this->l('Construcciones'),
            ),
            array(
              'id_option' => 'I02',
              'name' => $this->l('Mobilario y equipo de oficina por inversiones'),
            ),
            array(
              'id_option' => 'I03',
              'name' => $this->l('Equipo de transporte'),
            ),
            array(
              'id_option' => 'I04',
              'name' => $this->l('Equipo de computo y accesorios'),
            ),
            array(
              'id_option' => 'I05',
              'name' => $this->l('Dados, troqueles, moldes, matrices y herramental'),
            ),
            array(
              'id_option' => 'I06',
              'name' => $this->l('Comunicaciones telefónicas'),
            ),
            array(
              'id_option' => 'I07',
              'name' => $this->l('Comunicaciones satelitales'),
            ),
            array(
              'id_option' => 'I08',
              'name' => $this->l('Otra maquinaria y equipo'),
            ),
            array(
              'id_option' => 'D01',
              'name' => $this->l('Honorarios médicos, dentales y gastos hospitalarios'),
            ),
            array(
              'id_option' => 'D02',
              'name' => $this->l('Gastos médicos por incapacidad o discapacidad'),
            ),
            array(
              'id_option' => 'D03',
              'name' => $this->l('Gastos funerales'),
            ),
            array(
              'id_option' => 'D04',
              'name' => $this->l('Donativos'),
            ),
            array(
              'id_option' => 'D05',
              'name' => $this->l('Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)'),
            ),
            array(
              'id_option' => 'D06',
              'name' => $this->l('Aportaciones voluntarias al SAR'),
            ),
            array(
              'id_option' => 'D07',
              'name' => $this->l('Primas por seguros de gastos médicos'),
            ),
            array(
              'id_option' => 'D08',
              'name' => $this->l('Gastos de transportación escolar obligatoria'),
            ),
            array(
              'id_option' => 'D09',
              'name' => $this->l('Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones'),
            ),
            array(
              'id_option' => 'D10',
              'name' => $this->l('Pagos por servicios educativos (colegiaturas)'),
            ),
            array(
              'id_option' => 'S01',
              'name' => $this->l('Sin efectos fiscales'),
            ),
        );

        $days_billing = [['id_option' => 0, 'name' => 'No aplicar restricción']];
        for ($i = 1; $i < 31; $i++) {
            $days_billing[] = ['id_option' => $i, 'name' => $i . ' días'];
        }

        $options = array(
            array(
              'id_option' => 0,
              'name' => $this->l('Send mail manually from the admin dashboard.'),
            ),
            array(
              'id_option' => 1,
              'name' => $this->l('Send mail automatically when you create the invoice.'),
            ),
        );

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Factura.com module settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'name' => 'FACTURA_KEYAPI',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Secret Key'),
                        'name' => 'FACTURA_SECRETAPI',
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Sandbox'),
                        'name' => 'FACTURA_SANDBOX',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')]
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Billing Series'),
                        'name' => 'FACTURA_SERIE',
                        'required' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('CFDI use'),
                        'name' => 'FACTURA_USOCFDI',
                        'required' => true,
                        'desc' => $this->l('You must indicate what use the recipient of the invoice will give to it.'),
                        'options' => [
                            'query' => $uso_cfdi,
                            'id' => 'id_option',
                            'name' => 'name'
                        ]
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Choose the color of the fields in the invoice module.'),
                        'name' => 'FACTURA_COLORS',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Customer header information (optional)'),
                        'name' => 'FACTURA_ENCABEZADO',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Email delivery options'),
                        'name' => 'FACTURA_SENDEMAIL',
                        'required' => true,
                        'options' => [
                            'query' => $options,
                            'id' => 'id_option',
                            'name' => 'name'
                        ]
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Days allowed for billing (After the month is over)'),
                        'name' => 'FACTURA_DAYS',
                        'required' => true,
                        'options' => [
                            'query' => $days_billing,
                            'id' => 'id_option',
                            'name' => 'name'
                        ]
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ]
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'FACTURA_KEYAPI' => Configuration::get('FACTURA_KEYAPI', ''),
            'FACTURA_SECRETAPI' => Configuration::get('FACTURA_SECRETAPI', ''),
            'FACTURA_SANDBOX' => Configuration::get('FACTURA_SANDBOX', 0),
            'FACTURA_SERIE' => Configuration::get('FACTURA_SERIE', ''),
            'FACTURA_USOCFDI' => Configuration::get('FACTURA_USOCFDI', 'G03'),
            'FACTURA_COLORS' => Configuration::get('FACTURA_COLORS', '#000000'),
            'FACTURA_ENCABEZADO' => Configuration::get('FACTURA_ENCABEZADO', ''),
            'FACTURA_SENDEMAIL' => Configuration::get('FACTURA_SENDEMAIL', 0),
            'FACTURA_DAYS' => Configuration::get('FACTURA_DAYS', 0),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $errors = [];

        // Validaciones básicas
        if (!Validate::isGenericName(Tools::getValue('FACTURA_KEYAPI'))) {
            $errors[] = $this->l('Invalid API Key');
        }

        if (!Validate::isGenericName(Tools::getValue('FACTURA_SECRETAPI'))) {
            $errors[] = $this->l('Invalid Secret Key');
        }

        if (empty($errors)) {
            foreach ($this->getConfigFormValues() as $key => $value) {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }

        return $errors;
    }

    public function hookDisplayFooter($params)
    {
        $this->context->smarty->assign([
            'factura_link' => $this->context->link->getModuleLink($this->name, 'factura')
        ]);
    
        return $this->fetch('module:' . $this->name . '/views/templates/hook/footer.tpl');
    }

}
