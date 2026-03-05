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

namespace Splash\Connectors\MailChimp\Objects;

use Splash\Connectors\MailChimp\Connectors\MailChimpConnector;
use Splash\Connectors\MailChimp\Dictionary\WebhookEventTypes;
use Splash\Connectors\MailChimp\Models\Api\Webhook as WebhookModel;
use Splash\Core\Client\Splash;
use Splash\OpenApi\Models\Objects\AbstractRestAndMetadataObject;

/**
 * MailChimp Implementation of WebHooks
 */
class Webhook extends AbstractRestAndMetadataObject
{
    /**
     * @inheritDoc
     */
    protected static bool $disabled = true;

    /**
     * @var WebhookModel
     */
    protected object $object;

    /**
     * @var MailChimpConnector
     */
    protected MailChimpConnector $connector;

    /**
     * Class Constructor
     */
    public function __construct(MailChimpConnector $connector)
    {
        parent::__construct(
            $visitor = $connector->getVisitor(WebhookModel::class),
            $visitor->getMetadataAdapter(),
            WebhookModel::class
        );
        $this->connector = $connector;
        //====================================================================//
        //  Load Translation File
        Splash::translator()->load('local');
    }

    /**
     * Override Default Mode
     */
    public static function setDisabled(bool $disabled = true): void
    {
        static::$disabled = $disabled;
    }

    /**
     * Create Splash WebHook from Url
     */
    public function createFromUrl(string $url): bool
    {
        return !empty($this->set(null, array(
            "url" => $url,
            "events" => array(
                WebhookEventTypes::SUBSCRIBE => true,
                WebhookEventTypes::UNSUBSCRIBE => true,
                WebhookEventTypes::PROFILE => true,
                WebhookEventTypes::CLEANED => true,
                WebhookEventTypes::CAMPAIGN => true,
            ),
            "sources" => array(
                "user" => true,
                "admin" => true,
                "api" => false,
            ),
        )));
    }
}
