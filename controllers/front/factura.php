<?php
/**
* 2025 FACTURA PUNTO COM SAPI de CV
*
* NOTICE OF LICENSE
*
* This source file is subject to License
* It is also available through the world-wide-web at this URL:
* http://factura.com
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to apps@factura.com so we can send you a copy immediately.
*
*  @author factura.com <apps@factura.com>
*  @copyright  2025 Factura Punto Com
*  International Registered Trademark & Property of factura.com
*/

class FacturapuntocomFacturaModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $hide_left_column = true;

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();
        $this->context = Context::getContext();
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(
            array(
              'hide_left_column' => $this->hide_left_column,
              'keyapi' => $this->module->keyapi,
              'keysecret' => $this->module->keysecret,
              'encabezado' => $this->module->encabezado,
              'colors' => $this->module->color_fields,
              'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
              'base_dir_ssl' => 'https://'.Configuration::get('PS_SHOP_DOMAIN_SSL').__PS_BASE_URI__,
              'ssl_active' => Configuration::get('PS_SSL_ENABLED')
            )
        );

        $this->setTemplate('module:facturapuntocom/views/templates/front/factura.tpl');
        
    }

    public function setMedia()
    {
        parent::setMedia();
        
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/progress.js');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/control.js');
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/progress.css');
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/factura.css');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/sweetalert2.all.min.js');
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/sweetalert2.min.css');
    }

}
