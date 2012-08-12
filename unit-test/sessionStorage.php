<?php
require dirname(__FILE__) ."/unit-test.common.php";
function SessionStorage_V1_UnitTest(){

	if(!isset($_GET['step'])) $_GET['step'] = 1;

	echo("<!DOCTYPE HTML5>");
	echo("<html>");
	echo("<head>");
	echo("<title>Unit Test for class Storage (using SessionStorage) v 1.0</title>" );
	echo("<style type=\"text/css\">*{ padding: 0.2em; } .success{ color: #040; background-color: #efe; } .error{ color: #400; background-color: #fee;} code{ background-color: #eee; border: 1px solid #ccc; color: #444; } </style>");
	echo("</head>");
	echo("<body>");
	echo("<h1>Unit Test for class Storage (using SessionStorage)</h1>");
	echo("<p>Starting the checks</p>");

	$encrypted1 	= 'sessionEncrypted1';
	$encrypted2 	= 'sessionEncrypted2';
	$clearText 		= 'sessionClearText';
	$arraySession 	= 'arrayInSession';
	$arrayValues	= array(
			'first-item' => 'lorem',
			'second-item'=> 'ipsum'
			); 
	if($_GET['step']==1){
		
		/*
		 *  Step 1
		 */	
		$aut = new AlphaUnitTest('SessionStorage-1');
		$aut->throwException(false);
		$aut->stopOnFirstError(true);
		// Simple Variable Settings
		
		$storage = ClassFactory::get('Storage');
		$storage->setStorage(SESSION_STORAGE);
		$storage->enableEncryption(true);
		
		$storage->setSalt('AlphaFramework');
		
		/*
		 *  Test #1: Writing a key in the session
		 */
		$storage->write($encrypted1, $encrypted1);
		
		$aut->throwIfNot(
			$aut->equals( $storage->read($encrypted1), $encrypted1),
			"Session variable correctly setted", "Session variable not set correctly",
			$storage->read($encrypted1)
		);
	
		/*
		 * Test #2: checking Session variable encryption
		 */
		
		$aut->throwIf(
				$aut->isNull( $storage->read($encrypted1)),
				"Session variable is correctly encrypted",
				"Session variable is not encrypted",
				$_SESSION[$encrypted1]
				);
	
		/*
		 * Test #3: checking unencrypted Session variable
		 */
		$storage->enableEncryption(false);
		
		$storage->write($clearText, $clearText);
		$aut->throwIfNot(
				$aut->equals( $storage->read($clearText), unserialize($_SESSION[$clearText])),
				"Session variable is correctly stored as clean value",
				"Session variable is encrypted!",
				$storage->read($clearText),
				$_SESSION[$clearText]
		);
		
		/*
		 * Test #4: re-enabling encryption and reading the unencrypted value
		 * 
		 */
		$storage->enableEncryption(true);
		$aut->throwIfNot(
				$aut->equals( $storage->read($encrypted1), $encrypted1),
				"Session variable read correctly",
				"Session variable is not read correctly!",
				$storage->read($encrypted1),
				$_SESSION[$encrypted1]
		);
	
		/*
		 * Test #5: Changing the salt
		 */
		$storage->setSalt('Alpha-Framework');
		$aut->throwIf(
				$aut->equals( $storage->read($encrypted1), $encrypted1),
				
				"Session variable not read because it has a different salt key",
				
				"The salt has been changed, however the Session variable is read correctly! ",
				
				$storage->read($encrypted1),
				$_SESSION[$encrypted1]
		);
		
		/*
		 * Test #6: Writing Session variable with the new different salt key
		 */
		$storage->write($encrypted2, $encrypted2);
		$aut->throwIfNot(
				$aut->equals( $storage->read($encrypted2), $encrypted2),
				"Session variable with new salt has been read correctly",
				"The Session variable has a different salt!",
				$storage->read($encrypted2),
				$_SESSION[$encrypted2]
		);
		
		/*
		 * Test #7: Reading the unencrypted Session variable with encryption enabled
		 */
		$aut->throwIfNot(
				$aut->isNull( $storage->read($clearText)),
				"Session variable with different salt return null value!",
				"The Session variable has a different salt!",
				$storage->read($clearText),
				$_SESSION[$clearText]
		);
		
		
		/*
		 * Test #8: Writing and Reading Array in Session variable
		 */
		$storage->write($arraySession, $arrayValues);
		
		$aut->throwIf(
			$aut->isNull($storage->read($arraySession)), 
			"Array Session variable correctly written", 
			"Array Session variable seems to be not set",
			$storage->read($arraySession));
		
		
		/*
		 * Test #9: Checking array Session variable data type
		 */
		$aut->throwIfNot(
			$aut->isArray($storage->read($arraySession)),
			"Array Session variable is obviously an array", 
			"Array Session variable is not an array",
			$storage->read($arraySession));
		/*
		 * Test #10: Checking array Session variable value
		 */
		
		$aut->throwIfNot(
			$aut->equals($storage->read($arraySession), $arrayValues),
			"Data in Array Session variable is as expected",
			"Array Session variable contents is not what expected",
			$storage->read($arraySession));
		
		echo("<dl>");
		echo("<dt>Tests Passed:</dt><dd class=\"success\">" . $aut->passed() . "</dd>");
		echo("<dt>Tests Failed:</dt><dd class=\"error\">" . $aut->failed() . "</dd>");
		echo("</dl>");
		if($aut->isFullCompliant()){
			$url = $_SERVER['REQUEST_URI'];
			$url .= '&step=2';
			echo("<a href=\"$url\">Go to the next step 2</a>");
		}
	}else{
		$aut = new AlphaUnitTest('SessionStorage-2');
		$aut->throwException(false);
		$aut->stopOnFirstError(true);
		
		$storage = ClassFactory::get('Storage');
		$storage->setStorage(SESSION_STORAGE);
		$storage->setSalt('AlphaFramework');
		
		/*
		 * Test #1: Read the encrypted Session variable
		 */
		$storage->enableEncryption(true);
		$aut->throwIfNot(
				$aut->equals( $storage->read($encrypted1), $encrypted1),
				"Session variable stored in step 1 read correctly",
				"Session variable has not persistence! Check browser settings.",
				$storage->read($encrypted1),
				$_SESSION[$encrypted1]
		);
		
		/*
		 * Test #2: Read the clean Session variable
		 */
		$storage->enableEncryption(false);
		$aut->throwIfNot(
				$aut->equals( $storage->read($clearText), unserialize($_SESSION[$clearText])),
				"Session variable is correctly stored as clean value",
				"Session variable is encrypted!",
				$storage->read($clearText),
				$_SESSION[$clearText]
		);
		
		/*
		 * Test #3: Read the encoded Session variable with different salt key
		 */
		$storage->enableEncryption(true);
		$storage->setSalt('Alpha-Framework');
		
		$aut->throwIfNot(
				$aut->isNull( $storage->read($encrypted1) ),
				"Session variable has different salt, cannot be read",
				"Session variable has been read unexpectedly!",
				$storage->read($encrypted1),
				$_SESSION[$encrypted1]
		);
		
		/*
		 * Test #4: Read the encoded Session variable with right salt key
		 */
		$aut->throwIfNot(
				$aut->equals( $storage->read($encrypted2), $encrypted2),
				"Session variable correctly read!",
				"Session variable has different salt, cannot be read",
				$storage->read($encrypted2),
				$_SESSION[$encrypted2]
		);
		
		echo("<dl>");
		echo("<dt>Tests Passed:</dt><dd class=\"success\">" . $aut->passed() . "</dd>");
		echo("<dt>Tests Failed:</dt><dd class=\"error\">" . $aut->failed() . "</dd>");
		echo("</dl>");
		if($aut->isFullCompliant()){
			$url = $_SERVER['REQUEST_URI'];
			$url = preg_replace( '#'.preg_quote('&step=2','#').'#', '', $url);
			echo("<a href=\"$url\">Go to the previous step 1</a>");
		}
	}
	echo("</body>");
	echo("</html>");
	ob_flush();
	
}


SessionStorage_V1_UnitTest();
?>