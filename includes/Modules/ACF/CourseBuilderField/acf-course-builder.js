(function ($) {
  if (typeof acf === "undefined") return;

  function initializeCourseBuilder(field) {
    const $field = field.$el ? field.$el : $(field);
    const $wrapper = $field.find(".scf-course-builder-wrapper");
    const $hiddenInput = $wrapper.find(".scf-course-hidden-data");

    // Reads the DOM tree and dumps it into the JSON field
    function updateJSON() {
      const data = [];

      $wrapper
        .find(".scf-cb-lessons-list > .scf-cb-lesson-item")
        .each(function () {
          const $lesson = $(this);
          const lessonData = {
            id: $lesson.data("id"),
            type: "lesson",
            title: $lesson.find("> .scf-cb-header .scf-cb-title").val(),
            children: [],
          };

          $lesson.find("> .scf-cb-children-list > li").each(function () {
            const $child = $(this);
            const type = $child.data("type");
            const childData = {
              id: $child.data("id"),
              type: type,
              title: $child.find("> .scf-cb-header .scf-cb-title").val(),
            };

            lessonData.children.push(childData);
          });
          data.push(lessonData);
        });

      $hiddenInput.val(JSON.stringify(data));
    }

    // Generic Item Generator
    function createHtmlTemplate(type, label, headerBg) {
      const id = type + "_" + Date.now();
      return `
            <li class="scf-cb-${type}-item" data-id="${id}" data-type="${type}" style="background:#fff; border:1px solid #ccd0d4; margin-bottom:10px; padding:10px;">
                <div class="scf-cb-header" style="display:flex; justify-content:space-between; align-items:center; background:${headerBg}; padding:5px 10px; border:1px solid #e2e2e2;">
                    <strong>${label}:</strong>
                    <input type="text" class="scf-cb-title" value="" placeholder="${label} Title" style="flex:1; margin:0 10px;">
                    <div class="scf-cb-actions" style="display:flex; gap:5px;">
                        <button type="button" class="button scf-cb-move-up" title="Move Up"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
                        <button type="button" class="button scf-cb-move-down" title="Move Down"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                        ${type === "lesson" ? '<button type="button" class="button scf-cb-add-topic">+ Topic</button>' : ""}
                        <button type="button" class="button scf-cb-remove" title="Remove"><span class="dashicons dashicons-trash"></span></button>
                    </div>
                </div>
                ${type === "lesson" ? `<ul class="scf-cb-children-list" style="min-height: 30px; padding-left: 20px; margin-top: 10px; border-left: 2px dashed #ddd;"></ul>` : ""}
            </li>`;
    }

    // Event Bindings
    $wrapper.on("change keyup", ".scf-cb-title", updateJSON);
    $wrapper.on("click", ".scf-cb-remove", function () {
      $(this).closest("li").remove();
      updateJSON();
    });

    $wrapper.on("click", ".scf-cb-move-up", function () {
      const $item = $(this).closest("li");
      const $prev = $item.prev("li");
      if ($prev.length) {
        $item.insertBefore($prev);
        updateJSON();
      }
    });

    $wrapper.on("click", ".scf-cb-move-down", function () {
      const $item = $(this).closest("li");
      const $next = $item.next("li");
      if ($next.length) {
        $item.insertAfter($next);
        updateJSON();
      }
    });

    $wrapper.on("click", ".scf-cb-add-lesson", function () {
      $wrapper
        .find("> .scf-cb-lessons-list")
        .append(createHtmlTemplate("lesson", "Lesson", "#f9f9f9"));
      updateJSON();
    });
    $wrapper.on("click", ".scf-cb-add-topic", function () {
      $(this)
        .closest("li")
        .find("> .scf-cb-children-list")
        .append(createHtmlTemplate("topic", "Topic", "#f1f1f1"));
      updateJSON();
    });
  }

  // Register with ACF
  acf.addAction("append_field/type=course_builder", initializeCourseBuilder);
  acf.addAction("ready_field/type=course_builder", initializeCourseBuilder);
})(jQuery);
