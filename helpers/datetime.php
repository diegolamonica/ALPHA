<?php
class HelperDateTime{
	
	private static $echoTimeIndex = null;
	private static $lastMicrotime = null;
	
	
	
	/**
	*
	* Check if the given date is valid
	* @param string $date
	*
	* @param string $separator
	*
	* @param string $format defines the order which year month and day are in the $date string
	* this string supports "d" for days "m" for month and "y" for year
	*
	* @param int|bool $lower2DigitsDate (optional default 1950) if the given date is
	* defined as 2 digits value it will be adjusted
	* according to this value if the parameter is false
	* the correction will not be performed
	*
	* @example $date = '2012-08-30'; $validDate = isValidDate($date, '-', 'ymd');
	* @example $date = '30/08/2012'; $validDate = isValidDate($date, '/', 'dmy');
	* @example $date = '08/30/2012'; $validDate = isValidDate($date, '/', 'mdy');
	* @example $date = '08/30/12'; $validDate = isValidDate($date, '/', 'mdy');
	*
	* @return boolean
	*/
	public static function isValidDate($date, $separator = '/', $format = 'dmy', $lower2DigitsDate = 1950){
		/*
		* Detect the Datetime structure
		*/
		$yearIndex = strpos($format, 'y');
		$monthIndex = strpos($format, 'm');
		$dayIndex = strpos($format, 'd');
		
		/*
		* split the string date into an array of three elements according to given separator
		*/
		$dateElements = preg_split('#'. preg_quote($separator, '#') . '#', $date);
		/*
		* Check for EXACT 3 parts in the date
		*/
		if(count($dateElements)!=3) return false;
		
		$day = intval($dateElements[$dayIndex]);
		$month = intval($dateElements[$monthIndex]);
		$year = intval($dateElements[$yearIndex]);
		
		/*
		* Convert 2 digits year in 4 digits year
		*/
		if($year<100 && $lower2DigitsDate !==false){
			$marker = ($lower2DigitsDate%100);
			if($year<$marker) $year += 100;
			$year = $lower2DigitsDate-$marker + $year;
			
			$dateElements[$yearIndex] = $year;
		}
		
		
		$monthDays = array( 0, 31,	28, 31, 30, 31, 30, 31, 31, 30, 31, 30,31);
		
		/**
		* Exception on introduction of Gregorian Calendar
		* @see http://diegolamonica.info/howto-detect-if-typed-date-is-valid/#comment-5426
		*/
		if($year==1582 && $month==10 && $day>4 && $day<15) return false;
		
		/**
		* Feb has 29 days in the leap years
		* @see http://en.wikipedia.org/wiki/Leap_year#Algorithm
		*/
		$leapYear = ($year % 4 == 0 && ($year % 100 != 0 || $year % 400 == 0) );
		
		if($leapYear) $monthDays[2] = 29;
		
		// Output the adjusted date (with 4 digits year)
		$date = implode($separator, $dateElements);
		
		/*
		* Check if the day of the month is into the range
		*/
		return ($monthDays[$month] >= $day && $day>0);	
		
	}
	
		
	
	static public function formatDateTime($dateTime){
		$dateTime = str_replace('00:00:00', '', $dateTime);
		$dateTime = trim($dateTime);
		if(strpos($dateTime, ' ') !== false ){
			$valori = preg_split(" ", $dateTime);
			$valori[0] = formattaDateTime( $valori[0] );
			$dateTime = $valori[0] . ' ' . $valori[1];
		}else{
			$isDate = ( strlen( $dateTime ) == 10 );			// L'orario può essere scritto nel formato 00.00.00 oppure
			if($isDate){
				$valori = preg_split("/-/", $dateTime);
				if(count($valori)==3){
					$dateTime = $valori[2] . "/" . $valori[1] . "/" . $valori[0];
				}
			}
		}
		return $valore;
	}
	
	
	
	/**
	 * @uses HelperDateTime::$echoTimeIndex
	 * @uses HelperDateTime::$lastMicrotime
	 * Echoes the delay since the laste echoTime invocation.
	 */
	static public function echoTime(){
		
		if(is_null(self::$echoTimeIndex)) self::$echoTimeIndex = 1;
		$currentMT =time();
		if(is_null(self::$lastMicrotime)) self::$lastMicrotime = $currentMT;
		
		echo( self::$echoTimeIndex++ . ' - ' . $currentMT . ' &delta; '. ($currentMT-self::$lastMicrotime) . '<br />');
	}
	
	
	/**
	 * Add a specific time quantity to the given date
	 * @param string $interval is the interval, 
	 * 			allowed values are 
	 * 				- 'year' or 'yyyy' for the year 
	 * 				- 'q' for a period
	 * 				- 'month' or 'm' for the month
	 * 				- 'day', 'y','d' or 'w' for the day
	 * 				- 'week', 'ww' or 'W' for the week
	 * 				- 'hour' or 'h' for the hour
	 * 				- 'minute' or 'n' for the minute
	 * 				- 'second' or 's' for second
	 * @param integer $number is the quantity to increase the date. Negative numbers are accepted too.  
	 * @param string $date date time string in the "Y-m-d H:i:s" format. 
	 * @return string The new date in the "Y-m-d H:i:s" format
	 */
	static public function dateAdd($interval, $number, $date) {

		$date_time_array = preg_split('/[^0-9]+/', $date);
    
		$seconds = $date_time_array[5];
		$minutes = $date_time_array[4];
		$hours = $date_time_array[3];
		$day = $date_time_array[2];
		$month = $date_time_array[1];
		$year = $date_time_array[0];
    
		switch ($interval) {
			case "year": 
			case "yyyy":
				$year+=$number;
				break;
			case "q":
				$year+=($number*3);
				break;
			case "month":
			case "m":
				$month+=$number;
				break;
			case "day":
			case "y":
			case "d":
			case "w":
				$day+=$number;
				break;
			case "week":
			case "ww":
			case 'W':
				$day+=($number*7);
				break;
			case "hour":
			case "h":
				$hours+=$number;
				break;
			case "minute":
			case "n":
				$minutes+=$number;
				break;
			case "second":
			case "s":
				$seconds+=$number; 
				break;            
		}
		$timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
		return $timestamp;
	}	
}

