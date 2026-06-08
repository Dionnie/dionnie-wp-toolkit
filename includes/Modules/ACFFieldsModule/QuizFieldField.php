<?php
declare(strict_types=1);

namespace DionnieWPToolkit\Core\Modules\ACFFieldsModule;

/**
 * Actual ACF Field Type
 */
class QuizFieldField extends \acf_field
{
    public function initialize(): void
    {
        $this->name     = 'quiz';
        $this->label    = __('Quiz Choices', 'dionnie-wp');
        $this->category = 'choice';

        $this->defaults = [
            'return_format' => 'array',
        ];
    }

    public function render_field(array $field): void
    {
        $value = is_array($field['value']) ? $field['value'] : [];

        $choices = $value['choices'] ?? [''];
        $correct = (int) ($value['correct'] ?? 0);

        $fieldName = esc_attr($field['name']);

        echo '<div class="scf-quiz-wrapper" data-name="' . $fieldName . '">';
        echo '<ul class="scf-quiz-list" style="margin:0;padding:0;list-style:none;">';

        foreach ($choices as $index => $choice) {
            $this->renderRow(
                $fieldName,
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
        string $fieldName,
        int $index,
        string $value,
        string $checked
    ): void {
        ?>
        <li class="scf-quiz-row" style="display:flex;gap:10px;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #ccd0d4;align-items:center;">

            <label style="display:flex;gap:5px;align-items:center;margin:0;">
                <input type="radio"
                       name="<?php echo $fieldName; ?>[correct]"
                       value="<?php echo $index; ?>"
                       <?php echo $checked; ?>
                       required>
                <span><?php esc_html_e('Correct', 'dionnie-wp'); ?></span>
            </label>

            <input type="text"
                   name="<?php echo $fieldName; ?>[choices][]"
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

    public function input_admin_head(): void
    {
        ?>
        <style>
            .scf-quiz-remove {
                color: #d63638;
                border-color: #d63638;
            }
            .scf-quiz-remove:hover {
                background: #d63638;
                color: #fff;
            }
            .scf-quiz-confirm-yes:hover {
                background: #d63638;
                color: #fff !important;
            }
        .scf-quiz-input-error {
            border-color: #d63638 !important;
            box-shadow: 0 0 0 1px #d63638 !important;
        }
        </style>

        <script>
        (function($){

            function initQuiz() {

                $(document).on('click', '.scf-quiz-add', function(e) {
                    e.preventDefault();

                    const wrapper = $(this).closest('.scf-quiz-wrapper');
                    const list = wrapper.find('.scf-quiz-list');
                    const fieldName = wrapper.data('name');
                    const count = list.find('.scf-quiz-row').length;

                    list.append(`
                        <li class="scf-quiz-row" style="display:flex;gap:10px;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #ccd0d4;align-items:center;">
                            <label style="display:flex;gap:5px;align-items:center;margin:0;">
                                <input type="radio" name="${fieldName}[correct]" value="${count}" required>
                                <span>Correct</span>
                            </label>

                            <input type="text" name="${fieldName}[choices][]" style="flex:1;margin:0;" placeholder="Enter choice..." required>

                            <div class="scf-quiz-actions" style="display:flex; gap:5px;">
                                <button type="button" class="button scf-quiz-remove">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                                <div class="scf-quiz-confirm-delete" style="display:none; gap:5px;">
                                    <button type="button" class="button scf-quiz-confirm-yes" style="border-color:#d63638; color:#d63638;">Delete</button>
                                    <button type="button" class="button scf-quiz-confirm-no">Cancel</button>
                                </div>
                            </div>
                        </li>
                    `);
                });

                $(document).on('click', '.scf-quiz-remove', function() {
                    $(this).hide();
                    $(this).siblings('.scf-quiz-confirm-delete').css('display', 'flex');
                });

                $(document).on('click', '.scf-quiz-confirm-no', function() {
                    $(this).closest('.scf-quiz-confirm-delete').hide();
                    $(this).closest('.scf-quiz-actions').find('.scf-quiz-remove').show();
                });

                $(document).on('click', '.scf-quiz-confirm-yes', function() {
                    const list = $(this).closest('.scf-quiz-wrapper').find('.scf-quiz-list');
                    $(this).closest('.scf-quiz-row').remove();
                    
                    list.find('.scf-quiz-row').each(function(i){
                        $(this).find('input[type="radio"]').val(i);
                    });
                });
            }

        if (typeof acf !== 'undefined') {
            acf.addAction('ready', initQuiz);

            acf.addAction('invalid_field', function(field) {
                if (field.$('.scf-quiz-wrapper').length) {
                    field.$('.scf-quiz-row input[type="text"]').each(function() {
                        if ($.trim($(this).val()) === '') {
                            $(this).addClass('scf-quiz-input-error');
                        }
                    });
                }
            });

            acf.addAction('validation_begin', function() {
                $('.scf-quiz-input-error').removeClass('scf-quiz-input-error');
            });
        }

        $(document).on('input', '.scf-quiz-row input[type="text"]', function() {
            if ($.trim($(this).val()) !== '') {
                $(this).removeClass('scf-quiz-input-error');
            }
        });

        })(jQuery);
        </script>
        <?php
    }
}