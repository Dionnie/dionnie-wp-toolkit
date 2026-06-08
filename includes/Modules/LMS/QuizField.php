<?php
declare(strict_types=1);

namespace DionnieWPToolkit\Core\Modules\LMS;

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
        <li class="scf-quiz-row" style="display:flex;gap:10px;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #ccd0d4;">

            <label style="display:flex;gap:5px;align-items:center;">
                <input type="radio"
                       name="<?php echo $fieldName; ?>[correct]"
                       value="<?php echo $index; ?>"
                       <?php echo $checked; ?>>
                <span><?php esc_html_e('Correct', 'dionnie-wp'); ?></span>
            </label>

            <input type="text"
                   name="<?php echo $fieldName; ?>[choices][]"
                   value="<?php echo $value; ?>"
                   style="flex:1;"
                   placeholder="<?php esc_attr_e('Enter choice...', 'dionnie-wp'); ?>">

            <button type="button" class="button scf-quiz-remove">
                <span class="dashicons dashicons-trash"></span>
            </button>

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
                        <li class="scf-quiz-row" style="display:flex;gap:10px;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #ccd0d4;">
                            <label style="display:flex;gap:5px;">
                                <input type="radio" name="${fieldName}[correct]" value="${count}">
                                <span>Correct</span>
                            </label>

                            <input type="text" name="${fieldName}[choices][]" style="flex:1;" placeholder="Enter choice...">

                            <button type="button" class="button scf-quiz-remove">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </li>
                    `);
                });

                $(document).on('click', '.scf-quiz-remove', function() {
                    const list = $(this).closest('.scf-quiz-wrapper').find('.scf-quiz-list');

                    $(this).closest('.scf-quiz-row').remove();

                    list.find('.scf-quiz-row').each(function(i){
                        $(this).find('input[type="radio"]').val(i);
                    });
                });
            }

            acf.addAction('ready', initQuiz);

        })(jQuery);
        </script>
        <?php
    }
}