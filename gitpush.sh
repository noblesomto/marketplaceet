#!/bin/bash

# Automatically detect the current branch
branch=$(git rev-parse --abbrev-ref HEAD)
echo "Pushing branch: $branch"

# Push the current branch to origin
git push -u origin "$branch" && .git/hooks/post-push
