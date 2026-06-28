<?php

declare(strict_types=1);

namespace DionnieWPToolkit\Core\Modules\ACF\QuizChoiceField;


class QuizChoiceField extends \acf_field
{
    /**
     * @var string The field name.
     */
    public $name;
    /**
     * @var string The field label.
     */
    public $label;
    /**
     * @var string The field category.
     */
    public $category;
    /**
     * @var array The field defaults.
     */
    public $defaults;

    public function initialize(): void
    {
        $this->name     = 'quiz';
        $this->label    = __('Quiz Choices', 'dionnie-wp');
        $this->category = 'choice';

        $this->defaults = [
            'return_format' => 'array',
        ];
    }

    public function input_admin_enqueue(): void
    {
        $url     = plugin_dir_url(__FILE__);
        $version = '1.0.0';

        wp_enqueue_style('scf-quiz-field-css', $url . 'assets/css/input.css', [], $version);
        wp_enqueue_script(
            'scf-quiz-field-js',
            $url . 'assets/js/input.js',
            ['acf-input'],
            $version,
            true
        );
    }

    public function render_field(array $field): void
    {
        // Ensure value is properly formatted for rendering
        $value = is_array($field['value']) ? $field['value'] : [];
        $choices = $value['choices'] ?? [''];
        $correct = (int) ($value['correct'] ?? 0);

        $fieldName = esc_attr($field['name']);

        // Create a unique name for the radio group so it groups correctly 
        // without interfering with the ACF submission
        $radioGroup = 'scf_quiz_radio_' . esc_attr($field['key']);
        $jsonString = wp_json_encode($value);

        echo '<div class="scf-quiz-wrapper" data-name="' . $fieldName . '" data-radio-group="' . $radioGroup . '">';

        // This is the actual field ACF will look at during form submission.
        // It is currently type="text" and visible for debugging as requested.
        echo '<label style="display:block; margin-bottom: 5px; font-weight: bold; color: #d63638;">Debug Data (Master Field):</label>';
        echo '<input type="text" class="scf-quiz-hidden-data" name="' . $fieldName . '" value="' . esc_attr($jsonString) . '" style="width: 100%; margin-bottom: 15px; font-family: monospace; background: #f0f0f1;" readonly>';

        echo '<ul class="scf-quiz-list" style="margin:0;padding:0;list-style:none;">';

        foreach ($choices as $index => $choice) {
            $this->renderRow(
                $radioGroup,
                $index,
                esc_attr($choice),
                $index === $correct ? 'checked' : ''
            );
        }

        echo '</ul>';
        echo '<button type="button" class="button button-primary scf-quiz-add" style="margin-top:10px;">';
        echo esc_html__('Add Choice', 'dionnie-wp');
        echo '</button>';
        echo '</div>';
    }

    private function renderRow(
        string $radioGroup,
        int $index,
        string $value,
        string $checked
    ): void {
?>
        <li class="scf-quiz-row" style="display:flex;gap:10px;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #ccd0d4;align-items:center;">

            <label style="display:flex;gap:5px;align-items:center;margin:0;">
                <input type="radio"
                    class="scf-quiz-radio"
                    name="<?php echo $radioGroup; ?>"
                    value="<?php echo $index; ?>"
                    <?php echo $checked; ?>
                    required>
                <span><?php esc_html_e('Correct', 'dionnie-wp'); ?></span>
            </label>

            <input type="text"
                class="scf-quiz-text"
                value="<?php echo $value; ?>"
                style="flex:1;margin:0;"
                placeholder="<?php esc_attr_e('Enter choice...', 'dionnie-wp'); ?>"
                required>

            <div class="scf-quiz-actions" style="display:flex; gap:5px;">
                <button type="button" class="button scf-quiz-remove">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <div class="scf-quiz-confirm-delete" style="display:none; gap:5px;">
                    <button type="button" class="button scf-quiz-confirm-yes" style="border-color:#d63638; color:#d63638;"><?php esc_html_e('Delete', 'dionnie-wp'); ?></button>
                    <button type="button" class="button scf-quiz-confirm-no"><?php esc_html_e('Cancel', 'dionnie-wp'); ?></button>
                </div>
            </div>

        </li>
<?php
    }

    public function update_value($value, $post_id, array $field): array
    {
        // Decode the JSON string sent from the master debug field
        if (is_string($value)) {
            $value = json_decode(wp_unslash($value), true);
        }

        if (!is_array($value)) {
            return ['choices' => [], 'correct' => 0];
        }

        $choices = [];
        foreach (($value['choices'] ?? []) as $choice) {
            $choices[] = sanitize_text_field($choice);
        }

        $correct = (int) ($value['correct'] ?? 0);

        if ($correct < 0 || $correct >= count($choices)) {
            $correct = 0;
        }

        return [
            'choices' => $choices,
            'correct' => $correct,
        ];
    }

    public function validate_value($valid, $value, $field, $input)
    {
        // Decode the JSON string before validation
        if (is_string($value)) {
            $value = json_decode(wp_unslash($value), true);
        }

        if (!is_array($value) || empty($value['choices'])) {
            return __('At least one choice is required.', 'dionnie-wp');
        }

        foreach ($value['choices'] as $choice) {
            if (trim((string)$choice) === '') {
                return __('All choice fields must have a text value.', 'dionnie-wp');
            }
        }

        if (!isset($value['correct']) || $value['correct'] === '') {
            return __('Please select the correct answer.', 'dionnie-wp');
        }

        return $valid;
    }
}
