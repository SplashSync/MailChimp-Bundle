<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
    public function indexAction(LoggerInterface $logger, Request $request, AbstractConnector $connector): JsonResponse
    {
        //====================================================================//
        // For Mailchimp Ping GET
        if ($request->isMethod('GET')) {
            $logger->error(__CLASS__.'::'.__FUNCTION__.' MailChimp Ping.', $request->attributes->all());

            return new JsonResponse(array( 'success' => true, 'ping' => 'pong' ));
        }

        //====================================================================//
        // Read Request Parameters
        $type = $this->extractType($request);
        $data = $this->extractData($request);

        //====================================================================//
        // Log MailChimp Request
        $logger->info(__CLASS__.'::'.__FUNCTION__.' WebHook Type '.$type.'.', $data);

        //====================================================================//
        // Verify Impacted List is Node Selected List
        if ($connector->getParameter('ApiList') != $data["list_id"]) {
            $logger->error(__CLASS__.'::'.__FUNCTION__.' MailChimp Wrong List.', $request->attributes->all());

            return new JsonResponse(array( 'success' => true, 'ping' => 'pong' ));
        }

        //==============================================================================
        // Detect Change Parameters
        if ($this->isDeleteEvent($type, $data)) {
            $action = SPL_A_DELETE;
            $objectId = ThirdParty::hash($data["email"]);
        } elseif ($this->isUpdateEvent($type)) {
            $action = SPL_A_UPDATE;
            $objectId = ThirdParty::hash($data["email"]);
        } elseif (in_array($type, array("upemail"), true)) {
            //====================================================================//
            // Update Object Id as Changed by this Request (Email Modified)
            $connector->objectIdChanged(
                "ThirdParty",
                ThirdParty::hash($data["old_email"]),
                ThirdParty::hash($data["new_email"])
            );
            $action = SPL_A_UPDATE;
            $objectId = ThirdParty::hash($data["new_email"]);
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

    /**
     * Extract Type from Request
     *
     * @param Request $request
     *
     * @throws BadRequestHttpException
     *
     * @return string
     */
    private function extractType(Request $request): string
    {
        //==============================================================================
        // Safety Check => Data are here
        if (!$request->isMethod('POST')) {
            throw new BadRequestHttpException('Malformed or missing data');
        }
        //==============================================================================
        // Decode Received Type
        $requestType = empty($request->request->get('type'))
            ? json_decode($request->getContent(), true, 512, \JSON_BIGINT_AS_STRING)['type']
            : $request->request->get('type')
        ;
        //==============================================================================
        // Safety Check => Type are here
        if (empty($requestType) || !is_scalar($requestType)) {
            throw new BadRequestHttpException('Malformed or missing data');
        }
        //==============================================================================
        // Return Request Type
        return (string) $requestType;
    }

    /**
     * Extract Data from Request
     *
     * @param Request $request
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    private function extractData(Request $request): array
    {
        //==============================================================================
        // Safety Check => Data are here
        if (!$request->isMethod('POST')) {
            throw new BadRequestHttpException('Malformed or missing data');
        }
        //==============================================================================
        // Decode Received Data
        $requestData = empty($request->request->get('data'))
            ? json_decode($request->getContent(), true, 512, \JSON_BIGINT_AS_STRING)['data']
            : $request->request->get('data')
        ;
        //==============================================================================
        // Safety Check => Data are here
        if (empty($requestData) || !is_array($requestData)) {
            throw new BadRequestHttpException('Malformed or missing data');
        }
        //==============================================================================
        // Return Request Data
        return $requestData;
    }

    /**
     * Check if Event is Update Event
     *
     * @param string $type
     *
     * @return bool
     */
    private function isUpdateEvent(string $type) : bool
    {
        if (in_array($type, array("subscribe", "unsubscribe", "profile" ), true)) {
            return true;
        }

        return false;
    }

    /**
     * Check if Event is Delete Event
     *
     * @param string $type
     * @param array  $data
     *
     * @return bool
     */
    private function isDeleteEvent(string $type, array $data) : bool
    {
        if (("unsubscribe" == $type) && isset($data["action"]) && ('delete' == $data["action"])) {
            return true;
        }
        if (in_array($type, array("cleaned"), true)) {
            return true;
        }

        return false;
    }
}
