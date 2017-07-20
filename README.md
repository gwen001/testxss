# testxss
PHP tool to test basic reflected Cross Site Scripting aka XSS
Note that this is an automated tool, manual check is still required.  

```
Usage: php testxss.php [OPTIONS] -f <request_file>

Options:
	--burp		export from Burp Suite
	--encode	encode special char in payloads (not implemented yet)
	--encode-char	specify what chars to encode (default=php -a./\=<>?+&*;:"{}|^`) (not implemented yet)
	--force-cl	force Content-Length header
	--gpg		try to send GET params to POST and POST params to GET
	-h, --help	print this help
	--inject	injection point, default=GPCHF
				G: GET parameters
				P: POST parameters
				C: Cookies
				H: Headers
				F: Fragment
	--inject_name	inject in paramater name as well, default=disabled
				G: GET parameters
				P: POST parameters
				C: Cookies
				H: Headers
	--no-test	do not performed any test, list only the urls called
	--no-redir	do not follow redirection
	--payload	set single payload (default='"><) or file
	--prefix	prefix all payloads with a string
	--request	source file of the orignal request
	--single	load a single url
	--ssl		force https
	--suffix	suffix all payloads with a string
	--threads	number of threads, default=5 (not implemented yet)s
	--tolerance	set tolerance for result output (not implemented yet)
	--urls		file that contains a list of urls

Examples:
	php testxss.php -f request.txt
	php testxss.php -r -s -i GPC -f request.txt
```

I don't believe in license.  
You can do want you want with this program.  
