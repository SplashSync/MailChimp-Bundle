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

namespace App\Serializer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\MailingList;
use App\Entity\Member;
use App\Entity\MergeField;
use App\Entity\WebHook;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Wraps API Platform collection responses in MailChimp API format.
 *
 * MailChimp collections: {"members": [...], "total_items": N}
 * MailChimp single items: returned as-is (flat JSON, NO wrapping)
 *
 * Key varies by entity type: members, lists, merge_fields, webhooks
 */
final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /**
     * Entity class => collection key mapping
     *
     * @var array<class-string, string>
     */
    private const COLLECTION_KEYS = array(
        Member::class => "members",
        MailingList::class => "lists",
        MergeField::class => "merge_fields",
        WebHook::class => "webhooks",
    );

    /**
     * @var DenormalizerInterface|NormalizerInterface
     */
    private DenormalizerInterface|NormalizerInterface $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(
                sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class)
            );
        }

        $this->decorated = $decorated;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = array()): bool
    {
        //====================================================================//
        // Handle Paginator collections only
        if ($data instanceof PaginatorInterface) {
            return true;
        }

        //====================================================================//
        // Delegate to decorated for single items (no wrapping needed)
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = array()
    ): array|string|int|float|bool|\ArrayObject|null {
        //====================================================================//
        // Handle paginated collections
        if ($data instanceof PaginatorInterface) {
            $items = array();
            $collectionKey = "items";
            foreach ($data as $item) {
                $items[] = $this->decorated->normalize($item, $format, $context);
                //====================================================================//
                // Detect collection key from first item
                if (1 === count($items)) {
                    $collectionKey = self::COLLECTION_KEYS[$item::class] ?? "items";
                }
            }

            return array(
                $collectionKey => $items,
                'total_items' => (int) $data->getTotalItems(),
            );
        }

        //====================================================================//
        // Single items: return as-is (flat JSON, no wrapping)
        return $this->decorated->normalize($data, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = array()
    ): bool {
        return $this->decorated->supportsDenormalization($data, $type, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize(
        mixed $data,
        string $class,
        ?string $format = null,
        array $context = array()
    ): mixed {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        return array(
            PaginatorInterface::class => true,
            '*' => false,
        );
    }
}
