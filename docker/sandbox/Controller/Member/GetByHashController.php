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
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Lookup Member by MD5 Hash instead of DB ID
 */
#[AsController]
class GetByHashController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(string $subscriberHash): JsonResponse
    {
        $member = $this->em->getRepository(Member::class)->findOneBy(array(
            'id' => $subscriberHash,
        ));

        if (!$member) {
            throw new NotFoundHttpException(sprintf('Member with hash "%s" not found.', $subscriberHash));
        }

        return new JsonResponse(
            $this->serializer->serialize($member, 'json'),
            200,
            array(),
            true
        );
    }
}
