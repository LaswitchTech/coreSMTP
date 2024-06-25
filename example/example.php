<?php

// Import additionnal class into the global namespace
use LaswitchTech\coreSMTP\SMTP;

//Load Composer's autoloader
require 'vendor/autoload.php';

//Initiate Auth
$SMTP = new SMTP();

$SMTP->addTemplate('text','example/templates/default.txt');
$SMTP->addTemplate('html','example/templates/default.html');

$SMTP->setTemplate('text');
$SMTP->send([
  'to' => "louis@laswitchtech.com",
  'subject' => "Lorem Ipsum",
  'body' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
]);

$SMTP->setTemplate('html');
$SMTP->send([
  'to' => "louis@laswitchtech.com",
  'subject' => "Lorem Ipsum",
  'body' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
]);
