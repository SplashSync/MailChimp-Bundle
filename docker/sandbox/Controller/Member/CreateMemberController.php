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

namespace App\Controller\Member;

use App\Entity\MailingList;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Create a new Member in a Mailing List.
 */
#[AsController]
class CreateMemberController
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
        // Create Member
        $member = new Member();
        $member->email_address = (string) ($data['email_address'] ?? '');
        $member->status = (string) ($data['status'] ?? 'subscribed');
        $member->vip = (bool) ($data['vip'] ?? false);
        $member->merge_fields = is_array($data['merge_fields'] ?? null) ? $data['merge_fields'] : array();
        $member->list = $list;
        //====================================================================//
        // Persist
        $this->em->persist($member);
        $this->em->flush();

        return new JsonResponse(
            $this->serializer->serialize($member, 'json'),
            200,
            array(),
            true
        );
    }
}
