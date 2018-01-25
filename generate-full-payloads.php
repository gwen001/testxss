<?php

function usage( $err=null ) {
	//echo 'Usage: '.$_SERVER['argv'][0]." <urls source file> <get<=>post> <remove host>\n";
	echo 'Usage: '.$_SERVER['argv'][0]." <urls source file> <remove host> <get<=>post>\n";
	if( $err ) {
		echo 'Error: '.$err."!\n";
	}
	exit();
}


if( $_SERVER['argc'] < 2 || $_SERVER['argc'] > 4 ) {
	usage();
}

$source_file = $_SERVER['argv'][1];
if( !is_file($source_file) ) {
	usage( 'Cannot find source file "'.$source_file.'"' );
}

//$r_method = $_SERVER['argv'][2];
$r_method = 'r_method.txt';
if( file_put_contents($r_method,'') === false ) {
	usage( 'Cannot write output file "'.$r_method.'"' );
}

//$r_get = $_SERVER['argv'][3];
$r_get = 'r_get.txt';
if( file_put_contents($r_get,'') === false ) {
	usage( 'Cannot write output file "'.$r_get.'"' );
}

//$r_post = $_SERVER['argv'][4];
$r_post = 'r_post.txt';
if( file_put_contents($r_post,'') === false ) {
	usage( 'Cannot write output file "'.$r_post.'"' );
}

$_nohost = ($_SERVER['argc'] >= 3);
$_switch_gp = ($_SERVER['argc'] >= 4);


$t_payloads = [
	'xss' => [
		['payload-xss.txt',0],
		//['payload-xss-full.txt',0],
	],
	'lfi' => [
		['payload-lfi-short.txt',1],
		//['payload-lfi.txt',1],
	],
	'ti' => [ // template injection
		['payload-ti-short.txt',0],
		//['payload-ti.txt',0],
	],
	'tib' => [ // template injection blind
		//['payload-tib.txt',0],
		//['payload-tib-http.txt',0],
	],
	'rce' => [
		//['payload-rce.txt',0],
		['payload-rce-short.txt',0],
		//['payload-rce-http.txt',0],
	],
];


$t_source_url = file( $source_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

foreach( $t_source_url as $url )
{
	$t_result_method = [];
	$t_result_url = [];
	$t_result_params = [];
	
	if( strstr($url,"\t") ) {
		$url = str_replace( "\t", '?', $url );
		$original_method = 'POST';
		$theother_method = 'GET';
	} else {
		$original_method = 'GET';
		$theother_method = 'POST';
	}

	generate( $url, $original_method, $t_result_method, $t_result_url, $t_result_params );

	if( $_switch_gp ) {
		generate( $url, $theother_method, $t_result_method, $t_result_url, $t_result_params );
	}
	
	if( $_nohost ) {
		removeDomain( $t_result_url );
	}
	
	file_put_contents( $r_method, implode("\n",$t_result_method)."\n", FILE_APPEND );
	file_put_contents( $r_get, implode("\n",$t_result_url)."\n", FILE_APPEND );
	file_put_contents( $r_post, implode("\n",$t_result_params)."\n", FILE_APPEND );
	//exit();
}


function removeDomain( &$t_urls )
{
	foreach( $t_urls as $k=>$u )
	{
		if( stristr($u,'http') == 0 ) {
			$tmp = explode( '/', $u );
			//var_dump( $tmp );
			$remove = $tmp[0].'//'.$tmp[2];
			$t_urls[$k] = substr( $u, strlen($remove) );
		}
	}
}


function generate( $url, $method, &$t_result_method, &$t_result_url, &$t_result_params )
{
	global $t_payloads;
	
	foreach( $t_payloads as $vuln=>$t_files )
	{
		foreach( $t_files as $file )
		{
			$cmd = 'php testxss.php --no-test --payload='.$file[0].' --single="'.$url.'" --prefix --suffix';
			if( $file[1] ) {
				$cmd .= ' --replace=GP';
			}
			//echo $cmd."\n";
			
			$output = null;
			exec( $cmd, $output );
			$cnt = count( $output );
			$output = array_slice( $output, 7, $cnt-7-1 );
			$cnt = count( $output );
			
			if( $method == 'POST' ) {
				foreach( $output as $k=>$line ) {
					$tmp = explode( '?', $line );
					$output[$k] = $tmp[0];
					$t_result_params[] = $tmp[1];
				}
				//$t_result_params = array_merge( $t_result_params, array_fill(0,$cnt,'') );
			} else {
				$t_result_params = array_merge( $t_result_params, array_fill(0,$cnt,'') );
			}
			
			$t_result_url = array_merge( $t_result_url, $output );
			$t_result_method = array_merge( $t_result_method, array_fill(0,$cnt,$method) );
		}
	}
}


exit();
