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

namespace Splash\Connectors\MailChimp\Objects\ThirdParty;

use Splash\Core\SplashCore      as Splash;

/**
 * MailChimp ThirdParty Merge Fields (Required)
 */
trait MergeTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildMergeFields()
    {
        //====================================================================//
        // Firstname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("FNAME")
            ->Name("Firstname")
            ->isLogged()
            ->MicroData("http://schema.org/Person", "familyName")
            ->isListed()
            ->Association("FNAME", "LNAME");
        
        //====================================================================//
        // Lastname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("LNAME")
            ->Name("Lastname")
            ->isLogged()
            ->MicroData("http://schema.org/Person", "givenName")
            ->isListed()
            ->Association("FNAME", "LNAME");
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getMergeFields($key, $fieldName)
    {
        //====================================================================//
        // Does the Field Exists?
        if (!isset($this->object->merge_fields->{$fieldName})) {
            return;
        }
        //====================================================================//
        // Insert in Response
        $this->out[$fieldName] = trim($this->object->merge_fields->{$fieldName});
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }
    
    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setMergeFields($fieldName, $fieldData)
    {
        //====================================================================//
        // Does the Field Exists?
        if (!in_array($fieldName, array('FNAME', 'LNAME'), true)) {
            return;
        }

        if (!empty($fieldData)) {
            if (!isset($this->object->merge_fields)) {
                $this->object->merge_fields = array();
            }
            if (!isset($this->object->merge_fields->{$fieldName}) || ($this->object->merge_fields->{$fieldName} !== $fieldData)) {
                $this->object->merge_fields->{$fieldName} = $fieldData;
                $this->needUpdate();
            }
        }

        unset($this->in[$fieldName]);
    }
}
