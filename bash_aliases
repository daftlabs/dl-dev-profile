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
    ;;
    pivotal\ log* )
      shift 2
      repo_name=$(basename `git rev-parse --show-toplevel`)

      story_ids=$(git log --pretty=oneline $@ | grep '#[0-9]\+' | sed 's/.*\(#[0-9]*\).*/\1/' | uniq)

      if [ -n "$story_ids" ]; then
        story_count=$(echo -n $(echo "$story_ids" | wc -l))
      else
        story_count=0
      fi

      echo "found $story_count stories"
      echo "$story_ids" | php ~/.daftlabs/helpers/pivotal-story-details.php $(dl config $repo_name-pivotal) $(dl config pivotal)
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
