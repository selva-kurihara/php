<?php
session_start(); // セッション開始

// ログアウト処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  $_SESSION = [];
  session_destroy();
  // ページを再読み込みしてログイン状態リセット
  header("Location: logout.php");
  exit;
}

// ログイン状態かどうかを判定
$isLoggedIn = isset($_SESSION['user']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>トップページ</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body>

  <div class="header">
    <a href="thread.php" class="login-button">スレッド一覧</a>
    <?php if ($isLoggedIn): ?>
      <div class="welcome">ようこそ <?php echo htmlspecialchars($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8'); ?> 様</div>
      <!-- ログアウトボタン（同じファイルにPOST） -->
      <form method="post" style="margin: 0;">
        <a href="thread_regist.php" class="login-button">新規スレッド作成</a>
        <button type="submit" name="logout" class="logout-button">ログアウト</button>
        <a href="member_withdrawal.php" class="btn btn-top">退会</a>
      </form>
    <?php else: ?>
      <a href="member_regist.php" class="login-button">新規会員登録</a>
      <a href="login.php" class="login-button">ログイン</a>
    <?php endif; ?>
  </div>

</body>

</html>