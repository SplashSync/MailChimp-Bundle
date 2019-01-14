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

namespace Splash\Connectors\MailChimp\Controller;

use Psr\Log\LoggerInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\MailChimp\Objects\ThirdParty;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Splash MailChimp WebHooks Actions Controller
 */
class WebHooksController extends Controller
{
    /**
     * Execute WebHook Actions for A MailChimp Connector
     *
     * @param LoggerInterface   $logger
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return JsonResponse
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function indexAction(LoggerInterface $logger, Request $request, AbstractConnector $connector)
    {
        //====================================================================//
        // For Mailchimp Ping GET
        if ($request->isMethod('GET')) {
            $logger->error(__CLASS__.'::'.__FUNCTION__.' MailChimp Ping.', $request->attributes->all());

            return new JsonResponse(array( 'success' => true, 'ping' => 'pong' ));
        }
        
        //====================================================================//
        // Read Request Parameters
        $type = $request->request->get('type');
        $data = $request->request->get('data');
        
        //====================================================================//
        // Log MailChimp Request
        $logger->info(__CLASS__.'::'.__FUNCTION__.' WebHook Type '.$type.'.', (is_array($data) ? $data : array()));
        
        //====================================================================//
        // Verify Impacted List is Node Selected List
        if ($connector->getParameter('ApiList') != $data["list_id"]) {
            $logger->error(__CLASS__.'::'.__FUNCTION__.' MailChimp Wrong List.', $request->attributes->all());

            return new JsonResponse(array( 'success' => true, 'ping' => 'pong' ));
        }
        
        //==============================================================================
        // Detect Change Parameters
        if (("unsubscribe" == $type) && ('delete' == $data["action"])) {
            $action     =   SPL_A_DELETE;
            $objectId   =   ThirdParty::hash($data["email"]);
        } elseif (in_array($type, array("subscribe", "unsubscribe", "profile" ), true)) {
            $action     =   SPL_A_UPDATE;
            $objectId   =   ThirdParty::hash($data["email"]);
        } elseif (in_array($type, array("upemail"), true)) {
            //====================================================================//
            // Update Object Id as Changed by this Request (Email Modified)
            $connector->objectIdChanged(
                "ThirdParty",
                ThirdParty::hash($data["old_email"]),
                ThirdParty::hash($data["new_email"])
            );
            $action     =   SPL_A_UPDATE;
            $objectId   =   ThirdParty::hash($data["new_email"]);
        } elseif (in_array($type, array("cleaned"), true)) {
            $action     =   SPL_A_DELETE;
            $objectId   =   ThirdParty::hash($data["email"]);
        } else {
            return new JsonResponse(array( 'success' => true, 'ping' => 'pong' ));
        }
        
        //==============================================================================
        // Commit Changes
        $connector->commit('ThirdParty', $objectId, $action, "MailChimp API", "Member Updated");
        
        //==============================================================================
        // Send Response
        return new JsonResponse(array('success' => true, 'type' => $type));
    }
}
