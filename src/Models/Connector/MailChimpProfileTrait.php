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

use Splash\Connectors\MailChimp\Actions;
use Splash\Connectors\MailChimp\Form\EditFormType;
use Splash\Connectors\MailChimp\Form\NewFormType;
use Splash\Connectors\MailChimp\Services\Managers\ListsManager;

/**
 * Manage MailChimp Connector Profile
 */
trait MailChimpProfileTrait
{
    //====================================================================//
    // Profile Interfaces
    //====================================================================//

    /**
     * Get Connector Profile Information
     *
     * @return array
     */
    public function getProfile() : array
    {
        return array(
            'enabled' => true,                                      // is Connector Enabled
            'beta' => false,                                        // is this a Beta release
            'type' => self::TYPE_ACCOUNT,                           // Connector Type or Mode
            'name' => 'mailchimp',                                  // Connector code (lowercase, no space allowed)
            'connector' => 'splash.connectors.mailchimp',           // Connector Symfony Service
            'title' => 'profile.card.title',                        // Public short name
            'label' => 'profile.card.label',                        // Public long name
            'domain' => 'MailChimpBundle',                          // Translation domain for names
            'ico' => '/bundles/mailchimp/img/MailChimp-Icon.png',   // Public Icon path
            'www' => 'mailchimp.com',                               // Website Url
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectedTemplate() : string
    {
        return "@MailChimp/Profile/connected.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getOfflineTemplate() : string
    {
        return "@MailChimp/Profile/offline.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getNewTemplate() : string
    {
        return "@MailChimp/Profile/new.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilderName() : string
    {
        return $this->getParameter(ListsManager::LISTS_INDEX, false) ? EditFormType::class : NewFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterAction(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicActions() : array
    {
        return array(
            "index" => Actions\Master::class,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredActions() : array
    {
        return array(
            "webhooks" => Actions\Webhooks\Update::class,
        );
    }
}
