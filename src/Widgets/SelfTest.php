<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\MailChimp\Widgets;

use Splash\Bundle\Models\AbstractStandaloneWidget;
use Splash\Connectors\MailChimp\Services\MailChimpConnector;
use Splash\Core\SplashCore      as Splash;

/**
 * MailChimp Config SelfTest
 */
class SelfTest extends AbstractStandaloneWidget
{
    //====================================================================//
    // Define Standard Options for this Widget
    // Override this array to change default options for your widget
    public static $OPTIONS       = array(
        "Width"     =>      self::SIZE_DEFAULT,
        'UseCache'      =>  true,
        'CacheLifeTime' =>  1,
    );
    
    /**
     * Widget Name
     */
    protected static $NAME            =  "Server SelfTest";
    
    /**
     * Widget Description
     */
    protected static $DESCRIPTION     =  "Results of your Server SelfTests";
    
    /**
     * Widget Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-info-circle";

    /**
     * @var MailChimpConnector
     */
    protected $connector;
    
    public function __construct(MailChimpConnector $connector = null)
    {
        $this->connector  =   $connector;
    }
    
    /**
     * Return requested Customer Data
     *
     * @param array $params Widget Inputs Parameters
     *
     * @return array
     */
    public function get($params=null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        
        //====================================================================//
        // Setup Widget Core Informations
        //====================================================================//

        $this->setTitle($this->getName());
        $this->setIcon($this->getIcon());
        
        //====================================================================//
        // Build Intro Text Block
        //====================================================================//
        $this->buildIntroBlock();
        
        //====================================================================//
        // Build SlefTest Results Block
        //====================================================================//
        $this->connector->selfTest();
        $this->buildNotificationsBlock();

        //====================================================================//
        // Set Blocks to Widget
        $this->setBlocks($this->blocksFactory()->render());

        //====================================================================//
        // Publish Widget
        return $this->render();
    }

    /**
     * Block Building - Text Intro
     */
    private function buildIntroBlock()
    {
        //====================================================================//
        // Into Text Block
        $this->blocksFactory()->addTextBlock("This widget summarize SelfTest of your MailChimp Account Config");
    }
    
    /**
     * Block Building - Notifications Parameters
     */
    private function buildNotificationsBlock()
    {
        //====================================================================//
        // Get Log
        $Log = Splash::log();
        //====================================================================//
        // If test was passed
        if (empty($Log->err)) {
            $this->blocksFactory()->addNotificationsBlock(array("success" => "Self-Test Passed!"));
        }
        //====================================================================//
        // Add Error Notifications
        foreach ($Log->err as $Text) {
            $this->blocksFactory()->addNotificationsBlock(array("error" => $Text));
        }
        //====================================================================//
        // Add Warning Notifications
        foreach ($Log->war as $Text) {
            $this->blocksFactory()->addNotificationsBlock(array("warning" => $Text));
        }
        //====================================================================//
        // Add Success Notifications
        foreach ($Log->msg as $Text) {
            $this->blocksFactory()->addNotificationsBlock(array("success" => $Text));
        }
        //====================================================================//
        // Add Debug Notifications
        foreach ($Log->deb as $Text) {
            $this->blocksFactory()->addNotificationsBlock(array("info" => $Text));
        }
    }
}
