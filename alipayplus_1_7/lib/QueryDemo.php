<?php
ini_set('display_errors',1);            //error message
ini_set('display_startup_errors',1);    //php start error
error_reporting(-1);                    //show all error messages

require_once 'StarpayUtil.php';

//Wechat interface
$gatewayurl="https://api.starpayes.com/aps-gateway/entry.do";

$config = array (
    //id assigned by por Starpay
    'access_id' => "A10000008",
    //transaction type(see documentation)
    'type' => "2010",
    //default version is 1.0
    'version' => "1.0",
    //timestamp format yyyy-MM-dd HH:mm:ss
    'timestamp' => "2019-05-16 00:17:34",
    //see documentation for how to set up the content field
    'content' => "{merchantAccessNo:\"B10000003\", orderNo: \"5\", orderAmt: 20, subject: \"Compra de ropa\", currency: \"EUR\", bgRetUrl: \"http://localhost/wechatpay/bg.php\"}",
    //for now we are 100% exclusive with JSON.
    'format'=>"JSON",
    //See "message signature" in the documentation
    'sign' => ""
);


echo "<font color=\"red\">Starting...</font><br>";

$clsName="StarpayUtil";

$ret = $clsName::SignData($config);

echo "<font color=\"red\">Result:</font>".$ret."<br>";

//build the request
$config["sign"]=$ret;
echo "<font color=\"red\">Request...</font><br>";
echo str_replace("\\\"", "\"", $clsName::curl($gatewayurl,$config));
echo "<br><font color=\"red\">Completed</font>";
