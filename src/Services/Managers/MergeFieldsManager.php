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

namespace Splash\Connectors\MailChimp\Services\Managers;

use Splash\Connectors\MailChimp\Helpers\MergeFieldsHelper;
use Splash\Connectors\MailChimp\Models\MailChimpConnectorAwareTrait;
use Splash\Core\Components\FieldsFactory;
use stdClass;

/**
 * Manage MailChimp Merge Fields (Dynamic Contact Properties)
 */
class MergeFieldsManager
{
    use MailChimpConnectorAwareTrait;

    /**
     * Merge Fields Details Storage Key
     */
    const string MERGE_FIELDS_DETAILS = "ApiMergeFieldsDetails";

    /**
     * Base Attributes Metadata Item Type
     */
    const string ITEM_TYPE = "http://meta.schema.org/additionalType";

    /**
     * Fetch Merge Fields Definitions from MailChimp API
     */
    public function fetchMergeFields(): bool
    {
        //====================================================================//
        // Get Merge Fields from Api (root connexion, list-scoped URL)
        $config = $this->getConnector()->getConfiguration();
        $listId = $config["ApiList"] ?? "";
        if (empty($listId)) {
            return false;
        }
        $response = $this->getConnexion()->get(
            sprintf('/lists/%s/merge-fields', $listId)
        );
        if (is_null($response) || empty($response["merge_fields"]) || !is_array($response["merge_fields"])) {
            return false;
        }
        //====================================================================//
        // Store in Connector Settings
        $this->getConnector()->setParameter(
            self::MERGE_FIELDS_DETAILS,
            json_decode((string) json_encode($response["merge_fields"]))
        );
        //====================================================================//
        // Update Connector Settings
        $this->getConnector()->updateConfiguration();

        return true;
    }

    /**
     * Build Fields using FieldFactory
     */
    public function buildMergeFieldsFields(FieldsFactory $factory): void
    {
        foreach ($this->getMergeFieldsDetails() as $attr) {
            $this->buildMergeField($factory, $attr);
        }
    }

    /**
     * Find a Merge Field by its Tag (Field Name)
     */
    public function findByFieldName(string $fieldName): ?stdClass
    {
        foreach ($this->getMergeFieldsDetails() as $attr) {
            if (($attr->tag ?? "") === $fieldName) {
                return $attr;
            }
        }

        return null;
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Get Merge Fields Details as stdClass array
     *
     * @return stdClass[]
     */
    private function getMergeFieldsDetails(): array
    {
        $raw = $this->getConnector()->getParameter(self::MERGE_FIELDS_DETAILS);
        if (empty($raw) || !is_array($raw)) {
            return array();
        }

        //====================================================================//
        // Ensure stdClass format (serialization may convert to arrays)
        return array_filter(array_map(
            static fn ($item) => $item instanceof stdClass
                ? $item
                : (is_array($item) ? (object) $item : null),
            $raw
        ));
    }

    /**
     * Build a single Merge Field using FieldFactory
     */
    private function buildMergeField(FieldsFactory $factory, stdClass $attr): void
    {
        $attrCode = $attr->tag ?? "";
        //====================================================================//
        // Check for Known Field Template
        if ($template = MergeFieldsHelper::getTemplate($attr)) {
            $factory->createFromTemplate($attrCode, $template);
        } else {
            //====================================================================//
            // Add Attribute to Fields
            $factory
                ->create(MergeFieldsHelper::toSplashType($attr))
                ->identifier($attrCode)
                ->name($attr->name ?? $attrCode)
                ->microData(self::ITEM_TYPE, strtolower($attrCode))
            ;
        }
        //====================================================================//
        // Configure Field
        if (MergeFieldsHelper::isWriteOnly($attr)) {
            $factory->isWriteOnly();
        }
        $factory->group("Attributes");
    }
}
