<?php

session_start();

// ログインしてなければリダイレクト
if (!isset($_SESSION['user'])) {
  header('Location: logout.php');
  exit;
}


// エラーメッセージの初期化
$errorMessages = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // エラーチェック
  if (empty($_POST["title"])) {
    $errorMessages["title"] = 'タイトルが未入力です。';
  } else if (mb_strlen($_POST["title"]) > 100) {
    $errorMessages["title"] = 'スレッドタイトルが100文字以内で入力してください。';
  }

  if (empty($_POST["comment"])) {
    $errorMessages["comment"] = 'コメントが未入力です。';
  } else if (mb_strlen($_POST["comment"]) > 500) {
    $errorMessages["comment"] = 'コメントが500文字以内で入力してください。';
  }


  if (isset($_POST['action']) && $_POST['action'] === 'confirm' && empty($errorMessages)) {
    $_SESSION['form'] = $_POST;
    header("Location: thread_confirm.php");
    exit;
  }
}






?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>スレッド作成フォーム</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body>
  <div class="container">
    <h2>スレッド作成フォーム</h2>
    <form action="thread_regist.php" method="post" novalidate>
      <input type="hidden" name="action" value="confirm">

      <div>
        <label for="title">スレッドタイトル</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        <?php if (!empty($errorMessages['title'])): ?>
          <p class="error"><?= htmlspecialchars($errorMessages['title']) ?></p>
        <?php endif; ?>
      </div>

      <div>
        <label for="comment">コメント</label>
        <textarea id="comment" name="comment"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
        <?php if (!empty($errorMessages['comment'])): ?>
          <p class="error"><?= htmlspecialchars($errorMessages['comment']) ?></p>
        <?php endif; ?>
      </div>

      <div class="buttons">
        <button type="submit" class="btn btn-confirm">確認画面へ</button>
        <button type="button" class="btn btn-top" onclick="location.href='logout.php'">トップに戻る</button>
      </div>
  </div>
</body>

</html>