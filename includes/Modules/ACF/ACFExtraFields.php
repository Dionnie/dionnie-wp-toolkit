<?php 
namespace DionnieWPToolkit\Core\Modules\ACF;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Modules\ACF\QuizChoiceField\QuizChoiceField;


class ACFExtraFields implements Registerable
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
        new QuizChoiceField();
    
    }
}
