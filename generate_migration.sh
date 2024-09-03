#!/bin/bash

# Get the current timestamp in the format YYYYMMDDHHMMSS
TIMESTAMP=$(date +"%Y%m%d%H%M%S")

# Check if a migration name was provided
if [ -z "$1" ]; then
  echo "Usage: $0 <migration_name>"
  exit 1
fi

MIGRATION_NAME=$1

# Replace hyphens with underscores for both class name and file name
MIGRATION_NAME_UNDERSCORE=$(echo $MIGRATION_NAME | sed 's/-/_/g')

# Convert migration name to class name format
CLASS_NAME=$(echo $MIGRATION_NAME_UNDERSCORE | awk -F'_' '{for(i=1;i<=NF;i++) $i=toupper(substr($i,1,1)) tolower(substr($i,2)); print "Migration_" $0}' | sed 's/ /_/g')

FILE_NAME="${TIMESTAMP}_${MIGRATION_NAME_UNDERSCORE}.php"
MIGRATION_PATH="src/application/migrations/$FILE_NAME"

# Create the migration file with the timestamp and boilerplate
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
