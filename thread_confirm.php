<?php

session_start();

//if (!isset($_SESSION['user'])) {
  //header('Location: logout.php');
  //exit;
//}

if (!isset($_SESSION['form'])) {
  header('Location: thread_regist.php');
  exit;
}

$form = $_SESSION['form'];

if ($_POST['action']== "submit") {

// DB接続（PDO使用）
try {
  $dsn = 'mysql:host=localhost;dbname=phpkadai;charset=utf8mb4';
  $username = 'kurihara';
  $password = 'uCmCLu2e8H';

  $pdo = new PDO($dsn, $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // スレッド登録
  $stmt = $pdo->prepare('INSERT INTO threads (member_id, title, content, created_at, updated_at) VALUES (:member_id, :title, :content, NOW(), NOW())');
  $stmt->bindParam(':member_id', $_SESSION['user']['id'], PDO::PARAM_INT);
  $stmt->bindParam(':title', $_SESSION['form']['title']);
  $stmt->bindParam(':content', $_SESSION['form']['comment']);
  $stmt->execute();

  // フォーム情報を削除（二重送信防止）
  unset($_SESSION['form']);

  // 完了後にトップへ
  header('Location: logout.php');
  exit;
} catch (PDOException $e) {
  echo 'エラーが発生しました：' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
  exit;
}
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>スレッド作成確認画面</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body>
  <div class="container">
    <h2>スレッド作成確認画面</h2>
    <dl>
      <dt>スレッドタイトル</dt>
      <dd><?= htmlspecialchars($form['title']) ?></dd>

      <dt>コメント</dt>
      <dd><?= nl2br(htmlspecialchars($form['comment'])) ?></dd>
    </dl>

    <div class="buttons">
      <!-- スレッド作成ボタン -->
      <form action="thread_confirm.php" method="post" style="display:inline;">
        <input type="hidden" name="action" value="submit">
        <button type="submit" class="btn btn-submit">スレッドを作成する</button>
      </form>

      <!-- 前に戻るボタン -->
      <form action="thread_regist.php" method="post" style="display:inline;">
        <input type="hidden" name="title" value="<?= htmlspecialchars($form['title'], ENT_QUOTES) ?>">
        <input type="hidden" name="comment" value="<?= htmlspecialchars($form['comment'], ENT_QUOTES) ?>">
        <input type="hidden" name="action" value="rewrite">
        <button type="submit" class="btn btn-back">前に戻る</button>
      </form>
    </div>
  </div>
</body>

</html>