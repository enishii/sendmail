## Introduction
I wrote this small class when I had to replace the standard PHP sendmail function by PHPMailer. The standard sendmail function takes several arguments, that could not be used in my case for PHPMailer directly. It would also mean I had to change a lot of code in each webpage. 

More information on sendmail can be found here: http://php.net/manual/en/function.mail.php
More information on PHPMailer can be found here: https://github.com/PHPMailer/PHPMailer

This class takes four arguments. I haven't used the optional 'additional_parameters' argument in sendmail, so no need to use it here either. The arguments were taken from a website form which composed the complete body of the e-mail in my case. This class separates the HTML and plain text based on boundaries that were used in the message creation on the webpage. You can of course change this as you like. 

## Example

```php
<?php

require 'myPHPMailer.php';

	# Set parameters
	$eol = "\r\n";
  $mime_boundary = md5(time());
	$toEmail = 'John User <example@example.com>';
	
	# First piece of the mailer
  $message = "--" . $mime_boundary . $eol;

  # Text message
  $message .= "Content-Type: text/plain; charset=iso-8859-1" . $eol;
  $message .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
	$message .= 'This is the TEXT message body.' . $eol . $eol;

  # HTML Version
  $message .= "--" . $mime_boundary . $eol;
  $message .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
  $message .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
	$message .= 'This is the HTML message body <b>in bold!</b>' . $eol . $eol;
  $message .= "--" . $mime_boundary . "--" . $eol . $eol;

	
	$subject = 'Here is the subject';
	$headers = "From: Joe User <joe.user@example.com>" . $eol;
  $headers .= "CC: Another User <another.user@example.com>" . $eol;
  $headers .= "Reply-To: Another User <another.user@example.com>" . $eol;
  $headers .= "Message-ID: <" . time() . "-" . "YOUR_UNIQUE_ID" . ">" . $eol;
  $headers .= "Content-Type: multipart/mixed; boundary=\"" . $mime_boundary . "\"" . $eol . $eol;

// Send the email
   $sendmail = new myPHPMailer(true); //true for exceptions	
   if ($sendmail->send_mail($toEmail, $subject, $message, $headers)) {
	      echo 'Message has been sent' . "\n";
	 }
	 else {
	      echo 'Message has NOT been sent' . "\n";
	 }
   
?>
```

