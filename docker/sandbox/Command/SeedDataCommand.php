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

namespace App\Command;

use App\Entity\MailingList;
use App\Entity\Member;
use App\Entity\MergeField;
use App\Entity\WebHook;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Seeds the sandbox database with default data for MailChimp API testing.
 */
#[AsCommand(name: 'app:seed-data', description: 'Seed sandbox with default MailChimp data')]
class SeedDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>Starting MailChimp sandbox data seeding...</comment>');
        $output->writeln('');

        //====================================================================//
        // Seed lists first (other entities depend on them)
        $this->seedMailingLists($output);
        $this->em->flush();

        //====================================================================//
        // Get list 1 reference for relations
        $list = $this->em->getRepository(MailingList::class)->find(1);
        if (!$list) {
            $output->writeln('<error>MailingList #1 not found, aborting.</error>');

            return Command::FAILURE;
        }

        $this->seedMergeFields($output, $list);
        $this->seedMembers($output, $list);
        $this->seedWebHooks($output, $list);

        $this->em->flush();
        $output->writeln('');
        $output->writeln('<info>Seed data loaded successfully.</info>');

        return Command::SUCCESS;
    }

    /**
     * Seed mailing lists.
     */
    private function seedMailingLists(OutputInterface $output): void
    {
        if ($this->em->getRepository(MailingList::class)->count(array()) > 0) {
            $output->writeln('MailingList already exists, skipping.');

            return;
        }

        $lists = array(
            array(
                'name' => 'Newsletter',
                'contact' => array(
                    'company' => 'Splash Test List',
                    'address1' => '28 AV DES COLOMBES',
                    'city' => 'MERIGNAC',
                    'zip' => '33700',
                    'country' => 'FR',
                    'phone' => '0606060606',
                ),
                'campaign_defaults' => array(
                    'from_name' => 'Splash Tester',
                    'from_email' => 'test@splashsync.com',
                    'subject' => '',
                    'language' => 'en',
                ),
            ),
            array(
                'name' => 'Clients',
                'contact' => array(
                    'company' => 'Splash Clients',
                    'address1' => '10 RUE DU TEST',
                    'city' => 'PARIS',
                    'zip' => '75001',
                    'country' => 'FR',
                    'phone' => '0101010101',
                ),
                'campaign_defaults' => array(
                    'from_name' => 'Splash Clients',
                    'from_email' => 'clients@splashsync.com',
                    'subject' => '',
                    'language' => 'fr',
                ),
            ),
        );

        foreach ($lists as $data) {
            $list = new MailingList();
            $list->name = $data['name'];
            $list->contact = $data['contact'];
            $list->campaign_defaults = $data['campaign_defaults'];
            $this->em->persist($list);
        }

        $output->writeln(sprintf('%d MailingLists seeded.', count($lists)));
    }

    /**
     * Seed merge fields for list 1.
     */
    private function seedMergeFields(OutputInterface $output, MailingList $list): void
    {
        if ($this->em->getRepository(MergeField::class)->count(array()) > 0) {
            $output->writeln('MergeField already exists, skipping.');

            return;
        }

        $fields = array(
            array('tag' => 'FNAME', 'name' => 'First Name', 'type' => 'text'),
            array('tag' => 'LNAME', 'name' => 'Last Name', 'type' => 'text'),
            array('tag' => 'PHONE', 'name' => 'Phone Number', 'type' => 'phone'),
            array('tag' => 'BIRTHDAY', 'name' => 'Birthday', 'type' => 'birthday'),
        );

        foreach ($fields as $data) {
            $field = new MergeField();
            $field->tag = $data['tag'];
            $field->name = $data['name'];
            $field->type = $data['type'];
            $field->list = $list;
            $this->em->persist($field);
        }

        $output->writeln(sprintf('%d MergeFields seeded.', count($fields)));
    }

    /**
     * Seed sample members.
     */
    private function seedMembers(OutputInterface $output, MailingList $list): void
    {
        if ($this->em->getRepository(Member::class)->count(array()) > 0) {
            $output->writeln('Members already exist, skipping.');

            return;
        }

        $members = array(
            array(
                'email' => 'test1@example.com',
                'status' => 'subscribed',
                'vip' => true,
                'merge_fields' => array('FNAME' => 'John', 'LNAME' => 'Doe'),
            ),
            array(
                'email' => 'test2@example.com',
                'status' => 'subscribed',
                'vip' => false,
                'merge_fields' => array('FNAME' => 'Jane', 'LNAME' => 'Smith'),
            ),
        );

        foreach ($members as $data) {
            $member = new Member();
            $member->email_address = $data['email'];
            $member->status = $data['status'];
            $member->vip = $data['vip'];
            $member->merge_fields = $data['merge_fields'];
            $member->list = $list;
            $this->em->persist($member);
        }

        $output->writeln(sprintf('%d Members seeded.', count($members)));
    }

    /**
     * Seed a sample webhook.
     */
    private function seedWebHooks(OutputInterface $output, MailingList $list): void
    {
        if ($this->em->getRepository(WebHook::class)->count(array()) > 0) {
            $output->writeln('WebHook already exists, skipping.');

            return;
        }

        $webhook = new WebHook();
        $webhook->url = 'https://example.com/webhook';
        $webhook->list = $list;
        $webhook->events = array(
            'subscribe' => true,
            'unsubscribe' => true,
            'profile' => true,
            'cleaned' => true,
            'campaign' => false,
        );
        $webhook->sources = array(
            'user' => true,
            'admin' => true,
            'api' => false,
        );
        $this->em->persist($webhook);

        $output->writeln('WebHook seeded.');
    }
}
