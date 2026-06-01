<?php
declare(strict_types=1);

namespace Dionnie\Wp\Users;

/**
 * Manages custom user roles, capabilities, and profile fields.
 */
class UserSetup
{
    /**
     * @var array<string, array{display_name: string, capabilities: array<string, bool>}>
     */
    private array $roles_to_add;

    /**
     * @var array<string, array{label: string, description: string}>
     */
    private array $profile_fields;

    /**
     * UserSetup constructor.
     *
     * @param array<string, array{display_name: string, capabilities: array<string, bool>}> $roles
     * @param array<string, array{label: string, description: string}> $fields
     */
    public function __construct(array $roles, array $fields)
    {
        $this->roles_to_add = $roles;
        $this->profile_fields = $fields;
    }

    /**
     * Registers WordPress hooks.
     */
    public function register_hooks(): void
    {
        add_action('init', [$this, 'create_roles']);

        add_action('show_user_profile', [$this, 'add_custom_profile_fields']);
        add_action('edit_user_profile', [$this, 'add_custom_profile_fields']);

        add_action('personal_options_update', [$this, 'save_custom_profile_fields']);
        add_action('edit_user_profile_update', [$this, 'save_custom_profile_fields']);
    }

    /**
     * Creates the custom user roles. Should be called on plugin activation.
     */
    public function create_roles(): void
    {
        foreach ($this->roles_to_add as $role_slug => $role_data) {
            if (!get_role($role_slug)) {
                add_role($role_slug, $role_data['display_name'], $role_data['capabilities']);
            }
        }
    }

    /**
     * Displays custom profile fields on the user profile page.
     *
     * @param \WP_User $user The user object.
     */
    public function add_custom_profile_fields(\WP_User $user): void
    {
        // Check if the user has any of the roles managed by this class
        $allowed_roles = array_keys($this->roles_to_add);
        if (empty(array_intersect($allowed_roles, $user->roles))) {
            return;
        }

        ?>
        <h2><?php esc_html_e('Additional Information', 'dionnie-wp'); ?></h2>
        <table class="form-table">
            <?php foreach ($this->profile_fields as $meta_key => $field_data) : ?>
                <tr>
                    <th><label for="<?php echo esc_attr($meta_key); ?>"><?php echo esc_html($field_data['label']); ?></label></th>
                    <td>
                        <input type="text"
                               name="<?php echo esc_attr($meta_key); ?>"
                               id="<?php echo esc_attr($meta_key); ?>"
                               value="<?php echo esc_attr(get_user_meta($user->ID, $meta_key, true)); ?>"
                               class="regular-text" />
                        <p class="description"><?php echo esc_html($field_data['description']); ?></p>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    /**
     * Saves the custom profile fields.
     *
     * @param int $user_id The user ID.
     */
    public function save_custom_profile_fields(int $user_id): void
    {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }

        // Ensure we only save these fields for the assigned roles
        $allowed_roles = array_keys($this->roles_to_add);
        if (empty(array_intersect($allowed_roles, $user->roles))) {
            return;
        }

        foreach ($this->profile_fields as $meta_key => $field_data) {
            if (isset($_POST[$meta_key])) {
                update_user_meta($user_id, $meta_key, sanitize_text_field($_POST[$meta_key]));
            }
        }
    }
}