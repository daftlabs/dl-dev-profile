alias gs='git status'; 
alias l='ls -lhaG';

function dl() {
  case $* in
    update )
      sh ~/.daftlabs/install.sh
    ;;
    * ) echo "not a real thing" ;;
  esac
}
