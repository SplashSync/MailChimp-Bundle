<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
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
     * @param mixed $email
     *
     * @return string $result
     */
    public static function hash($email) : string
    {
        return md5(strtolower($email));
    }

    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return mixed
     */
    public function load($objectId)
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
            return Splash::log()->errTrace("Unable to load Member (".$objectId.").");
        }
        //====================================================================//
        // Check Object Status
        if ("archived" == $mcObject->status) {
            return Splash::log()->errTrace("Member is Archived, you can't read it! (".$objectId.").");
        }

        return $mcObject;
    }

    /**
     * Create Request Object
     *
     * @return false|stdClass New Object
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["email_address"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "email_address");
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
     * @return false|string Object Id of False if Failed to Update
     */
    public function update(bool $needed)
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
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update Member (".$this->object->email_address.").");
        }
        //====================================================================//
        // Update Object Id if Changed by this Request (Email Modified)
        if (isset($this->objectIdChanged) && $this->objectIdChanged) {
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
     * Delete requested Object
     *
     * @param int $objectId Object Id
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Object
        $response = API::delete(self::getBaseUri()."/".$objectId);
        if (null === $response) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Delete Member (".$objectId.").");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (!isset($this->object->id)) {
            return false;
        }

        return $this->object->id;
    }

    /**
     * Get Object CRUD Base Uri
     *
     * @param string $email
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
