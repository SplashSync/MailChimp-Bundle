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

namespace App\Entity;

use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Link;
use App\Controller\Member\GetByHashController;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * MailChimp Member (Subscriber) Entity.
 *
 * Fakes /3.0/lists/{list_id}/members endpoints.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[API\ApiResource(
    uriTemplate: '/3.0/lists/{listId}/members',
    uriVariables: array(
        'listId' => new Link(fromClass: MailingList::class, toProperty: 'list'),
    ),
    operations: array(
        new API\GetCollection(),
        new API\Post(),
    )
)]
#[API\ApiResource(
    uriTemplate: '/3.0/lists/{listId}/members/{subscriberHash}',
    uriVariables: array(
        'listId' => new Link(fromClass: MailingList::class, toProperty: 'list'),
        'subscriberHash' => new Link(fromClass: self::class, identifiers: array('id')),
    ),
    operations: array(
        new API\Get(controller: GetByHashController::class, read: false),
        new API\Put(extraProperties: array('standard_put' => false)),
        new API\Delete(status: 204, output: false),
    )
)]
class Member
{
    use Traits\AuditTrait;

    /**
     * Internal DB ID.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Ignore]
    public int $dbId;

    /**
     * Member ID (MD5 hash of lowercase email).
     */
    #[ORM\Column(length: 32, unique: true)]
    #[SerializedName("id")]
    public string $id = "";

    /**
     * Member email address.
     */
    #[ORM\Column(unique: true, nullable: false)]
    #[SerializedName("email_address")]
    public string $email_address = "";

    /**
     * Subscription status.
     */
    #[ORM\Column(length: 50)]
    #[SerializedName("status")]
    public string $status = "subscribed";

    /**
     * VIP flag.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    #[SerializedName("vip")]
    public bool $vip = false;

    /**
     * Merge fields (FNAME, LNAME, etc.).
     *
     * @var array<string, string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[SerializedName("merge_fields")]
    public array $merge_fields = array();

    /**
     * Mailing List relation.
     */
    #[ORM\ManyToOne(targetEntity: MailingList::class)]
    #[ORM\JoinColumn(name: 'list_id', referencedColumnName: 'id', nullable: false)]
    #[Ignore]
    public ?MailingList $list = null;

    /**
     * Signup timestamp.
     */
    #[ORM\Column(length: 50, nullable: true)]
    #[SerializedName("timestamp_signup")]
    public ?string $timestamp_signup = null;

    /**
     * Last changed timestamp.
     */
    #[ORM\Column(length: 50, nullable: true)]
    #[SerializedName("last_changed")]
    public ?string $last_changed = null;

    public function __construct()
    {
        $this->initAudit();
    }

    /**
     * Auto-generate ID hash and timestamps on persist.
     */
    #[ORM\PrePersist]
    public function generateHash(): void
    {
        if (empty($this->id) && !empty($this->email_address)) {
            $this->id = md5(strtolower($this->email_address));
        }
        $now = (new \DateTime())->format('c');
        if (empty($this->timestamp_signup)) {
            $this->timestamp_signup = $now;
        }
        $this->last_changed = $now;
    }

    /**
     * Update last_changed on update.
     */
    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->last_changed = (new \DateTime())->format('c');
        //====================================================================//
        // Recalculate hash if email changed
        if (!empty($this->email_address)) {
            $this->id = md5(strtolower($this->email_address));
        }
    }
}
