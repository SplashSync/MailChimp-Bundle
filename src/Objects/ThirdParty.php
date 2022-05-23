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

namespace Splash\Connectors\MailChimp\Objects;

use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\MailChimp\Services\MailChimpConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use stdClass;

/**
 * MailChimp Implementation of ThirdParty
 */
class ThirdParty extends AbstractStandaloneObject
{
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ThirdParty\CRUDTrait;
    use ThirdParty\ObjectsListTrait;
    use ThirdParty\CoreTrait;
    use ThirdParty\MergeTrait;
    use ThirdParty\MetaTrait;

    /**
     * {@inheritdoc}
     */
    protected static bool $disabled = false;

    /**
     * {@inheritdoc}
     */
    protected static string $name = "Customer";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "MailChimp Subscriber";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-user";

    /**
     * @phpstan-var stdClass
     */
    protected object $object;

    /**
     * @var MailChimpConnector
     */
    protected MailChimpConnector $connector;

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
