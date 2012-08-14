#SMS-MMS.php

##LICENSE
Dual licensed under the MIT or GPL Version 2 licenses.

##ABOUT

SMS-MMS.php is a php function for parsing SMS and MMS messages. (Incidentally this means it also parses email messages).

##EXAMPLE

Here's a brief example demonstrating capturing a message that has been piped to a script. I usually setup a unique email address with a forward to a pipe.  If you want to utilize a real phone number for receiving messages (instead of an email address), one option is to forward messages from Google voice to the email address that forwards to the pipe.  Another is to utilize one of the many Google Voice wrappers.

	#!/usr/local/bin/php -q
	<?php
	
	//get the stream contents
	//-----------------------
	
	$stream = fopen("php://stdin","r");
	$message = "";
	while(!feof($stream))
		$message .= fread($stream,1024);
	fclose($stream);
	
	//parse the contents
	if ((include 'SMS-MMS.php') != 'OK')
	    die();
	
	$data = parseSMS_MMS($message);
	
	?>

##DATA

`parseSMS_MMS()` returns an associative array with the following structure.  Please note that `parseSMS_MMS()` makes no effort to validate or find specific headers, which is important because every carrier, every phone type, and most sms gateways have their own unique set of headers they use and header information is disgustingly non-standardized.  It is left completely up to you to validate that what you feel should be present is there.

	[
		'headers' => [
			'From' => 'test@example.com',
			'To' => 'beta@example.com',
			'Subject' => 'Beta Test'
			...
			],
		'content' => [
			0 => [
					'headers' => [
						'Content-Type'=>'text/plain'
						],
					'data' => 'message string'
				],
			1 => [
					'headers' => [
						'Content-Type'=>'image/jpeg'
						],
					'data' => 'an image data string in base64'
				]
			...
			]
	]
	
##Getting Dirty

SMS and MMS are by no means 'plug and play' technologies.  Because headers are so poorly standardized it can be very difficult to construct robust SMS and MMS based applications.  The quickest way to figure out what you'll need to look for is to get a number of your employees to text an email address and examine the original text of those emails.