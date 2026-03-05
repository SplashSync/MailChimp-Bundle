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

namespace Splash\Connectors\MailChimp\Helpers;

use Splash\Core\Dictionary\SplFields;
use Splash\Templates\ThirdPartyFields;
use stdClass;

/**
 * MailChimp Merge Fields Type Helper
 */
class MergeFieldsHelper
{
    /**
     * MailChimp Merge Field Type => Splash Type Mapping
     *
     * @var array<string, string>
     */
    private static array $typeMap = array(
        "text" => SplFields::VARCHAR,
        "number" => SplFields::INT,
        "phone" => SplFields::PHONE,
        "date" => SplFields::DATE,
        "birthday" => SplFields::DATE,
        "url" => SplFields::URL,
        "imageurl" => SplFields::URL,
        "zip" => SplFields::VARCHAR,
        "radio" => SplFields::VARCHAR,
        "dropdown" => SplFields::VARCHAR,
    );

    /**
     * Get Splash Field Type from MailChimp Merge Field
     */
    public static function toSplashType(stdClass $mergeField): string
    {
        $type = $mergeField->type ?? "text";
        if (isset(self::$typeMap[$type])) {
            return self::$typeMap[$type];
        }

        return SplFields::VARCHAR;
    }

    /**
     * MailChimp Merge Field Types that are Write-Only (lossy conversion)
     *
     * @var string[]
     */
    private static array $writeOnlyTypes = array(
        "birthday",
    );

    /**
     * Check if this Merge Field Type is Write-Only
     */
    public static function isWriteOnly(stdClass $mergeField): bool
    {
        return in_array($mergeField->type ?? "", self::$writeOnlyTypes, true);
    }

    /**
     * Get Splash Field Template for Known MailChimp Merge Fields
     *
     * @return null|class-string
     */
    public static function getTemplate(stdClass $mergeField): ?string
    {
        return match ($mergeField->tag ?? "") {
            "FNAME" => ThirdPartyFields::FIRSTNAME,
            "LNAME" => ThirdPartyFields::LASTNAME,
            default => null,
        };
    }
}
