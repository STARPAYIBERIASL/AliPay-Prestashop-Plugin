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
<style>
    .exclusive_pay {
        /* padding: 5px; */
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        border-radius: 5px;
        /* position: relative; */
        /* display: block; */
        /* background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiP…dpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JhZCkiIC8+PC9zdmc+IA==); */
        background-size: 100%;
        position: relative;
        display: inline-block;
        padding: 5px 7px;
        border: 1px solid #36943e;
        font-weight: bold;
        color: white;
        background: #55c65e;
        cursor: pointer;
        white-space: normal;
        text-align: left;
    }
</style>

<section>

<h2>{l s='Order summary' mod='StarPay'}</h2>

<input type="hidden" id='url' value="{$url}">

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='StarPay'}</p>
{else}
  <div class="box cheque-box">
    <h3>{l s='Check payment' mod='StarPay'}</h3>

    <p style="margin-top:20px;">
			- {l s='The total amount of your order comes to:' mod='StarPay'}
			<span id="amount" class="price">{$total}</span>
      {if $use_taxes == 1}
        {l s='(tax incl.)' mod='StarPay'}
      {/if}
		</p>
    <p>
			-
			<!-- {if isset($currencies) && $currencies|@count > 1}
				{l s='We accept several currencies to receive payments by check.' mod='StarPay'}
				<br /><br />
				{l s='Choose one of the following:' mod='StarPay'}
				<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
				{foreach from=$currencies item=currency}
					<option value="{$currency.id_currency}" {if isset($currencies) && $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
				{/foreach}
				</select>
			{else} -->
				{l s='We allow the following currencies to be sent by check:' mod='StarPay'}&nbsp;<b>{$currencies.0.iso_code}</b>
				<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
			<!-- {/if} -->
		</p>
    <p>
      {if $mobile == true}
        <form id="frmain" method="post" action="https://api.starpayes.com/aps-gateway/gateway.do">
          <p>
            <input type="hidden" id="access_id" name="access_id" value="{$config['access_id']}" />
            <input type="hidden" id="type" name="type" value="{$config['type']}" />
            <input type="hidden" id="version" name="version" value="{$config['version']}" />
            <input type="hidden" id="timestamp" name="timestamp" value="{$config['timestamp']}" />
            <input type="hidden" id="content" name="content" value='{$config["content"]}' />
            <input type="hidden" id="format" name="format" value="{$config['format']}" />
            <input type="hidden" id="sign" name="sign" value="{$config['sign']}" />
          </p>
          <div><input type="submit" id="btn_submit" class="exclusive_pay" name="btn_submit" value="Pay Order" /></div>
        </form>
      {elseif $qr_code_wechat == false}
        <p class="alert alert-danger">{l s="Something happened, check the StarPay's module settings" mod='StarPay'}</p>
      {else}
        <img src="{$qr_code_wechat}" alt="{l s='Pay with my payment module' mod='StarPay'}" />
      {/if}
    </p>
  </div>
{/if}
</section>

<!-- <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->

<script type="text/javascript">

  // document.onreadystatechange = () => {
  //   if (document.readyState === 'complete') {
  //     var url = document.getElementById('url').value;
  //     debugger;
  //     setInterval(function(){
  //         var xmlHttp = new XMLHttpRequest();
  //         xmlHttp.open( "GET", url, false ); // false for synchronous request
  //         xmlHttp.send( null );
  //         var response = xmlHttp.responseText;
  //         if(response != 3) {
  //           location.href = response;
  //         }
  //     }, 2000);
  //   }
  // };

	$(document).ready(function () {
  //
  //   // $('input[name=payment-option]').click(function() {
  //   //   console.log($('label[for='+$(this).attr('id')+']').text().trim());
  //   //   if ($('label[for='+$(this).attr('id')+']').text().trim() === '微信支付'){
  //   //     $('#conditions-to-approve').css('display', 'none');
  //   //     $('.ps-shown-by-js').find('button').css('display', 'none');
  //   //   }
  //   //   else{
  //   //     $('#conditions-to-approve').css('display', 'block');
  //   //     $('.ps-shown-by-js').find('button').css('display', 'block');
  //   //   }
  //   // });
  //
    var url = $('#url').val();

		setInterval(function(){
			$.get(
	      url,
	      function (result) {
					if(result != 3) {
						location.href = result;
					}
        });
      },2000
    );
	});



</script>
