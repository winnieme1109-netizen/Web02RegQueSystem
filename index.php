<?php
include 'db.php';

$success_message = "";
$error_message = "";
$student_name = "";
$student_id = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_ticket'])) {
    $student_name = trim($_POST['student_name']);
    $student_id = trim($_POST['student_id']);
    $category = $_POST['category'] ?? '';

    if (!empty($student_name) && !empty($student_id) && !empty($category)) {
        // Validate Name (letters and spaces only) and Student ID (numbers only)
        if (!preg_match("/^[a-zA-Z\s]+$/", $student_name)) {
            $error_message = "Invalid: Student Full Name must contain letters only.";
        } elseif (!preg_match("/^\d{2}-\d{5}$/", $student_id)) {
            $error_message = "Invalid: Student ID Number must contain XX-XXXXX";
        } else {


            // Safe integer parsing for sequential queue numbering
            $last_ticket = $pdo->query("SELECT queue_number FROM queues ORDER BY id DESC LIMIT 1")->fetch();
            $next_num = 1001;
            if ($last_ticket) {
                // Extract numbers safely from string (e.g., R1023 -> 1023)
                preg_match('/\d+/', $last_ticket['queue_number'], $matches);
                if (!empty($matches[0])) {
                    $next_num = intval($matches[0]) + 1;
                }
            }
            $queue_number = 'R' . $next_num;

            $stmt = $pdo->prepare("INSERT INTO queues (queue_number, student_name, student_id, category, status) VALUES (?, ?, ?, ?, 'waiting')");
            $stmt->execute([$queue_number, $student_name, $student_id, $category]);
            
            $success_message = "Your Queue Number is: <strong>{$queue_number}</strong>";
            
            // Clear input fields on successful submission if desired
            $student_name = "";
            $student_id = "";
        }
    } else {
        $error_message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Queue Management - MSU-TCTO Registrar</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #0f172a;">
    <div style="background: white; padding: 40px; border-radius: 16px; width: 100%; max-width: 450px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="color: #0f172a; margin: 0 0 5px 0;">MSU-TCTO Registrar</h2>
            <p style="color: #64748b; font-size: 14px; margin: 0;">Select your requested service to get a queue ticket</p>
        </div>

        <?php if ($success_message): ?>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-size: 18px;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #dc2626; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 5px;">Student Full Name</label>
                <input type="text" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 5px;">Student ID Number</label>
                <input type="text" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 5px;">Document Requested</label>
                <select name="category" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: white; box-sizing: border-box;">
                    <option value="" disabled selected>Select a document...</option>
                    <option value="Request Form">Request Form</option>
                    <option value="Transcript of Records(TOR) Request From">Transcript of Records(TOR) Request From</option>
                    <option value="Request Form for Certificate of Registration(COR)">Request Form for Certificate of Registration(COR)</option>
                    <option value="Honorable Dismissal">Honorable Dismissal</option>
                    <option value="Leave of Absence Form">Leave of Absence Form</option>
                    <option value="Application for Graduation Form">Application for Graduation Form</option>
                </select>
            </div>
            <button type="submit" name="get_ticket" class="btn" style="width: 100%; padding: 12px; font-size: 16px;">Get Queue Ticket</button>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <a href="admin.php" style="color: #2563eb; font-size: 13px; text-decoration: none; font-weight: 600;">← Back to Admin Panel</a>
        </div>
    </div>
</body>
</html>