'use strict';
const adminNavigation = document.querySelector('.admin-navigation');
if (adminNavigation) {
    const wideAdmin = window.matchMedia('(min-width: 64.001rem)');
    const summary = adminNavigation.querySelector('summary');
    let summaryFocused = false;
    summary.addEventListener('focus', () => { summaryFocused = true; });
    summary.addEventListener('blur', event => {
        // CSS hides the summary before the media-query callback runs on desktop.
        if (event.relatedTarget || !wideAdmin.matches) summaryFocused = false;
    });
    const setNavigation = () => {
        const focused = document.activeElement;
        if (wideAdmin.matches) {
            // Open the disclosure before focusing a link inside it.
            adminNavigation.open = true;
            if (focused === summary || (summaryFocused && focused === document.body)) {
                adminNavigation.querySelector('[aria-current="page"]')?.focus();
            }
            summaryFocused = false;
        } else {
            // Return focus before hiding desktop navigation on a smaller screen.
            if (adminNavigation.querySelector('nav').contains(focused)) summary.focus();
            adminNavigation.open = false;
        }
    };
    setNavigation(); wideAdmin.addEventListener('change', setNavigation);
    adminNavigation.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !wideAdmin.matches) { adminNavigation.open = false; adminNavigation.querySelector('summary').focus(); }
    });
}
const blockRoot = document.querySelector('#content-blocks');
const addBlock = document.querySelector('#add-block');
if (blockRoot && addBlock) {
    let serial = blockRoot.children.length;
    function enhance(block) {
        const area = block.querySelector('textarea');
        const toolbar = document.createElement('div'); toolbar.className = 'actions';
        for (const [label, start, end] of [['Bold', '**', '**'], ['Italic', '*', '*'], ['Link', '[', ']']]) {
            const button = document.createElement('button'); button.type = 'button'; button.textContent = label;
            button.addEventListener('click', () => {
                const selection = area.value.slice(area.selectionStart, area.selectionEnd);
                if (!selection) { area.focus(); return; }
                let suffix = end;
                if (label === 'Link') {
                    const link = window.prompt('Enter a full https:// address or mailto: email link.');
                    if (!link || !/^(https?:\/\/|mailto:)/i.test(link) || /[\s)]/.test(link)) return;
                    suffix += '(' + link + ')';
                }
                area.setRangeText(start + selection + suffix, area.selectionStart, area.selectionEnd, 'select'); area.dispatchEvent(new Event('input', {bubbles:true})); area.focus();
            }); toolbar.append(button);
        }
        const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = 'Remove block';
        remove.addEventListener('click', () => {
            if (!area.value || window.confirm('Remove this content block?')) {
                const target = block.nextElementSibling?.querySelector('textarea') || block.previousElementSibling?.querySelector('textarea') || document.querySelector('#add-block');
                block.remove(); target.focus(); blockRoot.dispatchEvent(new Event('input', {bubbles:true}));
            }
        });
        for (const [label, direction] of [['Move up', -1], ['Move down', 1]]) {
            const move = document.createElement('button'); move.type = 'button'; move.textContent = label;
            move.addEventListener('click', () => {
                const adjacent = direction < 0 ? block.previousElementSibling : block.nextElementSibling;
                if (adjacent) { direction < 0 ? blockRoot.insertBefore(block, adjacent) : blockRoot.insertBefore(adjacent, block); move.focus(); blockRoot.dispatchEvent(new Event('input', {bubbles:true})); }
            }); toolbar.append(move);
        }
        toolbar.append(remove); block.append(toolbar);
    }
    [...blockRoot.children].forEach(enhance);
    const add = addBlock; add.hidden = false;
    add.addEventListener('click', () => {
        if (blockRoot.children.length >= 80) return;
        const block = document.createElement('fieldset'); block.className = 'content-block';
        block.innerHTML = '<legend>Content block</legend><label>Block type<select><option value="p">Paragraph</option><option value="h2">Section heading</option><option value="h3">Subsection heading</option><option value="ul">Bullet list</option><option value="ol">Numbered list</option></select></label><label>Text<textarea rows="5" maxlength="10000"></textarea></label>';
        block.querySelector('select').name = `blocks[${serial}][type]`; block.querySelector('textarea').name = `blocks[${serial++}][text]`;
        enhance(block); blockRoot.append(block); block.querySelector('textarea').focus(); blockRoot.dispatchEvent(new Event('input', {bubbles:true}));
    });
}

// Course lists are independent of the article block editor.
document.querySelectorAll('.repeatable').forEach(group => {
    const root = group.querySelector('.repeatable-items');
    const add = group.querySelector('[data-add-item]');
    const status = group.querySelector('[data-list-status]');
    let serial = root.children.length;
    const refresh = () => { add.disabled = root.children.length >= 80; };
    const enhance = row => {
        const remove = row.querySelector('[data-remove-item]');
        remove.hidden = false;
        remove.addEventListener('click', () => {
            const focus = row.nextElementSibling?.querySelector('textarea') || row.previousElementSibling?.querySelector('textarea') || add;
            row.remove(); refresh(); focus.focus(); root.dispatchEvent(new Event('input', {bubbles:true})); status.textContent = 'Item removed. Save to apply changes.';
        });
    };
    [...root.children].forEach(enhance); add.hidden = false; refresh();
    add.addEventListener('click', () => {
        if (root.children.length >= 80) return;
        const row = document.createElement('div'); row.className = 'repeatable-row';
        const label = document.createElement('label'); label.textContent = 'Item ' + (serial + 1);
        const area = document.createElement('textarea'); area.name = `${group.dataset.listName}[${serial++}]`; area.rows = 2; area.maxLength = 10000;
        const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = 'Remove'; remove.dataset.removeItem = '';
        label.append(area); row.append(label, remove); root.append(row); enhance(row); refresh(); area.focus();
        status.textContent = 'Item added.'; root.dispatchEvent(new Event('input', {bubbles:true}));
    });
});

// Show the allowlisted saved-image selection; file uploads are still validated by PHP.
document.querySelectorAll('[data-image-picker]').forEach(picker => {
    const select = picker.querySelector('select'); const image = picker.querySelector('[data-image-preview]');
    select.addEventListener('change', () => {
        if (select.value) { image.src = '../' + select.value; image.hidden = false; }
        else { image.hidden = true; image.removeAttribute('src'); }
    });
});
// One form and the existing field names; hidden steps still submit normally.
const courseWizard = document.querySelector('[data-course-wizard]');
if (courseWizard) {
    const steps = [...courseWizard.querySelectorAll('[data-course-step]')];
    const nav = document.createElement('nav'); nav.className = 'course-steps'; nav.setAttribute('aria-label', 'Course editing steps');
    const progress = document.createElement('p'); progress.className = 'help'; progress.setAttribute('role', 'status');
    const controls = document.createElement('div'); controls.className = 'actions wizard-navigation';
    const previous = document.createElement('button'); previous.type = 'button'; previous.textContent = 'Previous';
    const next = document.createElement('button'); next.type = 'button'; next.textContent = 'Next';
    controls.append(previous, next); steps.at(-1).after(controls); steps[0].before(nav, progress);
    let current = 0;
    const review = () => {
        const root = courseWizard.querySelector('[data-course-review]'); root.replaceChildren();
        for (const step of steps.slice(0, -1)) {
            const title = document.createElement('h2'); title.textContent = step.querySelector('legend').textContent;
            const list = document.createElement('dl'); list.className = 'course-review'; root.append(title, list);
            for (const field of step.querySelectorAll('input:not([type=hidden]),select,textarea')) {
                let value = field.type === 'checkbox' ? (field.checked ? 'Yes' : 'No') : field.value;
                if (field.type === 'file') value = field.files[0]?.name || '';
                if (field.tagName === 'SELECT') value = field.selectedOptions[0]?.textContent || '';
                if (!value) continue;
                const label = field.closest('label'); const term = document.createElement('dt'); const description = document.createElement('dd');
                const group = field.closest('.repeatable')?.querySelector('legend')?.textContent;
                term.textContent = (group ? group + ' - ' : '') + (label?.firstChild?.textContent.trim() || field.name);
                description.textContent = value; list.append(term, description);
            }
        }
    };
    const buttons = steps.map((step, index) => {
        const button = document.createElement('button'); button.type = 'button';
        button.textContent = `${index + 1}. ${step.querySelector('legend').textContent}`;
        button.setAttribute('aria-controls', step.id); button.addEventListener('click', () => show(index)); nav.append(button);
        step.querySelector('legend').tabIndex = -1;
        return button;
    });
    function show(index, focus = true) {
        current = index;
        steps.forEach((step, i) => { step.hidden = i !== index; buttons[i].toggleAttribute('data-current', i === index); if (i === index) buttons[i].setAttribute('aria-current', 'step'); else buttons[i].removeAttribute('aria-current'); });
        previous.disabled = index === 0; next.hidden = index === steps.length - 1;
        progress.textContent = `Step ${index + 1} of ${steps.length}`;
        if (index === steps.length - 1) review();
        if (focus) steps[index].querySelector('legend').focus();
    }
    courseWizard.elements.name.required = true; courseWizard.elements.category_slug.required = true;
    courseWizard.elements.sort_order.min = '1'; courseWizard.elements.sort_order.max = '100000'; courseWizard.elements.sort_order.required = true;
    courseWizard.noValidate = true;
    function validate(fields) {
        const invalid = [...fields].find(field => !field.checkValidity());
        if (!invalid) return true;
        const index = steps.findIndex(step => step.contains(invalid)); if (index >= 0) show(index);
        invalid.reportValidity(); return false;
    }
    previous.addEventListener('click', () => show(current - 1));
    next.addEventListener('click', () => { if (validate(steps[current].querySelectorAll('input,select,textarea'))) show(current + 1); });
    courseWizard.addEventListener('submit', event => {
        const publishing = event.submitter?.value === 'publish';
        const image = courseWizard.elements.image, upload = courseWizard.elements.upload;
        image.setCustomValidity(publishing && !image.value && !upload.files.length ? 'Choose an image or upload one before publishing.' : '');
        courseWizard.elements.image_alt.required = publishing;
        if (!validate(courseWizard.querySelectorAll('input,select,textarea'))) event.preventDefault();
    });
    courseWizard.addEventListener('input', () => courseWizard.elements.image.setCustomValidity(''));
    courseWizard.addEventListener('change', () => courseWizard.elements.image.setCustomValidity(''));
    show(0, false);
}

document.querySelectorAll('[data-content-editor]').forEach(form => {
    const actions = form.querySelector('.editor-actions');
    if (!actions) return;
    let dirty = false, submitting = false;
    const note = document.createElement('p'); note.className = 'unsaved-note'; note.setAttribute('role', 'status');
    note.textContent = 'Changes are saved only when you choose a save or publish action.';
    actions.before(note);
    const changed = () => { if (!dirty) { dirty = true; note.textContent = 'You have unsaved changes.'; } };
    form.addEventListener('input', changed); form.addEventListener('change', changed);
    form.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        if (submitting) { event.preventDefault(); return; }
        submitting = true; dirty = false; note.textContent = 'Saving. Please wait...';
        // Keep submitter name/value enabled so PHP receives the intended action.
        form.querySelectorAll('button[type=submit],button[name=action],.editor-actions button:not([type])').forEach(button => button.setAttribute('aria-disabled', 'true'));
    });
    window.addEventListener('pageshow', () => { submitting = false; form.querySelectorAll('[aria-disabled=true]').forEach(button => button.removeAttribute('aria-disabled')); });
    document.addEventListener('submit', event => { if (event.target.matches('[data-course-delete-form]')) dirty = false; });
    window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
});

// Keep a visible server-submitted confirmation when native dialogs are unavailable.
const courseDeletePanel = document.querySelector('[data-course-delete-panel]');
if (courseDeletePanel && typeof HTMLDialogElement !== 'undefined' && 'showModal' in HTMLDialogElement.prototype) {
    const dialog = document.createElement('dialog');
    dialog.className = 'course-delete-dialog'; dialog.id = 'course-delete-dialog';
    dialog.setAttribute('aria-labelledby', 'course-delete-title');
    dialog.setAttribute('aria-describedby', 'course-delete-warning');
    const trigger = document.createElement('button');
    trigger.type = 'button'; trigger.className = 'destructive'; trigger.textContent = 'Delete course';
    trigger.setAttribute('aria-haspopup', 'dialog'); trigger.setAttribute('aria-controls', dialog.id);
    courseDeletePanel.before(trigger, dialog); dialog.append(courseDeletePanel);
    const cancelLink = dialog.querySelector('[data-course-delete-cancel]');
    const cancel = document.createElement('button');
    cancel.type = 'button'; cancel.textContent = 'Cancel'; cancel.setAttribute('data-course-delete-cancel', '');
    cancelLink.replaceWith(cancel);
    trigger.addEventListener('click', () => { dialog.showModal(); cancel.focus(); });
    cancel.addEventListener('click', event => { event.preventDefault(); dialog.close(); });
    dialog.addEventListener('close', () => trigger.focus());
}
