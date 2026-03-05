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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * MailChimp Mailing List Entity.
 *
 * Fakes /3.0/lists endpoints.
 */
#[ORM\Entity]
#[API\ApiResource(
    uriTemplate: '/3.0/lists',
    operations: array(
        new API\GetCollection(),
    )
)]
#[API\ApiResource(
    uriTemplate: '/3.0/lists/{id}',
    operations: array(
        new API\Get(),
    )
)]
class MailingList
{
    /**
     * List ID.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[SerializedName("id")]
    public int $id;

    /**
     * List name.
     */
    #[ORM\Column(length: 255)]
    #[SerializedName("name")]
    public string $name = "";

    /**
     * Contact information.
     *
     * @var array<string, string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[SerializedName("contact")]
    public array $contact = array();

    /**
     * Campaign defaults.
     *
     * @var array<string, string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[SerializedName("campaign_defaults")]
    public array $campaign_defaults = array();
}
