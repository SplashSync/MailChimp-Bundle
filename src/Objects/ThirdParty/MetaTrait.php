<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\MailChimp\Objects\ThirdParty;

use DateTime;

/**
 * MailChimp ThirdParty Meta Fields
 */
trait MetaTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMetaFields(): void
    {
        //====================================================================//
        // TRACEABILITY INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->Identifier("timestamp_signup")
            ->Name("Date Created")
            ->Group("Meta")
            ->MicroData("http://schema.org/DataFeedItem", "dateCreated")
            ->isReadOnly();

        //====================================================================//
        // Last Change Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->Identifier("last_changed")
            ->Name("Last modification")
            ->Group("Meta")
            ->MicroData("http://schema.org/DataFeedItem", "dateModified")
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getMetaFields($key, $fieldName): void
    {
        //====================================================================//
        // Does the Field Exists?
        if (!in_array($fieldName, array('timestamp_signup', 'last_changed'), true)) {
            return;
        }
        //====================================================================//
        // Insert in Response
        $dateField = new DateTime($this->object->{$fieldName});
        $this->out[$fieldName] = $dateField->format(SPL_T_DATETIMECAST);
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }
}
