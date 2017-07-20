<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class TestXss
{
	const DEFAULT_INJECTION = 'GPCH';
	const DEFAULT_NAME_INJECTION = 'GPCH';
	const DEFAULT_PAYLOAD = '\'"><';
	const MAX_CHILD = 50;
	const DEFAULT_MAX_CHILD = 5;
	const VERBOSE_LEVEL = [0,1,2];
	const DEFAULT_VERBOSE = 0;

	/**
	 * @var string
	 *
	 * injection point
	 */
	private $injection = self::DEFAULT_INJECTION;

	/**
	 * @var string
	 *
	 * inject in parameters name
	 */
	private $name_injection = '';

	/**
	 * @var array
	 *
	 * array of request to test
	 */
	private $n_request = 0;
	private $t_request = array();

	/**
	 * @var string
	 *
	 * random string
	 */
	private $n_payload = 0;
	private $t_payload = [];
	private $t_payload_wanted = [];
	private $s_payload = null;
	private $payload_prefix = '';
	private $payload_suffix = '';
	
	/**
	 * test a specific param/cookie/header
	 *
	 * @var string
	 */
	private $specific_param = null;
	
	/**
	 * urlencode the payload or not
	 *
	 * @var boolean
	 */
	private $encode = false;

	/**
	 * single url to test
	 *
	 * @var array
	 */
	private $s_single = null;
	
	/**
	 * file list of urls to test
	 *
	 * @var array
	 */
	private $s_file = null;
	
	/**
	 * export file from burp suite
	 *
	 * @var string
	 */
	private $s_burp = null;
	
	/**
	 * request from file
	 *
	 * @var string
	 */
	private $s_request = null;
	
	/**
	 * get2post and post2get
	 * 
	 */
	private $gpg = false;
	
	/**
	 * perform test or not
	 * 
	 */
	private $no_test = false;
	
	/**
	 * force content length or not
	 *
	 * @var boolean
	 */
	private $force_cl = false;
	
	/**
	 * force https
	 * 
	 */
	private $ssl = false;
	
	/**
	 * force https
	 * 
	 */
	private $cookies = null;
	
	/**
	 * follow redirection or not
	 *
	 * @var boolean
	 */
	private $redirect = true;
	
	/**
	 * verbose level
	 *
	 * @var integer
	 */
	private $verbose = self::DEFAULT_VERBOSE;
	
	/**
	 * results
	 *
	 * @var integer
	 */
	private $total_injection = 0;
	private $total_success = 0;

	/**
	 * daemon stuff
	 *
	 * @var mixed
	 */
	private $n_child = 0;
	private $max_child = self::DEFAULT_MAX_CHILD;
	private $loop_sleep = 100000;
	private $t_process = [];
	private $t_signal_queue = [];

	
	public function getBurpSource() {
		return $this->s_burp;
	}
	public function setBurpSource( $v )
	{
		if( !is_file($v) ) {
			return false;
		}
		$this->s_single = null;
		$this->s_file = null;
		$this->s_request = null;
		$this->s_burp = $v;
		return true;
	}

	
	public function getGpg() {
		return $this->gpg;
	}
	public function enableGpg() {
		$this->gpg = true;
		return true;
	}

	
	public function getEncode() {
		return $this->encode;
	}
	public function enableEncode() {
		$this->encode = true;
		return true;
	}

	
	public function getContentLength() {
		return $this->force_cl;
	}
	public function forceContentLength() {
		$this->force_cl = true;
		return true;
	}

	
	public function getCookies() {
		return $this->cookies;
	}
	public function setCookies( $v ) {
		$this->cookies = trim( $v );
		return true;
	}


	public function getInjection() {
		return $this->injection;
	}
	public function setInjection( $v ) {
		$v = strtoupper( trim($v) );
		$v = preg_replace( '#[^'.self::DEFAULT_INJECTION.']#', '', $v );
		if( $v == '' ) {
			$v = self::DEFAULT_INJECTION;
		}
		$this->injection = $v;
		return true;
	}
	

	public function getNameInjection() {
		return $this->name_injection;
	}
	public function setNameInjection( $v ) {
		$v = strtoupper( trim($v) );
		$v = preg_replace( '#[^'.self::DEFAULT_NAME_INJECTION.']#', '', $v );
		$this->name_injection = $v;
		return true;
	}
	
	
	public function getRedirect() {
		return $this->redirect;
	}
	public function noRedirect() {
		$this->redirect = false;
		return true;
	}

	
	public function getNoTest() {
		return $this->no_test;
	}
	public function noTest() {
		$this->no_test = true;
		return true;
	}


	public function getMaxChild() {
		return $this->max_child;
	}
	public function setMaxChild( $v ) {
		$v = (int)$v;
		if( $v < 0 ) {
			$this->max_child = 1;
		} else if( $v > self::MAX_CHILD ) {
			$this->max_child = self::MAX_CHILD;
		} else {
			$this->max_child = $v;
		}
		return true;
	}


	public function getVerbose() {
		return $this->verbose;
	}
	public function setVerbose( $v ) {
		$v = (int)$v;
		if( in_array($v,self::VERBOSE_LEVEL) ) {
			$this->verbose = $v;
		}
		return true;
	}


	public function getPayloads() {
		return $this->s_payload;
	}
	public function setPayload( $v ) {
		$this->s_payload = trim( $v );
		return true;
	}

	
	public function getPrefix() {
		return $this->payload_prefix;
	}
	public function setPrefix( $v ) {
		$this->payload_prefix = trim( $v );
		return true;
	}
	

	public function getSuffix() {
		return $this->payload_suffix;
	}
	public function setSuffix( $v ) {
		$this->payload_suffix = trim( $v );
		return true;
	}


	public function getRequest() {
		return $this->s_request;
	}
	public function setRequest( $v )
	{
		if( !is_file($v) ) {
			return false;
		}
		$this->s_single = null;
		$this->s_file = null;
		$this->s_burp = null;
		$this->s_request = trim( $v );
		return true;
	}
	
	
	public function getSsl() {
		return $this->ssl;
	}
	public function forceSsl() {
		$this->ssl = true;
		return true;
	}

	
	public function getSingle() {
		return $this->s_single;
	}
	public function setSingle( $v )
	{
		$this->s_single = trim( $v );
		$this->s_file = null;
		$this->s_burp = null;
		$this->s_request = null;
		return true;
	}

	
	public function getSourceFile() {
		return $this->s_file;
	}
	public function setSourceFile( $v )
	{
		if( !is_file($v) ) {
			return false;
			return true;
		}
		$this->s_single = null;
		$this->s_file = trim( $v );
		$this->s_burp = null;
		$this->s_request = null;
		return true;
	}

	
	public function getSpecificParam() {
		return $this->specific_param;
	}
	public function setSpecificParam( $v ) {
		$this->specific_param = trim( $v );
		return true;
	}
	

	public function loadPayload()
	{
		$uniqid = uniqid();
		if( !strlen($this->payload_prefix) ) {
			$this->payload_prefix = substr( $uniqid, 0, 6 );
		}
		if( !strlen($this->payload_suffix) ) {
			$this->payload_suffix = substr( $uniqid, -6 );
		}
		
		if( $this->s_payload ) {
			if( is_file($this->s_payload) ) {
				echo "Loading payloads from file '".$this->s_payload."'...\n";
				$this->t_payload = file( $this->s_payload, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
			} else {
				$this->t_payload = [$this->s_payload];
			}
		} else {
			$this->t_payload = [self::DEFAULT_PAYLOAD];
		}
		
		//if( strlen($this->payload_prefix) || strlen($this->payload_suffix) || $this->encode ) {
			foreach( $this->t_payload as &$p ) {
				if( $this->encode ) {
					$p = urlencode( urldecode($p) );
				}
				$this->t_payload_original[] = $p;
				$p = $this->payload_prefix . $p . $this->payload_suffix;
				$this->t_payload_wanted[] = urldecode( $p );
			}
		//}

		//var_dump($this->encode);
		//var_dump($this->t_payload);
		//var_dump($this->t_payload_original);
		//var_dump($this->t_payload_wanted);
		
		$this->n_payload = count( $this->t_payload );
		return $this->n_payload;
	}

	
	public function loadDatas()
	{
		if( $this->s_single )
		{
			echo "Loading single url from command line...\n";
			$r = new HttpRequest();
			$r->setUrl( $this->s_single );
			$this->t_request = [ $r ];
		}
		elseif( $this->s_file )
		{
			echo "Loading urls from file '".$this->s_file."'...\n";
			$t_urls = file( $this->s_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
			foreach( $t_urls as $u ) {
				$r = new HttpRequest();
				$r->setUrl( $u );
				$this->t_request[] = $r;
			}
		}
		elseif( $this->s_request )
		{
			echo "Loading single request from file '".$this->s_request."'...\n";
			$r = new HttpRequest();
			$a = $r->loadFile( $this->s_request );
			if( $a ) {
				$this->t_request[] = $r;
			}
		}
		elseif( $this->s_burp )
		{
			echo "Loading Burp export from file '".$this->s_burp."'...\n";
		}

		$this->n_request = count( $this->t_request );
		return $this->n_request;
	}
	
	
	public function run()
	{
		//echo "Testing ".$this->n_payload." payloads on ".$this->t_request." requests.\n\n";
		
		if( !$this->no_test ) {
			echo "Running ".$this->max_child." threads...\n\n";
		}
		
		posix_setsid();
		declare( ticks=1 );
		pcntl_signal( SIGCHLD, array($this,'signal_handler') );
		
		for( $rindex=0 ; $rindex<$this->n_request ; )
		{
			if( $this->n_child < $this->max_child )
			{
				$pid = pcntl_fork();
				
				if( $pid == -1 ) {
					// fork error
				} elseif( $pid ) {
					// father
					$this->n_child++;
					$rindex++;
					$this->t_process[$pid] = uniqid();
			        if( isset($this->t_signal_queue[$pid]) ){
			        	$this->signal_handler( SIGCHLD, $pid, $this->t_signal_queue[$pid] );
			        	unset( $this->t_signal_queue[$pid] );
			        }
				} else {
					// child process
					usleep( $this->loop_sleep );
					$this->testRequest( $rindex );
					exit( 0 );
				}
			}

			usleep( $this->loop_sleep );
		}
		
		while( $this->n_child ) {
			// surely leave the loop please :)
			sleep( 1 );
		}
		
		
		if( !$this->no_test ) {
			//echo $this->n_payload." payload(s) tested on ".$this->injection." of ".$this->n_request." request(s), so ".$this->total_injection." performed and ".$this->total_success." XSS found!\n";
			echo $this->n_payload." payload(s) tested on ".$this->injection." of ".$this->n_request." request(s)\n";
		}
		echo "\n";

		return $this->total_success;
	}

	
	private function testRequest( $rindex )
	{
		ob_start();

		$reference = $this->t_request[$rindex];
		$reference->setSsl( $this->ssl );
		$reference->setRedirect( $this->redirect );
		$reference->setContentLength( $this->force_cl );
		if( $this->cookies ) {
			$reference->setCookies( $this->cookies );
		}
		//var_dump( $reference );
		//$reference->export();
		//exit();
		
		if( !$this->no_test ) {
			echo "Request ".($rindex+1)."/".$this->n_request." -> ";
			Utils::_print( $reference->getUrl(), 'light_purple' );
			echo "\n";
		}
		
		for( $pindex=0 ; $pindex<$this->n_payload ; $pindex++ )
		{
			$xss = 0;
			ob_start();

			// perform tests on GET parameters
			if( strstr($this->injection,'G') ) {
				$xss += $this->testGet( $reference, $pindex );
			}

			// perform tests on POST parameters
			if( strstr($this->injection,'P') ) {
				$xss += $this->testPost( $reference, $pindex );
			}
			
			// perform tests on COOKIES
			if( strstr($this->injection,'C') && !$this->no_test ) {
				$xss += $this->testCookies( $reference, $pindex );
			}
			
			// perform tests on HEADERS
			if( strstr($this->injection,'H') && !$this->no_test ) {
				$xss += $this->testHeaders( $reference, $pindex );
			}
			
			$display = ob_get_contents();
			ob_end_clean();
			
			if( !$this->no_test ) {
				if( $this->verbose < 2 || $xss ) {
					echo str_pad(' ',4)."Payload ".($pindex+1)."/".$this->n_payload." -> injection: ";
					Utils::_print( $this->t_payload_original[$pindex], 'yellow' );
					echo " / wish: ";
					Utils::_print( $this->t_payload_wanted[$pindex], 'yellow' );
					echo "\n";
				}
			}
			
			echo $display;
			
			$this->total_success += $xss;
		}
		
		if( !$this->no_test ) {
			echo "\n";
		}
		
		$result = ob_get_contents();
		ob_end_clean();
		
		echo $result;
	}
	
	
	private function testGet( $reference, $pindex )
	{
		$xss = 0;
		$payload = $this->t_payload[$pindex];
		$t_params = $reference->getGetTable();
		$n_params = count( $t_params );
		
		foreach( $t_params as $pname=>$pvalue )
		{
			if( $this->specific_param && $pname!=$this->specific_param ) {
				continue;
			}
			
			// perform tests on POST parameters values
			$this->total_injection++;
			$r = clone $reference;
			$r->setGetParam( $pvalue.$payload, $pname );
			if( $this->no_test ) {
				echo $r->getFullUrl()."\n";
			} else {
				$r->request();
				$xss += $this->result( $r, $pindex, $pname, $pvalue, 'GET parameter' );
			}
			unset( $r );

			// transform GET parameters to POST
			if( $this->gpg && !$this->no_test ) {
				$this->total_injection++;
				$r = clone $reference;
				$r->setGetParam( $pvalue.$payload, $pname );
				$r->setPostParams( array_merge($r->getGetTable(),$r->getPostTable()) );
				$r->setGetParams( '' );
				if( $r->getMethod() == HttpRequest::METHOD_GET ) {
					$r->setMethod( HttpRequest::METHOD_POST );
				}
				$r->request();
				//$r->export();
				//exit();
				$xss += $this->result( $r, $pindex, $pname, $pvalue, 'GET->POST parameter' );
				unset( $r );
			}
			
			// perform tests on GET parameters names
			if( strstr($this->name_injection,'G') )
			{
				$this->total_injection++;
				$r = clone $reference;
				$v = $r->getGetParam( $pname );
				$r->unsetGetParam( $pname );
				$r->setGetParam( $pvalue, $pname.$payload );
				if( $this->no_test ) {
					echo $r->getFullUrl()."\n";
				} else {
					$r->request();
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'GET parameter (name)' );
				}
				unset( $r );
				
				// transform GET parameters to POST
				if( $this->gpg && !$this->no_test ) {
					$this->total_injection++;
					$r = clone $reference;
					$v = $r->getGetParam( $pname );
					$r->unsetGetParam( $pname );
					$r->setGetParam( $pvalue, $pname.$payload );
					$r->setPostParams( array_merge($r->getGetTable(),$r->getPostTable()) );
					$r->setGetParams( '' );
					if( $r->getMethod() == HttpRequest::METHOD_GET ) {
						$r->setMethod( HttpRequest::METHOD_POST );
					}
					$r->request();
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'GET->POST parameter (name)' );
					unset( $r );
				}
			}
		}
		
		return $xss;
	}

	
	private function testPost( $reference, $pindex )
	{
		$xss = 0;
		$payload = $this->t_payload[$pindex];
		$t_params = $reference->getPostTable();
		$n_params = count( $t_params );
		
		foreach( $t_params as $pname=>$pvalue )
		{
			if( $this->specific_param && $pname!=$this->specific_param ) {
				continue;
			}

			// perform tests on POST parameters values
			if( !$this->no_test ) { // no need to perform those tests if we we only want to display the urls
				$this->total_injection++;
				$r = clone $reference;
				$r->setPostParam( $pvalue.$payload, $pname );
				$r->request();
				$xss += $this->result( $r, $pindex, $pname, $pvalue, 'POST parameter' );
				unset( $r );
			}

			// transform POST parameters to GET
			if( $this->gpg ) {
				$this->total_injection++;
				$r = clone $reference;
				$r->setPostParam( $pvalue.$payload, $pname );
				$r->setGetParams( array_merge($r->getPostTable(),$r->getGetTable()) );
				$r->setPostParams( '' );
				if( $r->getMethod() == HttpRequest::METHOD_POST ) {
					$r->setMethod( HttpRequest::METHOD_GET );
				}
				if( $this->no_test ) {
					echo $r->getFullUrl()."\n";
				} else {
					$r->request();
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'POST->GET parameter' );
				}
				unset( $r );
			}
			
			// perform tests on POST parameters names
			if( strstr($this->name_injection,'P') )
			{
				if( !$this->no_test ) { // no need to perform those tests if we we only want to display the urls
					$this->total_injection++;
					$r = clone $reference;
					$v = $r->getPostParam( $pname );
					$r->unsetPostParam( $pname );
					$r->setPostParam( $pvalue, $pname.$payload );
					$r->request();
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'POST parameter (name)' );
					unset( $r );
				}
				
				// transform POST parameters to GET
				if( $this->gpg ) {
					$this->total_injection++;
					$r = clone $reference;
					$v = $r->getPostParam( $pname );
					$r->unsetPostParam( $pname );
					$r->setPostParam( $pvalue, $pname.$payload );
					$r->setGetParams( array_merge($r->getPostTable(),$r->getGetTable()) );
					$r->setPostParams( '' );
					if( $r->getMethod() == HttpRequest::METHOD_POST ) {
						$r->setMethod( HttpRequest::METHOD_GET );
					}
					if( $this->no_test ) {
						echo $r->getFullUrl()."\n";
					} else {
						$r->request();
					}
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'POST->GET parameter (name)' );
					unset( $r );
				}
			}
		}
		
		return $xss;
	}

	
	private function testCookies( $reference, $pindex )
	{
		$xss = 0;
		$payload = $this->t_payload[$pindex];
		$t_params = $reference->getCookieTable();
		$n_params = count( $t_params );
		
		foreach( $t_params as $pname=>$pvalue )
		{
			if( $this->specific_param && $pname!=$this->specific_param ) {
				continue;
			}
			
			// perform tests on COOKIES values
			$this->total_injection++;
			$r = clone $reference;
			$r->setCookie( $pvalue.$payload, $pname );
			$r->request();
			$xss += $this->result( $r, $pindex, $pname, $pvalue, 'Cookie' );
			unset( $r );
			
			// perform tests on COOKIES names
			if( strstr($this->name_injection,'C') )
			{
				$this->total_injection++;
				$r = clone $reference;
				$v = $r->getCookie( $pname );
				$r->unsetCookie( $pname );
				$r->setCookie( $pvalue, $pname.$payload );
				$r->request();
				$xss += $this->result( $r, $pindex, $pname, $pvalue, 'Cookie (name)' );
				unset( $r );
			}
		}
		
		return $xss;
	}
	
	
	private function testHeaders( $reference, $pindex )
	{
		$xss = 0;
		$payload = $this->t_payload[$pindex];
		$t_params = $reference->getHeaderTable();
		$n_params = count( $t_params );
		
		foreach( $t_params as $pname=>$pvalue )
		{
			if( $this->specific_param && $pname!=$this->specific_param ) {
				continue;
			}
			
			// perform tests on HEADERS values
			$this->total_injection++;
			$r = clone $reference;
			$r->setHeader( $pvalue.$payload, $pname );
			$r->request();
			$xss += $this->result( $r, $pindex, $pname, $pvalue, 'Header' );
			unset( $r );
			
			// perform tests on HEADERS names
			if( strstr($this->name_injection,'H') )
			{
				$this->total_injection++;
				$r = clone $reference;
				$v = $r->getHeader( $pname );
				$r->unsetHeader( $pname );
				$r->setHeader( $pvalue, $pname.$payload );
				$r->request();
				$xss += $this->result( $r, $pindex, $pname, $pvalue, 'Header (name)' );
				unset( $r );
			}
		}
		
		return $xss;
	}
	
	
	private function result( $r, $pindex, $param_name, $param_value, $param_type )
	{
		$xss = false;
		$render = '';
		$rr = $r->getResultBody();
		//var_dump($rr);
		//exit();
		$regexp = '#('.$this->payload_prefix.'(.*?)'.$this->payload_suffix.')#';
		$m = preg_match_all( $regexp, $rr, $matches );
		//var_dump( $m );
		//var_dump( $matches );
		
		if( $m ) {
			foreach( $matches as $m ) {
				if( $m[0] == $this->t_payload_wanted[$pindex] ) {
					$xss = true;
				}
			}
			$render = implode( ', ', $matches[0] );
			//$xss = true;
		}
		/*
		if( strstr($rr,$this->t_payload_wanted[$pindex]) ) {
			$xss = true;
		}
		*/
		
		if( $xss ) {
			echo str_pad( ' ', 8 );
			echo $param_type." '".$param_name."' seems to be ";
			Utils::_print( 'VULNERABLE', 'red' );
			echo ' with value: ';
			Utils::_print( $param_value.$this->t_payload[$pindex], 'light_cyan' );
			echo "\n";
		} elseif( $this->verbose < 2 ) {
			echo str_pad( ' ', 8 );
			echo $param_type." '".$param_name."' seems to be ";
			Utils::_print( 'SAFE', 'green' );
			echo "\n";
		}
		
		if( $this->verbose == 0 )
		{
			$str = str_pad( ' ', 8 );
			$str .= "C=".$r->result_code;
			$str .= ", L=".$r->result_body_size;
			$str .= ", ".$r->result_type.", ";
			if( $render == '' ) {
				$str .= 'empty';
			} else {
				$str .= $render;
			}
			$str .= "\n";
			
			Utils::_print( $str, 'light_grey' );
		}

		return (int)$xss;
	}


	// http://stackoverflow.com/questions/16238510/pcntl-fork-results-in-defunct-parent-process
	// Thousand Thanks!
	public function signal_handler( $signal, $pid=null, $status=null )
	{
		// If no pid is provided, Let's wait to figure out which child process ended
		if( !$pid ){
			$pid = pcntl_waitpid( -1, $status, WNOHANG );
		}
		
		// Get all exited children
		while( $pid > 0 )
		{
			if( $pid && isset($this->t_process[$pid]) ) {
				// I don't care about exit status right now.
				//  $exitCode = pcntl_wexitstatus($status);
				//  if($exitCode != 0){
				//      echo "$pid exited with status ".$exitCode."\n";
				//  }
				// Process is finished, so remove it from the list.
				$this->n_child--;
				unset( $this->t_process[$pid] );
			}
			elseif( $pid ) {
				// Job finished before the parent process could record it as launched.
				// Store it to handle when the parent process is ready
				$this->t_signal_queue[$pid] = $status;
			}
			
			$pid = pcntl_waitpid( -1, $status, WNOHANG );
		}
		
		return true;
	}
}
