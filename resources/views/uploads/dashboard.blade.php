<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Upload Dashboard</title>

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
            max-width: 1500px;
            margin: auto;
        }

        .topbar {
            background: #ffffff;
            border: 1px solid #e8ebf1;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -.5px;
        }

        .subtitle {
            color: #6b7280;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e8ebf1;
            border-radius: 16px;
            padding: 23px;
            height: 100%;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
            transition: .2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(15, 23, 42, .08);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .blue {
            background: #eef4ff;
            color: #2563eb;
        }

        .green {
            background: #ecfdf3;
            color: #16a34a;
        }

        .red {
            background: #fff1f2;
            color: #dc2626;
        }

        .purple {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .stat-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            margin-top: 5px;
        }

        .modern-card {
            background: #ffffff;
            border: 1px solid #e8ebf1;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        .modern-card-header {
            padding: 20px 22px;
            border-bottom: 1px solid #edf0f5;
        }

        .modern-card-body {
            padding: 22px;
        }

        .storage-box {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(79, 70, 229, .20);
        }

        .storage-value {
            font-size: 34px;
            font-weight: 700;
        }

        .type-row {
            padding: 14px 0;
            border-bottom: 1px solid #eef1f5;
        }

        .type-row:last-child {
            border-bottom: 0;
        }

        .type-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
        }

        .type-count {
            background: #eef4ff;
            color: #2563eb;
            border-radius: 20px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 700;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-bottom: 1px solid #e8ebf1;
            padding: 14px 16px;
        }

        .table td {
            padding: 15px 16px;
            border-color: #eef1f5;
        }

        .table tbody tr:hover {
            background: #fafbff;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 20px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 700;
        }

        .completed {
            background: #ecfdf3;
            color: #15803d;
        }

        .failed {
            background: #fff1f2;
            color: #dc2626;
        }

        .other {
            background: #fff7ed;
            color: #c2410c;
        }

        .file-name {
            max-width: 220px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-weight: 600;
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

                            <i class="bi bi-speedometer2 text-primary fs-3"></i>

                            <h1 class="page-title mb-0">
                                Upload Dashboard
                            </h1>

                        </div>

                        <p class="subtitle mb-0">
                            Monitor file uploads, storage and recent activity.
                        </p>

                    </div>

                    <a
                        href="{{ route('uploads.index') }}"
                        class="btn btn-primary px-4">

                        <i class="bi bi-clock-history me-1"></i>
                        Upload History

                    </a>

                </div>

            </div>


            {{-- Statistics --}}
            <div class="row g-3 mb-4">

                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="d-flex justify-content-between">

                            <div>

                                <div class="stat-label">
                                    Total Files
                                </div>

                                <div class="stat-number">
                                    {{ $statistics['total_files'] }}
                                </div>

                            </div>

                            <div class="stat-icon blue">
                                <i class="bi bi-files"></i>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="d-flex justify-content-between">

                            <div>

                                <div class="stat-label">
                                    Completed
                                </div>

                                <div class="stat-number text-success">
                                    {{ $statistics['completed_files'] }}
                                </div>

                            </div>

                            <div class="stat-icon green">
                                <i class="bi bi-check2-circle"></i>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="d-flex justify-content-between">

                            <div>

                                <div class="stat-label">
                                    Failed
                                </div>

                                <div class="stat-number text-danger">
                                    {{ $statistics['failed_files'] }}
                                </div>

                            </div>

                            <div class="stat-icon red">
                                <i class="bi bi-x-circle"></i>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="d-flex justify-content-between">

                            <div>

                                <div class="stat-label">
                                    Today's Uploads
                                </div>

                                <div class="stat-number">
                                    {{ $statistics['today_files'] }}
                                </div>

                            </div>

                            <div class="stat-icon purple">
                                <i class="bi bi-calendar-check"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Storage --}}
            <div class="storage-box mb-4">

                <div class="row align-items-center">

                    <div class="col-md-8">

                        <div class="opacity-75 small mb-1">
                            TOTAL STORAGE USED
                        </div>

                        <div class="storage-value">
                            {{ $statistics['total_size'] }}
                        </div>

                        <div class="opacity-75 mt-1">
                            Storage currently used by uploaded files.
                        </div>

                    </div>

                    <div class="col-md-4 text-md-end mt-3 mt-md-0">

                        <i class="bi bi-cloud-arrow-up"
                            style="font-size: 70px; opacity:.2;"></i>

                    </div>

                </div>

            </div>


            <div class="row g-4">

                {{-- Files By Type --}}
                <div class="col-lg-5">

                    <div class="modern-card">

                        <div class="modern-card-header">

                            <div class="d-flex justify-content-between
                                    align-items-center">

                                <div>

                                    <h5 class="fw-bold mb-1">
                                        Files By Type
                                    </h5>

                                    <small class="text-muted">
                                        Upload distribution
                                    </small>

                                </div>

                                <i class="bi bi-pie-chart text-primary fs-4"></i>

                            </div>

                        </div>


                        <div class="modern-card-body">

                            @forelse($fileTypes as $type)

                            <div class="type-row
                                        d-flex justify-content-between
                                        align-items-center">

                                <div class="d-flex align-items-center gap-3">

                                    <div class="type-icon">
                                        <i class="bi bi-file-earmark"></i>
                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            {{ strtoupper($type->extension ?? 'Unknown') }}
                                        </div>

                                        <small class="text-muted">
                                            File type
                                        </small>

                                    </div>

                                </div>

                                <span class="type-count">
                                    {{ $type->total }}
                                </span>

                            </div>

                            @empty

                            <div class="text-center py-5">

                                <i class="bi bi-folder-x fs-1 text-muted"></i>

                                <p class="text-muted mt-2 mb-0">
                                    No file type data available.
                                </p>

                            </div>

                            @endforelse

                        </div>

                    </div>

                </div>


                {{-- Recent Uploads --}}
                <div class="col-lg-7">

                    <div class="modern-card">

                        <div class="modern-card-header">

                            <div class="d-flex justify-content-between
                                    align-items-center">

                                <div>

                                    <h5 class="fw-bold mb-1">
                                        Recent Uploads
                                    </h5>

                                    <small class="text-muted">
                                        Latest uploaded files
                                    </small>

                                </div>

                                <i class="bi bi-clock-history text-primary fs-4"></i>

                            </div>

                        </div>


                        <div class="table-responsive">

                            <table class="table align-middle">

                                <thead>

                                    <tr>

                                        <th>File</th>
                                        <th>Size</th>
                                        <th>Status</th>
                                        <th>Date</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($recentUploads as $upload)

                                    <tr>

                                        <td>

                                            <div class="file-name"
                                                title="{{ $upload->original_name }}">

                                                {{ $upload->original_name }}

                                            </div>

                                        </td>


                                        <td class="fw-semibold">
                                            {{ $upload->formatted_size }}
                                        </td>


                                        <td>

                                            @if($upload->status === 'completed')

                                            <span class="status completed">

                                                <i class="bi bi-check-circle-fill"></i>
                                                Completed

                                            </span>

                                            @elseif($upload->status === 'failed')

                                            <span class="status failed">

                                                <i class="bi bi-x-circle-fill"></i>
                                                Failed

                                            </span>

                                            @else

                                            <span class="status other">

                                                <i class="bi bi-clock-fill"></i>
                                                {{ ucfirst($upload->status) }}

                                            </span>

                                            @endif

                                        </td>


                                        <td>

                                            <div class="fw-semibold">
                                                {{ $upload->created_at->format('d M Y') }}
                                            </div>

                                            <small class="text-muted">
                                                {{ $upload->created_at->format('h:i A') }}
                                            </small>

                                        </td>

                                    </tr>

                                    @empty

                                    <tr>

                                        <td colspan="4"
                                            class="text-center py-5">

                                            <i class="bi bi-cloud-slash
                                                  fs-1 text-muted"></i>

                                            <p class="text-muted mt-2 mb-0">
                                                No uploads found.
                                            </p>

                                        </td>

                                    </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>