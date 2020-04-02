#!/usr/bin/env bash

# Get the plugin slug from this git repository.
PLUGIN_SLUG="${PWD##*/}"

# Get the current release version
TAG=$(sed -e "s/refs\/tags\///g" <<< $GITHUB_REF)
VERSION="${TAG//v}"

# Get the SVN data from wp.org in a folder named `svn`
SVN_URL="https://plugins.svn.wordpress.org/${PLUGIN_SLUG}/"
SVN_DIR="$HOME/svn-${PLUGIN_SLUG}"
svn checkout --depth immediates "$SVN_URL" "$SVN_DIR"

# Switch to SVN directory
cd "$SVN_DIR"

svn update --set-depth infinity trunk
svn update --set-depth infinity tags/$VERSION

# Copy files from release to `svn/trunk`
rsync -rc --exclude-from="$GITHUB_WORKSPACE/.distignore" "$GITHUB_WORKSPACE/" trunk/ --delete --delete-excluded 

# Prepare the files for commit in SVN
svn add --force trunk

# Create the version tag in svn
svn cp "trunk" "tags/$VERSION"

# Prepare the tag for commit
svn add --force tags

# Commit files to wordpress.org.
svn ci  --message "Release $TAG" \
        --username $SVN_USERNAME \
        --password $SVN_PASSWORD \
        --non-interactive