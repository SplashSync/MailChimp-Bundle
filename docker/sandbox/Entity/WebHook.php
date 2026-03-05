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
use App\Controller\WebHook\CreateWebHookController;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * MailChimp WebHook Entity.
 *
 * Fakes /3.0/lists/{list_id}/webhooks endpoints.
 */
#[ORM\Entity]
#[API\ApiResource(
    uriTemplate: '/3.0/lists/{listId}/webhooks',
    operations: array(
        new API\GetCollection(),
        new API\Post(
            controller: CreateWebHookController::class,
            read: false,
        ),
    ),
    uriVariables: array(
        'listId' => new Link(fromClass: MailingList::class, toProperty: 'list'),
    )
)]
#[API\ApiResource(
    uriTemplate: '/3.0/lists/{listId}/webhooks/{id}',
    operations: array(
        new API\Get(),
        new API\Delete(status: 204, output: false),
    ),
    uriVariables: array(
        'listId' => new Link(fromClass: MailingList::class, toProperty: 'list'),
        'id' => new Link(fromClass: self::class),
    )
)]
class WebHook
{
    /**
     * WebHook ID.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[SerializedName("id")]
    public int $id;

    /**
     * WebHook URL.
     */
    #[ORM\Column(length: 500)]
    #[SerializedName("url")]
    public string $url = "";

    /**
     * Events configuration.
     *
     * @var array<string, bool>
     */
    #[ORM\Column(type: Types::JSON)]
    #[SerializedName("events")]
    public array $events = array();

    /**
     * Sources configuration.
     *
     * @var array<string, bool>
     */
    #[ORM\Column(type: Types::JSON)]
    #[SerializedName("sources")]
    public array $sources = array();

    /**
     * Mailing List relation.
     */
    #[ORM\ManyToOne(targetEntity: MailingList::class)]
    #[ORM\JoinColumn(name: 'list_id', referencedColumnName: 'id', nullable: false)]
    #[Ignore]
    public ?MailingList $list = null;

    /**
     * Get List ID as string (for MailChimp API format).
     */
    #[SerializedName("list_id")]
    public function getListId(): string
    {
        return $this->list ? (string) $this->list->id : "";
    }
}
