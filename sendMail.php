<?php

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class sendMail extends PHPMailer {

		protected $user = 'username';			// SMTP username
		protected $passw = 'password'; 			// SMTP password
		protected $hostname = 'smtp.server';		// Specify main and backup SMTP servers
		protected $eol = "\r\n";

    public function __construct($exceptions) {
		 parent::__construct($exceptions);		// Passing `true` enables exceptions
		//Server settings
		$this->SMTPDebug = 1;                        	// Enable verbose debug output
		$this->isSMTP();                             	// Set mailer to use SMTP
		$this->Host = $this->hostname;
		$this->SMTPAuth = true;                         // Enable SMTP authentication
		$this->Username = $this->user;
		$this->Password = $this->passw;
	    	$this->SMTPSecure = 'tls';                      // Enable TLS encryption, `ssl` also accepted
	    	$this->Port = 587;                             	// TCP port to connect to
		$this->XMailer = "Sendmail (https://github.com/jnelissen/sendmail) through PHPMailer";
    }

	public function send_mail($toEmail, $subject, $message, $headers) {

		//Parse headers for further use.
		//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
		$header = imap_rfc822_parse_headers($headers);
	
		//Set sender From address
		if (isset($header->fromaddress)) {
			$address = $header->from[0];
			$email = "{$address->mailbox}@{$address->host}";
			$name = isset($address->personal) ? $address->personal : $email;
			$this->setFrom($email, $name);
		}
	
		//To: Recipient(s)
		foreach ($this->parseAddresses($toEmail) as $address) {
		$this->addAddress($address['address'], $address['name']);
		}
	
		//Reply-to address
		if (isset($header->reply_toaddress)) {
			$address = $header->reply_to[0];
			$email = "{$address->mailbox}@{$address->host}";
			$name = isset($address->personal) ? $address->personal : $email;
			$this->addReplyTo($email, $name);
		}
	
		//CC address(ee)
		if (isset($header->ccaddress)) {
			foreach ($header->cc as $address) {
			$email = "{$address->mailbox}@{$address->host}";
			$name = $address->personal;
			$this->addCC($email, $name);
			}
		}
	
		//BCC address(ee)
		if (isset($header->bccaddress)) {
			foreach ($header->bcc as $address) {
			$email = "{$address->mailbox}@{$address->host}";
			$name = $address->personal;
			$this->addBCC($email, $name);
			}
		}
	
		//Set Message-ID header
		if (isset($header->message_id)) {
			$this->MessageID = $header->message_id;
		}
	
		//Get Content-type & boundary from original message
		preg_match('/Content-Type:\s{0,}(.*?);\s*boundary=\"(.*?)\"/', $headers, $match);
	// 	$cType = $match[1];
		$boundary = $match[2];
		
		//Content
		$this->isHTML(true);                                  // Set email format to HTML
		$this->Subject = $subject;
		preg_match("/Content-Type:\s*text\/plain.*\R.*\R.*\R(\X*?)--${boundary}/", $message, $textmessage);
		preg_match("/Content-Type:\s*text\/html.*\R.*\R.*\R(\X*?)--${boundary}/", $message, $htmlmessage);
		$this->Body    = $htmlmessage[1];
		$this->AltBody = $textmessage[1];
	
		//Saving the mail through IMAP folder (Not required for office365, automatically saved in Sent items)
	    	$this->saveMail();
	
		return($this->send()?true:$this->ErrorInfo);

	}

	//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
	//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
	//You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels.
	protected function saveMail() {
// 		You can change 'Sent Mail' to any other folder or tag
		$path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";
		//Tell your server to open an IMAP connection using the same username and password as you used for SMTP
		$imapStream = imap_open($path, $this->user, $this->passw);
		$this->preSend(); // Required otherwise empty message is returned.
		$result = imap_append($imapStream, $path, $this->getSentMIMEMessage());
		imap_close($imapStream);
		return $result;
	}
}
?>
