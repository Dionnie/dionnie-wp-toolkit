(function(e){if(typeof acf>`u`){console.error(`ACF API is not available. Ensure 'acf-input' is loaded.`);return}var t=acf.Field.extend({type:`quiz`,events:{"click .scf-quiz-add":`onClickAdd`,"click .scf-quiz-remove":`onClickRemove`,"click .scf-quiz-confirm-yes":`onConfirmDelete`,"click .scf-quiz-confirm-no":`onCancelDelete`,'input input[type="text"].scf-quiz-text':`onInputText`,'change input[type="radio"].scf-quiz-radio':`updateHiddenData`},$list:function(){return this.$(`.scf-quiz-list`)},initialize:function(){console.log(`QuizField prototype successfully bound to the DOM!`),this.updateHiddenData()},updateHiddenData:function(){var t={choices:[],correct:0};this.$(`.scf-quiz-row`).each(function(n){var r=e(this).find(`.scf-quiz-text`).val();t.choices.push(r),e(this).find(`.scf-quiz-radio`).is(`:checked`)&&(t.correct=n)}),this.$(`.scf-quiz-hidden-data`).val(JSON.stringify(t))},onClickAdd:function(e,t){e.preventDefault();var n=this.$(`.scf-quiz-row`).length,r=this.$(`.scf-quiz-wrapper`).data(`radio-group`),i=this.$list(),a=`
        <li class="scf-quiz-row" style="display:flex;gap:10px;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #ccd0d4;align-items:center;">
            <label style="display:flex;gap:5px;align-items:center;margin:0;">
                <input type="radio" class="scf-quiz-radio" name="${r}" value="${n}" required>
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
      `;i.append(a),this.updateHiddenData()},onClickRemove:function(e,t){t.hide(),t.siblings(`.scf-quiz-confirm-delete`).css(`display`,`flex`)},onConfirmDelete:function(t,n){n.closest(`.scf-quiz-row`).remove(),this.$(`.scf-quiz-row`).each(function(t){e(this).find(`input[type="radio"]`).val(t)}),this.updateHiddenData()},onCancelDelete:function(e,t){t.closest(`.scf-quiz-confirm-delete`).hide(),t.closest(`.scf-quiz-actions`).find(`.scf-quiz-remove`).show()},onInputText:function(t,n){e.trim(n.val())!==``&&n.removeClass(`scf-quiz-input-error`),this.updateHiddenData()}});acf.registerFieldType(t),e(function(){e(`.acf-field-quiz`).each(function(){e(this).removeData(`acf`),acf.newField(e(this))})})})(jQuery);
//# sourceMappingURL=acf-quiz-choices-CTHdaq4a.js.map