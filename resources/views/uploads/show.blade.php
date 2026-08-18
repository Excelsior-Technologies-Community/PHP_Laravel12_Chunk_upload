<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Upload Details</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">

    <style>
        body {
            background: #f6f8fc;
            color: #1f2937;
            font-family: Inter, system-ui, -apple-system,
                BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: auto;
        }

        .topbar {
            background: #ffffff;
            border: 1px solid #e8ebf1;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            color: #6b7280;
        }

        .content-card {
            background: #ffffff;
            border: 1px solid #e8ebf1;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        .card-header-modern {
            padding: 20px 22px;
            border-bottom: 1px solid #edf0f5;
        }

        .file-preview {
            width: 90px;
            height: 90px;
            border-radius: 18px;
            background: #eef4ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
        }

        .file-title {
            font-size: 22px;
            font-weight: 700;
            word-break: break-word;
        }

        .detail-row {
            padding: 16px 0;
            border-bottom: 1px solid #edf0f5;
        }

        .detail-row:last-child {
            border-bottom: 0;
        }

        .detail-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 15px;
            font-weight: 600;
            word-break: break-word;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-completed {
            background: #ecfdf3;
            color: #15803d;
        }

        .status-failed {
            background: #fff1f2;
            color: #dc2626;
        }

        .status-other {
            background: #fff7ed;
            color: #c2410c;
        }

        .action-btn {
            min-height: 42px;
            border-radius: 9px;
            font-weight: 600;
        }

        .path-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            padding: 12px;
            font-family: monospace;
            font-size: 13px;
            word-break: break-all;
        }
    </style>

</head>

<body>

    <div class="container-fluid py-4 px-3 px-md-4">

        <div class="page-wrapper">

            {{-- Header --}}
            <div class="topbar mb-4">

                <div class="d-flex flex-column flex-md-row
                        justify-content-between
                        align-items-md-center gap-3">

                    <div>

                        <div class="d-flex align-items-center gap-2 mb-1">

                            <div class="text-primary fs-4">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </div>

                            <h1 class="page-title mb-0">
                                Upload Details
                            </h1>

                        </div>

                        <p class="page-subtitle mb-0">
                            View complete information about this uploaded file.
                        </p>

                    </div>

                    <div>

                        <a
                            href="{{ route('uploads.index') }}"
                            class="btn btn-light border action-btn px-3">

                            <i class="bi bi-arrow-left me-1"></i>

                            Back to Upload History

                        </a>

                    </div>

                </div>

            </div>


            {{-- Main Card --}}
            <div class="content-card">

                {{-- File Header --}}
                <div class="card-header-modern">

                    <div class="d-flex flex-column flex-md-row
                            align-items-md-center
                            justify-content-between gap-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="file-preview">

                                <i class="bi bi-file-earmark"></i>

                            </div>

                            <div>

                                <div class="file-title">

                                    {{ $upload->original_name }}

                                </div>

                                <small class="text-muted">

                                    Upload ID #{{ $upload->id }}

                                </small>

                            </div>

                        </div>


                        {{-- Status --}}
                        <div>

                            @if($upload->status === 'completed')

                            <span class="status-badge status-completed">

                                <i class="bi bi-check-circle-fill"></i>

                                Completed

                            </span>

                            @elseif($upload->status === 'failed')

                            <span class="status-badge status-failed">

                                <i class="bi bi-x-circle-fill"></i>

                                Failed

                            </span>

                            @else

                            <span class="status-badge status-other">

                                <i class="bi bi-clock-fill"></i>

                                {{ ucfirst($upload->status) }}

                            </span>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- Details --}}
                <div class="p-4">

                    <div class="row">

                        {{-- Original File Name --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    Original File Name
                                </div>

                                <div class="detail-value">
                                    {{ $upload->original_name }}
                                </div>

                            </div>

                        </div>


                        {{-- Stored File Name --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    Stored File Name
                                </div>

                                <div class="detail-value">
                                    {{ $upload->file_name }}
                                </div>

                            </div>

                        </div>


                        {{-- File Type --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    File Type
                                </div>

                                <div class="detail-value">

                                    {{ strtoupper($upload->extension ?? 'N/A') }}

                                </div>

                            </div>

                        </div>


                        {{-- MIME Type --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    MIME Type
                                </div>

                                <div class="detail-value">

                                    {{ $upload->mime_type ?? 'N/A' }}

                                </div>

                            </div>

                        </div>


                        {{-- File Size --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    File Size
                                </div>

                                <div class="detail-value">

                                    {{ $upload->formatted_size }}

                                </div>

                            </div>

                        </div>

                        {{-- SHA-256 Checksum --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    SHA-256 Checksum
                                </div>

                                <div class="detail-value">

                                    @if($upload->checksum)

                                    <code class="small text-break">
                                        {{ $upload->checksum }}
                                    </code>

                                    {{-- Integrity Verification Result --}}
                                    <div
                                        id="integrityResult"
                                        class="mt-2">
                                    </div>

                                    @else

                                    <span class="text-muted">
                                        Not available
                                    </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- Status --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    Status
                                </div>

                                <div class="detail-value">

                                    {{ ucfirst($upload->status) }}

                                </div>

                            </div>

                        </div>


                        {{-- Created --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    Uploaded At
                                </div>

                                <div class="detail-value">

                                    {{ $upload->created_at->format('d M Y, h:i A') }}

                                </div>

                            </div>

                        </div>


                        {{-- Updated --}}
                        <div class="col-md-6">

                            <div class="detail-row">

                                <div class="detail-label">
                                    Last Updated
                                </div>

                                <div class="detail-value">

                                    {{ $upload->updated_at->format('d M Y, h:i A') }}

                                </div>

                            </div>

                        </div>


                        {{-- File Path --}}
                        <div class="col-12">

                            <div class="detail-row">

                                <div class="detail-label">
                                    Storage Path
                                </div>

                                <div class="path-box">

                                    {{ $upload->file_path }}

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="d-flex flex-column flex-sm-row
                            gap-2 mt-4 pt-3">

                        <a
                            href="{{ route('uploads.index') }}"
                            class="btn btn-light border action-btn px-4">

                            <i class="bi bi-arrow-left me-1"></i>

                            Back

                        </a>


                        @if($upload->status === 'completed')

                        <a
                            href="{{ Storage::url($upload->file_path) }}"
                            target="_blank"
                            class="btn btn-primary action-btn px-4">

                            <i class="bi bi-box-arrow-up-right me-1"></i>

                            Open File

                        </a>

                        @if($upload->checksum)

                        <button
                            type="button"
                            class="btn btn-outline-success action-btn px-4"
                            id="verifyIntegrityBtn">

                            <i class="bi bi-shield-check me-1"></i>

                            Verify Integrity

                        </button>

                        @endif

                        @endif


                        <form
                            action="{{ route('uploads.destroy', $upload) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this file?');">

                            @csrf

                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-outline-danger action-btn px-4">

                                <i class="bi bi-trash3 me-1"></i>

                                Delete

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

    <script>
        const verifyButton = document.getElementById(
            'verifyIntegrityBtn'
        );

        const integrityResult = document.getElementById(
            'integrityResult'
        );

        if (verifyButton && integrityResult) {

            verifyButton.addEventListener(
                'click',
                async function() {

                    const originalHtml = this.innerHTML;

                    this.disabled = true;

                    this.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-1">
                    </span>
                    Verifying...
                `;

                    integrityResult.innerHTML = '';

                    try {

                        const response = await fetch(
                            "{{ route('api.uploads.verify', $upload) }}"
                        );

                        const result = await response.json();

                        if (result.verified) {

                            integrityResult.innerHTML = `
                            <div class="alert alert-success mt-3 mb-0">

                                <i class="bi bi-shield-check me-2"></i>

                                <strong>
                                    Integrity Verified
                                </strong>

                                <br>

                                <small>
                                    The physical file matches its
                                    stored SHA-256 checksum.
                                </small>

                            </div>
                        `;

                        } else {

                            integrityResult.innerHTML = `
                            <div class="alert alert-danger mt-3 mb-0">

                                <i class="bi bi-shield-x me-2"></i>

                                <strong>
                                    Integrity Check Failed
                                </strong>

                                <br>

                                <small>
                                    The physical file does not match
                                    the stored checksum.
                                </small>

                            </div>
                        `;
                        }

                    } catch (error) {

                        console.error(error);

                        integrityResult.innerHTML = `
                        <div class="alert alert-danger mt-3 mb-0">

                            <i class="bi bi-exclamation-circle me-2"></i>

                            <strong>
                                Verification Error
                            </strong>

                            <br>

                            <small>
                                Unable to verify file integrity.
                            </small>

                        </div>
                    `;

                    } finally {

                        this.disabled = false;

                        this.innerHTML = originalHtml;
                    }
                }
            );
        }
    </script>

</body>

</html>