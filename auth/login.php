<?php
session_start();
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Login QueueFlow</title>
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="login-container">
  <!-- LEFT -->
  <div class="login-left">

    <h2>CONNEXION</h2>

    <div class="social-icons">
      <div class="icon">Q</div>
      <div class="icon">U</div>
      <div class="icon">E</div>
      <div class="icon">U</div>
      <div class="icon">E</div>
      <div class="icon">F</div>
      <div class="icon">L</div>
      <div class="icon">O</div>
      <div class="icon">W</div>
    </div>

    <!-- FORM LOGIN -->
<?php if ($error): ?>
  <div class="alert-error">
    <?= $error ?>
  </div>
<?php endif; ?>
    <form method="POST" action="login_process.php">

    <input type="email" name="email" placeholder="Email" required autocomplete="off">
    <input type="password" name="password" placeholder="Password" required autocomplete="off">

      <button type="submit" class="btn-primary">
        Connecter
      </button>

    </form>

    <p class="forgot">Mot de passe oublié</p>

  </div>

  <!-- RIGHT -->
  <div class="login-right">

    <div class="welcome-content">

      <h2>Bienvenue<br>dans QueueFlow</h2>

      <p>Système de gestion de file d'attente</p>

      <button class="btn-outline">
        Connexion
      </button>

    </div>

  </div>

</div>

</body>
</html>