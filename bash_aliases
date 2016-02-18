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
    * ) echo "not a real thing" ;;
  esac
}
