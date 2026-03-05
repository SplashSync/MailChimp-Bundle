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

namespace Splash\Connectors\MailChimp\Models;

use Splash\Connectors\MailChimp\Connectors\MailChimpConnector;
use Splash\OpenApi\Interfaces\ConnexionInterface;

/**
 * Makes any Service Aware of MailChimp Connector
 */
trait MailChimpConnectorAwareTrait
{
    /**
     * Currently Used Connector
     */
    private MailChimpConnector $connector;

    /**
     * Configure with Current Connector
     */
    public function configure(MailChimpConnector $connector): static
    {
        $this->connector = $connector;

        return $this;
    }

    /**
     * Get Connector
     */
    public function getConnector(): MailChimpConnector
    {
        return $this->connector;
    }

    /**
     * Get Connexion
     */
    public function getConnexion(): ConnexionInterface
    {
        return $this->getConnector()->getConnexion();
    }
}
