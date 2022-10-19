<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
/*
* 2007-2015 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class StarPay extends PaymentModule
{
    const FLAG_DISPLAY_PAYMENT_INVITE = 'BANK_WIRE_PAYMENT_INVITE';

    protected $_html = '';
    protected $_postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'starpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.7.1';
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
        $this->author = 'Starpay';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->limited_currencies = array("AED","AFN","ALL","AMD","AOA","ANG","ARS","AUD","AWG","AZN","BAM","BBD","BDT","BGN","BHD","BIF","BMD","BND","BOB","BOV","BRL","BSD","BTN","BWP","BYR","BZD","CAD","CDF","CHF","CLP","CNY","COP","CRC","CUP","CUC","CVE","CZK","DJF","DKK","DOP","DZD","EUR","EGP","ERN","ETB","FJD","FKP","GBP","GEL","GHS","GIP","GMD","GNF","GTQ","GYD","HKD","HNL","HRK","HTG","HUF","IDR","ILS","INR","IQD","IRR","ISK","JMD","JOD","JPY","KES","KGS","KHR","KMF","KRW","KPW","KWD","KYD","KZT","LAK","LBP","LKR","LRD","LSL","LYD","MAD","MDL","MGA","MRO","MKD","MMK","MNT","MOP","MUR","MVR","MWK","MXN","MYR","MZN","NAD","NGN","NIO","NOK","NPR","NZD","OMR","PAB","PEN","PGK","PHP","PKR","PLN","PYG","QAR","RON","RSD","RUB","RWF","SAR","SBD","SCR","SDG","SEK","SGD","SHP","SLL","SOS","SRD","SSP","STD","SYP","SZL","THB","TJS","TMT","TND","TOP","TRY","TTD","TWD","TZS","UAH","UGX","USD","UYU","UZS","VEF","VND","VUV","WST","XAF","XCD","XOF","XPF","XSU","YER","ZAR","ZMW");

        $this->displayName = $this->trans('StarPay', array(), 'Modules.StarPay.Admin');
        $this->description = $this->trans('Accept payments for your products via Wechat and AlipayPlus Pay.', array(), 'Modules.StarPay.Admin');
        $this->confirmUninstall = $this->trans('Are you sure about removing these details?', array(), 'Modules.StarPay.Admin');
        if (!isset($this->owner) || !isset($this->details) || !isset($this->address)) {
            $this->warning = $this->trans('Account owner and account details must be configured before using this module.', array(), 'Modules.StarPay.Admin');
        }
        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->trans('No currency has been set for this module.', array(), 'Modules.StarPay.Admin');
        }
    }

    public function install()
    {
        Configuration::updateValue(self::FLAG_DISPLAY_PAYMENT_INVITE, true);
        if (!parent::install() || !$this->registerHook('paymentReturn') || !$this->registerHook('paymentOptions')) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
      if (!Configuration::deleteByName('STARPAY_MODULO_ACCESS_ID')
        || !Configuration::deleteByName('STARPAY_MODULO_MERCHANT_ID')
        || !Configuration::deleteByName('STARPAY_MODULO_STORE_NO')
        || !Configuration::deleteByName('STARPAY_APP_PRIVATE_KEY')
        || !parent::uninstall())
        return false;
      return true;
    }

    protected function _postValidation()
    {

    }

    protected function postProcess()
    {
        if (Tools::isSubmit('starpay')) {
            $access_id = Tools::getValue('access_id');
            $merchantAccessNo = Tools::getValue('merchantAccessNo');
            $appPrivateKey = Tools::getValue('app_private_key');
            $storeNo = Tools::getValue('storeNo');
            Configuration::updateValue('STARPAY_MODULO_ACCESS_ID', $access_id);
            Configuration::updateValue('STARPAY_MODULO_MERCHANT_ID', $merchantAccessNo);
            Configuration::updateValue('STARPAY_MODULO_STORE_NO', $storeNo);
            Configuration::updateValue('STARPAY_APP_PRIVATE_KEY', $appPrivateKey);
        }
        return $this->displayConfirmation($this->l('Updated Successfully'));
    }

    protected function _displayStarPay()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        global $cookie;
        if(!$cookie->isLogged()){
         return;
        }
        $cart = $this->context->cart;

    		$customer = new Customer($cart->id_customer);
   		if(False) {
   			$this->smarty->assign(array(
   				'nbProducts' => $cart->nbProducts(),
   				'cust_currency' => $cart->id_currency,
   				'currencies' => $this->getCurrency($cart->id_currency),
   				'total' => $cart->getOrderTotal(true, Cart::BOTH),
   				'isoCode' => $this->context->language->iso_code,
   				'this_path' => $this->getPathUri(),
   				'this_path_cheque' => $this->getPathUri(),
   				'HOOK_LEFT_COLUMN' => '',
   				'HOOK_RIGTH_COLUMN' => '',
   				'url' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/cart.php?cart_id='.$cart->id.'&customer='.$customer->secure_key,
   				'url_phone' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/getpaydata.php',
   				// 'url_order' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'index.php?controller=history',
   				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
   				'qr_code' => $this->getBtnSubmit(),
   				'mobile' => True,
   			));
   		}
   		else{
   			$this->smarty->assign(array(
   				'nbProducts' => $cart->nbProducts(),
   				'cust_currency' => $cart->id_currency,
   				'currencies' => $this->getCurrency($cart->id_currency),
   				'total' => $cart->getOrderTotal(true, Cart::BOTH),
   				'isoCode' => $this->context->language->iso_code,
   				'this_path' => $this->getPathUri(),
   				'this_path_cheque' => $this->getPathUri(),
   				'HOOK_LEFT_COLUMN' => '',
   				'HOOK_RIGTH_COLUMN' => '',
   				'url' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/cart.php?cart_id='.$cart->id.'&customer='.$customer->secure_key,
   				'url_phone' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/getpaydata.php',
   				// 'url_order' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'index.php?controller=history',
   				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
   				'qr_code_wechat' => $this->getQRCodeWeChat(),
   				'qr_code_alipayplus' => $this->getQRCodeAliPayPlus(),
   				'mobile' => False,
   			));
   		}
        $newOptionAliPay = new PaymentOption();
        $newOptionAliPay->setModuleName($this->name)
                ->setCallToActionText($this->l('AlipayPlus Pay'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                ->setAdditionalInformation($this->fetch('module:starpay/views/templates/hook/payment_execution_alipayplus.tpl'));
        $newOptionWeChat = new PaymentOption();
        $newOptionWeChat->setModuleName($this->name)
                ->setCallToActionText($this->l('WeChat Pay'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                ->setAdditionalInformation($this->fetch('module:starpay/views/templates/hook/payment_execution_wechat.tpl'));
        $payment_options = [
            $newOptionAliPay, $newOptionWeChat,
        ];
        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active || !Configuration::get(self::FLAG_DISPLAY_PAYMENT_INVITE)) {
            return;
        }

        $state = $params['order']->getCurrentState();
        if (
            in_array(
                $state,
                array(
                    Configuration::get('PS_OS_BANKWIRE'),
                    Configuration::get('PS_OS_OUTOFSTOCK'),
                    Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'),
                )
        )) {
            $bankwireOwner = $this->owner;
            if (!$bankwireOwner) {
                $bankwireOwner = '___________';
            }

            $bankwireDetails = Tools::nl2br($this->details);
            if (!$bankwireDetails) {
                $bankwireDetails = '___________';
            }

            $bankwireAddress = Tools::nl2br($this->address);
            if (!$bankwireAddress) {
                $bankwireAddress = '___________';
            }

            $this->smarty->assign(array(
                'shop_name' => $this->context->shop->name,
                'total' => Tools::displayPrice(
                    $params['order']->getOrdersTotalPaid(),
                    new Currency($params['order']->id_currency),
                    false
                ),
                'bankwireDetails' => $bankwireDetails,
                'bankwireAddress' => $bankwireAddress,
                'bankwireOwner' => $bankwireOwner,
                'status' => 'ok',
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
        } else {
            $this->smarty->assign(
                array(
                    'status' => 'failed',
                    'contact_url' => $this->context->link->getPageLink('contact', true),
                )
            );
        }

        return $this->fetch('module:ps_wirepayment/views/templates/hook/payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getQRCodeWeChat()
    {
  		  require_once 'lib/StarpayUtil.php';

    		$cart = $this->context->cart;

    		$this->smarty->assign('module_dir', $this->_path);

    		$totalAmont = (float)($cart->getOrderTotal(true, Cart::BOTH)*100);

    		//Wechat interface
    		$gatewayurl="https://api.starpayes.com/aps-gateway/entry.do";

    		$bgRetUrl = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/notify.php';

    		// variables de retorno
    		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
    		$currency_contex = $this->context->currency;
    		$customer = new Customer($cart->id_customer);

    		$timestamp = date('Y-m-d H:i:s');
        // $timestamp = date('2019-09-20 21:33:05');
    		$orderID = $this->generateRandomString().$this->generateRandomString()."&".$cart->id."&".$currency_contex->id."&".$customer->secure_key;
    		$currency = new Currency((int)$cart->id_currency);
    		$currency = $currency->iso_code;
    		$access_id = Configuration::get('STARPAY_MODULO_ACCESS_ID');
    		$merchantAccessNo = Configuration::get('STARPAY_MODULO_MERCHANT_ID');
    		$storeNo = Configuration::get('STARPAY_MODULO_STORE_NO');
    		$app_private_key = Configuration::get('STARPAY_APP_PRIVATE_KEY');
    		$subject = str_replace( '"' , '' , Configuration::get('PS_SHOP_NAME'));

    		$config = array (
    			//id assigned by por Starpay
    			'access_id' => $access_id,
    			//action type(see documentation)
    			'type' => "2003",
    			//default version is 1.0
    			'version' => "1.0",
    			//timestamp format yyyy-MM-dd HH:mm:ss
    			'timestamp' => $timestamp,
    			//see documentation for how to set up the content field
    			'content' => "{merchantAccessNo:\"$merchantAccessNo\", orderNo: \"$orderID\", orderAmt: $totalAmont, subject: \"$subject\", currency: \"$currency\", bgRetUrl: \"$bgRetUrl\", storeNo: \"$storeNo\"}",
    			//for now we are 100% exclusive with JSON.
    			'format'=>"JSON",
    			//See "message signature" in the documentation
    			'sign' => ""
    		);

    		$clsName="StarpayUtil";
    		$ret = $clsName::SignData($config, $app_private_key);
    		$config["sign"]=$ret;

    		$result = $clsName::curl($gatewayurl,$config);

    		$array_result = json_decode($result, true);
    		if ($array_result['code'] != 'R000') {
    			$png = false;
    		}
    		else{
    			$content_result = json_decode($array_result['content'], true);
    			$url = $content_result['coreUrl'];
    			$png = $this->createTempQrcode($url);
    		}

    		return $png;
    }
    
    public function getQRCodeAliPayPlus()
    {
  		  require_once 'lib/StarpayUtil.php';

    		$cart = $this->context->cart;

    		$this->smarty->assign('module_dir', $this->_path);

    		$totalAmont = (float)($cart->getOrderTotal(true, Cart::BOTH)*100);

    		//AlipayPlus interface
    		$gatewayurl="https://api.starpayes.com/aps-gateway/entry.do";

    		$bgRetUrl = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/notify.php';

    		// variables de retorno
    		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
    		$currency_contex = $this->context->currency;
    		$customer = new Customer($cart->id_customer);

    		$timestamp = date('Y-m-d H:i:s');
        // $timestamp = date('2019-09-20 21:33:05');
    		$orderID = $this->generateRandomString()."&".$this->generateRandomString()."&".$cart->id."&".$currency_contex->id."&".$customer->secure_key;
    		$currency = new Currency((int)$cart->id_currency);
    		$currency = $currency->iso_code;
    		$access_id = Configuration::get('STARPAY_MODULO_ACCESS_ID');
    		$merchantAccessNo = Configuration::get('STARPAY_MODULO_MERCHANT_ID');
    		$storeNo = Configuration::get('STARPAY_MODULO_STORE_NO');
    		$app_private_key = Configuration::get('STARPAY_APP_PRIVATE_KEY');
    		$subject = str_replace( '"' , '' , Configuration::get('PS_SHOP_NAME'));

    		$config = array (
    			//id assigned by por Starpay
    			'access_id' => $access_id,
    			//transaction type(see documentation)
                'type' => "2013",
                //default version is 1.0
                'version' => "1.0",
                //timestamp format yyyy-MM-dd HH:mm:ss
                'timestamp' => $timestamp,
                //see documentation for how to set up the content field
                'content' => "{retUrl:\"$bgRetUrl\", channelType:\"ALIPAYPLUS\", merchantAccessNo:\"$merchantAccessNo\", orderNo: \"$orderID\", orderAmt: $totalAmont, subject: \"$subject\", currency: \"$currency\", bgRetUrl: \"$bgRetUrl\", storeNo: \"$storeNo\"}",
                //for now we are 100% exclusive with JSON.
                'format'=>"JSON",
    			//See "message signature" in the documentation
    			'sign' => ""
    		);

    		$clsName="StarpayUtil";
    		$ret = $clsName::SignData($config, $app_private_key);
    		$config["sign"]=$ret;

    		$result = $clsName::curl($gatewayurl,$config);

    		$array_result = json_decode($result, true);
    		if ($array_result['code'] != 'R000') {
    			$png = false;
    		}
    		else{
    			$content_result = json_decode($array_result['content'], true);
    			$url = $content_result['coreUrl'];
    			$png = $this->createTempQrcode($url);
    		}

    		return $png;
    }

    public function getBtnSubmit()
  	{
  		require_once 'lib/StarpayUtil.php';

  		$cart = $this->context->cart;

  		$this->smarty->assign('module_dir', $this->_path);

  		$totalAmont = (float)($cart->getOrderTotal(true, Cart::BOTH)*100);

  		$bgRetUrl = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/notify.php';

  		// variables de retorno
  		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
  		$currency_contex = $this->context->currency;
  		$customer = new Customer($cart->id_customer);

  		$timestamp = date('Y-m-d H:i:s');
  		//$timestamp = date('2019-09-26 13:55:05');
  		$orderID = $this->generateRandomString()."&".$cart->id."&".$currency_contex->id."&".$customer->secure_key;
  		$currency = new Currency((int)$cart->id_currency);
  		$currency = $currency->iso_code;
  		$access_id = Configuration::get('STARPAY_MODULO_ACCESS_ID');
  		$merchantAccessNo = Configuration::get('STARPAY_MODULO_MERCHANT_ID');
  		$storeNo = Configuration::get('STARPAY_MODULO_STORE_NO');
  		$app_private_key = Configuration::get('STARPAY_APP_PRIVATE_KEY');
  		$subject = str_replace( '"' , '' , Configuration::get('PS_SHOP_NAME'));

  		$config = array (
  			//id assigned by por Starpay
  			'access_id' => $access_id,
  			//transaction type(see documentation)
  			'type' => "2013",
  			//default version is 1.0
  			'version' => "1.0",
  			//timestamp format yyyy-MM-dd HH:mm:ss
  			'timestamp' => $timestamp,
  			//see documentation for how to set up the content field
  			'content' => "{retUrl:\"$bgRetUrl\", channelType:\"ALIPAYPLUS\", merchantAccessNo:\"$merchantAccessNo\", orderNo: \"$orderID\", orderAmt: $totalAmont, subject: \"$subject\", currency: \"$currency\", bgRetUrl: \"$bgRetUrl\", storeNo: \"$storeNo\"}",
  			//for now we are 100% exclusive with JSON.
  			'format'=>"JSON",
  			//See "message signature" in the documentation
  			'sign' => ""
  		);

  		$clsName="StarpayUtil";
  		$ret = $clsName::SignData($config, $app_private_key);
  		$config["sign"]=$ret;

  		// return json_encode($config);
  		return $config;

  	}

    // Random generate
    function generateRandomString($length = 1) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

  	public function createTempQrcode($data)
    {
        require_once 'lib/phpqrcode.php';
        $object = new \QRcode();
        $errorCorrectionLevel = 'L';    // Error logging level
        $matrixPointSize = 5;            //generate image size
        ob_start();
        $returnData = $object->png($data,false,$errorCorrectionLevel, $matrixPointSize, 2);
        $imageString = base64_encode(ob_get_contents());
        ob_end_clean();
        return "data:image/png;base64,".$imageString;
    }

    public function renderForm()
  	{
  		$helper = new HelperForm();
          $helper->module = $this;
          $helper->name_controller = $this->name;
          $helper->identifier = $this->identifier;
          $helper->token = Tools::getAdminTokenLite('AdminModules');
          $helper->languages = $this->context->controller->getLanguages();
          $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
          $helper->default_form_language = $this->context->controller->default_form_language;
          $helper->allow_employee_form_lang = $this->context->controller->allow_employee_form_lang;
          $helper->title = $this->displayName;

          $helper->submit_action = 'starpay';
          $helper->fields_value['app_private_key'] = Configuration::get('STARPAY_APP_PRIVATE_KEY');
          $helper->fields_value['access_id'] = Configuration::get('STARPAY_MODULO_ACCESS_ID');
          $helper->fields_value['merchantAccessNo'] = Configuration::get('STARPAY_MODULO_MERCHANT_ID');
          $helper->fields_value['storeNo'] = Configuration::get('STARPAY_MODULO_STORE_NO');

          $this->form[0] = array(
              'form' => array(
                  'legend' => array(
                  	'title' => $this->displayName,
  					        'a' => 'http://starpay.es'
                  ),
                  'input' => array(
                    	array(
  						'type' => 'text',
  						'label' => $this->l('Access ID'),
  						'desc' => $this->l('Access ID provided by Starpay'),
  						'hint' => $this->l('A123121213'),
  						'name' => 'access_id',
  						'lang' => false,
                     	),
  				    array(
  						'type' => 'text',
  						'label' => $this->l('Merchant Access Number'),
  						'desc' => $this->l('Merchant Access Number provided by Starpay'),
  						'hint' => $this->l('B553121213'),
  						'name' => 'merchantAccessNo',
  						'lang' => false,
  					),
  					array(
  						'type' => 'text',
  						'label' => $this->l('Store Number'),
  						'desc' => $this->l('Store Number'),
  						'hint' => $this->l('000'),
  						'name' => 'storeNo',
  						'lang' => false,
  					  ),
                     	array(
  						'type' => 'textarea',
  						'label' => $this->l('Private Key'),
  						'desc' => $this->l('Private Key'),
  						'hint' => $this->l('-----BEGIN RSA PRIVATE KEY-----'),
  						'name' => 'app_private_key',
  						'lang' => false,
                      ),
                  ),
                  'submit' => array(
                  	'title' => $this->l('Save')
                  )
              )
          );
          return $helper->generateForm($this->form);
  	}
}
