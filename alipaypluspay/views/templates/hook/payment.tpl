{*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<style media="screen">
	p.payment_module a.alipaypluspay {
		background: url('{$this_path_bw}alipaypluspay.png') 0px 12px no-repeat #fbfbfb;
		background-size: 100px 60px;
	}
	p.payment_module a.alipaypluspay:after, p.payment_module a.alipaypluspay:after, p.payment_module a.cash:after {
		display: block;
		content: "\f054";
		position: absolute;
		right: 15px;
		margin-top: -11px;
		top: 50%;
		font-family: "FontAwesome";
		font-size: 25px;
		height: 22px;
		width: 14px;
		color: #777;
	}
</style>

<div class="row">
	<div class="col-xs-12">
		<p class="payment_module">
			<a class="alipaypluspay" href="{$link->getModuleLink('alipaypluspay', 'payment')|escape:'html'}" title="{l s='支付宝+' mod='alipaypluspay'}">
				<!-- <img src="{$this_path_bw}alipaypluspay.png" alt="{l s='Pay by bank wire' mod='alipaypluspay'}" width="86" height="49"/> -->
				{l s='支付宝+' mod='alipaypluspay'}
			</a>
		</p>
	</div>
</div>
