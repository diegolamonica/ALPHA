<?php
/**
 * @name DIMDIM Integration Class
 * @package IWA
 * @subpackage Classes
 * @author Diego La Monica
 * @version 1.0
 * @desc Classe che consente un integrazione dell'applicazione con DIMDIM
 * @see Default page
 */

/**
 * 
 */
define('DIMDIM_HOST_URL' , 		'webmeeting.dimdim.com');
define('DIMDIM_BASE_URL' , 		'http://' . DIMDIM_HOST_URL . '/portal/');
define('DIMDIM_RETURN_URL',		'http://www.yourdomain.com/thanks/');
define('DIMDIM_AUTH_USER_NAME',	'iwaitaly1');
define('DIMDIM_AUTH_PASS_CODE',	'dimdim');

define('DIMDIM_ACTION_SCHEDULE_MEETING', 			'schedule.action');
define('DIMDIM_ACTION_JOIN_MEETING',				'join.action');

define('DIMDIM_URL_KEY_NAME',						'name');
define('DIMDIM_URL_KEY_PASSWORD',					'password');
define('DIMDIM_URL_KEY_START_DATE',					'startDate');
/**
 * Definisce la ripetitivit� del seminario secondo i valori specificati nelle costanti DIMDIM_RECURRANCE_*
 *
 */
define('DIMDIM_URL_KEY_MEETING_RECURRANCE',			'meetingRecurrance');
define('DIMDIM_URL_KEY_START_HOUR',					'startHour');
define('DIMDIM_URL_KEY_START_MINUTE',				'startMinute');
define('DIMDIM_URL_KEY_TIME_AMPM',					'timeAMPM');
define('DIMDIM_URL_KEY_WAITING_AREA',				'waitingarea');
define('DIMDIM_URL_KEY_NETWORK',					'network');
define('DIMDIM_URL_KEY_HOURS',						'hours');
define('DIMDIM_URL_KEY_MINUTES',					'minutes');
define('DIMDIM_URL_KEY_NUM_PARTICIPANTS',			'participants');
define('DIMDIM_URL_KEY_NUMBER_MIKES',				'mikes');
define('DIMDIM_URL_KEY_RETURN_URL',					'returnurl');
define('DIMDIM_URL_KEY_PRESENTER_NAME',				'displayname');
define('DIMDIM_URL_KEY_CONFERENCE_NAME',			'confname');
define('DIMDIM_URL_KEY_LOCAL_DIALIN_NUMBER',		'toll');
define('DIMDIM_URL_KEY_LOCAL_DIALIN_FREE_NUMBER',	'tollFree');
define('DIMDIM_URL_KEY_MODERATOR_PASS_CODE',		'moderatorPassCode');
#define('DIMDIM_URL_KEY_ATTENDEE_PASS_CODE',		'attendeePasscode');
define('DIMDIM_URL_KEY_PRESENTER_PASS_CODE',		'preseterPwd');
define('DIMDIM_URL_KEY_ATTENDEE_PASS_CODE',			'attendeePwd');
define('DIMDIM_URL_KEY_PARTICIPANT_LIST_ENABLED',	'participantListEnabled');
define('DIMDIM_URL_KEY_FEATURE_PRIVATE_CHAT',		'featurePrivateChat');
define('DIMDIM_URL_KEY_FEATURE_PUBLIC_CHAT',		'featurePublicChat');
define('DIMDIM_URL_KEY_FEATURE_PUBLISHER',			'featurePublisher');
define('DIMDIM_URL_KEY_FEATURE_WHITEBOARD',			'featureWhiteboard');
define('DIMDIM_URL_KEY_CONFERENCE_KEY',				'confkey');
/**
 * Consente di specificare uno dei valori indicati nelle costanti DIMDIM_AUDIOVIDEO_*
 */
define('DIMDIM_URL_KEY_AUDIO_VIDEO',				'audioVideo');
define('DIMDIM_URL_KEY_ATTENDEES',					'attendees');
define('DIMDIM_URL_KEY_TIMEZONE',					'timezone');
define('DIMDIM_URL_KEY_AGENDA',						'agenda');
/** * Email a cui devono arrivare i feedback
 *
 */
define('DIMDIM_URL_KEY_FEEDBACK',					'feedback');
/**
 * Prevede come valore: json o portal il valore viene sovrascritto dal metodo doAction impostando il valore al response di default 
 */
define('DIMDIM_URL_KEY_RESPONSE',					'response');

define('DIMDIM_DEFAULT_RESPONSE',					'json');

/* ********************************************************
 * 
 * COSTANTI PER IL SETTAGGIO AUDIO/VIDEO
 * 
 ******************************************************* */
define('DIMDIM_AUDIOVIDEO_AUDIO_ALLOWED'			,'A');
define('DIMDIM_AUDIOVIDEO_VIDEO_CHAT_ALLOWED'		,'Z');
define('DIMDIM_AUDIOVIDEO_AUDIO_VIDEO_DISABLED'		,'D');
define('DIMDIM_AUDIOVIDEO_AUDIO_VIDEO_ALLOWED'		,'V');

/* ********************************************************
 * 
 * COSTANTI DI RICORRENZA
 * 
 ******************************************************* */
/**
 * Nessuna ricorrenza dell'evento
 */
define('DIMDIM_RECURRANCE_SINGLE_EVENT',			'SINGLE EVENT');
/**
 * Ogni giorno
 */
define('DIMDIM_RECURRANCE_DAILY',					'DAILY');
/**
 * Ogni settimana
 */
define('DIMDIM_RECURRANCE_WEEKLY',					'WEEKLY');
/**
 * Ogni mese
 */
define('DIMDIM_RECURRANCE_MONTHLY',					'MON_DATE');

class Dimdim{
	
	private $parameters = array();
	
	public function setVar($key, $value){
		$this->parameters[$key] = $value;
	}
	
	public function Dimdim(){
		$this->resetVars();
	}
	
	public function resetVars(){
		$this->parameters = array();
		$this->parameters[DIMDIM_URL_KEY_NAME]		= DIMDIM_AUTH_USER_NAME;
		$this->parameters[DIMDIM_URL_KEY_PASSWORD]	= DIMDIM_AUTH_PASS_CODE;
		$this->parameters[DIMDIM_URL_KEY_FEEDBACK] 	= DIMDIM_DEFAULT_RESPONSE;
	}
	
	public function doAction($action){
		
		#print_r($this->parameters);
		$jsonResult = do_post_request(DIMDIM_BASE_URL . $action, $this->parameters );
		// ho la risposta in result
		$j = ClassFactory::get('Json');
		return $j->toObject($jsonResult);
		
	}

	
	
	public function getJoinUrl($confKey, $attendeePassword='', $presenterPassword=''){
		# http://hostname:port/portal/join.action?confkey=demoRoom&attendeePwd=demoKey&response=json
		
		$params = array(
			DIMDIM_URL_KEY_CONFERENCE_KEY =>DIMDIM_AUTH_USER_NAME,
			DIMDIM_URL_KEY_ATTENDEE_PASS_CODE=> $attendeePassword,
			DIMDIM_URL_KEY_PRESENTER_PASS_CODE=> $presenterPassword
		);
		
		$url = getUrl(DIMDIM_BASE_URL . DIMDIM_ACTION_JOIN_MEETING, $params, true);
		return $url;  
	}
	
	/**
	 * Trasforma una data nel formato yyyy-mm-dd, nel formato richiesto da dimdim
	 *
	 * @param string(yyyy-mm-dd) $date
	 * @return string
	 */
	public function parseDate($date){
		$months = explode(",",',January,February,March,April,May,June,July,August,September,October,November,December');
		list($year, $month, $day) = split('-',$date);
		
		$dateParsed = $months[$month] . ' ' . $day .', ' . $year;
		
		return $dateParsed;
	}
	
}
?>