<?php
include 'auth.php';
include 'db.php';

// Handle Search Query
$search_query = trim($_GET['search'] ?? '');

if ($search_query !== '') {
    $stmt = $pdo->prepare("SELECT * FROM queues WHERE student_name LIKE ? OR queue_number LIKE ? OR category LIKE ? ORDER BY id DESC");
    $searchTerm = "%" . $search_query . "%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $history_records = $stmt->fetchAll();
} else {
    $history_records = $pdo->query("SELECT * FROM queues ORDER BY id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transactions History -  Registrar</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .card-box {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="top-header">
            <div class="header-title">Transaction History</div>
            <div class="header-user">
                <span>👤 <?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
            </div>
        </header>

        <div class="dashboard-body">
            <div class="card-box">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <h3 style="margin: 0;">All Queue Transactions</h3>
                    
                    <!-- Search Form -->
                    <form method="GET" style="display: flex; gap: 10px; width: 100%; max-width: 400px;">
                        <input type="text" name="search" placeholder="Search name, queue no., or service..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #f8fafc;">
                        <button type="submit" style="background: #2563eb; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer;">Search</button>
                        <?php if ($search_query !== ''): ?>
                            <a href="history.php" style="background: #e2e8f0; color: #334155; padding: 10px 14px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; display: flex; align-items: center;">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <table class="styled-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Queue No.</th>
                            <th>Student Name</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history_records)): ?>
                            <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">No transaction records found matching your search.</td></tr>
                        <?php else: ?>
                            <?php foreach ($history_records as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['queue_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td>
                                    <span style="font-weight: 600; font-size: 12px; color: <?php echo ($row['status'] == 'completed') ? '#16a34a' : '#d97706'; ?>;">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <footer><p>&copy; 2026 Registrar Queuing System</p></footer>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.8); z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-card-box" style="background: #ffffff; padding: 40px; border-radius: 16px; width: 100%; max-width: 500px; text-align: center;">
            <h2 class="modal-card-title" style="font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">MSU-TCTO Registrar</h2>
            <div class="modal-card-subtitle" style="font-size: 14px; color: #64748b; margin-bottom: 30px;">End your current administrator session</div>
            
            <div style="font-size: 15px; color: #334155; margin-bottom: 25px; font-weight: 500;">
                Are you sure you want to log out of the system?
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn-submit-card" style="background: #e2e8f0; color: #334155; width: 50%; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" onclick="closeLogoutModal()">Cancel</button>
                <a href="admin.php?logout=true" class="btn-submit-card btn-danger-card" style="background: #dc2626; color: white; width: 50%; padding: 12px; border-radius: 8px; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center;">Logout</a>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout(e) {
            e.preventDefault();
            document.getElementById('logoutModal').style.display = 'flex';
        }
        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }
    </script>
</body>
</html>