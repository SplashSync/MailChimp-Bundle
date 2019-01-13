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
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return mixed
     */
    public function Load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        $this->objectIdChanged = false;
        //====================================================================//
        // Execute Read Request
        $mcObject = API::get(self::getBaseUri().$objectId);
        //====================================================================//
        // Fatch Object
        if (null == $mcObject) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Member (" . $objectId . ").");
        }

        return $mcObject;
    }

    /**
     * Create Request Object
     *
     * @param array $List Given Object Data
     *
     * @return object New Object
     */
    public function Create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
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
     * @return fale|string Object Id of False if Failed to Update
     */
    public function Update(bool $needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        if (!$needed) {
            return $this->object->id;
        }
        
        //====================================================================//
        // Generate id If Needed
        if (!isset($this->object->id) || empty($this->object->id)) {
            $this->object->id   =   API::hash($this->object->email_address);
        }
        //====================================================================//
        // Update Object
        $response = API::put(
                self::getBaseUri() . "/" . $this->object->id,
                $this->object
            );
        if (is_null($response)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update Member (" . $this->object->email_address . ").");
        }
        //====================================================================//
        // Update Object Id if Changed by this Request (Email Modified)
        if (isset($this->objectIdChanged) && $this->objectIdChanged) {
            $this->connector->objectIdChanged(
                    "ThirdParty",
                    $this->object->id,
                    API::hash($this->object->email_address)
                );
        }

        return $this->object->id;
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
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Delete Object
        $response = API::delete(
                self::getBaseUri() . "/" . $objectId,
                $this->object
            );
        if (true !==$response) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Delete Member (" . $objectId . ").");
        }

        return true;
    }
    
    /**
     * Get Object CRUD Base Uri
     *
     * @return string
     */
    private static function getBaseUri(string $email = null) : string
    {
        $baseUri = 'lists/' . API::getList() . '/members/';
        if (!is_null($email)) {
            return $baseUri . "/" . API::hash($email);
        }

        return $baseUri;
    }
}
