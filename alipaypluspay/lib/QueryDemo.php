<?php
ini_set('display_errors',1);            //error message
ini_set('display_startup_errors',1);    //php start error
error_reporting(-1);                    //show all error messages

require_once 'StarpayUtil.php';

//Wechat interface
$gatewayurl="https://api.starpayes.com/aps-gateway/entry.do";

$config = array (
    //id assigned by por Starpay
    'access_id' => "A10000046",
    //transaction type(see documentation)
    'type' => "2013",
    //default version is 1.0
    'version' => "1.0",
    //timestamp format yyyy-MM-dd HH:mm:ss
    'timestamp' => date('Y-m-d H:i:s'),
    //see documentation for how to set up the content field OJO REVISAR
    'content' => "{storeNo: \"000\", retUrl: \"http://localhost/starpaypluspay/bg.php\", channelType: \"ALIPAYPLUS\", merchantAccessNo:\"B10000128\", orderNo: \"5\", orderAmt: 20, subject: \"Compra de ropa\", currency: \"EUR\", bgRetUrl: \"http://localhost/starpaypluspay/bg.php\"}",
    //for now we are 100% exclusive with JSON.
    'format'=>"JSON",
    //See "message signature" in the documentation
    'sign' => ""
);


echo "<font color=\"red\">Starting...</font><br>";

$clsName="StarpayUtil";
$secret = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAmoUacVaCXeYopZKt5xrnr+f6Kv0YB5xTaL1gVwL9TacQzZd5ggzb4GQ79cl9KdLHfBNKk8BRGio3MMobU9xCX91Z0zPwC3sdGxXaFbqr+0x8W4erAmMglySjim9x/Rz6yqCD/m+XbixkGTBzLTf1q9cq3ryRe7EnQLsB5s80IDAOYQq8L+q3Ev3N5KDS50K5QcLifjjMl76DdaahlGi6xhwksm5cA7EstZRZ5Bi1Mwf32FQaE1BJ7+uVel4+DZaSOXcq9MxIvyetvBlFTdZ4kHDm2ZxWx2I4xkOAOw7he3CBOxQj5dafeQMqr9u5n5/LSXiuuMmaiKCglCa8yiq42QIDAQABAoIBAEYhnVPNkNPvb4lenoFi678iCaBTSQHDSPQj58L2P2NsLSsy0dpd3bnlpHU04xMFcLb2xoVy5xx6BpylTmvsb0+1VlyOmSeM1DQD4fy0wfQHzfV5CPvHY/ZVkch2NoVMDuPCoMXtfgxqJ3BrVgsy99TrjrCTY3CBgjBV8FICv0TP8eGd85+QbWDuwhkfcDvkSWxkjkmgWq7iImsyN6vj35XzI6rEokIBNiQtd6h/4wMZ+HSreGpRekZGJVKKRFi8434nSeiVTPuDc2MQF4wIgUsr3ZEZ9MphgZpBKlbP3pNPc2GQmbiVQglHBiQY56QW7FyaVcHLhpvoGyF9/rOk9oECgYEA+GZ9yQ9ORkJLXpwe/6pjweYIaYEDSFKwACtEU6GRmMhdR1nwvDhk8ILd+ytwsA17KHc3ZrfXeUUo18qoUWUgQKIBtAdPivjjJSGDSSacZid6mzTpGtKC8gFvg6bI5EU54z0thS1l4LiHgQrQDDQ54WN/pHzB4oPKcCwoJCLXqYkCgYEAnz9Tk6jnK9tgYVMUc2kI9F/nGZoQvQ2mUfJcJuicdU/nzOZDR9SQHP8IpkI8JoxuoYS59SfeTDcezKffqIU72lTHFFDN6a+3CQIb9PluZ7hmC1ydxUL0z11PrIn+1f/ChuIcVKSRdi+cCWQOwI92iZq6mMhbRGMSUoIzP8cK0NECgYBFQkZ2JfdcLaXYJk2aWYbhDGNyD0+7/ZonIek2LEltQQiJGXG++TZjvQlpu836uHeLa9odoMrKfFcL++x8bWbVvpYc/SDXjde/hZ2WataWwRE1b0ZDfUiXc4EFQx6mTwr56hfkFyldw/W8LCigDnvI9TYkxchFgRuTtSwoDzL0iQKBgQCIjMCZqQcwsBfg3GB45ePryCBd76HSulWXhv5Fzsf6P94/8GJYwYghlP5RshHp7BkhHrJl6R3QtKMQUhKSakx8Vp2uaetnKmkErG5UjV2CSrgVngEbGOLavOSIyntd/MVM663nVoX0lbZyxv1vWJUIQUknoQXBikI3xbU0RvSbQQKBgQCQVYhH5wpxQoBWMxU3gdiu3/yOB7A3zcqACmS86bKJVHu5QeOub7HvG4b1w/jJkwh+Af8g05cPaHr6ohnj2l5/Yx76LN6ldt4XuMTQLX9668EUveRo3VgxQpmxbyP3DOGSCdejd83BGDADLL+gE6yw7fK3WRqo95BVgIvK+bcuXw==
-----END RSA PRIVATE KEY-----";

$ret = $clsName::SignData($config, $secret);

echo "<font color=\"red\">Result:</font>".$ret."<br>";

//build the request
$config["sign"]=$ret;
echo "<font color=\"red\">Request...</font><br>";
echo str_replace("\\\"", "\"", $clsName::curl($gatewayurl,$config));
echo "<br><font color=\"red\">Completed</font>";
