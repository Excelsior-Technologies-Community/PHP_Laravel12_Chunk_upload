<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Upload History</title>

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
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont,
                "Segoe UI", sans-serif;
        }

        .page-wrapper {
            max-width: 1500px;
            margin: auto;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            color: #6b7280;
        }

        .topbar {
            background: #ffffff;
            border: 1px solid #e8ebf1;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e8ebf1;
            border-radius: 16px;
            padding: 22px;
            height: 100%;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
            transition: .2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(15, 23, 42, .08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .icon-blue {
            background: #eef4ff;
            color: #2563eb;
        }

        .icon-green {
            background: #ecfdf3;
            color: #16a34a;
        }

        .icon-red {
            background: #fff1f2;
            color: #dc2626;
        }

        .icon-purple {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 27px;
            font-weight: 700;
            margin-top: 5px;
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
            background: #ffffff;
        }

        .filter-card {
            padding: 22px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .form-control,
        .form-select {
            min-height: 43px;
            border-color: #dfe3ea;
            border-radius: 9px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .10);
        }

        .btn-modern {
            min-height: 43px;
            border-radius: 9px;
            font-weight: 600;
            font-size: 14px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 15px 18px;
            border-bottom: 1px solid #e8ebf1;
        }

        .table tbody td {
            padding: 16px 18px;
            border-color: #eef1f5;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #fafbff;
        }

        .file-name {
            max-width: 330px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 600;
            color: #1f2937;
        }

        .file-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
        }

        .type-badge {
            background: #f1f5f9;
            color: #475569;
            border-radius: 7px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 10px;
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
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state {
            padding: 70px 20px;
        }

        .empty-icon {
            width: 70px;
            height: 70px;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: auto;
        }

        .pagination-wrapper {
            padding: 18px 22px;
            border-top: 1px solid #edf0f5;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            border-radius: 8px !important;
            margin: 0 2px;
            border-color: #e2e8f0;
            color: #475569;
        }

        .page-item.active .page-link {
            background: #4f46e5;
            border-color: #4f46e5;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 23px;
            }

            .topbar {
                padding: 18px;
            }

            .file-name {
                max-width: 180px;
            }
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
                                <i class="bi bi-cloud-arrow-up-fill"></i>
                            </div>

                            <h1 class="page-title mb-0">
                                Upload History
                            </h1>

                        </div>

                        <p class="page-subtitle mb-0">
                            Manage, search and monitor your uploaded files.
                        </p>
                    </div>

                    <div class="d-flex gap-2">

                        <a href="{{ route('uploads.dashboard') }}"
                            class="btn btn-primary btn-modern px-3">

                            <i class="bi bi-bar-chart-line me-1"></i>
                            Dashboard

                        </a>

                    </div>

                </div>

            </div>


            {{-- Success --}}
            @if(session('success'))

            <div class="alert alert-success border-0 shadow-sm
                        d-flex align-items-center mb-4">

                <i class="bi bi-check-circle-fill me-2"></i>

                <div>
                    {{ session('success') }}
                </div>

                <button type="button"
                    class="btn-close ms-auto"
                    data-bs-dismiss="alert">
                </button>

            </div>

            @endif


            {{-- Statistics --}}
            <div class="row g-3 mb-4">

                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="d-flex justify-content-between">

                            <div>
                                <div class="stat-label">
                                    Total Files
                                </div>

                                <div class="stat-value">
                                    {{ $statistics['total_files'] }}
                                </div>
                            </div>

                            <div class="stat-icon icon-blue">
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

                                <div class="stat-value text-success">
                                    {{ $statistics['completed_files'] }}
                                </div>
                            </div>

                            <div class="stat-icon icon-green">
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

                                <div class="stat-value text-danger">
                                    {{ $statistics['failed_files'] }}
                                </div>
                            </div>

                            <div class="stat-icon icon-red">
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
                                    Total Storage
                                </div>

                                <div class="stat-value">
                                    {{ $statistics['total_size'] }}
                                </div>
                            </div>

                            <div class="stat-icon icon-purple">
                                <i class="bi bi-hdd-stack"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Filters --}}
            <div class="content-card mb-4">

                <div class="card-header-modern">

                    <div class="d-flex align-items-center gap-2">

                        <i class="bi bi-funnel text-primary"></i>

                        <strong>
                            Search & Filters
                        </strong>

                    </div>

                </div>

                <div class="filter-card">

                    <form method="GET"
                        action="{{ route('uploads.index') }}">

                        <div class="row g-3">

                            <div class="col-lg-3 col-md-6">

                                <label class="form-label">
                                    Search
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>

                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search filename..."
                                        value="{{ request('search') }}">

                                </div>

                            </div>


                            <div class="col-lg-2 col-md-6">

                                <label class="form-label">
                                    File Type
                                </label>

                                <select name="extension"
                                    class="form-select">

                                    <option value="">
                                        All Types
                                    </option>

                                    @foreach($extensions as $extension)

                                    <option value="{{ $extension }}"
                                        @selected(request('extension')==$extension)>

                                        {{ strtoupper($extension) }}

                                    </option>

                                    @endforeach

                                </select>

                            </div>


                            <div class="col-lg-2 col-md-6">

                                <label class="form-label">
                                    Status
                                </label>

                                <select name="status"
                                    class="form-select">

                                    <option value="">
                                        All Status
                                    </option>

                                    @foreach($statuses as $status)

                                    <option value="{{ $status }}"
                                        @selected(request('status')==$status)>

                                        {{ ucfirst($status) }}

                                    </option>

                                    @endforeach

                                </select>

                            </div>


                            <div class="col-lg-2 col-md-6">

                                <label class="form-label">
                                    From Date
                                </label>

                                <input
                                    type="date"
                                    name="date_from"
                                    class="form-control"
                                    value="{{ request('date_from') }}">

                            </div>


                            <div class="col-lg-2 col-md-6">

                                <label class="form-label">
                                    To Date
                                </label>

                                <input
                                    type="date"
                                    name="date_to"
                                    class="form-control"
                                    value="{{ request('date_to') }}">

                            </div>


                            <div class="col-lg-1 col-md-6">

                                <label class="form-label">
                                    Show
                                </label>

                                <select name="per_page"
                                    class="form-select">

                                    @foreach([5,10,25,50,100] as $value)

                                    <option value="{{ $value }}"
                                        @selected($perPage==$value)>

                                        {{ $value }}

                                    </option>

                                    @endforeach

                                </select>

                            </div>


                            <div class="col-12 pt-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-modern px-4">

                                    <i class="bi bi-search me-1"></i>
                                    Apply Filters

                                </button>

                                <a
                                    href="{{ route('uploads.index') }}"
                                    class="btn btn-light border btn-modern px-4 ms-1">

                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Reset

                                </a>

                            </div>

                        </div>

                    </form>

                </div>

            </div>


            {{-- Upload Table --}}
            <div class="content-card">

                <div class="card-header-modern">

                    <div class="d-flex justify-content-between
                            align-items-center">

                        <div>

                            <h5 class="mb-1 fw-bold">
                                Uploaded Files
                            </h5>

                            <small class="text-muted">
                                {{ $uploads->total() }} total records
                            </small>

                        </div>

                        <i class="bi bi-folder2-open fs-4 text-muted"></i>

                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>File</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th class="text-end">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($uploads as $upload)

                            <tr>

                                <td class="text-muted fw-semibold">
                                    {{ $upload->id }}
                                </td>


                                <td>

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="file-icon">

                                            <i class="bi bi-file-earmark"></i>

                                        </div>

                                        <div>

                                            <div class="file-name"
                                                title="{{ $upload->original_name }}">

                                                {{ $upload->original_name }}

                                            </div>

                                            <small class="text-muted">
                                                {{ $upload->file_name }}
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <span class="type-badge">

                                        {{ strtoupper($upload->extension ?? 'N/A') }}

                                    </span>

                                </td>


                                <td class="fw-semibold">

                                    {{ $upload->formatted_size }}

                                </td>


                                <td>

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

                                </td>


                                <td>

                                    <div class="fw-semibold">
                                        {{ $upload->created_at->format('d M Y') }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $upload->created_at->format('h:i A') }}
                                    </small>

                                </td>


                                <td class="text-end">

                                    {{-- View --}}
                                    <a
                                        href="{{ route('uploads.show', $upload) }}"
                                        class="btn btn-outline-primary action-btn me-1"
                                        title="View">

                                        <i class="bi bi-eye"></i>

                                    </a>


                                    {{-- Delete --}}
                                    <form
                                        action="{{ route('uploads.destroy', $upload) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this file?');">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-outline-danger action-btn"
                                            title="Delete">

                                            <i class="bi bi-trash3"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                            @empty

                            <tr>

                                <td colspan="7">

                                    <div class="empty-state text-center">

                                        <div class="empty-icon mb-3">

                                            <i class="bi bi-cloud-slash"></i>

                                        </div>

                                        <h5 class="fw-bold">
                                            No uploads found
                                        </h5>

                                        <p class="text-muted mb-0">
                                            Try changing your search or filters.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}
                @if($uploads->hasPages())

                <div class="pagination-wrapper">

                    <div class="d-flex flex-column flex-md-row
                                justify-content-between
                                align-items-md-center gap-3">

                        <div class="text-muted small">

                            Showing
                            <strong>{{ $uploads->firstItem() ?? 0 }}</strong>
                            to
                            <strong>{{ $uploads->lastItem() ?? 0 }}</strong>
                            of
                            <strong>{{ $uploads->total() }}</strong>
                            results

                        </div>

                        <div>
                            {{ $uploads->links('pagination::bootstrap-5') }}
                        </div>

                    </div>

                </div>

                @endif

            </div>

        </div>

    </div>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>