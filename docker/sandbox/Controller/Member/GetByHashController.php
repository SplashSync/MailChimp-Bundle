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

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Handle Member operations by MD5 Hash instead of DB ID.
 * Supports GET, PUT, DELETE on /members/{subscriberHash}.
 */
#[AsController]
class GetByHashController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(string $subscriberHash, Request $request): JsonResponse
    {
        $member = $this->em->getRepository(Member::class)->findOneBy(array(
            'id' => $subscriberHash,
        ));

        //====================================================================//
        // DELETE: Remove member and return 204
        if ('DELETE' === $request->getMethod()) {
            if ($member) {
                $this->em->remove($member);
                $this->em->flush();
            }

            return new JsonResponse(null, 204);
        }

        //====================================================================//
        // GET/PUT: Member must exist
        if (!$member) {
            throw new NotFoundHttpException(sprintf('Member with hash "%s" not found.', $subscriberHash));
        }

        //====================================================================//
        // PUT: Update member fields from request body
        if ('PUT' === $request->getMethod()) {
            /** @var array $data */
            $data = json_decode($request->getContent(), true) ?: array();
            if (isset($data['email_address'])) {
                $member->email_address = (string) $data['email_address'];
            }
            if (isset($data['status'])) {
                $member->status = (string) $data['status'];
            }
            if (isset($data['vip'])) {
                $member->vip = (bool) $data['vip'];
            }
            if (isset($data['merge_fields']) && is_array($data['merge_fields'])) {
                $member->merge_fields = array_merge($member->merge_fields, $data['merge_fields']);
            }
            $this->em->flush();
        }

        return new JsonResponse(
            $this->serializer->serialize($member, 'json'),
            200,
            array(),
            true
        );
    }
}
