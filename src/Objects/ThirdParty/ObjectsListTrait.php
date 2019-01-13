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

namespace   Splash\Connectors\MailChimp\Objects\ThirdParty;

use Splash\Connectors\MailChimp\Models\MailChimpHelper as API;

/**
 * MailChimp Users Objects List Functions
 */
trait ObjectsListTrait
{
    /**
     *  @abstract     Return List Of Customer with required filters
     *
     *  @param        string  $filter                   Filters/Search String for Contact List.
     *  @param        array   $params                   Search parameters for result List.
     *                        $params["max"]            Maximum Number of results
     *                        $params["offset"]         List Start Offset
     *                        $params["sortfield"]      Field name for sort list (Available fields listed below)
     *                        $params["sortorder"]      List Order Constraign (Default = ASC)
     *
     *  @return       array   $data                     List of all customers main data
     *                        $data["meta"]["total"]     ==> Total Number of results
     *                        $data["meta"]["current"]   ==> Total Number of results
     */
    public function ObjectsList($filter=null, $params=null)
    {
        //====================================================================//
        // Prepare Parameters
        $body     =    array();
        if (isset($params["max"], $params["offset"])) {
            $body['count']    =   $params["max"];
            $body['offset']   =   $params["offset"];
        }
        //====================================================================//
        // Get User Lists from Api
        $rawData  =   API::get('lists/' . API::getList() . '/members', $body);
        //====================================================================//
        // Request Failed
        if (null == $rawData) {
            return array( 'meta'    => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response   =   array(
            'meta'  => array('current' => count($rawData->members), 'total' => $rawData->total_items)
        );
        //====================================================================//
        // Parse Data in response
        foreach ($rawData->members as $member) {
            $response[]   = array(
                'id'                =>      API::hash($member->email_address),
                'email_address'     =>      $member->email_address,
                'status'            =>      ucwords($member->status),
                'FNAME'             =>      $member->merge_fields->FNAME,
                'LNAME'             =>      $member->merge_fields->LNAME,
            );
        }

        return $response;
    }
}
