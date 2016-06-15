#!/usr/bin/env bash

if [ "$1" == "--force" ] || [-z $(bash ~/.daftlabs/aliases/config.sh --get pivotal) ]; then
  echo "pivotal token? (https://www.pivotaltracker.com/profile)";
  read token;
  if [ -n $token ]; then
    bash ~/.daftlabs/aliases/config.sh --add pivotal $token
  fi
fi

if git rev-parse --git-dir > /dev/null 2>&1; then
  repo_name=$(basename `git rev-parse --show-toplevel`)

  if [ "$1" == "--force" ] || [ -z $(bash ~/.daftlabs/aliases/config.sh --get $repo_name-pivotal) ]; then
    echo "pivotal project id to use for \"$repo_name\"?";
    read project_id;
    if [ -n $project_id ]; then
      bash ~/.daftlabs/aliases/config.sh --add $repo_name-pivotal $project_id
    fi
  fi

  if [ "$1" == "--force" ] || [ -z $(bash ~/.daftlabs/aliases/config.sh --get $repo_name-aws-key) ] || [ -z $(bash ~/.daftlabs/aliases/config.sh --get $repo_name-aws-key-id) ]; then
    echo "aws key id to use for \"$repo_name\"?";
    read key_id;
    echo "aws key to use for \"$repo_name\"?";
    read key;

    if [ -n $key_id ] && [ -n $key ]; then
      bash ~/.daftlabs/aliases/config.sh --add $repo_name-aws-key-id $key_id
      bash ~/.daftlabs/aliases/config.sh --add $repo_name-aws-key $key
      echo "\n[${repo_name}]\naws_access_key_id = ${key_id}\naws_secret_access_key = ${key}\n" >> ~/.aws/credentials
    fi
  fi
fi

if [ -n "$(ls -a | grep ^\.git$)" ]; then
  echo "Installing githooks"
  cp ~/.daftlabs/hooks/require-pivotal-commit-msg.sh .git/hooks/commit-msg && chmod -R 755 .git/hooks
else
  echo "Not installing githooks, .git directory missing."
fi
