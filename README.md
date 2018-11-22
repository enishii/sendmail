## Introduction
I wrote this small class when I had to replace the standard PHP sendmail function by PHPMailer. The standard sendmail function takes several arguments, that could not be used in my case for PHPMailer directly. It would also mean I had to change a lot of code in each webpage. 

More information on sendmail can be found here: http://php.net/manual/en/function.mail.php
More information on PHPMailer can be found here: https://github.com/PHPMailer/PHPMailer

This class takes four arguments. I haven't used the optional 'additional_parameters' argument in sendmail, so no need to use it here either. The arguments were taken from a website form which composed the complete body of the e-mail in my case. This class separates the HTML and plain text based on boundaries that were used in the message creation on the webpage. You can of course change this as you like. 

##Example
To be done...
