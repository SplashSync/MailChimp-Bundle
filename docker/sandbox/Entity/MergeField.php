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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * MailChimp Merge Field Entity.
 *
 * Fakes /3.0/lists/{list_id}/merge-fields endpoints.
 */
#[ORM\Entity]
#[API\ApiResource(
    uriTemplate: '/3.0/lists/{listId}/merge-fields',
    uriVariables: array(
        'listId' => new Link(fromClass: MailingList::class, toProperty: 'list'),
    ),
    operations: array(
        new API\GetCollection(),
    )
)]
class MergeField
{
    /**
     * Merge Field ID.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[SerializedName("merge_id")]
    public int $id;

    /**
     * Merge field tag (FNAME, LNAME, etc.).
     */
    #[ORM\Column(length: 50)]
    #[SerializedName("tag")]
    public string $tag = "";

    /**
     * Merge field display name.
     */
    #[ORM\Column(length: 255)]
    #[SerializedName("name")]
    public string $name = "";

    /**
     * Merge field type (text, number, phone, etc.).
     */
    #[ORM\Column(length: 50)]
    #[SerializedName("type")]
    public string $type = "text";

    /**
     * Required flag.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    #[SerializedName("required")]
    public bool $required = false;

    /**
     * Default value.
     */
    #[ORM\Column(length: 255)]
    #[SerializedName("default_value")]
    public string $default_value = "";

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
