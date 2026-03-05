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

use Splash\Connectors\MailChimp\Models\MailChimpConnectorAwareTrait;
use Webmozart\Assert\Assert;

/**
 * Manage MailChimp Mailing Lists
 */
class ListsManager
{
    use MailChimpConnectorAwareTrait;

    /**
     * Default API List Index
     */
    const string DEFAULT_INDEX = "ApiList";

    /**
     * API List Indexes Storage Key
     */
    const string LISTS_INDEX = "ApiListsIndex";

    /**
     * API List Details Storage Key
     */
    const string LISTS_DETAILS = "ApiListsDetails";

    /**
     * Fetch Mailing Lists from MailChimp API
     */
    public function fetchMailingLists(): bool
    {
        //====================================================================//
        // Get User Lists from Api (root connexion)
        $response = $this->getConnexion()->get('/lists');
        if (is_null($response) || empty($response["lists"]) || !is_array($response["lists"])) {
            return false;
        }
        //====================================================================//
        // Parse Lists to Connector Settings
        $listIndex = array();
        foreach ($response["lists"] as $listDetails) {
            Assert::isArray($listDetails);
            //====================================================================//
            // Add List Index
            $listIndex[$listDetails["id"]] = $listDetails["name"];
        }
        //====================================================================//
        // Store in Connector Settings
        $this->getConnector()->setParameter(self::LISTS_INDEX, $listIndex);
        $this->getConnector()->setParameter(self::LISTS_DETAILS, $response["lists"]);
        //====================================================================//
        // Update Connector Settings
        $this->getConnector()->updateConfiguration();

        return true;
    }

    /**
     * Get All Lists as Choices Array
     *
     * @return array<string, string>
     */
    public function getChoices(): array
    {
        $index = $this->getConnector()->getParameter(self::LISTS_INDEX);
        if (!is_array($index)) {
            return array();
        }

        /** @var string[] $index */
        return array_combine(array_values($index), array_values($index));
    }
}
