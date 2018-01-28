<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class TestXss
{
	const DEFAULT_INJECTION = 'GPCHF';
	const DEFAULT_NAME_INJECTION = 'GPCH';
	const DEFAULT_PAYLOAD = '\'"><';
	const MAX_CHILD = 50;
	const DEFAULT_MAX_CHILD = 5;
	const VERBOSE_LEVEL = [0,1,2,3];
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
	private $payload_prefix = null;
	private $payload_suffix = null;
	
	/**
	 * replace the value of the parameter by the payload instead of concatenate at the end
	 *
	 * @var mixed
	 */
	private $replace_mode = null;
	
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
	 * enable phantomjs
	 * 
	 */
	private $phantom = null;
	
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
	 * stop on success level
	 *
	 * @var boolean
	 */
	private $stop_on_success = false;
	
	/**
	 * daemon stuff
	 *
	 * @var mixed
	 */
	private $n_child = 0;
	private $max_child = self::DEFAULT_MAX_CHILD;
	private $loop_sleep = 10000;
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

	
	public function stopOnSuccess() {
		$this->stop_on_success = true;
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

	
	public function getReplaceMode() {
		return $this->replace_mode;
	}
	public function setReplaceMode( $v ) {
		$v = strtoupper( trim($v) );
		$v = preg_replace( '#[^'.self::DEFAULT_INJECTION.']#', '', $v );
		//if( $v != '' )
		{
			$this->replace_mode = $v;
		}
		return true;
	}
	
	
	public function getCookies() {
		return $this->cookies;
	}
	public function setCookies( $v ) {
		$this->cookies = trim( $v );
		return true;
	}

	
	public function enablePhantom( $v ) {
		$this->phantom = trim( $v );
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
		if( is_null($this->payload_prefix) ) {
			$this->payload_prefix = substr( $uniqid, 0, 6 );
		}
		if( is_null($this->payload_suffix) ) {
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
		
		$t_sockets = [];
		$t_vulnerable = [];
		
		posix_setsid();
		declare( ticks=1 );
		pcntl_signal( SIGCHLD, array($this,'signal_handler') );
		
		for( $pindex=0 ; $pindex<$this->n_payload ; $pindex++ )
		{
			for( $rindex=0 ; $rindex<$this->n_request ; )
			{
				if( $this->stop_on_success && in_array($rindex,$t_vulnerable)!==false ) {
					$rindex++;
					continue;
				}
				
				if( $this->n_child < $this->max_child )
				{
					$t_sockets[] = $s = stream_socket_pair( STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP );
					stream_set_blocking ( $s[0], false );
					stream_set_blocking ( $s[1], false );
					
					$pid = pcntl_fork();

					if( $pid == -1 ) {
						// fork error
						//fclose( $sockets[0] );
						//fclose( $sockets[1] );
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
						//fclose( $sockets[1] );
						usleep( $this->loop_sleep );
						$xss = $this->testPayload( $rindex, $pindex );
						if( $xss ) {
							stream_socket_sendto( $s[0], $rindex );
						}
						exit( 0 );
					}
				}

				foreach( $t_sockets as $s ) {
					$msg = stream_socket_recvfrom( $s[1], 32 );
					if( strlen($msg) ) {
						$t_vulnerable[] = (int)$msg;
					}
				}
				
				usleep( 2000000 );
			}
		}
		
		while( $this->n_child ) {
			// surely leave the loop please :)
			usleep( 100000 );
		}
		
		return true;
	}

	
	private function testPayload( $rindex, $pindex )
	{
		ob_start();
		
		$xss = 0;
		
		$reference = $this->t_request[$rindex];
		$reference->setSsl( $this->ssl );
		$reference->setRedirect( $this->redirect );
		$reference->setContentLength( $this->force_cl );
		if( $this->cookies ) {
			$reference->setCookies( $this->cookies );
		}
		
		// perform tests on FRAGMENT
		if( strstr($this->injection,'F') && !$this->no_test ) {
			$xss += $this->testFragment( $reference, $pindex );
		}
		
		if( !$xss || !$this->stop_on_success )
		{
			// perform tests on GET parameters
			if( strstr($this->injection,'G') ) {
				$xss += $this->testGet( $reference, $pindex );
			}

			if( !$xss || !$this->stop_on_success )
			{
				// perform tests on POST parameters
				if( strstr($this->injection,'P') ) {
					$xss += $this->testPost( $reference, $pindex );
				}
				
				if( !$xss || !$this->stop_on_success )
				{
					// perform tests on COOKIES
					if( strstr($this->injection,'C') && !$this->no_test ) {
						$xss += $this->testCookies( $reference, $pindex );
					}
		
					if( !$xss || !$this->stop_on_success )
					{
						// perform tests on HEADERS
						if( strstr($this->injection,'H') && !$this->no_test ) {
							$xss += $this->testHeaders( $reference, $pindex );
						}
					}
				}
			}
		}
		
		$display = ob_get_contents();
		ob_end_clean();
		
		if( !$this->no_test ) {
			if( $this->verbose < 2 || $xss ) {
				echo "Request ".($rindex+1)."/".$this->n_request." -> ";
				Utils::_print( $reference->getFullUrl(), 'light_purple' );
				echo "\n";

				echo str_pad(' ',4)."Payload ".($pindex+1)."/".$this->n_payload." -> injection: ";
				Utils::_print( $this->t_payload_original[$pindex], 'yellow' );
				echo " / wish: ";
				Utils::_print( $this->t_payload_wanted[$pindex], 'yellow' );
				echo "\n";
			}
		}
		
		echo $display;
		
		return $xss;
	}
	

	private function testFragment( $reference, $pindex )
	{
		// perform tests on FRAGMENT value
		$xss = 0;
		$payload = $this->t_payload[$pindex];
		$pvalue = $reference->getFragment();
		
		$r = clone $reference;
		if( strstr($this->replace_mode,'F') ) {
			$new_pvalue = $payload;
		} else {
			$new_pvalue = $pvalue.$payload;
		}
		
		$r->setFragment( $new_pvalue );
		
		if( $this->no_test ) {
			echo $r->getFullUrl()."\n";
		} else {
			$this->request( $r );
			$xss += $this->result( $r, 0, '#', $new_pvalue, 'FRAGMENT' );
		}
		
		unset( $r );

		return $xss;
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
			
			// perform tests on GET parameters values
			$r = clone $reference;
			if( strstr($this->replace_mode,'G') ) {
				$new_pvalue = $payload;
			} else {
				$new_pvalue = $pvalue.$payload;
			}
			$r->setGetParam( $new_pvalue, $pname );
			if( $this->no_test ) {
				echo $r->getFullUrl()."\n";
			} else {
				$this->request( $r );
				$xss += $this->result( $r, $pindex, $pname, $new_pvalue, 'GET parameter' );
			}
			unset( $r );

			if( $xss && $this->stop_on_success ) {
				return $xss;
			}
			
			// transform GET parameters to POST
			if( $this->gpg && !$this->no_test ) {
				$r = clone $reference;
				if( strstr($this->replace_mode,'G') ) {
					$new_pvalue = $payload;
				} else {
					$new_pvalue = $pvalue.$payload;
				}
				$r->setGetParam( $new_pvalue, $pname );
				$r->setPostParams( array_merge($r->getGetTable(),$r->getPostTable()) );
				$r->setGetParams( '' );
				if( $r->getMethod() == HttpRequest::METHOD_GET ) {
					$r->setMethod( HttpRequest::METHOD_POST );
				}
				$this->request( $r );
				$xss += $this->result( $r, $pindex, $pname, $new_pvalue, 'GET->POST parameter' );
				unset( $r );
				
				if( $xss && $this->stop_on_success ) {
					return $xss;
				}
			}
			
			// perform tests on GET parameters names
			if( strstr($this->name_injection,'G') )
			{
				$r = clone $reference;
				$v = $r->getGetParam( $pname );
				$r->unsetGetParam( $pname );
				$r->setGetParam( $pvalue, $pname.$payload );
				if( $this->no_test ) {
					echo $r->getFullUrl()."\n";
				} else {
					$this->request( $r );
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'GET parameter (name)' );
				}
				unset( $r );

				if( $xss && $this->stop_on_success ) {
					return $xss;
				}
				
				// transform GET parameters to POST
				if( $this->gpg && !$this->no_test ) {
					$r = clone $reference;
					$v = $r->getGetParam( $pname );
					$r->unsetGetParam( $pname );
					$r->setGetParam( $pvalue, $pname.$payload );
					$r->setPostParams( array_merge($r->getGetTable(),$r->getPostTable()) );
					$r->setGetParams( '' );
					if( $r->getMethod() == HttpRequest::METHOD_GET ) {
						$r->setMethod( HttpRequest::METHOD_POST );
					}
					$this->request( $r );
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'GET->POST parameter (name)' );
					unset( $r );
				}
				
				if( $xss && $this->stop_on_success ) {
					return $xss;
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
				$r = clone $reference;
				if( strstr($this->replace_mode,'P') ) {
					$new_pvalue = $payload;
				} else {
					$new_pvalue = $pvalue.$payload;
				}
				$r->setPostParam( $new_pvalue, $pname );
				$this->request( $r );
				$xss += $this->result( $r, $pindex, $pname, $new_pvalue, 'POST parameter' );
				unset( $r );
				
				if( $xss && $this->stop_on_success ) {
					return $xss;
				}
			}

			// transform POST parameters to GET
			if( $this->gpg ) {
				$r = clone $reference;
				if( strstr($this->replace_mode,'P') ) {
					$new_pvalue = $payload;
				} else {
					$new_pvalue = $pvalue.$payload;
				}
				$r->setPostParam( $new_pvalue, $pname );
				$r->setGetParams( array_merge($r->getPostTable(),$r->getGetTable()) );
				$r->setPostParams( '' );
				if( $r->getMethod() == HttpRequest::METHOD_POST ) {
					$r->setMethod( HttpRequest::METHOD_GET );
				}
				if( $this->no_test ) {
					echo $r->getFullUrl()."\n";
				} else {
					$this->request( $r );
					$xss += $this->result( $r, $pindex, $pname, $new_pvalue, 'POST->GET parameter' );
				}
				unset( $r );
				
				if( $xss && $this->stop_on_success ) {
					return $xss;
				}
			}
			
			// perform tests on POST parameters names
			if( strstr($this->name_injection,'P') )
			{
				if( !$this->no_test ) { // no need to perform those tests if we we only want to display the urls
					$r = clone $reference;
					$v = $r->getPostParam( $pname );
					$r->unsetPostParam( $pname );
					$r->setPostParam( $pvalue, $pname.$payload );
					$this->request( $r );
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'POST parameter (name)' );
					unset( $r );

					if( $xss && $this->stop_on_success ) {
						return $xss;
					}
				}
				
				// transform POST parameters to GET
				if( $this->gpg ) {
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
						$this->request( $r );
					}
					$xss += $this->result( $r, $pindex, $pname, $pvalue, 'POST->GET parameter (name)' );
					unset( $r );
					
					if( $xss && $this->stop_on_success ) {
						return $xss;
					}
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
			$r = clone $reference;
			if( strstr($this->replace_mode,'C') ) {
				$new_pvalue = $payload;
			} else {
				$new_pvalue = $pvalue.$payload;
			}
			$r->setCookie( $new_pvalue, $pname );
			$this->request( $r );
			$xss += $this->result( $r, $pindex, $pname, $new_pvalue, 'Cookie' );
			unset( $r );

			if( $xss && $this->stop_on_success ) {
				return $xss;
			}
			
			// perform tests on COOKIES names
			if( strstr($this->name_injection,'C') )
			{
				$r = clone $reference;
				$v = $r->getCookie( $pname );
				$r->unsetCookie( $pname );
				$r->setCookie( $pvalue, $pname.$payload );
				$this->request( $r );
				$xss += $this->result( $r, $pindex, $pname, $pvalue, 'Cookie (name)' );
				unset( $r );
				
				if( $xss && $this->stop_on_success ) {
					return $xss;
				}
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
			$r = clone $reference;
			if( strstr($this->replace_mode,'H') ) {
				$r->setHeader( $payload, $pname );
				$new_pvalue = $payload;
			} else {
				$new_pvalue = $pvalue.$payload;
			}
			$r->setHeader( $new_pvalue, $pname );
			$this->request( $r );
			$xss += $this->result( $r, $pindex, $pname, $new_pvalue, 'Header' );
			unset( $r );

			if( $xss && $this->stop_on_success ) {
				return $xss;
			}

			// perform tests on HEADERS names
			if( strstr($this->name_injection,'H') )
			{
				$r = clone $reference;
				$v = $r->getHeader( $pname );
				$r->unsetHeader( $pname );
				$r->setHeader( $pvalue, $pname.$payload );
				$this->request( $r );
				$xss += $this->result( $r, $pindex, $pname, $pvalue, 'Header (name)' );
				unset( $r );

				if( $xss && $this->stop_on_success ) {
					return $xss;
				}
			}
		}
		
		return $xss;
	}
	
	
	private function request( $r )
	{
		if( !$this->phantom ) {
			$r->request();
			return;
		}
		
		$t_args = [];
		$t_args['METHOD'] = $r->getMethod();
		$t_args['URL'] = $r->getFullUrl();
		
		$t_args['POST'] = strlen($r->getPostParams()) ? $r->getPostParams() : '';
		
		$c = $this->phantom.' '.dirname(__FILE__).'/phantom-xss.js';
		
		$cookies = $r->getCookies();
		if( strlen($cookies) ) {
			$t_args['COOKIES'] = $cookies;
			$t_args['DOMAIN'] = Utils::extractDomain( $r->getHost() );
		} else {
			$t_args['COOKIES'] = $t_args['DOMAIN'] = '';
		}
		//var_dump( $t_args );
		
		$c = $this->phantom.' '.dirname(__FILE__).'/phantom-xss.js '.implode( ' ', array_map(function($v){return '"'.trim($v).'"';},$t_args) );
		//echo $c."\n";
		$c = $this->phantom.' '.dirname(__FILE__).'/phantom-xss.js '.implode( ' ', array_map(function($v){return '"'.base64_encode($v).'"';},$t_args) );
		//echo $c."\n";
		//exit();
		
		ob_start();
		system( $c );
		$this->phantom_output = ob_get_contents();
		ob_end_clean();
		
		//var_dump( $this->phantom_output );
	}
	
	
	private function result_phantom( $r, $pindex, $param_name, $param_value, $param_type )
	{
		$xss = 0;
		$m = preg_match( '/\(\) called/', $this->phantom_output );
		
		if( $m ) {
			$xss = true;
		}
		
		if( $xss ) {
			echo str_pad( ' ', 8 );
			echo $param_type." '".$param_name."' seems to be ";
			Utils::_print( 'VULNERABLE', 'red' );
			echo ' with value: ';
			Utils::_print( $param_value, 'light_cyan' );
			//Utils::_print( $param_value.$this->t_payload[$pindex], 'light_cyan' );
			echo "\n";
		} elseif( $this->verbose < 2 ) {
			echo str_pad( ' ', 8 );
			echo $param_type." '".$param_name."' seems to be ";
			Utils::_print( 'SAFE', 'green' );
			echo "\n";
		}
		/*
		if( $this->verbose == 0 || ($xss && $this->verbose == 3) )
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
		*/
		return (int)$xss;
	}
	
	
	private function result( $r, $pindex, $param_name, $param_value, $param_type )
	{
		if( $this->phantom ) {
			return $this->result_phantom( $r, $pindex, $param_name, $param_value, $param_type );
		}
		
		$xss = false;
		$render = '';
		$rr = $r->getResultBody();
		//$rr = $r->getResult();
		//var_dump($rr);
		//exit();
		$regexp = '#('.$this->payload_prefix.'(.*?)'.$this->payload_suffix.')#';
		$m = preg_match_all( $regexp, $rr, $matches );
		//var_dump( $m );
		//var_dump( $matches );
		//var_dump( $this->t_payload_wanted[$pindex] );
		
		if( $m ) {
			foreach( $matches[1] as $m ) {
				if( $m == $this->t_payload_wanted[$pindex] ) {
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
			Utils::_print( $param_value, 'light_cyan' );
			//Utils::_print( $param_value.$this->t_payload[$pindex], 'light_cyan' );
			echo "\n";
		} elseif( $this->verbose < 2 ) {
			echo str_pad( ' ', 8 );
			echo $param_type." '".$param_name."' seems to be ";
			Utils::_print( 'SAFE', 'green' );
			echo "\n";
		}
		
		if( $this->verbose == 0 || ($xss && $this->verbose == 3) )
		{
			$str = str_pad( ' ', 8 );
			$str .= "C=".$r->result_code;
			$str .= ", L=".$r->result_body_size;
			$str .= ", ".$r->result_type.", ";
			if( $render == '' ) {
				$str .= 'empty';
			} elseif( $this->payload_prefix!='' && $this->payload_suffix!='' ) {
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
		$pid = (int)$pid;
		
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
