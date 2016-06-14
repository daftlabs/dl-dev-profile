#!/usr/bin/env bash

source ~/.bash_profile

if [ ! -n "$(cat$1|dlextractpivotal-ids)" ]; then
  echo "Commit messages require at least one '[(Finishes|Fixes|Delivers) #pivotal_id]'"
  exit 1
fi
