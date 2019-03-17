<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\MailChimp\Models;

use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use Httpful\Response;
use Splash\Core\SplashCore as Splash;
use stdClass;

/**
 * MailChimp Specific Helper
 *
 * Support for Managing ApiKey, ApiRequests, Hashs, Etc...
 */
class MailChimpHelper
{
    /**
     * @var string
     */
    private static $endPoint;

    /**
     * @var string
     */
    private static $apiList;

    /**
     * Validate Api Key
     *
     * @param mixed $apiKey
     *
     * @return bool
     */
    public static function isValidApiKey($apiKey): bool
    {
        //====================================================================//
        // Verify Api Key is a String
        if (empty($apiKey) || !is_string($apiKey)) {
            return false;
        }
        //====================================================================//
        // Verify Api Key is on Right Format
        try {
            list($privateKey, $server) = explode("-", $apiKey);
        } catch (\Exception $ex) {
            return false;
        }
        if (empty($privateKey) || empty($server) || !is_string($server)) {
            return false;
        }

        return true;
    }

    /**
     * Get MailChimp Endpoint Url
     *
     * @param string $apiKey
     *
     * @return null|string
     */
    public static function getEndPoint(string $apiKey): ?string
    {
        //====================================================================//
        // Verify Api Key is a String
        if (!self::isValidApiKey($apiKey)) {
            return null;
        }
        //====================================================================//
        // Decode Api Key
        list($privateKey, $server) = explode("-", $apiKey);
        if (empty($privateKey) || empty($server) || !is_string($server)) {
            return null;
        }

        return "https://".$server.".api.mailchimp.com/3.0/";
    }

    /**
     * Get Current MailChimp List
     *
     * @return string
     */
    public static function getList(): string
    {
        return self::$apiList;
    }

    /**
     * Congigure MailChimp REST API
     *
     * @param string $apiKey
     * @param string $apiList
     *
     * @return bool
     */
    public static function configure(string $apiKey, string $apiList = null): bool
    {
        //====================================================================//
        // Clear EndPoint Url
        self::$endPoint = "";
        //====================================================================//
        // Verify Api Key is a String
        if (!self::isValidApiKey($apiKey)) {
            return false;
        }
        //====================================================================//
        // Store EndPoint Url
        $endPoint = self::getEndPoint($apiKey);
        if (!is_null($endPoint)) {
            self::$endPoint = $endPoint;
        }
        //====================================================================//
        // Store Current List to Use
        self::$apiList = is_string($apiList) ? $apiList : "";
        //====================================================================//
        // Configure API Template Request
        $template = Request::init()
            ->authenticateWith('splashsync', $apiKey)
            ->sendsJson()
            ->expectsJson()
            ->timeout(3)
            ;
        // Set it as a template
        Request::ini($template);

        return true;
    }

    /**
     * Ping MailChimp API Url as Annonymous User
     *
     * @return bool
     */
    public static function ping(): bool
    {
        //====================================================================//
        // Safety Check
        if (empty(self::$endPoint)) {
            return false;
        }
        //====================================================================//
        // Perform Ping Test
        try {
            $response = Request::get(self::$endPoint)->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }
        if (($response->code >= 200) && ($response->code < 500)) {
            return true;
        }

        return false;
    }

    /**
     * Ping MailChimp API Url with API Key (Logged User)
     *
     * @return bool
     */
    public static function connect(): bool
    {
        //====================================================================//
        // Safety Check
        if (empty(self::$endPoint)) {
            return false;
        }
        //====================================================================//
        // Perform Connect Test
        try {
            $response = Request::get(self::$endPoint."ping")->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }
        //====================================================================//
        // Catch Errors inResponse
        self::catchErrors($response);

        //====================================================================//
        // Return Connect Result
        return (200 == $response->code);
    }

    /**
     * MailChimp API GET Request
     *
     * @param string $path API REST Path
     * @param array  $body Request Data
     *
     * @return null|stdClass
     */
    public static function get(string $path, array $body = null): ?stdClass
    {
        //====================================================================//
        // Safety Check
        if (empty(self::$endPoint)) {
            return null;
        }
        //====================================================================//
        // Prepare Uri
        $uri = self::$endPoint.$path;
        if (!empty($body)) {
            $uri .= "?".http_build_query($body);
        }
        //====================================================================//
        // Perform Request
        try {
            $response = Request::get($uri)
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }

        //====================================================================//
        // Catch Errors inResponse
        return self::catchErrors($response) ? $response->body : null;
    }

    /**
     * MailChimp API PUT Request
     *
     * @param string   $path API REST Path
     * @param stdClass $body Request Data
     *
     * @return null|stdClass
     */
    public static function put(string $path, stdClass $body = null): ?stdClass
    {
        //====================================================================//
        // Safety Check
        if (empty(self::$endPoint)) {
            return null;
        }
        //====================================================================//
        // Perform Request
        try {
            $response = Request::put(self::$endPoint.$path)
                ->body($body)
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Catch Errors inResponse
        return self::catchErrors($response) ? $response->body : null;
    }

    /**
     * MailChimp API POST Request
     *
     * @param string   $path API REST Path
     * @param stdClass $body Request Data
     *
     * @return null|stdClass
     */
    public static function post(string $path, stdClass $body = null): ?stdClass
    {
        //====================================================================//
        // Safety Check
        if (empty(self::$endPoint)) {
            return null;
        }
        //====================================================================//
        // Perform Request
        try {
            $response = Request::post(self::$endPoint.$path)
                ->body($body)
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Catch Errors inResponse
        return self::catchErrors($response) ? $response->body : null;
    }

    /**
     * MailChimp API DELETE Request
     *
     * @param string $path API REST Path
     *
     * @return null|bool
     */
    public static function delete(string $path): ?bool
    {
        //====================================================================//
        // Safety Check
        if (empty(self::$endPoint)) {
            return null;
        }
        //====================================================================//
        // Perform Request
        try {
            $response = Request::delete(self::$endPoint.$path)->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Catch Errors in Response
        return self::catchErrors($response) ? true : false;
    }

    /**
     * Analyze MailChimp Api Response & Push Errors to Splash Log
     *
     * @param Response $response
     *
     * @return bool TRUE is no Error
     */
    private static function catchErrors(Response $response) : bool
    {
        //====================================================================//
        // Check if MailChimp Response has Errors
        if (!$response->hasErrors()) {
            return true;
        }
        //====================================================================//
        //  Debug Informations
        if (true == SPLASH_DEBUG) {
            Splash::log()->www("[MailChimp] Full Response", $response);
        }
        if (!$response->hasBody()) {
            return false;
        }
        //====================================================================//
        // Store MailChimp Errors if present
        Splash::log()->err($response->body->title.": ".$response->body->detail);
        //====================================================================//
        // Detect MailChimp Errors Details
        if (isset($response->body->errors) && is_array($response->body->errors)) {
            foreach ($response->body->errors as $mcError) {
                Splash::log()->err($mcError->message);
            }
        }

        return false;
    }
}
