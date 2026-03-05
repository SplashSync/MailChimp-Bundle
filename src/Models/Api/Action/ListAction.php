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

namespace Splash\Connectors\MailChimp\Models\Api\Action;

use Splash\OpenApi\Dictionary\ActionOptions;
use Splash\OpenApi\Interfaces\Visitor\VisitorInterface;
use Splash\OpenApi\Models\Action\AbstractListAction;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Read Objects List from MailChimp API.
 *
 * Unwraps MailChimp response format: {"members": [...], "total_items": N}
 * Key varies by endpoint: members, webhooks, lists, merge_fields, etc.
 */
class ListAction extends AbstractListAction
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        //====================================================================//
        // MailChimp uses count/offset pagination
        $resolver->setDefaults(array(
            ActionOptions::PAGE_KEY => null,
            ActionOptions::OFFSET_KEY => "offset",
            ActionOptions::MAX_KEY => "count",
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function extractData(VisitorInterface $visitor, array $rawResponse): array
    {
        //====================================================================//
        // Find the data array key (members, webhooks, lists, merge_fields, etc.)
        $items = $this->findDataKey($rawResponse);
        if (!is_array($items)) {
            return array();
        }

        return parent::extractData($visitor, $items);
    }

    /**
     * {@inheritDoc}
     */
    protected function extractTotal(array $rawResponse, ?array $params = null): int
    {
        //====================================================================//
        // Extract total from MailChimp total_items key
        if (isset($rawResponse["total_items"])) {
            return (int) $rawResponse["total_items"];
        }

        return parent::extractTotal($rawResponse, $params);
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Find the data array in MailChimp response.
     *
     * MailChimp wraps collections with a context-specific key:
     * members, webhooks, lists, merge_fields, etc.
     *
     * @return null|array<int, array<string, mixed>>
     */
    private function findDataKey(array $rawResponse): ?array
    {
        foreach ($rawResponse as $key => $value) {
            if ("total_items" === $key || "_links" === $key) {
                continue;
            }
            if (is_array($value)) {
                /** @var array<int, array<string, mixed>> $value */
                return $value;
            }
        }

        return null;
    }
}
