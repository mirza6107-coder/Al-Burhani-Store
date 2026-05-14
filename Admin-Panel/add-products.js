/* ========================================
   AL BURHAN ADMIN — Add Products JS
   Full interactive features
   ======================================== */

document.addEventListener('DOMContentLoaded', () => {

    /* ==============================
       QUICK STATS — load from API
       ============================== */
    async function loadStats() {
        try {
            const res  = await fetch('api.php?action=stats');
            const data = await res.json();
            if (data.success) {
                const s = data.data;
                document.getElementById('statTotal').textContent = s.total   ?? '—';
                document.getElementById('statLow').textContent   = s.low     ?? '—';
            }
        } catch (_) { /* silently fail */ }
    }
    loadStats();

    /* ==============================
       STEP INDICATOR
       auto-advance as user fills fields
       ============================== */
    function setStep(n) {
        document.querySelectorAll('.form-step').forEach((s, i) => {
            s.classList.remove('active', 'done');
            if (i < n - 1) s.classList.add('done');
            else if (i === n - 1) s.classList.add('active');
        });
    }

    document.getElementById('name')?.addEventListener('blur', () => {
        if (document.getElementById('name').value.trim()) setStep(2);
    });
    document.getElementById('description')?.addEventListener('blur', () => {
        if (document.getElementById('description').value.trim()) setStep(3);
    });
    document.getElementById('image')?.addEventListener('change', () => setStep(4));

    /* ==============================
       IMAGE UPLOAD
       ============================== */
    const imageInput = document.getElementById('image');
    const preview    = document.getElementById('image-preview');
    const uploadZone = document.getElementById('uploadZone');
    const removeBtn  = document.getElementById('removeImage');

    if (imageInput && preview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                showToast('Image must be under 5MB ✦', true);
                return;
            }
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML = `
                    <span class="preview-label">Preview</span>
                    <img src="${e.target.result}" alt="preview" />
                    <button type="button" class="preview-remove" id="removeImage" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>`;
                preview.classList.add('has-image');
                // Re-attach remove listener
                preview.querySelector('.preview-remove')?.addEventListener('click', clearImage);
            };
            reader.readAsDataURL(file);
        });
    }

    removeBtn?.addEventListener('click', clearImage);

    function clearImage() {
        if (imageInput) imageInput.value = '';
        preview.innerHTML = `<span class="preview-label">Preview</span><p>Image preview will appear here</p>
            <button type="button" class="preview-remove" id="removeImage" title="Remove">
                <i class="fas fa-times"></i>
            </button>`;
        preview.classList.remove('has-image');
        preview.querySelector('.preview-remove')?.addEventListener('click', clearImage);
    }

    // Drag-and-drop
    uploadZone?.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    uploadZone?.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
    uploadZone?.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dt = new DataTransfer();
            dt.items.add(file);
            imageInput.files = dt.files;
            imageInput.dispatchEvent(new Event('change'));
        }
    });

    /* ==============================
       STATUS TOGGLE
       ============================== */
    document.querySelectorAll('.status-opt').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.status-opt').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
        });
    });

    /* ==============================
       TAGS INPUT
       ============================== */
    const tagsWrap   = document.getElementById('tagsWrap');
    const tagsInput  = document.getElementById('tagsInput');
    const tagsHidden = document.getElementById('tagsHidden');
    const tags       = [];

    function renderTags() {
        // Remove existing tag items
        tagsWrap.querySelectorAll('.tag-item').forEach(t => t.remove());
        tags.forEach((tag, i) => {
            const el = document.createElement('div');
            el.className = 'tag-item';
            el.innerHTML = `${tag} <span class="tag-remove" data-i="${i}">×</span>`;
            tagsWrap.insertBefore(el, tagsInput);
        });
        tagsHidden.value = tags.join(',');
    }

    function addTag(val) {
        const v = val.trim().toLowerCase().replace(/[^a-z0-9-]/g, '');
        if (v && !tags.includes(v) && tags.length < 10) {
            tags.push(v);
            renderTags();
        }
        tagsInput.value = '';
    }

    tagsInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(tagsInput.value);
        }
        if (e.key === 'Backspace' && !tagsInput.value && tags.length) {
            tags.pop();
            renderTags();
        }
    });

    tagsInput?.addEventListener('blur', () => {
        if (tagsInput.value.trim()) addTag(tagsInput.value);
    });

    tagsWrap?.addEventListener('click', (e) => {
        if (e.target.classList.contains('tag-remove')) {
            tags.splice(parseInt(e.target.dataset.i), 1);
            renderTags();
        }
        tagsInput?.focus();
    });

    /* ==============================
       CHARACTER COUNTERS
       ============================== */
    function bindCounter(inputId, counterId) {
        const el = document.getElementById(inputId);
        const ct = document.getElementById(counterId);
        if (!el || !ct) return;
        el.addEventListener('input', () => {
            ct.textContent = el.value.length;
        });
    }
    bindCounter('name', 'nameCount');
    bindCounter('description', 'descCount');

    /* ==============================
       SKU GENERATOR
       ============================== */
    document.getElementById('genSkuBtn')?.addEventListener('click', () => {
        const cat  = (document.getElementById('category')?.value || 'GEN').substring(0, 4).toUpperCase();
        const rand = Math.floor(Math.random() * 9000) + 1000;
        document.getElementById('sku').value = `AB-${cat}-${rand}`;
        document.getElementById('sku').classList.add('shake');
        setTimeout(() => document.getElementById('sku').classList.remove('shake'), 400);
    });

    /* ==============================
       VALIDATION
       ============================== */
    function validateGroup(id, msg) {
        const input = document.getElementById(id);
        const group = document.getElementById('fg-' + id) || input?.closest('.form-group');
        const err   = group?.querySelector('.field-error span');
        const val   = input?.value.trim();

        if (!val || (id === 'price' && parseFloat(val) <= 0)) {
            group?.classList.add('has-error');
            group?.classList.remove('has-success');
            if (err) err.textContent = msg;
            input?.classList.add('shake');
            setTimeout(() => input?.classList.remove('shake'), 400);
            return false;
        }
        group?.classList.remove('has-error');
        group?.classList.add('has-success');
        return true;
    }

    // Clear error on input
    document.querySelectorAll('.form-group input, .form-group select, .form-group textarea').forEach(el => {
        el.addEventListener('input', () => {
            el.closest('.form-group')?.classList.remove('has-error');
        });
    });

    /* ==============================
       FORM SUBMIT (Publish)
       ============================== */
    const form    = document.getElementById('productForm');
    const saveBtn = document.getElementById('saveBtn');

    form?.addEventListener('submit', async function (e) {
        e.preventDefault();
        await submitForm('In Stock');
    });

    /* ==============================
       DRAFT SAVE
       ============================== */
    document.getElementById('draftBtn')?.addEventListener('click', async () => {
        const nameVal = document.getElementById('name')?.value.trim();
        if (!nameVal) { showToast('Add a product name to save draft ✦', true); return; }
        await submitForm('Draft');
    });

    async function submitForm(publishStatus) {
        /* Validate required fields */
        let valid = true;
        if (!validateGroup('name',     'Product name is required'))   valid = false;
        if (!validateGroup('category', 'Please select a category'))   valid = false;
        if (!validateGroup('price',    'Price must be greater than 0')) valid = false;
        if (!validateGroup('stock',    'Stock quantity is required'))  valid = false;
        if (!valid) { showToast('Please fill all required fields ✦', true); return; }

        /* Build FormData */
        const formData = new FormData();
        formData.append('name',        document.getElementById('name').value.trim());
        formData.append('category',    document.getElementById('category').value);
        formData.append('subcategory', document.getElementById('subcategory')?.value.trim() || '');
        formData.append('price',       parseFloat(document.getElementById('price').value));
        formData.append('stock',       document.getElementById('stock').value);
        formData.append('size',        document.getElementById('size')?.value.trim() || '');
        formData.append('status',      publishStatus === 'Draft'
                                        ? 'Draft'
                                        : (document.querySelector('.status-opt.selected')?.dataset.value || 'In Stock'));
        formData.append('description', document.getElementById('description')?.value.trim() || '');
        formData.append('features',    document.getElementById('features')?.value.trim() || '');
        formData.append('sku',         document.getElementById('sku')?.value.trim() || '');
        formData.append('tags',        tagsHidden?.value || '');
        formData.append('featured',    document.getElementById('featured')?.checked ? 1 : 0);
        formData.append('is_new',      document.getElementById('is_new')?.checked ? 1 : 0);
        formData.append('on_sale',     document.getElementById('on_sale')?.checked ? 1 : 0);

        if (imageInput?.files[0]) formData.append('image', imageInput.files[0]);

        /* Loading state */
        const origHTML = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
        saveBtn.disabled  = true;

        try {
            const res  = await fetch('api.php?action=add', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                const name = data.data?.name || document.getElementById('name').value;
                showToast(publishStatus === 'Draft'
                    ? `"${name}" saved as draft ✦`
                    : `"${name}" published to catalogue ✦`);

                // Update today counter
                const added = document.getElementById('statAdded');
                if (added) added.textContent = parseInt(added.textContent || 0) + 1;

                resetForm();
                setStep(1);
                saveBtn.innerHTML = '<i class="fas fa-check"></i> Published!';
                setTimeout(() => { saveBtn.innerHTML = origHTML; saveBtn.disabled = false; }, 2000);
            } else {
                showToast('Error: ' + (data.error || 'Unknown error'), true);
                saveBtn.innerHTML = origHTML;
                saveBtn.disabled  = false;
            }
        } catch (err) {
            console.error(err);
            showToast('Network error — check your server ✦', true);
            saveBtn.innerHTML = origHTML;
            saveBtn.disabled  = false;
        }
    }

    function resetForm() {
        form.reset();
        clearImage();
        tags.splice(0, tags.length);
        renderTags();
        // Reset status toggle
        document.querySelectorAll('.status-opt').forEach((o, i) => o.classList.toggle('selected', i === 0));
        // Reset counters
        document.getElementById('nameCount').textContent = '0';
        document.getElementById('descCount').textContent = '0';
        // Clear success states
        document.querySelectorAll('.form-group').forEach(g => g.classList.remove('has-success', 'has-error'));
    }

    /* ==============================
       ACTIVE NAV
       ============================== */
    document.querySelectorAll('.nav-item').forEach(link => {
        if (link.getAttribute('href') === 'add-products.php') link.classList.add('active');
    });

});

/* ==============================
   TOAST
   ============================== */
function showToast(message, isError = false) {
    let toast = document.querySelector('.al-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'al-toast';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.background = isError ? '#e05c5c' : 'var(--gold)';
    toast.style.color = isError ? '#fff' : 'var(--deep-green)';
    toast.classList.add('show');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => toast.classList.remove('show'), 3200);
}