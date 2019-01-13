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

/**
 * MailChimp ThirdParty Core Fields (Required)
 */
trait CoreTrait
{
    /**
     * @var bool
     */
    protected $objectIdChanged = false;
    
    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildCoreFields()
    {
        //====================================================================//
        // Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->Identifier("email_address")
            ->Name("Email")
            ->MicroData("http://schema.org/ContactPoint", "email")
            ->isRequired()
            ->isListed();
        
        //====================================================================//
        // Subscribed
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("status")
            ->Name("Is Subscribed")
            ->MicroData("http://schema.org/Organization", "newsletter")
            ->isNotTested()
            ->isListed();
        
        //====================================================================//
        // VIP
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("vip")
            ->Name("Is VIP")
            ->MicroData("http://schema.org/Organization", "vip");
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getCoreFields($key, $fieldName)
    {
        switch ($fieldName) {
            case 'email_address':
                $this->getSimple($fieldName);

                break;
            case 'status':
                $this->out[$fieldName] = ('subscribed' === $this->object->{$fieldName});

                break;
            case 'vip':
                $this->getSimpleBool($fieldName);

                break;
            default:
                return;
        }
        
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
    protected function setCoreFields($fieldName, $fieldData)
    {
        switch ($fieldName) {
            case 'email_address':
                if ($this->object->{$fieldName} != $fieldData) {
                    //====================================================================//
                    //  Update Field Data
                    $this->object->{$fieldName} = $fieldData;
                    $this->needUpdate();
                    //====================================================================//
                    //  Mark for Update Object Id In DataBase
                    $this->objectIdChanged  =   true;
                }

                break;
            case 'status':
                if (('subscribed' == $this->object->{$fieldName}) && empty($fieldData)) {
                    $this->object->{$fieldName} = 'unsubscribed';
                    $this->needUpdate();
                } elseif (('subscribed' != $this->object->{$fieldName}) && !empty($fieldData)) {
                    $this->object->{$fieldName} = 'subscribed';
                    $this->needUpdate();
                }

                break;
            case 'vip':
                $this->setSimple($fieldName, (bool) $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
