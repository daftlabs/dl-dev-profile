#!/usr/bin/env bash

alias gs='git status';
alias l='ls -lhaG';

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

dSSH() {
  docker exec -ti $1 /bin/bash
}

alias drun="docker-compose run web"
alias dash="docker-compose run web /bin/bash"
alias dssh=dSSH
alias dkill="docker kill \$(docker ps -q)"
alias drmc="docker rm \$(docker ps -aq)"
alias drmi="docker rmi \$(docker images -q)"

function dl() {
  a=$1;
  shift 1
  case $a in
    update)
      sh ~/.daftlabs/aliases/install.sh "$@"
    ;;
    releases)
      git tag | xargs -I@ git log --format=format:"%ai @%n" -1 @ | sort -r | awk '{print $4}' | head -n 25
    ;;
    setup)
      sh ~/.daftlabs/aliases/setup.sh "$@"
    ;;
    extract-pivotal-ids)
      php -r 'preg_match_all("/\[(\((Finishes|Fixes|Delivers)\) )?#[0-9]+\]/", file_get_contents("php://stdin"), $matches); echo implode("\n", $matches[0]) . "\n";' | uniq
    ;;
    pivotal-details)
      sh ~/.daftlabs/aliases/pivotal-details.sh "$@"
    ;;
    ecs-ssh)
      php ~/.daftlabs/aliases/ecs-ssh.php "$@"
    ;;
    ecs-db-backup)
      php ~/.daftlabs/aliases/ecs-db-backup.php "$@"
    ;;
    ecs-deploy)
      php ~/.daftlabs/aliases/ecs-deploy.php "$@"
    ;;
    config)
      sh ~/.daftlabs/aliases/config.sh "$@"
    ;;
    *)
      echo "not a real thing"
    ;;
  esac
}
