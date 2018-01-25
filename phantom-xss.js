
var system = require('system');
var args = system.args;
//console.log(args.length);
var page = require('webpage').create();

if( args.length !== 2 ) {
	console.log( 'Usage: phantomjs xss.js <url>');
	phantom.exit();
}

var url = args[1];


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


setTimeout(function() {
    console.log( 'Testing: '+url );
    page.open( url );
}, 0);

setTimeout(function() {
	phantom.exit();
}, 2000);
