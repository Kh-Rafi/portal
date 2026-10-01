<?php
date_default_timezone_set('Asia/Dhaka');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

$conn = mysqli_connect("localhost", "root", "") or die("DB Connection Error: " . mysqli_connect_error());

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS university_portal");
mysqli_select_db($conn, "university_portal") or die("Database selection error: " . mysqli_error($conn));

// ================= CREATE MEAL TRACKING TABLE =================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS meal_tracking(
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    meal_date DATE,
    status ENUM('taken', 'not_taken') DEFAULT 'not_taken',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_meal (student_id, meal_date)
)");

$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'hostel_students'");
if (mysqli_num_rows($table_check) == 0) {
    $create_table = "CREATE TABLE hostel_students(
        id INT AUTO_INCREMENT PRIMARY KEY,
        university_id VARCHAR(50) UNIQUE,
        name VARCHAR(100),
        department VARCHAR(100),
        father_name VARCHAR(100),
        mother_name VARCHAR(100),
        dob DATE,
        blood_group VARCHAR(10),
        email VARCHAR(100),
        phone VARCHAR(20),
        present_address TEXT,
        permanent_address TEXT,
        guardian_name VARCHAR(100),
        guardian_phone VARCHAR(20),
        hostel_name VARCHAR(20) DEFAULT 'Hostel_A',
        floor_no INT DEFAULT 1,
        room_no INT DEFAULT 1,
        seat_no VARCHAR(20) DEFAULT '1',
        student_photo VARCHAR(255),
        id_card_photo VARCHAR(255),
        password VARCHAR(255),
        total_due DECIMAL(10,2) DEFAULT 0,
        join_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $create_table);
}

$check_due = mysqli_query($conn, "SHOW COLUMNS FROM hostel_students LIKE 'total_due'");
if (mysqli_num_rows($check_due) == 0) {
    mysqli_query($conn, "ALTER TABLE hostel_students ADD COLUMN total_due DECIMAL(10,2) DEFAULT 0");
}

$check_join = mysqli_query($conn, "SHOW COLUMNS FROM hostel_students LIKE 'join_date'");
if (mysqli_num_rows($check_join) == 0) {
    mysqli_query($conn, "ALTER TABLE hostel_students ADD COLUMN join_date DATE DEFAULT NULL");
}

$bills_check = mysqli_query($conn, "SHOW TABLES LIKE 'hostel_bills'");
if (mysqli_num_rows($bills_check) == 0) {
    mysqli_query($conn, "CREATE TABLE hostel_bills(
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        university_id VARCHAR(50),
        bill_type VARCHAR(50),
        amount DECIMAL(10,2),
        month_year VARCHAR(20),
        due_date DATE,
        paid_date DATE NULL,
        status VARCHAR(20) DEFAULT 'unpaid',
        late_fine DECIMAL(10,2) DEFAULT 0,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        days_stayed INT DEFAULT 30,
        total_days_in_month INT DEFAULT 30,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

$check_student = mysqli_query($conn, "SELECT * FROM hostel_students WHERE university_id='2410020666'");
if (mysqli_num_rows($check_student) == 0) {
    mysqli_query($conn, "INSERT INTO hostel_students (university_id, name, department, father_name, mother_name, dob, blood_group, email, phone, present_address, permanent_address, guardian_name, guardian_phone, hostel_name, floor_no, room_no, seat_no, student_photo, id_card_photo, password, total_due, join_date)
    VALUES ('2410020666', 'Redoy Khan Redu', 'CSE', 'Ismail', 'Kajoly', '2002-11-11', 'B+', 'redu@gmail.com', '017*******', 'Kst', 'Kst', 'Ismail', '017******', 'Hostel_A', 1, 2, 3, 'uploads/user.jpg', 'uploads/id.jpg', MD5('123456'), 5000, '" . date('Y-m-d') . "')");
}

$student_logged_in = isset($_SESSION['student_id']) && $_SESSION['student_id'] !== null;
$student_error = "";
$student_success = "";

if (isset($_POST['student_login'])) {
    $university_id = mysqli_real_escape_string($conn, $_POST['university_id']);
    $password = md5($_POST['password']);
    $q = mysqli_query($conn, "SELECT * FROM hostel_students WHERE university_id='$university_id' AND password='$password'");
    if ($q && mysqli_num_rows($q) == 1) {
        $student_data = mysqli_fetch_assoc($q);
        $_SESSION['student_id'] = $student_data['id'];
        $_SESSION['student_name'] = $student_data['name'];
        $_SESSION['student_university_id'] = $student_data['university_id'];
        $student_logged_in = true;
        header("Location: student_portal.php?page=dashboard");
        exit();
    } else {
        $student_error = "❌ Wrong University ID or Password!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: student_portal.php");
    exit();
}

if (isset($_POST['change_password']) && $student_logged_in) {
    $old_password = md5($_POST['old_password']);
    $new_password = md5($_POST['new_password']);
    $student_id = $_SESSION['student_id'];
    $check = mysqli_query($conn, "SELECT * FROM hostel_students WHERE id='$student_id' AND password='$old_password'");
    if (mysqli_num_rows($check) == 1) {
        $update = mysqli_query($conn, "UPDATE hostel_students SET password='$new_password' WHERE id='$student_id'");
        if ($update) {
            $student_success = "✅ Password changed successfully!";
        } else {
            $student_error = "❌ Failed to update password!";
        }
    } else {
        $student_error = "❌ Old password is incorrect!";
    }
}

if (isset($_GET['toggle_meal']) && $student_logged_in) {
    $student_id = $_SESSION['student_id'];
    $current_hour = (int)date('H');
    
    if ($current_hour < 20) {
        $meal_date = date('Y-m-d', strtotime('+1 day'));
        $check = mysqli_query($conn, "SELECT * FROM meal_tracking WHERE student_id='$student_id' AND meal_date='$meal_date'");
        if (mysqli_num_rows($check) > 0) {
            $update_query = mysqli_query($conn, "UPDATE meal_tracking SET status='taken' WHERE student_id='$student_id' AND meal_date='$meal_date'");
            if ($update_query) {
                $student_success = "✅ Meal marked as TAKEN for tomorrow (" . date('F j, Y', strtotime($meal_date)) . ")";
            } else {
                $student_error = "❌ Failed to update meal status!";
            }
        } else {
            $insert_query = mysqli_query($conn, "INSERT INTO meal_tracking (student_id, meal_date, status) VALUES ('$student_id', '$meal_date', 'taken')");
            if ($insert_query) {
                $student_success = "✅ Meal marked as TAKEN for tomorrow (" . date('F j, Y', strtotime($meal_date)) . ")";
            } else {
                $student_error = "❌ Failed to insert meal status!";
            }
        }
    } else {
        $student_error = "❌ Cannot book meal after 8:00 PM. Please try again after 12:00 AM for the next day.";
    }
    header("Location: student_portal.php?page=meal");
    exit();
}

function getStudentData($conn, $student_id) {
    $query = mysqli_query($conn, "SELECT * FROM hostel_students WHERE id='$student_id'");
    return mysqli_fetch_assoc($query);
}

function getStudentBills($conn, $student_id) {
    $query = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='$student_id' ORDER BY month_year DESC");
    $bills = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $bills[] = $row;
    }
    return $bills;
}

function getBookedSeats($conn, $hostel_name, $floor_no, $room_no) {
    $query = "SELECT seat_no FROM hostel_students WHERE hostel_name='$hostel_name' AND floor_no='$floor_no' AND room_no='$room_no'";
    $result = mysqli_query($conn, $query);
    $booked = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $booked[] = $row['seat_no'];
    }
    return $booked;
}

function getMealStatus($conn, $student_id, $date) {
    $q = mysqli_query($conn, "SELECT status FROM meal_tracking WHERE student_id='$student_id' AND meal_date='$date'");
    if ($q && mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);
        return $row['status'];
    }
    return 'not_taken';
}

function getMealCount($conn, $student_id, $month, $year) {
    $start = "$year-$month-01";
    $end = "$year-$month-" . date('t', strtotime($start));
    $q = mysqli_query($conn, "SELECT COUNT(*) as c FROM meal_tracking WHERE student_id='$student_id' AND status='taken' AND meal_date BETWEEN '$start' AND '$end'");
    $row = mysqli_fetch_assoc($q);
    return $row['c'];
}

function getAllDatesOfMonth($month, $year) {
    $days = date('t', strtotime("$year-$month-01"));
    $dates = [];
    for ($i = 1; $i <= $days; $i++) {
        $dates[] = "$year-$month-" . str_pad($i, 2, '0', STR_PAD_LEFT);
    }
    return $dates;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'login';
$current_month = date('m');
$current_year = date('Y');
$today = date('Y-m-d');
$days_in_month = date('t');
$all_dates = getAllDatesOfMonth($current_month, $current_year);
$meal_count = 0;
if ($student_logged_in) {
    $meal_count = getMealCount($conn, $_SESSION['student_id'], $current_month, $current_year);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>GUB Hostel Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a3c2c 0%, #2e7d32 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-card {
            width: 450px;
            background: white;
            border-radius: 20px;
            padding: 45px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            text-align: center;
        }
        .login-card .logo { font-size: 60px; margin-bottom: 10px; }
        .login-card h1 { color: #1a3c2c; font-size: 24px; margin-bottom: 5px; }
        .login-card h3 { color: #666; font-size: 14px; margin-bottom: 30px; }
        .login-card input { width: 100%; padding: 14px; margin: 10px 0; border: 1px solid #ddd; border-radius: 10px; font-size: 15px; }
        .login-card button { width: 100%; padding: 14px; background: #2e7d32; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 15px; }
        .login-card button:hover { background: #1a3c2c; }
        .error-msg { color: #d32f2f; text-align: center; margin-top: 15px; font-size: 14px; background: #ffebee; padding: 10px; border-radius: 8px; }
        .success-msg { color: #2e7d32; text-align: center; margin-top: 15px; font-size: 14px; background: #e8f5e9; padding: 10px; border-radius: 8px; }

        .portal-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px; background: #1a3c2c; color: white; position: fixed;
            height: 100vh; overflow-y: auto; box-shadow: 2px 0 15px rgba(0,0,0,0.1);
        }
        .sidebar-header { padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: center; }
        .sidebar-header h2 { font-size: 22px; margin-bottom: 5px; }
        .user-info { background: rgba(255,255,255,0.1); padding: 18px; margin: 20px; border-radius: 12px; text-align: center; }
        .user-info span { font-size: 12px; opacity: 0.8; display: block; }
        .user-info strong { display: block; font-size: 16px; margin: 8px 0 3px; color: #ffc107; }
        .user-info small { display: block; font-size: 11px; opacity: 0.7; }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu li a {
            display: flex; align-items: center; padding: 14px 22px; color: #e0e0e0;
            text-decoration: none; border-left: 3px solid transparent; gap: 12px;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: #ffc107; }
        .sidebar-menu li.logout-item { margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 15px; }

        .main-content { flex: 1; margin-left: 280px; padding: 30px; background: #f5f7fa; min-height: 100vh; }
        .welcome-header { background: white; padding: 25px 30px; border-radius: 16px; margin-bottom: 30px; border-left: 5px solid #2e7d32; }
        .welcome-header h1 { font-size: 24px; color: #333; }
        .form-page { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .form-page h2 { color: #1a3c2c; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e0e0e0; }
        .row { margin-bottom: 18px; display: flex; flex-wrap: wrap; align-items: center; }
        .label { width: 170px; font-weight: 600; color: #333; }
        .row input { flex: 1; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .form-page button { background: #2e7d32; color: white; border: none; padding: 14px; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 10px; }

        .dashboard-cards { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
        .card { background: white; padding: 25px; border-radius: 16px; flex: 1; min-width: 150px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .card h3 { font-size: 28px; color: #2e7d32; margin-bottom: 10px; }
        .card p { color: #555; font-size: 14px; }

        .student-detail { background: #fafafa; padding: 25px; border-radius: 16px; }
        .student-detail p { margin: 12px 0; padding-bottom: 8px; border-bottom: 1px solid #eee; display: flex; flex-wrap: wrap; }
        .student-detail strong { width: 180px; color: #555; }
        .student-detail img { max-width: 200px; margin-top: 10px; border-radius: 10px; }
        .profile-pic { text-align: center; margin-bottom: 25px; }
        .profile-pic img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #2e7d32; }

        .bill-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
        .bill-table th, .bill-table td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #eee; }
        .bill-table th { background: #1565c0; color: white; font-size: 13px; }
        .status-paid { background: #c8e6c9; color: #2e7d32; padding: 3px 8px; border-radius: 20px; font-size: 11px; display: inline-block; }
        .status-unpaid { background: #ffcdd2; color: #c62828; padding: 3px 8px; border-radius: 20px; font-size: 11px; display: inline-block; }
        .total-due { font-size: 28px; font-weight: bold; color: #c62828; }
       
        .financial-summary {
            background: linear-gradient(135deg, #1a3c2c 0%, #2e7d32 100%);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            color: white;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 12px;
        }
        .summary-item {
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            padding: 8px 12px;
            text-align: center;
        }
        .summary-item h4 { font-size: 11px; opacity: 0.8; margin-bottom: 5px; }
        .summary-item .amount { font-size: 16px; font-weight: bold; }
        .summary-item .amount.positive { color: #c8e6c9; }
        .summary-item .amount.warning { color: #ffeb3b; }
        .summary-item small { font-size: 9px; opacity: 0.7; display: block; margin-top: 3px; }

        .rooms-grid { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .room-card { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); min-width: 300px; }
        .room-title { font-size: 22px; font-weight: bold; color: #1565c0; margin-bottom: 20px; text-align: center; }
        .seats-grid { display: flex; gap: 20px; justify-content: center; margin-top: 15px; }
        .seat { width: 90px; height: 90px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 12px; font-weight: bold; font-size: 12px; text-align: center; }
        .seat-booked { background: #c8e6c9; color: #2e7d32; border: 2px solid #a5d6a7; }
        .seat-available { background: #ffcdd2; color: #c62828; border: 2px solid #ef9a9a; }

        .info-box { background: #e3f2fd; padding: 15px 20px; border-radius: 12px; margin-top: 20px; }
        .bill-info-box { background: #fff8e1; padding: 15px 20px; border-radius: 12px; margin-top: 20px; border-left: 4px solid #ffc107; }
        .discount-badge { background: #e8f5e9; color: #2e7d32; padding: 2px 6px; border-radius: 12px; font-size: 10px; display: inline-block; }
       
        .meal-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .meal-table th, .meal-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .meal-table th { background: #2e7d32; color: white; }
        .meal-status-taken { background: #c8e6c9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; display: inline-block; font-weight: bold; }
        .meal-status-not { background: #ffcdd2; color: #c62828; padding: 5px 12px; border-radius: 20px; display: inline-block; font-weight: bold; }
        .meal-status-future { background: #e0e0e0; color: #666; padding: 5px 12px; border-radius: 20px; display: inline-block; }
        .meal-toggle-btn { background: #ff9800; color: white; border: none; cursor: pointer; font-size: 12px; padding: 8px 15px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: bold; }
        .meal-toggle-btn:hover { background: #e65100; }
        .meal-toggle-btn-disabled { background: #ccc; color: #666; padding: 8px 15px; border-radius: 8px; display: inline-block; font-size: 12px; cursor: not-allowed; }
        .meal-summary-card { background: linear-gradient(135deg, #ff9800, #f57c00); padding: 20px; border-radius: 12px; margin-bottom: 20px; text-align: center; color: white; }
        .meal-summary-card h2 { font-size: 48px; }

        @media (max-width: 768px) {
            .sidebar { width: 240px; }
            .main-content { margin-left: 240px; padding: 20px; }
            .student-detail strong { width: 140px; }
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<?php if (!$student_logged_in) { ?>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">🏢</div>
            <h1>Green University of Bangladesh</h1>
            <h3>GUB Hostel Student Portal</h3>
            <form method="post">
                <input type="text" name="university_id" placeholder="University ID" required>
                <input type="password" name="password" placeholder="Password" required>
                <button name="student_login">🔐 Login to Student Portal</button>
            </form>
            <?php if (isset($student_error) && $student_error != "") echo "<p class='error-msg'>$student_error</p>"; ?>
            <?php if (isset($student_success) && $student_success != "") echo "<p class='success-msg'>$student_success</p>"; ?>
        </div>
    </div>
<?php } else {
    $student_data = getStudentData($conn, $_SESSION['student_id']);
    if (!$student_data) {
        session_destroy();
        header("Location: student_portal.php");
        exit();
    }
   
    $student_bills = getStudentBills($conn, $_SESSION['student_id']);
    
    $total_fee_without_discount = 0;
    $total_discount_sum = 0;
    $total_late_fine_sum = 0;
    $total_paid_sum = 0;
    $security_amount = 0;
    $security_paid = false;
    $display_total_days_month = 30;
    
    $security_query = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='{$_SESSION['student_id']}' AND bill_type='security'");
    if ($security_query && mysqli_num_rows($security_query) > 0) {
        $security = mysqli_fetch_assoc($security_query);
        $security_amount = $security['amount'];
        if ($security['status'] == 'paid') {
            $security_paid = true;
        }
    }
    
    $monthly_bills = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='{$_SESSION['student_id']}' AND bill_type='monthly'");
    while ($bill = mysqli_fetch_assoc($monthly_bills)) {
        $original_amount = 7500; // Base monthly fee
        $total_fee_without_discount += $original_amount;
        $total_discount_sum += $bill['discount_amount'];
        $total_late_fine_sum += $bill['late_fine'];
        if ($bill['status'] == 'paid') {
            $total_paid_sum += $bill['amount'];
        }
        if ($bill['total_days_in_month'] > 0) {
            $display_total_days_month = $bill['total_days_in_month'];
        }
    }
    
    $total_bill_after_discount = $total_fee_without_discount - $total_discount_sum + $total_late_fine_sum;
    $total_due_calc = $total_bill_after_discount - $total_paid_sum;
    
    if ($security_paid) {
        $security_display = "0.00";
        $advance_display = number_format($security_amount, 2);
    } else {
        $security_display = number_format($security_amount, 2);
        $advance_display = "0.00";
    }
    
    $total_bill_amount = 0;
    $total_paid = 0;
    $total_discount = 0;
    $total_late_fine = 0;
    $total_due = $student_data['total_due'];
   
    foreach ($student_bills as $bill) {
        if ($bill['status'] == 'paid') $total_paid += $bill['amount'];
        $total_bill_amount += $bill['amount'];
        $total_discount += $bill['discount_amount'];
        $total_late_fine += $bill['late_fine'];
    }
?>
    <div class="portal-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>🏢 Green University<br>of Bangladesh</h2>
                <p>Student Portal</p>
            </div>
            <div class="user-info">
                <span>👤 Logged in as</span>
                <strong><?php echo htmlspecialchars($student_data['name']); ?></strong>
                <small><?php echo $student_data['university_id']; ?></small>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_portal.php?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">🏠 Home</a></li>
                <li><a href="student_portal.php?page=profile" class="<?php echo $page == 'profile' ? 'active' : ''; ?>">👤 My Profile</a></li>
                <li><a href="student_portal.php?page=room" class="<?php echo $page == 'room' ? 'active' : ''; ?>">🏘️ My Room</a></li>
                <li><a href="student_portal.php?page=meal" class="<?php echo $page == 'meal' ? 'active' : ''; ?>">🍽️ Meal Calendar</a></li>
                <li><a href="student_portal.php?page=bills" class="<?php echo $page == 'bills' ? 'active' : ''; ?>">💰 Bill History</a></li>
                <li><a href="student_portal.php?page=change_password" class="<?php echo $page == 'change_password' ? 'active' : ''; ?>">🔒 Change Password</a></li>
                <li class="logout-item"><a href="student_portal.php?logout=1">🚪 Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="welcome-header">
                <h1>Welcome to GUB Hostel, <?php echo htmlspecialchars($student_data['name']); ?>! 🎉</h1>
            </div>

            <?php if ($student_success != "") echo "<div class='form-page'><p class='success-msg'>$student_success</p></div>"; ?>
            <?php if ($student_error != "") echo "<div class='form-page'><p class='error-msg'>$student_error</p></div>"; ?>

            <?php if ($page == 'dashboard') { ?>
                <div class="form-page">
                    <h2>📊 Dashboard Overview</h2>
                    <div class="dashboard-cards">
                        <div class="card"><h3><?php echo htmlspecialchars($student_data['name']); ?></h3><p>Student Name</p></div>
                        <div class="card"><h3><?php echo htmlspecialchars($student_data['university_id']); ?></h3><p>University ID</p></div>
                        <div class="card"><h3><?php echo htmlspecialchars($student_data['department']); ?></h3><p>Department</p></div>
                        <div class="card"><h3><?php echo str_replace('_', ' ', $student_data['hostel_name']); ?></h3><p>Hostel Name</p></div>
                        <div class="card"><h3>Floor <?php echo $student_data['floor_no']; ?></h3><p>Floor No</p></div>
                        <div class="card"><h3>Room <?php echo $student_data['room_no']; ?></h3><p>Room No</p></div>
                        <div class="card"><h3>Seat <?php echo $student_data['seat_no']; ?></h3><p>Seat No</p></div>
                    </div>
                    <div class="dashboard-cards" style="margin-top: 10px;">
                        <div class="card"><h3 class="total-due"><?php echo number_format($total_due, 2); ?> TK</h3><p>Total Due Amount</p></div>
                        <div class="card"><h3><?php echo number_format($total_paid, 2); ?> TK</h3><p>Total Paid</p></div>
                        <div class="card"><h3><?php echo number_format($total_discount, 2); ?> TK</h3><p>Total Discount Received</p></div>
                    </div>
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="student_portal.php?page=meal" style="background: #ff9800; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; margin-right: 10px;">🍽️ Meal Calendar</a>
                        <a href="student_portal.php?page=bills" style="background: #2e7d32; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none;">💰 Bill History</a>
                    </div>
                </div>
            <?php } elseif ($page == 'profile') { ?>
                <div class="form-page">
                    <h2>👤 My Profile</h2>
                    <div class="student-detail">
                        <div class="profile-pic"><img src="<?php echo $student_data['student_photo']; ?>" alt="Student Photo"></div>
                        <p><strong>🏷️ University ID:</strong> <?php echo htmlspecialchars($student_data['university_id']); ?></p>
                        <p><strong>👨‍🎓 Name:</strong> <?php echo htmlspecialchars($student_data['name']); ?></p>
                        <p><strong>📚 Department:</strong> <?php echo htmlspecialchars($student_data['department']); ?></p>
                        <p><strong>👨 Father's Name:</strong> <?php echo htmlspecialchars($student_data['father_name']); ?></p>
                        <p><strong>👩 Mother's Name:</strong> <?php echo htmlspecialchars($student_data['mother_name']); ?></p>
                        <p><strong>🎂 Date of Birth:</strong> <?php echo $student_data['dob']; ?></p>
                        <p><strong>🩸 Blood Group:</strong> <?php echo $student_data['blood_group']; ?></p>
                        <p><strong>📧 Email:</strong> <?php echo $student_data['email']; ?></p>
                        <p><strong>📞 Phone:</strong> <?php echo $student_data['phone']; ?></p>
                        <p><strong>🏠 Present Address:</strong> <?php echo nl2br($student_data['present_address']); ?></p>
                        <p><strong>📍 Permanent Address:</strong> <?php echo nl2br($student_data['permanent_address']); ?></p>
                        <p><strong>👨‍👩‍👧 Guardian:</strong> <?php echo $student_data['guardian_name']; ?> (<?php echo $student_data['guardian_phone']; ?>)</p>
                        <p><strong>🏢 Hostel:</strong> <?php echo str_replace('_', ' ', $student_data['hostel_name']); ?></p>
                        <p><strong>📍 Room Location:</strong> Floor <?php echo $student_data['floor_no']; ?> / Room <?php echo $student_data['room_no']; ?> / Seat <?php echo $student_data['seat_no']; ?></p>
                        <p><strong>💰 Total Due:</strong> <span class="total-due"><?php echo number_format($total_due, 2); ?> TK</span></p>
                        <p><strong>🎁 Total Discount Received:</strong> <?php echo number_format($total_discount, 2); ?> TK</p>
                        <p><strong>🍽️ Meals Taken This Month:</strong> <?php echo $meal_count; ?> / <?php echo $days_in_month; ?></p>
                        <p><strong>🪪 ID Card:</strong><br><img src="<?php echo $student_data['id_card_photo']; ?>" alt="ID Card"></p>
                    </div>
                </div>
            <?php } elseif ($page == 'room') {
                $hostel = $student_data['hostel_name'];
                $floor = $student_data['floor_no'];
                $room = $student_data['room_no'];
                $bookedSeats = getBookedSeats($conn, $hostel, $floor, $room);
            ?>
                <div class="form-page">
                    <h2>🏘️ My Room Details</h2>
                    <div style="background: #e8f5e9; border-radius: 16px; padding: 20px; margin-bottom: 25px;">
                        <p><strong>🏠 Hostel:</strong> <?php echo str_replace('_', ' ', $hostel); ?></p>
                        <p><strong>📍 Floor:</strong> <?php echo $floor; ?></p>
                        <p><strong>🚪 Room No:</strong> <?php echo $room; ?></p>
                        <p><strong>🪑 Your Seat:</strong> <span style="background: #2e7d32; color: white; padding: 5px 15px; border-radius: 25px;">Seat <?php echo $student_data['seat_no']; ?></span></p>
                    </div>
                    <h3>🗺️ Room Seat Map</h3>
                    <div class="rooms-grid">
                        <div class="room-card">
                            <div class="room-title">Room <?php echo $room; ?></div>
                            <div class="seats-grid">
                                <?php for($seat = 1; $seat <= 3; $seat++):
                                    $isBooked = in_array((string)$seat, $bookedSeats);
                                    $isYourSeat = ($seat == $student_data['seat_no']);
                                    if($isYourSeat) {
                                        echo "<div class='seat seat-booked'><strong>Seat $seat</strong><br><small>✅ Your Seat</small></div>";
                                    } else if($isBooked) {
                                        echo "<div class='seat seat-booked'><strong>Seat $seat</strong><br><small>📌 Booked</small></div>";
                                    } else {
                                        echo "<div class='seat seat-available'><strong>Seat $seat</strong><br><small>🟢 Available</small></div>";
                                    }
                                endfor; ?>
                            </div>
                        </div>
                    </div>
                    <div class="info-box">
                        <p>💡 Your seat is highlighted in green. For any issues, contact the hostel office.</p>
                    </div>
                </div>
            <?php } elseif ($page == 'meal') { ?>
                <div class="form-page">
                    <h2>🍽️ Meal Calendar - <?php echo date('F Y'); ?></h2>
                   
                    <div class="meal-summary-card">
                        <h2><?php echo $meal_count; ?> / <?php echo $days_in_month; ?></h2>
                        <p>Meals taken in <?php echo date('F Y'); ?></p>
                    </div>
                   
                    <?php
                    $current_hour_display = (int)date('H');
                    $can_book_tomorrow = ($current_hour_display < 20);
                    ?>
                   
                    <div class="info-box" style="margin-bottom: 20px; background: #fff3e0;">
                        <p>⏰ <strong>Booking Rules:</strong></p>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>✅ Before 8:00 PM: You can book meal for <strong>tomorrow</strong></li>
                            <li>❌ After 8:00 PM: Booking for tomorrow is <strong>closed</strong></li>
                            <li>🔄 After 12:00 AM (midnight): You can book for the <strong>next available day</strong></li>
                            <?php if($can_book_tomorrow): ?>
                                <li style="color: green;">🔔 <strong>Booking is OPEN now!</strong> You can book for tomorrow.</li>
                            <?php else: ?>
                                <li style="color: red;">🔴 <strong>Booking is CLOSED now!</strong> Please try after 12:00 AM.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                   
                    <table class="meal-table">
                        <thead>
                            <tr><th>Date</th><th>Day</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $tomorrow_date_obj = date('Y-m-d', strtotime('+1 day'));
                            $day_after_tomorrow = date('Y-m-d', strtotime('+2 day'));
                            $can_book = ((int)date('H') < 20);
                           
                            foreach ($all_dates as $date) {
                                $day_name = date('l', strtotime($date));
                                $display_date = date('F j, Y', strtotime($date));
                                $status = getMealStatus($conn, $_SESSION['student_id'], $date);
                                $is_past = ($date < $today);
                                $is_tomorrow = ($date == $tomorrow_date_obj);
                                $is_day_after_tomorrow = ($date == $day_after_tomorrow);
                               
                                if ($is_past) {
                                    $status_class = ($status == 'taken') ? 'meal-status-taken' : 'meal-status-not';
                                    $status_text = ($status == 'taken') ? '✅ Taken' : '❌ Not Taken';
                                    echo "<tr><td><strong>$display_date</strong></td><td>$day_name</td><td><span class='$status_class'>$status_text</span></td><td style='color:#999;'>🔒 Locked</td></tr>";
                                }
                                elseif ($is_tomorrow) {
                                    $status_class = ($status == 'taken') ? 'meal-status-taken' : 'meal-status-not';
                                    $status_text = ($status == 'taken') ? '✅ Taken' : '❌ Not Taken';
                                    echo "<tr><td><strong>$display_date (TOMORROW)</strong></td><td>$day_name</td><td><span class='$status_class'>$status_text</span></td>";
                                    if ($can_book) {
                                        if ($status == 'taken') {
                                            echo "<td><a href='student_portal.php?page=meal&toggle_meal=1' class='meal-toggle-btn' onclick='return confirm(\"Cancel meal for tomorrow?\")'>❌ Cancel Meal</a></td>";
                                        } else {
                                            echo "<td><a href='student_portal.php?page=meal&toggle_meal=1' class='meal-toggle-btn' onclick='return confirm(\"Book meal for tomorrow?\")'>🍽️ Book Meal</a></td>";
                                        }
                                    } else {
                                        echo "<td><span class='meal-toggle-btn-disabled'>🚫 Closed (After 8 PM)</span></td>";
                                    }
                                    echo "</tr>";
                                }
                                elseif ($is_day_after_tomorrow && !$can_book) {
                                    $status_class = ($status == 'taken') ? 'meal-status-taken' : 'meal-status-not';
                                    $status_text = ($status == 'taken') ? '✅ Taken' : '❌ Not Taken';
                                    echo "<tr><td>$display_date</td><td>$day_name</td><td><span class='$status_class'>$status_text</span></td>";
                                    if ($status == 'taken') {
                                        echo "<td><a href='student_portal.php?page=meal&toggle_meal=1' class='meal-toggle-btn' onclick='return confirm(\"Cancel meal?\")'>❌ Cancel Meal</a></td>";
                                    } else {
                                        echo "<td><a href='student_portal.php?page=meal&toggle_meal=1' class='meal-toggle-btn' onclick='return confirm(\"Book meal?\")'>🍽️ Book Meal</a></td>";
                                    }
                                    echo "</tr>";
                                }
                                elseif ($date > $today) {
                                    $status_class = ($status == 'taken') ? 'meal-status-taken' : 'meal-status-future';
                                    $status_text = ($status == 'taken') ? '✅ Taken' : '🔮 Future Date';
                                    echo "<tr><td>$display_date</td><td>$day_name</td><td><span class='$status_class'>$status_text</span></td><td style='color:#999;'>⏳ Not available</td></tr>";
                                }
                                else {
                                    $status_class = ($status == 'taken') ? 'meal-status-taken' : 'meal-status-not';
                                    $status_text = ($status == 'taken') ? '✅ Taken' : '❌ Not Taken';
                                    echo "<tr><td><strong>$display_date (TODAY)</strong></td><td>$day_name</td><td><span class='$status_class'>$status_text</span></td><td style='color:#999;'>📅 Today's meal</td></tr>";
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>
            <?php } elseif ($page == 'bills') { ?>
                <div class="form-page">
                    <h2>💰 Bill History & Payment</h2>
                   
                    <!-- Financial Summary - Exactly like Admin Portal -->
                    <div class="financial-summary">
                        <div class="summary-grid">
                            <div class="summary-item">
                                <h4>💰 Total Fee</h4>
                                <div class="amount positive"><?php echo number_format($total_fee_without_discount, 2); ?></div>
                                <small>7,500 TK/month</small>
                            </div>
                            <div class="summary-item">
                                <h4>🎁 Discount</h4>
                                <div class="amount warning">- <?php echo number_format($total_discount_sum, 2); ?></div>
                                <small>Based on days</small>
                            </div>
                            <div class="summary-item">
                                <h4>⚠️ Late Fine</h4>
                                <div class="amount warning">+ <?php echo number_format($total_late_fine_sum, 2); ?></div>
                                <small>500 TK/month</small>
                            </div>
                            <div class="summary-item">
                                <h4>📋 Net Bill</h4>
                                <div class="amount positive"><?php echo number_format($total_bill_after_discount, 2); ?></div>
                                <small>After discount</small>
                            </div>
                            <div class="summary-item">
                                <h4>🔒 Security</h4>
                                <div class="amount"><?php echo $security_display; ?></div>
                                <small>One time</small>
                            </div>
                            <div class="summary-item">
                                <h4>💵 Paid</h4>
                                <div class="amount positive"><?php echo number_format($total_paid_sum, 2); ?></div>
                                <small>Total received</small>
                            </div>
                            <div class="summary-item">
                                <h4>⚖️ Balance</h4>
                                <div class="amount"><?php echo $advance_display; ?> Advance</div>
                                <small>Security Deposit</small>
                            </div>
                            <div class="summary-item">
                                <h4>📌 Due Amount</h4>
                                <div class="amount <?php echo $total_due_calc < 0 ? 'negative' : 'warning'; ?>">
                                    <?php echo number_format($total_due_calc, 2); ?>
                                </div>
                                <small>(+ due, - advance)</small>
                            </div>
                        </div>
                    </div>
                   
                    <?php if ($security_query && mysqli_num_rows($security_query) > 0): ?>
                    <h3 style="margin: 12px 0 6px; font-size: 14px;">🔒 Security Deposit</h3>
                    <table class="bill-table">
                        <thead>
                            <tr><th>Month</th><th>Bill Type</th><th>Amount (TK)</th><th>Due Date</th><th>Paid Date</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $security_query2 = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='{$_SESSION['student_id']}' AND bill_type='security'");
                            while ($sec = mysqli_fetch_assoc($security_query2)) { 
                                $due_date_formatted = date('Y-m-d', strtotime($sec['due_date']));
                                $paid_date_formatted = $sec['paid_date'] ? date('Y-m-d', strtotime($sec['paid_date'])) : '0000-00-00';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sec['month_year']); ?></div>
                                <td>🔒 Security Deposit</div>
                                <td><?php echo number_format($sec['amount'], 2); ?></div>
                                <td><?php echo $due_date_formatted; ?></div>
                                <td><?php echo $paid_date_formatted; ?></div>
                                <td><span class="<?php echo $sec['status'] == 'paid' ? 'status-paid' : 'status-unpaid'; ?>"><?php echo $sec['status'] == 'paid' ? '✅ Paid' : '⚠️ Unpaid'; ?></span></div>
                                <td><?php echo $sec['status'] == 'paid' ? '✓ Paid' : '🏢 Pay at Office'; ?></div>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </div>
                    <?php endif; ?>
                   
                    <h3 style="margin: 15px 0 8px; font-size: 14px;">📅 Monthly Bill Details</h3>
                    <table class="bill-table">
                        <thead>
                            <tr><th>Month</th><th>Bill Type</th><th>Days Stayed</th><th>Amount (TK)</th><th>Discount</th><th>Late Fine</th><th>Due Date</th><th>Paid Date</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $monthly_bills_query = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='{$_SESSION['student_id']}' AND bill_type='monthly' ORDER BY month_year DESC");
                            if ($monthly_bills_query && mysqli_num_rows($monthly_bills_query) > 0) {
                                while ($bill = mysqli_fetch_assoc($monthly_bills_query)) {
                                    $days = $bill['days_stayed'] ?? 30;
                                    $total_days_month = $bill['total_days_in_month'] ?? 30;
                                    $month_display = date('M Y', strtotime($bill['month_year'] . '-01'));
                                    $due_date_formatted = date('Y-m-d', strtotime($bill['due_date']));
                                    $paid_date_formatted = $bill['paid_date'] ? date('Y-m-d', strtotime($bill['paid_date'])) : '0000-00-00';
                                    $status_class = ($bill['status'] == 'paid') ? 'status-paid' : 'status-unpaid';
                                    $status_text = ($bill['status'] == 'paid') ? '✅ Paid' : '⚠️ Unpaid';
                            ?>
                            <tr>
                                <td><?php echo $month_display; ?></div>
                                <td>🏠 Monthly Bill</div>
                                <td><?php echo $days . '/' . $total_days_month; ?></div>
                                <td><?php echo number_format($bill['amount'], 2); ?></div>
                                <td><?php echo $bill['discount_amount'] > 0 ? '- ' . number_format($bill['discount_amount'], 2) : '-'; ?></div>
                                <td><?php echo $bill['late_fine'] > 0 ? number_format($bill['late_fine'], 2) : '-'; ?></div>
                                <td><?php echo $due_date_formatted; ?></div>
                                <td><?php echo $paid_date_formatted; ?></div>
                                <td><span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span></div>
                                <td><?php echo ($bill['status'] == 'paid') ? '✓ Paid' : '💰 Pending - Pay at Office'; ?></div>
                            </tr>
                            <?php } } else { ?>
                            <tr><td colspan="10" style="text-align:center; padding:40px;">📭 No monthly bills found</div></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                   
                    <div class="bill-info-box">
                        <h4>📌 Billing & Discount Information:</h4>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li>🔒 <strong>Security Deposit:</strong> 5,000 TK (One time payment - Pay at hostel office)</li>
                            <li>🏠 <strong>Monthly Bill:</strong> 7,500 TK (Room: 4,000 TK + Food: 3,500 TK)</li>
                            <li>🎁 <strong>Discount:</strong> You get discount on FOOD cost only based on how many days you stayed</li>
                            <li>📊 <strong>Discount Formula:</strong> (<?php echo $display_total_days_month; ?> - Days Stayed) × (3,500 ÷ <?php echo $display_total_days_month; ?>) TK</li>
                            <li>💡 <strong>Example:</strong> If you stayed 20 days in a <?php echo $display_total_days_month; ?>-day month, discount = 10 × <?php echo number_format(3500/$display_total_days_month, 2); ?> = <?php echo number_format(10 * (3500/$display_total_days_month), 2); ?> TK</li>
                            <li>📅 <strong>Bill Generation:</strong> 1st-3rd of each month</li>
                            <li>⏰ <strong>Due Date:</strong> 20th of each month</li>
                            <li>⚠️ <strong>Late Fine:</strong> 500 TK added automatically after 20th if unpaid</li>
                            <li>🏢 <strong>Payment Process:</strong> Please pay at the hostel office. Admin will confirm your payment.</li>
                        </ul>
                    </div>
                </div>
            <?php } elseif ($page == 'change_password') { ?>
                <div class="form-page">
                    <h2>🔒 Change Password</h2>
                    <form method="post">
                        <div class="row"><span class="label">Current Password :</span><input type="password" name="old_password" required></div>
                        <div class="row"><span class="label">New Password :</span><input type="password" name="new_password" id="new_password" required></div>
                        <div class="row"><span class="label">Confirm Password :</span><input type="password" name="confirm_password" id="confirm_password" required></div>
                        <button name="change_password">Update Password</button>
                    </form>
                </div>
                <script>
                    document.getElementById('confirm_password').addEventListener('keyup', function() {
                        var newPass = document.getElementById('new_password').value;
                        if (this.value != newPass) {
                            this.setCustomValidity('Passwords do not match');
                        } else {
                            this.setCustomValidity('');
                        }
                    });
                </script>
            <?php } ?>
        </div>
    </div>
<?php } ?>
</body>
</html>