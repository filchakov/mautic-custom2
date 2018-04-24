<?php

namespace App\Providers\ElasticEmailClient;

class ApiClient
{
    private static $apiKey = "00000000-0000-0000-0000-000000000000";
    private static $ApiUri = "https://api.elasticemail.com/v2/";

    public static function Request($target, $data = array(), $method = "GET", array $attachments = array())
    {
        self::cleanNullData($data);
        $data['apikey'] = self::$apiKey;
        $ch = curl_init();
        $url = self::$ApiUri . $target . (($method === "GET") ? '?' . http_build_query($data) : '');
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false
        ));

        if ($method === "POST" && count($attachments) > 0) {
            foreach ($attachments as $k => $attachment) {
                $att = self::attachFile($attachment);
                $postnameSplit = explode('/', $att->postname);
                $att->postname = trim(end($postnameSplit));
                $data['file_'. $k] = $att;
            }
        }

        if ($method === "POST")
        {
            curl_setopt($ch, CURLOPT_POST, true);
            if (empty($attachments)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } else {
                error_reporting(E_ALL ^ E_NOTICE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        $response = self::executeWithRetry($ch, true);
        if ($response === false)
        {
            throw new ApiException($url, $method, 'Request Error: ' . curl_error($ch));
        }
        curl_close($ch);
        $jsonResult = json_decode($response);
        $parseError = self::getParseError();
        if ($parseError !== false)
        {
            throw new ApiException($url, $method, 'Request Error: ' . $parseError, $response);
        }
        if ($jsonResult->success === false)
        {
            throw new ApiException($url, $method, $jsonResult->error);
        }

        return (isset($jsonResult->data)? $jsonResult->data : null);
    }

    public static function executeWithRetry($ch, $sleep = false)
    {
        $counter = 0;
        $maxRetries = 3;
        $lastErr = null;
        $sleepInSeconds = 5;

        while ($counter < $maxRetries)
        {
            try
            {
                $response = curl_exec($ch);
                return $response;
            }
            catch (\Exception $e)
            {
                $counter++;
                $lastErr = $e->getMessage();

                if ($sleep)
                {
                    sleep($sleepInSeconds);
                }
            }
        }

        throw new \Exception('Error after '.$maxRetries.' retries: '.$lastErr);
    }

    public static function getFile($target, $data)
    {
        self::cleanNullData($data);
        $data['apikey'] = self::$apiKey;
        $url = self::$ApiUri . $target;
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));
        $response = curl_exec($ch);
        if ($response === false)
        {
            throw new ApiException($url, "POST", 'Request Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    public static function SetApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    private static function cleanNullData(&$data)
    {
        foreach ($data as $key => $item)
        {
            if ($item === null)
            {
                unset($data[$key]);
            }
            if (is_bool($item))
            {
                $data[$key] = ($item) ? 'true' : 'false';
            }
        }
    }

    private static function attachFile($attachment)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $attachment);
        finfo_close($finfo);
        $save_file = realpath($attachment);
        return new \CurlFile($save_file, $mimeType, $attachment);
    }

    private static function getParseError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return false;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }
}