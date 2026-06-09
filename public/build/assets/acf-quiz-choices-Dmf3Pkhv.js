(function(e){if(typeof acf>`u`){console.error(`ACF API is not available. Ensure 'acf-input' is loaded.`);return}var t=acf.Field.extend({type:`quiz`,events:{"click .scf-quiz-add":`onClickAdd`,"click .scf-quiz-remove":`onClickRemove`,"click .scf-quiz-confirm-yes":`onConfirmDelete`,"click .scf-quiz-confirm-no":`onCancelDelete`,'input input[type="text"]':`onInputText`},$list:function(){return this.$(`.scf-quiz-list`)},initialize:function(){},onClickAdd:function(e,t){e.preventDefault();var n=this.$(`.scf-quiz-row`).length,r=this.$(`.scf-quiz-wrapper`).data(`name`),i=this.$list(),a=`
        <li class="scf-quiz-row" style="display:flex;gap:10px;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #ccd0d4;align-items:center;">
            <label style="display:flex;gap:5px;align-items:center;margin:0;">
                <input type="radio" name="${r}[correct]" value="${n}" required>
                <span>Correct</span>
            </label>
            <input type="text" name="${r}[choices][]" style="flex:1;margin:0;" placeholder="Enter choice..." required>
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
      `;i.append(a)},onClickRemove:function(e,t){t.hide(),t.siblings(`.scf-quiz-confirm-delete`).css(`display`,`flex`)},onConfirmDelete:function(t,n){n.closest(`.scf-quiz-row`).remove(),this.$(`.scf-quiz-row`).each(function(t){e(this).find(`input[type="radio"]`).val(t)})},onCancelDelete:function(e,t){t.closest(`.scf-quiz-confirm-delete`).hide(),t.closest(`.scf-quiz-actions`).find(`.scf-quiz-remove`).show()},onInputText:function(t,n){e.trim(n.val())!==``&&n.removeClass(`scf-quiz-input-error`)}});acf.registerFieldType(t),e(function(){e(`.acf-field-quiz`).each(function(){acf.getInstance(e(this))||acf.newField(e(this))})})})(jQuery);
//# sourceMappingURL=acf-quiz-choices-Dmf3Pkhv.js.map