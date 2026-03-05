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

use Splash\Connectors\MailChimp\Connectors\MailChimpConnector;
use Splash\Connectors\MailChimp\Models\Api\Member as MemberModel;
use Splash\Core\Client\Splash;
use Splash\Core\Interfaces\Object\PrimaryKeysAwareInterface;
use Splash\OpenApi\Models\Objects\AbstractRestAndMetadataObject;

/**
 * MailChimp Implementation of ThirdParty
 */
class ThirdParty extends AbstractRestAndMetadataObject implements PrimaryKeysAwareInterface
{
    use ThirdParty\CRUDTrait;
    use ThirdParty\MergeTrait;

    /**
     * @var MemberModel
     */
    protected object $object;

    /**
     * @var MailChimpConnector
     */
    protected MailChimpConnector $connector;

    /**
     * Class Constructor
     */
    public function __construct(MailChimpConnector $connector)
    {
        parent::__construct(
            $visitor = $connector->getVisitor(MemberModel::class),
            $visitor->getMetadataAdapter(),
            MemberModel::class
        );
        $this->connector = $connector;
        //====================================================================//
        //  Load Translation File
        Splash::translator()->load('local');
    }

    /**
     * Get MailChimp Subscriber Hash
     */
    public static function hash(string $email): string
    {
        return md5(strtolower($email));
    }
}
