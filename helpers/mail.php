<?php
class HelperMail{
		
	/**
	 * Send an email
	 * @param string $from
	 * @param string $to
	 * @param string $cc
	 * @param string $bcc
	 * @param string $subject
	 * @param string $body
	 * @param string $replyTo
	 * @return void
	 */
	public static function mailSend($from, $to, $cc, $bcc, $subject, $body, $replyTo=null){
		
		if(function_exists('customMailSend')) 
			return customMailSend($from, $to, $cc, $bcc, $subject, $body, $replyTo);
		# ---
		# issue 0000025 resolution: Use of undefined constant sendmail_from - assumed 'sendmail_from'
		# Note:
		# I've forgotten to enclose sendmail_from between the quotes
		# ini_set(sendmail_from, $from);   
		ini_set('sendmail_from', $from);
		# ---
		if($replyTo==null) $replyTo = $from;
		$smtp = $_SERVER['SERVER_NAME']; 
		//if(function_exists('imap_8bit')) $body = imap_8bit($body);
		$header = "MIME-Version: 1.0\r\n";
		$header .= "Content-type: text/plain; charset=utf-8\r\n";
		//$header .= "Content-Transfer-Encoding: quoted-printable\r\n";
		$header .= "From: $from\r\n";
		$header .= ($cc!=''?"Cc:$cc\r\n":'');
		$header .= ($bcc!=''?"Bcc:$bcc\r\n":''); 
		$header .= "Reply-To: $replyTo\r\n" ;
		$header .= "Errors-To: $replyTo\r\n" ;
		$header .= "X-Sender: $replyTo\r\n";
		$header .= "Date: " . date('D, d M Y H:i:s O') . "\r\n";
		$header .= "Message-Id: <".md5(uniqid(rand())).".alpha@localhost>\r\n";
		$header .= "X-Mailer: ALPHA-Mail-PHP/" . phpversion();
	
		 mail($to,
			$subject,
			//$body,
			str_replace('=','=3D',$body),
			$header);
	}

}