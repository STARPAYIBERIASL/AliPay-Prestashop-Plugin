<?php


class StarpayUtilAlipayPlusPay {
    // Encoding for the form
    public static $postCharset = "UTF-8";
    public static $fileCharset = "UTF-8";

    public static function SignData($p, $private_content) {
        // Firma
        $signature = '';
        if(is_array($p)){
            
            $p["content"]=md5($p["content"]);
            //Encrypt and replace content field from the form
            $data = self::buildQuery($p);
            //Trim, sort and filter keys to remover  ", null and empty fieds
            //echo htmlentities($data,ENT_QUOTES);
            $priv_key_id = openssl_pkey_get_private($private_content);
            // $priv_key_id=openssl_get_privatekey($private_content);
            openssl_sign($data, $signature, $priv_key_id,OPENSSL_ALGO_SHA256);
            //Encrypted signature now ready
            return base64_encode($signature);
        }else {
            ($p) or die('There was a problem with the parameter.  Please check key-values');
        }
    }

    /*
     * Sort parameters a-z
     * */
    public static function buildQuery( $query ){
        if ( !$query ) {
            return null;
        }
        array_filter($query);
        //Sort parameters
        ksort( $query );
        //Rebuild parameters
        $params = array();
        foreach($query as $key => $value){
            if($value!=null)
                $params[] = $key .'='. $value ;
        }
        $data = implode('&', $params);
        return $data;
    }

    public static function curl($url, $postFields = null) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $postBodyString = "";
        $encodeArray = Array();
        $postMultipart = false;

        if (is_array($postFields) && 0 < count($postFields)) {
            foreach ($postFields as $k => $v) {
                if ("@" != substr($v, 0, 1)) //Check to see if file has been uploaded
                {
                    $postBodyString .= "$k=" . urlencode(self::characet($v, self::$postCharset)) . "&";
                    $encodeArray[$k] = self::characet($v, self::$postCharset);
                } else //Uploaded file with multipart/form-data, if not use www-form-urlencoded
                {
                    $postMultipart = true;
                    $encodeArray[$k] = new \CURLFile(substr($v, 1));
                }
            }

            unset ($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);

            if ($postMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeArray);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            }
        }

        //echo $postBodyString;

        if ($postMultipart) {
            $headers = array('content-type: multipart/form-data;charset=' . self::$postCharset . ';boundary=' . self::getMillisecond());
        } else {
            $headers = array('content-type: application/x-www-form-urlencoded;charset=' . self::$postCharset);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new Exception($reponse, $httpStatusCode);
            }
        }

        curl_close($ch);
        return $reponse;
    }



    public static function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }



    /**

     * Character encoding conversion

     * @param $data

     * @param $targetCharset

     * @return string

     */

    public static function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = self::$fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }

}
