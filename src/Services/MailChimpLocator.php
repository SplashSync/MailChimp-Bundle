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

namespace Splash\Connectors\MailChimp\Services;

use Psr\Container\ContainerInterface;
use Splash\Connectors\MailChimp\Models\MailChimpConnectorAwareTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Webmozart\Assert\Assert;

/**
 * MailChimp Services Locator (ServiceSubscriber Pattern)
 */
class MailChimpLocator implements ServiceSubscriberInterface
{
    use MailChimpConnectorAwareTrait;

    public function __construct(
        private ContainerInterface $locator,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return array(
            Managers\ListsManager::class,
            Managers\MergeFieldsManager::class,
            Managers\WebHookManager::class,
        );
    }

    //====================================================================//
    // Access to Configured Services
    //====================================================================//

    /**
     * Get MailChimp Lists Manager
     */
    public function getListsManager(): Managers\ListsManager
    {
        Assert::isInstanceOf(
            $service = $this->locator->get(Managers\ListsManager::class),
            Managers\ListsManager::class
        );

        return $service->configure($this->connector);
    }

    /**
     * Get MailChimp Merge Fields Manager
     */
    public function getMergeFieldsManager(): Managers\MergeFieldsManager
    {
        Assert::isInstanceOf(
            $service = $this->locator->get(Managers\MergeFieldsManager::class),
            Managers\MergeFieldsManager::class
        );

        return $service->configure($this->connector);
    }

    /**
     * Get MailChimp WebHook Manager
     */
    public function getWebHookManager(): Managers\WebHookManager
    {
        Assert::isInstanceOf(
            $service = $this->locator->get(Managers\WebHookManager::class),
            Managers\WebHookManager::class
        );

        return $service->configure($this->connector);
    }
}
