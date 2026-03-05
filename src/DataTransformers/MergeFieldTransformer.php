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

namespace Splash\Connectors\MailChimp\DataTransformers;

use stdClass;

/**
 * Transform MailChimp Merge Field Values between Splash and MailChimp Formats
 */
class MergeFieldTransformer
{
    /**
     * Convert MailChimp Merge Field Value to Splash Value
     *
     * @param null|float|int|string $rawValue Raw value from MailChimp API
     */
    public static function toSplash(stdClass $mergeField, null|string|float|int $rawValue): null|string
    {
        if (null === $rawValue || "" === $rawValue) {
            return null;
        }

        return match ($mergeField->type ?? "text") {
            "birthday" => self::birthdayToSplash((string) $rawValue),
            default => (string) $rawValue,
        };
    }

    /**
     * Convert Splash Value to MailChimp Merge Field Value
     *
     * @param null|bool|float|int|string $value Splash field value
     */
    public static function toMailChimp(stdClass $mergeField, null|bool|string|float|int $value): string
    {
        if (null === $value || "" === $value) {
            return "";
        }

        return match ($mergeField->type ?? "text") {
            "birthday" => self::birthdayToMailChimp((string) $value),
            default => (string) $value,
        };
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Convert MailChimp Birthday (MM/DD) to Splash Date (Y-m-d)
     */
    private static function birthdayToSplash(string $rawValue): ?string
    {
        $date = \DateTime::createFromFormat("m/d", $rawValue);
        if (!$date) {
            return null;
        }
        //====================================================================//
        // Use fixed year (birthday has no year in MailChimp)
        $date->setDate(2000, (int) $date->format("m"), (int) $date->format("d"));

        return $date->format("Y-m-d");
    }

    /**
     * Convert Splash Date (Y-m-d) to MailChimp Birthday (MM/DD)
     */
    private static function birthdayToMailChimp(string $value): string
    {
        $date = \DateTime::createFromFormat("Y-m-d", $value);
        if (!$date) {
            $date = \DateTime::createFromFormat("Y-m-d H:i:s", $value);
        }

        return $date ? $date->format("m/d") : "";
    }
}