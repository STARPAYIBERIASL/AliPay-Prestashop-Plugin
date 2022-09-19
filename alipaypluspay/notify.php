<?php

    include(dirname(__FILE__).'/../../config/config.inc.php');
    // include(dirname(__FILE__).'/../../header.php');
    include(dirname(__FILE__).'/../../init.php');

    try{
        $access = "";
        if (!empty($_POST)) {
            $access = 'POST';
        } else if (!empty($_GET)) {
            $access = 'GET';
        }

        if ($access === 'POST' || $access === 'GET') {
            if ($access === 'POST'){
                $result = $_POST;
        		$content = json_decode($result['content'], true);

                // GET PARAMETERS
                $access_id = $result['access_id'];
                $merchantAccessNo = $content['merchantAccessNo'];
                $orderNo = $content['orderNo'];
                $merOrderNo = $content['merOrderNo'];
                $orderCurrency = $content['orderCurrency'];
                $orderAmt = (int) $content['orderAmt'];
                $payCurrency = $content['payCurrency'];
                $payAmt = $content['payAmt'];
                $acctDate = $content['acctDate'];

                $status = $content['tradeStatus'];

                if ($status == 'R000'){
                    $cart = mb_split("&", $merOrderNo);
                    $cart_id = $cart[1];;
					          $currency_id = $cart[2];
          					$customer = $cart[3];
          					$total = $orderAmt/100;
                    $alipaypluspay = Module::getInstanceByName('alipaypluspay');
                    $alipaypluspay->validateOrder($cart_id, Configuration::get('PS_OS_PAYMENT'), $total, 'AlipayPlus Pay', NULL, array(), $currency_id, false, $customer);
                }
                echo 'SUCCESS';
            }
            else{
                $result = $_GET;
        		    $content = json_decode($result['content'], true);

                // GET PARAMETERS
                $access_id = $result['access_id'];
                $merchantAccessNo = $content['merchantAccessNo'];
                $orderNo = $content['orderNo'];
                $merOrderNo = $content['merOrderNo'];
                $orderCurrency = $content['orderCurrency'];
                $orderAmt = (int) $content['orderAmt'];
                $payCurrency = $content['payCurrency'];
                $payAmt = $content['payAmt'];
                $acctDate = $content['acctDate'];

                $status = $content['tradeStatus'];

                if ($status == 'R000'){
                    $cart = mb_split("&", $merOrderNo);
                    $cart_id = $cart[1];;
          					$currency_id = $cart[2];
          					$customer = $cart[3];
          					$total = $orderAmt/100;
                    $alipaypluspay = Module::getInstanceByName('alipaypluspay');
                    $alipaypluspay->validateOrder($cart_id, Configuration::get('PS_OS_PAYMENT'), $total, 'AlipayPlus Pay', NULL, array(), $currency_id, false, $customer);
                }
                echo 'SUCCESS';
            }
        }

    }
    catch (Exception $e){
    	$idLogExc = generateIdLog();
    	escribirLog($idLogExc." -- Excepcion en la validacion: ".$e->getMessage(),"si");
    	die("Excepcion en la validacion");
    }

?>
