<?php
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');
if (isset($_POST['body'])) {
  $replyto = isset($_POST['reply_to']) ? $_POST['reply_to'] :NULL;
  $insert_sth = $dbh->prepare("INSERT INTO posts(content) VALUES (:body)");
  $insert_sth->execute([
      ':body' => $_POST['body'],
  ]);
  $postid = $dbh->lastInsertId();
  if($replyto){
    $insert_reply=$dbh->prepare("INSERT INTO replies(post_id,reply_to,text) VALUES (:post_id.:reply_to,:text)");
    $insert_reply->execute([
      ':post_id' => $post_id,
      ':reply_to' => $reply_to,
      ':text' => $_POST['body'],
    ]);
  }
  header("HTTP/1.1 302 Found");
  header("Location: ./formtodbtest.php");
  return;
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$count_per_page = 5;
$skip_page = $count_per_page * ($page - 1);

$count_sth = $dbh->prepare("SELECT COUNT(*) FROM posts;");
$count_sth->execute();
$count_all = $count_sth->fetchColumn();

$select_sth = $dbh->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT :count_per_page OFFSET :skip_page");
$select_sth->bindParam(':count_per_page', $count_per_page, PDO::PARAM_INT);
$select_sth->bindParam(':skip_page', $skip_page, PDO::PARAM_INT);
$select_sth->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initail-scale=1.0">
    <title>掲示板</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
<div class="container">
<div class="header">
<h1>掲示板</h1>
</div>
<form method="POST" action="./formtodbtest.php">
<textarea name="body" placeholder="メッセージを入力してください"></textarea>
<button type="submit">送信</button>
</form>
<div class="pagination">
  <?= $page ?>ページ目
(全<?= floor($count_all / $count_per_page) + 1 ?>ページ中)
  </div>
  <div class="pagination">
  <?php if ($page > 1): ?>
  <a href="?page=<?= $page - 1 ?>">前へ</a>
  <?php endif; ?>
  
  <?php if ($count_all > $page * $count_per_page): ?>
  <a href="?page=<?= $page + 1 ?>">次へ</a>
  <?php endif; ?>
  </div>
  <?php
  if ($count_all == 0) {
    echo "まだ投稿はありません。";
  } else {
    if ($skip_page >= $count_all) {
      echo "このページは存在しません";
    } else {
      foreach ($select_sth as $data): ?>
        <div class="post">
        <p class="post-id"><?= $data['id']?></p>
        </div>
        <p class="post-date">日時：<?= $data['created_at']?></p>
        <p class="post-content"><?= nl2br(htmlspecialchars($data['content']))?></p>
        
        <?php endforeach; 
    }
  }
?>
</div>
</body>
</html>
