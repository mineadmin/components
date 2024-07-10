#!/usr/bin/env bash
set -e
if (( "$#" == 0 ))
then
    echo "Tag has to be provided"

    exit 1
fi

NOW=$(date +%s)
CURRENT_BRANCH=$(cd $1 && git rev-parse --abbrev-ref HEAD)
BASEPATH=$1
VERSION=$3
REMOTE=$2
# Always prepend with "v"
if [[ $VERSION != v*  ]]
then
    VERSION="v$VERSION"
fi

echo ""
echo ""
echo "Cloning $REMOTE";
TMP_DIR="/tmp/mineAdmin-split"
REMOTE_URL=$REMOTE

rm -rf $TMP_DIR;
mkdir $TMP_DIR;

(
    cd $TMP_DIR;

    git clone $REMOTE_URL .
    git checkout "$CURRENT_BRANCH";

    if [[ $(git log --pretty="%d" -n 1 | grep tag --count) -eq 0 ]]; then
        echo "Releasing $REMOTE"
        git tag $VERSION
        git push origin --tags
    fi
)

TIME=$(echo "$(date +%s) - $NOW" | bc)

printf "Execution time: %f seconds" $TIME