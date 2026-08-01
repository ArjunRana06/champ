@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Upload Study Materials</h2>
            <p>Upload PDFs, slides, handwritten notes — AI will index them for you</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="processingBadge" class="stat-badge" style="display:none;background:#fef3c7;color:#d97706;">
                <i class="bi bi-hourglass-split"></i> <span id="processingCount">0</span> processing
            </span>
            <span style="color:var(--card-accent);font-size:0.85rem;font-weight:500;" id="uploadCountBadge">0 documents</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="glass-card text-center p-5" id="dropzone" style="cursor:pointer;border:2px dashed #c7d2fe;transition:all 0.3s;">
                <div class="mb-3">
                    <i class="bi bi-cloud-upload" style="font-size:3.5rem;color:var(--card-accent);"></i>
                </div>
                <h5 style="color:var(--text-primary);font-weight:700;">Drag & drop your files here</h5>
                <p style="color:var(--text-secondary);">or click to browse</p>
                <input type="file" id="fileInput" multiple accept="*/*" class="d-none">
                <button class="dark-btn" id="browseBtn">
                    <i class="bi bi-folder2-open"></i> Choose Files
                </button>
                <div class="mt-3" style="color:var(--text-muted);font-size:0.75rem;">
                    Any file type supported — no size limit
                </div>
            </div>

            <div class="glass-card mt-4" id="pendingQueue" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 style="color:var(--text-primary);font-weight:700;margin:0;">
                        <i class="bi bi-file-earmark me-2" style="color:var(--card-accent);"></i> Ready to upload (<span id="pendingCount">0</span>)
                    </h6>
                </div>
                <div id="pendingList"></div>
                <div class="mt-3">
                    <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Assign to subject</label>
                    <select id="subjectSelect" class="form-glass form-select" style="margin-bottom:0.8rem;">
                        <option value="">-- General / Uncategorized --</option>
                        @forelse($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->semester ?? '' }})</option>
                        @empty
                            <option disabled>No subjects yet</option>
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
                <h6 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">
                    <i class="bi bi-hourglass-split me-2" style="color:var(--card-accent);"></i> Uploading (<span id="activeCount">0</span>)
                </h6>
                <div id="activeUploadsList"></div>
            </div>

            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 style="color:var(--text-primary);font-weight:700;margin:0;">
                        <i class="bi bi-folder-fill me-2" style="color:var(--card-accent);"></i> Your materials
                    </h6>
                    <button class="btn-soft py-1 px-3" style="font-size:0.75rem;" id="refreshDocsBtn" title="Refresh"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
                <div id="documentsList">
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size:2.5rem;color:#c7d2fe;"></i>
                        <p class="mt-2" style="color:var(--text-muted);">No documents yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes processingPulse { 0%,100%{opacity:1} 50%{opacity:.5} }
    .processing-pulse { animation: processingPulse 2s ease-in-out infinite; }
    .doc-progress-bar { height:4px; border-radius:4px; background:#f1f5f9; overflow:hidden; margin-top:6px; }
    .doc-progress-bar .bar { height:100%; border-radius:4px; background:linear-gradient(90deg,#6366f1,#a855f7); transition:width 0.4s ease; }
    .doc-status-badge {
        display:inline-flex; align-items:center; gap:4px; font-size:0.68rem; font-weight:600;
        padding:2px 8px; border-radius:20px;
    }
    .doc-status-badge.completed { background:#ecfdf5; color:#059669; }
    .doc-status-badge.failed { background:#fef2f2; color:#dc2626; }
    .doc-status-badge.processing { background:#eff6ff; color:#3b82f6; }
    .doc-status-badge.pending { background:#fefce8; color:#ca8a04; }
    .doc-actions { display:flex; gap:4px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let pendingFiles = [], activeUploads = [], documents = [], pollTimer = null;
    const CSRF = '{{ csrf_token() }}';
    const $ = (id) => document.getElementById(id);

    function fmtBytes(b) { if(!b)return'0 B'; const k=1024,s=['B','KB','MB'],i=Math.floor(Math.log(b)/Math.log(k)); return parseFloat((b/Math.pow(k,i)).toFixed(1))+' '+s[i]; }
    function fmtDate(s) { if(!s)return''; const d=new Date(s); return isNaN(d)?'':d.toLocaleDateString('en-US',{month:'short',day:'numeric'}); }
    function esc(s) { if(!s)return''; const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

    function statusBadge(status, progress, message) {
        const labels = {completed:'Completed',failed:'Failed',processing:'Processing',pending:'Queued'};
        const icons = {completed:'bi-check-circle-fill',failed:'bi-x-circle-fill',processing:'bi-arrow-repeat',pending:'bi-clock'};
        const spin = status === 'processing' ? ' processing-pulse' : '';
        return `<span class="doc-status-badge ${status}${spin}"><i class="bi ${icons[status]||'bi-circle'}"></i> ${labels[status]||status}</span>`;
    }

    function progressBar(progress) {
        if (progress <= 0) return '';
        return `<div class="doc-progress-bar"><div class="bar" style="width:${Math.min(100,progress)}%"></div></div>`;
    }

    function renderPending() {
        const q=$('pendingQueue'),c=$('pendingCount'),l=$('pendingList');
        if(!q||!l)return;
        if(!pendingFiles.length){q.style.display='none';return;}
        q.style.display='block'; c.innerText=pendingFiles.length;
        l.innerHTML=pendingFiles.map((f,i)=>`
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--divider-color);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text" style="font-size:1.1rem;color:var(--card-accent);"></i>
                    <div>
                        <div style="color:var(--text-primary);font-size:0.82rem;">${esc(f.name)}</div>
                        <div style="color:var(--text-muted);font-size:0.68rem;">${fmtBytes(f.size)}</div>
                    </div>
                </div>
                <button class="btn-soft py-1 px-2" style="font-size:0.7rem;border:none;" onclick="removeFile(${i})"><i class="bi bi-x"></i></button>
            </div>`).join('');
        window.removeFile=(i)=>{pendingFiles.splice(i,1);renderPending();};
    }

    function getExt(name){const p=name.split('.');return p.length>1?p.pop().toLowerCase():'';}

    function addFiles(files){
        pendingFiles.push(...Array.from(files)); renderPending();
    }

    function renderActive(){
        const card=$('activeUploadsCard'),c=$('activeCount'),l=$('activeUploadsList');
        if(!card||!l)return;
        if(!activeUploads.length){card.style.display='none';return;}
        card.style.display='block'; c.innerText=activeUploads.length;
        l.innerHTML=activeUploads.map(t=>`
            <div class="mb-2">
                <div class="d-flex justify-content-between" style="font-size:0.78rem;">
                    <span style="color:var(--text-primary);">${esc(t.name)}</span>
                    <span style="color:var(--card-accent);font-weight:600;">${t.progress}%</span>
                </div>
                <div class="doc-progress-bar"><div class="bar" style="width:${t.progress}%"></div></div>
            </div>`).join('');
    }

    async function uploadFiles(){
        if(!pendingFiles.length)return;
        const subjectId=($('subjectSelect')||{}).value||'';
        const batch=[...pendingFiles]; pendingFiles=[]; renderPending();
        for(const file of batch){
            const id=Date.now()+'-'+file.name;
            const task={id,name:file.name,progress:0};
            activeUploads.push(task); renderActive();
            const fd=new FormData(); fd.append('document',file); fd.append('subject_id',subjectId);
            try{
                await axios.post('{{ route("documents.upload") }}',fd,{
                    headers:{'Content-Type':'multipart/form-data'},
                    onUploadProgress:e=>{const t=activeUploads.find(t=>t.id===id);if(t)t.progress=Math.round(e.loaded*100/e.total);renderActive();}
                });
                activeUploads=activeUploads.filter(t=>t.id!==id); renderActive();
                await fetchDocs();
            }catch(err){
                activeUploads=activeUploads.filter(t=>t.id!==id); renderActive();
                showToast(`Upload failed: ${file.name}`,'error');
            }
        }
    }

    async function fetchDocs(){
        const list=$('documentsList'),badge=$('uploadCountBadge'),pBadge=$('processingBadge'),pCount=$('processingCount');
        if(!list)return;
        try{
            const res=await axios.get('{{ route("documents.index") }}?ajax=1',{timeout:10000});
            documents=(res.data&&Array.isArray(res.data.documents))?res.data.documents:[];
            if(badge) badge.innerText=documents.length+' document'+(documents.length!==1?'s':'');
            const processingDocs=documents.filter(d=>d.status==='processing'||d.status==='pending');
            if(pBadge) pBadge.style.display=processingDocs.length?'inline-flex':'none';
            if(pCount) pCount.innerText=processingDocs.length;

            if(!documents.length){
                list.innerHTML=`<div class="text-center py-4"><i class="bi bi-inbox" style="font-size:2.5rem;color:#c7d2fe;"></i><p class="mt-2" style="color:var(--text-muted);">No documents yet.</p></div>`;
                startPolling(false); return;
            }
            list.innerHTML=documents.map(d=>{
                const isProcessing=d.status==='processing'||d.status==='pending';
                const progress=d.processing_progress||0;
                const msg=d.processing_message||'';
                let actions='';
                if(d.status==='completed'){
                    actions+=`<button class="btn-soft py-1 px-2" style="font-size:0.72rem;color:var(--card-accent);" onclick="summarizeDoc(${d.id},this)" title="AI Summary"><i class="bi bi-file-text"></i></button>`;
                }
                if(d.status==='failed'){
                    actions+=`<button class="btn-soft py-1 px-2" style="font-size:0.72rem;color:#f59e0b;border-color:#f59e0b;" onclick="retryDoc(${d.id})" title="Retry processing"><i class="bi bi-arrow-clockwise"></i></button>`;
                }
                actions+=`<a href="/documents/${d.id}/preview" class="btn-soft py-1 px-2" style="font-size:0.72rem;" target="_blank" title="Preview"><i class="bi bi-eye"></i></a>`;
                actions+=`<button class="btn-soft danger py-1 px-2" style="font-size:0.72rem;" onclick="deleteDoc(${d.id},'${esc(d.original_name)}')" title="Delete"><i class="bi bi-trash"></i></button>`;

                return `
                <div class="py-3" style="border-bottom:1px solid var(--divider-color);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;min-width:0;">
                            <div style="color:var(--text-primary);font-size:0.82rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <i class="bi bi-file-earmark-text me-1" style="color:var(--card-accent);"></i>${esc(d.original_name)}
                            </div>
                            <div style="color:var(--text-muted);font-size:0.68rem;margin-top:2px;">
                                ${d.subject?esc(d.subject.name):'Uncategorized'} &bull; ${fmtDate(d.created_at)} &nbsp; ${statusBadge(d.status,progress,msg)}
                            </div>
                            ${isProcessing?`<div style="font-size:0.68rem;color:var(--text-secondary);margin-top:3px;">${esc(msg)} ${progress>0?'('+progress+'%)':''}</div>${progressBar(progress)}`:''}
                        </div>
                        <div class="doc-actions">${actions}</div>
                    </div>
                    <div id="summary-${d.id}" class="mt-2" style="display:none;"></div>
                </div>`;
            }).join('');
            startPolling(processingDocs.length>0);
        }catch(err){
            list.innerHTML=`<div class="text-center py-4"><i class="bi bi-exclamation-triangle" style="font-size:2rem;color:#f59e0b;"></i><p class="mt-2" style="color:var(--text-secondary);font-size:0.82rem;">Could not load documents.</p><button class="btn-soft mt-2" onclick="fetchDocs()">Retry</button></div>`;
        }
    }

    function startPolling(shouldPoll){
        if(pollTimer){clearInterval(pollTimer);pollTimer=null;}
        if(shouldPoll){pollTimer=setInterval(fetchDocs,3000);}
    }

    window.deleteDoc=async function(id,name){
        if(!confirm(`Delete "${name}"?`))return;
        try{await axios.delete('/documents/'+id);await fetchDocs();}catch(e){showToast('Delete failed','error');}
    };

    window.retryDoc=async function(id){
        try{
            await axios.post('/documents/'+id+'/retry');
            showToast('Re-queued for processing','success');
            await fetchDocs();
        }catch(e){showToast('Retry failed','error');}
    };

    window.summarizeDoc=async function(id,btn){
        if(btn.disabled)return;
        btn.disabled=true; btn.innerHTML='<i class="bi bi-arrow-repeat"></i>';
        try{
            const fd=new FormData(); fd.append('_token',CSRF);
            const res=await axios.post('/documents/'+id+'/summarize',fd);
            const div=document.getElementById('summary-'+id);
            if(div){
                div.style.display='block';
                div.innerHTML=`<div class="glass-card p-3" style="border-left:4px solid var(--card-accent);">
                    <h6 style="color:var(--text-primary);font-weight:700;font-size:0.82rem;margin-bottom:0.4rem;"><i class="bi bi-file-text me-1" style="color:var(--card-accent);"></i>AI Summary</h6>
                    <div style="color:var(--text-primary);font-size:0.82rem;line-height:1.6;">${res.data?.summary||'Summary generated'}</div>
                </div>`;
            }
            btn.innerHTML='<i class="bi bi-check-lg"></i>'; btn.style.color='#10b981';
        }catch(err){
            btn.innerHTML='<i class="bi bi-x-lg"></i>'; btn.style.color='#dc2626';
            showToast('Summarization failed','error');
            setTimeout(()=>{btn.innerHTML='<i class="bi bi-file-text"></i>';btn.disabled=false;btn.style.color='';},3000);
        }
    };

    function showToast(msg,type){
        const t=document.createElement('div');
        t.style.cssText=`position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:12px;font-size:0.82rem;font-weight:500;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.15);transition:opacity 0.3s;`;
        t.style.background=type==='error'?'#fef2f2':'#ecfdf5';
        t.style.color=type==='error'?'#dc2626':'#059669';
        t.style.border=`1px solid ${type==='error'?'#fecaca':'#a7f3d0'}`;
        t.textContent=msg;
        document.body.appendChild(t);
        setTimeout(()=>{t.style.opacity='0';setTimeout(()=>t.remove(),300);},3000);
    }

    const dz=$('dropzone'),bb=$('browseBtn'),fi=$('fileInput'),ub=$('uploadBtn'),rf=$('refreshDocsBtn');
    if(dz){
        dz.addEventListener('dragover',e=>{e.preventDefault();dz.style.borderColor='#6366f1';dz.style.background='var(--badge-bg)';});
        dz.addEventListener('dragleave',()=>{dz.style.borderColor='#c7d2fe';dz.style.background='';});
        dz.addEventListener('drop',e=>{e.preventDefault();dz.style.borderColor='#c7d2fe';dz.style.background='';if(e.dataTransfer.files.length)addFiles(e.dataTransfer.files);});
        dz.addEventListener('click',e=>{if(e.target!==bb&&e.target.id!=='browseBtn')fi?.click();});
    }
    if(bb&&fi) bb.addEventListener('click',e=>{e.stopPropagation();fi.click();});
    if(fi) fi.addEventListener('change',e=>{if(e.target.files.length){addFiles(e.target.files);fi.value='';}});
    if(ub) ub.addEventListener('click',uploadFiles);
    if(rf) rf.addEventListener('click',fetchDocs);
    fetchDocs();
});
</script>
@endsection
