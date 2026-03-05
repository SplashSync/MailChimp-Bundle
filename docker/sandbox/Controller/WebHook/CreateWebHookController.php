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

namespace App\Controller\WebHook;

use App\Entity\MailingList;
use App\Entity\WebHook;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Create a new WebHook in a Mailing List.
 */
#[AsController]
class CreateWebHookController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(int $listId, Request $request): JsonResponse
    {
        //====================================================================//
        // Resolve Mailing List
        $list = $this->em->getRepository(MailingList::class)->find($listId);
        if (!$list) {
            return new JsonResponse(array('detail' => 'List not found'), 404);
        }
        //====================================================================//
        // Decode Request Body
        /** @var array $data */
        $data = json_decode($request->getContent(), true) ?: array();
        //====================================================================//
        // Create WebHook
        $webhook = new WebHook();
        $webhook->url = (string) ($data['url'] ?? '');
        $webhook->events = is_array($data['events'] ?? null) ? $data['events'] : array();
        $webhook->sources = is_array($data['sources'] ?? null) ? $data['sources'] : array();
        $webhook->list = $list;
        //====================================================================//
        // Persist
        $this->em->persist($webhook);
        $this->em->flush();

        return new JsonResponse(
            $this->serializer->serialize($webhook, 'json'),
            200,
            array(),
            true
        );
    }
}
