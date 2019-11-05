#!/bin/bash

function usage() {
    echo "Usage: "$0" <url> [<payload>]"
    if [ -n "$1" ] ; then
	echo "Error: "$1"!"
    fi
    exit
}

if [ $# -lt 1 ] || [ $# -gt 2 ] ; then
    usage
fi

url=$1

if [ $# -eq 2 ] ; then
	payload=$2
else
	payload="/opt/SecLists/mine/xss-mytop50.txt"
fi

phantom=$(whereis phantomjs | awk '{print $2}')
echo $phantom
echo $payload
echo $url

#php testxss.php --threads=10 --urls=$f_src --payload=$f_payloads --prefix --suffix --gpg --phantom=$phantom --sos --verbose=2
testxss --encode --threads 10 --gpg --phantom $phantom --payload $payload --prefix --suffix --sos --single "$url"

