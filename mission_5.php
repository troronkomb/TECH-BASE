<html>
 <head>
  <title>mission5</title>
  <meta charset = "utf-8">
 </head>
 <body bgcolor=azure>
  <h1>掲示板</h1>
  <strong>バイトの愚痴を吐き出す掲示板</strong>
  <br>
  <h4>コメント欄</h4>
  <hr>


<?php
//データベースに接続
	$dsn = 'mysql:dbname="データベース名";host=localhost';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

	$sql = "CREATE TABLE IF NOT EXISTS mission5"
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY," //INT=数字の値しか許可しない、AUTO_INCREMENT=デフォルトで数字連番が入る、PRIMARY KEY=主キー。ユニークな値を持つ(=値が重複しない)列。
	. "name char(32)," //格納される列は常に32バイト、満たない場合は文字列の右側に空白が追加され、32バイトぴったりに調整される。
	. "comment TEXT,"
	. "date TEXT,"
	. "password TEXT"
	.");";
	$stmt = $pdo->query($sql); //SQL文をデータベースへ送信

//■■１□□名前とコメントの欄がどちらも埋まっていたら□□１■■

if (!empty($_POST['name']) && !empty($_POST['comment'])){

	//■■１-a□□パスワードが埋まっていたら□□１-a■■

	if (!empty($_POST['password'])){
		//名前と投稿とパスワードを受け取り
		$name = $_POST['name'];
		$comment = $_POST['comment'];
		$pass = $_POST['password'];
		//日付を設定
		$date = date("Y/m/d H:i:s");

		//■■１-a-a□□新規投稿なら□□１-a-a■■

		if (empty($_POST['editednumber'])){
			$sql = $pdo -> prepare("INSERT INTO mission5 (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
				//INSERT INTO table名(カラム名) VALUES(値a1,値a2,...),(値b1,値b2,...) : テーブルのそれぞれのカラムに値を挿入する
			$sql -> bindParam(':name', $name, PDO::PARAM_STR);
			$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
			$sql -> bindParam(':date', $date, PDO::PARAM_STR);
			$sql -> bindParam(':password', $pass, PDO::PARAM_STR);
				//bindParam:準備されたSQL文の中で対応するVALUE(パラメータ)に変数を参照として結びつける。
				//PDO::PARAM_STR:SQL CHAR, VARCHAR, または他の文字列データ型を表します。<-パラメータのデータ型を指定
				//好きな名前、好きな言葉は自分で決めること
			$sql -> execute();
				//execute:クエリを実行

		//■■１-a-a□□新規投稿なら□□１-a-a■■
		//■■１-a-b□□編集投稿なら□□１-a-b■■
		} else {
			$id = $_POST['editednumber']; //変更する投稿番号
			$sql = 'update mission5 set name=:name,comment=:comment,password=:password where id=:id';
			//update 表の名前 SET カラム名=値 WHERE 更新する行を特定する条件
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':name', $name, PDO::PARAM_STR);
			$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
			$stmt->bindParam(':password', $pass, PDO::PARAM_STR);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		}//■■１-a-b□□編集投稿なら□□１-a-b■■
	//■■１-a□□パスワードが埋まっていたら□□１-a■■

	//■■１-b□□パスワードが埋まっていなかったら□□１-b■■
	}else{
      $errorcode = "パスワードを設定して投稿してください(1-b)";
	}//■■１-b□□パスワードが埋まっていなかったら□□１-b■■

//■■１□□名前とコメントの欄がどちらも埋まっていたら□□１■■

//■■２□□名前とコメントが埋まっておらず、削除対象番号が埋まっていたら□□２■■
} elseif (!empty($_POST["deletenumber"])){
	$deletenumber = $_POST["deletenumber"];

	//■■２-a□□パスワードがあれば□□２-a■■
	if ((!empty($_POST['password']))){
		$deletepass = $_POST['password'];

		$sql = 'SELECT * FROM mission5';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll(); //結果データを全件まとめて配列で取得
		foreach ($results as $row){
			//■■２-a-a□□削除対象番号と一致する投稿番号があれば□□２-a-a■■
			if ($row['id'] == $deletenumber){
				$id = $deletenumber;
				//■■２-a-a-a□□パスワードが一致すれば□□２-a-a-a■■
				if ($row['password'] == $deletepass){
					$pass = $deletepass;
				}//■■２-a-a-a□□パスワードが一致すれば□□２-a-a-a■■
			}//■■２-a-a□□削除対象番号と一致する投稿番号があれば□□２-a-a■■
		}
		//■■２-a-b□□削除対象番号とパスワードが適正ならば□□２-a-b■■
		if (!empty($id) && !empty($pass)){
			$sql = 'delete from mission5 where id=:id AND password=:password';
			//DELETE FROM 表の名前 WHERE 削除する行を特定する条件1 AND 条件2
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':password', $pass, PDO::PARAM_INT);
			$stmt->execute();
		//■■２-a-b□□削除対象番号とパスワードが適正ならば□□２-a-b■■
		//■■２-a-c□□削除対象番号とパスワードが不適ならば□□２-a-c■■
		} else {
			$errorcode = "削除したい投稿番号が存在しないか、パスワードが一致しません(2-a-c)";
		}//■■２-a-c□□削除対象番号とパスワードが不適ならば□□２-a-c■■

	//■■２-a□□パスワードがあれば□□２-a■■
	//■■２-b□□パスワードがなければ□□２-b■■
	}else{
		$errorcode = "パスワードを入力してください(2-b)";
	}//■■２-b□□パスワードがなければ□□２-b■■

//■■２□□名前とコメントが埋まっておらず、削除対象番号が埋まっていたら□□２■■

//■■３□□名前とコメントと削除対象番号が埋まっておらず、編集対象番号が埋まっていたら□□３■■
} elseif (!empty($_POST["editnumber"])){
	$editnumber = $_POST["editnumber"];

	//■■３-a□□パスワードがあれば□□３-a■■
	if ((!empty($_POST['password']))){
		$editpass = $_POST['password'];

		$sql = 'SELECT * FROM mission5';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll(); //結果データを全件まとめて配列で取得
		foreach ($results as $row){
			//■■３-a-a□□編集対象番号と一致する投稿番号があれば□□３-a-a■■
			if ($row['id'] == $editnumber){
				//■■３-a-a-a□□パスワードが一致すれば□□３-a-a-a■■
				if ($row['password'] == $editpass){
					$editnum = $row['id'];
					$editname = $row['name'];
					$editcomment = $row['comment'];
					$editpassword = $row['password'];
				}//■■３-a-a-a□□パスワードが一致すれば□□３-a-a-a■■
			}//■■３-a-a□□編集対象番号と一致する投稿番号があれば□□３-a-a■■
		}
		//■■３-a-b□□編集対象番号とパスワードが不適ならば□□３-a-b■■
		if (empty($editnum) && empty($editname) && empty($editcomment)){
			$errorcode = "編集したい投稿番号が存在しないか、パスワードが一致しません(3-a-b)";
		}//■■３-a-b□□編集対象番号とパスワードが不適ならば□□３-a-b■■

    //■■３-a□□パスワードがあれば□□３-a■■
	//■■３-b□□パスワードがなければ□□３-b■■
	}else{
		$errorcode = "パスワードを入力してください(3-b)";
	}//■■3-b□□パスワードがなければ□□3-b■■

}//■■３□□名前とコメントと削除対象番号が埋まっておらず、編集対象番号が埋まっていたら□□３■■


$sql = 'SELECT * FROM mission5';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(); //結果データを全件まとめて配列で取得
foreach ($results as $row){
	//$rowの中にはテーブルのカラム名が入る
	echo $row['id'].' <> ';
	echo $row['name'].' <> ';
	echo $row['comment'].' <> ';
	echo $row['date'].'<br>';
	echo "<hr>";
}
?>
 <br>
<strong>エラー</strong><br>
 <textarea id = "error" name = "error" cols = "40" rows = "3" style = "overflow:auto;"><?php if(!empty($errorcode)){echo $errorcode;}else{echo "エラーはありません";} ?></textarea>
 <br>
  <h2>送信フォーム</h2>
   <form action = "/mission_5.php" method = "post">
    <div>
     <label for = "name">名前</label>
     <input type ="text" id = "name" name = "name" value = "<?php if(!empty($editname)){echo $editname;} ?>">
    </div>
    <div> 
     <label for = "comment">コメント</label> 
     <input type = "text" id = "comment" name="comment" value = "<?php if(!empty($editcomment)){echo $editcomment;} ?>">
    </div>
    <div>
     <label for = "password">パスワード</label> 
     <input type = "text" id = "password" name="password" value = "<?php if(!empty($editpassword)){echo $editpassword;} ?>">
    </div>
    <input type = "submit" value = "送信する">
    <div>
    <input type = "hidden" id = "editednumber" name="editednumber" value = "<?php if(!empty($editnum)){echo $editnum;} ?>">
    </div>
   </form>

   <hr>

  <h3>削除フォーム</h3>
  <form action = "/mission_5.php" method = "post">
   <div>
    <label for = "deletenumber">削除対象番号</label>
    <input type = "number" id = "deletenumber" name = "deletenumber" placeholder = "※半角">
   </div>
   <div>
     <label for = "password">パスワード</label> 
     <input type = "text" id = "password" name="password">
    </div>
   <input type = "submit" value = "削除">
  </form>

   <hr>

<h3>編集フォーム</h3>
  <form action = "/mission_5.php" method = "post">
   <div>
    <label for = "editnumber">編集対象番号</label>
    <input type = "number" id = "editnumber" name = "editnumber" placeholder = "※半角">
   </div>
   <div>
     <label for = "password">パスワード</label> 
     <input type = "text" id = "password" name="password">
    </div>
   <input type = "submit" value = "編集">
  </form>

 </body>
</html>
