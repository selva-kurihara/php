<?php
session_start();

$isLoggedIn = isset($_SESSION['user']);
$userId = $_SESSION['user']['id'] ?? null;

if (!$isLoggedIn) {
  header("Location: logout.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
  try {
    $dsn = 'mysql:dbname=phpkadai;host=localhost;charset=utf8';
    $user = 'kurihara';
    $password = 'uCmCLu2e8H';
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ソフトデリート
    $stmt = $pdo->prepare("UPDATE members SET deleted_at = NOW() WHERE id = :id");
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    session_unset();
    session_destroy();
    header("Location: logout.php");
    exit;
  } catch (PDOException $e) {
    error_log('DBエラー: ' . $e->getMessage());
    $error = "システムエラーが発生しました。";
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>退会ページ</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body>
  <div class="withdrawal-container">
    <div class="thread-header">
      <span></span>
      <button class="btn btn-top" onclick="location.href='logout.php'">トップに戻る</button>
    </div>

    <h2>退会</h2>
    <p>退会しますか？</p>

    <?php if (isset($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
      <div class="buttons">
        <button type="submit" name="withdraw" class="btn">退会する</button>
      </div>
    </form>
  </div>
</body>

</html>