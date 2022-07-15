<?php

# @Author: Grupo Vermon 
# @Date:   01-06-2022
# @Email:  info@grupovermon.com
# @Last modified by:   mlopez
# @Last modified time: 01-06-2022

/**
 * @since 1.5.0
 */
class AlipayPlusPayPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');

		$customer = new Customer($cart->id_customer);

		$isMobile = $this->module->isMobile();
		if(true) {
			$this->context->smarty->assign(array(
				'nbProducts' => $cart->nbProducts(),
				'cust_currency' => $cart->id_currency,
				'currencies' => $this->module->getCurrency((int)$cart->id_currency),
				'total' => $cart->getOrderTotal(true, Cart::BOTH),
				'isoCode' => $this->context->language->iso_code,
				'this_path' => $this->module->getPathUri(),
				'this_path_cheque' => $this->module->getPathUri(),
				'HOOK_LEFT_COLUMN' => '',
				'HOOK_RIGTH_COLUMN' => '',
				'url' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/cart.php?cart_id='.$cart->id.'&customer='.$customer->secure_key,
				'url_phone' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/getpaydata.php',
				// 'url_order' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'index.php?controller=history',
				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
				'qr_code' => $this->module->getBtnSubmit(),
				'mobile' => $isMobile,
			));
		}
		else{
			$this->context->smarty->assign(array(
				'nbProducts' => $cart->nbProducts(),
				'cust_currency' => $cart->id_currency,
				'currencies' => $this->module->getCurrency((int)$cart->id_currency),
				'total' => $cart->getOrderTotal(true, Cart::BOTH),
				'isoCode' => $this->context->language->iso_code,
				'this_path' => $this->module->getPathUri(),
				'this_path_cheque' => $this->module->getPathUri(),
				'HOOK_LEFT_COLUMN' => '',
				'HOOK_RIGTH_COLUMN' => '',
				'url' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/cart.php?cart_id='.$cart->id.'&customer='.$customer->secure_key,
				'url_phone' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/getpaydata.php',
				// 'url_order' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'index.php?controller=history',
				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
				'qr_code' => $this->module->getQRCode(),
				'mobile' => $isMobile,
			));
		}



		$this->setTemplate('payment_execution.tpl');
	}
}
