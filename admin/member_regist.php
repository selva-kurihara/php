<?php
// セッション開始
session_start();

// エラーメッセージの初期化
$errorMessages = [];

// 都道府県
$prefectures = [
  '北海道',
  '青森県',
  '岩手県',
  '宮城県',
  '秋田県',
  '山形県',
  '福島県',
  '茨城県',
  '栃木県',
  '群馬県',
  '埼玉県',
  '千葉県',
  '東京都',
  '神奈川県',
  '新潟県',
  '富山県',
  '石川県',
  '福井県',
  '山梨県',
  '長野県',
  '岐阜県',
  '静岡県',
  '愛知県',
  '三重県',
  '滋賀県',
  '京都府',
  '大阪府',
  '兵庫県',
  '奈良県',
  '和歌山県',
  '鳥取県',
  '島根県',
  '岡山県',
  '広島県',
  '山口県',
  '徳島県',
  '香川県',
  '愛媛県',
  '高知県',
  '福岡県',
  '佐賀県',
  '長崎県',
  '熊本県',
  '大分県',
  '宮崎県',
  '鹿児島県',
  '沖縄県'
];


// POSTを受け取った場合
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // エラーチェック
  if (empty($_POST["last_name"])) {
    $errorMessages["last_name"] = '姓が未入力です。';
  } else if (mb_strlen($_POST["last_name"]) > 20) {
    $errorMessages["last_name"] = '姓は20文字以内で入力してください。';
  } else if (!preg_match('/^[ぁ-んァ-ン一-龥々ー]+$/u', $_POST["last_name"])) {
    $errorMessages["last_name"] = '姓にはひらがな・カタカナ・漢字以外の文字は使用できません。';
  }

  if (empty($_POST["first_name"])) {
    $errorMessages["first_name"] = '名が未入力です。';
  } else if (mb_strlen($_POST["first_name"]) > 20) {
    $errorMessages["first_name"] = '名は20文字以内で入力してください。';
  } else if (!preg_match('/^[ぁ-んァ-ン一-龥々ー]+$/u', $_POST["first_name"])) {
    $errorMessages["first_name"] = '名にはひらがな・カタカナ・漢字以外の文字は使用できません。';
  }

  if (empty($_POST["gender"])) {
    $errorMessages["gender"] = '性別が未入力です。';
  } else if ($_POST["gender"] !== '1' && $_POST["gender"] !== '2') {
    $errorMessages["gender"] = '性別の値が不正です。';
  }

  if (empty($_POST["prefecture"])) {
    $errorMessages["prefecture"] = '都道府県が未選択です。';
  } else if (!in_array($_POST["prefecture"], $prefectures, true)) {
    $errorMessages["prefecture"] = '都道府県の値が不正です。';
  }

  if (mb_strlen($_POST["address"]) > 100) {
    $errorMessages["address"] = 'アドレスは100文字以内で入力してください。';
  }

  if (empty($_POST["password"])) {
    $errorMessages["password"] = 'パスワードが未入力です。';
  } else if (!preg_match('/^[a-zA-Z0-9]{8,20}$/', $_POST["password"])) {
    $errorMessages["password"] = 'パスワードは半角英数字8〜20文字で入力してください。';
  }

  if (empty($_POST["password_confirm"])) {
    $errorMessages["password_confirm"] = '確認用パスワードが未入力です。';
  } else if ($_POST["password"] !== $_POST["password_confirm"]) {
    $errorMessages["password_confirm"] = 'パスワードと確認用パスワードが一致しません。';
  }

  if (empty($_POST["email"])) {
    $errorMessages["email"] = 'メールアドレスが未入力です。';
  } else if (mb_strlen($_POST["email"]) > 200) {
    $errorMessages["email"] = 'メールアドレスは200文字以内で入力してください。';
  } else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    $errorMessages["email"] = 'メールアドレスの形式が正しくありません。';
  }

  if (empty($errorMessages["email"])) {
    try {
      $dsn      = 'mysql:dbname=phpkadai;host=localhost;charset=utf8';
      $user     = 'kurihara';
      $password = 'uCmCLu2e8H';
      $pdo      = new PDO($dsn, $user, $password);
      $stmt     = $pdo->prepare('SELECT COUNT(*) FROM members WHERE email = :email');
      $stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
      $stmt->execute();
      $count = (int)$stmt->fetchColumn();
      if ($count > 0) {
        $errorMessages["email"] = 'このメールアドレスは既に使われています。';
      }
    } catch (PDOException $e) {
      error_log('DBエラー: ' . $e->getMessage());
      $errorMessages["email"] = 'システムエラーが発生しました。';
    }
  }

  // 「確認へ進む」ボタンが押された場合のみ、セッション保存＋リダイレクト
  if (isset($_POST['action']) && $_POST['action'] === 'confirm' && empty($errorMessages)) {
    $_SESSION['form'] = $_POST;
    $_SESSION['form']['type'] = 'regist';
    header("Location: member_confirm.php");
    exit;
  }
}

// セッションからフォーム値を取得
$last_name    = isset($_POST['last_name']) ? $_POST['last_name'] : '';
$first_name   = isset($_POST['first_name']) ? $_POST['first_name'] : '';
$gender       = isset($_POST['gender']) ? $_POST['gender'] : '';
$prefecture   = isset($_POST['prefecture']) ? $_POST['prefecture'] : '';
$address      = isset($_POST['address']) ? $_POST['address'] : '';
$password     = '';
$password_confirm = '';
$email        = isset($_POST['email']) ? $_POST['email'] : '';

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>会員登録</title>
  <link rel="stylesheet" href="../stylesheet.css">
</head>

<body>
  <div class="container">
    <h2>会員登録</h2>

    <a href="member.php" class="btn-link">一覧へ戻る</a>

    <form action="member_regist.php" method="post">

      <div class="form-row">
        <label class="form-label">ID</label>
        <span class="form-note">登録後に自動採番</span>
      </div>

      <label>氏名</label>
      <div class="name-group">
        <label>姓</label> <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>">
        <?php
        if (!empty($errorMessages["last_name"])) {
          echo '<p class="error">' . $errorMessages["last_name"] . '</p>';
        }
        ?>

        <label>名</label> <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>">
        <?php
        if (!empty($errorMessages["first_name"])) {
          echo '<p class="error">' . $errorMessages["first_name"] . '</p>';
        }
        ?>
      </div>

      <div class="gender-group">
        <label>性別</label>
        <label class="gender"><input type="radio" name="gender" value="1" <?= $gender === '1' ? 'checked' : '' ?>> 男性</label>
        <label class="gender"><input type="radio" name="gender" value="2" <?= $gender === '2' ? 'checked' : '' ?>> 女性</label>
        <?php
        if (!empty($errorMessages["gender"])) {
          echo '<p class="error">' . $errorMessages["gender"] . '</p>';
        }
        ?>
      </div>

      <div class="prefecture-group">
        <label>住所</label>
        都道府県
        <select name="prefecture">
          <option value="" <?php echo $prefecture === '' ? 'selected' : '' ?>>選択してください▼</option>
          <?php foreach ($prefectures as $p): ?>
            <option value="<?php echo htmlspecialchars($p) ?>" <?php echo $prefecture === $p ? 'selected' : '' ?>>
              <?php echo htmlspecialchars($p) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php
        if (!empty($errorMessages["prefecture"])) {
          echo '<p class="error">' . $errorMessages["prefecture"] . '</p>';
        }
        ?>
      </div>
      <label>それ以降の住所</label>
      <input type="text" name="address" value="<?= htmlspecialchars($address) ?>">
      <?php
      if (!empty($errorMessages["address"])) {
        echo '<p class="error">' . $errorMessages["address"] . '</p>';
      }
      ?>

      <label>パスワード</label>
      <input type="password" name="password" value="">
      <?php
      if (!empty($errorMessages["password"])) {
        echo '<p class="error">' . $errorMessages["password"] . '</p>';
      }
      ?>

      <label>パスワード確認</label>
      <input type="password" name="password_confirm" value="">
      <?php
      if (!empty($errorMessages["password_confirm"])) {
        echo '<p class="error">' . $errorMessages["password_confirm"] . '</p>';
      }
      ?>

      <label>メールアドレス</label>
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
      <?php
      if (!empty($errorMessages["email"])) {
        echo '<p class="error">' . $errorMessages["email"] . '</p>';
      }
      ?>

      <input type="hidden" name="action" value="confirm">
      <div class="buttons">
        <button type="submit" class="btn">確認画面へ</button>
      </div>

    </form>

  </div>
</body>

</html>