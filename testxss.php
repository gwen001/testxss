#!/usr/bin/php
<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

function __autoload( $c ) {
	include( __DIR__.'/'.$c.'.php' );
}


// parse command line
{
	$options = [
		'burp:',
		'cookies:',
		'encode:',
		'force-cl:',
		'gpg',
		'h',
		'help',
		'inject',
		'inject-name:',
		'no-test',
		'no-redir',
		'payload:',
		'phantom:',
		'prefix::',
		'replace:',
		'request:',
		'single:',
		'sos',
		'ssl',
		'suffix::',
		'test:',
		'threads:',
		'urls:',
		'verbose:',
	];
	$t_options = getopt( '', $options );
	//var_dump( $t_options );

	$testxss = new TestXss();

	foreach( $t_options as $k=>$v ) {
		switch( $k ) {
			case 'burp':
				$testxss->setBurpFile( $v );
				break;

			case 'cookies':
				$testxss->setCookies( $v );
				break;

			case 'encode':
				$testxss->enableEncode();
				break;
				
			case 'force-cl':
				$testxss->forceContentLength();
				break;
				
			case 'gpg':
				$testxss->enableGpg();
				break;

			case '-h':
			case 'help':
				Utils::help();
				break;

			case 'inject':
				$testxss->setInjection( $v );
				break;

			case 'inject-name':
				$testxss->setNameInjection($v );
				break;

			case 'no-test':
				$testxss->noTest();
				break;

			case 'no-redir':
				$testxss->noRedirect();
				break;

			case 'payload':
				$testxss->setPayload( $v );
				break;

			case 'phantom':
				$testxss->enablePhantom( $v );
				break;

			case 'prefix':
				$testxss->setPrefix( $v );
				break;

			case 'replace':
				$testxss->setReplaceMode( $v );
				break;

			case 'request':
				$testxss->setRequest( $v );
				break;

			case 'single':
				$testxss->setSingle( $v );
				break;

			case 'sos':
				$testxss->stopOnSuccess();
				break;

			case 'ssl':
				$testxss->forceSsl( true );
				break;

			case 'suffix':
				$testxss->setSuffix( $v );
				break;

			case 'test':
				$testxss->setSpecificParam( $v );
				break;

			case 'threads':
				$testxss->setMaxChild( $v );
				break;

			case 'urls':
				$testxss->setSourceFile( $v );
				break;

			case 'verbose':
				$testxss->setVerbose( $v );
				break;

			default:
				Utils::help( 'Unknown option: '.$k );
		}
	}

//	if( !$testxss->getPayloads() ) {
//		Utils::help('Payloads not found!');
//	}
}
// ---


// init
{
	if( !$testxss->getSingle() && !$testxss->getRequest() && !$testxss->getSourceFile() && !$testxss->getBurpSource() ) {
		Utils::help('No source found!');
	}
	
	echo "\n";
	
	$n = $testxss->loadDatas();
	echo $n." request(s) has been loaded.\n";
	if( !$n ) {
		Utils::help('No source found!');
	}
	
	$n = $testxss->loadPayload();
	echo $n." payload(s) has been loaded.\n";
	if( !$n ) {
		Utils::help('No payload configured!');
	}
	
	echo "Parameters ".$testxss->getInjection()." will be tested.\n";
}
// ---


// main loop
{
	echo "\n";
	$testxss->run();
}
// ---


exit();
