#!/bin/bash

# FTP Credentials
HOST='ftp.marketplace.ng'
USER='somto@marketplace.ng'
PASS='NL%c?_F46?lH'

# Local to remote folder mapping
declare -A FOLDERS=(
  ["./"]="marketplace/"
  ["./public/frontend/"]="public_html/frontend/"
  ["./public/dashboard/"]="public_html/dashboard/"
  ["./public/backend/"]="public_html/backend/"
  ["./public/build/"]="public_html/build/"
)

# Extra remote build paths using same local folder
EXTRA_BUILD_PATHS=(
  "marketplace/public/build/"
)

# Excludes (folders/files to ignore)
EXCLUDES=(
  ".git/"
  ".claude/"
  ".vite/"
  ".gitignore"
  ".editorconfig"
  ".gitattributes"
  "node_modules/"
  "vendor/"
  ".env"
  "deploy.sh"
  "deploy.log"
  "gitpush.sh"
  "storage/"
  "bootstrap/"
  "public/hot"
  "public/uploads/"
  "public/ckeditor/"
  "public/sitemap*.xml"
  "*.zip"
)

# Build exclude string for lftp and rsync
EXCLUDE_ARGS=""
for pattern in "${EXCLUDES[@]}"; do
  EXCLUDE_ARGS+=" --exclude-glob $pattern"
done

# Build RSYNC_EXCLUDES for rsync
RSYNC_EXCLUDES=""
for pattern in "${EXCLUDES[@]}"; do
  RSYNC_EXCLUDES+=" --exclude=$pattern"
done

# Function to upload folder if changes exist
upload_if_changed() {
    local LOCAL_DIR=$1
    local REMOTE_DIR=$2
    local NAME=$3
    local CLEAR_FIRST=$4

    CHANGED=$(rsync -av --dry-run $RSYNC_EXCLUDES "$LOCAL_DIR" "/tmp/deploy_check" | grep -v '/$' | wc -l)

    if [ "$CHANGED" -gt 0 ]; then
        echo "🔄 Changes detected in $NAME ($CHANGED files)."

        if [[ "$CLEAR_FIRST" == "yes" ]]; then
            echo "🧹 Clearing remote $NAME folder before upload (preserving .htaccess)..."
            lftp -e "
            set ssl:verify-certificate no;
            open -u $USER,$PASS $HOST;
            cd $REMOTE_DIR;
            mrm -r [!.]*;   # Remove all non-hidden files/folders
            bye
            "
        fi

        echo "📤 Uploading $NAME..."
        lftp -e "
        set ssl:verify-certificate no;
        open -u $USER,$PASS $HOST;
        mirror --reverse \
               --only-newer \
               $EXCLUDE_ARGS \
               --verbose \
               $LOCAL_DIR $REMOTE_DIR;
        bye
        "
    else
        echo "✅ No changes detected in $NAME. Skipping upload."
    fi
}

# Upload all mapped folders
for LOCAL_DIR in "${!FOLDERS[@]}"; do
  REMOTE_DIR=${FOLDERS[$LOCAL_DIR]}
  NAME=$(basename "$REMOTE_DIR")

  if [[ "$LOCAL_DIR" == "./public/build/" ]]; then
      # Main build folder
      upload_if_changed "$LOCAL_DIR" "$REMOTE_DIR" "$NAME" "yes"

      # Also upload to extra build paths
      for EXTRA_PATH in "${EXTRA_BUILD_PATHS[@]}"; do
          upload_if_changed "$LOCAL_DIR" "$EXTRA_PATH" "build (extra)" "yes"
      done
  else
      upload_if_changed "$LOCAL_DIR" "$REMOTE_DIR" "$NAME" "no"
  fi
done
