<?php

// Declaring namespace
namespace LaswitchTech\coreSMTP;

// Import additionnal class into the global namespace
use LaswitchTech\coreBase\BaseController;
use LaswitchTech\coreSMTP\SMTP;

class Controller extends BaseController {

    // Properties
    protected $SMTP;

    /**
     * Constructor
     * @param object $Auth
     */
	public function __construct($Auth){

        // Namespace: /smtp

		// Set the controller Authentication Policy
		$this->Public = true; // Set to false to require authentication

		// Set the controller Authorization Policy
		$this->Permission = false; // Set to true to require a permission for the namespace used.
		$this->Level = 1; // Set the permission level required

        // Initialize SMTP
        $this->SMTP = new SMTP();

		// Call the parent constructor
		parent::__construct($Auth);
	}
}
