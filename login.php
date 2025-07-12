<?php
session_start();

// エラーメッセージの初期化
$errorMessages = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (empty($_POST["password"])) {
    $errorMessages["password"] = 'パスワードが未入力です。';
  }

  if (empty($_POST["email"])) {
    $errorMessages["email"] = 'メールアドレスが未入力です。';
  }


  try {
    $dsn      = 'mysql:dbname=phpkadai;host=localhost;charset=utf8';
    $user     = 'kurihara';
    $password = 'uCmCLu2e8H';
    $pdo      = new PDO($dsn, $user, $password);
    $stmt = $pdo->prepare('SELECT * FROM members WHERE email = :email AND password = :password');
    $stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
    $stmt->bindValue(':password', $_POST['password'], PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      $_SESSION['username'] = $user['name_sei'] . $user['name_mei']; 
      header("Location: logout.php");
      exit;
    } else {
      $errorMessages["email"] = 'IDもしくはパスワードが間違っています';
    }
  } catch (PDOException $e) {
    error_log('DBエラー: ' . $e->getMessage());
    $errorMessages["email"] = 'システムエラーが発生しました。';
  }

  $email = $_POST['email'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>ログインフォーム</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>

<body>

  <div class="login-box">
    <h2>ログイン</h2>

    <form action="login.php" method="post">
      <div class="form-group">
        <label for="email">メールアドレス（ID）</label>
        <input type="text" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
        <?php
        if (!empty($errorMessages["email"])) {
          echo '<p class="error">' . $errorMessages["email"] . '</p>';
        }
        ?>
      </div>
      <div class=" form-group">
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password">
        <?php
        if (!empty($errorMessages["password"])) {
          echo '<p class="error">' . $errorMessages["password"] . '</p>';
        }
        ?>
      </div>


      <div class="button-group">
        <button type="submit" class="btn btn-login">ログイン</button>
        <button type="button" class="btn btn-top" onclick="location.href='logout.php'">トップに戻る</button>
      </div>
    </form>
  </div>

</body>

</html>