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

namespace Splash\Connectors\MailChimp\Objects\WebHook;

use Splash\Connectors\MailChimp\Models\MailChimpHelper as API;
use Splash\Core\SplashCore      as Splash;
use stdClass;

/**
 * MailChimp WebHook CRUD Functions
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
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Execute Read Request
        $mcWebHook = API::get(self::getUri($objectId));
        //====================================================================//
        // Fatch Object
        if (null == $mcWebHook) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load WebHook (".$objectId.").");
        }

        return $mcWebHook;
    }

    /**
     * Create Request Object
     *
     * @param string $url
     *
     * @return false|stdClass New Object
     */
    public function create(string $url = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($url) && empty($this->in["url"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "url");
        }
        //====================================================================//
        // Init Object
        $this->object = new stdClass();
        //====================================================================//
        // Pre-Setup of Member
        $this->setSimple("url", empty($url) ? $this->in["url"] : $url);
        //====================================================================//
        // WebHooks Events Triggers
        $events = new stdClass();
        $events->subscribe = true;
        $events->unsubscribe = true;
        $events->subscribe = true;
        $events->profile = true;
        $events->cleaned = true;
        $events->campaign = true;
        $this->setSimple("events", $events);
        //====================================================================//
        // WebHooks Events Sources
        $sources = new stdClass();
        $sources->user = true;
        $sources->admin = true;
        $sources->api = false;
        $this->setSimple("status", $sources);
               
        //====================================================================//
        // Create Object
        $this->object = API::post(
            self::getUri(),
            $this->object
        );
        if (is_null($this->object) || empty($this->object->id)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update WebHook");
        }

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
        // Update Not Allowed
        Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " WebHook Update is diasbled.");
        
        return $this->getObjectIdentifier();
    }
    
    /**
     * Delete requested Object
     *
     * @param string $objectId Object Id
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
        $response = API::delete(self::getUri($objectId));
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
     * Get Object CRUD Uri
     *
     * @param string $objectId
     *
     * @return string
     */
    private static function getUri(string $objectId = null) : string
    {
        $baseUri = 'lists/'.API::getList().'/webhooks';
        if (!is_null($objectId)) {
            return $baseUri."/".$objectId;
        }

        return $baseUri;
    }
}
