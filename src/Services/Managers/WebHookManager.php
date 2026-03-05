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

namespace Splash\Connectors\MailChimp\Services\Managers;

use Splash\Bundle\Services\ConnectorRoutesBuilder;
use Splash\Connectors\MailChimp\Models\MailChimpConnectorAwareTrait;
use Splash\Connectors\MailChimp\Objects;

/**
 * Manage MailChimp WebHooks Registration & Verification
 */
class WebHookManager
{
    use MailChimpConnectorAwareTrait;

    public function __construct(
        private readonly ConnectorRoutesBuilder $routesBuilder,
    ) {
    }

    /**
     * Verify Splash WebHook is Registered
     */
    public function verify(): bool
    {
        //====================================================================//
        // Generate Expected WebHook Url
        $webHookUrl = $this->routesBuilder->getMasterActionUrl($this->getConnector());
        //====================================================================//
        // Get List Of WebHooks
        $webHooks = $this->fetchWebHooksList();

        //====================================================================//
        // Search for Expected WebHook
        foreach ($webHooks as $webHook) {
            if (!is_array($webHook) || empty($webHook['url']) || !is_scalar($webHook['url'])) {
                continue;
            }
            //====================================================================//
            // This is NOT a Splash WebHook
            if (!$this->routesBuilder->isSplashUrl((string) $webHook['url'])) {
                continue;
            }
            //====================================================================//
            // This is the Expected WebHook
            if (trim((string) $webHook['url']) == $webHookUrl) {
                return true;
            }
        }

        //====================================================================//
        // Splash WebHook was NOT Found
        return false;
    }

    /**
     * Update Splash WebHooks Registration
     */
    public function update(): bool
    {
        //====================================================================//
        // Generate Expected WebHook Url
        $webHookUrl = $this->routesBuilder->getMasterActionUrl($this->getConnector());
        //====================================================================//
        // Create WebHook Object Class
        $webHookObject = new Objects\Webhook($this->getConnector());
        //====================================================================//
        // Get List Of WebHooks
        $webHooks = $this->fetchWebHooksList();
        //====================================================================//
        // Filter & Clean List Of WebHooks
        $foundWebHook = false;
        foreach ($webHooks as $webHook) {
            if (!is_array($webHook) || empty($webHook['url']) || !is_scalar($webHook['url'])) {
                continue;
            }
            //====================================================================//
            // This is the Current Node WebHook
            if (trim((string) $webHook['url']) == $webHookUrl) {
                $foundWebHook = true;

                continue;
            }
            //====================================================================//
            // This is an Old Splash WebHook => Delete
            if ($this->routesBuilder->isSplashUrl((string) $webHook['url'])) {
                $webHookObject->delete((string) $webHook['id']);
            }
        }
        //====================================================================//
        // Splash WebHook Already Registered
        if ($foundWebHook) {
            return true;
        }

        //====================================================================//
        // Register New Splash WebHook
        return $webHookObject->createFromUrl($webHookUrl);
    }

    //====================================================================//
    // Private Methods
    //====================================================================//

    /**
     * Fetch All WebHooks from MailChimp API
     *
     * @return array<int|string, mixed>
     */
    private function fetchWebHooksList(): array
    {
        //====================================================================//
        // Create WebHook Object Class
        $webHookObject = new Objects\Webhook($this->getConnector());
        //====================================================================//
        // Get List Of WebHooks
        $webHooks = $webHookObject->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }

        return $webHooks;
    }
}
