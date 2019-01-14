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

use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Local\ActionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Splash MailChimp Connector Actions Controller
 */
class ActionsController extends Controller
{
    use ActionsTrait;
    
    /**
     * Update User Connector WebHooks List
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function webhooksAction(Request $request, AbstractConnector $connector)
    {
        $Result = false;
        //====================================================================//
        // Connector SelfTest
        if ($connector->selfTest()) {
            //====================================================================//
            // Update WebHooks Config
            $Result =   $connector->updateWebHooks($this->get('router'));
        }
        //====================================================================//
        // Inform User
        if ($Result) {
            $this->addFlash(
                    "success",
                    $this->get('translator')->trans("admin.webhooks.msg", array(), "MailChimpBundle")
                );
        } else {
            $this->addFlash(
                    "danger",
                    $this->get('translator')->trans("admin.webhooks.err", array(), "MailChimpBundle")
                );
        }
        //====================================================================//
        // Redirect Response
        /** @var string $referer */
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return self::getDefaultResponse();
        }

        return new RedirectResponse($referer);
    }
}
