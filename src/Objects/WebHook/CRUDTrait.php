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

namespace Splash\Connectors\MailChimp\Objects\WebHook;

use Splash\Connectors\MailChimp\Models\MailChimpHelper as API;
use Splash\Core\SplashCore as Splash;
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
     * @return null|stdClass
     */
    public function load(string $objectId): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Execute Read Request
        $mcWebHook = API::get(self::getUri($objectId));
        //====================================================================//
        // Fetch Object
        if (null == $mcWebHook) {
            return Splash::log()->errNull(" Unable to load WebHook (".$objectId.").");
        }

        return $mcWebHook;
    }

    /**
     * Create Request Object
     *
     * @param null|string $url
     *
     * @return null|stdClass New Object
     */
    public function create(string $url = null): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($url) && empty($this->in["url"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "url");

            return null;
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
        $object = API::post(
            self::getUri(),
            $this->object
        );
        if (is_null($object) || empty($object->id)) {
            return Splash::log()->errNull(" Unable to Update WebHook");
        }

        return $this->object = $object;
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID of NULL if Failed to Update
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
        // Update Not Allowed
        Splash::log()->errTrace(" WebHook Update is disabled.");

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
        $response = API::delete(self::getUri($objectId));
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
     * Get Object CRUD Uri
     *
     * @param null|string $objectId
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
