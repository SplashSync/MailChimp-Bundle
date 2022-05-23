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

use Splash\Connectors\MailChimp\Models\MailChimpHelper as API;
use Splash\Core\SplashCore      as Splash;
use stdClass;

/**
 * MailChimp Users CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Get MailChimp Subscriber Hash
     *
     * @param string $email
     *
     * @return string $result
     */
    public static function hash(string $email) : string
    {
        return md5(strtolower($email));
    }

    /**
     * Load Request Object
     *
     * @param string $objectId Object ID
     *
     * @return null|stdClass
     */
    public function load(string$objectId): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        $this->objectIdChanged = false;
        //====================================================================//
        // Execute Read Request
        $mcObject = API::get(self::getBaseUri().$objectId);
        //====================================================================//
        // Fetch Object
        if (null == $mcObject) {
            return Splash::log()->errNull("Unable to load Member (".$objectId.").");
        }
        //====================================================================//
        // Check Object Status
        if ("archived" == $mcObject->status) {
            return Splash::log()->errNull("Member is Archived, you can't read it! (".$objectId.").");
        }

        return $mcObject;
    }

    /**
     * Create Request Object
     *
     * @return null|stdClass New Object
     */
    public function create(): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["email_address"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "email_address");

            return null;
        }
        //====================================================================//
        // Init Object
        $this->object = new stdClass();
        //====================================================================//
        // Pre-Setup of Member
        $this->setSimple("email_address", $this->in["email_address"]);
        $this->setSimple("status_if_new", "subscribed");
        $this->setSimple("status", "subscribed");
        $this->needUpdate();

        return $this->object;
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID of False if Failed to Update
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (!$needed) {
            return $this->getObjectIdentifier();
        }

        //====================================================================//
        // Generate id If Needed
        if (!isset($this->object->id) || empty($this->object->id)) {
            $this->object->id = self::hash($this->object->email_address);
        }
        //====================================================================//
        // Update Object
        $response = API::put(
            self::getBaseUri()."/".$this->object->id,
            $this->object
        );

        if (is_null($response) || ($response->id != self::hash($this->object->email_address))) {
            return Splash::log()->errNull(" Unable to Update Member (".$this->object->email_address.").");
        }
        //====================================================================//
        // Update Object Id if Changed by this Request (Email Modified)
        if ($this->objectIdChanged) {
            $this->connector->objectIdChanged(
                "ThirdParty",
                $this->object->id,
                self::hash($this->object->email_address)
            );

            return self::hash($this->object->email_address);
        }

        return $this->getObjectIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Object
        $response = API::delete(self::getBaseUri()."/".$objectId);
        if (null === $response) {
            return Splash::log()->errTrace(" Unable to Delete Member (".$objectId.").");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        return $this->object->id ?? null;
    }

    /**
     * Get Object CRUD Base Uri
     *
     * @param null|string $email
     *
     * @return string
     */
    private static function getBaseUri(string $email = null) : string
    {
        $baseUri = 'lists/'.API::getList().'/members/';
        if (!is_null($email)) {
            return $baseUri."/".self::hash($email);
        }

        return $baseUri;
    }
}
