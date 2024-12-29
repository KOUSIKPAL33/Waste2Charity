<?php
session_start();
require 'db.php'; // Include database connection

$error = '';
$success = '';
$loginHeader = "User Login"; // Default login header text

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $userType = $_POST['user_type'] ?? 'user';
    $redirect = $_POST['redirect'] ?? '';

    $table = $userType === 'ngo' ? 'ngo' : ($userType === 'admin' ? 'admin' : 'users');

    // Query to check credentials from the appropriate table
    $sql = "SELECT * FROM $table WHERE userid = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Correct credentials
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['name'];
        $_SESSION['user_id'] = $user['id']; // Store user ID
        $_SESSION['user_type'] = $userType;

        // Redirect if a specific action was requested
        if ($redirect === 'donate') {
            header("Location: userform.php");
            exit;
        }
    } else {
        // Incorrect credentials
        $error = "Incorrect username or password!";
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $registerAs = $_POST['register_as'] ?? 'user';

    // Validate email uniqueness
    $table = $registerAs === 'ngo' ? 'ngo' : 'users';
    $emailCheckQuery = "SELECT * FROM $table WHERE userid = ?";
    $stmt = $conn->prepare($emailCheckQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $emailCheckResult = $stmt->get_result();

    if ($emailCheckResult->num_rows > 0) {
        $error = "Email ID already registered!";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        // Insert new user into the appropriate table
        $insertQuery = "INSERT INTO $table (name, userid, password, mobileno) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssss", $name, $email, $password, $mobile);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now log in.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
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
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Wms - Home</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/clients/Capture.PNG" rel="icon">
    <link href="assets/img/clients/Capture.PNG" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Jost:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
    <!-- Vendor CSS Files -->
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- cdn for awesome fonts icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css"
        crossorigin="anonymous" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .navbar-nb {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background-color: #37517e;
            color: white;
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            align-items: center;
            border-bottom: 1px solid white;
        }

        .navbar-nb a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 20px;
        }

        .navbar-nb .right {
            display: flex;
            align-items: center;
        }

        button {
            margin: 0 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            background-color: #47b2e4;
        }

        button.logout-btn {
            background-color: #dc3545;
            font-size: 15px;
        }

        .log-in-button,
        .sign-up-button {
            font-size: 15px;
            font-weight: 500;
            margin-right: 15px;
            border-radius: 44px;
            padding: 8px 22px;
        }

        .sign-up-button {
            min-width: 100px;
        }

        #login-form,
        #register-form {
            position: absolute;
            top: 82px;
            /* Adjust this to align below the navbar */
            right: 20px;
            /* Aligns to the right, below the login button */
            z-index: 10;
            display: none;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }

        /* Ensure active form displays correctly */
        #login-form.active,
        #register-form.active {
            display: block;
        }

        .form {
            display: none;
            position: relative;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }

        .form.active {
            display: block;
        }

        .form .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 18px;
        }

        input,
        button,select {
            margin: 10px 0;
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }
        h3{
            color: black;
        }
        .close-btn{
            color: black;
        }
        .submit_button{
            background-color:  #37517e;
        }

    </style>
    <script>
        function toggleForm(formId) {
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('register-form').classList.remove('active');
            if (formId) {
                document.getElementById(formId).classList.add('active');
            }
        }

        function changeLoginHeader(header) {
            document.getElementById('login-header').innerText = header;
        }

        function handleDonateClick() {
            <?php if (isset($_SESSION['username'])): ?>
                window.location.href = "userform.php";
            <?php else: ?>
                document.getElementById('redirect').value = 'donate';
                toggleForm('login-form');
            <?php endif; ?>
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('redirect') === 'donate') {
            window.location.href = "userform.php";
        }

        function toggleLoginButtons(excludeButtonId) {
            const buttons = ['ngo-login-btn', 'admin-login-btn', 'user-login-btn'];
            buttons.forEach(buttonId => {
                const button = document.getElementById(buttonId);
                if (buttonId === excludeButtonId) {
                    button.style.display = 'none';
                } else {
                    button.style.display = 'inline-block';
                }
            });
        }
    </script>
</head>

<body>
    <div class="navbar-nb">
        <div>
            <h1>WASTE2CHARITY</h1>
        </div>
        <div>
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'ngo'): ?>
                <a href="#">Home</a>
                <a href="#about">About Us</a>
                <a href="#footer">Contact Us</a>
                <a href="ngopage.php">Collect Food/Cloth</a>

            <?php elseif(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'):?>
                <a href="#">Home</a>
                <a href="#about">About Us</a>
                <a href="#footer">Contact Us</a>
                <a href="adminpage.php">show requests</a>
            <?php else: ?>
                <a href="#">Home</a>
                <a href="#about">About Us</a>
                <a href="#footer">Contact</a>
                <a href="#" id="donate-link" onclick="handleDonateClick()">Donate/Complaint</a>
            <?php endif; ?>
        </div>
        <div class="right">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome <?= htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1"><button class="logout-btn">Logout</button></a>
            <?php else: ?>
                <button onclick="toggleForm('login-form')" class="log-in-button">Login</button>
                <button onclick="toggleForm('register-form')" class="sign-up-button">Sign Up</button>
            <?php endif; ?>
        </div>
        <div id="login-form" class="form <?= $error ? 'active' : '' ?>">
            <span class="close-btn" onclick="toggleForm('null')">✖</span>
            <h3 id="login-header"><?= htmlspecialchars($loginHeader); ?></h3>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="hidden" id="user-type" name="user_type" value="user">
                <input type="hidden" id="redirect" name="redirect" value="">
                <button type="submit" class="submit_button" name="login">Submit</button>
                <button type="button" class="submit_button" id="ngo-login-btn" onclick="changeLoginHeader('NGO Login'); document.getElementById('user-type').value = 'ngo';  toggleLoginButtons('ngo-login-btn');">Login as NGO</button>
                <button type="button" class="submit_button" id="admin-login-btn" onclick="changeLoginHeader('Admin Login'); document.getElementById('user-type').value = 'admin';toggleLoginButtons('admin-login-btn');">Login as Admin</button>
                <button type="button" class="submit_button" id="user-login-btn" onclick="changeLoginHeader('User Login'); document.getElementById('user-type').value = 'user'; toggleLoginButtons('user-login-btn');">Login as User</button>
            </form>
        </div>
        <div id="register-form" class="form <?= $success ? 'active' : '' ?>">
            <span class="close-btn" onclick="toggleForm('null')">✖</span>
            <h2>Register</h2>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error); ?></div>
            <?php elseif ($success): ?>
                <div class="success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <input type="tel" name="mobile" placeholder="Mobile Number" required>
                <select name="register_as" required>
                    <option value="">---Regester As---</option>
                    <option value="user">User</option>
                    <option value="ngo">NGO</option>
                </select>
                <button type="submit" class="submit_button" name="register">Register</button>
            </form>
        </div>
    </div>


    <section id="hero" class="d-flex align-items-center" style="background: #37517e;">

        <div class="container">
            <div class="row">
                <div class="col-lg-6 d-flex flex-column justify-content-center pt-4 pt-lg-0 order-2 order-lg-1"
                    data-aos="fade-up" data-aos-delay="200">
                    <h1>Better Solutions For The Waste Around You!</h1>
                    <h2>Keep our Environment Healthy</h2>

                    <div class="d-flex justify-content-center justify-content-lg-start">
                        <a href="<?= (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'ngo') ? 'ngopage.php' : '#' ?>" 
                            id="donate-link" 
                            class="btn-get-started scrollto" 
                            <?= (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'ngo') ? 'onclick="handleDonateClick()"' : '' ?>
                            >Get Started
                        </a>

                        <a href="https://www.youtube.com/watch?v=4JDGFNoY-rQ" class="glightbox btn-watch-video"><i
                                class="bi bi-play-circle"></i><span>Watch Video</span></a>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-in" data-aos-delay="200">
                    <img src="assets/img/recycling.jpeg" class="img-fluid animated" alt="">
                </div>
            </div>
        </div>

    </section><!-- End Hero -->

    <main id="main">


        <!-- ======= About Us Section ======= -->
        <section id="about" class="about">
            <div class="container" data-aos="fade-up">

                <div class="section-title">
                    <h2>About Us</h2>
                </div>

                <div class="row content">
                    <div class="col-lg-6">
                        <p>

                            The ‘WASTE MANAGEMENT SYSTEM’
                            is a web application aimed to provide a digital way
                            of complaining the concerns of general citizens to their relative municipalities.


                        </p>
                        <ul>
                            <li><i class="ri-check-double-line"></i> Complaining about waste or garbage problems near their locality.
                            </li>
                            <li><i class="ri-check-double-line"></i> See thier complain Report and check if the work is done! or not.
                            </li>
                            <li><i class="ri-check-double-line"></i> people can take different ideas regarding recycling of waste
                                through this website. </li>
                        </ul>
                    </div>
                    <div class="col-lg-6 pt-4 pt-lg-0">
                        <p>
                            Complaining about the waste problem encountered everyday to municipality is hefty process and waste
                            management
                            aims to make this process easier. With a simple handheld device with access to internet, user can use this
                            platform complain
                            their concerns to municipality . The automated system will redirect the complains .The municipality admins
                            at the receiving side can acknowledge
                            the reports which lets the users whether their complain is adddressed or not.
                        </p>
                        <a href="#" class="btn-learn-more">Learn More</a>
                    </div>
                </div>

            </div>
        </section><!-- End About Us Section -->

        <!-- ======= Why Us Section ======= -->
        <section id="why-us" class="why-us section-bg">
            <div class="container-fluid" data-aos="fade-up">

                <div class="row">

                    <div class="col-lg-7 d-flex flex-column justify-content-center align-items-stretch  order-2 order-lg-1">

                        <div class="content">
                            <h3>Guidelines implementation <strong>must for proper management of medical waste</strong></h3>
                            <p>
                                A hospital is the last resort of hope for sick people who expect to get better and heal.
                                However, shortcoming on the part of the hospital staff and management could instead have an adverse
                                effect on public health.
                                According to the annual health report published by the Department of Health Services in 2000/2001, there
                                are 74 hospitals,
                                172 Primary Health Care Units, 710 Health Posts and 3132 Sub-health Post run by the government across
                                Nepal.
                                The number of healthcare institutions has certainly surged in the recent years.
                                All healthcare facilities are required to follow the ‘National Health Care Waste Management Guidelines’
                                prepared by the National Health Research Council (NHRC).</p>

                        </div>

                        <div class="accordion-list">
                            <ul>
                                <li>
                                    <a data-bs-toggle="collapse" class="collapse" data-bs-target="#accordion-list-1"><span>01</span>
                                        “Wastes from health care institutions can be categorized as infectious or noninfectious." <i
                                            class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i></a>
                                    <div id="accordion-list-1" class="collapse show" data-bs-parent=".accordion-list">
                                        <p>
                                            Infectious wastes include human, animal, or biological wastes and any items that may be
                                            contaminated with pathogens.
                                            Noninfectious wastes include toxic chemicals, cytotoxic drugs, and radioactive, flammable, and
                                            explosive wastes,
                                            reads the guideline.
                                        </p>

                                    </div>
                                </li>

                                <li>
                                    <a data-bs-toggle="collapse" data-bs-target="#accordion-list-2" class="collapsed"><span>02</span>
                                        Implementation problem <i class="bx bx-chevron-down icon-show"></i><i
                                            class="bx bx-chevron-up icon-close"></i></a>
                                    <div id="accordion-list-2" class="collapse" data-bs-parent=".accordion-list">
                                        <p>
                                            However, not all hospitals are following the guidelines mainly because of the lack of budget,
                                            lack of orientation regarding medical waste management to waste handlers, and flimsy monitoring
                                            from the government.
                                            Segregation of medical waste is a vital part of hospital waste management. Poor management of
                                            hospital waste poses
                                            risk not only to its handlers during its treatment and disposal but also to the environment
                                            if not disposed in a proper manner. Haphazard management of hospital waste also gives black
                                            marketers an opportunity
                                            to collect the disposed medical equipment and resell them. </p>
                                    </div>
                                </li>

                                <li>
                                    <a data-bs-toggle="collapse" data-bs-target="#accordion-list-3" class="collapsed"><span>03</span> Dr
                                        Kedar Century, Executive Director at Bir Hospital said, <i
                                            class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i></a>
                                    <div id="accordion-list-3" class="collapse" data-bs-parent=".accordion-list">
                                        <p>
                                            “We used to have an ideal waste management system to sterilize and dispose hospital waste before
                                            the 2015 earthquake. But the earthquake damaged the building where we had installed the autoclave
                                            device and now we don’t have any space. The new building is currently being used to accommodate
                                            admitted patients.”

                                            Dr Century added that lack of space has barred the hospital from following NHRC’s National Health
                                            Care Waste Management Guidelines and they are relying on traditional means of sterilization for
                                            the time being.

                                            Management of medical waste at private hospitals is poorer as compared to government run hospitals
                                            for want of regular monitoring from authorities concerned. A report published by the Ministry of
                                            Health and Population shows that private hospitals continue to burn, bury and dispose hazardous
                                            immunization waste mixing it up with the municipal waste. </p>
                                    </div>
                                </li>

                            </ul>
                        </div>

                    </div>

                    <div class="col-lg-5 align-items-stretch order-1 order-lg-2 img"
                        style='background-image: url("assets/img/Medical-Waste.jpg");' data-aos="zoom-in" data-aos-delay="150">
                        &nbsp;</div>
                </div>

            </div>
        </section><!-- End Why Us Section -->

        <!-- ======= Skills Section ======= -->
        <section id="skills" class="skills">
            <div class="container" data-aos="fade-up">

                <div class="row">
                    <div class="col-lg-6 d-flex align-items-center" data-aos="fade-right" data-aos-delay="100">
                        <img src="assets/img/recycling5.png" class="img-fluid" alt="">
                    </div>
                    <div class="col-lg-6 pt-4 pt-lg-0 content" data-aos="fade-left" data-aos-delay="100">
                        <h3>Composition and Percentage of Waste Generation</h3>
                        <p class="fst-italic">
                            Management of Municipal Solid Wastes: A Case Study in Limpopo Province, South Africa

                        </p>

                        <div class="skills-content">

                            <div class="progress">
                                <span class="skill">Plastics <i class="val">35%</i></span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="progress">
                                <span class="skill">Paper and Glass <i class="val">25%</i></span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="progress">
                                <span class="skill">Food Waste <i class="val">25%</i></span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="progress">
                                <span class="skill">Garden Waste<i class="val">15%</i></span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </section><!-- End Skills Section -->

        <!-- ======= Frequently Asked Questions Section ======= -->
        <section id="faq" class="faq section-bg">
            <div class="container" data-aos="fade-up">

                <div class="section-title">
                    <h2>Frequently Asked Questions</h2>
                    <p>Waste management regulations and disposal methods.</p>
                </div>

                <div class="faq-list">
                    <ul>
                        <li data-aos="fade-up" data-aos-delay="100">
                            <i class="bx bx-help-circle icon-help"></i> <a data-bs-toggle="collapse" class="collapse"
                                data-bs-target="#faq-list-1"> What is waste management? <i class="bx bx-chevron-down icon-show"></i><i
                                    class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-1" class="collapse show" data-bs-parent=".faq-list">
                                <p>
                                    Waste management is the collection, transportation and disposal of waste materials. </p>
                            </div>
                        </li>

                        <li data-aos="fade-up" data-aos-delay="200">
                            <i class="bx bx-help-circle icon-help"></i> <a data-bs-toggle="collapse" data-bs-target="#faq-list-2"
                                class="collapsed"> How do I practice waste management at home? <i
                                    class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-2" class="collapse" data-bs-parent=".faq-list">
                                <p>
                                    Keep separate containers for dry and wet waste in the kitchen.
                                    Keep two bags for dry waste collection- paper and plastic, for the rest of the household waste.
                                    Keep plastic from the kitchen clean and dry and drop into the dry waste bin. Keep glass /plastic
                                    containers rinsed of food matter.
                                    Keep a paper bag for throwing sanitary waste. </p>
                            </div>
                        </li>

                        <li data-aos="fade-up" data-aos-delay="300">
                            <i class="bx bx-help-circle icon-help"></i> <a data-bs-toggle="collapse" data-bs-target="#faq-list-3"
                                class="collapsed"> What are the first few steps to initiate a waste management programme in your
                                apartment complex?
                                <i class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-3" class="collapse" data-bs-parent=".faq-list">
                                <p>
                                    Form a group with like-minded people.
                                    Explain waste segregation to your family / neighbours in your apartment building.
                                    Get the staff in the apartment building to also understand its importance.
                                    Get separate storage drums for storing dry and wet waste.
                                    Have the dry waste picked up by the dry waste collection centre or your local scrap dealer. </p>
                            </div>
                        </li>

                        <li data-aos="fade-up" data-aos-delay="400">
                            <i class="bx bx-help-circle icon-help"></i> <a data-bs-toggle="collapse" data-bs-target="#faq-list-4"
                                class="collapsed">What are the different types of waste? <i class="bx bx-chevron-down icon-show"></i><i
                                    class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-4" class="collapse" data-bs-parent=".faq-list">
                                <p>
                                    1. Wet waste- Wet waste consists of kitchen waste - including vegetable and fruit peels and pieces,
                                    tea leaves, coffee grounds, eggshells, bones and entrails, fish scales, as well as cooked food (both
                                    veg and non-veg).
                                    <br><br>
                                    2. Dry Waste- Paper, plastics, metal, glass, rubber, thermocol, styrofoam, fabric, leather, rexine,
                                    wood – anything that can be kept for an extended period without decomposing is classified as dry
                                    waste.
                                    <br><br>
                                    3 .Hazardous waste- Household hazardous waste or HHW include three sub-categories – E-waste; toxic
                                    substances such as paints, cleaning agents, solvents, insecticides and their containers, other
                                    chemicals; and biomedical waste.
                                    <br><br>
                                    4. E-waste- E-waste or electronic waste consists of batteries, computer parts, wires, electrical
                                    equipment of any kind, electrical and electronic toys, remotes, watches, cell phones, bulbs, tube
                                    lights and CFLs.
                                    <br><br>
                                    5. Biomedical waste- This includes used menstrual cloth, sanitary napkins, disposable diapers,
                                    bandages and any material that is contaminated with blood or other body fluids.
                                </p>
                            </div>
                        </li>

                        <li data-aos="fade-up" data-aos-delay="500">
                            <i class="bx bx-help-circle icon-help"></i> <a data-bs-toggle="collapse" data-bs-target="#faq-list-5"
                                class="collapsed">What are ways of storing the waste at homes? <i
                                    class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-5" class="collapse" data-bs-parent=".faq-list">
                                <p>
                                    1. Dry waste-
                                    Store it in a bag in the utility area after cleaning and drying till it is picked up. No food residue
                                    must be left in the bottles and packets. Clean them as you would to reuse them. If clothes are totally
                                    unusable, or very damaged, they are categorized as dry waste. If clothes are soiled with body fluids,
                                    they become sanitary waste. If they are soiled with paint, or any chemicals, they are HHW (household
                                    hazardous waste).

                                    <br><br> 2. E-waste-
                                    Store them in separate container which is kept closed, away from moisture and in which nothing else is
                                    put.
                                </p>
                            </div>
                        </li>
                        <li data-aos="fade-up" data-aos-delay="600">
                            <i class="bx bx-help-circle icon-help"></i> <a data-bs-toggle="collapse" data-bs-target="#faq-list-6"
                                class="collapsed">How do I dispose my waste? <i class="bx bx-chevron-down icon-show"></i><i
                                    class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-5" class="collapse" data-bs-parent=".faq-list">
                                <p>
                                    1. Compost your wet waste at home-

                                    Home composting can easily be done in any aerated container. Get more details on composting and begin
                                    composting today!
                                    <br><br>
                                    2 Compost your wet waste at the community level-

                                    If you live in a large apartment building, a community composting system like tank composting could be
                                    set up for all the wet waste from the residents. If not, the wet waste can be given out every day to
                                    your Municipality collection system.
                                    <br><br>
                                    3 Biomedical waste has to be wrapped separately in a newspaper and handed over to the municipality
                                    waste collection system. Expired medicines and injections, used syringes also have to be disposed in
                                    the same manner.

                                    Paint and other hazardous waste like cosmetics, mosquito repellents, tube lights etc have to be stored
                                    separately and handed over to the Municipal collection system.
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </section><!-- End Frequently Asked Questions Section -->
    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer">


        <div class="footer-top">
            <div class="container">
                <div class="row">

                    <div class="col-lg-3 col-md-6 footer-contact">
                        <h3><img src="assets/img/clients/Capture.PNG" style="width:80px;height:60px;"></h3>
                        <p>
                            NIT Waragal <br> Hanamkonda,Telangana. <br>PIN:-506004. <br>

                            <strong>Phone:</strong> +91 7602783633<br>
                            <strong>Email:</strong> <a
                                href="mailto:kousikpal652@gmail.com">kousikpal652@gmail.com<br></a>
                        </p>
                    </div>

                    <div class="col-lg-3 col-md-6 footer-links">
                        <h4>Useful Links</h4>
                        <ul>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">Home</a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#about">About us</a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#faq">FAQ</a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">Terms of service</a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">Privacy policy</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 footer-links">
                        <h4>Our Services</h4>
                        <ul>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">Waste Pick up </a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">Food Pick up</a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">Cloth Pick up</a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">E- managementwaste</a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">Garbage Management</a></li>
                            <li><i class="bx bx-chevron-right"></i> <a href="#">Awareness program</a></li>
                            
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 footer-links">
                        <h4>Our Social Networks</h4>
                        <p>Follow us in our social media to stay updated about community waste management.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="twitter"><i class="bx bxl-twitter"></i></a>
                            <a href="#" class="facebook"><i class="bx bxl-facebook"></i></a>
                            <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
                            <a href="#" class="google-plus"><i class="bx bxl-skype"></i></a>
                            <a href="#" class="linkedin"><i class="bx bxl-linkedin"></i></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="container footer-bottom clearfix">
            <div class="copyright">
                &copy; Copyright <strong><span>WMS</span></strong>. All Rights Reserved
            </div>
            <div class="credits">
                Designed by <a href="">Kousik</a> and <a href="">Kuldeep </a> and  <a href="">Rahul </a>
            </div>
        </div>
    </footer><!-- End Footer -->

    <div id="preloader"></div>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>