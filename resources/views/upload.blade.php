<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel 12 Chunk Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress {
            height: 25px;
            margin-top: 20px;
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4>Laravel 12 Chunk File Upload</h4>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm">
                            <div class="mb-3">
                                <label for="fileInput" class="form-label">Choose a large file to upload</label>
                                <input type="file" class="form-control" id="fileInput" required>
                                <small class="text-muted">Files will be uploaded in 1MB chunks</small>
                            </div>
                            <button type="submit" class="btn btn-primary" id="uploadBtn">Upload</button>
                            <button type="button" class="btn btn-danger" id="cancelBtn" style="display: none;">Cancel</button>
                        </form>
                        
                        <div class="progress mt-4" style="display: none;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: 0%">0%</div>
                        </div>
                        
                        <div id="result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const fileInput = document.getElementById('fileInput');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a file');
                return;
            }
            
            const CHUNK_SIZE = 1024 * 1024; // 1MB chunks
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            const sessionId = `${Date.now()}-${file.name}-${file.size}`;
            
            // Show progress bar
            const progressBar = document.querySelector('.progress');
            const progressBarInner = document.querySelector('.progress-bar');
            progressBar.style.display = 'block';
            
            document.getElementById('uploadBtn').disabled = true;
            document.getElementById('cancelBtn').style.display = 'inline-block';
            
            // Check which chunks are already uploaded
            let uploadedChunks = [];
            try {
                const progressResponse = await this.checkUploadProgress(sessionId);
                uploadedChunks = progressResponse.uploaded_chunks || [];
                console.log('Already uploaded chunks:', uploadedChunks);
            } catch (error) {
                console.log('No previous upload found');
            }
            
            // Upload chunks
            let isCancelled = false;
            document.getElementById('cancelBtn').onclick = () => {
                isCancelled = true;
                document.getElementById('cancelBtn').disabled = true;
            };
            
            for (let chunkNumber = 1; chunkNumber <= totalChunks; chunkNumber++) {
                if (isCancelled) {
                    alert('Upload cancelled');
                    resetUI();
                    return;
                }
                
                // Skip if chunk already uploaded
                if (uploadedChunks.includes(chunkNumber)) {
                    updateProgress(chunkNumber, totalChunks, progressBarInner);
                    continue;
                }
                
                const start = (chunkNumber - 1) * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, file.size);
                const chunk = file.slice(start, end);
                
                const formData = new FormData();
                formData.append('chunk', chunk);
                formData.append('session_id', sessionId);
                formData.append('chunk_number', chunkNumber);
                formData.append('total_chunks', totalChunks);
                formData.append('filename', file.name);
                
                try {
                    const response = await fetch('/api/upload/chunk', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        updateProgress(chunkNumber, totalChunks, progressBarInner);
                        
                        // If this was the last chunk and we got a URL
                        if (result.url) {
                            document.getElementById('result').innerHTML = `
                                <div class="alert alert-success">
                                    <strong>Success!</strong> File uploaded successfully.<br>
                                    <a href="${result.url}" target="_blank">View File</a>
                                </div>
                            `;
                            resetUI();
                            return;
                        }
                    } else {
                        throw new Error('Upload failed');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    document.getElementById('result').innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Error!</strong> Upload failed. Please try again.
                        </div>
                    `;
                    resetUI();
                    return;
                }
            }
        });
        
        async function checkUploadProgress(sessionId) {
            const formData = new FormData();
            formData.append('session_id', sessionId);
            
            const response = await fetch('/api/upload/progress', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            return await response.json();
        }
        
        function updateProgress(current, total, progressBar) {
            const percentage = Math.round((current / total) * 100);
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';
        }
        
        function resetUI() {
            document.getElementById('uploadBtn').disabled = false;
            document.getElementById('cancelBtn').style.display = 'none';
            document.getElementById('cancelBtn').disabled = false;
        }
    </script>
</body>
</html>