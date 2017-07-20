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
	const DEFAULT_MAX_CHILD = 5;

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
	private $t_request = array();

	/**
	 * @var string
	 *
	 * random string
	 */
	private $t_payload = [];
	private $s_payload = null;
	private $payload_prefix = '';
	private $payload_suffix = '';

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
	 * max child
	 * 
	 */
	private $threads = self::DEFAULT_MAX_CHILD;
	
	/**
	 * force https
	 * 
	 */
	private $ssl = false;
	
	/**
	 * follow redirection or not
	 *
	 * @var boolean
	 */
	private $redirect = true;


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

	
	public function getContentLength() {
		return $this->force_cl;
	}
	public function forceContentLength() {
		$this->force_cl = true;
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


	public function getThreads() {
		return $this->threads;
	}
	public function setThreads( $v ) {
		$v = (int)$v;
		if( $v ) {
			$this->threads = $v;
		}
		return true;
	}


	public function getPayloads() {
		return $this->s_payload;
	}
	public function setPayload( $v ) {
		if( is_file($v) ) {
			$this->s_payload = trim( $v );
			return true;
		} else {
			return false;
		}
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


	public function loadPayload()
	{
		if( $this->s_payload ) {
			echo "Loading payloads from file '".$this->s_payload."'...\n";
			$this->t_payload = file( $this->s_payload, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		} else {
			$this->t_payload = [self::DEFAULT_PAYLOAD];
		}
		
		if( strlen($this->payload_prefix) || strlen($this->payload_suffix) ) {
			foreach( $this->t_payload as &$p ) {
				$p = $this->payload_prefix . $p . $this->payload_suffix;
			}
		}

		return count($this->t_payload);
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

		return count( $this->t_request );
	}
	
	
	public function run()
	{
		$total_injection = 0;
		$total_success = 0;
		$n_request = count( $this->t_request );
		$n_payload = count( $this->t_payload );
		//echo "Testing ".$n_payload." payloads on ".$n_request." requests.\n\n";
		
		echo "Running ".$this->threads." threads...\n\n";
		
		for( $i=0 ; $i<$n_request ; $i++ )
		{
			$reference = $this->t_request[ $i ];
			$reference->setSsl( $this->ssl );
			$reference->setContentLength( $this->force_cl );
			//var_dump( $reference );
			//$reference->export();
			//exit();
			
			if( !$this->no_test ) {
				echo "Request ".($i+1)."/".$n_request." -> ";
				Utils::_print( $reference->getUrl(), 'light_purple' );
				echo "\n";
			}
			
			for( $j=0 ; $j<$n_payload ; $j++ )
			{
				$payload = $this->t_payload[$j];
				
				if( !$this->no_test ) {
					echo str_pad(' ',4)."Payload ".($j+1)."/".$n_payload." -> ";
					Utils::_print( $payload, 'yellow' );
					echo "\n";
				}
				
				// perform tests on GET parameters
				if( strstr($this->injection,'G') )
				{
					$t_params = $reference->getGetTable();
					$n_params = count( $t_params );
					
					foreach( $t_params as $pname=>$pvalue )
					{
						// perform tests on POST parameters values
						$total_injection++;
						$r = clone $reference;
						$r->setGetParam( $pvalue.$payload, $pname );
						if( $this->no_test ) {
							echo $r->getFullUrl()."\n";
						} else {
							$r->request();
							$total_success += $this->result( $r, $payload, $pname, $pvalue, 'GET parameter' );
						}
						unset( $r );
						
						// transform GET parameters to POST
						if( $this->gpg && !$this->no_test ) {
							$total_injection++;
							$r = clone $reference;
							$r->setGetParam( $pvalue.$payload, $pname );
							$r->setPostParams( array_merge($r->getGetTable(),$r->getPostTable()) );
							$r->setGetParams( '' );
							if( $r->getMethod() == HttpRequest::METHOD_GET ) {
								$r->setMethod( HttpRequest::METHOD_POST );
							}
							$r->request();
							$total_success += $this->result( $r, $payload, $pname, $pvalue, 'GET->POST parameter' );
							unset( $r );
						}
						
						// perform tests on GET parameters names
						if( strstr($this->name_injection,'G') )
						{
							$total_injection++;
							$r = clone $reference;
							$v = $r->getGetParam( $pname );
							$r->unsetGetParam( $pname );
							$r->setGetParam( $pvalue, $pname.$payload );
							if( $this->no_test ) {
								echo $r->getFullUrl()."\n";
							} else {
								$r->request();
								$total_success += $this->result( $r, $payload, $pname, $pvalue, 'GET parameter (name)' );
							}
							unset( $r );
							
							// transform GET parameters to POST
							if( $this->gpg && !$this->no_test ) {
								$total_injection++;
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
								$total_success += $this->result( $r, $payload, $pname, $pvalue, 'GET->POST parameter (name)' );
								unset( $r );
							}
						}
					}
				}
				
				// perform tests on POST parameters
				if( strstr($this->injection,'P') )
				{
					$t_params = $reference->getPostTable();
					$n_params = count( $t_params );
					
					foreach( $t_params as $pname=>$pvalue )
					{
						// perform tests on POST parameters values
						if( !$this->no_test ) { // no need to perform those tests if we we only want to display the urls
							$total_injection++;
							$r = clone $reference;
							$r->setPostParam( $pvalue.$payload, $pname );
							$r->request();
							$total_success += $this->result( $r, $payload, $pname, $pvalue, 'POST parameter' );
							unset( $r );
						}
						
						// transform POST parameters to GET
						if( $this->gpg ) {
							$total_injection++;
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
								$total_success += $this->result( $r, $payload, $pname, $pvalue, 'POST->GET parameter' );
							}
							unset( $r );
						}
						
						// perform tests on POST parameters names
						if( strstr($this->name_injection,'P') )
						{
							if( !$this->no_test ) { // no need to perform those tests if we we only want to display the urls
								$total_injection++;
								$r = clone $reference;
								$v = $r->getPostParam( $pname );
								$r->unsetPostParam( $pname );
								$r->setPostParam( $pvalue, $pname.$payload );
								$r->request();
								$total_success += $this->result( $r, $payload, $pname, $pvalue, 'POST parameter (name)' );
								unset( $r );
							}
							
							// transform POST parameters to GET
							if( $this->gpg ) {
								$total_injection++;
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
								$total_success += $this->result( $r, $payload, $pname, $pvalue, 'POST->GET parameter (name)' );
								unset( $r );
							}
						}
					}
				}
				
				// perform tests on COOKIES
				if( strstr($this->injection,'C') && !$this->no_test )
				{
					$t_params = $reference->getCookieTable();
					$n_params = count( $t_params );
					
					foreach( $t_params as $pname=>$pvalue )
					{
						// perform tests on COOKIES values
						$total_injection++;
						$r = clone $reference;
						$r->setCookie( $pvalue.$payload, $pname );
						$r->request();
						$total_success += $this->result( $r, $payload, $pname, $pvalue, 'Cookie' );
						unset( $r );
						
						// perform tests on COOKIES names
						if( strstr($this->name_injection,'C') )
						{
							$total_injection++;
							$r = clone $reference;
							$v = $r->getCookie( $pname );
							$r->unsetCookie( $pname );
							$r->setCookie( $pvalue, $pname.$payload );
							$r->request();
							$total_success += $this->result( $r, $payload, $pname, $pvalue, 'Cookie (name)' );
							unset( $r );
						}
					}
				}
				
				// perform tests on HEADERS
				if( strstr($this->injection,'H') && !$this->no_test )
				{
					$t_params = $reference->getHeaderTable();
					$n_params = count( $t_params );
					
					foreach( $t_params as $pname=>$pvalue )
					{
						// perform tests on HEADERS values
						$total_injection++;
						$r = clone $reference;
						$r->setHeader( $pvalue.$payload, $pname );
						$r->request();
						$total_success += $this->result( $r, $payload, $pname, $pvalue, 'Header' );
						unset( $r );
						
						// perform tests on HEADERS names
						if( strstr($this->name_injection,'H') )
						{
							$total_injection++;
							$r = clone $reference;
							$v = $r->getHeader( $pname );
							$r->unsetHeader( $pname );
							$r->setHeader( $pvalue, $pname.$payload );
							$r->request();
							$total_success += $this->result( $r, $payload, $pname, $pvalue, 'Header (name)' );
							unset( $r );
						}
					}
				}
			}
			
			if( !$this->no_test ) {
				echo "\n";
			}
		}
		
		if( !$this->no_test ) {
			echo $n_payload." payload(s) tested on ".$this->injection." of ".$n_request." request(s), so ".$total_injection." performed and ".$total_success." XSS found!\n";
		}
		echo "\n";
		
		return $total_success;
	}


	private function result( $r, $payload, $param_name, $param_value, $param_type )
	{
		$xss = false;
		$rr = $r->getResult();
		//var_dump($rr);
		/*
		$regexp = '#('.addcslashes($payload,'][)(.').')#';
		$m = preg_match_all( $regexp, $rr, $matches );
		//var_dump( $matches );
		
		if( $m ) {
			foreach( $matches[0] as $find ) {
				if( $find == $payload ) {
					$xss = true;
				}
			}
		}
		*/
		if( strstr($rr,$payload) ) {
			$xss = true;
		}
		
		echo str_pad( ' ', 8 );
		
		if( $xss ) {
			echo $param_type." '".$param_name."' seems to be ";
			Utils::_print( 'VULNERABLE', 'red' );
			echo ' with value: ';
			Utils::_print( $param_value.$payload, 'light_cyan' );
			echo "\n";
		} else {
			echo $param_type." '".$param_name."' seems to be ";
			Utils::_println( 'SAFE', 'green' );
		}

		return (int)$xss;
	}
}
