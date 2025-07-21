<?php
session_start(); // セッション開始

// ログアウト処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  $_SESSION = [];
  session_destroy();
  // ページを再読み込みしてログイン状態リセット
  header("Location: login.php");
  exit;
}

// ログイン状態かどうかを判定
$isLoggedIn = isset($_SESSION['administer']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>掲示板管理画面メインメニュー</title>
  <link rel="stylesheet" href="../stylesheet.css">
</head>

<body>

  <div class="header">
    <h2>掲示板管理画面メインメニュー</h2>
      <?php if ($isLoggedIn): ?>
        <div class="welcome">ようこそ <?php echo htmlspecialchars($_SESSION['administer']['name'], ENT_QUOTES, 'UTF-8'); ?> さん</div>
        <!-- ログアウトボタン（同じファイルにPOST） -->
        <form method="post" style="margin: 0;">
          <button type="submit" name="logout" class="logout-button">ログアウト</button>
        </form>
      <?php endif; ?>
  </div>

</body>

</html>