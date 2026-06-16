@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Upload Study Materials</h2>
            <p>Upload PDFs, slides, handwritten notes — AI will index them for you</p>
        </div>
        <span style="color:#6366f1;font-size:0.85rem;font-weight:500;" id="uploadCountBadge">0 documents uploaded</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="glass-card text-center p-5" id="dropzone" style="cursor:pointer;border:2px dashed #c7d2fe;transition:all 0.2s;">
                <div class="mb-3">
                    <i class="bi bi-cloud-upload" style="font-size:3.5rem;color:#6366f1;"></i>
                </div>
                <h5 style="color:#1e1b4b;font-weight:700;">Drag & drop your files here</h5>
                <p style="color:#6b7280;">or click to browse</p>
                <input type="file" id="fileInput" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif,.bmp,.webp,.csv,.rtf,.odt" class="d-none">
                <button class="dark-btn" id="browseBtn">
                    <i class="bi bi-folder2-open"></i> Choose Files
                </button>
                <div class="mt-3" style="color:#9ca3af;font-size:0.75rem;">
                    Supported: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, JPG, PNG, GIF, BMP, WEBP, CSV, RTF, ODT (max 50MB each)
                </div>
            </div>

            <div class="glass-card mt-4" id="pendingQueue" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 style="color:#1e1b4b;font-weight:700;margin:0;">
                        <i class="bi bi-file-earmark me-2" style="color:#6366f1;"></i> Ready to upload (<span id="pendingCount">0</span>)
                    </h6>
                </div>
                <div id="pendingList"></div>
                <div class="mt-3">
                    <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6366f1;margin-bottom:0.4rem;display:block;">Assign to subject</label>
                    <select id="subjectSelect" style="width:100%;background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;color:#111827;font-family:'Inter',sans-serif;margin-bottom:0.8rem;">
                        <option value="">-- General / Uncategorized --</option>
                        @forelse($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->semester ?? 'Semester?' }})</option>
                        @empty
                            <option disabled>No subjects yet. Create one first.</option>
                        @endforelse
                    </select>
                    <button class="dark-btn w-100 justify-content-center" id="uploadBtn">
                        <i class="bi bi-upload"></i> Upload files
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="glass-card mb-4" id="activeUploadsCard" style="display:none;">
                <h6 style="color:#1e1b4b;font-weight:700;margin-bottom:1rem;">
                    <i class="bi bi-hourglass-split me-2" style="color:#6366f1;"></i> Uploading (<span id="activeCount">0</span>)
                </h6>
                <div id="activeUploadsList"></div>
            </div>

            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 style="color:#1e1b4b;font-weight:700;margin:0;">
                        <i class="bi bi-folder-fill me-2" style="color:#6366f1;"></i> Your uploaded materials
                    </h6>
                    <button class="btn-soft py-1 px-3" style="font-size:0.75rem;" id="refreshDocsBtn"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
                <div id="documentsList">
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size:2.5rem;color:#c7d2fe;"></i>
                        <p class="mt-2" style="color:#9ca3af;">No documents yet. Upload your first file above.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let pendingFiles = [], activeUploads = [], documents = [];
        const $ = (id) => document.getElementById(id);

        function fmtBytes(b) {
            if (b===0) return '0 Bytes';
            const k=1024, s=['Bytes','KB','MB'], i=Math.floor(Math.log(b)/Math.log(k));
            return parseFloat((b/Math.pow(k,i)).toFixed(1))+' '+s[i];
        }
        function fmtDate(s) { if(!s)return'Unknown'; const d=new Date(s); return isNaN(d.getTime())?'Invalid date':d.toLocaleDateString(); }
        function esc(s) { if(!s)return''; return s.replace(/[&<>]/g,m=>m==='&'?'&amp;':m==='<'?'&lt;':'&gt;'); }

        function renderPending() {
            const q = $('pendingQueue'), c = $('pendingCount'), l = $('pendingList');
            if (!q||!c||!l) return;
            if (!pendingFiles.length) { q.style.display='none'; return; }
            q.style.display='block'; c.innerText=pendingFiles.length;
            l.innerHTML = pendingFiles.map((f,i)=>`
                <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid #f1f5f9;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-file-earmark-text" style="font-size:1.2rem;color:#6366f1;"></i>
                        <div>
                            <div style="color:#1e1b4b;font-size:0.85rem;">${esc(f.name)}</div>
                            <div style="color:#9ca3af;font-size:0.7rem;">${fmtBytes(f.size)}</div>
                        </div>
                    </div>
                    <button class="btn-soft py-1 px-2" style="font-size:0.75rem;" onclick="removeFile(${i})"><i class="bi bi-x"></i></button>
                </div>
            `).join('');
            window.removeFile = (i) => { pendingFiles.splice(i,1); renderPending(); };
        }

        function getExt(name) { const p = name.split('.'); return p.length>1 ? p.pop().toLowerCase() : ''; }

        function addFiles(files) {
            const allowedExt = ['pdf','doc','docx','ppt','pptx','xls','xlsx','txt','jpg','jpeg','png','gif','bmp','webp','csv','rtf','odt'];
            const allowedMime = ['text/plain','text/csv','text/rtf','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','image/jpeg','image/png','image/gif','image/bmp','image/webp'];
            const maxSize = 50 * 1024 * 1024;
            const valid = Array.from(files).filter(f => {
                const ext = getExt(f.name);
                const extOk = allowedExt.includes(ext);
                const mimeOk = allowedMime.includes(f.type) || f.type.startsWith('text/');
                return (extOk || mimeOk || (!ext && !f.type)) && f.size <= maxSize;
            });
            if (valid.length!==files.length) alert('Some files were skipped (unsupported format or >50MB)');
            pendingFiles.push(...valid);
            renderPending();
        }

        function renderActive() {
            const card = $('activeUploadsCard'), c = $('activeCount'), l = $('activeUploadsList');
            if (!card||!c||!l) return;
            if (!activeUploads.length) { card.style.display='none'; return; }
            card.style.display='block'; c.innerText=activeUploads.length;
            l.innerHTML = activeUploads.map(t=>`
                <div class="mb-2">
                    <div class="d-flex justify-content-between" style="font-size:0.8rem;">
                        <span style="color:#374151;">${esc(t.name)}</span>
                        <span style="color:#6366f1;">${t.progress}%</span>
                    </div>
                    <div class="progress" style="height:4px;background:#f1f5f9;border-radius:4px;">
                        <div class="progress-bar" style="width:${t.progress}%;background:#6366f1;border-radius:4px;"></div>
                    </div>
                </div>
            `).join('');
        }

        async function uploadFiles() {
            if (!pendingFiles.length) return;
            const subjectId = ($('subjectSelect')||{}).value||'';
            const batch = [...pendingFiles];
            pendingFiles = []; renderPending();
            for (const file of batch) {
                const id = Date.now()+'-'+file.name;
                const task = {id, name: file.name, progress: 0};
                activeUploads.push(task); renderActive();
                const fd = new FormData();
                fd.append('document', file); fd.append('subject_id', subjectId);
                try {
                    await axios.post('{{ route("documents.upload") }}', fd, {
                        headers: {'Content-Type':'multipart/form-data'},
                        onUploadProgress: (e) => {
                            const pct = Math.round((e.loaded*100)/e.total);
                            const t = activeUploads.find(t=>t.id===id);
                            if (t) t.progress=pct; renderActive();
                        }
                    });
                    activeUploads = activeUploads.filter(t=>t.id!==id); renderActive();
                    await fetchDocs();
                } catch (err) {
                    activeUploads = activeUploads.filter(t=>t.id!==id); renderActive();
                    alert(`Upload failed for ${file.name}`);
                }
            }
        }

        async function fetchDocs() {
            const list = $('documentsList'), badge = $('uploadCountBadge');
            if (!list) return;
            try {
                const res = await axios.get('{{ route("documents.index") }}?ajax=1', {timeout:10000});
                documents = (res.data && Array.isArray(res.data.documents)) ? res.data.documents : [];
                if (badge) badge.innerText = documents.length+' document'+(documents.length!==1?'s':'')+' uploaded';
                if (!documents.length) {
                    list.innerHTML = `<div class="text-center py-4"><i class="bi bi-inbox" style="font-size:2.5rem;color:#c7d2fe;"></i><p class="mt-2" style="color:#9ca3af;">No documents yet.</p></div>`;
                    return;
                }
                list.innerHTML = documents.map(d=>{
                    const summaryBtn = d.status==='completed'
                        ? `<button class="btn-soft py-1 px-2 summary-btn" style="font-size:0.75rem;color:#6366f1;" onclick="summarizeDoc(${d.id}, this)" title="Generate Summary"><i class="bi bi-file-text"></i></button>`
                        : '';
                    return `
                    <div class="py-3" style="border-bottom:1px solid #f1f5f9;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div style="color:#1e1b4b;font-size:0.85rem;font-weight:500;"><i class="bi bi-file-earmark-text me-2" style="color:#6366f1;"></i>${esc(d.original_name)}</div>
                                <div style="color:#9ca3af;font-size:0.75rem;margin-top:0.2rem;">
                                    ${d.subject?esc(d.subject.name):'Uncategorized'} &bull; ${fmtDate(d.created_at)} &bull;
                                    <span style="color:${d.status==='completed'?'#059669':d.status==='failed'?'#dc2626':'#d97706'};">${d.status}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                ${summaryBtn}
                                <a href="/documents/${d.id}/preview" class="btn-soft py-1 px-2" style="font-size:0.75rem;" target="_blank"><i class="bi bi-eye"></i></a>
                                <button class="btn-soft danger py-1 px-2" style="font-size:0.75rem;" onclick="deleteDoc(${d.id}, '${esc(d.original_name)}')"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <div id="summary-${d.id}" class="mt-2" style="display:none;"></div>
                    </div>`;
                }).join('');
            } catch (err) {
                list.innerHTML = `<div class="text-center py-4"><i class="bi bi-exclamation-triangle" style="font-size:2.5rem;color:#dc2626;"></i><p class="mt-2" style="color:#6b7280;">Failed to load documents.</p><button class="btn-soft mt-2" onclick="fetchDocs()">Retry</button></div>`;
            }
        }

        window.deleteDoc = async function(id, name) {
            if (!confirm(`Delete "${name}"? This will also remove all related chunks and questions.`)) return;
            try {
                await axios.delete(`/documents/${id}`);
                await fetchDocs();
            } catch (err) {
                alert('Failed to delete document');
            }
        };

        window.summarizeDoc = async function(id, btn) {
            if (btn.disabled) return;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                const res = await axios.post('/documents/' + id + '/summarize', formData);
                const summaryDiv = document.getElementById('summary-' + id);
                if (summaryDiv) {
                    summaryDiv.style.display = 'block';
                    summaryDiv.innerHTML = '<div class="glass-card p-3 mt-2" style="border-left:4px solid #6366f1;"><h6 style="color:#1e1b4b;font-weight:700;font-size:0.85rem;margin-bottom:0.5rem;"><i class="bi bi-file-text me-2" style="color:#6366f1;"></i>AI Summary</h6><div style="color:#374151;font-size:0.85rem;line-height:1.6;">' + esc(res.data?.summary || 'Summary generated') + '</div></div>';
                    if (res.data?.summary) {
                        summaryDiv.querySelector('div').innerHTML = '<h6 style="color:#1e1b4b;font-weight:700;font-size:0.85rem;margin-bottom:0.5rem;"><i class="bi bi-file-text me-2" style="color:#6366f1;"></i>AI Summary</h6><div style="color:#374151;font-size:0.85rem;line-height:1.6;">' + res.data.summary + '</div>';
                    }
                }
                btn.innerHTML = '<i class="bi bi-file-text"></i>';
                btn.style.borderColor = '#10b981';
                btn.style.color = '#10b981';
            } catch (err) {
                btn.innerHTML = '<i class="bi bi-exclamation-triangle"></i>';
                const summaryDiv = document.getElementById('summary-' + id);
                if (summaryDiv) {
                    summaryDiv.style.display = 'block';
                    summaryDiv.innerHTML = '<div class="p-3 mt-2" style="border-left:4px solid #dc2626;background:#fef2f2;border-radius:0.75rem;font-size:0.85rem;color:#dc2626;">Summarization failed: ' + (err.response?.data?.message || err.message) + '</div>';
                }
                setTimeout(() => { btn.innerHTML = '<i class="bi bi-file-text"></i>'; btn.disabled = false; }, 3000);
            }
        };

        const dz = $('dropzone'), bb = $('browseBtn'), fi = $('fileInput'), ub = $('uploadBtn'), rf = $('refreshDocsBtn');
        if (dz) {
            dz.addEventListener('dragover', e=>{ e.preventDefault(); dz.style.borderColor='#6366f1'; dz.style.background='#eef2ff'; });
            dz.addEventListener('dragleave', ()=>{ dz.style.borderColor='#c7d2fe'; dz.style.background=''; });
            dz.addEventListener('drop', e=>{ e.preventDefault(); dz.style.borderColor='#c7d2fe'; dz.style.background=''; if(e.dataTransfer.files.length) addFiles(e.dataTransfer.files); });
            dz.addEventListener('click', e=>{ if(e.target!==bb) fi?.click(); });
        }
        if (bb&&fi) bb.addEventListener('click', e=>{ e.stopPropagation(); fi.click(); });
        if (fi) fi.addEventListener('change', e=>{ if(e.target.files.length){ addFiles(e.target.files); fi.value=''; } });
        if (ub) ub.addEventListener('click', uploadFiles);
        if (rf) rf.addEventListener('click', fetchDocs);
        fetchDocs();
    });
</script>
@endsection
