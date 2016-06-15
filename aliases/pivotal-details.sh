#!/usr/bin/env bash

if git rev-parse --git-dir > /dev/null 2>&1; then
  repo_name=$(basename `git rev-parse --show-toplevel`)

  if [ -n $(dl config $repo_name-pivotal) ] && [ -n $(dl config pivotal) ]; then

    story_ids=$(dl extract pivotal-ids)

    if [ -n "$story_ids" ]; then
      story_count=$(echo -n $(echo "$story_ids" | wc -l))
    else
      story_count=0
    fi

    echo "found $story_count stories"

    if [ $story_count -gt 0 ]; then
      echo "$story_ids" | php ~/.daftlabs/helpers/pivotal-story-details.php $(dl config $repo_name-pivotal) $(dl config pivotal)
    fi
  else
    echo "required configurations not present - run dl setup"
  fi
else
  echo "get in a project directory dude"
fi
;
