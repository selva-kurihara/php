<?php
session_start();

// エラーメッセージの初期化
$errorMessages = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (empty($_POST["password"])) {
    $errorMessages["password"] = 'パスワードが未入力です。';
  }

  if (empty($_POST["loginid"])) {
    $errorMessages["loginid"] = 'ログインIDが未入力です。';
  }

  if (empty($errorMessages)) {

    try {
      $dsn      = 'mysql:dbname=phpkadai;host=localhost;charset=utf8';
      $user     = 'kurihara';
      $password = 'uCmCLu2e8H';
      $pdo      = new PDO($dsn, $user, $password);
      $stmt = $pdo->prepare('SELECT * FROM administers WHERE login_id = :login_id AND password = :password AND deleted_at IS NULL');
      $stmt->bindValue(':login_id', $_POST['loginid'], PDO::PARAM_STR);
      $stmt->bindValue(':password', $_POST['password'], PDO::PARAM_STR);
      $stmt->execute();
      $administer = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($administer) {
        $_SESSION['administer'] = [
          'id' => $administer['id'],
          'name' => $administer['name']
        ];
        header("Location: top.php");
        exit;
      } else {
        $errorMessages["password"] = 'IDもしくはパスワードが間違っています';
      }
    } catch (PDOException $e) {
      error_log('DBエラー: ' . $e->getMessage());
      $errorMessages["password"] = 'システムエラーが発生しました。';
    }
  }
  $loginid = $_POST['loginid'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>ログインフォーム</title>
  <link rel="stylesheet" href="../stylesheet.css">
</head>

<body>

  <div class="login-box">
    <h2>ログイン</h2>

    <form action="" method="post">
      <div class="form-group">
        <label for="email">ログインID</label>
        <input type="text" id="loginid" name="loginid" value="<?= htmlspecialchars($loginid) ?>">
        <?php
        if (!empty($errorMessages["loginid"])) {
          echo '<p class="error">' . $errorMessages["loginid"] . '</p>';
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
      </div>
    </form>
  </div>

</body>

</html>