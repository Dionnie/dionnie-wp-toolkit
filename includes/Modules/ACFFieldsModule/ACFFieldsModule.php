<?php 
namespace DionnieWPToolkit\Core\Modules\ACFFieldsModule;

use DionnieWPToolkit\Core\Interfaces\Registerable;

class AcfFieldsModule implements Registerable
{
    public function register(): void
    {
        add_action('acf/include_field_types', [$this, 'boot']);
    }

    public function boot(): void
    {
        if (!class_exists('\acf_field')) {
            return;
        }

        // Register ALL fields here
        new \DionnieWPToolkit\Core\Modules\ACFFieldsModule\QuizFieldField();
    
    }
}

?>