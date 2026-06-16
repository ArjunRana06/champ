{{-- resources/views/backend/uploads/index.blade.php --}}
@extends('Backend.master')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📤 Upload Study Materials</h1>
            <p class="text-muted">Upload PDFs, slides, handwritten notes – AI will index them for you</p>
        </div>
        <div>
            <span class="badge bg-primary" id="uploadCountBadge">0 documents uploaded</span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left column: Upload area -->
        <div class="col-lg-7">
            <!-- Drag & Drop Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4" id="dropzone">
                <div class="card-body text-center p-5">
                    <div class="mb-3">
                        <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title">Drag & drop your files here</h5>
                    <p class="text-muted">or click to browse</p>
                    <input type="file" id="fileInput" multiple accept=".pdf,.docx,.pptx,.txt,.jpg,.png" class="d-none">
                    <button class="btn btn-outline-primary rounded-pill px-4" id="browseBtn">
                        <i class="bi bi-folder2-open"></i> Choose Files
                    </button>
                    <div class="mt-3 small text-muted">
                        Supported: PDF, DOCX, PPTX, TXT, JPG, PNG (max 20MB each)
                    </div>
                </div>
            </div>

            <!-- Pending Files Queue -->
            <div class="card shadow-sm border-0 rounded-4" id="pendingQueue" style="display: none;">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold">📄 Ready to upload (<span id="pendingCount">0</span>)</h6>
                </div>
                <div class="card-body">
                    <div id="pendingList" class="list-group list-group-flush"></div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">📁 Assign to subject (optional)</label>
                        <select class="form-select" id="subjectSelect">
                            <option value="">-- General / Uncategorized --</option>
                            @forelse($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->semester ?? 'Semester?' }})</option>
                            @empty
                                <option disabled>No subjects yet. Create one first.</option>
                            @endforelse
                        </select>
                        <button class="btn btn-primary rounded-pill px-5 mt-3 w-100" id="uploadBtn">
                            <i class="bi bi-upload"></i> Upload files
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column: Active uploads & document list -->
        <div class="col-lg-5">
            <!-- Active uploads progress -->
            <div class="card shadow-sm border-0 rounded-4 mb-4" id="activeUploadsCard" style="display: none;">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold">⏳ Uploading (<span id="activeCount">0</span>)</h6>
                </div>
                <div class="card-body" id="activeUploadsList"></div>
            </div>

            <!-- Uploaded documents -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold">📂 Your uploaded materials</h6>
                    <button class="btn btn-sm btn-link text-primary" id="refreshDocsBtn">Refresh</button>
                </div>
                <div class="card-body p-0">
                    <div id="documentsList" class="list-group list-group-flush">
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-secondary"></i>
                            <p class="mt-2 text-muted">No documents yet. Upload your first file above.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Wait for DOM to be fully loaded before accessing elements
    document.addEventListener('DOMContentLoaded', function() {
        // -------------------- STATE --------------------
        let pendingFiles = [];
        let activeUploads = [];
        let documents = [];

        // -------------------- HELPER FUNCTIONS --------------------
        function safeGetElement(id) {
            const el = document.getElementById(id);
            if (!el) console.warn(`Element #${id} not found`);
            return el;
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function formatDate(dateString) {
            if (!dateString) return 'Unknown';
            const date = new Date(dateString);
            return isNaN(date.getTime()) ? 'Invalid date' : date.toLocaleDateString();
        }

        function statusBadge(status) {
            const map = {
                'pending': 'bg-warning text-dark',
                'processing': 'bg-info text-dark',
                'completed': 'bg-success',
                'failed': 'bg-danger'
            };
            return map[status] || 'bg-secondary';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // -------------------- RENDER UI --------------------
        function renderPendingQueue() {
            const pendingQueue = safeGetElement('pendingQueue');
            const pendingCountSpan = safeGetElement('pendingCount');
            const pendingListDiv = safeGetElement('pendingList');

            if (!pendingQueue || !pendingCountSpan || !pendingListDiv) return;
            if (pendingFiles.length === 0) {
                pendingQueue.style.display = 'none';
                return;
            }
            pendingQueue.style.display = 'block';
            pendingCountSpan.innerText = pendingFiles.length;
            pendingListDiv.innerHTML = pendingFiles.map((file, idx) => `
                <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-file-earmark-text fs-5 text-secondary"></i>
                        <div>
                            <div class="fw-semibold">${escapeHtml(file.name)}</div>
                            <div class="small text-muted">${formatBytes(file.size)}</div>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="removeFile(${idx})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `).join('');
        }

        window.removeFile = function(idx) {
            pendingFiles.splice(idx, 1);
            renderPendingQueue();
        };

        function getExt(name) { const p = name.split('.'); return p.length>1 ? p.pop().toLowerCase() : ''; }

        function addFiles(files) {
            const allowedExt = ['pdf','doc','docx','ppt','pptx','xls','xlsx','txt','jpg','jpeg','png','gif','bmp','webp','csv','rtf','odt'];
            const allowedMime = ['text/plain','text/csv','text/rtf','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','image/jpeg','image/png','image/gif','image/bmp','image/webp'];
            const maxSize = 50 * 1024 * 1024;
            const validFiles = Array.from(files).filter(f => {
                const ext = getExt(f.name);
                const extOk = allowedExt.includes(ext);
                const mimeOk = allowedMime.includes(f.type) || f.type.startsWith('text/');
                return (extOk || mimeOk || (!ext && !f.type)) && f.size <= maxSize;
            });
            if (validFiles.length !== files.length) {
                alert('Some files were skipped (unsupported format or >50MB)');
            }
            pendingFiles.push(...validFiles);
            renderPendingQueue();
        }

        function renderActiveUploads() {
            const activeUploadsCard = safeGetElement('activeUploadsCard');
            const activeCountSpan = safeGetElement('activeCount');
            const activeUploadsList = safeGetElement('activeUploadsList');

            if (!activeUploadsCard || !activeCountSpan || !activeUploadsList) return;
            if (activeUploads.length === 0) {
                activeUploadsCard.style.display = 'none';
                return;
            }
            activeUploadsCard.style.display = 'block';
            activeCountSpan.innerText = activeUploads.length;
            activeUploadsList.innerHTML = activeUploads.map(task => `
                <div class="mb-3">
                    <div class="d-flex justify-content-between small">
                        <span class="text-truncate" style="max-width: 200px;">${escapeHtml(task.name)}</span>
                        <span>${task.progress}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: ${task.progress}%"></div>
                    </div>
                </div>
            `).join('');
        }

        async function uploadFiles() {
            if (pendingFiles.length === 0) return;
            const subjectSelect = safeGetElement('subjectSelect');
            const subjectId = subjectSelect ? subjectSelect.value : '';
            const filesToUpload = [...pendingFiles];
            pendingFiles = [];
            renderPendingQueue();

            for (let file of filesToUpload) {
                const taskId = Date.now() + '-' + file.name;
                const task = { id: taskId, name: file.name, progress: 0 };
                activeUploads.push(task);
                renderActiveUploads();

                const formData = new FormData();
                formData.append('document', file);
                formData.append('subject_id', subjectId);

                try {
                    await axios.post('{{ route("documents.upload") }}', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        onUploadProgress: (progressEvent) => {
                            const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                            const t = activeUploads.find(t => t.id === taskId);
                            if (t) t.progress = percent;
                            renderActiveUploads();
                        }
                    });
                    activeUploads = activeUploads.filter(t => t.id !== taskId);
                    renderActiveUploads();
                    await fetchDocuments();
                } catch (error) {
                    console.error('Upload error:', error);
                    let errorMsg = `Upload failed for ${file.name}: `;
                    if (error.response?.data?.message) errorMsg += error.response.data.message;
                    else if (error.message) errorMsg += error.message;
                    else errorMsg += 'Unknown error';
                    alert(errorMsg);
                    activeUploads = activeUploads.filter(t => t.id !== taskId);
                    renderActiveUploads();
                }
            }
        }

        async function fetchDocuments() {
            const documentsListEl = safeGetElement('documentsList');
            const uploadCountBadgeEl = safeGetElement('uploadCountBadge');
            if (!documentsListEl) return;

            try {
                const response = await axios.get('{{ route("documents.index") }}?ajax=1', {
                    timeout: 10000,
                    validateStatus: (status) => status >= 200 && status < 300
                });

                if (response.data && Array.isArray(response.data.documents)) {
                    documents = response.data.documents;
                } else {
                    console.error('Invalid response format:', response.data);
                    documents = [];
                }

                if (uploadCountBadgeEl) {
                    uploadCountBadgeEl.innerText = documents.length + ' document' + (documents.length !== 1 ? 's' : '') + ' uploaded';
                }

                if (documents.length === 0) {
                    documentsListEl.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-secondary"></i>
                            <p class="mt-2 text-muted">No documents yet. Upload your first file above.</p>
                        </div>
                    `;
                    return;
                }

                documentsListEl.innerHTML = documents.map(doc => `
                    <div class="list-group-item bg-transparent">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                <span class="fw-medium">${escapeHtml(doc.original_name)}</span>
                                <div class="small text-muted">
                                    ${doc.subject ? escapeHtml(doc.subject.name) : 'Uncategorized'} •
                                    ${formatDate(doc.created_at)} •
                                    <span class="badge ${statusBadge(doc.status)}">${doc.status}</span>

                                </div>
                            </div>
                            <a href="/documents/${doc.id}/preview" class="btn btn-sm btn-outline-secondary rounded-circle" target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Fetch documents error:', error);
                documentsListEl.innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <p class="mt-2">Failed to load documents. Please refresh the page.</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="fetchDocuments()">Retry</button>
                    </div>
                `;
            }
        }

        // -------------------- INITIALIZE EVENT LISTENERS --------------------
        function initEventListeners() {
            const dropzone = safeGetElement('dropzone');
            const browseBtn = safeGetElement('browseBtn');
            const fileInput = safeGetElement('fileInput');
            const uploadBtn = safeGetElement('uploadBtn');
            const refreshDocsBtn = safeGetElement('refreshDocsBtn');

            if (dropzone) {
                dropzone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropzone.classList.add('border-primary', 'bg-light');
                });
                dropzone.addEventListener('dragleave', () => {
                    dropzone.classList.remove('border-primary', 'bg-light');
                });
                dropzone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('border-primary', 'bg-light');
                    const files = e.dataTransfer.files;
                    if (files && files.length) addFiles(files);
                });
            }
            if (browseBtn && fileInput) {
                browseBtn.addEventListener('click', () => fileInput.click());
            }
            if (fileInput) {
                fileInput.addEventListener('change', (e) => {
                    if (e.target.files && e.target.files.length) {
                        addFiles(e.target.files);
                        fileInput.value = '';
                    }
                });
            }
            if (uploadBtn) uploadBtn.addEventListener('click', uploadFiles);
            if (refreshDocsBtn) refreshDocsBtn.addEventListener('click', fetchDocuments);
        }

        initEventListeners();
        fetchDocuments();
    });
</script>
@endsection
