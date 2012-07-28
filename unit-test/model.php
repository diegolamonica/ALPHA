<?php
require dirname(__FILE__) ."/unit-test.common.php";
function Model_V2_0_UnitTest(){
	
	$aut = new AlphaUnitTest('Model');
	$aut->throwException(false);
	$aut->stopOnFirstError(true);	
	// Simple Variable Settings
	$m = ClassFactory::get('Model');
	echo("<!DOCTYPE HTML5>");
	echo("<html>");
	echo("<head>");
	echo("<title>Unit Test for class Model 2.0</title>" );
	echo("<style type=\"text/css\">*{ padding: 0.2em; } .success{ color: #040; background-color: #efe; } .error{ color: #400; background-color: #fee;} code{ background-color: #eee; border: 1px solid #ccc; color: #444; } </style>");
	echo("</head>");
	echo("<body>");
	echo("<h1>Unit Test for class Model</h1>");
	echo("<p>Starting the checks</p>");
	
	// the static variable "$variables" have to be defined on constructor method of the class Model
	// Test #1
	$aut->throwIf( 
			$aut->isNull(Model::$variables) ,	# Check 
			'$variables is not null!', 			# Success
			'$variables is null!');				# Fail
	
	
	// the static variable $variables is an associative array.
	// Test #2
	$aut->throwIfNot( 
			$aut->isArray(Model::$variables), 	# Check
			'$variables is an array', 			# Success
			'$variables is not an array!');		# Fail
	
	
	// Test #3
	$var = Model::$variables;
	$m->setVar("contact", "Average Joe");
	// is the same as writing:
	$var['contact'][0] = "Average Joe"; 
	
	$aut->throwIfNot( 
			$aut->equals(Model::$variables, $var),															# Check 
			'Simple association <code>$m->setVar("contact", "Average Joe")</code> has worked as expected', 	# Success
			'object $var is not equal to Model::$variables', 												# Fail
			
			Model::$variables);																				# Arguments
	
	// Test #4
	$m->setVar("contact.name", "Joe");
	$var['contact']['name'][0] = "Joe";
	
	$aut->throwIfNot(
		$aut->equals(Model::$variables, $var),															# Check
		'Simple association <code>$m->setVar("contact.name", "Joe")</code> has worked as expected', 	# Success
		'object $var is not equal to Model::$variables', 												# Fail
			
		Model::$variables);																				# Arguments
	
	
	// Test #5
	$m->setVar("contact.surname", "Average");
	$var['contact']['surname'][0] = "Average";

	$aut->throwIfNot(
			$aut->equals(Model::$variables, $var),															# Check
			'Simple association <code>$m->setVar("contact.surname", "Joe")</code> has worked as expected', 	# Success
			'object $var is not equal to Model::$variables', 												# Fail
				
			Model::$variables);																				# Arguments
	
	// Test #6
	$m->setVar("baseAddress", array(
			'street'	=> '52th Avenue',
			'city'		=> 'New York'
		));
	
	$var['baseAddress']['street'][0] = "52th Avenue";
	$var['baseAddress']['city'][0] = "New York";

	$aut->throwIfNot(
			$aut->equals(Model::$variables, $var),															# Check
			'Simple association <code>$m->setVar("variable", &lt;Associative Array&gt;)</code> has worked as expected', 	# Success
			'object $var is not equal to Model::$variables', 												# Fail
	
			Model::$variables);																				# Arguments
	
	# Test #7
	$m->setVar('contact.address', array(
			'street'	=> '52th Avenue',
			'city'		=> 'New York'
		));
	
	$var['contact']['address']['street'][0] = "52th Avenue";
	$var['contact']['address']['city'][0] = "New York";
	
	
	$aut->throwIfNot(
			$aut->equals(Model::$variables, $var),																						# Check
			'Complex association <code>$m->setVar("variable.subvariable", &lt;Associative Array&gt;)</code> has worked as expected', 	# Success
			'object $var is not equal to Model::$variables', 																			# Fail
	
			Model::$variables);																											# Arguments
	
	# Test #8
	
	$exampleArray = array( '1234567890', '1111111111' );
	
	$m->setVar('exampleArray', $exampleArray);
	$var['exampleArray'] = $exampleArray ;
	$aut->throwIfNot(
			$aut->equals(Model::$variables, $var),																						# Check
			'Simple association <code>$m->setVar("variable", &lt;Indexed Array&gt;)</code> has worked as expected', 	# Success
			'object $var is not equal to Model::$variables', 																			# Fail
	
			Model::$variables);																											# Arguments
	
	

	
	# Test #9
	$m->setVar('contact.phones', array( '1234567890', '1111111111' ));
	$var['contact']['phones'] = array("1234567890", "1111111111") ;
	$aut->throwIfNot(
			$aut->equals(Model::$variables, $var),																						# Check
			'Complext association <code>$m->setVar("variable.subvariables", &lt;Indexed Array&gt;)</code> has worked as expected', 	# Success
			'object $var is not equal to Model::$variables', 																			# Fail
	
			Model::$variables);																											# Arguments
	
	
	# Test #10
	$simpleObject = new stdClass();
	$simpleObject->key = 'abc';
	$simpleObject->value = 123;
	$simpleObject->flag = true;
	
	$m->setVar('simpleObject', $simpleObject);
	
	$var['simpleObject']['key'][0] 		= 'abc';
	$var['simpleObject']['value'][0] 	= 123;
	$var['simpleObject']['flag'][0] 	= true;
	
	$aut->throwIfNot(
			$aut->equals(Model::$variables, $var),																	# Check
			'Simple association <code>$m->setVar("variable", &lt;Class Object&gt;)</code> has worked as expected', 	# Success
			'object $var is not equal to Model::$variables', 														# Fail
	
			Model::$variables);																						# Arguments
	
	# Test #11
	$m->setVar('contact.apointments', 
			array( 
					array(
							'description' 	=> 'Remember to visit Alpha Framework Website!',
							'where'			=> 'http://alpha-framework.com',
							'when'			=> 'Mon 30 Jul 2012'
					),
					$simpleObject
			)
	);
	
	$var['contact']['apointments'][0]['description'][0] = 'Remember to visit Alpha Framework Website!';
	$var['contact']['apointments'][0]['where'][0] 		= 'http://alpha-framework.com';
	$var['contact']['apointments'][0]['when'][0] 		= 'Mon 30 Jul 2012';
	
	$var['contact']['apointments'][1]['key'][0] 		= 'abc';
	$var['contact']['apointments'][1]['value'][0]	 	= 123;
	$var['contact']['apointments'][1]['flag'][0] 		= true;
	
	$aut->throwIfNot(
			$aut->equals(Model::$variables, $var),																						# Check
			'Complext association <code>$m->setVar("variable.subvariables", &lt;Mixed types&gt;)</code> has worked as expected', 		# Success
			'object $var is not equal to Model::$variables', 																			# Fail
	
			Model::$variables);																											# Arguments
	
	# Test #12
	$m->clearVar('simpleObject', false);
	unset($var['simpleObject']);
	
	$aut->throwIfNot(
			$aut->equals(Model::$variables, $var),																						# Check
			'Method <code>clearVar</code> in fullMatch has worked as expected', 														# Success
			'Method <code>clearVar</code> in fullMatch has not worked as expected', 													# Fail
	
			Model::$variables);																											# Arguments
	
	# Test #13
	$m->clearVar('contact.apointments.1', true);
	unset($var['contact']['apointments'][1]);
	
	$aut->throwIf(
			$aut->equals(Model::$variables, $var),																						# Check
			'Method <code>clearVar</code> in exactMatch has worked as expected', 														# Success
			'Method <code>clearVar</code> in exactMatch has not worked as expected', 													# Fail
	
			Model::$variables);																											# Arguments
	
	# Test #14
	$data = $m->getVar('exampleArray', null, true);
	$aut->throwIfNot(
			$aut->equals($data, $exampleArray), 
			'getVar successed on iterable variable', 
			'getVar failed on iterable variable');
	
	
	echo("<dl>");
	echo("<dt>Tests Passed:</dt><dd class=\"success\">" . $aut->passed() . "</dd>");
	echo("<dt>Tests Failed:</dt><dd class=\"error\">" . $aut->failed() . "</dd>");
	echo("</dl>");
	
	
	echo("</body>");
	echo("</html>");
	
}
Model_V2_0_UnitTest();
