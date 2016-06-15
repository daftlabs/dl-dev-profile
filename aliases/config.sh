#!/usr/bin/env bash

case $1 in
  --show)
    cat ~/.daftlabs/config
  ;;
  --rm)
    cat ~/.daftlabs/config | grep -v "^$2=" > tmp; mv tmp ~/.daftlabs/config
  ;;
  --add)
    cat ~/.daftlabs/config | grep -v "^$2=" > tmp; mv tmp ~/.daftlabs/config
    echo "$2=$3" >> ~/.daftlabs/config
  ;;
esac
