#!/bin/bash

function usage() {
    echo "Usage: "$0" <url file> [<payloads file>]"
    if [ -n "$1" ] ; then
	echo "Error: "$1"!"
    fi
    exit
}

if [ $# -lt 1 ] || [ $# -gt 2 ] ; then
    usage
fi

f_src=$1

if [ $# -eq 2 ] ; then
	f_payloads=$2
else
	f_payloads=brute-full.txt
fi

phantom=$(whereis phantomjs | awk '{print $2}')

php testxss.php --threads=10 --urls=$f_src --payload=$f_payloads --prefix --suffix --gpg --phantom=$phantom --sos --verbose=2
