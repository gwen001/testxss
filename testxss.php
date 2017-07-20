<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

function __autoload( $c ) {
	include( $c.'.php' );
}


// parse command line
{
	$testxss = new TestXss();

	$argc = $_SERVER['argc'] - 1;

	for ($i = 1; $i <= $argc; $i++) {
		switch ($_SERVER['argv'][$i]) {
			case '--burp':
				$testxss->setBurpFile( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '--cookies':
				$testxss->setCookies( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '--encode':
				$testxss->enableEncode();
				break;
				
			case '--force-cl':
				$testxss->forceContentLength();
				break;
				
			case '--gpg':
				$testxss->enableGpg();
				break;

			case '-h':
			case '--help':
				Utils::help();
				break;

			case '--inject':
				$testxss->setInjection( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '--inject-name':
				$testxss->setNameInjection( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '--no-test':
				$testxss->noTest();
				break;

			case '--no-redir':
				$testxss->noRedirect();
				break;

			case '--payload':
				$testxss->setPayload( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '--prefix':
				$testxss->setPrefix( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '--request':
				$testxss->setRequest( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '--single':
				$testxss->setSingle($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '--ssl':
				$testxss->forceSsl( true );
				break;

			case '--suffix':
				$testxss->setSuffix( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '--threads':
				$testxss->setMaxChild($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '--urls':
				$testxss->setSourceFile($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '--verbose':
				$testxss->setVerbose($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			default:
				Utils::help('Unknown option: '.$_SERVER['argv'][$i]);
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
