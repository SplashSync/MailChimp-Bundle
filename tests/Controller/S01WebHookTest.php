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

namespace Splash\Connectors\MailChimp\Test\Controller;

use Splash\Connectors\MailChimp\Connectors\MailChimpConnector;
use Splash\Connectors\MailChimp\Dictionary\WebhookEventTypes;
use Splash\Connectors\MailChimp\Objects\ThirdParty;
use Splash\Core\Dictionary\SplOperations;
use Splash\Tests\Tools\TestCase;

/**
 * Test of MailChimp Connector WebHook Controller
 */
class S01WebHookTest extends TestCase
{
    const PING_RESPONSE = '{"success":true,"ping":"pong"}';
    const MEMBER = "ThirdParty";
    const FAKE_EMAIL = "fake@exemple.com";
    const METHOD = "JSON";

    /**
     * Test WebHook For Ping
     */
    public function testWebhookPing(): void
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("mailchimp");
        $this->assertInstanceOf(MailChimpConnector::class, $connector);

        //====================================================================//
        // Touch Url
        $this->assertPublicActionWorks($connector);
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());

        $this->assertPublicActionFail($connector, null, array(), "POST");
        $this->assertPublicActionFail($connector, null, array(), self::METHOD);
    }

    /**
     * Test WebHook with Errors
     */
    public function testWebhookErrors(): void
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("mailchimp");
        $this->assertInstanceOf(MailChimpConnector::class, $connector);

        //====================================================================//
        // GOOD LIST ID BUT GET METHOD
        //====================================================================//

        //====================================================================//
        // Prepare Request
        $data = array(
            "data" => array(
                "list_id" => $connector->getParameter("ApiList"),
            ),
        );

        //====================================================================//
        // Touch Url
        $this->assertPublicActionWorks($connector, null, $data, "GET");
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());

        //====================================================================//
        // WRONG LIST ID
        //====================================================================//

        //====================================================================//
        // Prepare Request
        $data2 = array(
            "data" => array(
                "list_id" => "ThisIsWrong",
            ),
        );

        //====================================================================//
        // POST MODE
        $this->assertPublicActionFail($connector, null, $data2, "POST");
        //====================================================================//
        // JSON MODE
        $this->assertPublicActionFail($connector, null, $data2, self::METHOD);

        //====================================================================//
        // GOOD LIST ID BUT WRONG TYPE
        //====================================================================//

        //====================================================================//
        // Prepare Request
        $data3 = array(
            "type" => "ThisIsWrong",
            "data" => array(
                "list_id" => $connector->getParameter("ApiList"),
            ),
        );

        //====================================================================//
        // POST MODE
        $this->assertPublicActionWorks($connector, null, $data3, "POST");
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());
        //====================================================================//
        // JSON MODE
        $this->assertPublicActionWorks($connector, null, $data3, self::METHOD);
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());
    }

    /**
     * Test WebHook Member Delete
     *
     * @dataProvider webHooksInputsProvider
     *
     * @param string $type
     * @param array  $data
     * @param string $objectType
     * @param string $action
     * @param string $objectId
     */
    public function testWebhookRequest(
        string $type,
        array $data,
        string $objectType,
        string $action,
        string $objectId
    ): void {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("mailchimp");
        $this->assertInstanceOf(MailChimpConnector::class, $connector);

        //====================================================================//
        // Prepare Request
        $post = array(
            "type" => $type,
            "data" => array_replace_recursive(
                array("list_id" => $connector->getParameter("ApiList")),
                $data
            ),
        );

        //====================================================================//
        // POST MODE
        $this->assertPublicActionWorks($connector, null, $post, "POST");
        $this->assertEquals(
            json_encode(array("success" => true, "type" => $type)),
            $this->getResponseContents()
        );
        $this->assertIsLastCommitted($action, $objectType, $objectId);
        //====================================================================//
        // JSON MODE
        $this->assertPublicActionWorks($connector, null, $post, self::METHOD);
        $this->assertEquals(
            json_encode(array("success" => true, "type" => $type)),
            $this->getResponseContents()
        );
        $this->assertIsLastCommitted($action, $objectType, $objectId);
    }

    /**
     * Generate Fake Inputs from WebHook Request
     *
     * @return array
     */
    public static function webHooksInputsProvider(): array
    {
        return array(
            //====================================================================//
            // Subscribe
            array(
                WebhookEventTypes::SUBSCRIBE,
                array("email" => self::FAKE_EMAIL),
                self::MEMBER,
                SplOperations::UPDATE,
                ThirdParty::hash(self::FAKE_EMAIL),
            ),

            //====================================================================//
            // Update Profile
            array(
                WebhookEventTypes::PROFILE,
                array("email" => self::FAKE_EMAIL),
                self::MEMBER,
                SplOperations::UPDATE,
                ThirdParty::hash(self::FAKE_EMAIL),
            ),

            //====================================================================//
            // Update Email
            array(
                WebhookEventTypes::UPEMAIL,
                array(
                    "old_email" => "old.".self::FAKE_EMAIL,
                    "new_email" => self::FAKE_EMAIL,
                ),
                self::MEMBER,
                SplOperations::UPDATE,
                ThirdParty::hash(self::FAKE_EMAIL),
            ),

            //====================================================================//
            // Unsubscribe & No Delete
            array(
                WebhookEventTypes::UNSUBSCRIBE,
                array("email" => self::FAKE_EMAIL),
                self::MEMBER,
                SplOperations::UPDATE,
                ThirdParty::hash(self::FAKE_EMAIL),
            ),

            //====================================================================//
            // Unsubscribe & Delete
            array(
                WebhookEventTypes::UNSUBSCRIBE,
                array("email" => self::FAKE_EMAIL, "action" => "delete"),
                self::MEMBER,
                SplOperations::DELETE,
                ThirdParty::hash(self::FAKE_EMAIL),
            ),

            //====================================================================//
            // Cleaned
            array(
                WebhookEventTypes::CLEANED,
                array("email" => self::FAKE_EMAIL),
                self::MEMBER,
                SplOperations::DELETE,
                ThirdParty::hash(self::FAKE_EMAIL),
            ),
        );
    }
}
