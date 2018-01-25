<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class TestOnion
{
	const DEFAULT_DOMAIN_TLD = 'onion';
	const DEFAULT_DOMAIN_LENGTH = 16;
	const DEFAULT_DOMAIN_ALPHABET = 'abcdefghijklmnopqrstuvwxyz0123456789';

	const DEFAULT_PORT = 80;
	const DEFAULT_TIMEOUT = 5;
	
	const MAX_CHILD = 50;
	const DEFAULT_MAX_CHILD = 5;

	/**
	 * @var string
	 *
	 * tld
	 */
	private $n_alphabet = 0;

	/**
	 * @var string
	 *
	 * tld
	 */
	private $tld = self::DEFAULT_DOMAIN_TLD;

	/**
	 * @var integer
	 *
	 * quantity
	 */
	private $quantity = 0;

	/**
	 * @var array
	 *
	 * array of domain
	 */
	private $t_domain = [];

	/**
	 * @var integer
	 *
	 * port
	 */
	private $port = self::DEFAULT_PORT;

	/**
	 * @var string
	 *
	 * output directory
	 */
	private $output_dir = __DIR__.'/output/';

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

	
	public function getQuantity() {
		return $this->quantity;
	}
	public function setQuantity( $v )
	{
		$this->quantity = (int)$v;
		return true;
	}

	
	public function getPort() {
		return $this->port;
	}
	public function setPort( $v )
	{
		$this->port = (int)$v;
		return true;
	}

	
	public function getOutputDir() {
		return $this->tld;
	}
	public function setOutputDir( $v ) {
		$this->output_dir = trim( $v, ' /' ).'/';
		return true;
	}
	

	public function getTld() {
		return $this->tld;
	}
	public function setTld( $v ) {
		$this->tld = trim( $v );
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


	private function init()
	{
		$this->n_alphabet = strlen(self::DEFAULT_DOMAIN_ALPHABET) - 1;

		if( !is_dir($this->output_dir) ) {
			$m = @mkdir( $this->output_dir, 0777, true );
		}
		if( !is_dir($this->output_dir) && !$m ) {
			Utils::help( 'Cannot create output directory!' );
		}
	}

	
	private function clean()
	{
		exec( 'rm -f '.$this->output_dir.'/*.html' );
	}
	
	
	public function run()
	{
		$this->init();
		
		posix_setsid();
		declare( ticks=1 );
		pcntl_signal( SIGCHLD, array($this,'signal_handler') );
		
		for( $rindex=0 ; $rindex<$this->quantity ; )
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
					$this->process( $rindex );
					exit( 0 );
				}
			}

			usleep( $this->loop_sleep );
		}
		
		while( $this->n_child ) {
			// surely leave the loop please :)
			sleep( 1 );
		}
		
		$this->clean();
	}


	private function process( $index )
	{
		$t_domain = [
'bxswch5zuk3jp7p4.onion',
'2etvih2djigwolnv.onion',
'zwq7vf5hssw2fqhw.onion',
'spiderwjzbmsmu7y.onion',
'skrqzx4z4mfa4mrz.onion',
'ivpgoufenlvcozgg.onion',
'ceztgxskbupbd325.onion',
'fbitipsu2i6rqii5.onion',
'pifoigk46spu2566.onion',
'n35hwaokto6glcf6.onion',
'roparopaei7h5ju5.onion',
'fnj54tbs4qfo5q43.onion',
'iijydixxrvncyvpp.onion',
'z4d3nro7mjpueg56.onion',
'6czbqs4xe6eug3eg.onion',
'ncmsx3faerwiuwgm.onion',
'qe4zohjnmtu4pn2a.onion',
'lq3wg5cksh7utted.onion',
'scjufsm5owjp4kra.onion',
'j7qz365xygdjlyzu.onion',
];

		ob_start();
		
		$domain = $this->generateDomain();
		$domain = $t_domain[$index];
		echo $index.". ".$domain." ";
		
		$t = $this->testDomain( $domain );
		if( !$t ) {
			echo "KO";
			$color = 'light_grey';
			$r = false;
		} else {
			echo "OK";
			$color = 'green';
			$r = true;
			$this->recon( $domain );
		}
		
		echo "\n";
		$output = ob_get_contents();
		ob_end_clean();
		
		Utils::_print( $output, $color );
		return $r;
	}
	
	
	private function testDomain( $domain )
	{
		return true;
		
		$r = new HttpRequest();
		//$r->setPort( $this->port );
		$r->setUrl( $domain );
		$r->request();
		//var_dump( $r->getResultBody() );
		//var_dump( $r->getResultCode() );
		
		return ((int)$r->getResultCode()!=0);
		/*
		$fp = @fsockopen( $domain, $this->port, $errno, $errstr, self::DEFAULT_TIMEOUT );
	    if( !$fp ) {
			//echo "$errstr ($errno)<br />\n";
		    return false;
	    }

	    fclose( $fp );
	    */
    	return true;
	}
	
	
	private function recon( $domain )
	{
		//$c = '/opt/bin/httpscreenshot  -s http://'.$domain.' --headless -o '.$this->output_dir;
		$c = '/opt/bin/httpscreenshot  -s http://'.$domain.' --headless -o '.$this->output_dir.' -pX 127.0.0.1:9050';
		exec( $c );
	}
	
	
	private function generateDomain()
	{
		$domain = '';
		
		for( $i=0 ; $i<self::DEFAULT_DOMAIN_LENGTH ; $i++ ) {
			$domain .= self::DEFAULT_DOMAIN_ALPHABET[ rand(0,$this->n_alphabet) ];
		}
		
		$domain = $domain . '.' . $this->tld;
		
		return $domain;
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
