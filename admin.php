<?php
session_start();
include 'db.php';


// Handle Login Form Submission
$login_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($email === 'admin@registrar.com' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        header("Location: admin.php");
        exit();
    } else {
        $login_error = "Invalid email or password!";
    }
}

// Handle Logout Action
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_email']);
    session_destroy();
    header("Location: admin.php");
    exit();
}

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// If not logged in, show only the full-screen login gate
if (!$is_logged_in):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Registrar</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0f172a;
            font-family: inherit;
        }
        .login-overlay {
            display: flex;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #0f172a; 
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-card-box {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            box-sizing: border-box;
        }
        .modal-card-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px 0;
        }
        .modal-card-subtitle {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            background: #f8fafc;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            background: #ffffff;
        }
        .btn-submit-card {
            width: 100%;
            background: #2563eb;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit-card:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="login-overlay">
        <div class="modal-card-box">
            <h2 class="modal-card-title">Registrar</h2>
            <div class="modal-card-subtitle">Sign in to access the administrator panel</div>
            
            <?php if (!empty($login_error)): ?>
                <div style="background: #fee2e2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="admin@registrar.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" name="login_submit" class="btn-submit-card">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
    exit();
endif;

// --- Dashboard Logic (Loads only if logged in) ---
$spoken_ticket = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['call_next'])) {
        $stmt = $pdo->query("SELECT id FROM queues WHERE LOWER(status) = 'waiting' ORDER BY id ASC LIMIT 1");
        $next_ticket = $stmt->fetch();
        if ($next_ticket) {
            $pdo->exec("UPDATE queues SET status = 'completed' WHERE LOWER(status) = 'serving'");
            $update_next = $pdo->prepare("UPDATE queues SET status = 'serving' WHERE id = ?");
            $update_next->execute([$next_ticket['id']]);
        }
    } elseif (isset($_POST['mark_done'])) {
        $pdo->exec("UPDATE queues SET status = 'completed' WHERE LOWER(status) = 'serving'");
    } elseif (isset($_POST['update_status'])) {
        $id = $_POST['queue_id'];
        $new_status = $_POST['new_status'];
        $stmt = $pdo->prepare("UPDATE queues SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
    } elseif (isset($_POST['reset_queue'])) {
        $pdo->exec("DELETE FROM queues");
    }
    
    header("Location: admin.php");
    exit();
}

// Metrics Counts
$waiting_count = $pdo->query("SELECT COUNT(*) FROM queues WHERE LOWER(status) = 'waiting'")->fetchColumn();
$served_count = $pdo->query("SELECT COUNT(*) FROM queues WHERE LOWER(status) = 'completed'")->fetchColumn();
$total_transactions = $pdo->query("SELECT COUNT(*) FROM queues")->fetchColumn();
$serving = $pdo->query("SELECT * FROM queues WHERE LOWER(status) = 'serving' LIMIT 1")->fetch();

if ($serving) {
    $spoken_ticket = $serving['queue_number'];
}

$queue_list = $pdo->query("SELECT * FROM queues WHERE LOWER(status) = 'waiting' ORDER BY id ASC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrar Queuing System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-grid-top {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .dashboard-grid-bottom {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .card-box {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .now-serving-card {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 25px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .metric-mini-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Logout Modal Unique Styles */
        .logout-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.75); 
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .logout-modal-box {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            box-sizing: border-box;
        }
        .logout-modal-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px 0;
        }
        .logout-modal-subtitle {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 30px;
        }
        .btn-submit-card {
            width: 100%;
            background: #2563eb;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit-card:hover {
            background: #1d4ed8;
        }
        .back-link-container {
            text-align: center;
            margin-top: 20px;
        }
        .back-link-container a {
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .back-link-container a:hover {
            text-decoration: underline;
        }
        .btn-danger-card {
            background: #dc2626;
        }
        .btn-danger-card:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="top-header">
            <div class="header-title">Registrar Queuing System</div>
            <div class="header-user">
                <span>👤 <?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
            </div>
        </header>

        <div class="dashboard-body">
            <!-- Top Row: Now Serving + Metrics Grid -->
            <div class="dashboard-grid-top">
                <div class="now-serving-card">
                    <div>
                        <div style="font-size: 13px; opacity: 0.9; font-weight: 600;">Now Serving</div>
                        <div style="font-size: 48px; font-weight: 800; margin: 10px 0;">
                            <?php echo $serving ? htmlspecialchars($serving['queue_number']) : 'None'; ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 15px; font-weight: 600;"><?php echo $serving ? htmlspecialchars($serving['category']) : 'No active service'; ?></div>
                        <div style="font-size: 12px; opacity: 0.8; margin-top: 2px;"><?php echo $serving ? 'Student: ' . htmlspecialchars($serving['student_name']) : ''; ?></div>
                    </div>
                </div>

                <div class="metric-mini-card">
                    <div style="color: #64748b; font-size: 12px; font-weight: bold;">👥 WAITING</div>
                    <div style="font-size: 32px; font-weight: 800; color: #0f172a;"><?php echo $waiting_count; ?></div>
                    <div style="font-size: 11px; color: #94a3b8;">Total in Queue</div>
                </div>

                <div class="metric-mini-card">
                    <div style="color: #16a34a; font-size: 12px; font-weight: bold;">✔️ SERVED TODAY</div>
                    <div style="font-size: 32px; font-weight: 800; color: #0f172a;"><?php echo $served_count; ?></div>
                    <div style="font-size: 11px; color: #94a3b8;">Completed Forms</div>
                </div>

                <div class="metric-mini-card">
                    <div style="color: #d97706; font-size: 12px; font-weight: bold;">📊 TRANSACTIONS</div>
                    <div style="font-size: 32px; font-weight: 800; color: #0f172a;"><?php echo $total_transactions; ?></div>
                    <div style="font-size: 11px; color: #94a3b8;">Today's Volume</div>
                </div>
            </div>

            <!-- Control Actions Bar -->
            <div style="margin-bottom: 25px;">
                <form method="POST" style="display: flex; gap: 10px;">
                    <button type="submit" name="call_next" class="btn" style="background: #2563eb; padding: 10px 20px;">Call Next Ticket</button>
                    <button type="submit" name="mark_done" class="btn" style="background: #16a34a; padding: 10px 20px;">Done</button>
                    <button type="submit" name="reset_queue" class="btn" style="background: #dc2626; padding: 10px 20px;" onclick="return confirm('Reset all queue logs?');">Reset Queue</button>
                </form>
            </div>

            <!-- Bottom Row: Split Grid -->
            <div class="dashboard-grid-bottom">
                <div class="card-box">
                    <h3>Queue List</h3>
                    <table class="styled-table" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Queue No.</th>
                                <th>Service</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($queue_list)): ?>
                            <tr><td colspan="3" style="text-align: center; color: #64748b; padding: 20px;">No students waiting in line.</td></tr>
                            <?php else: ?>
                                <?php foreach ($queue_list as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['queue_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><span style="color: #d97706; font-weight: 600; font-size: 12px;"><?php echo ucfirst($row['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-box">
                    <h3>Counters</h3>
                    <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 15px;">
                        <div style="padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 12px; font-weight: 600; color: #64748b;">Counter 1 (Active Window)</div>
                            <div style="font-size: 16px; font-weight: 700; color: #16a34a; margin-top: 4px;">
                                <?php echo $serving ? htmlspecialchars($serving['queue_number']) : 'Available'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer><p>&copy; 2026 Registrar Queuing System</p></footer>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="logout-modal-overlay">
        <div class="logout-modal-box" style="text-align: center;">
            <h2 class="logout-modal-title">MSU-TCTO Registrar</h2>
            <div class="logout-modal-subtitle">End your current administrator session</div>
            
            <div style="font-size: 15px; color: #334155; margin-bottom: 25px; font-weight: 500;">
                Are you sure you want to log out of the system?
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn-submit-card" style="background: #e2e8f0; color: #334155; margin-top: 0;" onclick="closeLogoutModal()">Cancel</button>
                <a href="admin.php?logout=true" class="btn-submit-card btn-danger-card" style="text-decoration: none; display: flex; align-items: center; justify-content: center; margin-top: 0;">Logout</a>
            </div>

            <div class="back-link-container">
                <a href="#" onclick="closeLogoutModal()">← Return to Panel</a>
            </div>
        </div>
    </div>

    <!-- Script Section for Logout Modal & Speech Synthesis -->
    <script>
        function confirmLogout(e) {
            e.preventDefault();
            document.getElementById('logoutModal').style.display = 'flex';
        }
        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        const activeTicket = "<?php echo $spoken_ticket; ?>";
        if (activeTicket) {
            if ('speechSynthesis' in window) {
                const lastSpoken = sessionStorage.getItem('last_spoken');
                if (lastSpoken !== activeTicket) {
                    sessionStorage.setItem('last_spoken', activeTicket);
                    
                    const textToSpeak = "Now serving queue number " + activeTicket.split('').join(' ');
                    const utterance = new SpeechSynthesisUtterance(textToSpeak);
                    utterance.rate = 0.9;
                    window.speechSynthesis.speak(utterance);
                }
            }
        }
    </script>
</body>
</html>