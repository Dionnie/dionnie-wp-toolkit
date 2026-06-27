<?php
declare(strict_types=1);

namespace DionnieWPToolkit\Core\Modules\ACF\CourseBuilderField;

class CourseBuilderField extends \acf_field
{
    public function initialize(): void
    {
        $this->name     = 'course_builder';
        $this->label    = __('Course Builder', 'dionnie-wp');
        $this->category = 'layout';
        $this->defaults = [
            'return_format' => 'array',
        ];
    }

    public function input_admin_enqueue(): void
    {
        $url     = plugin_dir_url(__FILE__); 
        $version = '1.0.0';

        wp_enqueue_script(
            'scf-course-builder-js', 
            $url . 'assets/js/input.js', 
            ['acf-input'], 
            $version, 
            true
        );
    }

    public function render_field(array $field): void
    {
        $value = is_array($field['value']) ? $field['value'] : [];
        $fieldName = esc_attr($field['name']);
        $jsonString = wp_json_encode($value);

        echo '<div class="scf-course-builder-wrapper" data-name="' . $fieldName . '">';
        
        // The hidden field that stores the serialized JSON data
        echo '<label style="display:block; margin-bottom: 5px; font-weight: bold; color: #d63638;">Debug Data (Master Field):</label>';
        echo '<input type="text" class="scf-course-hidden-data" name="' . $fieldName . '" value="' . esc_attr($jsonString) . '" style="width: 100%; margin-bottom: 15px; font-family: monospace; background: #f0f0f1;" readonly>';

        // Toolbar
        echo '<div class="scf-cb-toolbar" style="margin-bottom: 15px; display: flex; gap: 10px;">';
        echo '<button type="button" class="button button-primary scf-cb-add-lesson">' . esc_html__('Add Lesson', 'dionnie-wp') . '</button>';
        echo '</div>';

        // Container
        echo '<ul class="scf-cb-lessons-list" style="margin:0;padding:0;list-style:none;">';
        
        if (!empty($value)) {
            foreach ($value as $lesson) {
                $this->render_lesson($lesson);
            }
        }

        echo '</ul>';
        echo '</div>';
    }

    private function render_lesson(array $lesson): void
    {
        $title = esc_attr($lesson['title'] ?? '');
        $id = esc_attr($lesson['id'] ?? uniqid('lesson_'));
        ?>
        <li class="scf-cb-lesson-item" data-id="<?php echo $id; ?>" data-type="lesson" style="background:#fff; border:1px solid #ccd0d4; margin-bottom:10px; padding:10px;">
            <div class="scf-cb-header" style="display:flex; justify-content:space-between; align-items:center; background:#f9f9f9; padding:5px 10px; border:1px solid #eee;">
                <strong><?php esc_html_e('Lesson:', 'dionnie-wp'); ?></strong>
                <input type="text" class="scf-cb-title" value="<?php echo $title; ?>" placeholder="<?php esc_attr_e('Lesson Title', 'dionnie-wp'); ?>" style="flex:1; margin:0 10px;">
                <div class="scf-cb-actions" style="display:flex; gap:5px;">
                    <button type="button" class="button scf-cb-move-up" title="<?php esc_attr_e('Move Up', 'dionnie-wp'); ?>"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
                    <button type="button" class="button scf-cb-move-down" title="<?php esc_attr_e('Move Down', 'dionnie-wp'); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                    <button type="button" class="button scf-cb-add-topic"><?php esc_html_e('+ Topic', 'dionnie-wp'); ?></button>
                    <button type="button" class="button scf-cb-remove" title="<?php esc_attr_e('Remove', 'dionnie-wp'); ?>"><span class="dashicons dashicons-trash"></span></button>
                </div>
            </div>
            <ul class="scf-cb-children-list" style="min-height: 30px; padding-left: 20px; margin-top: 10px; border-left: 2px dashed #ddd;">
                <?php 
                if (!empty($lesson['children'])) {
                    foreach ($lesson['children'] as $child) {
                        if (($child['type'] ?? '') === 'topic') {
                            $this->render_topic($child);
                        }
                    }
                }
                ?>
            </ul>
        </li>
        <?php
    }

    private function render_topic(array $topic): void
    {
        $title = esc_attr($topic['title'] ?? '');
        $id = esc_attr($topic['id'] ?? uniqid('topic_'));
        ?>
        <li class="scf-cb-topic-item" data-id="<?php echo $id; ?>" data-type="topic" style="background:#fff; border:1px solid #ccd0d4; margin-bottom:10px; padding:10px;">
            <div class="scf-cb-header" style="display:flex; justify-content:space-between; align-items:center; background:#f1f1f1; padding:5px 10px; border:1px solid #e2e2e2;">
                <strong><?php esc_html_e('Topic:', 'dionnie-wp'); ?></strong>
                <input type="text" class="scf-cb-title" value="<?php echo $title; ?>" placeholder="<?php esc_attr_e('Topic Title', 'dionnie-wp'); ?>" style="flex:1; margin:0 10px;">
                <div class="scf-cb-actions" style="display:flex; gap:5px;">
                    <button type="button" class="button scf-cb-move-up" title="<?php esc_attr_e('Move Up', 'dionnie-wp'); ?>"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
                    <button type="button" class="button scf-cb-move-down" title="<?php esc_attr_e('Move Down', 'dionnie-wp'); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                    <button type="button" class="button scf-cb-remove" title="<?php esc_attr_e('Remove', 'dionnie-wp'); ?>"><span class="dashicons dashicons-trash"></span></button>
                </div>
            </div>
        </li>
        <?php
    }

    public function update_value($value, $post_id, array $field): array
    {
        if (is_string($value)) {
            $value = json_decode(wp_unslash($value), true);
        }
        if (!is_array($value)) return [];

        // Recursive sanitizer to ensure clean input
        $sanitize_items = function ($items) use (&$sanitize_items) {
            $clean = [];
            foreach ($items as $item) {
                if (!is_array($item)) continue;
                $clean_item = [
                    'id'    => sanitize_text_field($item['id'] ?? ''),
                    'type'  => sanitize_text_field($item['type'] ?? ''),
                    'title' => sanitize_text_field($item['title'] ?? ''),
                ];
                if (!empty($item['children']) && is_array($item['children'])) {
                    $clean_item['children'] = $sanitize_items($item['children']);
                }
                $clean[] = $clean_item;
            }
            return $clean;
        };
        return $sanitize_items($value);
    }
}