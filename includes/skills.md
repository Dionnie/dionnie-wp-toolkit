### Plugin Architecture

The `DionnieWPToolkit` plugin follows a modular architecture, centered around the `DionnieWPToolkit\Core\Plugin` class. This class acts as the main orchestrator, responsible for:

1.  **Module Management**: It maintains a collection of individual modules, each designed to handle a specific feature or set of functionalities.
2.  **Lifecycle Hooks**: It manages the plugin's activation, deactivation, and uninstallation processes, ensuring proper setup and cleanup (e.g., database table creation/deletion).
3.  **Asset Management**: It integrates with a `ViteManifestHelper` to efficiently enqueue front-end assets (JavaScript, CSS) for both development and production environments.
4.  **Module Registration**: During the `plugins_loaded` WordPress action, it iterates through its registered modules and invokes their respective `register()` methods.

The core principle is to keep functionalities separated into distinct, manageable units (modules), promoting code organization, reusability, and maintainability.

### How a New Module is Added

Adding a new module to the `DionnieWPToolkit` plugin involves a straightforward process:

1.  **Create the Module Class**: Develop a new PHP class that encapsulates the desired functionality.
2.  **Implement `Registerable` Interface**: Ensure this new class implements the `DionnieWPToolkit\Core\Interfaces\Registerable` interface. This interface mandates a `register()` method.
3.  **Instantiate and Add to `Plugin`**: In the `DionnieWPToolkit\Core\Plugin` class, specifically within the `bootstrap_modules()` method, instantiate your new module class and add it to the `$this->modules` array.

```php
// Example in Plugin.php::bootstrap_modules()
private function bootstrap_modules(): void
{
    $this->modules = [
        // ... existing modules
        new YourNewModule(), // Add your new module here
    ];
}
```

Once added, the `Plugin` class will automatically call the `register()` method of your new module during the `plugins_loaded` WordPress action.

### How to Create a New Module

To create a new module for the `DionnieWPToolkit` plugin, follow these steps:

1.  **Define the Module Class**:
    Create a new PHP file (e.g., `YourNewModule.php`) within an appropriate directory (e.g., `includes/Modules/YourNewModule/`).

```php
<?php

declare(strict_types=1);

namespace DionnieWPToolkit\Core\Modules\YourNewModule; // Adjust namespace as needed

use DionnieWPToolkit\Core\Interfaces\Registerable;

class YourNewModule implements Registerable
{
    // Constructor (optional)
    public function __construct()
    {
        // Initialize module properties or dependencies
    }

    /**
     * Registers WordPress hooks and filters for this module.
     */
    public function register(): void
    {
        // Add your WordPress actions and filters here
        add_action('init', [$this, 'init_hook_callback']);
        add_filter('the_content', [$this, 'content_filter_callback']);
        // ... other hooks
    }

    // Example callback for an 'init' action
    public function init_hook_callback(): void
    {
        // Your module's initialization logic
        // e.g., register custom post types, taxonomies, etc.
    }

    // Example callback for 'the_content' filter
    public function content_filter_callback(string $content): string
    {
        // Modify content
        return $content . '<p>Added by YourNewModule!</p>';
    }

    // ... other methods for your module's functionality
}
```

2.  **Implement `Registerable` Interface**:
    As shown above, the class must `implement Registerable` and contain a `public function register(): void` method. This method is where all WordPress-specific hooks (actions and filters) for your module should be defined.

3.  **Add Functionality**:
    Inside your module class, define methods that will serve as callbacks for the WordPress hooks. These methods should contain the specific logic for your module's features.

4.  **Integrate with `Plugin.php`**:
    Finally, add an instance of `YourNewModule` to the `$this->modules` array in `DionnieWPToolkit\Core\Plugin::bootstrap_modules()`.

```php
// In d:\laragon\www\markdionniebulingit\wp-content\plugins\dionnie-wp-toolkit\includes\Plugin.php
// ...
private function bootstrap_modules(): void
{
    $this->modules = [
        // ... existing modules
        new \DionnieWPToolkit\Core\Modules\YourNewModule\YourNewModule(), // Make sure to use the correct namespace
    ];
}
// ...
```

By following these steps, your new module will be seamlessly integrated into the plugin's architecture and its `register()` method will be automatically called by the main `Plugin` class.
