#!/bin/sh
source ~/.bash_profile

if [ ! -n "$(cat $1 | dl extract pivotal-ids)" ]; then
	echo "Commit messages require at least one '[(Finishes|Fixes|Delivers) #pivotal_id]'"
	exit 1
fi
