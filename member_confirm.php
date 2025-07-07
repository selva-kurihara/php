<?php
// セッション開始
session_start();

// 登録画面からの遷移でなければ、登録画面にリダイレクト
if (!isset($_SESSION['form'])) {
  header("Location: member_regist.php");
  exit;
}

$form = $_SESSION['form'];

// 登録完了メッセージの初期化
$signUpMessage = "";

// $db['host'] = "localhost";  // DBサーバのURL
// $db['user'] = "hogeUser";  // ユーザー名
// $db['pass'] = "hogehoge";  // ユーザー名のパスワード
// $db['dbname'] = "loginManagement";  // データベース名
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>会員情報確認画面</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body>
  <div class="container">
    <h2>会員情報確認画面</h2>
    <form action="member_complete.php" method="post">
      <!-- 氏名 -->
      <div class="row"><span class="label">氏名</span><span class="value"><?= htmlspecialchars($form['last_name'] . '　' . $form['first_name']) ?></span></div>

      <!-- 性別 -->
      <div class="row"><span class="label">性別</span><span class="value"><?= htmlspecialchars($form['gender']) ?></span></div>

      <!-- 住所 -->
      <div class="row"><span class="label">住所</span><span class="value"><?= htmlspecialchars($form['prefecture'] . $form['address']) ?></span></div>

      <!-- パスワード（非表示） -->
      <div class="row"><span class="label">パスワード</span><span class="value">セキュリティのため非表示</span></div>

      <!-- メールアドレス -->
      <div class="row"><span class="label">メールアドレス</span><span class="value"><?= htmlspecialchars($form['email']) ?></span></div>

      <!-- hidden inputs -->
      <input type="hidden" name="last_name" value="<?= htmlspecialchars($form['last_name']) ?>">
      <input type="hidden" name="first_name" value="<?= htmlspecialchars($form['first_name']) ?>">
      <input type="hidden" name="gender" value="<?= htmlspecialchars($form['gender']) ?>">
      <input type="hidden" name="prefecture" value="<?= htmlspecialchars($form['prefecture']) ?>">
      <input type="hidden" name="address" value="<?= htmlspecialchars($form['address']) ?>">
      <input type="hidden" name="password" value="<?= htmlspecialchars($form['password']) ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($form['email']) ?>">

      <div class="buttons">
        <button type="submit" class="btn submit">登録完了</button>
      </div>
    </form>

    <div class="buttons">
      <a href="member_regist.php" class="btn back">前へ戻る</a>
    </div>

  </div>
</body>

</html>