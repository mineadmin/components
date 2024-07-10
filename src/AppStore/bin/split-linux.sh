#!/usr/bin/env bash
set -e
set -x
CURRENT_BRANCH=$(cd $1 && git rev-parse --abbrev-ref HEAD)
bin=$3
BASEPATH=$1
REPO=$2
function split()
{
    SHA1=`$bin/splitsh-lite-linux --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

remote 'appStore' $REPO
split $BASEPATH $REPO
