#!/bin/bash

# Updates the internal-webserver repository, including updating all
# the phabricator repos from upstream, and then pushes it to the
# phabricator machine (toby), where it safely restarts the webserver
# there.

# Die if something goes wrong.
set -e

# Make git 1.8 revert to git 1.7 behavior, and not prompt for a merge
# message when doing a 'git pull' from upstream.
export GIT_MERGE_AUTOEDIT=no

# We always run from the same directory as where this script lives.
# This is the only bash-ism in the script.
cd "$(dirname "${BASH_SOURCE[0]}")"

# We need the internal-webserver repository to be set up properly.
if [ ! -f "phabricator/.git" ]; then
    echo "You need to set up the phabricator subdirectories as submodules."
    exit 1
fi

# We'll need the right permissions file to push to production.
# c.f. https://sites.google.com/a/khanacademy.org/forge/for-khan-employees/accessing-amazon-ec2-instances#TOC-Accessing-EC2-Instances
if [ ! -s "$HOME/.ssh/internal-webserver.pem" ]; then
  echo "You need to install internal_webserver.pem to push to production."
  echo "At https://www.dropbox.com/home/Khan%20Academy%20All%20Staff/Secrets"
  echo "download internal-webserver.pem and save it in your ~/.ssh directory"
  exit 1
fi

git checkout master
trap 'git checkout -' 0  # reset to old branch when the script exits
git pull --no-rebase

# $1: the directory to cd to (root of some git tree).
push_upstream() {
(  cd "$@"
   # We hard-code the remotes to the user doesn't need to set them up.
   # We use the ssh form so it doesn't ask for passwords each time.
   origin="git@github.com:Khan/$@.git"
   upstream="git@github.com:facebook/$@.git"
   git checkout master
   git pull --no-rebase
   git pull --no-rebase "$upstream" master
   # Make sure we push using ssh so we don't need to enter a password.
   git push "$origin" master
)
   git add "$@"    # update the substate in our main repo
}

push_upstream phabricator
push_upstream libphutil
push_upstream arcanist

git status
# The summary has lines like '* arcanist db0f22a...b3021f4 (1):'
git submodule summary --summary-limit 1 | grep '^\*' | while read line; do
    subrepo=`echo "$line" | cut -d" " -f2`
    range=`echo "$line" | cut -d" " -f3`
    echo
    echo ">>> $subrepo"
    ( cd "$subrepo" && git log --oneline "$range" | cat)
done

echo -n "Does everything look ok? (y/N) "
read prompt
if [ "$prompt" != "y" -a "$prompt" != "Y" -a "$prompt" != "yes" ]; then
   echo "Aborting; user said no"
   echo "[Note the subrepos (e.g. phabricator/) have already been pushed]"
   exit 1
fi

# Turn off linting (it's all third-party code).
env FORCE_COMMIT=1 git commit -am "merge from upstream phabricator" && git push

# Now push to production
ssh ubuntu@phabricator.khanacademy.org -i "$HOME/.ssh/phabricator.pem" \
   "cd internal-webserver; \
    git checkout master; \
    git pull; \
    git submodule update --init --recursive; \
    sudo service phd stop; \
    sudo service nginx stop; \
    sudo service php5-fpm stop; \
    PHABRICATOR_ENV=khan phabricator/bin/storage upgrade --force; \
    sudo service php5-fpm start; \
    sudo service nginx start; \
    sudo service phd start; \
   "

echo "DONE!"
