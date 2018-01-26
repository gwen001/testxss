
var system = require('system');
var args = system.args;
//console.log(args.length);
var page = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64; rv:56.0) Gecko/20100101 Firefox/56.0';

if( args.length < 2 || args.length > 4 ) {
	console.log( 'Usage: phantomjs xss.js <url> [<cookies> <domain>]');
	phantom.exit();
}

var url = atob( args[1] );

phantom.clearCookies();
if( args.length == 4 ) {
	var domain = atob( args[3] );
	var cookies = atob( args[2] ).split(';');
	for( var i=0 ; i<cookies.length ; i++ ) {
		c = cookies[i].trim().split( '=' );
		//console.log( c[0]+' -> '+c[1] );
		phantom.addCookie( {'name':c[0],'value':c[1],'domain':'.'+domain} );
	}
}

////////////////////////////////////////////////////////////////////////////////
page.onAlert = function() {
    console.log('alert() called');
    phantom.exit();
};
page.onConfirm = function() {
    console.log('confirm() called');
};
page.onPrompt = function() {
    console.log('prompt() callled');
};
////////////////////////////////////////////////////////////////////////////////


function run( page, url )
{
	console.log( 'Testing: '+url );
    page.open( url, function (status) {
    	//console.log("Status: " + status);
    	//page.render('a.png');
    	/*console.log("Status: " + status);
		var cookies = page.cookies;
		console.log('Listing cookies:');
		for(var i in cookies) {
			console.log(cookies[i].name + '=' + cookies[i].value);
		}*/
	});
}


setTimeout( run(page,url), 0 );

setTimeout(function() {
	phantom.exit();
}, 2000);
