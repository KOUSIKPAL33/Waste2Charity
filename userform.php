<?php
session_start();
require 'db.php'; // Include database connection

// Default active form
$activeForm = 'request';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = $_POST['location'] ?? '';
    $name = $_POST['name'] ?? '';
    $mobile = $_POST['mobile'] ?? '';

    if (isset($_POST['submit_waste'])) {
        $type_of_waste = implode(',', $_POST['type_of_waste'] ?? []);
        $quantity = $_POST['quantity'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0; // Fetch user ID from session

        $stmt = $conn->prepare("INSERT INTO waste (location, type_of_waste, quantity, name, mobile, user_id, submitted_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssissi", $location, $type_of_waste, $quantity, $name, $mobile, $user_id); // Corrected: 6 placeholders
        $stmt->execute();
        $stmt->close();
        $success = "Waste complaint submitted successfully!";
    } elseif (isset($_POST['submit_food'])) {
        $type_of_food = $_POST['type_of_food'] ?? '';
        $food_category = implode(',', $_POST['food_category'] ?? []);
        $quantity = $_POST['quantity'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0; // Fetch user ID from session

        $stmt = $conn->prepare("INSERT INTO food (location, type_of_food, food_category, quantity, name, mobile, submitted_at, user_id) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("sssissi", $location, $type_of_food, $food_category, $quantity, $name, $mobile, $user_id); // Corrected: 7 placeholders
        $stmt->execute();
        $stmt->close();
        $success = "Food donation submitted successfully!";
    } elseif (isset($_POST['submit_cloth'])) {
        $type_of_cloth = implode(',', $_POST['type_of_cloth'] ?? []);
        $quantity = $_POST['quantity'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0; // Fetch user ID from session

        $stmt = $conn->prepare("INSERT INTO cloth (location, type_of_cloth, quantity, name, mobile, user_id, submitted_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssissi", $location, $type_of_cloth, $quantity, $name, $mobile, $user_id); // Corrected: 6 placeholders
        $stmt->execute();
        $stmt->close();
        $success = "Cloth donation submitted successfully!";
    }
}

// Handle navigation to switch forms
if (isset($_GET['form'])) {
    $activeForm = $_GET['form'];
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Donation and Complaint</title>
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
        .my-content{
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

        }

        form {
            background: #f4f4f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }

        button {
            margin: 0 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            background-color: #37517e;
            font-size: 20px;
        }

        input,
        select,
        button {
            margin: 10px 0;
            padding: 10px;
            width: 100%;
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
        td{
            text-align: center;
            font-size: 18px;
        }
        th{
            text-align: center;
            font-size: 19px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <div>
            <a href="index.php">Home</a>
            <a href="?form=food">Donate Food </a>
            <a href="?form=cloth">Donate Cloth </a>
            <a href="?form=waste">Waste Complaint</a>
            <a href="?form=request">My requests</a>
        </div>
        <div class="right">
            <span style="font-size: 20px;"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
            <a href="?logout=1" style="margin-right: 50px;"><button class="logout-btn">Logout</button></a>
        </div>
    </div>
    <!-- Main Container -->
    <div class="my-content">
        <div class="container">
            <?php if (isset($success)): ?>
                <div class="success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($activeForm === 'waste'): ?>
                <!-- Waste Complaint Form -->
                <h2>Waste Complaint Form</h2>
                <form method="post">
                    <input type="text" name="location" placeholder="Location" required>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="type_of_waste[]" value="organic"> Organic</label>
                        <label><input type="checkbox" name="type_of_waste[]" value="inorganic"> Inorganic</label>
                        <label><input type="checkbox" name="type_of_waste[]" value="household"> Household</label>
                    </div>
                    <input type="number" name="quantity" placeholder="Quantity (kg)" required>
                    <h3>Contact Details</h3>
                    <input type="text" name="name" placeholder="Name" required>
                    <input type="text" name="mobile" placeholder="Mobile Number" required>
                    <button type="submit" name="submit_waste">Submit</button>
                </form>
            <?php elseif ($activeForm === 'food'): ?>
                <!-- Food Donation Form -->
                <h2>Food Donation Form</h2>
                <form method="post">
                    <input type="text" name="location" placeholder="Location" required>
                    <select name="type_of_food" required>
                        <option value="">Select Food Type</option>
                        <option value="veg">Veg</option>
                        <option value="non-veg">Non-Veg</option>
                    </select>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="food_category[]" value="raw food"> Raw Food</label>
                        <label><input type="checkbox" name="food_category[]" value="cooked food"> Cooked Food</label>
                        <label><input type="checkbox" name="food_category[]" value="packed food"> Packed Food</label>
                    </div>
                    <input type="number" name="quantity" placeholder="Quantity (kg)" required>
                    <h3>Contact Details</h3>
                    <input type="text" name="name" placeholder="Name" required>
                    <input type="text" name="mobile" placeholder="Mobile Number" required>
                    <button type="submit" name="submit_food">Submit</button>
                </form>
            <?php elseif ($activeForm === 'cloth'): ?>
                <!-- Cloth Donation Form -->
                <h2>Cloth Donation Form</h2>
                <form method="post">
                    <input type="text" name="location" placeholder="Location" required>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="type_of_cloth[]" value="shirt"> Shirt</label>
                        <label><input type="checkbox" name="type_of_cloth[]" value="pant"> Pant</label>
                    </div>
                    <input type="number" name="quantity" placeholder="Quantity (pieces)" required>
                    <h3>Contact Details</h3>
                    <input type="text" name="name" placeholder="Name" required>
                    <input type="text" name="mobile" placeholder="Mobile Number" required>
                    <button type="submit" name="submit_cloth">Submit</button>
                </form>
            <?php endif; ?>
        </div>
    
        <?php if($activeForm ==='request'):?>
            <div class="container">
                <h2>Your Food Donations</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                            <th>Location</th>
                            <th>Type of Food</th>
                            <th>Food Category</th>
                            <th>Quantity(kg)</th>
                            <th>Submitted At</th>
                            <th>Approval Status</th>
                            <th>Collection Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ensure the user is logged in
                        if (isset($_SESSION['username'])) {
                            $username = $_SESSION['username'];
                            $user_id = $_SESSION['user_id'];
                            // Fetch data from the food table for the logged-in user
                            $stmt = $conn->prepare("SELECT location, type_of_food, food_category, quantity, submitted_at, status,approval_datetime FROM food WHERE user_id = ? ORDER BY submitted_at DESC");
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();

                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                // Loop through and display each record
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['type_of_food']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['food_category']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['approval_datetime']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center;'>No records found.</td></tr>";
                            }

                            $stmt->close();
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center;'>Please log in to view your submissions.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="container">
                <h2>Your Cloth Donations</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                            <th>Location</th>
                            <th>Type of Cloth</th>
                            <th>Quantity(pc)</th>
                            <th>Submitted At</th>
                            <th>Approval Status</th>
                            <th>Collection Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ensure the user is logged in
                        if (isset($_SESSION['username'])) {
                            $username = $_SESSION['username'];
                            $user_id = $_SESSION['user_id'];
                            // Fetch data from the food table for the logged-in user
                            $stmt = $conn->prepare("SELECT location, type_of_cloth, quantity, submitted_at, status, approval_datetime FROM cloth WHERE user_id = ? ORDER BY submitted_at DESC");
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();

                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                // Loop through and display each record
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['type_of_cloth']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['approval_datetime']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center;'>No records found.</td></tr>";
                            }

                            $stmt->close();
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center;'>Please log in to view your submissions.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="container" >
                <h2>Your Waste Complaints</h2>
                <table border="1" class="table mx-auto" style="width: 90%; border-collapse: collapse;">
                    <thead class="table-primary">
                        <tr>
                            <th>Location</th>
                            <th>Type of Cloth</th>
                            <th>Quantity(kg)</th>
                            <th>Submitted At</th>
                            <th>Approval Status</th>
                            <th>Collection Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ensure the user is logged in
                        if (isset($_SESSION['username'])) {
                            $username = $_SESSION['username'];
                            $user_id = $_SESSION['user_id'];
                            // Fetch data from the food table for the logged-in user
                            $stmt = $conn->prepare("SELECT location, type_of_waste, quantity, submitted_at, status, approval_datetime FROM waste WHERE user_id = ? ORDER BY submitted_at DESC");
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();

                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                // Loop through and display each record
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['type_of_waste']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['approval_datetime']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center;'>No records found.</td></tr>";
                            }

                            $stmt->close();
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center;'>Please log in to view your submissions.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>

</html>
