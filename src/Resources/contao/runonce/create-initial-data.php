<?php 

// Be silenced
@error_reporting(0);
@ini_set("display_errors", 0);

/**
 * Runonce Job
 */
class runonceJob extends \Backend
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run job
     */
    public function run()
    {
        if (!isset($GLOBALS['TL_CONFIG']['rating_type']))
        {
            $this->Config->add("\$GLOBALS['TL_CONFIG']['rating_type']", 'hearts');
        }

        if (!isset($GLOBALS['TL_CONFIG']['rating_count']))
        {
            $this->Config->add("\$GLOBALS['TL_CONFIG']['rating_count']", 5);
        }

        if (!isset($GLOBALS['TL_CONFIG']['rating_textposition']))
        {
            $this->Config->add("\$GLOBALS['TL_CONFIG']['rating_textposition']", 'after');
        }

        if (!isset($GLOBALS['TL_CONFIG']['rating_listsize']))
        {
            $this->Config->add("\$GLOBALS['TL_CONFIG']['rating_listsize']", 10);
        }

        if (!isset($GLOBALS['TL_CONFIG']['rating_template']))
        {
        	$this->Config->add("\$GLOBALS['TL_CONFIG']['rating_template']", 'rateit_default');
        }
    
        if (!isset($GLOBALS['TL_CONFIG']['rating_description']))
        {
            $this->Config->add("\$GLOBALS['TL_CONFIG']['rating_description']", '%current%/%max% %type% (%count% [Stimme|Stimmen])');
        }
    }
}

// Run once
$objRunonceJob = new runonceJob();
$objRunonceJob->run();