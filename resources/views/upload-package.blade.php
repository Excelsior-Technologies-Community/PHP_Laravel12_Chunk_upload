<!DOCTYPE html>
<html>
<head>
    <title>Laravel Chunk Upload with Resumable.js</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h4>Chunk Upload with Resumable.js</h4>
            </div>
            <div class="card-body">
                <div class="dropzone" id="upload-area">
                    <div class="text-center p-5 border border-dashed">
                        <h5>Drop files here or click to upload</h5>
                        <p class="text-muted">Large files supported (up to 2GB)</p>
                    </div>
                </div>
                
                <div class="mt-4" id="file-list"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
    <script>
        const r = new Resumable({
            target: '/api/upload',
            chunkSize: 1 * 1024 * 1024, // 1MB
            simultaneousUploads: 3,
            testChunks: true,
            throttleProgressCallbacks: 1,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        // If resumable not supported
        if (!r.support) {
            alert('Your browser does not support resumable uploads');
        }

        // Assign drop zone
        r.assignDrop(document.getElementById('upload-area'));

        // Handle file addition
        r.on('fileAdded', function(file) {
            console.log('File added:', file);
            addFileToList(file);
            r.upload();
        });

        // Handle progress
        r.on('fileProgress', function(file) {
            updateProgress(file);
        });

        // Handle success
        r.on('fileSuccess', function(file, message) {
            const response = JSON.parse(message);
            showSuccess(file, response);
        });

        // Handle error
        r.on('fileError', function(file, message) {
            showError(file, message);
        });

        function addFileToList(file) {
            const list = document.getElementById('file-list');
            const template = `
                <div class="file-item mb-3" id="file-${file.uniqueIdentifier}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${file.fileName}</strong>
                            <small class="text-muted">(${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                        </div>
                        <div>
                            <span class="badge bg-secondary">Pending</span>
                        </div>
                    </div>
                    <div class="progress mt-1" style="height: 10px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', template);
        }

        function updateProgress(file) {
            const element = document.getElementById(`file-${file.uniqueIdentifier}`);
            if (element) {
                const progressBar = element.querySelector('.progress-bar');
                const percentage = file.progress() * 100;
                progressBar.style.width = percentage + '%';
                
                const badge = element.querySelector('.badge');
                badge.textContent = Math.round(percentage) + '%';
                badge.className = 'badge bg-primary';
            }
        }

        function showSuccess(file, response) {
            const element = document.getElementById(`file-${file.uniqueIdentifier}`);
            if (element) {
                const badge = element.querySelector('.badge');
                badge.textContent = 'Completed';
                badge.className = 'badge bg-success';
                
                // Add download link
                const downloadLink = `
                    <div class="mt-1">
                        <a href="${response.url}" target="_blank" class="small">View File</a>
                    </div>
                `;
                element.insertAdjacentHTML('beforeend', downloadLink);
            }
        }

        function showError(file, message) {
            const element = document.getElementById(`file-${file.uniqueIdentifier}`);
            if (element) {
                const badge = element.querySelector('.badge');
                badge.textContent = 'Error';
                badge.className = 'badge bg-danger';
            }
        }
    </script>
</body>
</html>