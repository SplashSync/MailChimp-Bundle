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

namespace Splash\Connectors\MailChimp\Models\Api;

use Splash\Core\Dictionary\SplFields;
use Splash\Metadata\Attributes as SPL;
use Splash\OpenApi\Attributes\Rest\RestResource;
use Splash\OpenApi\Dictionary\SerializerGroups as SplGroups;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Json Metadata Model for MailChimp WebHooks.
 */
#[SPL\SplashObject(
    type: "Webhook",
    name: "WebHook",
    description: "MailChimp WebHook",
    ico: "fa fa-plug",
)]
#[RestResource(
    collectionUri: "/webhooks",
    itemUri: "/webhooks/{id}",
)]
/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Webhook
{
    /**
     * WebHook ID on API
     */
    #[Serializer\SerializedName("id")]
    #[Serializer\Groups(array(SplGroups::READ, SplGroups::LIST))]
    public string $id = "";

    /**
     * WebHook Endpoint URL
     */
    #[SPL\Field(
        type: SplFields::URL,
        name: "Url",
        desc: "WebHook endpoint URL",
    )]
    #[SPL\Flags(required: true, listed: true)]
    #[Serializer\Groups(SplGroups::ALL)]
    #[Serializer\SerializedName("url")]
    public string $url = '';

    /**
     * WebHook Events Configuration
     *
     * @var array<string, bool>
     */
    #[Serializer\Groups(SplGroups::DEFAULT)]
    #[Serializer\SerializedName("events")]
    public array $events = array();

    /**
     * WebHook Sources Configuration
     *
     * @var array<string, bool>
     */
    #[Serializer\Groups(SplGroups::DEFAULT)]
    #[Serializer\SerializedName("sources")]
    public array $sources = array();

    /**
     * List ID
     */
    #[Serializer\Groups(array(SplGroups::READ))]
    #[Serializer\SerializedName("list_id")]
    public ?string $list_id = null;

    //====================================================================//
    // Getters & Setters
    //====================================================================//

    /**
     * Get WebHook ID
     */
    public function getId(): string
    {
        return $this->id;
    }
}
