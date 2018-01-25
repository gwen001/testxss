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
		'n:',
		'output:',
		'port:',
		'threads:',
		'tld:',
	];
	$t_options = getopt( '', $options );
	//var_dump( $t_options );

	$testonion = new TestOnion();

	foreach( $t_options as $k=>$v ) {
		switch( $k ) {
			case 'n':
				$testonion->setQuantity( $v );
				break;

			case 'output':
				$testonion->setOutputDir( $v );
				break;

			case 'port':
				$testonion->setPort( $v );
				break;

			case 'threads':
				$testonion->setMaxChild( $v );
				break;

			case 'tld':
				$testonion->setTld( $v );
				break;

			case '-h':
			case 'help':
				Utils::help();
				break;

			default:
				Utils::help( 'Unknown option: '.$k );
		}
	}
}
// ---


// init
{
	if( !$testonion->getQuantity() ) {
		Utils::help('Quantity not found!');
	}
}
// ---


// main loop
{
	$testonion->run();
}
// ---


exit();
