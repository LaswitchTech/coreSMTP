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
		$this->Public = false; // Set to false to require authentication

		// Set the controller Authorization Policy
		$this->Permission = false; // Set to true to require a permission for the namespace used.
		$this->Level = 1; // Set the permission level required

        // Initialize SMTP
        $this->SMTP = new SMTP();

		// Call the parent constructor
		parent::__construct($Auth);
	}

    /**
     * Send Action
     */
    public function sendAPIAction(){

        // Namespace: /smtp/send

        // Retrieve the request data
		$from = $this->getParams('REQUEST', 'from') ?? null;
		$to = $this->getParams('REQUEST', 'to') ?? null;
		$replyTo = $this->getParams('REQUEST', 'replyTo') ?? null;
		$cc = $this->getParams('REQUEST', 'cc') ?? null;
		$bcc = $this->getParams('REQUEST', 'bcc') ?? null;
		$subject = $this->getParams('REQUEST', 'subject') ?? null;
		$body = $this->getParams('REQUEST', 'body') ?? null;
		$attachments = $this->getParams('REQUEST', 'attachments') ?? [];
		$headers = $this->getParams('REQUEST', 'headers') ?? null;
		$template = $this->getParams('REQUEST', 'template') ?? null;

        // Check if $to, $subject and $body are set
        if($to && $subject && $body){

            // Check if the user is authenticated
            if($this->Auth && $this->Auth->Authentication && $this->Auth->Authentication->isAuthenticated()){

                // Initialize the smtp parameters
                $params = [
                    'to' => json_decode($to,true) ?? $to,
                    'subject' => $subject,
                    'body' => $body,
                ];

                // Check if $from is set
                if($from){
                    $params['from'] = $from;
                }

                // Check if $replyTo is set
                if($replyTo){
                    $params['replyTo'] = $replyTo;
                }

                // Check if $cc is set
                if($cc){
                    $params['cc'] = json_decode($cc,true) ?? $cc;
                }

                // Check if $bcc is set
                if($bcc){
                    $params['bcc'] = json_decode($bcc,true) ?? $bcc;
                }

                // Check if $attachments is set
                if($attachments){
                    $params['attachments'] = json_decode($attachments,true) ?? $attachments;
                }

                // Check if $headers is set
                if($headers){
                    $params['headers'] = json_decode($headers,true) ?? $headers;
                }

                // Check if $template is set
                if($template && file_exists($this->Configurator->root().'/Template/mail/'.$template.'.html')){

                    // Add the template
                    $this->SMTP->addTemplate($template,$this->Configurator->root().'/Template/mail/'.$template.'.html');

                    // Set the template
                    $this->SMTP->setTemplate($template);
                }

                // Attempt to send the email
                if($eml = $this->SMTP->send($params)){

                    // Send the output
                    $this->output(
                        ['success' => 'Message sent', 'email' => $eml],
                        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                    );
                } else {

                    // Send the output
                    $this->output(
                        ['error' => 'Unable to send the message'],
                        array('Content-Type: application/json', 'HTTP/1.1 404 Not Found')
                    );
                }
            } else {

                // Send the output
                $this->output(
                    ['error' => 'Authentication required'],
                    array('Content-Type: application/json', 'HTTP/1.1 401 Unauthorized')
                );
            }
        } else {

            // Send the output
            $this->output(
                ['error' => 'Missing required fields'],
                array('Content-Type: application/json', 'HTTP/1.1 404 Not Found')
            );
        }
    }
}
