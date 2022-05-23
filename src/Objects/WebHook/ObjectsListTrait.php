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

namespace   Splash\Connectors\MailChimp\Objects\WebHook;

use Splash\Connectors\MailChimp\Models\MailChimpHelper as API;

/**
 * MailChimp WebHook Objects List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Prepare Parameters
        $body = array();
        if (isset($params["max"], $params["offset"])) {
            $body['count'] = $params["max"];
            $body['offset'] = $params["offset"];
        }
        //====================================================================//
        // Get User Lists from Api
        $rawData = API::get('lists/'.API::getList().'/webhooks', $body);
        //====================================================================//
        // Request Failed
        if (null == $rawData) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response = array(
            // @codingStandardsIgnoreStart
            'meta' => array('current' => count($rawData->webhooks), 'total' => $rawData->total_items),
            // @codingStandardsIgnoreEnd
        );
        //====================================================================//
        // Parse Data in response
        foreach ($rawData->webhooks as $webhook) {
            $response[] = array(
                'id' => $webhook->id,
                'url' => $webhook->url,
            );
        }

        return $response;
    }
}
