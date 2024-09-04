#!/bin/bash

# Ensure at least the type argument is provided
if [ $# -lt 1 ]; then
  echo "Usage: $0 <type> [<module_name>] <class_name>"
  exit 1
fi

MODULES_PATH="src/application/modules"

# Extract arguments
TYPE=$1
MODULE_NAME=${2:-$3}  # If module name is not provided, use the class name
CLASS_NAME=$3

# Function to capitalize the first letter
capitalize_first_letter() {
    echo "$(tr '[:lower:]' '[:upper:]' <<< ${1:0:1})${1:1}"
}

# Function to create a file with boilerplate
create_file() {
    local file_path="$1"
    local class_name_proper="$2"
    local type="$3"

    # Create the file with the appropriate boilerplate
    case "$type" in
        controller)
            cat > "$file_path" <<EOL
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ${class_name_proper} extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        // Your code here
    }
}
EOL
            ;;
        model)
            cat > "$file_path" <<EOL
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ${class_name_proper} extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // Add model methods here
}
EOL
            ;;
        view)
            # Create an empty view file or with basic HTML structure
            cat > "$file_path" <<EOL
<!DOCTYPE html>
<html>
<head>
    <title>${class_name_proper} View</title>
</head>
<body>
    <h1>${class_name_proper} View</h1>
    <!-- Your view content here -->
</body>
</html>
EOL
            ;;
        *)
            echo "Unknown type: $type. Please specify controller, model, or view."
            exit 1
            ;;
    esac

    echo "$type created: $file_path"
}

# Capitalize the first letter of the class name
CLASS_NAME_PROPER=$(capitalize_first_letter "$(echo "$CLASS_NAME" | sed 's/-/_/g')")

# Create directories and files based on type
case "$TYPE" in
    controller|model)
        # Create directory if it does not exist
        mkdir -p "$MODULES_PATH/$MODULE_NAME/${TYPE}s"
        # Create the file
        create_file "$MODULES_PATH/$MODULE_NAME/${TYPE}s/${CLASS_NAME_PROPER}.php" "$CLASS_NAME_PROPER" "$TYPE"
        ;;
    view)
        # Create directory if it does not exist
        mkdir -p "$MODULES_PATH/$MODULE_NAME/views"
        # Create the file
        create_file "$MODULES_PATH/$MODULE_NAME/views/${CLASS_NAME_PROPER}.php" "$CLASS_NAME_PROPER" "$TYPE"
        ;;
    *)
        echo "Unknown type: $TYPE. Use 'controller', 'model', or 'view'."
        exit 1
        ;;
esac
