(function ($) {
  if (typeof acf === "undefined") {
    console.error("ACF API is not available. Ensure 'acf-input' is loaded.");
    return;
  }

  var QuizField = acf.Field.extend({
    type: "quiz",

    events: {
      "click .scf-quiz-add": "onClickAdd",
      "click .scf-quiz-remove": "onClickRemove",
      "click .scf-quiz-confirm-yes": "onConfirmDelete",
      "click .scf-quiz-confirm-no": "onCancelDelete",
      'input input[type="text"].scf-quiz-text': "onInputText",
      'change input[type="radio"].scf-quiz-radio': "updateHiddenData",
    },

    $list: function () {
      return this.$(".scf-quiz-list");
    },

    initialize: function () {
      console.log("QuizField prototype successfully bound to the DOM!");
      // Ensure the hidden/debug data matches the DOM on load
      this.updateHiddenData();
    },

    updateHiddenData: function () {
      var data = {
        choices: [],
        correct: 0,
      };

      this.$(".scf-quiz-row").each(function (i) {
        var textVal = $(this).find(".scf-quiz-text").val();
        data.choices.push(textVal);

        if ($(this).find(".scf-quiz-radio").is(":checked")) {
          data.correct = i;
        }
      });

      // Update the main submission field with the JSON string
      this.$(".scf-quiz-hidden-data").val(JSON.stringify(data));
    },

    onClickAdd: function (e, $el) {
      e.preventDefault();
      var count = this.$(".scf-quiz-row").length;
      var radioGroup = this.$(".scf-quiz-wrapper").data("radio-group");
      var $list = this.$list();

      var template = `
        <li class="scf-quiz-row" style="display:flex;gap:10px;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #ccd0d4;align-items:center;">
            <label style="display:flex;gap:5px;align-items:center;margin:0;">
                <input type="radio" class="scf-quiz-radio" name="${radioGroup}" value="${count}" required>
                <span>Correct</span>
            </label>
            <input type="text" class="scf-quiz-text" style="flex:1;margin:0;" placeholder="Enter choice..." required>
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
      `;

      $list.append(template);
      this.updateHiddenData();
    },

    onClickRemove: function (e, $el) {
      $el.hide();
      $el.siblings(".scf-quiz-confirm-delete").css("display", "flex");
    },

    onConfirmDelete: function (e, $el) {
      var $row = $el.closest(".scf-quiz-row");
      $row.remove();

      // Re-index radio buttons
      this.$(".scf-quiz-row").each(function (i) {
        $(this).find('input[type="radio"]').val(i);
      });

      this.updateHiddenData();
    },

    onCancelDelete: function (e, $el) {
      $el.closest(".scf-quiz-confirm-delete").hide();
      $el.closest(".scf-quiz-actions").find(".scf-quiz-remove").show();
    },

    onInputText: function (e, $el) {
      if ($.trim($el.val()) !== "") {
        $el.removeClass("scf-quiz-input-error");
      }
      this.updateHiddenData();
    },
  });

  acf.registerFieldType(QuizField);

  $(function () {
    $(".acf-field-quiz").each(function () {
      $(this).removeData("acf");
      acf.newField($(this));
    });
  });
})(jQuery);
