#!/usr/bin/env bash

#definitions
#======================================================================
RED="\[\e[0;31m\]"
CYAN="\[\e[0;36m\]"
GREEN="\[\e[0;32m\]"
LGREEN="\[\e[1;32m\]"
WHITE="\[\e[0;37m\]"
PURPLE="\[\e[0;35m\]"
BLUE="\[\e[1;34m\]"

function ref() {
  git branch --no-color 2>/dev/null | sed -e "/^[^*]/d" -e "s/* \(.*\)/\[\1\]/" || return
}

function sha() {
  git rev-parse --short HEAD 2>/dev/null | sed -e "s/\(.*\)/\[\1\]/" || return
}

gc() {
  branch_name=`git symbolic-ref --short HEAD`
  pivotal_id=`echo $branch_name | sed -En 's/^([0-9]{7,}).*$/\1/p'`
  if [ -z "$pivotal_id" ]; then
    if [ -z "$2" ]; then
      echo 'No Pivotal Id detected in branch name, please supply pivotal ID and commit message'
    else
      git commit -m "[#$1] $2"
    fi
  else
    git commit -m "[#$pivotal_id] $1"
  fi
}

#exports
#======================================================================
export CLICOLOR=1
export TERM=xterm-256color

#docker shortcuts
#======================================================================
dssh() {
  docker exec -ti $1 /bin/bash
}
alias drun="docker-compose run web"
alias dash="docker-compose run web /bin/bash"
alias dkill="docker kill \$(docker ps -q)"
alias drmc="docker rm \$(docker ps -aq)"
alias drmi="docker rmi \$(docker images -q)"

#misc
#======================================================================
alias gs="git status";
alias l="ls -lhaG";
alias dl="node ~/.daftlabs/src/main.js";

PS1="$GREEN\u@\h$CYAN\$(ref)$PURPLE\$(sha)$BLUE\w\[\e[0m\]: "
