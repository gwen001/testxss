# testxss
PHP tool to test XSS.  
Note that this is an automated tool, manual check is still required.  

```
Usage: php testxss.php [OPTIONS]

Options:
	-h, --help	print this help

	--burp		export from Burp Suite (not implement yet)
	--request	source file of the orignal request
	--single	load a single url
	--urls		file that contains a list of urls
	
	--cookies	set the cookie (overwrite all other cookies)
	--force-cl	force Content-Length header
	--no-redir	do not follow redirection
	--ssl		force https
	
	--test		name of a specific param/cookie/header to test
	--inject	injection point, default=GPCHF
				G: GET parameters
				P: POST parameters
				C: Cookies
				H: Headers
				F: Fragment
	--inject-name	inject in paramater name as well, default=disabled
				G: GET parameters
				P: POST parameters
				C: Cookies
				H: Headers
	--gpg		try to send GET params to POST and POST params to GET
	
	--payload	set single payload or file, default='"><
	--prefix	prefix all payloads with a string, default is random string
	--suffix	suffix all payloads with a string, default is random string
	--encode	urlencode the payload, default=disabled
	--replace	replace the value of the parameter by the payload instead of concatenate at the end (only for GP)

	--no-test	do not performed any test, list only the urls called
	--phantom	if you test XSS with phantomjs, full path to the executable
	--sos		stop on success
	--threads	number of threads, default=5
```

# todo
```
Mouse/keyboard events
Random user-agent
JSON Padding
```


I don't believe in license.  
You can do want you want with this program.  
