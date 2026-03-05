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

namespace Splash\Connectors\MailChimp\Objects\ThirdParty;

use Splash\Connectors\MailChimp\Models\Api\Member;
use Splash\Connectors\MailChimp\Objects\ThirdParty;
use Splash\Core\Client\Splash;

/**
 * MailChimp Members CRUD Functions
 */
trait CRUDTrait
{
    /**
     * {@inheritDoc}
     */
    public function load(string $objectId): ?object
    {
        $member = $this->coreLoad($objectId);
        //====================================================================//
        // In CI/CD Mode, filter out archived members (MailChimp archive on delete)
        if (Splash::isCiCdMode() && $member instanceof Member && "archived" === $member->status) {
            return null;
        }

        return $member;
    }

    /**
     * Create Request Object
     */
    public function create(): ?Member
    {
        //====================================================================//
        // Check Email is given
        if (empty($this->in["email_address"]) || !is_string($this->in["email_address"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "email_address");

            return null;
        }
        //====================================================================//
        // Execute Core Create
        $member = $this->coreCreate();
        if (!$member instanceof Member) {
            return null;
        }

        return $member;
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID or NULL if Failed
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // No Update Required
        if (!$needed) {
            return $this->object->getId();
        }

        //====================================================================//
        // Email Changed => Delete old + Create new (ID is MD5 hash of email)
        if ($this->object->hasEmailChanged()) {
            $oldHash = ThirdParty::hash((string) $this->object->getOldEmail());
            //====================================================================//
            // Delete Old Member
            $this->delete($oldHash);
            //====================================================================//
            // Create New Member
            $createResponse = $this->visitor->create($this->object);
            if (!$createResponse->isSuccess()) {
                return Splash::log()->errNull(
                    "Unable to Create Member (".$this->object->email_address.")."
                );
            }
            //====================================================================//
            // Dispatch Object ID Updated Event
            $newHash = ThirdParty::hash($this->object->email_address);
            $this->connector->objectIdChanged("ThirdParty", $oldHash, $newHash);

            return $newHash;
        }

        //====================================================================//
        // Standard Update
        $objectId = $this->coreUpdate(true);

        return $objectId ?: null;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(?string $objectId = null): bool
    {
        //====================================================================//
        // Execute Core Delete
        if ($this->coreDelete($objectId)) {
            return true;
        }
        //====================================================================//
        // MailChimp returns 405 when member is already archived
        $lastResponse = $this->visitor->getConnexion()->getLastResponse();
        if ($lastResponse && 405 === $lastResponse->code) {
            Splash::log()->cleanLog();

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getByPrimary(array $keys): ?string
    {
        //====================================================================//
        // Safety Check
        $email = $keys['email_address'] ?? null;
        if (!$email) {
            return null;
        }
        //====================================================================//
        // Try to Load Contact by MD5 Hash of Email
        $member = $this->load(ThirdParty::hash((string) $email));
        //====================================================================//
        // Clean Splash Log
        Splash::log()->cleanLog();

        return $member instanceof Member ? $member->getId() : null;
    }
}
