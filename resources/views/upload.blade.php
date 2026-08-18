<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

    <title>Laravel 12 Chunk Upload</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">

    <style>
        .progress {
            height: 25px;
            margin-top: 20px;
        }

        .progress-bar {
            transition: width 0.3s ease;
        }

        .checksum-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            word-break: break-all;
        }
    </style>

</head>

<body>

    <div class="container mt-5">

        <div class="row">

            <div class="col-md-8 offset-md-2">

                <div class="card">

                    <div class="card-header">

                        <h4 class="mb-0">
                            Laravel 12 Chunk File Upload
                        </h4>

                    </div>

                    <div class="card-body">

                        <form id="uploadForm">

                            <div class="mb-3">

                                <label
                                    for="fileInput"
                                    class="form-label">

                                    Choose a large file to upload

                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    id="fileInput"
                                    required>

                                <small class="text-muted">

                                    Files will be uploaded in 1MB chunks

                                </small>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="uploadBtn">

                                <i class="bi bi-cloud-arrow-up me-1"></i>

                                Upload

                            </button>

                            <button
                                type="button"
                                class="btn btn-danger"
                                id="cancelBtn"
                                style="display: none;">

                                <i class="bi bi-x-circle me-1"></i>

                                Cancel

                            </button>

                        </form>


                        {{-- Progress Bar --}}

                        <div
                            class="progress mt-4"
                            style="display: none;">

                            <div
                                class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar"
                                style="width: 0%">

                                0%

                            </div>

                        </div>


                        {{-- Upload Result --}}

                        <div
                            id="result"
                            class="mt-3">
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <script>
        document
            .getElementById('uploadForm')
            .addEventListener('submit', async (e) => {

                e.preventDefault();

                const fileInput =
                    document.getElementById('fileInput');

                const file =
                    fileInput.files[0];

                if (!file) {

                    alert('Please select a file');

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Chunk Configuration
                |--------------------------------------------------------------------------
                */

                const CHUNK_SIZE =
                    1024 * 1024; // 1MB

                const totalChunks =
                    Math.ceil(
                        file.size / CHUNK_SIZE
                    );


                /*
                |--------------------------------------------------------------------------
                | Stable Upload Session
                |--------------------------------------------------------------------------
                |
                | Keep the same session ID when the user reloads the page
                | and selects the same file again.
                |
                */

                const sessionKey = 'chunk-upload-session';

                const savedSessions = JSON.parse(
                    localStorage.getItem(sessionKey) || '{}'
                );

                const fileKey =
                    `${file.name}-${file.size}-${file.lastModified}`;

                let sessionId = savedSessions[fileKey];

                if (!sessionId) {

                    sessionId = crypto.randomUUID();

                    savedSessions[fileKey] = sessionId;

                    localStorage.setItem(
                        sessionKey,
                        JSON.stringify(savedSessions)
                    );

                }

                console.log(
                    'Upload session ID:',
                    sessionId
                );

                /*
                |--------------------------------------------------------------------------
                | UI Elements
                |--------------------------------------------------------------------------
                */

                const progressContainer =
                    document.querySelector('.progress');

                const progressBar =
                    document.querySelector('.progress-bar');

                const uploadButton =
                    document.getElementById('uploadBtn');

                const cancelButton =
                    document.getElementById('cancelBtn');

                const resultContainer =
                    document.getElementById('result');


                /*
                |--------------------------------------------------------------------------
                | Show Progress
                |--------------------------------------------------------------------------
                */

                progressContainer.style.display =
                    'block';

                uploadButton.disabled =
                    true;

                cancelButton.style.display =
                    'inline-block';

                resultContainer.innerHTML =
                    '';


                /*
                |--------------------------------------------------------------------------
                | Check Existing Upload Progress
                |--------------------------------------------------------------------------
                */

                let uploadedChunks = [];

                try {

                    const progressResponse =
                        await checkUploadProgress(
                            sessionId
                        );

                    uploadedChunks =
                        progressResponse.uploaded_chunks || [];

                    console.log(
                        'Already uploaded chunks:',
                        uploadedChunks
                    );

                } catch (error) {

                    console.log(
                        'No previous upload found'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Cancellation Flag
                |--------------------------------------------------------------------------
                */

                let isCancelled =
                    false;


                cancelButton.onclick =
                    () => {

                        isCancelled =
                            true;

                        cancelButton.disabled =
                            true;

                    };


                /*
                |--------------------------------------------------------------------------
                | Upload Chunks
                |--------------------------------------------------------------------------
                */

                for (
                    let chunkNumber = 1; chunkNumber <= totalChunks; chunkNumber++
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Check Cancellation
                    |--------------------------------------------------------------------------
                    */

                    if (isCancelled) {

                        resultContainer.innerHTML = `

                            <div class="alert alert-warning">

                                <i class="bi bi-exclamation-triangle me-1"></i>

                                <strong>Upload Cancelled</strong>

                                <br>

                                The upload was cancelled.

                            </div>

                        `;

                        resetUI();

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Skip Already Uploaded Chunks
                    |--------------------------------------------------------------------------
                    */

                    if (
                        uploadedChunks.includes(
                            chunkNumber
                        )
                    ) {

                        updateProgress(
                            chunkNumber,
                            totalChunks,
                            progressBar
                        );

                        continue;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Calculate Chunk Position
                    |--------------------------------------------------------------------------
                    */

                    const start =
                        (chunkNumber - 1) *
                        CHUNK_SIZE;

                    const end =
                        Math.min(
                            start + CHUNK_SIZE,
                            file.size
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Create File Chunk
                    |--------------------------------------------------------------------------
                    */

                    const chunk =
                        file.slice(
                            start,
                            end
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Create Form Data
                    |--------------------------------------------------------------------------
                    */

                    const formData =
                        new FormData();

                    formData.append(
                        'chunk',
                        chunk
                    );

                    formData.append(
                        'session_id',
                        sessionId
                    );

                    formData.append(
                        'chunk_number',
                        chunkNumber
                    );

                    formData.append(
                        'total_chunks',
                        totalChunks
                    );

                    formData.append(
                        'filename',
                        file.name
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Upload Chunk
                    |--------------------------------------------------------------------------
                    */

                    try {

                        const response =
                            await fetch(
                                '/api/upload/chunk', {
                                    method: 'POST',

                                    body: formData,

                                    headers: {
                                        'X-CSRF-TOKEN': document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            .content
                                    }
                                }
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Convert Response To JSON
                        |--------------------------------------------------------------------------
                        */

                        const result =
                            await response.json();


                        /*
                        |--------------------------------------------------------------------------
                        | Successful Chunk
                        |--------------------------------------------------------------------------
                        */

                        if (result.success) {

                            updateProgress(
                                chunkNumber,
                                totalChunks,
                                progressBar
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Final Upload Response
                            |--------------------------------------------------------------------------
                            */

                            if (result.url) {

                                resultContainer.innerHTML = `

                                    <div class="alert alert-success">

                                        <h5 class="alert-heading">

                                            <i class="bi bi-check-circle-fill me-1"></i>

                                            Upload Successful

                                        </h5>

                                        <hr>

                                        <p class="mb-2">

                                            <strong>File:</strong>

                                            ${escapeHtml(
                                                result.file_name ||
                                                file.name
                                            )}

                                        </p>

                                        <p class="mb-2">

                                            <strong>Size:</strong>

                                            ${formatBytes(
                                                result.size ??
                                                file.size
                                            )}

                                        </p>

                                        <p class="mb-3">

                                            <strong>SHA-256:</strong>

                                        </p>

                                        <div class="checksum-box mb-3">

                                            <code class="text-break">

                                                ${escapeHtml(
                                                    result.checksum ||
                                                    'Not available'
                                                )}

                                            </code>

                                        </div>

                                        <a
                                            href="${result.url}"
                                            target="_blank"
                                            class="btn btn-success">

                                            <i class="bi bi-box-arrow-up-right me-1"></i>

                                            View File

                                        </a>

                                    </div>

                                `;


                                /*
                                |--------------------------------------------------------------------------
                                | Remove Completed Session
                                |--------------------------------------------------------------------------
                                */

                                delete savedSessions[fileKey];

                                localStorage.setItem(
                                    sessionKey,
                                    JSON.stringify(savedSessions)
                                );


                                resetUI();

                                return;

                            }

                        } else {

                            throw new Error(
                                result.message ||
                                'Upload failed'
                            );

                        }

                    } catch (error) {

                        console.error(
                            'Upload error:',
                            error
                        );


                        resultContainer.innerHTML = `

                            <div class="alert alert-danger">

                                <h5 class="alert-heading">

                                    <i class="bi bi-x-circle-fill me-1"></i>

                                    Upload Failed

                                </h5>

                                <p class="mb-0">

                                    ${escapeHtml(
                                        error.message ||
                                        'Upload failed. Please try again.'
                                    )}

                                </p>

                            </div>

                        `;


                        resetUI();

                        return;

                    }

                }

            });


        /*
        |--------------------------------------------------------------------------
        | Check Upload Progress
        |--------------------------------------------------------------------------
        */

        async function checkUploadProgress(
            sessionId
        ) {

            const formData =
                new FormData();

            formData.append(
                'session_id',
                sessionId
            );


            const response =
                await fetch(
                    '/api/upload/progress', {
                        method: 'POST',

                        body: formData,

                        headers: {
                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content
                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'Unable to check upload progress.'
                );

            }


            return await response.json();

        }


        /*
        |--------------------------------------------------------------------------
        | Update Progress Bar
        |--------------------------------------------------------------------------
        */

        function updateProgress(
            current,
            total,
            progressBar
        ) {

            const percentage =
                Math.round(
                    (current / total) * 100
                );


            progressBar.style.width =
                percentage + '%';


            progressBar.textContent =
                percentage + '%';

        }


        /*
        |--------------------------------------------------------------------------
        | Format File Size
        |--------------------------------------------------------------------------
        */

        function formatBytes(
            bytes
        ) {

            if (
                bytes === null ||
                bytes === undefined ||
                isNaN(bytes)
            ) {

                return 'Unknown';

            }


            if (bytes <= 0) {

                return '0 Bytes';

            }


            const units = [
                'Bytes',
                'KB',
                'MB',
                'GB',
                'TB'
            ];


            const power =
                Math.floor(
                    Math.log(bytes) /
                    Math.log(1024)
                );


            const index =
                Math.min(
                    power,
                    units.length - 1
                );


            return (
                    Math.round(
                        (
                            bytes /
                            Math.pow(
                                1024,
                                index
                            )
                        ) * 100
                    ) / 100
                ) +
                ' ' +
                units[index];

        }


        /*
        |--------------------------------------------------------------------------
        | Escape HTML
        |--------------------------------------------------------------------------
        |
        | Prevents API/file-name values from being inserted as raw HTML.
        |
        */

        function escapeHtml(
            value
        ) {

            const div =
                document.createElement('div');

            div.textContent =
                value ?? '';

            return div.innerHTML;

        }


        /*
        |--------------------------------------------------------------------------
        | Reset Upload UI
        |--------------------------------------------------------------------------
        */

        function resetUI() {

            document.getElementById(
                'uploadBtn'
            ).disabled = false;


            document.getElementById(
                'cancelBtn'
            ).style.display = 'none';


            document.getElementById(
                'cancelBtn'
            ).disabled = false;

        }
    </script>

</body>

</html>