#!/bin/bash

# Define the migration directory
MIGRATION_DIR="src/application/migrations"

# Find the latest migration file number
LAST_MIGRATION=$(ls -1 "$MIGRATION_DIR" | grep -E '^[0-9]+_' | sort -n | tail -n 1 | awk -F'_' '{print $1}')

# Determine the number of digits for the next migration number
if [ -z "$LAST_MIGRATION" ]; then
  NEXT_NUMBER="001"
else
  if [ "$LAST_MIGRATION" -ge 1000 ]; then
    NEXT_NUMBER=$(printf "%04d" $((10#$LAST_MIGRATION + 1)))
  else
    NEXT_NUMBER=$(printf "%03d" $((10#$LAST_MIGRATION + 1)))
  fi
fi

# Check if a migration name was provided
if [ -z "$1" ]; then
  echo "Usage: $0 <migration_name>"
  exit 1
fi

MIGRATION_NAME=$1

# Replace hyphens with underscores for both class name and file name
MIGRATION_NAME_UNDERSCORE=$(echo "$MIGRATION_NAME" | sed 's/-/_/g')

# Convert migration name to class name format
CLASS_NAME=$(echo "$MIGRATION_NAME_UNDERSCORE" | awk -F'_' '{for(i=1;i<=NF;i++) $i=toupper(substr($i,1,1)) tolower(substr($i,2)); print "Migration_" $0}' | sed 's/ /_/g')

# Generate the file name
FILE_NAME="${NEXT_NUMBER}_${MIGRATION_NAME_UNDERSCORE}.php"
MIGRATION_PATH="$MIGRATION_DIR/$FILE_NAME"

# Create the migration file with the sequential number and boilerplate
cat > "$MIGRATION_PATH" <<EOL
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ${CLASS_NAME} extends CI_Migration {

    public function up() {
        // Define the fields
    }

    public function down() {
        // Drop the table if it exists
    }
}
EOL

# Output the file path
echo "Migration created: $MIGRATION_PATH"
