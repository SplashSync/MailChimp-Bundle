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
use Splash\Templates\ThirdPartyFields;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Json Metadata Model for MailChimp Members (Subscribers).
 */
#[SPL\SplashObject(
    type: "ThirdParty",
    name: "Customer",
    description: "MailChimp Subscriber",
    ico: "fa fa-user",
)]
#[RestResource(
    collectionUri: "/members",
    itemUri: "/members/{id}",
)]
/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Member
{
    use Member\EmailTrait;

    /**
     * Member ID (MD5 hash of lowercase email)
     */
    #[Serializer\SerializedName("id")]
    #[Serializer\Groups(array(SplGroups::READ, SplGroups::LIST))]
    public string $id = "";

    /**
     * Member Status (subscribed, unsubscribed, cleaned, pending, transactional)
     */
    #[Serializer\Groups(SplGroups::DEFAULT)]
    #[Serializer\SerializedName("status")]
    public string $status = "subscribed";

    /**
     * Status if New (for PUT upsert)
     */
    #[Serializer\Groups(array(SplGroups::WRITE))]
    #[Serializer\SerializedName("status_if_new")]
    public string $status_if_new = "subscribed";

    /**
     * Is Subscribed (computed from status)
     */
    #[SPL\Field(
        type: SplFields::BOOL,
        name: "Is Subscribed",
        desc: "Member subscription status",
    )]
    #[SPL\Microdata("http://schema.org/Organization", "newsletter")]
    #[SPL\Flags(listed: true)]
    #[SPL\IsNotTested]
    #[Serializer\Groups(array(SplGroups::READ, SplGroups::LIST))]
    public ?bool $isSubscribed = null;

    /**
     * VIP Flag
     */
    #[SPL\Field(
        type: SplFields::BOOL,
        name: "Is VIP",
        desc: "Member VIP status",
    )]
    #[SPL\Microdata("http://schema.org/Organization", "vip")]
    #[Serializer\Groups(SplGroups::DEFAULT)]
    #[Serializer\SerializedName("vip")]
    public bool $vip = false;

    /**
     * Signup Date
     */
    #[SPL\Template(ThirdPartyFields::DATE_CREATED)]
    #[SPL\IsReadOnly]
    #[Serializer\Groups(array(SplGroups::READ))]
    #[Serializer\SerializedName("timestamp_signup")]
    public ?string $timestamp_signup = null;

    /**
     * Last Changed Date
     */
    #[SPL\Template(ThirdPartyFields::DATE_MODIFIED)]
    #[SPL\IsReadOnly]
    #[Serializer\Groups(array(SplGroups::READ))]
    #[Serializer\SerializedName("last_changed")]
    public ?string $last_changed = null;

    //====================================================================//
    // Merge Fields (Dynamic, managed by MergeFieldsManager)
    //====================================================================//

    /**
     * Merge Fields Values
     *
     * @var null|array<string, null|string>
     */
    #[Serializer\Groups(SplGroups::DEFAULT)]
    #[Serializer\SerializedName("merge_fields")]
    public ?array $merge_fields = null;

    //====================================================================//
    // Getters & Setters
    //====================================================================//

    /**
     * Get Member ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get Is Subscribed from Status
     */
    public function getIsSubscribed(): bool
    {
        return "subscribed" === $this->status;
    }

    /**
     * Set Status from Is Subscribed
     */
    public function setIsSubscribed(bool $isSubscribed): void
    {
        $this->status = $isSubscribed ? "subscribed" : "unsubscribed";
    }
}
