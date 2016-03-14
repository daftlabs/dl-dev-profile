alias gs='git status'; 
alias l='ls -lhaG';

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
  case $* in
    update )
      sh ~/.daftlabs/install.sh
    ;;
    releases )
      git tag | xargs -I@ git log --format=format:"%ai @%n" -1 @ | sort -r | awk '{print $4}' | head -n 25
    ;;
    setup )
      if [ -z $(dl config pivotal) ]; then
        echo "pivotal token? (https://www.pivotaltracker.com/profile)"; read token;
        if [ -n $token ]; then
          dl config pivotal $token
        fi
      fi

      if git rev-parse --git-dir > /dev/null 2>&1; then
        repo_name=$(basename `git rev-parse --show-toplevel`)

        if [ -z $(dl config $repo_name-pivotal) ]; then
          echo "pivotal project id to use for \"$repo_name\"?"; read project_id;
          if [ -n $project_id ]; then
            dl config $repo_name-pivotal $project_id
          fi
        fi

        if [ -z $(dl config $repo_name-aws-key) ] || [ -z $(dl config $repo_name-aws-key-id) ]; then
          echo "aws key id to use for \"$repo_name\"?"; read key_id;
          echo "aws key to use for \"$repo_name\"?"; read key;

          if [ -n $key_id ] && [ -n $key ]; then
            dl config $repo_name-aws-key-id $key_id 
            dl config $repo_name-aws-key $key
          fi
        fi
      fi

      if [ -n "$(ls -a | grep ^\.git$)" ]; then
        echo "Installing githooks"
        cp ~/.daftlabs/hooks/require-pivotal-commit-msg.sh .git/hooks/commit-msg && chmod -R 755 .git/hooks
      else
        echo "Not installing githooks, .git directory missing."
      fi
    ;;
    extract\ pivotal-ids )
      php -r 'preg_match_all("/\[(\((Finishes|Fixes|Delivers)\) )?#[0-9]+\]/", file_get_contents("php://stdin"), $matches); echo implode("\n", $matches[0]) . "\n";' | uniq
    ;;
    pivotal\ details )
      shift 2

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
      fi;
    ;;
    config )
      cat ~/.daftlabs/config 
    ;;
    config\ --rm\ * )
      cat ~/.daftlabs/config | grep -v "^$3=" > tmp; mv tmp ~/.daftlabs/config 
    ;;
    config\ *\ * )
      dl config --rm $2
      echo "$2=$3" >> ~/.daftlabs/config
    ;;
    config\ * )
      cat ~/.daftlabs/config | grep "^$2=" | sed "s/$2=//"
    ;;
    * )
      echo "not a real thing"
    ;;
  esac
}
