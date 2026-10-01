<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Dhaka');

$conn = mysqli_connect("localhost", "root", "") or die("DB Error");

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS university_portal");
mysqli_select_db($conn, "university_portal");

$check_manual = mysqli_query($conn, "SHOW COLUMNS FROM hostel_bills LIKE 'manual_late_fine'");
if (mysqli_num_rows($check_manual) == 0) {
    mysqli_query($conn, "ALTER TABLE hostel_bills ADD COLUMN manual_late_fine TINYINT DEFAULT 0");
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS meal_tracking(
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    meal_date DATE,
    status ENUM('taken', 'not_taken') DEFAULT 'not_taken',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_meal (student_id, meal_date)
)");

$check_days = mysqli_query($conn, "SHOW COLUMNS FROM hostel_bills LIKE 'days_stayed'");
if (mysqli_num_rows($check_days) == 0) {
    mysqli_query($conn, "ALTER TABLE hostel_bills ADD COLUMN days_stayed INT DEFAULT 30");
}

$check_discount = mysqli_query($conn, "SHOW COLUMNS FROM hostel_bills LIKE 'discount_amount'");
if (mysqli_num_rows($check_discount) == 0) {
    mysqli_query($conn, "ALTER TABLE hostel_bills ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0");
}

$check_late = mysqli_query($conn, "SHOW COLUMNS FROM hostel_bills LIKE 'late_fine'");
if (mysqli_num_rows($check_late) == 0) {
    mysqli_query($conn, "ALTER TABLE hostel_bills ADD COLUMN late_fine DECIMAL(10,2) DEFAULT 0");
}

$check_total_days = mysqli_query($conn, "SHOW COLUMNS FROM hostel_bills LIKE 'total_days_in_month'");
if (mysqli_num_rows($check_total_days) == 0) {
    mysqli_query($conn, "ALTER TABLE hostel_bills ADD COLUMN total_days_in_month INT DEFAULT 30");
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hostel_bills(
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
    manual_late_fine TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'hostel_students'");
if (mysqli_num_rows($table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE hostel_students(
        id INT AUTO_INCREMENT PRIMARY KEY,
        university_id VARCHAR(50),
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
        join_date DATE DEFAULT NULL
    )");
}

$cols = ['join_date', 'hostel_name', 'floor_no', 'room_no', 'seat_no', 'total_due'];
$col_sqls = [
    'join_date' => "ALTER TABLE hostel_students ADD COLUMN join_date DATE DEFAULT NULL",
    'hostel_name' => "ALTER TABLE hostel_students ADD COLUMN hostel_name VARCHAR(20) DEFAULT 'Hostel_A'",
    'floor_no' => "ALTER TABLE hostel_students ADD COLUMN floor_no INT DEFAULT 1",
    'room_no' => "ALTER TABLE hostel_students ADD COLUMN room_no INT DEFAULT 1",
    'seat_no' => "ALTER TABLE hostel_students ADD COLUMN seat_no VARCHAR(20) DEFAULT '1'",
    'total_due' => "ALTER TABLE hostel_students ADD COLUMN total_due DECIMAL(10,2) DEFAULT 0"
];

foreach ($col_sqls as $col => $sql) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM hostel_students LIKE '$col'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, $sql);
    }
}

mysqli_query($conn, "UPDATE hostel_students SET hostel_name = 'Hostel_A' WHERE hostel_name IS NULL OR hostel_name = ''");
mysqli_query($conn, "UPDATE hostel_students SET floor_no = 1 WHERE floor_no IS NULL OR floor_no = 0");
mysqli_query($conn, "UPDATE hostel_students SET room_no = 1 WHERE room_no IS NULL OR room_no = 0");
mysqli_query($conn, "UPDATE hostel_students SET seat_no = '1' WHERE seat_no IS NULL OR seat_no = ''");
mysqli_query($conn, "UPDATE hostel_students SET total_due = 0 WHERE total_due IS NULL");
mysqli_query($conn, "UPDATE hostel_students SET join_date = CURDATE() WHERE join_date IS NULL");

// Fix existing bills due_date to 20th
mysqli_query($conn, "UPDATE hostel_bills SET due_date = CONCAT(LEFT(month_year, 7), '-20') WHERE bill_type='monthly' AND due_date != CONCAT(LEFT(month_year, 7), '-20')");

$backup_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'hostel_students_backup'");
if (mysqli_num_rows($backup_table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE hostel_students_backup LIKE hostel_students");
    mysqli_query($conn, "ALTER TABLE hostel_students_backup ADD COLUMN deleted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$backup_bills_check = mysqli_query($conn, "SHOW TABLES LIKE 'hostel_bills_backup'");
if (mysqli_num_rows($backup_bills_check) == 0) {
    mysqli_query($conn, "CREATE TABLE hostel_bills_backup LIKE hostel_bills");
    mysqli_query($conn, "ALTER TABLE hostel_bills_backup ADD COLUMN deleted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hostel_admin(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255)
)");

$chk = mysqli_query($conn, "SELECT * FROM hostel_admin");
if (mysqli_num_rows($chk) == 0) {
    mysqli_query($conn, "INSERT INTO hostel_admin(username,password) VALUES('hosteladmin', MD5('hostel123'))");
}

$check_redu = mysqli_query($conn, "SELECT * FROM hostel_students WHERE university_id='2410020666'");
if (mysqli_num_rows($check_redu) == 0) {
    mysqli_query($conn, "INSERT INTO hostel_students (university_id, name, department, father_name, mother_name, dob, blood_group, email, phone, present_address, permanent_address, guardian_name, guardian_phone, hostel_name, floor_no, room_no, seat_no, student_photo, id_card_photo, password, total_due, join_date) 
    VALUES ('2410020666', 'Redoy Khan Redu', 'CSE', 'ismail', 'kajoly', '2002-11-11', 'B+', 'redu@gmail.com', '017*******', 'kst', 'kst', 'ismail', '017******', 'Hostel_A', 1, 2, 3, 'uploads/placeholder.jpg', 'uploads/placeholder.jpg', MD5('123456'), 0, '2026-04-27')");
}


function getTotalDaysInMonth($month_year) {
    if (empty($month_year)) return 30;
    return date('t', strtotime($month_year . '-01'));
}

function calculateBillBreakdown($days_stayed, $total_days_in_month) {
    $room_rent = 4000;
    $base_food_cost = 3500;
    if ($total_days_in_month <= 0) $total_days_in_month = 30;
    $per_day_food = $base_food_cost / $total_days_in_month;
    if ($days_stayed >= $total_days_in_month) {
        $food_cost = $base_food_cost;
        $discount = 0;
    } else {
        $food_cost = round($days_stayed * $per_day_food, 2);
        $discount = round($base_food_cost - $food_cost, 2);
    }
    return [
        'room_rent' => $room_rent,
        'food_cost' => $food_cost,
        'discount' => $discount,
        'total' => $room_rent + $food_cost,
        'per_day_food' => $per_day_food,
        'total_days' => $total_days_in_month
    ];
}

function generateMonthlyBills($conn) {
    $current_month = date('Y-m');
    $current_day = (int)date('d');
    $due_date = date('Y-m-20');
    $total_days = date('t');
    if ($current_day >= 1 && $current_day <= 3) {
        $check = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE month_year='$current_month' AND bill_type='monthly'");
        if (mysqli_num_rows($check) == 0) {
            $students = mysqli_query($conn, "SELECT id, university_id FROM hostel_students");
            while ($student = mysqli_fetch_assoc($students)) {
                $bill_data = calculateBillBreakdown($total_days, $total_days);
                $insert = "INSERT INTO hostel_bills (student_id, university_id, bill_type, amount, month_year, due_date, status, discount_amount, days_stayed, total_days_in_month, late_fine, manual_late_fine) 
                           VALUES ('{$student['id']}', '{$student['university_id']}', 'monthly', {$bill_data['total']}, '$current_month', '$due_date', 'unpaid', {$bill_data['discount']}, $total_days, $total_days, 0, 0)";
                mysqli_query($conn, $insert);
                mysqli_query($conn, "UPDATE hostel_students SET total_due = total_due + {$bill_data['total']} WHERE id='{$student['id']}'");
            }
        }
    }
    if ($current_day > 20) {
        $unpaid_bills = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE month_year='$current_month' AND status='unpaid' AND late_fine = 0 AND manual_late_fine = 0");
        while ($bill = mysqli_fetch_assoc($unpaid_bills)) {
            mysqli_query($conn, "UPDATE hostel_bills SET late_fine = 500, amount = amount + 500 WHERE id='{$bill['id']}'");
            mysqli_query($conn, "UPDATE hostel_students SET total_due = total_due + 500 WHERE id='{$bill['student_id']}'");
        }
    }
}

function updateBillDays($conn, $bill_id, $student_id, $days_stayed) {
    $bill_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT month_year, total_days_in_month FROM hostel_bills WHERE id='$bill_id'"));
    $total_days = $bill_info['total_days_in_month'];
    if ($days_stayed < 1) $days_stayed = 1;
    if ($days_stayed > $total_days) $days_stayed = $total_days;
    $bill_data = calculateBillBreakdown($days_stayed, $total_days);
    $old_bill = mysqli_fetch_assoc(mysqli_query($conn, "SELECT amount FROM hostel_bills WHERE id='$bill_id'"));
    $amount_difference = $bill_data['total'] - $old_bill['amount'];
    mysqli_query($conn, "UPDATE hostel_bills SET amount = {$bill_data['total']}, discount_amount = {$bill_data['discount']}, days_stayed = $days_stayed WHERE id='$bill_id'");
    mysqli_query($conn, "UPDATE hostel_students SET total_due = total_due + $amount_difference WHERE id='$student_id'");
    return true;
}

function addSecurityDeposit($conn, $student_id, $university_id) {
    $check = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='$student_id' AND bill_type='security'");
    if (mysqli_num_rows($check) == 0) {
        $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT join_date FROM hostel_students WHERE id='$student_id'"));
        $join_date = $student['join_date'];
        if (empty($join_date) || $join_date == '0000-00-00') {
            $join_date = date('Y-m-d');
        }
        $month_year = date('M Y', strtotime($join_date));
        $due_date = date('Y-m-d', strtotime($join_date . ' +1 month'));
        
        $insert = "INSERT INTO hostel_bills (student_id, university_id, bill_type, amount, month_year, due_date, status, manual_late_fine) 
                    VALUES ('$student_id', '$university_id', 'security', 5000, '$month_year', '$due_date', 'unpaid', 0)";
        mysqli_query($conn, $insert);
        mysqli_query($conn, "UPDATE hostel_students SET total_due = total_due + 5000 WHERE id='$student_id'");
    }
}

function makePayment($conn, $bill_id, $student_id) {
    $bill_query = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE id='$bill_id' AND student_id='$student_id' AND status='unpaid'");
    if (mysqli_num_rows($bill_query) > 0) {
        $bill = mysqli_fetch_assoc($bill_query);
        mysqli_query($conn, "UPDATE hostel_bills SET status='paid', paid_date=NOW() WHERE id='$bill_id'");
        mysqli_query($conn, "UPDATE hostel_students SET total_due = total_due - {$bill['amount']} WHERE id='$student_id'");
        return true;
    }
    return false;
}

function removeLateFine($conn, $bill_id, $student_id) {
    $bill_query = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE id='$bill_id' AND late_fine > 0");
    if (mysqli_num_rows($bill_query) > 0) {
        $bill = mysqli_fetch_assoc($bill_query);
        $new_amount = $bill['amount'] - $bill['late_fine'];
        mysqli_query($conn, "UPDATE hostel_bills SET late_fine = 0, amount = $new_amount, manual_late_fine = 1 WHERE id='$bill_id'");
        mysqli_query($conn, "UPDATE hostel_students SET total_due = total_due - {$bill['late_fine']} WHERE id='$student_id'");
        return true;
    }
    return false;
}

function resetStudentPassword($conn, $student_id) {
    $new_password = '123456';
    $hashed_password = md5($new_password);
    $query = mysqli_query($conn, "UPDATE hostel_students SET password = '$hashed_password' WHERE id = '$student_id'");
    if ($query) {
        return true;
    }
    return false;
}

function isSeatAvailable($conn, $hostel_name, $floor_no, $room_no, $seat_no, $exclude_id = 0) {
    $exclude = $exclude_id > 0 ? "AND id != $exclude_id" : "";
    $query = "SELECT * FROM hostel_students WHERE hostel_name='$hostel_name' AND floor_no='$floor_no' AND room_no='$room_no' AND seat_no='$seat_no' $exclude";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) == 0;
}

function getBookedSeats($conn, $hostel_name, $floor_no, $room_no) {
    $query = "SELECT seat_no FROM hostel_students WHERE hostel_name='$hostel_name' AND floor_no='$floor_no' AND room_no='$room_no'";
    $result = mysqli_query($conn, $query);
    $booked = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $booked[] = $row['seat_no'];
        }
    }
    return $booked;
}

function getMealStatus($conn, $student_id, $date) {
    $query = mysqli_query($conn, "SELECT status FROM meal_tracking WHERE student_id='$student_id' AND meal_date='$date'");
    if ($query && mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        return $row['status'];
    }
    return 'not_taken';
}

function getTodayMealCount($conn, $date) {
    $query = mysqli_query($conn, "SELECT COUNT(*) as count FROM meal_tracking WHERE meal_date='$date' AND status='taken'");
    $row = mysqli_fetch_assoc($query);
    return $row['count'];
}

function getStudentMealCount($conn, $student_id, $month, $year) {
    $start = "$year-$month-01";
    $end = "$year-$month-" . date('t', strtotime($start));
    $query = mysqli_query($conn, "SELECT COUNT(*) as count FROM meal_tracking WHERE student_id='$student_id' AND status='taken' AND meal_date BETWEEN '$start' AND '$end'");
    $row = mysqli_fetch_assoc($query);
    return $row['count'];
}

function getAllDatesOfMonth($month, $year) {
    $days = date('t', strtotime("$year-$month-01"));
    $dates = [];
    for ($i = 1; $i <= $days; $i++) {
        $dates[] = "$year-$month-" . str_pad($i, 2, '0', STR_PAD_LEFT);
    }
    return $dates;
}

$logged_in = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
$error = "";
$msg = "";
$delete_msg = "";
$restore_msg = "";
$payment_msg = "";
$update_msg = "";
$backup_delete_msg = "";
$reset_msg = "";

if (isset($_POST['admin_login'])) {
    $id = mysqli_real_escape_string($conn, $_POST['admin_id']);
    $pw = md5($_POST['password']);
    $q = mysqli_query($conn, "SELECT * FROM hostel_admin WHERE username='$id' AND password='$pw'");
    if (mysqli_num_rows($q) == 1) {
        $_SESSION['admin'] = true;
        $_SESSION['admin_name'] = $id;
        $logged_in = true;
        generateMonthlyBills($conn);
        header("Location: admin.php");
        exit();
    } else {
        $error = "Wrong Admin ID or Password";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

if (isset($_POST['update_days']) && $logged_in) {
    $bill_id = (int)$_POST['bill_id'];
    $student_id = (int)$_POST['student_id'];
    $days_stayed = (int)$_POST['days_stayed'];
    if (updateBillDays($conn, $bill_id, $student_id, $days_stayed)) {
        $update_msg = "✅ Bill updated successfully for $days_stayed days!";
        header("Location: admin.php?page=bills&student_id=$student_id");
        exit();
    }
}

if (isset($_GET['remove_late_fine']) && $logged_in) {
    $bill_id = (int)$_GET['remove_late_fine'];
    $student_id = (int)$_GET['student_id'];
    if (removeLateFine($conn, $bill_id, $student_id)) {
        $payment_msg = "✅ Late fine removed successfully!";
        header("Location: admin.php?page=bills&student_id=$student_id");
        exit();
    } else {
        $payment_msg = "❌ Failed to remove late fine!";
    }
}

if (isset($_GET['reset_password']) && $logged_in) {
    $student_id = (int)$_GET['reset_password'];
    $student_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM hostel_students WHERE id='$student_id'"));
    if ($student_info) {
        if (resetStudentPassword($conn, $student_id)) {
            $reset_msg = "✅ Password reset successful for " . htmlspecialchars($student_info['name']) . "! New password: 123456";
        } else {
            $reset_msg = "❌ Failed to reset password!";
        }
    } else {
        $reset_msg = "❌ Student not found!";
    }
    header("Location: admin.php?page=students&reset_msg=" . urlencode($reset_msg));
    exit();
}

if (isset($_GET['delete_id']) && $logged_in) {
    $delete_id = (int)$_GET['delete_id'];
    $student_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hostel_students WHERE id='$delete_id'"));
    if ($student_data) {
        mysqli_query($conn, "INSERT INTO hostel_students_backup (id, university_id, name, department, father_name, mother_name, dob, blood_group, email, phone, present_address, permanent_address, guardian_name, guardian_phone, hostel_name, floor_no, room_no, seat_no, student_photo, id_card_photo, password, total_due, join_date, deleted_date) SELECT id, university_id, name, department, father_name, mother_name, dob, blood_group, email, phone, present_address, permanent_address, guardian_name, guardian_phone, hostel_name, floor_no, room_no, seat_no, student_photo, id_card_photo, password, total_due, join_date, NOW() FROM hostel_students WHERE id='$delete_id'");
        
        $bills_to_backup = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='$delete_id'");
        while ($bill = mysqli_fetch_assoc($bills_to_backup)) {
            mysqli_query($conn, "INSERT INTO hostel_bills_backup (student_id, university_id, bill_type, amount, month_year, due_date, paid_date, status, late_fine, discount_amount, days_stayed, total_days_in_month, manual_late_fine, created_at, deleted_date) VALUES ('{$bill['student_id']}', '{$bill['university_id']}', '{$bill['bill_type']}', '{$bill['amount']}', '{$bill['month_year']}', '{$bill['due_date']}', '{$bill['paid_date']}', '{$bill['status']}', '{$bill['late_fine']}', '{$bill['discount_amount']}', '{$bill['days_stayed']}', '{$bill['total_days_in_month']}', '{$bill['manual_late_fine']}', '{$bill['created_at']}', NOW())");
        }
        
        if (!is_dir("backups")) mkdir("backups");
        $backup_file = "backups/student_{$student_data['university_id']}_{$student_data['id']}_" . date('Y-m-d_H-i-s') . ".json";
        file_put_contents($backup_file, json_encode($student_data, JSON_PRETTY_PRINT));
        mysqli_query($conn, "DELETE FROM hostel_bills WHERE student_id='$delete_id'");
        mysqli_query($conn, "DELETE FROM hostel_students WHERE id='$delete_id'");
        $delete_msg = "✅ Student deleted successfully!";
    }
}

if (isset($_GET['restore_id']) && $logged_in) {
    $restore_id = (int)$_GET['restore_id'];
    $backup_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hostel_students_backup WHERE id='$restore_id'"));
    if ($backup_data) {
        $backup_id = $backup_data['id'];
        unset($backup_data['id']);
        unset($backup_data['deleted_date']);
        $columns = implode(", ", array_keys($backup_data));
        $values = "'" . implode("', '", array_map(function($val) use ($conn) {
            return mysqli_real_escape_string($conn, $val);
        }, array_values($backup_data))) . "'";
        mysqli_query($conn, "INSERT INTO hostel_students ($columns) VALUES ($values)");
        
        $restored_student_id = mysqli_insert_id($conn);
        $total_due_calc = 0;
        
        $bills_backup = mysqli_query($conn, "SELECT * FROM hostel_bills_backup WHERE student_id='$backup_id'");
        while ($bill = mysqli_fetch_assoc($bills_backup)) {
            mysqli_query($conn, "INSERT INTO hostel_bills (student_id, university_id, bill_type, amount, month_year, due_date, paid_date, status, late_fine, discount_amount, days_stayed, total_days_in_month, manual_late_fine, created_at) VALUES ('$restored_student_id', '{$bill['university_id']}', '{$bill['bill_type']}', '{$bill['amount']}', '{$bill['month_year']}', '{$bill['due_date']}', '{$bill['paid_date']}', '{$bill['status']}', '{$bill['late_fine']}', '{$bill['discount_amount']}', '{$bill['days_stayed']}', '{$bill['total_days_in_month']}', '{$bill['manual_late_fine']}', '{$bill['created_at']}')");
            
            if ($bill['status'] == 'unpaid') {
                $total_due_calc += $bill['amount'];
            }
        }
        
        mysqli_query($conn, "UPDATE hostel_students SET total_due = $total_due_calc WHERE id='$restored_student_id'");
        
        $restore_msg = "✅ Student restored successfully with all bill records!";
        header("Location: admin.php?page=backup");
        exit();
    }
}

if (isset($_POST['delete_selected_backup']) && $logged_in) {
    if (isset($_POST['selected_backups']) && is_array($_POST['selected_backups'])) {
        $selected_ids = array_map('intval', $_POST['selected_backups']);
        $ids_string = implode(",", $selected_ids);
        $backups_to_delete = mysqli_query($conn, "SELECT * FROM hostel_students_backup WHERE id IN ($ids_string)");
        while ($backup = mysqli_fetch_assoc($backups_to_delete)) {
            $json_file = "backups/student_{$backup['university_id']}_{$backup['id']}_*.json";
            foreach (glob($json_file) as $file) {
                if (file_exists($file)) unlink($file);
            }
            mysqli_query($conn, "DELETE FROM hostel_bills_backup WHERE student_id='{$backup['id']}'");
        }
        mysqli_query($conn, "DELETE FROM hostel_students_backup WHERE id IN ($ids_string)");
        $backup_delete_msg = "✅ Selected backup records deleted successfully from database!";
        header("Location: admin.php?page=backup");
        exit();
    } else {
        $backup_delete_msg = "⚠️ Please select at least one backup record to delete.";
    }
}

if (isset($_GET['delete_backup_id']) && $logged_in) {
    $delete_backup_id = (int)$_GET['delete_backup_id'];
    $backup_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hostel_students_backup WHERE id='$delete_backup_id'"));
    if ($backup_data) {
        $json_file = "backups/student_{$backup_data['university_id']}_{$backup_data['id']}_*.json";
        foreach (glob($json_file) as $file) {
            if (file_exists($file)) unlink($file);
        }
        mysqli_query($conn, "DELETE FROM hostel_bills_backup WHERE student_id='{$backup_data['id']}'");
        mysqli_query($conn, "DELETE FROM hostel_students_backup WHERE id='$delete_backup_id'");
        $backup_delete_msg = "✅ Backup record deleted permanently from database!";
    }
    header("Location: admin.php?page=backup");
    exit();
}

if (isset($_POST['pay_bill']) && $logged_in) {
    $bill_id = (int)$_POST['bill_id'];
    $student_id = (int)$_POST['student_id'];
    if (makePayment($conn, $bill_id, $student_id)) {
        $payment_msg = "✅ Payment successful!";
        header("Location: admin.php?page=bills&student_id=$student_id");
        exit();
    }
}

if (isset($_POST['register']) && $logged_in) {
    if (!is_dir("uploads")) mkdir("uploads");
    $photo = "uploads/" . time() . "_" . basename($_FILES['photo']['name']);
    $idcard = "uploads/" . time() . "_" . basename($_FILES['idcard']['name']);
    move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    move_uploaded_file($_FILES['idcard']['tmp_name'], $idcard);

    $university_id = mysqli_real_escape_string($conn, $_POST['university_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $father = mysqli_real_escape_string($conn, $_POST['father']);
    $mother = mysqli_real_escape_string($conn, $_POST['mother']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $blood = mysqli_real_escape_string($conn, $_POST['blood']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $present = mysqli_real_escape_string($conn, $_POST['present']);
    $permanent = mysqli_real_escape_string($conn, $_POST['permanent']);
    $guardian = mysqli_real_escape_string($conn, $_POST['guardian']);
    $guardian_phone = mysqli_real_escape_string($conn, $_POST['guardian_phone']);
    $hostel_name = mysqli_real_escape_string($conn, $_POST['hostel_name']);
    $floor_no = (int)$_POST['floor_no'];
    $room_no = (int)$_POST['room_no'];
    $seat_no = (int)$_POST['seat_no'];
    $student_password = md5($_POST['student_password']);
    $join_date = date('Y-m-d');

    if (isSeatAvailable($conn, $hostel_name, $floor_no, $room_no, $seat_no)) {
        $insert = "INSERT INTO hostel_students (
            university_id, name, department, father_name, mother_name, dob, blood_group,
            email, phone, present_address, permanent_address, guardian_name,
            guardian_phone, hostel_name, floor_no, room_no, seat_no, student_photo, id_card_photo, password, total_due, join_date
        ) VALUES (
            '$university_id', '$name', '$department', '$father', '$mother', '$dob', '$blood',
            '$email', '$phone', '$present', '$permanent', '$guardian',
            '$guardian_phone', '$hostel_name', '$floor_no', '$room_no', '$seat_no', '$photo', '$idcard', '$student_password', 0, '$join_date'
        )";
        if (mysqli_query($conn, $insert)) {
            $new_id = mysqli_insert_id($conn);
            addSecurityDeposit($conn, $new_id, $university_id);
            
            $current_month = date('Y-m');
            $due_date = date('Y-m-20');
            $total_days = date('t');
            $bill_data = calculateBillBreakdown($total_days, $total_days);
            
            $check_monthly = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='$new_id' AND month_year='$current_month' AND bill_type='monthly'");
            if (mysqli_num_rows($check_monthly) == 0) {
                mysqli_query($conn, "INSERT INTO hostel_bills (student_id, university_id, bill_type, amount, month_year, due_date, status, discount_amount, days_stayed, total_days_in_month, late_fine, manual_late_fine) 
                                      VALUES ('$new_id', '$university_id', 'monthly', {$bill_data['total']}, '$current_month', '$due_date', 'unpaid', {$bill_data['discount']}, $total_days, $total_days, 0, 0)");
                mysqli_query($conn, "UPDATE hostel_students SET total_due = total_due + {$bill_data['total']} WHERE id='$new_id'");
            }
            $msg = "✅ Hostel Admission Successful! Security Deposit (5,000 TK) and current month bill (7,500 TK) added.";
        } else {
            $msg = "❌ Error: " . mysqli_error($conn);
        }
    } else {
        $msg = "❌ Error: This seat is already booked!";
    }
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$selected_hostel = isset($_GET['hostel']) ? mysqli_real_escape_string($conn, $_GET['hostel']) : 'Hostel_A';

if (isset($_GET['reset_msg'])) {
    $reset_msg = urldecode($_GET['reset_msg']);
}

if ($logged_in) {
    generateMonthlyBills($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>GUB Hostel Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }

        .login-box {
            width: 420px; margin: 70px auto; background: #ffffff; padding: 28px;
            border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        .login-box h2 { color: #2e7d32; text-align: center; }
        .login-box input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #cfd8dc; border-radius: 6px; }
        .login-box button { width: 100%; padding: 12px; background: #2e7d32; color: white; border: none; border-radius: 6px; cursor: pointer; }
        .error { color: red; text-align: center; margin-top: 10px; }
        .success { color: green; text-align: center; margin-top: 10px; }

        .portal-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px; background: #1a3c2c; color: white; position: fixed;
            height: 100vh; overflow-y: auto; box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: center; }
        .sidebar-header h2 { font-size: 20px; margin-bottom: 5px; }
        .admin-info { background: rgba(255,255,255,0.1); padding: 12px; margin: 15px; border-radius: 8px; text-align: center; }
        .admin-info strong { display: block; margin-top: 5px; color: #ffc107; }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu li a {
            display: flex; align-items: center; padding: 12px 20px; color: #e0e0e0;
            text-decoration: none; border-left: 3px solid transparent;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: #ffc107; }
        .sidebar-menu li.logout-item { margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 10px; }

        .main-content { flex: 1; margin-left: 280px; padding: 30px; background: #f5f7fa; min-height: 100vh; }
        .welcome-header { background: white; padding: 20px 25px; border-radius: 12px; margin-bottom: 30px; border-left: 5px solid #2e7d32; }
        .form-page { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-page h2 { color: #1a3c2c; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; }
        .row { margin-bottom: 15px; display: flex; flex-wrap: wrap; align-items: center; }
        .label { width: 180px; font-weight: 600; color: #333; }
        .row input, .row textarea, .row select { flex: 1; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .form-page button { background: #2e7d32; color: white; border: none; padding: 12px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 15px; }

        .hostel-tabs { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .hostel-tab { padding: 10px 25px; background: #e0e0e0; border-radius: 8px; text-decoration: none; color: #333; font-weight: bold; }
        .hostel-tab.active { background: #2e7d32; color: white; }
        .floors-container { display: flex; flex-direction: column; gap: 30px; }
        .floor-card { background: #f9f9f9; border-radius: 12px; padding: 20px; }
        .floor-title { font-size: 20px; font-weight: bold; color: #1a3c2c; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #2e7d32; }
        .rooms-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .room-card { background: white; border-radius: 10px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); min-width: 200px; }
        .room-title { font-size: 18px; font-weight: bold; color: #1565c0; margin-bottom: 10px; }
        .seats-grid { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .seat { width: 65px; height: 65px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 8px; font-weight: bold; font-size: 11px; text-align: center; cursor: pointer; }
        .seat-available { background: #ffcdd2; color: #c62828; border: 2px solid #ef9a9a; }
        .seat-booked { background: #c8e6c9; color: #2e7d32; border: 2px solid #a5d6a7; cursor: default; }
        .seat-available:hover { transform: scale(1.05); }

        .student-table { width: 100%; border-collapse: collapse; border-radius: 8px; overflow: hidden; }
        .student-table th, .student-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        .student-table th { background: #1a3c2c; color: white; }
        
        .student-table td:last-child {
            text-align: center;
            padding: 8px 5px;
        }
        
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-box input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .search-box button { padding: 10px 20px; background: #2e7d32; color: white; border: none; border-radius: 8px; cursor: pointer; }
        .dashboard-cards { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; flex: 1; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .card h3 { font-size: 36px; color: #2e7d32; }
        .student-detail { background: #fafafa; padding: 20px; border-radius: 8px; }
        .student-detail img { max-width: 200px; margin-top: 10px; border-radius: 5px; }
        .back-link { display: inline-block; margin-top: 20px; color: #2e7d32; text-decoration: none; }
        
        .bill-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            font-size: 12px; 
        }
        .bill-table th, .bill-table td { 
            padding: 6px 6px; 
            text-align: left; 
            border-bottom: 1px solid #eee; 
        }
        .bill-table th { 
            background: #1565c0; 
            color: white; 
            font-size: 12px; 
            font-weight: 500;
            white-space: nowrap;
        }
        .bill-table td {
            white-space: nowrap;
        }
        
        .status-paid { background: #c8e6c9; color: #2e7d32; padding: 2px 6px; border-radius: 20px; font-size: 10px; display: inline-block; }
        .status-unpaid { background: #ffcdd2; color: #c62828; padding: 2px 6px; border-radius: 20px; font-size: 10px; display: inline-block; }
        
        button.pay-btn, 
        button.update-btn,
        a.view-btn, 
        a.bills-btn,
        a.meal-btn,
        .remove-fine-btn,
        .action-delete,
        .reset-pwd-btn {
            all: unset !important;
        }

        button.pay-btn, 
        button.update-btn,
        a.view-btn, 
        a.bills-btn,
        a.meal-btn,
        .remove-fine-btn,
        .action-delete,
        .reset-pwd-btn {
            background: #2e7d32 !important;
            color: white !important;
            padding: 4px 10px !important;
            border-radius: 4px !important;
            font-size: 11px !important;
            display: inline-block !important;
            text-align: center !important;
            cursor: pointer !important;
            margin: 0 2px !important;
            width: auto !important;
            min-width: 45px !important;
            border: none !important;
            text-decoration: none !important;
        }

        .reset-pwd-btn {
            background: #ff9800 !important;
        }
        .reset-pwd-btn:hover {
            background: #f57c00 !important;
        }

        a.meal-btn {
            background: #ff9800 !important;
        }
        a.meal-btn:hover {
            background: #f57c00 !important;
        }

        .remove-fine-btn {
            background: #ff9800 !important;
        }

        .remove-fine-btn:hover {
            background: #f57c00 !important;
        }

        .action-delete {
            background: #d32f2f !important;
        }

        .action-delete:hover {
            background: #b71c1c !important;
        }

        button.pay-btn:hover, 
        button.update-btn:hover,
        a.view-btn:hover, 
        a.bills-btn:hover {
            background: #1a3c2c !important;
        }
        
        .total-due { font-size: 20px; font-weight: bold; color: #c62828; }
        .days-input { width: 45px; padding: 2px 3px; border-radius: 3px; border: 1px solid #ddd; text-align: center; font-size: 10px; }
        
        .financial-summary {
            background: linear-gradient(135deg, #1a3c2c 0%, #2e7d32 100%);
            border-radius: 12px;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: white;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 10px;
        }
        .summary-item {
            background: rgba(255,255,255,0.12);
            border-radius: 6px;
            padding: 6px 10px;
            text-align: center;
        }
        .summary-item h4 { font-size: 10px; opacity: 0.8; margin-bottom: 3px; }
        .summary-item .amount { font-size: 13px; font-weight: bold; }
        .summary-item .amount.positive { color: #c8e6c9; }
        .summary-item .amount.negative { color: #ffcdd2; }
        .summary-item .amount.warning { color: #ffeb3b; }
        .summary-item small { font-size: 8px; opacity: 0.7; display: block; margin-top: 2px; }
        
        .student-info-card {
            background: white;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 15px;
            border-left: 4px solid #2e7d32;
            font-size: 12px;
        }
        .student-info-card h3 { font-size: 15px; margin-bottom: 3px; }
        .student-info-card p { color: #666; margin: 2px 0; font-size: 11px; }
        
        .info-box { background: #e3f2fd; padding: 10px 12px; border-radius: 8px; margin-top: 12px; font-size: 11px; }
        
        .checkbox-col { width: 40px; text-align: center; }
        .action-buttons { 
            text-align: center; 
            margin-bottom: 25px; 
            padding: 10px 0;
        }
        .select-all-btn { 
            background: #1565c0; 
            color: white; 
            border: none; 
            padding: 8px 20px; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 13px;
            display: inline-block;
            margin-right: 15px;
        }
        .select-all-btn:hover { 
            background: #0d47a1; 
        }
        .delete-selected-btn { 
            background: #d32f2f; 
            color: white; 
            border: none; 
            padding: 8px 20px; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 13px;
            display: inline-block;
        }
        .delete-selected-btn:hover { 
            background: #b71c1c; 
        }
        .action-buttons form { 
            display: inline-block; 
            margin: 0;
        }
        
        .search-bill-box {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .search-bill-box .search-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .search-bill-box .search-group label {
            font-size: 11px;
            font-weight: bold;
            color: #555;
        }
        .search-bill-box .search-group input, 
        .search-bill-box .search-group select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .search-bill-box button {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .search-bill-box button:hover {
            background: #1a3c2c;
        }
        .search-bill-box .reset-btn {
            background: #666;
        }
        .search-bill-box .reset-btn:hover {
            background: #444;
        }
        
        .meal-summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .meal-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            flex: 1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .meal-card h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .meal-card .count { font-size: 36px; font-weight: bold; color: #2e7d32; }
        .meal-card.taken .count { color: #2e7d32; }
        .meal-card.not-taken .count { color: #c62828; }
        .meal-card.total .count { color: #1565c0; }
        
        .meal-status-taken { background: #c8e6c9; color: #2e7d32; padding: 3px 8px; border-radius: 20px; font-size: 11px; display: inline-block; }
        .meal-status-not { background: #ffcdd2; color: #c62828; padding: 3px 8px; border-radius: 20px; font-size: 11px; display: inline-block; }
        
        .search-meal-box {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .search-meal-box .search-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .search-meal-box .search-group label {
            font-size: 11px;
            font-weight: bold;
            color: #555;
        }
        .search-meal-box .search-group input {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .search-meal-box button {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .meal-history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 12px;
        }
        .meal-history-table th, .meal-history-table td {
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .meal-history-table th {
            background: #1565c0;
            color: white;
            font-size: 12px;
        }
        
        @media (max-width: 768px) { 
            .sidebar { width: 240px; } 
            .main-content { margin-left: 240px; padding: 20px; } 
            .label { width: 140px; }
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
            .select-all-btn, .delete-selected-btn { padding: 8px 18px; font-size: 13px; }
            .student-table th, .student-table td { padding: 8px 10px; font-size: 12px; }
            .bill-table th, .bill-table td { padding: 4px 4px; font-size: 10px; }
            .bill-table td { white-space: normal; }
            .search-bill-box { flex-direction: column; align-items: stretch; }
            .meal-summary-cards { flex-direction: column; }
        }
    </style>
    <script>
        function confirmDelete(name) { return confirm("⚠️ Delete " + name + "?\n\nThis will move the student to backup."); }
        function confirmRestore(name) { return confirm("♻️ Restore " + name + "?"); }
        function confirmBackupDelete(name) { return confirm("⚠️ PERMANENTLY DELETE " + name + " from backup?\n\nThis action cannot be undone! The student will be gone forever from database."); }
        function confirmReset(name) { return confirm("🔐 Reset password for " + name + "?\n\nNew password will be: 123456"); }
        function selectSeat(hostel, floor, room, seat) {
            window.location.href = "admin.php?page=form&hostel=" + hostel + "&floor=" + floor + "&room=" + room + "&seat=" + seat;
        }
        function confirmRemoveFine() {
            return confirm("⚠️ Remove 500 TK late fine from this bill?");
        }
        function toggleAllCheckboxes(source) {
            var checkboxes = document.getElementsByName('selected_backups[]');
            for(var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
        function confirmDeleteSelected() {
            var checkboxes = document.getElementsByName('selected_backups[]');
            var selectedCount = 0;
            for(var i = 0; i < checkboxes.length; i++) {
                if(checkboxes[i].checked) selectedCount++;
            }
            if(selectedCount === 0) {
                alert("Please select at least one backup record to delete.");
                return false;
            }
            return confirm("⚠️ PERMANENTLY DELETE " + selectedCount + " selected backup record(s)?\n\nThis action cannot be undone! These records will be removed from database forever.");
        }
    </script>
</head>
<body>

<?php if (!$logged_in) { ?>
    <div class="login-box">
        <h2>🏠 GUB Hostel Management</h2>
        <form method="post">
            <input name="admin_id" placeholder="Admin ID" required>
            <input type="password" name="password" placeholder="Password" required>
            <button name="admin_login">LOGIN</button>
        </form>
        <?php if (isset($error) && $error != "") echo "<p class='error'>$error</p>"; ?>
    </div>
<?php } else { ?>
    <div class="portal-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>🏢 Green University<br>of Bangladesh</h2>
                <p>Hostel Management</p>
            </div>
            <div class="admin-info">
                <span>👤 Logged in as</span>
                <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin.php?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">🏠 Home</a></li>
                <li><a href="admin.php?page=form" class="<?php echo $page == 'form' ? 'active' : ''; ?>">📝 Admission Form</a></li>
                <li><a href="admin.php?page=rooms" class="<?php echo $page == 'rooms' ? 'active' : ''; ?>">🏘️ Hostel Rooms</a></li>
                <li><a href="admin.php?page=students" class="<?php echo $page == 'students' ? 'active' : ''; ?>">👨‍🎓 Student Information</a></li>
                <li><a href="admin.php?page=bills" class="<?php echo $page == 'bills' ? 'active' : ''; ?>">💰 Bill History</a></li>
                <li><a href="admin.php?page=daily_meal" class="<?php echo $page == 'daily_meal' ? 'active' : ''; ?>">🍽️ Daily Meal</a></li>
                <li><a href="admin.php?page=backup" class="<?php echo $page == 'backup' ? 'active' : ''; ?>">💾 Backup & Restore</a></li>
                <li class="logout-item"><a href="admin.php?logout=1">🚪 Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="welcome-header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h1>
                <p>GUB Hostel Management System - Manage hostels, rooms, seats, and billing.</p>
            </div>

            <?php
            if ($msg != "") echo "<div class='form-page'><p class='success'>$msg</p></div>";
            if ($delete_msg != "") echo "<div class='form-page'><p class='success'>$delete_msg</p></div>";
            if ($restore_msg != "") echo "<div class='form-page'><p class='success'>$restore_msg</p></div>";
            if ($payment_msg != "") echo "<div class='form-page'><p class='success'>$payment_msg</p></div>";
            if ($update_msg != "") echo "<div class='form-page'><p class='success'>$update_msg</p></div>";
            if ($backup_delete_msg != "") echo "<div class='form-page'><p class='success'>$backup_delete_msg</p></div>";
            if ($reset_msg != "") echo "<div class='form-page'><p class='success'>$reset_msg</p></div>";
           
            if ($page == 'dashboard') {
                $totalStudents = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM hostel_students"));
                $totalDue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_due) as due FROM hostel_students"))['due'];
                $totalBackup = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM hostel_students_backup"));
                $hostels = ['Hostel_A', 'Hostel_B', 'Hostel_C', 'Hostel_D'];
                $hostel_counts = [];
                foreach ($hostels as $h) {
                    $res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM hostel_students WHERE hostel_name='$h'");
                    $row = mysqli_fetch_assoc($res);
                    $hostel_counts[$h] = $row ? $row['cnt'] : 0;
                }
                ?>
                <div class="form-page">
                    <h2>Dashboard Overview</h2>
                    <div class="dashboard-cards">
                        <div class="card"><h3><?php echo $totalStudents; ?></h3><p>Total Students</p><a href="admin.php?page=students">View List →</a></div>
                        <div class="card"><h3><?php echo $totalDue ? number_format($totalDue, 2) : '0'; ?> TK</h3><p>Total Due Amount</p><a href="admin.php?page=bills">View Bills →</a></div>
                        <div class="card"><h3><?php echo $totalBackup; ?></h3><p>Backup Records</p><a href="admin.php?page=backup">View & Manage →</a></div>
                    </div>
                    <div style="margin-top: 20px;">
                        <h3>Hostel wise Student Distribution</h3>
                        <div class="dashboard-cards">
                        <?php foreach ($hostels as $h) { ?>
                        <div class="card"><h3><?php echo $hostel_counts[$h]; ?></h3><p><?php echo str_replace('_', ' ', $h); ?></p></div>
                        <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            elseif ($page == 'form') {
                $pre_hostel = isset($_GET['hostel']) ? $_GET['hostel'] : '';
                $pre_floor = isset($_GET['floor']) ? $_GET['floor'] : '';
                $pre_room = isset($_GET['room']) ? $_GET['room'] : '';
                $pre_seat = isset($_GET['seat']) ? $_GET['seat'] : '';
            ?>
                <div class="form-page">
                    <h2>📝 Hostel Admission Form</h2>
                    <form method="post" enctype="multipart/form-data">
                        <div class="row"><span class="label">University ID :</span><input name="university_id" required></div>
                        <div class="row"><span class="label">Name :</span><input name="name" required></div>
                        <div class="row"><span class="label">Department :</span><input name="department" required></div>
                        <div class="row"><span class="label">Father :</span><input name="father" required></div>
                        <div class="row"><span class="label">Mother :</span><input name="mother" required></div>
                        <div class="row"><span class="label">DOB :</span><input type="date" name="dob" required></div>
                        <div class="row"><span class="label">Blood Group :</span><input name="blood"></div>
                        <div class="row"><span class="label">Email :</span><input type="email" name="email" required></div>
                        <div class="row"><span class="label">Phone :</span><input name="phone" required></div>
                        <div class="row"><span class="label">Present Address :</span><textarea name="present" rows="2"></textarea></div>
                        <div class="row"><span class="label">Permanent Address :</span><textarea name="permanent" rows="2"></textarea></div>
                        <div class="row"><span class="label">Guardian :</span><input name="guardian" required></div>
                        <div class="row"><span class="label">Guardian Phone :</span><input name="guardian_phone" required></div>
                       
                        <div class="row"><span class="label">Select Hostel :</span>
                            <select name="hostel_name" required>
                                <option value="">Select Hostel</option>
                                <option value="Hostel_A" <?php echo $pre_hostel == 'Hostel_A' ? 'selected' : ''; ?>>🏠 Hostel A</option>
                                <option value="Hostel_B" <?php echo $pre_hostel == 'Hostel_B' ? 'selected' : ''; ?>>🏠 Hostel B</option>
                                <option value="Hostel_C" <?php echo $pre_hostel == 'Hostel_C' ? 'selected' : ''; ?>>🏠 Hostel C</option>
                                <option value="Hostel_D" <?php echo $pre_hostel == 'Hostel_D' ? 'selected' : ''; ?>>🏠 Hostel D</option>
                            </select>
                        </div>
                        <div class="row"><span class="label">Floor :</span>
                            <select name="floor_no" required>
                                <option value="">Select Floor</option>
                                <?php for($i=1; $i<=5; $i++) {
                                    $selected = ($pre_floor == $i) ? 'selected' : '';
                                    echo "<option value='$i' $selected>Floor $i</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="row"><span class="label">Room No :</span>
                            <select name="room_no" required>
                                <option value="">Select Room</option>
                                <?php for($i=1; $i<=4; $i++) {
                                    $selected = ($pre_room == $i) ? 'selected' : '';
                                    echo "<option value='$i' $selected>Room $i</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="row"><span class="label">Seat No :</span>
                            <select name="seat_no" required>
                                <option value="">Select Seat</option>
                                <?php for($i=1; $i<=3; $i++) {
                                    $selected = ($pre_seat == $i) ? 'selected' : '';
                                    echo "<option value='$i' $selected>Seat $i</option>";
                                } ?>
                            </select>
                        </div>
                       
                        <div class="row"><span class="label">Student Photo :</span><input type="file" name="photo" accept="image/*" required></div>
                        <div class="row"><span class="label">ID Card Photo :</span><input type="file" name="idcard" accept="image/*" required></div>
                        <div class="row"><span class="label">Password :</span><input type="password" name="student_password" required></div>
                        <button name="register">SUBMIT ADMISSION</button>
                    </form>
                    <?php if (isset($msg) && $msg != "") echo "<p class='success'>$msg</p>"; ?>
                </div>
            <?php
            }
            elseif ($page == 'rooms') {
                $hostels = ['Hostel_A', 'Hostel_B', 'Hostel_C', 'Hostel_D'];
                $hostel_names = ['Hostel_A' => '🏠 Hostel A', 'Hostel_B' => '🏠 Hostel B', 'Hostel_C' => '🏠 Hostel C', 'Hostel_D' => '🏠 Hostel D'];
            ?>
                <div class="form-page">
                    <h2>🏘️ Hostel Room & Seat Management</h2>
                    <div class="hostel-tabs">
                        <?php foreach($hostels as $h): ?>
                            <a href="admin.php?page=rooms&hostel=<?php echo $h; ?>" class="hostel-tab <?php echo $selected_hostel == $h ? 'active' : ''; ?>">
                                <?php echo $hostel_names[$h]; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="floors-container">
                        <?php for($floor = 1; $floor <= 5; $floor++): ?>
                            <div class="floor-card">
                                <div class="floor-title">📍 Floor <?php echo $floor; ?></div>
                                <div class="rooms-grid">
                                    <?php for($room = 1; $room <= 4; $room++):
                                        $bookedSeats = getBookedSeats($conn, $selected_hostel, $floor, $room);
                                    ?>
                                        <div class="room-card">
                                            <div class="room-title">🚪 Room <?php echo $room; ?></div>
                                            <div class="seats-grid">
                                                <?php for($seat = 1; $seat <= 3; $seat++):
                                                    $isBooked = in_array((string)$seat, $bookedSeats);
                                                    $statusClass = $isBooked ? 'seat-booked' : 'seat-available';
                                                    $statusText = $isBooked ? '📌 Booked' : '🟢 Available';
                                                    $onclick = $isBooked ? '' : "selectSeat('$selected_hostel', $floor, $room, $seat)";
                                                ?>
                                                    <div class="seat <?php echo $statusClass; ?>" <?php echo $onclick ? "onclick=\"$onclick\"" : ''; ?> title="Seat <?php echo $seat; ?> - <?php echo $statusText; ?>">
                                                        Seat <?php echo $seat; ?><br>
                                                        <small><?php echo $statusText; ?></small>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="info-box">
                        <p>💡 <strong>Legend:</strong> <span style="color:#c62828;">🔴 Red Seat</span> = Available | <span style="color:#2e7d32;">🟢 Green Seat</span> = Booked</p>
                        <p>Click on available (red) seat to go to admission form.</p>
                    </div>
                </div>
            <?php
            }
            elseif ($page == 'students') {
                $query = ($search != '') ? "SELECT * FROM hostel_students WHERE university_id LIKE '%$search%' OR name LIKE '%$search%' OR department LIKE '%$search%' OR phone LIKE '%$search%' ORDER BY id DESC" : "SELECT * FROM hostel_students ORDER BY id DESC";
                $res = mysqli_query($conn, $query);
                ?>
                <div class="form-page">
                    <h2>👨‍🎓 Student Information List</h2>
                    <div class="search-box">
                        <form method="get" style="display: flex; gap: 10px; width: 100%;">
                            <input type="hidden" name="page" value="students">
                            <input type="text" name="search" placeholder="🔍 Search by University ID, Name or Department..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit">Search</button>
                            <?php if ($search != '') { ?>
                                <a href="admin.php?page=students" style="background: #666; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Reset</a>
                            <?php } ?>
                        </form>
                    </div>
                    <table class="student-table">
                        <thead>
                            <tr><th>University ID</th><th>Name</th><th>Department</th><th>Hostel</th><th>Floor/Room/Seat</th><th>Due (TK)</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if($res && mysqli_num_rows($res) > 0) {
                                while ($row = mysqli_fetch_assoc($res)) {
                                    $hostel_display = isset($row['hostel_name']) ? str_replace('_', ' ', $row['hostel_name']) : 'Hostel A';
                                    $seat_display = "F" . ($row['floor_no'] ?? '1') . "/R" . ($row['room_no'] ?? '1') . "/S" . ($row['seat_no'] ?? '1');
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['university_id']); ?></div>
                                <td><?php echo htmlspecialchars($row['name']); ?></div>
                                <td><?php echo htmlspecialchars($row['department']); ?></div>
                                <td><?php echo $hostel_display; ?></div>
                                <td><?php echo $seat_display; ?></div>
                                <td><?php echo number_format($row['total_due'], 2); ?> TK</div>
                                <td>
                                    <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                                        <a href="admin.php?page=view&id=<?php echo $row['id']; ?>" class="view-btn">📄 View</a>
                                        <a href="admin.php?page=bills&student_id=<?php echo $row['id']; ?>" class="bills-btn">💰 Bills</a>
                                        <a href="admin.php?page=student_meal&id=<?php echo $row['id']; ?>" class="meal-btn">🍽️ Meal</a>
                                        <a href="admin.php?page=students&reset_password=<?php echo $row['id']; ?>" class="reset-pwd-btn" onclick="return confirmReset('<?php echo addslashes($row['name']); ?>')">🔐 Reset Pwd</a>
                                        <a href="admin.php?page=students&delete_id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirmDelete('<?php echo addslashes($row['name']); ?>')">🗑️ Delete</a>
                                    </div>
                                 </div>
                            </tr>
                            <?php } } else { ?>
                            <tr><td colspan="7" style="text-align:center; padding:40px;">❌ No students found</div></tr>
                            <?php } ?>
                        </tbody>
                    </div>
                </div>
            <?php
            }
            elseif ($page == 'bills') {
                $student_id_filter = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
                
                $search_paid_from = isset($_GET['paid_from']) ? $_GET['paid_from'] : '';
                $search_paid_to = isset($_GET['paid_to']) ? $_GET['paid_to'] : '';
                $search_month = isset($_GET['search_month']) ? $_GET['search_month'] : '';
                
                if ($student_id_filter > 0) {
                    $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hostel_students WHERE id='$student_id_filter'"));
                    $bills_query = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='$student_id_filter' AND bill_type='monthly' ORDER BY month_year DESC");
                    $security_query = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='$student_id_filter' AND bill_type='security'");
                    
                    $total_fee_without_discount = 0;
                    $total_discount_all = 0;
                    $total_late_fine_all = 0;
                    $total_paid = 0;
                    $security_amount = 0;
                    $security_paid = false;
                    $display_total_days_month = 30;
                    
                    $all_bills = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='$student_id_filter' AND bill_type='monthly'");
                    while ($bill = mysqli_fetch_assoc($all_bills)) {
                        $original_amount = 7500;
                        $total_fee_without_discount += $original_amount;
                        $total_discount_all += $bill['discount_amount'];
                        $total_late_fine_all += $bill['late_fine'];
                        if ($bill['status'] == 'paid') {
                            $total_paid += $bill['amount'];
                        }
                        if ($bill['total_days_in_month'] > 0) {
                            $display_total_days_month = $bill['total_days_in_month'];
                        }
                    }
                    
                    $sec_bills = mysqli_query($conn, "SELECT * FROM hostel_bills WHERE student_id='$student_id_filter' AND bill_type='security'");
                    while ($sec = mysqli_fetch_assoc($sec_bills)) {
                        $security_amount = $sec['amount'];
                        if ($sec['status'] == 'paid') {
                            $security_paid = true;
                        }
                    }
                    
                    $total_bill_after_discount = $total_fee_without_discount - $total_discount_all + $total_late_fine_all;
                    $total_due_calc = $total_bill_after_discount - $total_paid;
                    
                    if ($security_paid) {
                        $security_display = "0.00";
                        $advance_display = number_format($security_amount, 2) . " Advance";
                    } else {
                        $security_display = number_format($security_amount, 2);
                        $advance_display = "0.00";
                    }
                    ?>
                    <div class="form-page">
                        <h2>💰 Bill History - <?php echo htmlspecialchars($student['name']); ?></h2>
                        
                        <div class="student-info-card">
                            <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                            <p>📚 ID: <?php echo $student['university_id']; ?> | 🏠 <?php echo str_replace('_', ' ', $student['hostel_name']); ?> | 📍 F<?php echo $student['floor_no']; ?>/R<?php echo $student['room_no']; ?>/S<?php echo $student['seat_no']; ?></p>
                            <p>📅 Joined: <?php echo $student['join_date']; ?> | 📞 <?php echo $student['phone']; ?></p>
                        </div>
                        
                        <div class="financial-summary">
                            <div class="summary-grid">
                                <div class="summary-item"><h4>💰 Total Fee</h4><div class="amount positive"><?php echo number_format($total_fee_without_discount, 2); ?></div><small>7,500 TK/month</small></div>
                                <div class="summary-item"><h4>🎁 Discount</h4><div class="amount warning">- <?php echo number_format($total_discount_all, 2); ?></div><small>Based on days</small></div>
                                <div class="summary-item"><h4>⚠️ Late Fine</h4><div class="amount warning">+ <?php echo number_format($total_late_fine_all, 2); ?></div><small>500 TK/month</small></div>
                                <div class="summary-item"><h4>📋 Net Bill</h4><div class="amount positive"><?php echo number_format($total_bill_after_discount, 2); ?></div><small>After discount</small></div>
                                <div class="summary-item"><h4>🔒 Security</h4><div class="amount"><?php echo $security_display; ?></div><small>One time</small></div>
                                <div class="summary-item"><h4>💵 Paid</h4><div class="amount positive"><?php echo number_format($total_paid, 2); ?></div><small>Total received</small></div>
                                <div class="summary-item"><h4>⚖️ Balance</h4><div class="amount"><?php echo $advance_display; ?></div><small>Security Deposit</small></div>
                                <div class="summary-item">
                                    <h4>📌 Due Amount</h4>
                                    <div class="amount <?php echo $total_due_calc < 0 ? 'negative' : 'warning'; ?>">
                                        <?php echo number_format($total_due_calc, 2); ?>
                                    </div>
                                    <small>(+ due, - advance)</small>
                                </div>
                            </div>
                        </div>
                        
                        <a href="admin.php?page=bills" style="display: inline-block; margin-bottom: 15px; font-size: 12px;">← View All Bills</a>
                        
                        <?php if ($security_query && mysqli_num_rows($security_query) > 0): ?>
                        <h3 style="margin: 12px 0 6px; font-size: 14px;">🔒 Security Deposit</h3>
                        <table class="bill-table">
                            <thead>
                                <tr><th>Month</th><th>Bill Type</th><th>Amount (TK)</th><th>Due Date</th><th>Paid Date</th><th>Status</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php while ($sec = mysqli_fetch_assoc($security_query)) { 
                                    $due_date_formatted = date('Y-m-d', strtotime($sec['due_date']));
                                    $paid_date_formatted = $sec['paid_date'] ? date('Y-m-d', strtotime($sec['paid_date'])) : '0000-00-00';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sec['month_year']); ?></td>
                                    <td>🔒 Security Deposit</div>
                                    <td><?php echo number_format($sec['amount'], 2); ?></div>
                                    <td><?php echo $due_date_formatted; ?></div>
                                    <td><?php echo $paid_date_formatted; ?></div>
                                    <td><span class="<?php echo $sec['status'] == 'paid' ? 'status-paid' : 'status-unpaid'; ?>"><?php echo $sec['status'] == 'paid' ? '✅ Paid' : '⚠️ Unpaid'; ?></span></div>
                                    <td>
                                        <?php if ($sec['status'] == 'unpaid'): ?>
                                            <form method="post" style="margin:0; padding:0; display:inline-block;">
                                                <input type="hidden" name="bill_id" value="<?php echo $sec['id']; ?>">
                                                <input type="hidden" name="student_id" value="<?php echo $sec['student_id']; ?>">
                                                <button type="submit" name="pay_bill" class="pay-btn" style="background:#2e7d32; color:white; border:none; padding:4px 12px; border-radius:4px; cursor:pointer;">💰 Pay</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:green;">✓ Paid</span>
                                        <?php endif; ?>
                                     </div>
                                </table>
                                <?php } ?>
                            </tbody>
                        </div>
                        <?php endif; ?>
                        
                        <h3 style="margin: 15px 0 8px; font-size: 14px;">📅 Monthly Bill Details</h3>
                        <table class="bill-table">
                            <thead><tr><th>Month</th><th>Bill Type</th><th>Days Stayed</th><th>Amount (TK)</th><th>Discount</th><th>Late Fine</th><th>Due Date</th><th>Paid Date</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php if ($bills_query && mysqli_num_rows($bills_query) > 0) {
                                    while ($bill = mysqli_fetch_assoc($bills_query)) {
                                        $days = $bill['days_stayed'] ?? 30;
                                        $total_days_month = $bill['total_days_in_month'] ?? 30;
                                        if ($total_days_month <= 0) $total_days_month = 30;
                                        $discount = $bill['discount_amount'] ?? 0;
                                        $late_fine = $bill['late_fine'] ?? 0;
                                        $total = $bill['amount'];
                                        $month_display = date('M Y', strtotime($bill['month_year'] . '-01'));
                                        $due_date_formatted = date('Y-m-d', strtotime($bill['due_date']));
                                        $paid_date_formatted = $bill['paid_date'] ? date('Y-m-d', strtotime($bill['paid_date'])) : '0000-00-00';
                                ?>
                                <tr>
                                    <td><?php echo $month_display; ?></div>
                                    <td>Monthly Bill</div>
                                    <td><?php if ($bill['status'] == 'unpaid'): ?><form method="post" style="display: flex; gap: 3px; align-items: center; flex-wrap: wrap;"><input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>"><input type="hidden" name="student_id" value="<?php echo $bill['student_id']; ?>"><input type="number" class="days-input" name="days_stayed" value="<?php echo $days; ?>" min="1" max="<?php echo $total_days_month; ?>" step="1" required><button type="submit" name="update_days" class="update-btn">Up</button></form><?php else: echo $days . '/' . $total_days_month; endif; ?> </div>
                                    <td><?php echo number_format($total, 2); ?></div>
                                    <td>- <?php echo number_format($discount, 2); ?></div>
                                    <td><?php echo $late_fine > 0 ? number_format($late_fine) : '-'; ?></div>
                                    <td><?php echo $due_date_formatted; ?></div>
                                    <td><?php echo $paid_date_formatted; ?></div>
                                    <td><span class="<?php echo $bill['status'] == 'paid' ? 'status-paid' : 'status-unpaid'; ?>"><?php echo $bill['status'] == 'paid' ? 'Paid' : 'Unpaid'; ?></span></div>
                                    <td><?php if ($bill['status'] == 'unpaid'): ?><div style="display: flex; gap: 3px; flex-wrap: wrap;"><form method="post" style="display: inline;"><input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>"><input type="hidden" name="student_id" value="<?php echo $bill['student_id']; ?>"><button type="submit" name="pay_bill" class="pay-btn">Pay</button></form><?php if ($late_fine > 0): ?><a href="admin.php?page=bills&remove_late_fine=<?php echo $bill['id']; ?>&student_id=<?php echo $bill['student_id']; ?>" class="remove-fine-btn" onclick="return confirmRemoveFine()">Remove Fine</a><?php endif; ?></div><?php else: ?>✓<?php endif; ?></div>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="10" style="text-align:center; padding:20px;">📭 No monthly bills found</div></tr>
                                <?php } ?>
                            </tbody>
                         </div>
                        
                        <?php if ($student_id_filter > 0 && $student): ?>
                        <div class="info-box">
                            <strong>📌 How Discount Works:</strong> Food cost 3,500 TK for <?php echo $display_total_days_month; ?> days = <?php echo number_format(3500/$display_total_days_month, 2); ?> TK/day.<br>
                            Discount = (<?php echo $display_total_days_month; ?> - Days Stayed) × <?php echo number_format(3500/$display_total_days_month, 2); ?> TK.<br>
                            Room rent 4,000 TK (no discount). <strong>Due Date: 20th</strong> | Late fine 500 TK if unpaid after 20th.<br>
                            <strong>Security Deposit (5,000 TK):</strong> One time payment. Unpaid = Security shows 5,000, Balance 0. Paid = Security shows 0, Balance shows 5,000 Advance.
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                } else {
                    $where_conditions = ["b.status = 'paid'"];
                    
                    if (!empty($search_paid_from)) {
                        $where_conditions[] = "b.paid_date >= '$search_paid_from'";
                    }
                    if (!empty($search_paid_to)) {
                        $where_conditions[] = "b.paid_date <= '$search_paid_to'";
                    }
                    if (!empty($search_month)) {
                        $where_conditions[] = "b.month_year = '$search_month'";
                    }
                    
                    $where_sql = implode(" AND ", $where_conditions);
                    $paid_bills_query = mysqli_query($conn, "SELECT b.*, s.name, s.university_id, s.hostel_name, s.floor_no, s.room_no, s.seat_no FROM hostel_bills b LEFT JOIN hostel_students s ON b.student_id = s.id WHERE $where_sql ORDER BY b.id DESC");
                    
                    $total_paid_amount = 0;
                    $total_discount_amount = 0;
                    $total_late_fine_amount = 0;
                    $bill_count = 0;
                    
                    $bills_data = [];
                    if ($paid_bills_query && mysqli_num_rows($paid_bills_query) > 0) {
                        while ($bill = mysqli_fetch_assoc($paid_bills_query)) {
                            $bills_data[] = $bill;
                            $total_paid_amount += $bill['amount'];
                            $total_discount_amount += $bill['discount_amount'];
                            $total_late_fine_amount += $bill['late_fine'];
                            $bill_count++;
                        }
                    }
                    ?>
                    <div class="form-page">
                        <h2>💰 Bill History - All Paid Bills</h2>
                        
                        <form method="get" class="search-bill-box">
                            <input type="hidden" name="page" value="bills">
                            <div class="search-group">
                                <label>📅 Paid Date From</label>
                                <input type="date" name="paid_from" value="<?php echo htmlspecialchars($search_paid_from); ?>">
                            </div>
                            <div class="search-group">
                                <label>📅 Paid Date To</label>
                                <input type="date" name="paid_to" value="<?php echo htmlspecialchars($search_paid_to); ?>">
                            </div>
                            <div class="search-group">
                                <label>📆 Month Year</label>
                                <input type="month" name="search_month" value="<?php echo htmlspecialchars($search_month); ?>">
                            </div>
                            <div class="search-group">
                                <button type="submit">🔍 Search</button>
                            </div>
                            <div class="search-group">
                                <a href="admin.php?page=bills" class="reset-btn" style="background:#666; color:white; padding:6px 15px; border-radius:4px; text-decoration:none; font-size:12px;">⟳ Reset</a>
                            </div>
                        </form>
                        
                        <div class="financial-summary" style="margin-bottom: 20px;">
                            <div class="summary-grid">
                                <div class="summary-item"><h4>💰 Total Paid</h4><div class="amount positive"><?php echo number_format($total_paid_amount, 2); ?> TK</div><small>All paid bills</small></div>
                                <div class="summary-item"><h4>🎁 Total Discount</h4><div class="amount warning"><?php echo number_format($total_discount_amount, 2); ?> TK</div><small>Given discount</small></div>
                                <div class="summary-item"><h4>⚠️ Total Late Fine</h4><div class="amount warning"><?php echo number_format($total_late_fine_amount, 2); ?> TK</div><small>Collected fine</small></div>
                                <div class="summary-item"><h4>📋 Total Bills</h4><div class="amount positive"><?php echo $bill_count; ?></div><small>Number of bills</small></div>
                            </div>
                        </div>
                        
                        <h3 style="margin: 15px 0 8px; font-size: 14px;">📅 Paid Bill Details</h3>
                        <table class="bill-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>University ID</th>
                                    <th>Hostel</th>
                                    <th>Floor/Room/Seat</th>
                                    <th>Month</th>
                                    <th>Bill Type</th>
                                    <th>Days Stayed</th>
                                    <th>Amount (TK)</th>
                                    <th>Discount</th>
                                    <th>Late Fine</th>
                                    <th>Due Date</th>
                                    <th>Paid Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($bills_data) > 0) {
                                    foreach ($bills_data as $bill) {
                                        $days = $bill['days_stayed'] ?? 30;
                                        $total_days_month = $bill['total_days_in_month'] ?? 30;
                                        if ($total_days_month <= 0) $total_days_month = 30;
                                        $discount = $bill['discount_amount'] ?? 0;
                                        $late_fine = $bill['late_fine'] ?? 0;
                                        $total = $bill['amount'];
                                        if (empty($bill['month_year']) || $bill['month_year'] == '0000-00') {
                                            if (!empty($bill['paid_date']) && $bill['paid_date'] != '0000-00-00') {
                                                $month_display = date('M Y', strtotime($bill['paid_date']));
                                            } else {
                                                $month_display = 'N/A';
                                            }
                                        } else {
                                            $month_display = date('M Y', strtotime($bill['month_year'] . '-01'));
                                        }
                                        $due_date_formatted = date('Y-m-d', strtotime($bill['due_date']));
                                        $paid_date_formatted = $bill['paid_date'] ? date('Y-m-d', strtotime($bill['paid_date'])) : '0000-00-00';
                                        $hostel_display = isset($bill['hostel_name']) ? str_replace('_', ' ', $bill['hostel_name']) : 'Hostel A';
                                        $seat_display = "F" . ($bill['floor_no'] ?? '1') . "/R" . ($bill['room_no'] ?? '1') . "/S" . ($bill['seat_no'] ?? '1');
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($bill['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($bill['university_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo $hostel_display; ?></td>
                                    <td><?php echo $seat_display; ?></td>
                                    <td><?php echo $month_display; ?></td>
                                    <td>Monthly Bill</div>
                                    <td><?php echo $days . '/' . $total_days_month; ?></div>
                                    <td><?php echo number_format($total, 2); ?></div>
                                    <td><?php echo number_format($discount, 2); ?></div>
                                    <td><?php echo $late_fine > 0 ? number_format($late_fine) : '0'; ?></div>
                                    <td><?php echo $due_date_formatted; ?></div>
                                    <td><?php echo $paid_date_formatted; ?></div>
                                    <td><span class="status-paid">✅ Paid</span></div>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="13" style="text-align:center; padding:30px;">📭 No paid bills found</div></tr>
                                <?php } ?>
                            </tbody>
                         </div>
                        
                        <div class="info-box" style="margin-top: 15px;">
                            <strong>📌 Note:</strong> This page shows only <strong>paid bills</strong>. Unpaid bills can be viewed from Student Information → Bills button.
                        </div>
                    </div>
                    <?php
                }
            }
            elseif ($page == 'daily_meal') {
                $selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                $search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';
                $student_search = isset($_GET['student_search']) ? $_GET['student_search'] : '';
                
                if (!empty($search_date)) {
                    $selected_date = $search_date;
                }
                
                $student_where = "";
                if (!empty($student_search)) {
                    $student_where = "WHERE name LIKE '%$student_search%' OR university_id LIKE '%$student_search%'";
                }
                $students_query = mysqli_query($conn, "SELECT id, name, university_id, hostel_name, floor_no, room_no, seat_no FROM hostel_students $student_where ORDER BY name");
                
                $meal_status = [];
                $meal_query = mysqli_query($conn, "SELECT student_id, status FROM meal_tracking WHERE meal_date='$selected_date'");
                while ($row = mysqli_fetch_assoc($meal_query)) {
                    $meal_status[$row['student_id']] = $row['status'];
                }
                
                $total_students = mysqli_num_rows($students_query);
                $taken_count = 0;
                $not_taken_count = 0;
                
                $students = [];
                if ($students_query && mysqli_num_rows($students_query) > 0) {
                    while ($row = mysqli_fetch_assoc($students_query)) {
                        $students[] = $row;
                        $status = isset($meal_status[$row['id']]) ? $meal_status[$row['id']] : 'not_taken';
                        if ($status == 'taken') {
                            $taken_count++;
                        } else {
                            $not_taken_count++;
                        }
                    }
                }
                
                $tomorrow = date('Y-m-d', strtotime('+1 day'));
                $tomorrow_count = getTodayMealCount($conn, $tomorrow);
                $today_count = getTodayMealCount($conn, $selected_date);
            ?>
                <div class="form-page">
                    <h2>🍽️ Daily Meal Management</h2>
                    
                    <div class="meal-summary-cards">
                        <div class="meal-card total">
                            <h3>👨‍🎓 Total Students</h3>
                            <div class="count"><?php echo $total_students; ?></div>
                        </div>
                        <div class="meal-card taken">
                            <h3>✅ Meal Taken (<?php echo date('d M Y', strtotime($selected_date)); ?>)</h3>
                            <div class="count"><?php echo $taken_count; ?></div>
                        </div>
                        <div class="meal-card not-taken">
                            <h3>❌ Not Taken</h3>
                            <div class="count"><?php echo $not_taken_count; ?></div>
                        </div>
                        <div class="meal-card taken">
                            <h3>🍽️ Tomorrow's Meal (<?php echo date('d M Y', strtotime($tomorrow)); ?>)</h3>
                            <div class="count"><?php echo $tomorrow_count; ?></div>
                        </div>
                    </div>
                    
                    <form method="get" class="search-meal-box">
                        <input type="hidden" name="page" value="daily_meal">
                        <div class="search-group">
                            <label>📅 Select Date</label>
                            <input type="date" name="search_date" value="<?php echo $selected_date; ?>">
                        </div>
                        <div class="search-group">
                            <label>🔍 Search Student</label>
                            <input type="text" name="student_search" placeholder="Name or ID..." value="<?php echo htmlspecialchars($student_search); ?>">
                        </div>
                        <div class="search-group">
                            <button type="submit">🔍 Search</button>
                        </div>
                        <div class="search-group">
                            <a href="admin.php?page=daily_meal" style="background:#666; color:white; padding:6px 15px; border-radius:4px; text-decoration:none; font-size:12px;">⟳ Reset</a>
                        </div>
                    </form>
                    
                    <h3 style="margin: 15px 0 8px; font-size: 14px;">📋 Meal Status for <?php echo date('F j, Y', strtotime($selected_date)); ?></h3>
                    <table class="meal-history-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>University ID</th>
                                <th>Hostel</th>
                                <th>Floor/Room/Seat</th>
                                <th>Meal Status</th>
                                <th>Updated At</th>
                            </thead>
                            <tbody>
                                <?php if (count($students) > 0) {
                                    foreach ($students as $student) {
                                        $status = isset($meal_status[$student['id']]) ? $meal_status[$student['id']] : 'not_taken';
                                        $status_class = ($status == 'taken') ? 'meal-status-taken' : 'meal-status-not';
                                        $status_text = ($status == 'taken') ? '✅ Taken' : '❌ Not Taken';
                                        $hostel_display = str_replace('_', ' ', $student['hostel_name']);
                                        $seat_display = "F" . $student['floor_no'] . "/R" . $student['room_no'] . "/S" . $student['seat_no'];
                                        
                                        $update_query = mysqli_query($conn, "SELECT updated_at FROM meal_tracking WHERE student_id='{$student['id']}' AND meal_date='$selected_date'");
                                        $updated_at = '';
                                        if ($update_query && mysqli_num_rows($update_query) > 0) {
                                            $up_row = mysqli_fetch_assoc($update_query);
                                            $updated_at = date('d M Y h:i A', strtotime($up_row['updated_at']));
                                        } else {
                                            $updated_at = '-';
                                        }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name']); ?></div>
                                    <td><?php echo htmlspecialchars($student['university_id']); ?></div>
                                    <td><?php echo $hostel_display; ?></div>
                                    <td><?php echo $seat_display; ?></div>
                                    <td><span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span></div>
                                    <td><?php echo $updated_at; ?></div>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="6" style="text-align:center; padding:40px;">📭 No students found</div></tr>
                                <?php } ?>
                            </tbody>
                     </div>
                    
                    <div class="info-box" style="margin-top: 15px;">
                        <strong>📌 Note:</strong> Students can mark their meal from Student Portal → Meal Calendar. Admin can only view the meal status here.
                    </div>
                </div>
            <?php
            }
            elseif ($page == 'student_meal') {
                $student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hostel_students WHERE id='$student_id'"));
                
                if (!$student) {
                    echo "<div class='form-page'><p class='error'>Student not found!</p></div>";
                } else {
                    $current_month = isset($_GET['month']) ? $_GET['month'] : date('m');
                    $current_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                    $all_dates = getAllDatesOfMonth($current_month, $current_year);
                    $meal_count = getStudentMealCount($conn, $student_id, $current_month, $current_year);
                    $total_days = count($all_dates);
            ?>
                <div class="form-page">
                    <h2>🍽️ Meal History - <?php echo htmlspecialchars($student['name']); ?></h2>
                    
                    <div class="student-info-card">
                        <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                        <p>📚 ID: <?php echo $student['university_id']; ?> | 🏠 <?php echo str_replace('_', ' ', $student['hostel_name']); ?> | 📍 F<?php echo $student['floor_no']; ?>/R<?php echo $student['room_no']; ?>/S<?php echo $student['seat_no']; ?></p>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-bottom: 20px; align-items: center; flex-wrap: wrap;">
                        <a href="admin.php?page=student_meal&id=<?php echo $student_id; ?>&month=<?php echo date('m', strtotime('-1 month', strtotime("$current_year-$current_month-01"))); ?>&year=<?php echo date('Y', strtotime('-1 month', strtotime("$current_year-$current_month-01"))); ?>" class="view-btn" style="background:#1565c0;">◀ Previous Month</a>
                        <h3 style="margin:0; flex:1; text-align:center;"><?php echo date('F Y', strtotime("$current_year-$current_month-01")); ?></h3>
                        <a href="admin.php?page=student_meal&id=<?php echo $student_id; ?>&month=<?php echo date('m', strtotime('+1 month', strtotime("$current_year-$current_month-01"))); ?>&year=<?php echo date('Y', strtotime('+1 month', strtotime("$current_year-$current_month-01"))); ?>" class="view-btn" style="background:#1565c0;">Next Month ▶</a>
                    </div>
                    
                    <div class="meal-summary-cards">
                        <div class="meal-card total">
                            <h3>📅 Total Days</h3>
                            <div class="count"><?php echo $total_days; ?></div>
                        </div>
                        <div class="meal-card taken">
                            <h3>✅ Meals Taken</h3>
                            <div class="count"><?php echo $meal_count; ?></div>
                        </div>
                        <div class="meal-card not-taken">
                            <h3>❌ Meals Not Taken</h3>
                            <div class="count"><?php echo ($total_days - $meal_count); ?></div>
                        </div>
                    </div>
                    
                    <table class="meal-history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Status</th>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($all_dates as $date) {
                                    $day_name = date('l', strtotime($date));
                                    $display_date = date('F j, Y', strtotime($date));
                                    $status = getMealStatus($conn, $student_id, $date);
                                    $status_class = ($status == 'taken') ? 'meal-status-taken' : 'meal-status-not';
                                    $status_text = ($status == 'taken') ? '✅ Taken' : '❌ Not Taken';
                                ?>
                                <tr>
                                    <td><?php echo $display_date; ?></td>
                                    <td><?php echo $day_name; ?></div>
                                    <td><span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span></div>
                                </tr>
                                <?php } ?>
                            </tbody>
                         </div>
                    
                    <div class="info-box" style="margin-top: 15px;">
                        <strong>📌 Note:</strong> This page shows complete meal history for the student. Students can mark meals from their portal.
                    </div>
                    
                    <a href="admin.php?page=students" class="back-link" style="display: inline-block; margin-top: 20px;">← Back to Student List</a>
                </div>
            <?php
                }
            }
            elseif ($page == 'view') {
                $id = (int)$_GET['id'];
                $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hostel_students WHERE id='$id'"));
                if ($data) {
            ?>
                <div class="form-page">
                    <h2>📄 Student Full Details</h2>
                    <div class="student-detail">
                        <p><strong>University ID:</strong> <?php echo htmlspecialchars($data['university_id']); ?></p>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($data['name']); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($data['department']); ?></p>
                        <p><strong>Father:</strong> <?php echo htmlspecialchars($data['father_name']); ?></p>
                        <p><strong>Mother:</strong> <?php echo htmlspecialchars($data['mother_name']); ?></p>
                        <p><strong>DOB:</strong> <?php echo $data['dob']; ?></p>
                        <p><strong>Blood:</strong> <?php echo $data['blood_group']; ?></p>
                        <p><strong>Email:</strong> <?php echo $data['email']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $data['phone']; ?></p>
                        <p><strong>Present Address:</strong> <?php echo nl2br($data['present_address']); ?></p>
                        <p><strong>Permanent Address:</strong> <?php echo nl2br($data['permanent_address']); ?></p>
                        <p><strong>Guardian:</strong> <?php echo $data['guardian_name']; ?> (<?php echo $data['guardian_phone']; ?>)</p>
                        <p><strong>Hostel:</strong> <?php echo isset($data['hostel_name']) ? str_replace('_', ' ', $data['hostel_name']) : 'Hostel A'; ?></p>
                        <p><strong>Location:</strong> Floor <?php echo $data['floor_no'] ?? '1'; ?> / Room <?php echo $data['room_no'] ?? '1'; ?> / Seat <?php echo $data['seat_no'] ?? '1'; ?></p>
                        <p><strong>Join Date:</strong> <?php echo $data['join_date']; ?></p>
                        <p><strong>Total Due:</strong> <span class="total-due"><?php echo number_format($data['total_due'], 2); ?> TK</span></p>
                        <p><strong>Student Photo:</strong><br><img src="<?php echo $data['student_photo']; ?>"></p>
                        <p><strong>ID Card:</strong><br><img src="<?php echo $data['id_card_photo']; ?>"></p>
                        <a href="admin.php?page=students" class="back-link">← Back to Student List</a>
                        <a href="admin.php?page=bills&student_id=<?php echo $data['id']; ?>" style="margin-left: 15px;">💰 View Bills</a>
                        <a href="admin.php?page=student_meal&id=<?php echo $data['id']; ?>" style="margin-left: 15px;">🍽️ View Meals</a>
                    </div>
                </div>
            <?php
                } else {
                    echo "<div class='form-page'><p class='error'>Student not found!</p></div>";
                }
            } elseif ($page == 'backup') {
                $backup_res = mysqli_query($conn, "SELECT * FROM hostel_students_backup ORDER BY id DESC");
                $backup_count = $backup_res ? mysqli_num_rows($backup_res) : 0;
            ?>
                <div class="form-page">
                    <h2>💾 Backup & Restore - Deleted Students</h2>
                    
                    <?php if ($backup_res && $backup_count > 0) { ?>
                        <div class="action-buttons">
                            <button type="button" class="select-all-btn" onclick="toggleAllCheckboxes(this)">✅ Select All</button>
                            <form method="post" style="display: inline; margin: 0;" onsubmit="return confirmDeleteSelected()">
                                <button type="submit" name="delete_selected_backup" class="delete-selected-btn">🗑️ Delete Permanently</button>
                            </form>
                        </div>
                        <br>
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th class="checkbox-col"><input type="checkbox" id="select_all" onclick="toggleAllCheckboxes(this)"></th>
                                    <th>University ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Deleted Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($backup = mysqli_fetch_assoc($backup_res)) { ?>
                                <tr>
                                    <td class="checkbox-col"><input type="checkbox" name="selected_backups[]" value="<?php echo $backup['id']; ?>"> </div>
                                    <td><?php echo htmlspecialchars($backup['university_id']); ?></div>
                                    <td><?php echo htmlspecialchars($backup['name']); ?></div>
                                    <td><?php echo htmlspecialchars($backup['department']); ?></div>
                                    <td><?php echo $backup['deleted_date']; ?></div>
                                    <td>
                                        <a href="admin.php?page=backup&restore_id=<?php echo $backup['id']; ?>" onclick="return confirmRestore('<?php echo addslashes($backup['name']); ?>')">♻️ Restore</a>
                                        &nbsp;|&nbsp;
                                        <a href="admin.php?page=backup&delete_backup_id=<?php echo $backup['id']; ?>" onclick="return confirmBackupDelete('<?php echo addslashes($backup['name']); ?>')">🗑️ Delete</a>
                                     </div>
                                </tr>
                                <?php } ?>
                            </tbody>
                         </div>
                    <?php } else { ?>
                        <div style="padding: 40px; text-align: center; background: #f9f9f9; border-radius: 8px;">
                            <p style="font-size: 18px; color: #666;">📭 No deleted students found in backup.</p>
                            <p style="margin-top: 10px; color: #999;">When you delete a student, they will appear here for restoration.</p>
                            <a href="admin.php?page=students" style="display: inline-block; margin-top: 20px; background: #2e7d32; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none;">Go to Student List</a>
                        </div>
                    <?php } ?>
                    
                    <div class="info-box" style="margin-top: 20px;">
                        <strong>📌 Backup & Restore Instructions:</strong><br>
                        • ♻️ <strong>Restore</strong> - Brings the student back to main student list<br>
                        • 🗑️ <strong>Delete</strong> - Removes single backup record from database forever<br>
                        • ☑️ <strong>Select All + Delete Selected</strong> - Remove multiple backup records at once<br>
                        • ⚠️ <strong>Warning:</strong> Permanently deleted records cannot be recovered!
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
<?php } ?>

</body>
</html>

