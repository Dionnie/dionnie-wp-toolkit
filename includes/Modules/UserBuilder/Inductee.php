<?php

declare(strict_types=1);

namespace DionnieWPToolkit\Wp\Inductee;

use DionnieWPToolkit\Core\Interfaces\Registerable;

/**
 * Manages the 'Inductee' custom user role and profile fields.
 */
class Inductee implements Registerable
{
    /**
     * Registers the UserBuilder for the Inductee role.
     */
    public function register(): void
    {
        $roles_to_add = [
            'inductee' => [
                'display_name' => __('Inductee', 'dionnie-wp'),
                'capabilities' => [
                    'read' => true,
                ],
            ],
        ];

        $profile_fields = [
            'company_id' => [
                'label' => __('Company ID', 'dionnie-wp'),
                'description' => __('Enter the company-issued identification number.', 'dionnie-wp'),
            ],
            'valid_id' => [
                'label' => __('Valid ID', 'dionnie-wp'),
                'description' => __('Specify the type of valid ID submitted (e.g., Driver\'s License, Passport).', 'dionnie-wp'),
            ],
            'other_work_requirements' => [
                'label' => __('Other Work Requirements', 'dionnie-wp'),
                'description' => __('List any other work-related requirements or certifications.', 'dionnie-wp'),
            ],
        ];

        $user_builder = new UserBuilder($roles_to_add, $profile_fields);
        $user_builder->register();
    }
}
