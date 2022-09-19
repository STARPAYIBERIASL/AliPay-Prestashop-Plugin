
<?php

    include(dirname(__FILE__).'/../../config/config.inc.php');

    try{

		$last_id = Db::getInstance()->getValue('
        SELECT id_order
        FROM '._DB_PREFIX_.'orders
		    WHERE id_cart ='.$_GET["cart_id"]);

        $module_name = 'alipaypluspay';
        $module_id = Db::getInstance()->getValue('
        SELECT id_module
        FROM '._DB_PREFIX_.'module
		    WHERE name =\''.$module_name.'\'');

		    $last_id = (int)$last_id;

        if ($last_id == 0) {
            echo 3;
        }
        else {
            $url = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'confirmacion-pedido?id_cart='.$_GET["cart_id"].'&id_module='.$module_id.'&id_order='.$last_id.'&key='.$_GET["customer"];
            echo $url;
        }
    }
    catch (Exception $e){
    	$idLogExc = generateIdLog();
    	escribirLog($idLogExc." -- Excepcion en la validacion: ".$e->getMessage(),"si");
    	die("Excepcion en la validacion");
    }

?>
