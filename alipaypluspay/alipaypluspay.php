<?php
# @Author: Grupo Vermon 
# @Date:   01-06-2022
# @Email:  info@grupovermon.com
# @Last modified by:   mlopez
# @Last modified time: 01-06-2022

if (!defined('_PS_VERSION_'))
	exit;

class AlipayPlusPay extends PaymentModule
{
	protected $_html = '';
	protected $_postErrors = array();

	public $details;
	public $owner;
	public $address;
	public $extra_mail_vars;
	public function __construct()
	{
		$this->name = 'alipaypluspay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'Grupo Vermon';
		$this->controllers = array('payment', 'validation');
		$this->is_eu_compatible = 1;

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->limited_currencies = array("AED","AFN","ALL","AMD","AOA","ANG","ARS","AUD","AWG","AZN","BAM","BBD","BDT","BGN","BHD","BIF","BMD","BND","BOB","BOV","BRL","BSD","BTN","BWP","BYR","BZD","CAD","CDF","CHF","CLP","CNY","COP","CRC","CUP","CUC","CVE","CZK","DJF","DKK","DOP","DZD","EUR","EGP","ERN","ETB","FJD","FKP","GBP","GEL","GHS","GIP","GMD","GNF","GTQ","GYD","HKD","HNL","HRK","HTG","HUF","IDR","ILS","INR","IQD","IRR","ISK","JMD","JOD","JPY","KES","KGS","KHR","KMF","KRW","KPW","KWD","KYD","KZT","LAK","LBP","LKR","LRD","LSL","LYD","MAD","MDL","MGA","MRO","MKD","MMK","MNT","MOP","MUR","MVR","MWK","MXN","MYR","MZN","NAD","NGN","NIO","NOK","NPR","NZD","OMR","PAB","PEN","PGK","PHP","PKR","PLN","PYG","QAR","RON","RSD","RUB","RWF","SAR","SBD","SCR","SDG","SEK","SGD","SHP","SLL","SOS","SRD","SSP","STD","SYP","SZL","THB","TJS","TMT","TND","TOP","TRY","TTD","TWD","TZS","UAH","UGX","USD","UYU","UZS","VEF","VND","VUV","WST","XAF","XCD","XOF","XPF","XSU","YER","ZAR","ZMW");

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('AlipayPlus Pay');
		$this->description = $this->l('Accept payments for your products via AlipayPlus Pay.');
		$this->confirmUninstall = $this->l('Are you sure about removing these details?');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');

	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || ! $this->registerHook('displayPaymentEU') || !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('ALIPAYPLUSPAY_MODULO_ACCESS_ID')
			|| !Configuration::deleteByName('ALIPAYPLUSPAY_MODULO_MERCHANT_ID')
			|| !Configuration::deleteByName('ALIPAYPLUSPAY_MODULO_STORE_NO')
			|| !Configuration::deleteByName('BANK_WIRE_OWNER')
			|| !Configuration::deleteByName('ALIPAYPLUSPAY_APP_PRIVATE_KEY')
			|| !parent::uninstall())
			return false;
		return true;
	}

	protected function postProcess()
	{
		if (Tools::isSubmit('alipaypluspay')) {
            $access_id = Tools::getValue('access_id');
            $merchantAccessNo = Tools::getValue('merchantAccessNo');
			$appPrivateKey = Tools::getValue('app_private_key');
            $storeNo = Tools::getValue('storeNo');
            Configuration::updateValue('ALIPAYPLUSPAY_MODULO_ACCESS_ID', $access_id);
            Configuration::updateValue('ALIPAYPLUSPAY_MODULO_MERCHANT_ID', $merchantAccessNo);
			Configuration::updateValue('ALIPAYPLUSPAY_MODULO_STORE_NO', $storeNo);
            Configuration::updateValue('ALIPAYPLUSPAY_APP_PRIVATE_KEY', $appPrivateKey);
            return $this->displayConfirmation($this->l('Updated Successfully'));
        }
	}

	protected function _displayAlipayPlusPay()
	{
		return $this->display(__FILE__, 'infos.tpl');
	}

	public function getContent()
	{
		return $this->postProcess() . $this->renderForm();
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;

		$cart = $this->context->cart;
		$currency = new Currency((int)$cart->id_currency);
		if (in_array($currency->iso_code, $this->limited_currencies) == true){
		  $this->smarty->assign(array(
  			'this_path' => $this->_path,
  			'this_path_bw' => $this->_path,
  			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
	  		));
	  		return $this->display(__FILE__, 'payment.tpl');
	    }
		else{
			return;
		}
	}

	public function hookDisplayPaymentEU($params)
	{
		if (!$this->active)
			return;

		if (!$this->checkCurrency($params['cart']))
			return;

		$payment_options = array(
			'cta_text' => $this->l('Pay by AlipayPlus Pay'),
			'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/alipaypluspay.png'),
			'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
		);

		return $payment_options;
	}

	public function hookPaymentReturn($params)
	{
		$this->smarty->assign(array(
			'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
			'bankwireDetails' => Tools::nl2br($this->details),
			'bankwireAddress' => Tools::nl2br($this->address),
			'bankwireOwner' => $this->owner,
			'status' => 'ok',
			'id_order' => $params['objOrder']->id
		));

		return $this->display(__FILE__, 'payment_return.tpl');
	}

	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
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

        $helper->submit_action = 'alipaypluspay';
        $helper->fields_value['app_private_key'] = Configuration::get('ALIPAYPLUSPAY_APP_PRIVATE_KEY');
        $helper->fields_value['access_id'] = Configuration::get('ALIPAYPLUSPAY_MODULO_ACCESS_ID');
        $helper->fields_value['merchantAccessNo'] = Configuration::get('ALIPAYPLUSPAY_MODULO_MERCHANT_ID');
        $helper->fields_value['storeNo'] = Configuration::get('ALIPAYPLUSPAY_MODULO_STORE_NO');

        $this->form[0] = array(
            'form' => array(
                'legend' => array(
                	'title' => $this->displayName,
					'a' => 'http://starpay.es'
                ),
                'input' => array(
                  	array(
						'type' => 'text',
						'label' => $this->l('Numero de acceso'),
						'desc' => $this->l('Access ID'),
						'hint' => $this->l('A123121213'),
						'name' => 'access_id',
						'lang' => false,
                   	),
				    array(
						'type' => 'text',
						'label' => $this->l('Número del comercio'),
						'desc' => $this->l('Merchant Access Number'),
						'hint' => $this->l('B553121213'),
						'name' => 'merchantAccessNo',
						'lang' => false,
					),
					array(
						'type' => 'text',
						'label' => $this->l('Número de Tienda'),
						'desc' => $this->l('Store Number'),
						'hint' => $this->l('000'),
						'name' => 'storeNo',
						'lang' => false,
					  ),
                   	array(
						'type' => 'textarea',
						'label' => $this->l('Clave Privada'),
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

	public function getBtnSubmit()
	{
		require_once 'lib/StarpayUtil.php';

		$cart = $this->context->cart;

		$this->smarty->assign('module_dir', $this->_path);

		$totalAmont = (float)($cart->getOrderTotal(true, Cart::BOTH)*100);
		$gatewayurl="https://api.starpayes.com/aps-gateway/entry.do";

		$bgRetUrl = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/notify.php';

		// variables de retorno
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$currency_contex = $this->context->currency;
		$customer = new Customer($cart->id_customer);

		$timestamp = date('Y-m-d H:i:s');
		// $timestamp = date('2019-06-19 03:06:05');
		$orderID = $this->generateRandomString()."&".$cart->id."&".$currency_contex->id."&".$customer->secure_key;
		$currency = new Currency((int)$cart->id_currency);
		$currency = $currency->iso_code;
		$access_id = Configuration::get('ALIPAYPLUSPAY_MODULO_ACCESS_ID');
		$merchantAccessNo = Configuration::get('ALIPAYPLUSPAY_MODULO_MERCHANT_ID');
		$storeNo = Configuration::get('ALIPAYPLUSPAY_MODULO_STORE_NO');
		$app_private_key = Configuration::get('ALIPAYPLUSPAY_APP_PRIVATE_KEY');
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

    public function getQRCode()
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
		// $timestamp = date('2019-06-19 03:06:05');
		$orderID = $this->generateRandomString()."&".$cart->id."&".$currency_contex->id."&".$customer->secure_key;
		$currency = new Currency((int)$cart->id_currency);
		$currency = $currency->iso_code;
		$access_id = Configuration::get('ALIPAYPLUSPAY_MODULO_ACCESS_ID');
		$merchantAccessNo = Configuration::get('ALIPAYPLUSPAY_MODULO_MERCHANT_ID');
		$storeNo = Configuration::get('ALIPAYPLUSPAY_MODULO_STORE_NO');
		$app_private_key = Configuration::get('ALIPAYPLUSPAY_APP_PRIVATE_KEY');
		$subject = str_replace( '"' , '' , Configuration::get('PS_SHOP_NAME'));

		$config = array (
			//id assigned by por Starpay
			'access_id' => $access_id,
			//transaction type(see documentation) OJO REVISAR
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

	public function isMobile()
	{
		require_once 'lib/MobileDetect.php';

		$detect = new MobileDetect();

		if ($detect->isMobile()) {
			return true;
		}
		else {
		    return false;
		}
	}
}
