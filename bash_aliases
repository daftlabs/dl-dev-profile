alias gs='git status'; 
alias l='ls -lhaG';

function dl() {
  case $* in
    update )
      sh ~/.daftlabs/install.sh
    ;;
    releases )
      git tag | xargs -I@ git log --format=format:"%ai @%n" -1 @ | sort -r | awk '{print $4}' | head -n 25
    ;;
    setup )
      if [ -x $(dl config pivotal) ]; then
        echo "pivotal token? (https://www.pivotaltracker.com/profile)"; read token;
        dl config pivotal $token
      fi

      if git rev-parse --git-dir > /dev/null 2>&1; then
        repo_name=$(basename `git rev-parse --show-toplevel`)

        if [ -x $(dl config $repo_name-pivotal) ]; then
          echo "pivotal project id to use for \"$repo_name\"?"; read project_id;
          dl config $repo_name-pivotal $project_id
        fi
      fi
    ;;
    config )
      cat ~/.daftlabs/config 
    ;;
    config\ --rm\ * )
      cat ~/.daftlabs/config | grep -v ^$3 > tmp; mv tmp ~/.daftlabs/config 
    ;;
    config\ *\ * )
      dl config --rm $2
      echo "$2=$3" >> ~/.daftlabs/config
    ;;
    config\ * )
      cat ~/.daftlabs/config | grep ^$2 | sed "s/$2=//"
    ;;
    * )
      echo "not a real thing"
    ;;
  esac
}
