#!/usr/bin/env bash

case $1 in
  --show)
    cat ~/.daftlabs/config
  ;;
  --get)
    cat ~/.daftlabs/config | grep "^$2=" | sed "s/$2=//"
  ;;
  --rm)
    cat ~/.daftlabs/config | grep -v "^$2=" > tmp; mv tmp ~/.daftlabs/config
  ;;
  --add)
    cat ~/.daftlabs/config | grep -v "^$2=" > tmp; mv tmp ~/.daftlabs/config
    echo "$2=$3" >> ~/.daftlabs/config
  ;;
esac
