<?php
session_start();

$form = $_SESSION['form'];

// トークン生成（1回目の表示時のみ）
if (!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(16));
}
$token = $_SESSION['token'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["regist_complete"])) {

  // トークンチェック＆二重送信スキップ
  if (!isset($_POST["token"]) || $_POST["token"] !== $_SESSION['token']) {
    // トークンが消費済み＝二重送信と判断 → 完了画面にリダイレクト
    header('Location: member.php');
    exit;
  }

  // トークン無効化（使い捨て）
  unset($_SESSION['token']);
  
  try {
    // DB接続
    $dsn = 'mysql:dbname=phpkadai;host=localhost;charset=utf8;';
    $user = 'kurihara';
    $password = 'uCmCLu2e8H';
    $pdo = new PDO($dsn, $user, $password);

    // 新規登録
    $prepare = $pdo->prepare('INSERT INTO members (name_sei, name_mei, gender, pref_name, address, password, email, created_at) VALUES (:name_sei, :name_mei, :gender, :pref_name, :address, :password, :email, NOW())');
    $prepare->bindValue(':name_sei', $form['last_name'], PDO::PARAM_STR);
    $prepare->bindValue(':name_mei', $form['first_name'], PDO::PARAM_STR);
    $prepare->bindValue(':gender', $form['gender'], PDO::PARAM_INT);
    $prepare->bindValue(':pref_name', $form['prefecture'], PDO::PARAM_STR);
    $prepare->bindValue(':address', $form['address'], PDO::PARAM_STR);
    $prepare->bindValue(':password', $form['password'], PDO::PARAM_STR);
    $prepare->bindValue(':email', $form['email'], PDO::PARAM_STR);
    $prepare->execute();

    // 完了後にセッションのformも破棄
    unset($_SESSION['form']);

    header('Location: member.php');
    exit;
  } catch (PDOException $e) {
    error_log('DBエラー: ' . $e->getMessage());
    echo 'DBエラーが発生しました。';
  }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_complete"])) {

  // トークンチェック＆二重送信スキップ
  if (!isset($_POST["token"]) || $_POST["token"] !== $_SESSION['token']) {
    // トークンが消費済み＝二重送信と判断 → 完了画面にリダイレクト
    header('Location: member.php');
    exit;
  }

  // トークン無効化（使い捨て）
  unset($_SESSION['token']);

  try {
    // DB接続
    $dsn = 'mysql:dbname=phpkadai;host=localhost;charset=utf8;';
    $user = 'kurihara';
    $password = 'uCmCLu2e8H';
    $pdo = new PDO($dsn, $user, $password);

    $prepare = $pdo->prepare('
      UPDATE members 
      SET 
        name_sei   = :name_sei,
        name_mei   = :name_mei,
        gender     = :gender,
        pref_name  = :pref_name,
        address    = :address,
        password   = :password,
        email      = :email
      WHERE id = :id
    ');

    $prepare->bindValue(':name_sei', $form['last_name'], PDO::PARAM_STR);
    $prepare->bindValue(':name_mei', $form['first_name'], PDO::PARAM_STR);
    $prepare->bindValue(':gender', $form['gender'], PDO::PARAM_INT);
    $prepare->bindValue(':pref_name', $form['prefecture'], PDO::PARAM_STR);
    $prepare->bindValue(':address', $form['address'], PDO::PARAM_STR);
    $prepare->bindValue(':password', $form['password'], PDO::PARAM_STR);
    $prepare->bindValue(':email', $form['email'], PDO::PARAM_STR);
    $prepare->bindValue(':id', $form['id'], PDO::PARAM_INT); // ID指定を忘れずに！

    $prepare->execute();

    unset($_SESSION['form']);
    header('Location: member.php');
    exit;
  } catch (PDOException $e) {
    error_log('DBエラー: ' . $e->getMessage());
    echo 'DBエラーが発生しました。';
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>確認画面</title>
  <link rel="stylesheet" href="../stylesheet.css">
</head>

<body>
  <div class="container">
    <?php if ($form['type'] == 'regist'): ?>
      <h2>会員登録</h2>
    <?php else: ?>
      <h2>会員編集</h2>
    <?php endif; ?>

    <a href="member.php" class="btn-link">一覧へ戻る</a>
    <form action="member_confirm.php" method="post">
      <div class="row">
        <span class="label">ID</span>
        <?php if ($form['type'] == 'regist'): ?>
          <span class="note">登録後に自動採番</span>
        <?php else: ?>
          <span class="note"><?= htmlspecialchars($form['id']) ?></span>
        <?php endif; ?>
      </div>
      <!-- 氏名 -->
      <div class="row"><span class="label">氏名</span><span class="value"><?= htmlspecialchars($form['last_name'] . '　' . $form['first_name']) ?></span></div>

      <!-- 性別 -->
      <div class="row">
        <span class="label">性別</span>
        <span class="value">
          <?php
          if ($form['gender'] == 1) {
            echo '男性';
          } else {
            echo '女性';
          }
          ?>
        </span>
      </div>

      <!-- 住所 -->
      <div class="row"><span class="label">住所</span><span class="value"><?= htmlspecialchars($form['prefecture'] . $form['address']) ?></span></div>

      <!-- パスワード（非表示） -->
      <div class="row"><span class="label">パスワード</span><span class="value">セキュリティのため非表示</span></div>

      <!-- メールアドレス -->
      <div class="row"><span class="label">メールアドレス</span><span class="value"><?= htmlspecialchars($form['email']) ?></span></div>

      <div class="buttons">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <?php if ($form['type'] == 'regist'): ?>
          <button type="submit" class="btn submit" name="regist_complete">登録完了</button>
        <?php else: ?>
          <button type="submit" class="btn submit" name="edit_complete">編集完了</button>
        <?php endif; ?>
      </div>
    </form>

  </div>
</body>

</html>