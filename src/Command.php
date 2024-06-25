<?php

// Declaring namespace
namespace LaswitchTech\coreSMTP;

// Import additionnal class into the global namespace
use LaswitchTech\coreBase\BaseCommand;
use LaswitchTech\coreSMTP\SMTP;

class Command extends BaseCommand {

    // Properties
    protected $SMTP;

    /**
     * Constructor
     * @param object $Auth
     */
	public function __construct($Auth){

        // Namespace: /smtp

        // Initialize SMTP
        $this->SMTP = new SMTP();

		// Call the parent constructor
		parent::__construct($Auth);
	}
}
