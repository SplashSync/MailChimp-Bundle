<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\MailChimp\Dictionary;

/**
 * MailChimp API Endpoints Definition
 */
class MailChimpEndpoints
{
    const string API_VERSION = "3.0";

    const string SANDBOX = "http://sandbox.mailchimp.local/3.0";

    /**
     * Get MailChimp API Endpoint Url
     */
    public static function getEndpoint(string $apiKey, bool $sandbox = false): string
    {
        if ($sandbox) {
            return self::SANDBOX;
        }
        $parts = explode("-", $apiKey);
        if (2 !== count($parts) || empty($parts[1])) {
            return "";
        }

        return sprintf("https://%s.api.mailchimp.com/%s", $parts[1], self::API_VERSION);
    }

    /**
     * Validate MailChimp API Key Format (key-server)
     */
    public static function isValidApiKey(string $apiKey): bool
    {
        $parts = explode("-", $apiKey);

        return 2 === count($parts) && !empty($parts[0]) && !empty($parts[1]);
    }
}
