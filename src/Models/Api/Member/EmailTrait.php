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

namespace Splash\Connectors\MailChimp\Models\Api\Member;

use Splash\Metadata\Attributes as SPL;
use Splash\OpenApi\Dictionary\SerializerGroups as SplGroups;
use Splash\Templates\ThirdPartyFields;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * MailChimp Member Email Management
 *
 * Track email changes to handle ID (MD5 hash) updates.
 * MailChimp uses md5(strtolower(email)) as member ID,
 * so changing the email requires delete + create.
 */
trait EmailTrait
{
    /**
     * Member Email Address
     */
    #[SPL\Template(ThirdPartyFields::EMAIL)]
    #[SPL\IsPrimary]
    #[SPL\IsRequired]
    #[Serializer\Groups(SplGroups::ALL)]
    #[Serializer\SerializedName("email_address")]
    public string $email_address = "";

    /**
     * Previous Email Address (before update)
     */
    private ?string $oldEmail = null;

    /**
     * Get Previous Email Address
     */
    public function getOldEmail(): ?string
    {
        return $this->oldEmail;
    }

    /**
     * Set Email Address and Track Previous Value
     */
    public function setEmailAddress(string $emailAddress): static
    {
        if (!empty($this->email_address) && $this->email_address !== $emailAddress) {
            $this->oldEmail = $this->email_address;
        }
        $this->email_address = $emailAddress;

        return $this;
    }

    /**
     * Check if Email Address has Changed
     */
    public function hasEmailChanged(): bool
    {
        return null !== $this->oldEmail && $this->oldEmail !== $this->email_address;
    }
}
