<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class sendMail extends PHPMailer {

		protected $user = 'username';		// SMTP username
		protected $passw = 'password'; 					// SMTP password
		protected $hostname = 'smtp.server';		// Specify main and backup SMTP servers
		protected $eol = "\r\n";

    public function __construct($exceptions) {
		 parent::__construct($exceptions);			// Passing `true` enables exceptions
		//Server settings
		$this->SMTPDebug = 1;                        // Enable verbose debug output
		$this->isSMTP();                             // Set mailer to use SMTP
		$this->Host = $this->hostname;
		$this->SMTPAuth = true;                         // Enable SMTP authentication
		$this->Username = $this->user;
		$this->Password = $this->passw;
	    $this->SMTPSecure = 'tls';                      // Enable TLS encryption, `ssl` also accepted
	    $this->Port = 587;                              // TCP port to connect to
		$this->XMailer = "Sendmail (https://github.com/jnelissen/sendmail) through PHPMailer 6.0.5";
    }

	public function send_mail($naarEmail, $onderwerp, $bericht, $headers) {

		//Parse headers for further use.
		$header = imap_rfc822_parse_headers($headers);
	
		//Set sender From address
		if (isset($header->fromaddress)) {
			$address = $header->from[0];
			$email = "{$address->mailbox}@{$address->host}";
			$name = isset($address->personal) ? $address->personal : $email;
			$this->setFrom($email, $name);
		}
	
		//To: Recipient(s)
		foreach ($this->parseAddresses($naarEmail) as $address) {
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
		$this->Subject = $onderwerp;
		preg_match("/Content-Type:\s*text\/plain.*\R.*\R.*\R(\X+)\R\R--${boundary}/U", $bericht, $textmessage);
// 		preg_match("/Content-Type:\s*text\/plain.*\R.*8bit.*\R\R(\X*?)--${boundary}/", $bericht, $textmessage); //Mooier?
		preg_match("/Content-Type:\s*text\/html.*\R.*\R.*\R(\X+)\R\R--${boundary}/U", $bericht, $htmlmessage);
	// 	print_r("Textmessage:" . $eol . $eol . $textmessage[1] . $eol);
	// 	print_r("HTMLmessage:" . $eol . $eol . $htmlmessage[1] . $eol);
		$this->Body    = $htmlmessage[1];
		$this->AltBody = $textmessage[1];

// 		Optional: Check the final message
// 		$this->preSend();
// 		print_r("MIMEHeader: " . $this->getMailMIME());
// 		print_r("MIMEMessage: " . $this->getSentMIMEMessage() . $this->eol);
	
		//Saving the mail through IMAP folder (Not required for office365, automagically saved)
// 	    if ($this->saveMail()) {
// 	        echo "Message saved!" . $eol;
// 	    }
// 	    else {
// 	    	echo "Message save FAILED!" . $eol;
// 	    }
	
		return($this->send()?true:$this->ErrorInfo);

	}

	//Section 2: IMAP
	//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
	//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
	//You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels.
	protected function saveMail() {
// 		You can change 'Verzonden items' to any other folder or tag
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
