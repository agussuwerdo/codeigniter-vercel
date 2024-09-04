#!/bin/bash

# Define the modules directory
MODULES_PATH="src/application/modules"

# Function to capitalize the first letter
capitalize_first_letter() {
    echo "$(tr '[:lower:]' '[:upper:]' <<< ${1:0:1})${1:1}"
}

# Function to create a controller
create_controller() {
  local module_name="$1"
  local class_name="$2"
  
  # Capitalize the first letter for proper case
  local class_name_proper=$(capitalize_first_letter "$(echo "$class_name" | sed 's/-/_/g')")

  # Create the module directory if it doesn't exist
  mkdir -p "${MODULES_PATH}/${module_name}/controllers"

  # Define the file path
  local file_path="${MODULES_PATH}/${module_name}/controllers/${class_name_proper}.php"

  # Check if the file already exists
  if [ -e "$file_path" ]; then
    echo "Controller already exists: $file_path"
  else
    # Create the controller file with the basic structure
    cat > "$file_path" <<EOL
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ${class_name_proper} extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries, models, etc.
    }

    public function index() {
        // Default method
    }
}
EOL

    echo "Controller created: $file_path"
  fi
}

# Function to create a model
create_model() {
  local module_name="$1"
  local class_name="$2"
  
  # Capitalize the first letter for proper case
  local class_name_proper=$(capitalize_first_letter "$(echo "$class_name" | sed 's/-/_/g')")

  # Create the module directory if it doesn't exist
  mkdir -p "${MODULES_PATH}/${module_name}/models"

  # Define the file path
  local file_path="${MODULES_PATH}/${module_name}/models/${class_name_proper}.php"

  # Check if the file already exists
  if [ -e "$file_path" ]; then
    echo "Model already exists: $file_path"
  else
    # Create the model file with the basic structure
    cat > "$file_path" <<EOL
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ${class_name_proper}_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        // Initialize the model
    }
}
EOL

    echo "Model created: $file_path"
  fi
}

# Function to create a view
create_view() {
  local module_name="$1"
  local view_name="$2"
  
  # Define the directory and file paths
  local view_dir="${MODULES_PATH}/${module_name}/views/${view_name}"
  local file_path="${view_dir}/main.php"

  # Create the module directory if it doesn't exist
  mkdir -p "$view_dir"

  # Check if the file already exists
  if [ -e "$file_path" ]; then
    echo "View already exists: $file_path"
  else
    # Create the view file
    cat > "$file_path" <<EOL
<!-- View: ${view_name} -->
EOL

    echo "View created: $file_path"
  fi
}

# Parse the command and arguments
command="$1"
module_name="${2:-$3}"
class_name="$3"

if [ -z "$class_name" ]; then
  class_name="$module_name"
  module_name="$class_name"
fi

case $command in
  make:controller)
    create_controller "$module_name" "$class_name"
    ;;
  make:model)
    create_model "$module_name" "$class_name"
    ;;
  make:view)
    create_view "$module_name" "$class_name"
    ;;
  *)
    echo "Invalid command. Use make:controller, make:model, or make:view."
    ;;
esac
