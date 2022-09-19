<?php
ini_set('display_errors',1);            //Error message
ini_set('display_startup_errors',1);    //php initialisation error message
error_reporting(-1);                    //print out all the error messages

require_once 'phpqrcode.php';

$url = "http://cashier.starpayes.com/aps-cashier/qr/B10000003/000/0abbe7479dd741e295c78bb0e91567c3";

function createTempQrcode($data)
{
    $object = new \QRcode();
    $errorCorrectionLevel = 'L';    // Error logging level
    $matrixPointSize = 5;            //generate image size

    ob_start();
    $returnData = $object->png($data,false,$errorCorrectionLevel, $matrixPointSize, 2);
    $imageString = base64_encode(ob_get_contents());
    ob_end_clean();

    return "data:image/png;base64,".$imageString;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>Display QR code image</p>
    <p>
        <img src="<?php echo createTempQrcode($url)?>" />
    </p>
</body>
</html>
