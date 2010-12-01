<?php
class Paging extends Debugger {

	private $recordsPerPage = PAGING_DEFAULT_RECORDS_PER_PAGE;
	private $pagingObjectName = '';
	private $currentPage = 0;
	private $pagesCount = 0;
	public function Paging(){
		if(isset($_GET[PAGING_CURRENT_PAGE_VARIABLE_NAME])){
			$this->currentPage = $_GET[PAGING_CURRENT_PAGE_VARIABLE_NAME];
		}
	}
	
	public function setRecordsPerPage($numberOfRecords){
		$this->recordsPerPage = $numberOfRecords;
	}
	
	public function getRecordsPerPage(){
		return $this->recordsPerPage;
	}
	public function setPagingObjectName($objectName){
		$this->pagingObjectName = $objectName;
		if(!isset($_GET[$objectName.PAGING_CURRENT_PAGE_VARIABLE_NAME])){
			
			$this->currentPage = 0;
		}else{
			$this->currentPage = $_GET[$objectName.PAGING_CURRENT_PAGE_VARIABLE_NAME];
		}
	}
	
	public function updateCount($sql){
		$mt = microtime();
		$tmpSql = $sql;
		$c = ClassFactory::get('connector',true,'pagingConnector');
		$c->disablePagination();
		$c->query($tmpSql);
		$count = $c->getCount();
		ClassFactory::destroy('pagingConnector');
		# used new constant REQUESTED_URL instead of $_GET['__url'] to improove framework security.
		$uri = REQUESTED_URL;
		$queryStringStart = strpos($uri,'?');
		if($queryStringStart===false) $queryStringStart = strlen($uri);
		$uri = substr($uri,0, $queryStringStart) . '?'; 

		$params = '';
		$uri = APPLICATION_URL . $uri; #substr($uri,1);
		
		foreach($_GET as $key=>$value){
			/*
			 * Removed the check on __fn and __url key in the $_GET object because them no longer exists.
			 * 
			 */
			if($value!='' && 
				$key != $this->pagingObjectName .PAGING_CURRENT_PAGE_VARIABLE_NAME /* && 
				$key!='__url' &&
				$key!='__fn'*/){
				if($params!='') $params.='&amp;';
				$params .= urlencode($key) . '=' . urlencode($value);
			}
		}
		if($params!='') $params .= '&amp;';
		
		$this->pagesCount = intval($count/$this->recordsPerPage)+((($count%$this->recordsPerPage)!=0)?1:0);
		
		$pagingData= array();
		$uriParams = $uri . $params . $this->pagingObjectName.PAGING_CURRENT_PAGE_VARIABLE_NAME .'=';
		$isFirstPage = ($this->currentPage==0);
		$isLastPage = ($this->currentPage==$this->pagesCount-1);
		
		$pagingData[$this->pagingObjectName.PAGING_FIRST_PAGE_VARIABLE_NAME]= 		($isFirstPage?'#':($uriParams .'0'));
		$pagingData[$this->pagingObjectName.PAGING_PREVIOUS_PAGE_VARIABLE_NAME]=  	($isFirstPage?'#':($uriParams .($this->currentPage-1)));
		
		
		$pagingData[$this->pagingObjectName.PAGING_NEXT_PAGE_VARIABLE_NAME]=  ($isLastPage?'#': ($uriParams. ($this->currentPage+1)));
		$pagingData[$this->pagingObjectName.PAGING_LAST_PAGE_VARIABLE_NAME] = ($isLastPage?'#': ($uriParams. ($this->pagesCount-1)));

		$pagingData[$this->pagingObjectName.PAGING_CURRENT_PAGE_VARIABLE_NAME] = $this->currentPage+1;
		$pagingData[$this->pagingObjectName.PAGING_PAGE_COUNT_VARIABLE_NAME] = $this->pagesCount;
		
		$m = ClassFactory::get('Model');
		$m->setMultipleVar($pagingData,  'paging');
	}
	
	public function buildLimitClause(){
		$c = ClassFactory::get('connector');
		return $c->getLimitClause(($this->currentPage*$this->recordsPerPage), $this->recordsPerPage);
		
	}
}
?>