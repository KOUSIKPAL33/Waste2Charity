<?php
session_start();
require 'db.php'; // Include database connection

// Default active form
$activeForm = isset($_GET['form']) ? $_GET['form'] : 'my_requests';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Flag to check if the request is submitted
$isSubmitted = false;
$isClothSubmitted = false; // Flag to track cloth form submission

// Handle form submissions based on activeForm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Food form submission logic
    if ($activeForm === 'food') {
        $requests = $_POST['requests'] ?? [];
        $errors = [];

        foreach ($requests as $key => $request) {
            $inputQty = isset($request['quantity']) ? (int)$request['quantity'] : 0;
            $totalQty = (int)$request['total_quantity'];

            if ($inputQty > $totalQty) {
                $errors[$key] = "Input quantity cannot exceed total quantity.";
            }
        }

        if (empty($errors)) {
            foreach ($requests as $request) {
                $foodType = $request['food_type'];
                $foodCategory = $request['food_category'];
                $quantity = isset($request['quantity']) ? (int)$request['quantity'] : 0;
                $userId = $_SESSION['user_id'];

                $stmt = $conn->prepare("INSERT INTO food_requests (food_type, food_category, quantity, user_id, requested_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssii", $foodType, $foodCategory, $quantity, $userId);
                $stmt->execute();
            }

            $isSubmitted = true;
        }
    }

    // Cloth form submission logic
    if ($activeForm === 'cloth') {
        $clothRequests = $_POST['cloth_requests'] ?? [];
        $clothErrors = [];

        foreach ($clothRequests as $key => $request) {
            $inputQty = isset($request['quantity']) ? (int)$request['quantity'] : 0;
            $totalQty = (int)$request['total_quantity'];

            if ($inputQty > $totalQty) {
                $clothErrors[$key] = "Input quantity cannot exceed total quantity.";
            }
        }

        if (empty($clothErrors)) {
            foreach ($clothRequests as $request) {
                $clothType = $request['type_of_cloth'];
                $quantity = isset($request['quantity']) ? (int)$request['quantity'] : 0;
                $userId = $_SESSION['user_id'];

                $stmt = $conn->prepare("INSERT INTO cloth_requests (type_of_cloth, quantity, user_id, requested_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("sii", $clothType, $quantity, $userId);
                $stmt->execute();
            }

            $isClothSubmitted = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Welocome ngo</title>
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
            margin-top:0px;
        }
        .my-content{
            margin: 100px 100px;
            border-width: 15px;
            border-style: double;
            Border-color: green;
            box-shadow: 0px 0px 10px black;

        }

        h2 {
            margin-top: 0;
            font-size: 50px;
            color: #333;
            font-weight: 700;
            text-align: center;

        }

        form {
            background: inherit;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 850px;
            margin: auto;
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
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        input {
            width: 200px;
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

        th,
        td {
            text-align: center;

        }

        th {
            font-size: 19px;
        }

        td {
            font-size: 16px;
        }

        p {
            font-size: 25px;
            text-align: center;
        }
    </style>

</head>

<body>

    <!-- Navbar with link to "Collect Cloth" -->
    <div class="navbar">
        <div>
            <a href="index.php">Home</a>
            <a href="?form=food">Collect Food</a>
            <a href="?form=cloth">Collect Cloth</a>
            <a href="?form=my_requests">My Requests</a>

        </div>
        <div class="right">
            <span style="font-size: 20px;"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
            <a href="?logout=1" style="margin-right: 50px;"><button class="logout-btn">Logout</button></a>
        </div>
    </div>

    <div class="my-content">
        <div class="container">
            <!-- Display Food Form if activeForm is 'food' -->
            <?php if ($activeForm === 'food'): ?>
                <?php if ($isSubmitted): ?>
                    <h2>Success</h2>
                    <p>Your food requests have been submitted successfully!</p>
                <?php else: ?>
                    <h2>Food Available</h2>
                    <form method="post">
                        <table border="1" class="table mx-auto" style="width: 800px; border-collapse: collapse;">
                            <thead class="table-primary">
                                <tr>
                                    <th>Food Type</th>
                                    <th>Food Category</th>
                                    <th>Total Quantity(Kg)</th>
                                    <th>Your Request</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT type_of_food AS food_type, food_category, SUM(quantity) AS total_quantity FROM food WHERE status = 'pending' GROUP BY type_of_food, food_category";
                                $result = $conn->query($query);

                                while ($row = $result->fetch_assoc()):
                                    $key = $row['food_type'] . '_' . $row['food_category'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['food_type']); ?></td>
                                        <td><?= htmlspecialchars($row['food_category']); ?></td>
                                        <td><?= htmlspecialchars($row['total_quantity']); ?></td>
                                        <td>
                                            <input type="number" name="requests[<?= $key; ?>][quantity]" min="0" max="<?= $row['total_quantity']; ?>" placeholder="Enter quantity(kg)">
                                            <input type="hidden" name="requests[<?= $key; ?>][food_type]" value="<?= htmlspecialchars($row['food_type']); ?>">
                                            <input type="hidden" name="requests[<?= $key; ?>][food_category]" value="<?= htmlspecialchars($row['food_category']); ?>">
                                            <input type="hidden" name="requests[<?= $key; ?>][total_quantity]" value="<?= $row['total_quantity']; ?>">
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div style="text-align: center;"><button type="submit" class="btn btn-primary" style="background-color: #37517e">Submit Requests</button></div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Display Cloth Form if activeForm is 'cloth' -->
            <?php if ($activeForm === 'cloth'): ?>
                <?php if ($isClothSubmitted): ?>
                    <h2>Success</h2>
                    <p>Your cloth requests have been submitted successfully!</p>
                <?php else: ?>
                    <h2>Cloth Available</h2>
                    <form method="post">
                        <table border="1" class="table mx-auto" style="width: 800px; border-collapse: collapse;">
                            <thead class="table-primary">
                                <tr>
                                    <th>Type of Cloth</th>
                                    <th>Total Quantity(Piece)</th>
                                    <th>Your Request</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT type_of_cloth, SUM(quantity) AS total_quantity FROM cloth WHERE status = 'pending' GROUP BY type_of_cloth";
                                $result = $conn->query($query);

                                while ($row = $result->fetch_assoc()):
                                    $key = $row['type_of_cloth'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['type_of_cloth']); ?></td>
                                        <td><?= htmlspecialchars($row['total_quantity']); ?></td>
                                        <td>
                                            <input type="number" name="cloth_requests[<?= $key; ?>][quantity]" min="1" max="<?= $row['total_quantity']; ?>" placeholder="Enter quantity(piece)">
                                            <input type="hidden" name="cloth_requests[<?= $key; ?>][type_of_cloth]" value="<?= htmlspecialchars($row['type_of_cloth']); ?>">
                                            <input type="hidden" name="cloth_requests[<?= $key; ?>][total_quantity]" value="<?= $row['total_quantity']; ?>">
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div style="text-align: center;"><button type="submit" class="btn btn-primary" style="background-color: #37517e;">Submit Requests</button></div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
        if ($activeForm === 'my_requests'):
            $userId = $_SESSION['user_id']; // Get logged-in user's ID
            $query = "SELECT * FROM food_requests WHERE user_id = ? ORDER BY requested_at ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0): ?>
                <h2>Food Requests</h2>
                <table border="1" class="table mx-auto" style="width: 80%; border-collapse: collapse; margin-top: 20px;">
                    <thead class="table-primary">
                        <tr>

                            <th>Food Type</th>
                            <th>Food Category</th>
                            <th>Quantity (Kg)</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Approval Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['food_type']); ?></td>
                                <td><?= htmlspecialchars($row['food_category']); ?></td>
                                <td><?= htmlspecialchars($row['quantity']); ?></td>
                                <td><?= htmlspecialchars($row['requested_at']); ?></td>
                                <td><?= htmlspecialchars($row['status']); ?></td>
                                <td><?= htmlspecialchars($row['approval_datetime']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No food requests found.</p>
            <?php endif; ?>

            <?php
            $query = "SELECT * FROM cloth_requests WHERE user_id = ? ORDER BY requested_at ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0): ?>
                <h2 style="margin-top:80px">Cloth Requests</h2>
                <table border="1" class="table mx-auto" style="width: 80%; border-collapse: collapse; margin-top: 20px;">
                    <thead class="table-primary">
                        <tr>

                            <th>Cloth Type</th>
                            <th>Quantity (Kg)</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Approval Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['type_of_cloth']); ?></td>
                                <td><?= htmlspecialchars($row['quantity']); ?></td>
                                <td><?= htmlspecialchars($row['requested_at']); ?></td>
                                <td><?= htmlspecialchars($row['status']); ?></td>
                                <td><?= htmlspecialchars($row['approval_datetime']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No cloth requests found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</body>

</html>