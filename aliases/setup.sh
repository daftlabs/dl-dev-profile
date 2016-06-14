#!/usr/bin/env bash

if [ -z $(dl config pivotal) ]; then
  echo "pivotal token? (https://www.pivotaltracker.com/profile)";
  read token;
  if [ -n $token ]; then
    dl config pivotal $token
  fi
fi

if git rev-parse --git-dir > /dev/null 2>&1; then
  repo_name=$(basename `git rev-parse --show-toplevel`)

  if [ -z $(dl config $repo_name-pivotal) ]; then
    echo "pivotal project id to use for \"$repo_name\"?";
    read project_id;
    if [ -n $project_id ]; then
      dl config $repo_name-pivotal $project_id
    fi
  fi

  if [ -z $(dl config $repo_name-aws-key) ] || [ -z $(dl config $repo_name-aws-key-id) ]; then
    echo "aws key id to use for \"$repo_name\"?";
    read key_id;
    echo "aws key to use for \"$repo_name\"?";
    read key;

    if [ -n $key_id ] && [ -n $key ]; then
      dl config $repo_name-aws-key-id $key_id
      dl config $repo_name-aws-key $key
    fi
  fi
fi

if [ -n "$(ls-a|grep^\.git$)" ]; then
  echo "Installing githooks"
  cp ~/.daftlabs/hooks/require-pivotal-commit-msg.sh .git/hooks/commit-msg && chmod -R 755 .git/hooks
else
  echo "Not installing githooks, .git directory missing."
fi
