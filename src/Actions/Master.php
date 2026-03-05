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

namespace Splash\Connectors\MailChimp\Actions;

use Psr\Log\LoggerInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\MailChimp\Dictionary\WebhookEventTypes;
use Splash\Connectors\MailChimp\Objects\ThirdParty;
use Splash\Core\Dictionary\SplOperations;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Splash MailChimp WebHooks Actions Controller
 */
class Master extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Execute WebHook Actions for A MailChimp Connector
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, AbstractConnector $connector): JsonResponse
    {
        //====================================================================//
        // For Mailchimp Ping GET
        if ($request->isMethod('GET')) {
            $this->logger->notice(__CLASS__.'::'.__FUNCTION__.' MailChimp Ping.', $request->attributes->all());

            return new JsonResponse(array('success' => true, 'ping' => 'pong'));
        }

        //====================================================================//
        // Read Request Parameters
        $type = $this->extractType($request);
        $data = $this->extractData($request);

        //====================================================================//
        // Log MailChimp Request
        $this->logger->info(__CLASS__.'::'.__FUNCTION__.' WebHook Type '.$type.'.', $data);

        //====================================================================//
        // Verify Impacted List is Node Selected List
        if ($connector->getParameter('ApiList') != $data["list_id"]) {
            $this->logger->error(__CLASS__.'::'.__FUNCTION__.' MailChimp Wrong List.', $request->attributes->all());

            return new JsonResponse(array('success' => true, 'ping' => 'pong'));
        }

        //==============================================================================
        // Detect Change Parameters
        if ($this->isDeleteEvent($type, $data)) {
            $action = SplOperations::DELETE;
            $objectId = ThirdParty::hash($data["email"]);
        } elseif ($this->isUpdateEvent($type)) {
            $action = SplOperations::UPDATE;
            $objectId = ThirdParty::hash($data["email"]);
        } elseif (WebhookEventTypes::UPEMAIL === $type) {
            //====================================================================//
            // Update Object Id as Changed by this Request (Email Modified)
            $connector->objectIdChanged(
                "ThirdParty",
                ThirdParty::hash($data["old_email"]),
                ThirdParty::hash($data["new_email"])
            );
            $action = SplOperations::UPDATE;
            $objectId = ThirdParty::hash($data["new_email"]);
        } else {
            return new JsonResponse(array('success' => true, 'ping' => 'pong'));
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
     * @throws BadRequestHttpException
     */
    private function extractType(Request $request): string
    {
        //==============================================================================
        // Safety Check => Data are here
        if (!$request->isMethod('POST')) {
            throw new BadRequestHttpException('Malformed or missing data');
        }
        //==============================================================================
        // Decode Received Type (POST form or JSON body)
        $postData = $request->request->all();
        /** @var null|array $jsonData */
        $jsonData = json_decode($request->getContent(), true, 512, \JSON_BIGINT_AS_STRING);
        $requestType = $postData['type'] ?? $jsonData['type'] ?? null;
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
        // Decode Received Data (POST form or JSON body)
        $postData = $request->request->all();
        /** @var null|array $jsonData */
        $jsonData = json_decode($request->getContent(), true, 512, \JSON_BIGINT_AS_STRING);
        $requestData = $postData['data'] ?? $jsonData['data'] ?? null;
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
     */
    private function isUpdateEvent(string $type) : bool
    {
        return in_array($type, array(
            WebhookEventTypes::SUBSCRIBE,
            WebhookEventTypes::UNSUBSCRIBE,
            WebhookEventTypes::PROFILE,
        ), true);
    }

    /**
     * Check if Event is Delete Event
     *
     * @param array $data
     */
    private function isDeleteEvent(string $type, array $data) : bool
    {
        if ((WebhookEventTypes::UNSUBSCRIBE === $type) && isset($data["action"]) && ('delete' === $data["action"])) {
            return true;
        }

        return WebhookEventTypes::CLEANED === $type;
    }
}
