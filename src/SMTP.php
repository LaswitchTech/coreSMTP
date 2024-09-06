<?php

// Declaring namespace
namespace LaswitchTech\coreSMTP;

// Import additionnal class into the global namespace
use LaswitchTech\coreConfigurator\Configurator;
use LaswitchTech\coreLogger\Logger;
use LaswitchTech\coreNet\Net;
use DOMDocument;
use Exception;

class SMTP{

	// SMTP Validation Codes
	const SMTP_OK = '250';
	const SMTP_DATA_OK = '354';
	const SMTP_AUTH_OK = '334';
	const SMTP_USERNAME_OK = '334';
	const SMTP_PASSWORD_OK = '235';

	// Default eml destination
	const SMTP_DATA_DIRECTORY = 'data';

	// Logger
	private $Logger;

	// Configurator
	private $Configurator = null;

	// Net
	private $Net;

	// Saved Connection
	private $Connection = null;

	// Saved Connection Information
	private $Host = null;
	private $Port = null;
	private $Encryption = null;
	private $Username = null;
	private $Password = null;
	private $CTEncoding = 'base64'; // quoted-printable or base64
	private $SBJIllegalCharacters = false;

	// Templates and Settings
	private $Templates = [];
	private $Template = null;

    /**
     * Create a new SMTP instance.
     *
     * @param  boolean|null  $debug
     * @return void
     */
    public function __construct(){

        // Initialize Configurator
        $this->Configurator = new Configurator('smtp');

        // Initiate Logger
        $this->Logger = new Logger('smtp');

        // Initiate Net
        $this->Net = new Net();

        // Retrieve SMTP Settings
        $this->Username = $this->Configurator->get('smtp', 'username') ?: $this->Username;
        $this->Password = $this->Configurator->get('smtp', 'password') ?: $this->Password;
        $this->Host = $this->Configurator->get('smtp', 'host') ?: $this->Host;
        $this->Port = $this->Configurator->get('smtp', 'port') ?: $this->Port;
        $this->Encryption = $this->Configurator->get('smtp', 'encryption') ?: $this->Encryption;
        $this->CTEncoding = $this->Configurator->get('smtp', 'ctencoding') ?: $this->CTEncoding;
        $this->SBJIllegalCharacters = $this->Configurator->get('smtp', 'sbjillegalcharacters') ?: $this->SBJIllegalCharacters;

        // Attempt a connection
        if($this->Host !== null && $this->Username !== null && $this->Password !== null && $this->Port !== null && $this->Encryption !== null){
            $this->connect($this->Username,$this->Password,$this->Host,$this->Port,$this->Encryption);
        }

        // Add default template
        if(is_file($this->Configurator->root() . '/Mail/default.html')){
            if($this->addTemplate('default',$this->Configurator->root() . '/Mail/default.html')){
                $this->setTemplate('default');
            }
        } else {
            if(is_file($this->Configurator->root() . '/Mail/default.txt')){
                if($this->addTemplate('default',$this->Configurator->root() . '/Mail/default.txt')){
                    $this->setTemplate('default');
                }
            }
        }
    }

    /**
     * This method closes the SMTP connection when the object is destroyed.
     *
     * @return void
     */
    public function __destruct(){
        $this->close();
    }

    /**
     * Configure Library.
     *
     * @param  string  $option
     * @param  bool|int  $value
     * @return void
     * @throws Exception
     */
    public function config($option, $value){
        try {
            if(is_string($option)){
                switch($option){
                    case"host":
                        if(is_string($value)){

                            // Set Host
                            $this->Host = $value;

                            // Save to Configurator
                            $this->Configurator->set('smtp',$option, $value);
                        } else{
                            throw new Exception("2nd argument must be a string.");
                        }
                        break;
                    case"username":
                        if(is_string($value)){

                            // Set Username
                            $this->Username = $value;

                            // Save to Configurator
                            $this->Configurator->set('smtp',$option, $value);
                        } else{
                            throw new Exception("2nd argument must be a string.");
                        }
                        break;
                    case"password":
                        if(is_string($value)){

                            // Set Password
                            $this->Password = $value;

                            // Save to Configurator
                            $this->Configurator->set('smtp',$option, $value);
                        } else{
                            throw new Exception("2nd argument must be a string.");
                        }
                        break;
                    case"port":
                        if(is_int($value)){

                            // Set Port
                            $this->Port = $value;

                            // Save to Configurator
                            $this->Configurator->set('smtp',$option, $value);
                        } else{
                            throw new Exception("2nd argument must be an integer.");
                        }
                        break;
                    case"encryption":
                        if(is_string($value)){

                            // Set Encryption
                            $this->Encryption = $value;

                            // Save to Configurator
                            $this->Configurator->set('smtp',$option, $value);
                        } else{
                            throw new Exception("2nd argument must be a string.");
                        }
                        break;
                    case"ctencoding":
                        if(is_string($value)){

                            // Set Encoding
                            $this->CTEncoding = $value;

                            // Save to Configurator
                            $this->Configurator->set('smtp',$option, $value);
                        } else{
                            throw new Exception("2nd argument must be a string.");
                        }
                        break;
                    case"sbjillegalcharacters":
                        if(is_bool($value)){

                            // Set Illegal Characters
                            $this->SBJIllegalCharacters = $value;

                            // Save to Configurator
                            $this->Configurator->set('smtp',$option, $value);
                        } else{
                            throw new Exception("2nd argument must be a string.");
                        }
                        break;
                    default:
                        throw new Exception("unable to configure $option.");
                        break;
                }
            } else{
                throw new Exception("1st argument must be as string.");
            }
        } catch (Exception $e) {
            $this->Logger->error('Error: '.$e->getMessage());
        }

        return $this;
    }

    /**
     * This method connects to an SMTP server using the specified credentials and encryption type.
     *
     * @param  string  $username
     * @param  string  $password
     * @param  string  $host
     * @param  int|string|null  $port
     * @param  string|null  $encryption
     * @return boolean
     * @throws Exception
     */
    public function connect($username,$password,$host,$port = 465,$encryption = 'ssl'){
        try {

            // If a connection is already established return it
            if ($this->Connection) {
                return $this->Connection;
            }

            // Checking for an open port
            if(!$this->Net->scan($host,$port)){
                throw new Exception("SMTP port on {$host} is closed or blocked.");
            }

            // Set Encryption
            $ssl = in_array($encryption, ['SSL', 'ssl']);
            if($ssl){
                $host = 'ssl://' . $host;
            }

            // Connect to an SMTP server
            $this->Logger->info("Establishing connection to SMTP server.");
            $smtp = stream_socket_client($host . ':' . $port, $errno, $errstr, 30);
            if (!$smtp) {
                throw new Exception("Could not connect to SMTP server: {$errstr}");
            }
            $this->Logger->success("SMTP server connected.");

            // Greeting
            $greeting = fgets($smtp, 1024);
            if (!$greeting) {
                fclose($smtp);
                throw new Exception("No greeting received from SMTP server");
            }
            $this->Logger->debug("SMTP Greeting: {$greeting}");
            if (substr($greeting, 0, 3) != '220') {
                fclose($smtp);
                throw new Exception("{$greeting}");
            }

            // EHLO
            fputs($smtp, "EHLO {$host}" . PHP_EOL);
            $ehlo_response = '';
            while ($line = fgets($smtp, 1024)) {
                $ehlo_response .= $line;
                if (substr($line, 3, 1) === ' ') {
                    break;
                }
            }
            $this->Logger->debug("SMTP EHLO: " . PHP_EOL . "{$ehlo_response}");
            if (substr($ehlo_response, 0, 3) != self::SMTP_OK) {
                fclose($smtp);
                throw new Exception("{$ehlo_response}");
            }

            // TLS
            if ($ssl && strpos($ehlo_response, 'STARTTLS') !== false) {
                fputs($smtp, "STARTTLS" . PHP_EOL);
                $tls_response = fgets($smtp, 1024);
                $this->Logger->debug("SMTP TLS: {$tls_response}");
                if (substr($tls_response, 0, 3) != '220') {
                    fclose($smtp);
                    throw new Exception("{$tls_response}");
                }
                if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($smtp);
                    throw new Exception("Could not start TLS encryption.");
                }
                // Re-issue EHLO
                fputs($smtp, "EHLO {$_SERVER['HTTP_HOST']}" . PHP_EOL);
                $ehlo_response = '';
                while ($line = fgets($smtp, 1024)) {
                    $ehlo_response .= $line;
                    if (substr($line, 3, 1) === ' ') {
                        break;
                    }
                }
                $this->Logger->debug("SMTP 2nd EHLO: {$ehlo_response}");
                if (substr($ehlo_response, 0, 3) != self::SMTP_OK) {
                    fclose($smtp);
                    throw new Exception("{$ehlo_response}");
                }
            }

            // Authenticate
            $this->Logger->info("Authenticating on SMTP server.");
            fputs($smtp, "AUTH LOGIN" . PHP_EOL);
            $out = fgets($smtp, 1024);
            $this->Logger->debug("SMTP AUTH: {$out}");
            if (substr($out, 0, 3) != self::SMTP_AUTH_OK) {
                fclose($smtp);
                throw new Exception("{$out}");
            }

            // Send Username
            fputs($smtp, base64_encode($username) . PHP_EOL);
            $out = fgets($smtp, 1024);
            $this->Logger->debug("SMTP Username: {$out}");
            if (substr($out, 0, 3) != self::SMTP_USERNAME_OK) {
                fclose($smtp);
                throw new Exception("{$out}");
            }

            // Send Password
            fputs($smtp, base64_encode($password) . PHP_EOL);
            $out = fgets($smtp, 1024);
            $this->Logger->debug("SMTP Password: {$out}");
            if (substr($out, 0, 3) != self::SMTP_PASSWORD_OK) {
                fclose($smtp);
                throw new Exception("{$out}");
            }

            // If we've got this far, authentication was successful
            $this->Logger->success("Authenticated on SMTP server");

            // Let's save the connection
            $this->Connection = $smtp;
            $this->Host = $host;
            $this->Port = $port;
            $this->Encryption = $encryption;
            $this->Username = $username;
            $this->Password = $password;

            // Return
            return true;
        } catch (Exception $e) {
            $this->Logger->error('SMTP Error: '.$e->getMessage());
            return false;
        }
    }

    /**
     * This method closes the SMTP connection.
     *
     * @return void
     */
    public function close(){
        // Check if a connection exist
        if($this->Connection){

            // Close the active connection
            fclose($this->Connection);

            // Clear any connection information
            $this->Connection = null;
            $this->Host = null;
            $this->Port = null;
            $this->Encryption = null;
            $this->Username = null;
            $this->Password = null;

            // Log the closing
            $this->Logger->success("SMTP connection closed");
        }
    }

    /**
     * This method return a boolean value indicating if a connection was established or not.
     *
     * @return boolean
     */
    public function isConnected(){
        return ($this->Connection);
    }

    /**
     * This method logs in to the SMTP server using the specified credentials and encryption type and can be use as an authentication method.
     *
     * @param  string  $username
     * @param  string  $password
     * @param  string  $newHost
     * @param  int|string|null  $newPort
     * @param  string|null  $newEncryption
     * @return boolean
     * @throws Exception
     */
    public function login($username,$password,$newHost = null,$newPort = 465,$newEncryption = 'ssl'){

        // Initiate variables
        $Connection = null;
        $Host = null;
        $Port = null;
        $Encryption = null;
        $Username = null;

        // Check if a connection was already established. If so, let's store it for later.
        if($this->Connection){
            $Connection = $this->Connection;
            $Host = $this->Host;
            $Port = $this->Port;
            $Encryption = $this->Encryption;
            $Username = $this->Username;
            $Password = $this->Password;
        }

        // Check if a SMTP server connection information was provided.
        // If none are provided, and a connection already exist, use the same connection information as the one stored.
        if($newHost === null && $this->Connection){
            $newHost = $Host;
            $newPort = $Port;
            $newEncryption = $Encryption;
        }

        // Lets attempt a connection with the provided connection information
        $result = $this->connect($username,$password,$newHost,$newPort,$newEncryption);
        $this->close();

        // Let's restore the saved connection
        if($Connection){
            $this->Connection = $Connection;
            $this->Host = $Host;
            $this->Port = $Port;
            $this->Encryption = $Encryption;
            $this->Username = $Username;
            $this->Password = $Password;
        }

        // Return
        return $result;
    }

    /**
     * This method evaluate if a string contains HTML tags.
     *
     * @param  string  $content
     * @return boolean
     */
    private function hasHTML($content){
        // Strip all HTML tags from the content
        $stripped = strip_tags($content);

        // Compare the stripped content to the original content
        return $stripped !== $content;
    }

    /**
     * This method converts a string that may contain HTML into plain text.
     *
     * @param  string  $html
     * @return string
     */
    private function toText($html){

        // Check if content contains HTML
        if($this->hasHTML($html)){

            // Initialise $text
            $text = '';

            // Create an empty DOMDocument
            $dom = new DOMDocument();

            // Suppress any parsing errors
            libxml_use_internal_errors(true);

            // Load Content
            $dom->loadHTML($html);

            // Retrieve the body
            $content = $dom->textContent;

            // Remove spaces between new lines
            $content = preg_replace('/\n\s+\n/', "\n\n", $content);

            // Convert to array based on lines
            $lines = explode(PHP_EOL,$content);

            // Trim each lines of extra spaces add them to the $text string
            foreach($lines as $line){
                $text .= trim($line) . PHP_EOL;
            }

            // Return $text string
            return trim($text);
        }

        // Otherwise return the original content
        return $html;
    }

    /**
     * Save the sent email as a .eml file in the specified directory.
     *
     * @param string $content The email content.
     * @param string $filename The filename to use for the .eml file (without extension).
     * @param string|null $directory The directory where the .eml file should be saved.
     * @return bool True if the file was saved successfully, false otherwise.
     * @throws Exception
     */
    private function save($content, $filename = null, $directory = self::SMTP_DATA_DIRECTORY) {
        try{

            // Sanitize file name
            if($filename === null){
                $filename = time();
            }

            // Generate file path
            $filepath = $directory . DIRECTORY_SEPARATOR . $filename . ".eml";

            // Create the directory recursively if it doesn't exist
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0777, true)) {
                    throw new Exception("Unable to create directory {$directory}");
                }
            }

            // Validate the directory and create the eml file
            if (is_dir($directory) && is_writable($directory)) {
                if (file_put_contents($filepath, trim($content))) {
                    $this->Logger->success("File Saved: {$filepath}");
                    return true;
                } else {
                    throw new Exception("Unable to create {$filepath}");
                }
            } else {
                throw new Exception("Invalid directory specified: {$directory}");
            }
        } catch (Exception $e) {

            // Log error
            $this->Logger->error('SMTP Error: '.$e->getMessage());
            return false;
        }
    }

    /**
     * This method is used to add template files.
     *
     * @param  string|null  $name
     * @param  string|null  $file
     * @return void
     * @throws Exception
     */
    public function addTemplate($name, $file = null){
        try {
            if(is_string($name) && is_string($file)){
                if(is_file($file)){
                    if(!isset($this->Templates[$name])){
                        $this->Templates[$name] = $file;
                        return true;
                    } else {
                        throw new Exception("This template already exist.");
                    }
                } else {
                    throw new Exception("Could not find the following template file: {$file}.");
                }
            } else {
                throw new Exception("Both arguments must be strings.");
            }
        } catch (Exception $e) {

            // Log error
            $this->Logger->error('SMTP template error: '.$e->getMessage());

            // Return
            return false;
        }
    }

    /**
     * This method is used to select a template file.
     *
     * @param  string|null  $name
     * @return void
     * @throws Exception
     */
    public function setTemplate($name){
        try {
            if(is_string($name)){
                if(isset($this->Templates[$name])){
                    $this->Template = $name;
                    return true;
                } else {
                    throw new Exception("Could not find the requested template.");
                }
            } else {
                throw new Exception("Argument must be string.");
            }
        } catch (Exception $e) {
            $this->Logger->error('SMTP template error: '.$e->getMessage());

            // Return
            return false;
        }
    }

    /**
     * This method retrieves the loaded template and inserts the body into it.
     *
     * @param  string  $body
     * @return string
     * @throws Exception
     */
    private function loadTemplate($defaults){
        try {

            // Validations
            if(!is_array($defaults)){
                throw new Exception("Invalid argument.");
            }
            if(!isset($defaults['body'])){
                throw new Exception("Body not found.");
            }

            // Check if a template has been loaded
            if($this->Template){

                // Get the content of the template file
                $Template = file_get_contents($this->Templates[$this->Template]);

                // Insert the body content
                foreach($defaults as $key => $value){
                    if(!in_array($key,['attachment'])){
                        if(is_array($value)){
                            $value = current($value);
                        }
                        if($value !== null){
                            $Template = str_replace('%' . strtoupper($key) . '%',$value,$Template);
                        }
                    }
                }

                // Return the Template
                return $Template;
            }

            // If no template were loaded, return the existing body.
            return $defaults['body'];
        } catch (Exception $e) {
            $this->Logger->error('SMTP template error: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Sanitize the email subject to remove illegal characters.
     *
     * @param string $subject
     * @return string
     */
    private function sanitizeSubject($subject) {
        if ($this->SBJIllegalCharacters) {
            return $subject; // Return original subject if illegal characters are allowed
        }

        // Array of French accented characters and their ASCII equivalents
        $accents = array(
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ç' => 'c', 'é' => 'e',
            'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ÿ' => 'y', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ç' => 'C',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Î' => 'I',
            'Ï' => 'I', 'Ô' => 'O', 'Ö' => 'O', 'Ù' => 'U', 'Û' => 'U',
            'Ü' => 'U', 'Ÿ' => 'Y'
        );

        // Replace accented characters with their non-accented counterparts
        $subject = strtr($subject, $accents);

        // Define illegal characters
        $illegalChars = array(
            '\n' => '',
            '\r\n' => '',
            '<' => '',
            '>' => '',
            '{' => '',
            '}' => '',
            '|' => '/',
            '\\' => '/',
        );

        // Replace illegal characters with a safe alternative or remove them
        $subject = strtr($subject, $illegalChars);

        return $subject;
    }

    /**
     * This method sends an email using the SMTP connection. It accepts an array of options including the email recipients, subject, body, and attachments.
     *
     * @param  array|null  $options
     * @return string Return the content sent
     * @throws Exception
     */
    public function send($options = []){

        // Default Values
        $defaults = [
            "from" => $this->Username,
            "reply-to" => null,
            "to" => [],
            "cc" => [],
            "bcc" => [],
            "attachment" => [],
            "subject" => null,
            "body" => null,
            "headers" => [],
        ];

        // Required Values
        $required = ['from','to','subject','body'];

        // Are options valide?
        if (!is_array($options)) {
            $this->Logger->error("Invalid argument");
            throw new Exception("Invalid argument");
        }

        // Importing Configurations
        foreach($options as $key => $value){
            $key = strtolower($key);
            switch($key){
                case "to":
                case "cc":
                case "bcc":
                case "attachment":
                case "headers":
                    if(!is_array($value)){
                        $value = array($value);
                    }
                    if(isset($defaults[$key])){
                        foreach($value as $k => $v){
                            $defaults[$key][] = $v;
                        }
                    }
                    break;
                default:
                    if(isset($defaults[$key]) || $defaults[$key] === null){
                        $defaults[$key] = $value;
                    }
                    break;
            }
        }

        $this->Logger->debug("Configuration: ".json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Try to send the email
        try {

            // Check if the connection is established
            if (!$this->Connection) {
                throw new Exception("No connection found");
            }

            // Check for required information
            foreach($required as $criteria){
                if(empty($defaults[$criteria])){
                    throw new Exception("Missing Criteria: {$criteria}");
                }
            }

            // Sender
            fputs($this->Connection, "MAIL FROM:<{$defaults['from']}>" . PHP_EOL);
            $out = fgets($this->Connection, 1024);
            $this->Logger->debug("SMTP Sender: {$out}");
            if (substr($out, 0, 3) != self::SMTP_OK) {
                throw new Exception("{$out}");
            }

            // Recipients (To, CC, BCC) handling
            foreach (['to', 'cc', 'bcc'] as $recipient_type) {
                if (!empty($defaults[$recipient_type])) {
                    foreach ($defaults[$recipient_type] as $recipient) {
                        $this->Logger->debug("Sending RCPT TO for {$recipient_type}: {$recipient}");
                        fputs($this->Connection, "RCPT TO:<{$recipient}>" . PHP_EOL);
                        $out = fgets($this->Connection, 1024);
                        $this->Logger->debug("SMTP {$recipient_type} Recipient: {$out}");
                        if (substr($out, 0, 3) != self::SMTP_OK) {
                            throw new Exception("{$out}");
                        }
                    }
                }
            }

            // Data
            fputs($this->Connection, "DATA" . PHP_EOL);
            $out = fgets($this->Connection, 1024);
            $this->Logger->debug("SMTP Data: {$out}");
            if (substr($out, 0, 3) != self::SMTP_DATA_OK) {
                throw new Exception("{$out}");
            }

            // Body preparation
            $html = $this->loadTemplate($defaults);
            if(!$html){
                throw new Exception("Unable to retreive the body for the message.");
            }
            $text = $this->toText($html);

            // Boundary for multipart emails
            $boundary = strtoupper(uniqid(time() . '-'));

            // Headers setup
            $headers = "From: {$defaults['from']}" . PHP_EOL;
            $headers .= "To: " . implode(',', $defaults['to']) . PHP_EOL;
            if (!empty($defaults['reply-to'])) {
                $headers .= "Reply-To: " . implode(',', $defaults['reply-to']) . PHP_EOL;
            }
            if (!empty($defaults['cc'])) {
                $headers .= "Cc: " . implode(',', $defaults['cc']) . PHP_EOL;
            }
            $headers .= "Subject: " . $this->sanitizeSubject($defaults['subject']) . PHP_EOL;
            $headers .= "Date: " . date('r') . PHP_EOL;
            $headers .= "MIME-Version: 1.0" . PHP_EOL;

            // Add custom headers (including JSON if needed)
            foreach ($defaults['headers'] as $header) {
                if (is_array($header)) {
                    // JSON-encode the header value if it is an array
                    $header_value = json_encode($header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $headers .= "X-Custom-Header: {$header_value}" . PHP_EOL;
                } else {
                    $headers .= $header . PHP_EOL;
                }
            }

            // Check encoding and apply it to the message body
            if ($this->CTEncoding === 'base64') {
                $encodedTextBody = chunk_split(base64_encode($text));
                $encodedHtmlBody = chunk_split(base64_encode($html));
            } else {
                $encodedTextBody = quoted_printable_encode($text);
                $encodedHtmlBody = quoted_printable_encode($html);
            }

            // Determine if multipart
            $multipart = (count($defaults['attachment']) > 0 || $this->hasHTML($html));
            if ($multipart) {
                $headers .= "Content-Type: multipart/alternative; boundary={$boundary}" . PHP_EOL . PHP_EOL;
            }

            // Message preparation
            $message = '';
            if ($multipart) {
                $message .= "--{$boundary}" . PHP_EOL;
            }

            // Insert plain text part
            $message .= "Content-Type: text/plain; charset=UTF-8" . PHP_EOL;
            $message .= "Content-Transfer-Encoding: " . $this->CTEncoding . PHP_EOL . PHP_EOL;
            $message .= $encodedTextBody . PHP_EOL;

            // Insert HTML part if present
            if ($this->hasHTML($html)) {
                $message .= "--{$boundary}" . PHP_EOL;
                $message .= "Content-Type: text/html; charset=UTF-8" . PHP_EOL;
                $message .= "Content-Transfer-Encoding: " . $this->CTEncoding . PHP_EOL . PHP_EOL;
                $message .= $encodedHtmlBody . PHP_EOL;
            }

            // Handle attachments
            foreach ($defaults['attachment'] as $attachment) {
                $file_path = $attachment;
                $file_name = basename($file_path);
                $file_mime_type = mime_content_type($file_path);
                $file_content = chunk_split(base64_encode(file_get_contents($file_path)));
                $message .= "--{$boundary}" . PHP_EOL;
                $message .= "Content-Type: $file_mime_type; name=\"$file_name\"" . PHP_EOL;
                $message .= "Content-Transfer-Encoding: base64" . PHP_EOL;
                $message .= "Content-Disposition: attachment; filename=\"$file_name\"" . PHP_EOL . PHP_EOL;
                $message .= $file_content . PHP_EOL;
            }

            // Finalize message
            if ($multipart) {
                $message .= "--{$boundary}--" . PHP_EOL;
            }
            $message .= "." . PHP_EOL;

            // Log the message
            $this->Logger->debug("SMTP Message: " . PHP_EOL . $headers . $message);

            // Send message
            fputs($this->Connection, $headers . $message);
            $out = fgets($this->Connection, 1024);
            $this->Logger->debug("SMTP Message: {$out}");

            // Check if the message was sent successfully
            if (substr($out, 0, 3) != self::SMTP_OK) {
                throw new Exception("{$out}");
            }

            // Extract the Message-ID if present in the response
            if (preg_match('/<(.+)>/', $out, $matches)) {
                $messageId = $matches[1];
                $this->Logger->success("Message-ID: {$messageId}");
            }

            // return the message
            $this->Logger->success("Email sent successfully");
            return $headers . $message;
        } catch (Exception $e) {

            // Log error
            $this->Logger->error('SMTP Error: '.$e->getMessage());
            return false;
        }
	}

    /**
     * Check if the library is installed.
     *
     * @return bool
     */
    public function isInstalled(){

        // Retrieve the path of this class
        $reflector = new ReflectionClass($this);
        $path = $reflector->getFileName();

        // Retrieve the filename of this class
        $filename = basename($path);

        // Modify the path to point to the config directory
        $path = str_replace('src/' . $filename, 'config/', $path);

        // Add the requirements to the Configurator
        $this->Configurator->add('requirements', $path . 'requirements.cfg');

        // Retrieve the list of required modules
        $modules = $this->Configurator->get('requirements','modules');

        // Check if the required modules are installed
        foreach($modules as $module){

            // Check if the class exists
            if (!class_exists($module)) {
                return false;
            }

            // Initialize the class
            $class = new $module();

            // Check if the method exists
            if(method_exists($class, isInstalled)){

                // Check if the class is installed
                if(!$class->isInstalled()){
                    return false;
                }
            }
        }

        // Return true
        return true;
    }
}
