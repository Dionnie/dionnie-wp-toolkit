<?php
declare(strict_types=1);

namespace DionnieWPToolkit\Modules\LMS;

if (!class_exists('acf_field')) {
    return;
}

/**
 * Custom SCF/ACF Field Type: Quiz
 * Allows adding unlimited choices and selecting one correct answer.
 */
class QuizField extends \acf_field {

    public function initialize() {
        $this->name     = 'quiz';
        $this->label    = __('Quiz Choices', 'dionnie-wp');
        $this->category = 'choice';
        $this->defaults = [
            'return_format' => 'array',
        ];
    }

    /**
     * HTML content to show when rendering the field in the admin.
     *
     * @param array $field The field settings and values.
     */
    public function render_field(array $field): void {
        $value = is_array($field['value']) ? $field['value'] : [];
        $choices = isset($value['choices']) && is_array($value['choices']) ? $value['choices'] : [''];
        $correct = isset($value['correct']) ? (int) $value['correct'] : 0;
        
        $field_name = esc_attr($field['name']);
        
        echo '<div class="scf-quiz-wrapper" data-name="' . $field_name . '">';
        echo '  <ul class="scf-quiz-list" style="margin:0; padding:0; list-style:none;">';
        
        foreach ($choices as $index => $choice) {
            $is_checked = ($index === $correct) ? 'checked' : '';
            $this->render_choice_row($field_name, $index, esc_attr($choice), $is_checked);
        }
        
        echo '  </ul>';
        echo '  <button type="button" class="button button-primary scf-quiz-add" style="margin-top: 10px;">';
        echo '    ' . __('Add Choice', 'dionnie-wp');
        echo '  </button>';
        echo '</div>';
    }

    /**
     * Helper to render a single choice row.
     */
    private function render_choice_row(string $field_name, int $index, string $value = '', string $checked = ''): void {
        ?>
        <li class="scf-quiz-row" style="display:flex; align-items:center; gap:10px; margin-bottom:10px; background:#f9f9f9; padding:10px; border:1px solid #ccd0d4;">
            <label style="cursor:pointer; display:flex; align-items:center; gap:5px; margin:0;">
                <input type="radio" 
                       name="<?php echo $field_name; ?>[correct]" 
                       value="<?php echo $index; ?>" 
                       <?php echo $checked; ?> 
                       title="<?php esc_attr_e('Mark as correct answer', 'dionnie-wp'); ?>" />
                <span style="font-size:12px; color:#555;"><?php esc_html_e('Correct', 'dionnie-wp'); ?></span>
            </label>
            
            <input type="text" 
                   name="<?php echo $field_name; ?>[choices][]" 
                   value="<?php echo $value; ?>" 
                   placeholder="<?php esc_attr_e('Enter choice text...', 'dionnie-wp'); ?>" 
                   style="flex-grow:1;" />
            
            <button type="button" class="button scf-quiz-remove" title="<?php esc_attr_e('Remove Choice', 'dionnie-wp'); ?>">
                <span class="dashicons dashicons-trash" style="margin-top:4px;"></span>
            </button>
        </li>
        <?php
    }

    /**
     * Sanitize the value before saving it to the database.
     *
     * @param mixed $value The value to save.
     * @param int $post_id The post ID.
     * @param array $field The field array.
     * @return array
     */
    public function update_value($value, $post_id, array $field): array {
        if (!is_array($value)) {
            return ['choices' => [], 'correct' => 0];
        }
        
        $choices = [];
        if (!empty($value['choices']) && is_array($value['choices'])) {
            foreach ($value['choices'] as $choice) {
                $choices[] = sanitize_text_field($choice);
            }
        }
        
        // Ensure the correct answer falls within the actual bounds of choices
        $correct = isset($value['correct']) ? (int) $value['correct'] : 0;
        if ($correct < 0 || $correct >= count($choices)) {
            $correct = 0;
        }

        return [
            'choices' => $choices,
            'correct' => $correct,
        ];
    }

    /**
     * Enqueue CSS and JS required for the field in the admin.
     */
    public function input_admin_head(): void {
        ?>
        <style>
            .scf-quiz-wrapper .scf-quiz-row input[type="radio"] { margin-top: 0; }
            .scf-quiz-wrapper .scf-quiz-remove { color: #d63638; border-color: #d63638; }
            .scf-quiz-wrapper .scf-quiz-remove:hover { background: #d63638; color: #fff; border-color: #d63638; }
        </style>
        <script type="text/javascript">
            (function($) {
                if (typeof acf === 'undefined') { return; }
                
                function initialize_quiz_field() {
                    // Add new choice
                    $(document).on('click', '.scf-quiz-add', function(e) {
                        e.preventDefault();
                        var wrapper = $(this).closest('.scf-quiz-wrapper');
                        var list = wrapper.find('.scf-quiz-list');
                        var fieldName = wrapper.data('name');
                        var count = list.find('.scf-quiz-row').length;
                        
                        var newRow = `
                            <li class="scf-quiz-row" style="display:flex; align-items:center; gap:10px; margin-bottom:10px; background:#f9f9f9; padding:10px; border:1px solid #ccd0d4;">
                                <label style="cursor:pointer; display:flex; align-items:center; gap:5px; margin:0;">
                                    <input type="radio" name="${fieldName}[correct]" value="${count}" />
                                    <span style="font-size:12px; color:#555;">Correct</span>
                                </label>
                                <input type="text" name="${fieldName}[choices][]" value="" placeholder="Enter choice text..." style="flex-grow:1;" />
                                <button type="button" class="button scf-quiz-remove" title="Remove Choice">
                                    <span class="dashicons dashicons-trash" style="margin-top:4px;"></span>
                                </button>
                            </li>
                        `;
                        list.append(newRow);
                    });
                    
                    // Remove choice
                    $(document).on('click', '.scf-quiz-remove', function(e) {
                        e.preventDefault();
                        var wrapper = $(this).closest('.scf-quiz-wrapper');
                        var list = wrapper.find('.scf-quiz-list');
                        
                        $(this).closest('.scf-quiz-row').remove();
                        
                        // Re-index the radio buttons to map correctly to the remaining choices
                        list.find('.scf-quiz-row').each(function(index) {
                            $(this).find('input[type="radio"]').val(index);
                        });
                    });
                }
                
                acf.addAction('ready', initialize_quiz_field);
            })(jQuery);
        </script>
        <?php
    }
}
