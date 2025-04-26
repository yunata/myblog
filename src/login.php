<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>ログイン</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h1>🔐 ログイン</h1>
<form action="login_check.php" method="POST">
  <label>ユーザー名:</label><br>
  <input type="text" name="username" required><br><br>
  <label>パスワード:</label><br>
  <input type="password" name="password" required><br><br>
  <button type="submit">ログイン</button>
</form>
</body>
</html>