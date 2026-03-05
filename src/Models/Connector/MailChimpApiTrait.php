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

namespace Splash\Connectors\MailChimp\Models\Connector;

use Httpful\Request;
use Splash\Connectors\MailChimp\Dictionary\MailChimpEndpoints;
use Splash\Connectors\MailChimp\Models\Api\Action as MailChimpAction;
use Splash\Connectors\MailChimp\Services\Connexion\MailChimpErrorParser;
use Splash\Connectors\MailChimp\Services\MailChimpLocator;
use Splash\OpenApi\Connexion\JsonConnexion;
use Splash\OpenApi\Hydrators\SymfonyHydrator;
use Splash\OpenApi\Interfaces\ConnexionInterface;
use Splash\OpenApi\Visitor\JsonVisitor;
use Webmozart\Assert\Assert;

/**
 * Manage MailChimp Connector API Configuration
 */
trait MailChimpApiTrait
{
    /**
     * @var array<string, ConnexionInterface>
     */
    private array $connexions = array();

    /**
     * Get Root API Connexion (for /ping, /lists, /lists/{id}/merge-fields)
     */
    public function getConnexion(): ConnexionInterface
    {
        return $this->getOrCreateConnexion("root", "");
    }

    /**
     * Get List-Scoped API Connexion (for /members, /webhooks)
     */
    public function getListConnexion(): ConnexionInterface
    {
        $config = $this->getConfiguration();
        $listId = $config["ApiList"] ?? "";

        Assert::scalar($listId, "API List ID is required for list-scoped connexion");
        Assert::stringNotEmpty((string) $listId, "API List ID is required for list-scoped connexion");

        return $this->getOrCreateConnexion("list", "lists/".$listId);
    }

    /**
     * Get Connector Hydrator
     */
    public function getHydrator(): SymfonyHydrator
    {
        return $this->hydrator;
    }

    /**
     * Get MailChimp Connector Services Locator
     */
    public function getLocator(): MailChimpLocator
    {
        return $this->locator->configure($this);
    }

    /**
     * Get MailChimp API Visitor configured with MailChimp-specific actions.
     *
     * Uses list-scoped connexion for ThirdParty and Webhook objects.
     *
     * @param class-string $model API model class
     */
    public function getVisitor(string $model): JsonVisitor
    {
        $visitor = new JsonVisitor(
            $this->getRestAdapter(),
            $this->getListConnexion(),
            $this->getHydrator(),
            $model,
        );
        //====================================================================//
        // Configure MailChimp-specific actions
        $visitor->setTimezone("UTC");
        $visitor->setUpdateAction(MailChimpAction\PutAction::class);
        $visitor->setListAction(MailChimpAction\ListAction::class);

        return $visitor;
    }

    /**
     * Check if we are in Sandbox Mode
     */
    public function isSandbox(): bool
    {
        return !empty($this->getParameter("isSandbox", false));
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Get or Create a Connexion with optional URI prefix
     */
    private function getOrCreateConnexion(string $scope, string $uriPrefix): ConnexionInterface
    {
        $cacheKey = $this->getWebserviceId()."_".$scope;
        //====================================================================//
        // Connexion already created
        if (isset($this->connexions[$cacheKey])) {
            return $this->connexions[$cacheKey];
        }
        //====================================================================//
        // Safety check
        Assert::true($this->selfTest(), "Self-test fails... Unable to create API Connexion!");
        //====================================================================//
        // Fetch Connector Configuration
        $config = $this->getConfiguration();
        $apiKey = (string) ($config["ApiKey"] ?? "");
        //====================================================================//
        // Build Endpoint URL
        $endpoint = MailChimpEndpoints::getEndpoint($apiKey, $this->isSandbox());
        if (!empty($uriPrefix)) {
            $endpoint .= "/".$uriPrefix;
        }
        //====================================================================//
        // Setup Api Connexion
        $connexion = new JsonConnexion(
            $endpoint,
            array(),
            function (Request $request) use ($apiKey) {
                $request
                    ->authenticateWith("splashsync", $apiKey)
                    ->sendsJson()
                    ->expectsJson()
                    ->timeout(3)
                ;
            }
        );
        //====================================================================//
        // Setup Rate Limiter
        $connexion->setRateLimiter($this->getLocator()->getRateLimiter());
        //====================================================================//
        // Setup Error Parser
        $connexion->setErrorParser(new MailChimpErrorParser());

        return $this->connexions[$cacheKey] = $connexion;
    }
}
