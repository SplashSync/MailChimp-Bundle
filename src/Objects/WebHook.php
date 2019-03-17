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

namespace Splash\Connectors\MailChimp\Objects;

use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\MailChimp\Services\MailChimpConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * MailChimp Implementation of WebHooks
 */
class WebHook extends AbstractStandaloneObject
{
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use WebHook\CRUDTrait;
    use WebHook\CoreTrait;
    use WebHook\ObjectsListTrait;

    /**
     *  Object Disable Flag. Override this flag to disable Object.
     */
    protected static $DISABLED = true;
    /**
     *  Object Name
     */
    protected static $NAME = "WebHook";
    /**
     *  Object Description
     */
    protected static $DESCRIPTION = "MailChimp WebHook";
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO = "fa fa-cogs";

    /**
     * @var MailChimpConnector
     */
    protected $connector;

    /**
     * Class Constructor
     *
     * @param MailChimpConnector $parentConnector
     */
    public function __construct(MailChimpConnector $parentConnector)
    {
        $this->connector = $parentConnector;
    }
}
