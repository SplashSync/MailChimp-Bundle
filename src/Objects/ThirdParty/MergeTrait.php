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

namespace Splash\Connectors\MailChimp\Objects\ThirdParty;

use Splash\Connectors\MailChimp\DataTransformers\MergeFieldTransformer;
use stdClass;

/**
 * MailChimp ThirdParty Merge Fields (Dynamic)
 */
trait MergeTrait
{
    /**
     * Build Merge Fields using FieldFactory
     */
    protected function buildMergeFieldsFields(): void
    {
        $this->connector
            ->getLocator()
            ->getMergeFieldsManager()
            ->buildMergeFieldsFields($this->fieldsFactory())
        ;
    }

    /**
     * Read Requested Merge Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getMergeFieldsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Field is not a Merge Field
        $mergeField = $this->isMergeField($fieldName);
        if (!$mergeField) {
            return;
        }
        //====================================================================//
        // Read Merge Field Value (inline on member)
        $mergeFields = $this->object->merge_fields ?? array();
        $rawValue = isset($mergeFields[$fieldName]) ? trim((string) $mergeFields[$fieldName]) : null;
        $this->out[$fieldName] = MergeFieldTransformer::toSplash($mergeField, $rawValue);
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }

    /**
     * Write Given Merge Field
     *
     * @param string                     $fieldName Field Identifier / Name
     * @param null|bool|float|int|string $fieldData Field Data
     */
    protected function setMergeFieldsFields(string $fieldName, null|bool|string|float|int $fieldData): void
    {
        //====================================================================//
        // Field is not a Merge Field
        $mergeField = $this->isMergeField($fieldName);
        if (!$mergeField) {
            return;
        }
        //====================================================================//
        // Ensure merge_fields array exists
        if (null === $this->object->merge_fields) {
            $this->object->merge_fields = array();
        }
        //====================================================================//
        // Transform Value to MailChimp Format
        $origin = $this->object->merge_fields[$fieldName] ?? null;
        $newValue = MergeFieldTransformer::toMailChimp($mergeField, $fieldData);
        if ($origin !== $newValue) {
            $this->object->merge_fields[$fieldName] = $newValue;
            $this->needUpdate();
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Check if this Field is a Merge Field
     */
    private function isMergeField(string $fieldName): ?stdClass
    {
        return $this->connector
            ->getLocator()
            ->getMergeFieldsManager()
            ->findByFieldName($fieldName)
        ;
    }
}
