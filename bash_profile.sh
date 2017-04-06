#!/usr/bin/env bash

source ~/.bash_include

#definitions
#======================================================================
RED="\[\e[0;31m\]"
CYAN="\[\e[0;36m\]"
GREEN="\[\e[0;32m\]"
LGREEN="\[\e[1;32m\]"
WHITE="\[\e[0;37m\]"
PURPLE="\[\e[0;35m\]"
BLUE="\[\e[1;34m\]"

function getTime() {
  date +%H:%M:%S
}

function ref() {
  git branch --no-color 2>/dev/null | sed -e "/^[^*]/d" -e "s/* \(.*\)/\[\1\]/" || return
}

function sha() {
  git rev-parse --short HEAD 2>/dev/null | sed -e "s/\(.*\)/\[\1\]/" || return
}

#exports
#======================================================================
export CLICOLOR=1
export TERM=xterm-256color
export EDITOR=/usr/bin/vim

#docker shortcuts
#======================================================================
dssh() {
  docker exec -ti $1 /bin/bash
}

alias drun="docker-compose run web"
alias dash="docker-compose run web /bin/bash"
alias dkill="docker kill \$(docker ps -q)"
alias drmc="docker rm -v \$(docker ps -aq)"
alias drmi="docker rmi \$(docker images -q)"

#misc
#======================================================================
alias gs="git status";
alias gits='git status;'
alias l="ls -lhaG";
alias dl="node ~/.daftlabs/src/main.js";
alias e="vim ."

#tab completing movement to code directory
#======================================================================
function cdc() {
    cd ~/code/$1
}

cdc() {
    cd ~/code/$1
}

_cdc() {
    local cur opts
    cur="${COMP_WORDS[COMP_CWORD]}"
    opts=$(cd ~/code ; ls -d *)
    COMPREPLY=($(compgen -W "${opts}" -- ${cur}))
}

complete -F _cdc cdc

PS1="$GREEN\u@\h$WHITE[\$(getTime)]$CYAN\$(ref)$PURPLE\$(sha)$BLUE\w\[\e[0m\]: "
