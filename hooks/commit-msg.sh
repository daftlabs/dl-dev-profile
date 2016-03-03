#!/bin/sh

branch_name=$(git rev-parse --abbrev-ref HEAD)
last_commit_msg=$1
pivotal_regex=\[(\((Finishes|Fixes|Delivers)\) )?#[0-9]+\]

if ! echo $last_commit_msg | grep $pivotal_regex; then
	echo "Commit messages require at least one '[(Finishes|Fixes|Delivers) #pivotal_id]'"
	exit 1
fi
