<?php
/**
* 2025 FACTURA PUNTO COM SAPI de CV
*
* NOTICE OF LICENSE
*
* This source file is subject to License
* It is also available through the world-wide-web at this URL:
* http://factura.com
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to apps@factura.com so we can send you a copy immediately.
*
*  @author factura.com <apps@factura.com>
*  @copyright  2025 Factura Punto Com
*  International Registered Trademark & Property of factura.com
*/

class Curls
{
    public static function frontCurl($url, $request, $keyapi, $keysecret, $params = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);

        if (!isset($params)) {
            $params = 'no data';
        }

        if ($request == 'post') {
            $dataString = json_encode($params);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'F-PLUGIN: f6a36f158b2b09cae5054e2170d62f750f7e9ff7',
                'F-API-KEY: '.$keyapi,
                'F-SECRET-KEY: '.$keysecret,
            ));
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'F-PLUGIN: f6a36f158b2b09cae5054e2170d62f750f7e9ff7',
                'F-API-KEY: '.$keyapi,
                'F-SECRET-KEY: '.$keysecret,
            ));
        }

        try {
            $data = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            echo 'Exception occured: '.$e->getMessage();
        }

        return $data;
    }

    public static function adminCurl($url, $keyapi, $keysecret, $binary = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        // Si es un archivo binario, no establecer Content-Type como JSON
        if (!$binary) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'F-PLUGIN: f6a36f158b2b09cae5054e2170d62f750f7e9ff7',
                'F-API-KEY: '.$keyapi,
                'F-SECRET-KEY: '.$keysecret,
            ));
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'F-PLUGIN: f6a36f158b2b09cae5054e2170d62f750f7e9ff7',
                'F-API-KEY: '.$keyapi,
                'F-SECRET-KEY: '.$keysecret,
            ));
        }

        try {
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Verificar si la respuesta fue exitosa
            if ($httpCode !== 200) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return $data;
    }
}
