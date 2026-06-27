<?php

declare(strict_types=1);

namespace DionnieWPToolkit\Wp\Users;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Modules\UserBuilder\UserBuilder;

/**
 * Manages custom user roles, capabilities, and profile fields.
 */
class Users implements Registerable
{

    public function register(): void {}

    public function create_shop_manager()
    {
        $roles_to_add = [
            'shop_manager_lite' => [
                'display_name' => __('Shop Manager Lite', 'dionnie-wp'),
                'capabilities' => [
                    'read' => true,
                    'edit_posts' => true,
                    'delete_posts' => false,
                ],
            ],
        ];

        $profile_fields = [
            'employee_id' => [
                'label' => __('Employee ID', 'dionnie-wp'),
                'description' => __('Enter the employee identification number.', 'dionnie-wp'),
            ],
            'department' => [
                'label' => __('Department', 'dionnie-wp'),
                'description' => __("Enter the user's department.", 'dionnie-wp'),
            ],
        ];

        $user_setup = new UserSetup($roles_to_add, $profile_fields);
    }
}
