<?php
session_start();
include_once 'config/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$switches = get_all_switches();

$months_years = get_months_years_from_switches();

$filtered_switches = $switches;

if (isset($_GET['month_year']) && $_GET['month_year'] !== "") {
    $selected_month_year = $_GET['month_year'];
    $filtered_switches = get_switches_by_month_year($selected_month_year);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = sanitize_input($_POST['name']);
    $ip_address = sanitize_input($_POST['ip_address']);
    $area = sanitize_input($_POST['area']);
    $status = sanitize_input($_POST['status']);

    if (insert_switch($name, $ip_address, $area, $status)) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMOSI - Aplikasi Monitoring Switch | PT. Kilang Pertamina Internasional RU II Sungai Pakning</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css">
</head>

<body>
    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="#">Aplikasi Monitoring Switch</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active me-2" data-bs-toggle="modal" data-bs-target="#addSwitchModal">Tambah Switch</a>
                    </li>
                </ul>
                <form class="d-flex" role="logout">
                    <a href="logout.php" class="btn btn-danger">Keluar</a>
                </form>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Add Switch Modal -->
    <div class="modal fade" id="addSwitchModal" tabindex="-1" aria-labelledby="addSwitchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSwitchModalLabel">Tambah Switch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSwitchForm" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Switch Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter switch name" required>
                        </div>
                        <div class="mb-3">
                            <label for="ip_address" class="form-label">IP Address</label>
                            <input type="text" class="form-control" id="ip_address" name="ip_address" placeholder="Enter IP address" required>
                        </div>
                        <div class="mb-3">
                            <label for="area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="area" name="area" placeholder="Enter area" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="up">Up</option>
                                <option value="down">Down</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan</button>
                    </form>
                    <div id="addSwitchResponse" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="text-center mb-4">
            <h1 class="mb-3">Welcome to Web Monitoring</h1>
            <span class="badge bg-info" id="last-check">Last Check: --:--</span>
        </div>

        <!-- Dropdown Filter -->
        <div class="mb-4">
            <form method="GET" action="">
                <div class="input-group">
                    <select name="month_year" class="form-select" onchange="this.form.submit()">
                        <option value="">Pilih Bulan</option>
                        <?php foreach ($months_years as $month_year): ?>
                            <option value="<?php echo $month_year; ?>"
                                <?php echo isset($_GET['month_year']) && $_GET['month_year'] === $month_year ? 'selected' : ''; ?>>
                                <?php echo $month_year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Tabel Data Switch -->
        <div class="table-responsive">
            <table id="switch-table" class="table table-striped">
                <thead class="table-info">
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>IP Address</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th>Last Down</th>
                        <th>Downtime</th>
                        <th>Availability</th>
                        <th>#</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($filtered_switches)):
                        $status_class = $row['status'] === 'up' ? 'bg-success' : 'bg-danger';
                    ?>
                        <tr>
                            <td><?php echo $no++; ?>.</td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['ip_address']; ?></td>
                            <td><?php echo $row['area']; ?></td>
                            <td>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $row['last_down'] ?: 'N/A'; ?></td>
                            <td><?php echo $row['downtime'] ?: '0m'; ?></td>
                            <td><?php echo $row['availability'] ?: '100%'; ?></td>
                            <td>
                                <button class="badge text-bg-info border-0 view-details" data-id="<?php echo $row['id']; ?>">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- DataTables JavaScript  -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
    <!-- My JS -->
    <script src="js/admin.js"></script>
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            $('#addSwitchForm').on('submit', function(event) {
                event.preventDefault();

                $.ajax({
                    url: 'dashboard.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Switch added successfully!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#addSwitchModal').modal('hide');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to add switch. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });

        function confirmLogout(event) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to log out from the system!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, log out',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }

        document.querySelector('.btn-danger').addEventListener('click', confirmLogout);
    </script>
</body>

</html>