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

namespace Splash\Connectors\MailChimp\Connectors;

use ArrayObject;
use Psr\Log\LoggerInterface;
use Splash\Bundle\Interfaces\ConnectorInterface;
use Splash\Bundle\Interfaces\Connectors\PrimaryKeysInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Connectors\GenericObjectMapperTrait;
use Splash\Bundle\Models\Connectors\GenericObjectPrimaryMapperTrait;
use Splash\Bundle\Models\Connectors\GenericWidgetMapperTrait;
use Splash\Bundle\Models\Connectors\RoutesBuilderAwareTrait;
use Splash\Bundle\Services\ConnectorRoutesBuilder;
use Splash\Connectors\MailChimp\Dictionary\MailChimpEndpoints;
use Splash\Connectors\MailChimp\Models\Connector\MailChimpApiTrait;
use Splash\Connectors\MailChimp\Models\Connector\MailChimpProfileTrait;
use Splash\Connectors\MailChimp\Objects;
use Splash\Connectors\MailChimp\Services\MailChimpLocator;
use Splash\Core\Client\Splash;
use Splash\Core\Dictionary\SplDefinition;
use Splash\OpenApi\Hydrators\SymfonyHydrator;
use Splash\OpenApi\Models\Connector\RestAdapterAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * MailChimp REST API Connector for Splash
 */
#[AutoconfigureTag(ConnectorInterface::TAG)]
class MailChimpConnector extends AbstractConnector implements PrimaryKeysInterface
{
    use RestAdapterAwareTrait;
    use MailChimpApiTrait;
    use MailChimpProfileTrait;
    use GenericObjectMapperTrait;
    use GenericObjectPrimaryMapperTrait;
    use GenericWidgetMapperTrait;
    use RoutesBuilderAwareTrait;

    /**
     * Objects Type Class Map
     *
     * @var array<string, class-string>
     */
    protected static array $objectsMap = array(
        "ThirdParty" => Objects\ThirdParty::class,
        "Webhook" => Objects\Webhook::class,
    );

    /**
     * Widgets Type Class Map
     *
     * @var array<string, class-string>
     */
    protected static array $widgetsMap = array(
        "SelfTest" => "Splash\\Connectors\\MailChimp\\Widgets\\SelfTest",
    );

    /**
     * Class Constructor
     */
    public function __construct(
        private readonly SymfonyHydrator $hydrator,
        private readonly MailChimpLocator $locator,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        ConnectorRoutesBuilder $routesBuilder,
    ) {
        parent::__construct($eventDispatcher, $logger);
        $this->setRouteBuilder($routesBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function ping() : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Perform Ping Test
        $this->getConnexion()->get("/ping");
        //====================================================================//
        // Check Response
        $response = $this->getConnexion()->getLastResponse();
        if ($response && ($response->code >= 200) && ($response->code < 500)) {
            return true;
        }

        //====================================================================//
        // Ping Test Fail
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function connect() : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Perform Connect Test
        $this->getConnexion()->get("/ping");
        //====================================================================//
        // Check Response
        $response = $this->getConnexion()->getLastResponse();
        if (!$response || (200 != $response->code)) {
            return false;
        }
        //====================================================================//
        // Get List of Available Lists
        if (!$this->getLocator()->getListsManager()->fetchMailingLists()) {
            return false;
        }

        //====================================================================//
        // Get List of Available Merge Fields
        if (!$this->getLocator()->getMergeFieldsManager()->fetchMergeFields()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function informations(ArrayObject $informations) : ArrayObject
    {
        //====================================================================//
        // Server General Description
        $informations->shortdesc = "MailChimp";
        $informations->longdesc = "Splash Integration for Mailchimp Api V3";
        //====================================================================//
        // Server Logo & Ico
        $informations->icoraw = Splash::file()->readFileContents(
            dirname(dirname(__FILE__))."/Resources/public/img/MailChimp-Icon.png"
        );
        $informations->logourl = null;
        $informations->logoraw = Splash::file()->readFileContents(
            dirname(dirname(__FILE__))."/Resources/public/img/MailChimp-Logo-Small.png"
        );
        //====================================================================//
        // Server Information
        $informations->servertype = "MailChimp REST Api V3";
        $informations->serverurl = "https://mailchimp.com";
        //====================================================================//
        // Module Information
        $informations->moduleauthor = "Splash Sync";
        $informations->moduleversion = SplDefinition::VERSION;

        //====================================================================//
        // Load API Configurations
        $config = $this->getConfiguration();
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest() || empty($config["ApiList"])) {
            return $informations;
        }
        //====================================================================//
        // Get List Detailed Information
        $response = $this->getConnexion()->get("/lists/".$config["ApiList"]);
        if (is_null($response) || !is_array($response)) {
            return $informations;
        }

        //====================================================================//
        // Company Information
        $contact = $response["contact"] ?? array();
        $informations->company = $contact["company"] ?? null;
        $informations->address = $contact["address1"] ?? null;
        $informations->zip = $contact["zip"] ?? null;
        $informations->town = $contact["city"] ?? null;
        $informations->country = $contact["country"] ?? null;
        $informations->www = "https://mailchimp.com";
        $informations->email = $response["campaign_defaults"]["from_email"] ?? " ";
        $informations->phone = $contact["phone"] ?? null;

        return $informations;
    }

    /**
     * {@inheritdoc}
     */
    public function selfTest() : bool
    {
        $config = $this->getConfiguration();

        //====================================================================//
        // Verify Api Key is Set
        //====================================================================//
        if (empty($config["ApiKey"]) || !is_string($config["ApiKey"])) {
            Splash::log()->err("Api Key is Invalid");

            return false;
        }

        //====================================================================//
        // Verify Api Key Format
        //====================================================================//
        if (!MailChimpEndpoints::isValidApiKey($config["ApiKey"])) {
            Splash::log()->err("Api Key format is Invalid (expected: key-server)");

            return false;
        }

        //====================================================================//
        // Sandbox Mode
        //====================================================================//
        if ($this->isSandbox()) {
            Objects\Webhook::setDisabled(false);
        }

        return true;
    }

    //====================================================================//
    // Objects Interfaces
    //====================================================================//

    //====================================================================//
    // Files Interfaces
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function getFile(string $filePath, string $fileMd5): ?array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return null;
        }
        Splash::log()->err("There are No Files Reading for MailChimp Up To Now!");

        return null;
    }

    //====================================================================//
    //  HIGH LEVEL WEBSERVICE CALLS
    //====================================================================//

    /**
     * Check MailChimp Api Account WebHooks.
     */
    public function verifyWebHooks(): bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$this->selfTest()) {
            return false;
        }

        return $this->getLocator()->getWebHookManager()->verify();
    }

    /**
     * Update MailChimp Api Account WebHooks.
     */
    public function updateWebHooks(): bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$this->selfTest()) {
            return false;
        }

        return $this->getLocator()->getWebHookManager()->update();
    }
}
