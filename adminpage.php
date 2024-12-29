<?php
session_start();
require 'db.php';

$activeForm = $_GET['form'] ?? 'home';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

//===============================================================================

$activeForm = $_GET['form'] ?? 'dashboard';
$tableName = $_GET['table'] ?? null;
//Fetch Dashboard data
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
$totalNGOs = $conn->query("SELECT COUNT(*) as count FROM ngo")->fetch_assoc()['count'] ?? 0;
$totalFoodCollected = $conn->query("SELECT SUM(quantity) as sum FROM food WHERE status = 'granted'")->fetch_assoc()['sum'] ?? 0;
$totalWorkers = $conn->query("SELECT COUNT(*) as count FROM workers")->fetch_assoc()['count'] ?? 0;
$totalVehicles = $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc()['count'] ?? 0;

// Fetch table data when clicked from dashboard
$tableData = [];
if ($tableName) {
    $validTables = ['users', 'ngo', 'food', 'workers', 'vehicles'];
    if (in_array($tableName, $validTables)) {
        $stmt = $conn->prepare("SELECT * FROM $tableName");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tableData[] = $row;
        }
        $stmt->close();
    }
}

//=============================================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'] ?? null;
    $approvalDateTime = $_POST['approval_datetime'] ?? null;
    $table = $_POST['table'] ?? null;

    if ($requestId && $table) {
        $allowedTables = ['food', 'waste', 'cloth']; // Only allow specific tables
        if (in_array($table, $allowedTables)) {
            if (isset($_POST['approve_request'])) {
                if (!empty($_POST['approval_datetime'])) {
                    $stmt = $conn->prepare("UPDATE $table SET approval_datetime = ?, status = 'granted' WHERE id = ?");
                    $stmt->bind_param('si', $approvalDateTime, $requestId);

                    if ($stmt->execute()) {
                        $message = ucfirst($table) . " request approved successfully!";
                    } else {
                        $message = "Failed to approve the $table request. Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    echo '<script> alert("Please provide a date and time for approval.")</script>';
                }
            } elseif (isset($_POST['reject_request'])) {
                $stmt = $conn->prepare("UPDATE $table SET status = 'rejected' WHERE id = ?");
                $stmt->bind_param('i', $requestId);

                if ($stmt->execute()) {
                    $message = ucfirst($table) . " request rejected successfully!";
                } else {
                    $message = "Failed to approve the $table request. Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
//=================================================================================

// Approve or Reject Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? 0;
    $approvalDateTime = $_POST['approval_datetime'] ?? null;
    $table = $_POST['table'] ?? '';


    // Validate table name to prevent SQL injection
    if ($request_id && $table) {
        $allowedTables = ['food_requests', 'cloth_requests'];
        if (in_array($table, $allowedTables)) {
            if (isset($_POST['approve'])) {
                if (!empty($_POST['approval_datetime'])) {
                    // Approve the request (set status to "grant")
                    $stmt = $conn->prepare("UPDATE $table SET approval_datetime = ?, status = 'granted' WHERE id = ?");
                    $stmt->bind_param('si', $approvalDateTime, $request_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    echo '<script> alert("Please provide a date and time for approval.")</script>';
                }
            } elseif (isset($_POST['reject'])) {
                // Reject the request (update status to 'rejected')
                $stmt = $conn->prepare("UPDATE $table SET status = 'rejected' WHERE id = ?");
                $stmt->bind_param("i", $request_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['completed'])) {
    $request_id = $_POST['request_id'] ?? 0;
    $table = $_POST['table'] ?? '';

    $stmt = $conn->prepare("UPDATE $table SET completed = 1 WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->close();
}


//=================================================================================
// Fetch Pending Requests for Users
$foodRequests = [];
$wasteRequests = [];
$clothRequests = [];
$todaysrequests = [];

if ($activeForm === 'user_requests') {
    // Debug food requests
    $stmt = $conn->prepare("
            SELECT f.id, u.name AS username, u.mobileno,f.location, f.type_of_food, f.quantity,
                TIMESTAMPDIFF(MINUTE, f.submitted_at, NOW()) AS time_diff
            FROM food f
            JOIN users u ON f.user_id = u.id
            WHERE f.status = 'pending'
            ORDER BY f.submitted_at ASC
        ");

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $foodRequests[] = $row;
        }
        $stmt->close();
    } else {
        echo "Error in food requests query: " . $conn->error;
    }

    // Debug waste requests
    $stmt = $conn->prepare("
            SELECT w.id, u.name AS username, w.location, w.type_of_waste, w.quantity,
                TIMESTAMPDIFF(MINUTE, w.submitted_at, NOW()) AS time_diff
            FROM waste w
            JOIN users u ON w.user_id = u.id
            WHERE w.status = 'pending'
            ORDER BY w.submitted_at ASC
        ");

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $wasteRequests[] = $row;
        }
        $stmt->close();
    } else {
        echo "Error in waste requests query: " . $conn->error;
    }

    // Debug cloth requests
    $stmt = $conn->prepare("
            SELECT c.id, u.name AS username, c.location, c.type_of_cloth, c.quantity,
                TIMESTAMPDIFF(MINUTE, c.submitted_at, NOW()) AS time_diff
            FROM cloth c
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'pending'
            ORDER BY c.submitted_at ASC
        ");

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $clothRequests[] = $row;
        }
        $stmt->close();
    } else {
        echo "Error in cloth requests query: " . $conn->error;
    }
} else if ($activeForm === 'ngo_requests') {
    // Fetch food requests
    $stmt = $conn->prepare("
        SELECT f.id, u.name AS username, u.mobileno, f.food_type, f.food_category, f.quantity,
            TIMESTAMPDIFF(MINUTE, f.requested_at, NOW()) AS time_diff
        FROM food_requests f
        JOIN ngo u ON f.user_id = u.id
        WHERE f.status = 'pending'
        ORDER BY f.requested_at ASC
        ");

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $foodRequests[] = $row;
    }
    $stmt->close();

    // Fetch cloth requests
    $stmt = $conn->prepare("
        SELECT c.id, u.name AS username,u.mobileno, c.type_of_cloth, c.quantity,
            TIMESTAMPDIFF(MINUTE, c.requested_at, NOW()) AS time_diff
        FROM cloth_requests c
        JOIN ngo u ON c.user_id = u.id
        WHERE c.status = 'pending'
        ORDER BY c.requested_at ASC
        ");

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $clothRequests[] = $row;
    }
    $stmt->close();
} elseif ($activeForm === 'todays_collection') {
    $stmt = $conn->prepare("
    SELECT id, location, type_of_food, food_category, quantity, name, mobile, approval_datetime 
    FROM food 
    WHERE status = 'granted' 
      AND approval_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
      AND completed = FALSE
");

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $todaysrequests[] = $row;
    }
    $stmt->close();
}
//==========================================================================================
$userfoodrequests = $conn->query("SELECT COUNT(*) as count FROM food where status='pending'")->fetch_assoc()['count'] ?? 0;
$userclothrequests = $conn->query("SELECT COUNT(*) as count FROM cloth where status='pending'")->fetch_assoc()['count'] ?? 0;
$userwasterequests = $conn->query("SELECT COUNT(*) as count FROM waste where status='pending'")->fetch_assoc()['count'] ?? 0;
$userrequests = $userfoodrequests + $userclothrequests + $userwasterequests;

$ngofoodrequests = $conn->query("SELECT COUNT(*) as count FROM food_requests where status='pending'")->fetch_assoc()['count'] ?? 0;
$ngoclothrequests = $conn->query("SELECT COUNT(*) as count FROM cloth_requests where status='pending'")->fetch_assoc()['count'] ?? 0;
$ngorequests = $ngofoodrequests + $ngoclothrequests;

//======================================================================================


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: antiquewhite;
        }

        /* Navbar styles */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background-color: #37517e;
            color: white;
            display: flex;
            justify-content: space-between;
            padding: 5px 20px;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 20px;
        }

        .navbar .right {
            display: flex;
            align-items: center;
        }

        .container {
            padding: 20px;

        }

        .my-content {
            margin: 100px 100px;
            border-width: 15px;
            border-style: double;
            Border-color: green;
            box-shadow: 0px 0px 10px black;

        }

        h2 {
            margin-top: 0;
            font-size: 30px;
            color: #333;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;

        }

        button {
            margin: 0 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            background-color: #007bff;
            font-size: 20px;
        }

        input,
        select,
        button {
            margin: 5px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button.logout-btn {
            background-color: #dc3545;
            font-size: 15px;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
        }

        .checkbox-group label {
            margin-right: 10px;
        }

        .success {
            color: green;
            margin-bottom: 10px;
            text-align: center;
        }

        .bttn {
            width: min-content;
            font-size: 18px;
            padding: 5px;
        }

        th {
            text-align: center;
            font-size: 18px;
        }

        td {
            font-size: 17px;
            text-align: center;
        }

        form {
            display: flex;
            gap: 20px;
        }

        p {
            font-size: large;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <div>
            <a href="index.php">Home</a>
            <a href="?form=dashboard">Dashboard</a>
            <a href="?form=user_requests">User Requests <span>(<?php echo $userrequests ?>)</span></a>
            <a href="?form=ngo_requests">NGO Requests (<?php echo $ngorequests ?>)</a>
            <a href="?form=todays_collection">Today's Collection</a>
        </div>
        <div class="right">
            <span style="font-size: 20px;"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            <a href="adminpage.php?logout=1" style="margin-right: 50px;"><button class="logout-btn">Logout</button></a>
        </div>
    </div>
    <div class="my-content">
        <?php if ($activeForm === 'dashboard'): ?>
            <div class="container">
                <!-- Dashboard Metrics -->
                <h2>Dashboard</h2>
                <div class="row mb-0">
                    <div class="col-3">
                        <div class="card text-center shadow p-3 mb-5 bg-white rounded">
                            <div class="card-body">
                                <h5>Total Users</h5>
                                <p><?= $totalUsers; ?></p>
                                <a href="?form=dashboard&table=users" class="btn btn-primary" style="background-color: #37517e;">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card text-center shadow p-3 mb-5 bg-white rounded">
                            <div class="card-body">
                                <h5>Total NGOs</h5>
                                <p><?= $totalNGOs; ?></p>
                                <a href="?form=dashboard&table=ngo" class="btn btn-primary" style="background-color: #37517e;">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card text-center shadow p-3 mb-5 bg-white rounded">
                            <div class="card-body">
                                <h5>Total Food Collection</h5>
                                <p><?= $totalFoodCollected; ?></p>
                                <a href="?form=dashboard&table=food" class="btn btn-primary" style="background-color: #37517e;">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card text-center shadow p-3 mb-5 bg-white rounded">
                            <div class="card-body">
                                <h5>Total Workers</h5>
                                <p><?= $totalWorkers; ?></p>
                                <a href="?form=dashboard&table=workers" class="btn btn-primary" style="background-color: #37517e;">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-3 mt-3">
                        <div class="card text-center shadow p-3 mb-5 bg-white rounded">
                            <div class="card-body">
                                <h5>Total Vehicles</h5>
                                <p><?= $totalVehicles; ?></p>
                                <a href="?form=dashboard&table=vehicles" class="btn btn-primary" style="background-color: #37517e;">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Data Container -->
                <?php if ($tableName): ?>
                    <div class="table-responsive">
                        <h3 class="text-center">Details of <?= ucfirst($tableName); ?></h3>
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <?php if (!empty($tableData)): ?>
                                        <?php foreach (array_keys($tableData[0]) as $header): ?>
                                            <th><?= htmlspecialchars(ucfirst($header)); ?></th>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tableData)): ?>
                                    <?php foreach ($tableData as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $value): ?>
                                                <td><?= htmlspecialchars($value); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="100%" class="text-center">No data available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Main Container -->
        <?php if ($activeForm === 'user_requests'): ?>
            <div class="container"">
                <h2>User Requests - Food</h2>
                <table border=" 1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                <thead class="table-primary">
                    <tr>
                        <th>Username</th>
                        <th>Mobile No</th>
                        <th>Location</th>
                        <th>Food Type</th>
                        <th>Quantity</th>
                        <th>Time (HH:MM)</th>
                        <th>Collection Date & Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($foodRequests)): ?>
                        <?php foreach ($foodRequests as $request): ?>
                            <tr>
                                <td><?= htmlspecialchars($request['username']); ?></td>
                                <td><?= htmlspecialchars($request['mobileno']); ?></td>
                                <td><?= htmlspecialchars($request['location']); ?></td>
                                <td><?= htmlspecialchars($request['type_of_food']); ?></td>
                                <td><?= htmlspecialchars($request['quantity']); ?></td>
                                <td><?= sprintf("%02d:%02d", floor($request['time_diff'] / 60), $request['time_diff'] % 60); ?></td>
                                <form method="post" action="">
                                    <td>
                                        <input type="datetime-local" name="approval_datetime">
                                    </td>
                                    <td>
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                        <input type="hidden" name="table" value="food">
                                        <button type="submit" name="approve_request" class="btn btn-success bttn">Approve</button>
                                        <button type="submit" name="reject_request" class="btn btn-danger bttn">Reject</button>
                                    </td>
                                </form>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No pending requests.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                </table>
                <br><br>
                <h2>User Requests - Waste</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                            <th>Username</th>
                            <th>Location</th>
                            <th>Type of Waste</th>
                            <th>Quantity</th>
                            <th>Time (HH:MM)</th>
                            <th>Collection Date & Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($wasteRequests)): ?>
                            <?php foreach ($wasteRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['username']); ?></td>
                                    <td><?= htmlspecialchars($request['location']); ?></td>
                                    <td><?= htmlspecialchars($request['type_of_waste']); ?></td>
                                    <td><?= htmlspecialchars($request['quantity']); ?></td>
                                    <td><?= sprintf("%02d:%02d", floor($request['time_diff'] / 60), $request['time_diff'] % 60); ?></td>
                                    <form method="post" action="">
                                        <td>
                                            <input type="datetime-local" name="approval_datetime" required>
                                        </td>
                                        <td>
                                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                            <input type="hidden" name="table" value="waste">
                                            <button type="submit" name="approve_request" class="btn btn-success">Approve</button>
                                            <button type="submit" name="reject_request" class="btn btn-danger">Reject</button>
                                        </td>
                                    </form>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No pending requests.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
                <br><br>
                <h2>User Requests - Cloth</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                            <th>Username</th>
                            <th>Location</th>
                            <th>Type of Cloth</th>
                            <th>Quantity</th>
                            <th>Time (HH:MM)</th>
                            <th>Collection Date & Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clothRequests)): ?>
                            <?php foreach ($clothRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['username']); ?></td>
                                    <td><?= htmlspecialchars($request['location']); ?></td>
                                    <td><?= htmlspecialchars($request['type_of_cloth']); ?></td>
                                    <td><?= htmlspecialchars($request['quantity']); ?></td>
                                    <td><?= sprintf("%02d:%02d", floor($request['time_diff'] / 60), $request['time_diff'] % 60); ?></td>
                                    <form method="post" action="">
                                        <td>
                                            <input type="datetime-local" name="approval_datetime" required>
                                        </td>
                                        <td>
                                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                            <input type="hidden" name="table" value="cloth">
                                            <button type="submit" name="approve_request" class="btn btn-success">Approve</button>
                                            <button type="submit" name="reject_request" class="btn btn-danger">Reject</button>
                                        </td>
                                    </form>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No pending requests.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        <?php elseif ($activeForm === 'ngo_requests'): ?>
            <div class="container" style="margin-top: 60px;">
                <h2>NGO Requests - Food</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                            <th>Username</th>
                            <th>Mobile No</th>
                            <th>Food Type</th>
                            <th>Food Category</th>
                            <th>Quantity</th>
                            <th>Time (HH:MM)</th>
                            <th>Collection Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($foodRequests)): ?>
                            <?php foreach ($foodRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['username']); ?></td>
                                    <td><?= htmlspecialchars($request['mobileno']); ?></td>
                                    <td><?= htmlspecialchars($request['food_type']); ?></td>
                                    <td><?= htmlspecialchars($request['food_category']); ?></td>
                                    <td><?= htmlspecialchars($request['quantity']); ?></td>
                                    <td><?= sprintf("%02d:%02d", floor($request['time_diff'] / 60), $request['time_diff'] % 60); ?></td>
                                    <form method="post" action="" style="display: flex; ">
                                        <td>
                                            <input type="datetime-local" name="approval_datetime">
                                        </td>
                                        <td>
                                            <input type="hidden" name="request_id" value="<?= $request['id']; ?>">
                                            <input type="hidden" name="table" value="food_requests">
                                            <button type="submit" name="approve" class="btn-success bttn">Approve</button>
                                            <button type="submit" name="reject" class="btn-danger bttn">Reject</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No pending requests.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>


                </table>
                <br><br>

                <h2>NGO Requests - Cloth</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                            <th>Username</th>
                            <th>MobileNo</th>
                            <th>Type of Cloth</th>
                            <th>Quantity</th>
                            <th>Time (HH:MM)</th>
                            <th>Collection Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clothRequests)): ?>
                            <?php foreach ($clothRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['username']); ?></td>
                                    <td><?= htmlspecialchars($request['mobileno']); ?></td>
                                    <td><?= htmlspecialchars($request['type_of_cloth']); ?></td>
                                    <td><?= htmlspecialchars($request['quantity']); ?></td>
                                    <td><?= sprintf("%02d:%02d", floor($request['time_diff'] / 60), $request['time_diff'] % 60); ?></td>
                                    <form method="post" style="display: inline-block;">
                                        <td>
                                            <input type="datetime-local" name="approval_datetime">
                                        </td>
                                        <td>
                                            <input type="hidden" name="request_id" value="<?= $request['id']; ?>">
                                            <input type="hidden" name="table" value="cloth_requests">
                                            <button type="submit" name="approve" class="btn-success bttn">Approve</button>
                                            <button type="submit" name="reject" class="btn-danger bttn">Reject</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No pending requests.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>


            </div>
        <?php elseif ($activeForm === "todays_collection"): ?>
            <div class="container" style="margin-top: 60px;">
                <h2>Todays Collection- Food</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                            <th>Location</th>
                            <th>Food Type</th>
                            <th>Food Category</th>
                            <th>Quantity(kg)</th>
                            <th>Contact Name</th>
                            <th>Contact MobileNo</th>
                            <th>Collection Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($todaysrequests)): ?>
                            <?php foreach ($todaysrequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['location']); ?></td>
                                    <td><?= htmlspecialchars($request['type_of_food']); ?></td>
                                    <td><?= htmlspecialchars($request['food_category']); ?></td>
                                    <td><?= htmlspecialchars($request['quantity']); ?></td>
                                    <td><?= htmlspecialchars($request['name']); ?></td>
                                    <td><?= htmlspecialchars($request['mobile']); ?></td>
                                    <td><?= htmlspecialchars($request['approval_datetime']); ?></td>
                                    <form method="post" action="">
                                        <td>
                                            <input type="hidden" name="request_id" value="<?= $request['id']; ?>">
                                            <input type="hidden" name="table" value="food">
                                            <button type="submit" name="completed" class="btn-success bttn">completed</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No pending requests.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>


                </table>
                <br><br>

                <h2>Todays Collection - Cloth</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                        <th>Location</th>
                            <th>Food Type</th>
                            <th>Quantity(pc)</th>
                            <th>Contact Name</th>
                            <th>Contact MobileNo</th>
                            <th>Collection Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clothRequests)): ?>
                            <?php foreach ($clothRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['username']); ?></td>
                                    <td><?= htmlspecialchars($request['mobileno']); ?></td>
                                    <td><?= htmlspecialchars($request['type_of_cloth']); ?></td>
                                    <td><?= htmlspecialchars($request['quantity']); ?></td>
                                    <td><?= sprintf("%02d:%02d", floor($request['time_diff'] / 60), $request['time_diff'] % 60); ?></td>
                                    <form method="post" style="display: inline-block;">
                                        <td>
                                            <input type="datetime-local" name="approval_datetime">
                                        </td>
                                        <td>
                                            <input type="hidden" name="request_id" value="<?= $request['id']; ?>">
                                            <input type="hidden" name="table" value="cloth_requests">
                                            <button type="submit" name="approve" class="btn-success bttn">Approve</button>
                                            <button type="submit" name="reject" class="btn-danger bttn">Reject</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No pending requests.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>


            </div>
        <?php endif; ?>
    </div>

</body>

</html>