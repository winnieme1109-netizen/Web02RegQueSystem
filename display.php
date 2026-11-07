<?php
include 'db.php';

$spoken_ticket = "";

$serving = $pdo->query("SELECT * FROM queues WHERE LOWER(status) = 'serving' LIMIT 1")->fetch();
$waiting_list = $pdo->query("SELECT * FROM queues WHERE LOWER(status) = 'waiting' ORDER BY id ASC LIMIT 5")->fetchAll();

if ($serving) {
    $spoken_ticket = $serving['queue_number'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Queue Display - Registrar Office</title>
    <meta http-equiv="refresh" content="3"> <!-- Auto refreshes every 3 seconds to sync with admin actions -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: #0f172a; color: white; height: 100vh; margin: 0; display: flex; flex-direction: column; justify-content: space-between; padding: 40px; box-sizing: border-box;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 20px;">
        <h1 style="margin: 0; font-size: 24px;">🏛️  Registrar Office - Live Queue</h1>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; align-items: center; flex: 1; padding: 40px 0;">
        <div style="background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 50px; border-radius: 20px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <div style="font-size: 28px; font-weight: 600; letter-spacing: 2px; opacity: 0.9;">NOW SERVING</div>
            <div style="font-size: 120px; font-weight: 900; margin: 20px 0; line-height: 1;">
                <?php echo $serving ? htmlspecialchars($serving['queue_number']) : '---'; ?>
            </div>
            <div style="font-size: 24px; font-weight: 500;">
                <?php echo $serving ? htmlspecialchars($serving['category']) : 'Please wait for the next call'; ?>
            </div>
            <div style="font-size: 18px; opacity: 0.8; margin-top: 10px;">
                <?php echo $serving ? 'Student: ' . htmlspecialchars($serving['student_name']) : ''; ?>
            </div>
        </div>

        <div style="background: #1e293b; padding: 30px; border-radius: 20px; border: 1px solid #334155;">
            <h3 style="margin-top: 0; color: #94a3b8; font-size: 18px; border-bottom: 1px solid #334155; padding-bottom: 10px;">UPCOMING IN LINE</h3>
            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
                <?php if (empty($waiting_list)): ?>
                    <div style="color: #64748b; text-align: center; padding: 20px;">No queue waiting.</div>
                <?php else: ?>
                    <?php foreach ($waiting_list as $w): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: #0f172a; padding: 15px 20px; border-radius: 10px; border: 1px solid #334155;">
                            <span style="font-size: 24px; font-weight: bold; color: #38bdf8;"><?php echo htmlspecialchars($w['queue_number']); ?></span>
                            <span style="font-size: 14px; color: #cbd5e1;"><?php echo htmlspecialchars($w['category']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="text-align: center; color: #64748b; font-size: 14px; border-top: 1px solid #334155; padding-top: 20px;">
        Please monitor the display screen and prepare your requirements when your number is called.
    </div>

    <!-- Voice Announcement Script -->
    <script>
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