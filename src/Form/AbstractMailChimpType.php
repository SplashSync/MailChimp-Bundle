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

namespace Splash\Connectors\MailChimp\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
//use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base Form Type for MailChimp Connectors Servers
 */
abstract class AbstractMailChimpType extends AbstractType
{
    /**
     * Add Api Key Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addApiKeyField(FormBuilderInterface $builder, array $options): self
    {
        $builder
            //==============================================================================
            // MailChimp Api Key Option Authentification
            ->add('ApiKey', TextType::class, array(
                'label' => "var.apikey.label",
                'help' => "var.apikey.desc",
                'required' => true,
                'translation_domain' => "MailChimpBundle",
            ))
        ;

        return $this;
    }

    /**
     * @abstract    Add List Selector Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     */
    public function addApiListField(FormBuilderInterface $builder, array $options): self
    {
        //==============================================================================
        // Check MailChimp Lists are Available
        if (empty($options["data"]["ApiListsIndex"])) {
            return $this;
        }

        $builder
            //==============================================================================
            // MailChimp List Option Selector
            ->add('ApiList', ChoiceType::class, array(
                'label' => "var.list.label",
                'help' => "var.list.desc",
                'required' => true,
                'translation_domain' => "MailChimpBundle",
                'choice_translation_domain' => false,
                'choices' => array_flip($options["data"]["ApiListsIndex"]),
            ))
        ;

        return $this;
    }
}
