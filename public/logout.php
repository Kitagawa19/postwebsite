<?php
session_start();
$_SESSION=[];
if(isset($_COOKIE["PHPSESSID"])){
  setcookie(session_name(),'',time()-12000);
}
session_destroy();
?>
<h1>ログアウトしました</h1>
<p><a href="/login.php">ログインページに戻る</a></p>
