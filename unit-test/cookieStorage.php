<?php
require dirname(__FILE__) ."/unit-test.common.php";
function CookieStorage_V1_UnitTest(){

	if(!isset($_GET['step'])) $_GET['step'] = 1;

	echo("<!DOCTYPE HTML5>");
	echo("<html>");
	echo("<head>");
	echo("<title>Unit Test for class Storage (using CookieStorage) v 1.0</title>" );
	echo("<style type=\"text/css\">*{ padding: 0.2em; } .success{ color: #040; background-color: #efe; } .error{ color: #400; background-color: #fee;} code{ background-color: #eee; border: 1px solid #ccc; color: #444; } </style>");
	echo("</head>");
	echo("<body>");
	echo("<h1>Unit Test for class Storage (using CookieStorage)</h1>");
	echo("<p>Starting the checks</p>");

	$encrypted1 	= 'cookieEncrypted1';
	$encrypted2 	= 'cookieEncrypted2';
	$clearText 		= 'cookieClearText';
	$arrayCookie 	= 'arrayCookie';
	$arrayValues	= array(
			'first-item' => 'lorem',
			'second-item'=> 'ipsum'
			); 
	if($_GET['step']==1){
		
		/*
		 *  Step 1
		 */	
		$aut = new AlphaUnitTest('CookieStorage-1');
		$aut->throwException(false);
		$aut->stopOnFirstError(true);
		// Simple Variable Settings
		
		$storage = ClassFactory::get('Storage');
		$storage->setStorage(COOKIE_STORAGE);
		$storage->enableEncryption(true);
		
		$storage->setSalt('AlphaFramework');
		
		/*
		 *  Test #1: Writing a cookie
		 */
		$storage->write($encrypted1, $encrypted1);
		
		$aut->throwIfNot(
			$aut->equals( $storage->read($encrypted1), $encrypted1),
			"Cookie correctly setted", 
			"Cookie not set correctly",
			$storage->read($encrypted1)
		);
	
		/*
		 * Test #2: checking cookie encryption
		 */
		
		$aut->throwIf(
				$aut->isNull( $storage->read($encrypted1)),
				"Cookie is correctly encrypted",
				"Cookie is not encrypted",
				$_COOKIE[$encrypted1]
				);
	
		/*
		 * Test #3: checking unencrypted cookie
		 */
		$storage->enableEncryption(false);
		
		$storage->write($clearText, $clearText);
		$aut->throwIfNot(
				$aut->equals( $storage->read($clearText), unserialize($_COOKIE[$clearText])),
				"Cookie is correctly stored as clean value",
				"Cookie is encrypted!",
				$storage->read($clearText),
				$_COOKIE[$clearText]
		);
		
		/*
		 * Test #4: re-enabling encryption and reading the unencrypted value
		 * 
		 */
		$storage->enableEncryption(true);
		$aut->throwIfNot(
				$aut->equals( $storage->read($encrypted1), $encrypted1),
				"Cookie read correctly",
				"Cookie is not read correctly!",
				$storage->read($encrypted1),
				$_COOKIE[$encrypted1]
		);
	
		/*
		 * Test #5: Changing the salt
		 */
		$storage->setSalt('Alpha-Framework');
		$aut->throwIf(
				$aut->equals( $storage->read($encrypted1), $encrypted1),
				
				"Cookie not read because it has a different salt key",
				
				"The salt has been changed, however the cookie is read correctly! ",
				
				$storage->read($encrypted1),
				$_COOKIE[$encrypted1]
		);
		
		/*
		 * Test #6: Writing cookie with the new different salt key
		 */
		$storage->write($encrypted2, $encrypted2);
		$aut->throwIfNot(
				$aut->equals( $storage->read($encrypted2), $encrypted2),
				"Cookie with new salt has been read correctly",
				"The cookie has a different salt!",
				$storage->read($encrypted2),
				$_COOKIE[$encrypted2]
		);
		
		/*
		 * Test #7: Reading the unencrypted cookie with encryption enabled
		 */
		$aut->throwIfNot(
				$aut->isNull( $storage->read($clearText)),
				"Cookie with different salt return null value!",
				"The cookie has a different salt!",
				$storage->read($clearText),
				$_COOKIE[$clearText]
		);
		
		
		/*
		 * Test #8: Writing and Reading Array in cookie
		 */
		$storage->write($arrayCookie, $arrayValues);
		
		$aut->throwIf(
			$aut->isNull($storage->read($arrayCookie)), 
			"Array Cookie correctly written", 
			"Array Cookie seems to be not set",
			$storage->read($arrayCookie));
		
		
		/*
		 * Test #9: Checking array cookie data type
		 */
		$aut->throwIfNot(
			$aut->isArray($storage->read($arrayCookie)),
			"Array Cookie is obviously an array", 
			"Array cookie is not an array",
			$storage->read($arrayCookie));
		/*
		 * Test #10: Checking array cookie value
		 */
		
		$aut->throwIfNot(
			$aut->equals($storage->read($arrayCookie), $arrayValues),
			"Data in Array Cookie is as expected",
			"Array Cookie contents is not what expected",
			$storage->read($arrayCookie));
		
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
		$aut = new AlphaUnitTest('CookieStorage-2');
		$aut->throwException(false);
		$aut->stopOnFirstError(true);
		
		$storage = ClassFactory::get('Storage');
		$storage->setStorage(COOKIE_STORAGE);
		$storage->setSalt('AlphaFramework');
		
		/*
		 * Test #1: Read the encrypted cookie
		 */
		$storage->enableEncryption(true);
		$aut->throwIfNot(
				$aut->equals( $storage->read($encrypted1), $encrypted1),
				"Cookie stored in step 1 read correctly",
				"Cookie has not persistence! Check browser settings.",
				$storage->read($encrypted1),
				$_COOKIE[$encrypted1]
		);
		
		/*
		 * Test #2: Read the clean cookie
		 */
		$storage->enableEncryption(false);
		$aut->throwIfNot(
				$aut->equals( $storage->read($clearText), unserialize($_COOKIE[$clearText])),
				"Cookie is correctly stored as clean value",
				"Cookie is encrypted!",
				$storage->read($clearText),
				$_COOKIE[$clearText]
		);
		
		/*
		 * Test #3: Read the encoded cookie with different salt key
		 */
		$storage->enableEncryption(true);
		$storage->setSalt('Alpha-Framework');
		
		$aut->throwIfNot(
				$aut->isNull( $storage->read($encrypted1) ),
				"Cookie has different salt, cannot be read",
				"Cookie has been read unexpectedly!",
				$storage->read($encrypted1),
				$_COOKIE[$encrypted1]
		);
		
		/*
		 * Test #4: Read the encoded cookie with right salt key
		 */
		$aut->throwIfNot(
				$aut->equals( $storage->read($encrypted2), $encrypted2),
				"Cookie correctly read!",
				"Cookie has different salt, cannot be read",
				$storage->read($encrypted2),
				$_COOKIE[$encrypted2]
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


CookieStorage_V1_UnitTest();
?>