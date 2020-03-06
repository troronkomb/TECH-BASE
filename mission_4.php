<?php		
//用語
	//カラム：表の列や項目のこと（https://wa3.i-3-i.info/word1174.html）
	//クエリ：データベースに対する「お問い合わせ」
//注意
	//queryとexecuteの違い：https://infra.salmon0852.com/php-pdo-query/

	//4-2以降でも毎回接続は必要。
	//$dsnの式の中にスペースを入れないこと！

	// 【データベース設定】
	// ・データベース名：tb210877db
	// ・ユーザー名：tb-210877
	// ・パスワード：配布用にはかかない
	// の学生の場合：

//1.データベースに接続する(接続)
	//https://blog.codecamp.jp/programming-php-pdo-mysql-1
	$dsn = 'mysql:dbname=tb210877db;host=localhost';
	$user = 'tb-210877';
	$password = 'パスワード'; //配布用には書かない
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

	/*以下注釈のためコード内に含める必要はありません。
	array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)とは、データベース操作で発生したエラーを
	警告として表示してくれる設定をするための要素です。
	デフォルトでは、PDOのデータベース操作で発生したエラーは何も表示されません。
	その場合、不具合の原因を見つけるのに時間がかかってしまうので、このオプションはつけておきましょう。*/
	
//2.テーブルを作成する
	//https://gray-code.com/php/create-table-by-using-pdo/
    //文中の指定の意味：https://techa1008.com/rocket-note/2017/09/20/create-a-table-in-the-database-and-insert-and-get-data-with-php/
    //charとvarcharの違い：https://qiita.com/oseibo/items/c589430bdb00c6ab4922
    //SQL文をデータベースに送信する方法：https://www.javadrive.jp/php/pdo/index7.html
	$sql = "CREATE TABLE IF NOT EXISTS tbtest"
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY," //INT=数字の値しか許可しない、AUTO_INCREMENT=デフォルトで数字連番が入る、PRIMARY KEY=主キー。ユニークな値を持つ(=値が重複しない)列。
	. "name char(32)," //格納される列は常に32バイト、満たない場合は文字列の右側に空白が追加され、32バイトぴったりに調整される。
	. "comment TEXT"
	.");";
	$stmt = $pdo->query($sql); //SQL文をデータベースへ送信
	
	/*以下注釈のためコード内に含める必要はありません。
	IF NOT EXISTSを入れないと２回目以降にこのプログラムを呼び出した際に、
	SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'tbtest' already exists
	という警告が発生します。これは、既に存在するテーブルを作成しようとした際に発生するエラーです。*/

//3.作成したテーブルを一覧で表示させる
	$sql ='SHOW TABLES'; //テーブル名を一覧表示
	$result = $pdo -> query($sql);
	foreach ($result as $row){
		echo $row[0];
		echo '<br>';
	}
	//var_dump($row);
	//結果：array(2) { ["Tables_in_tb210877db"]=> string(6) "tbtest" [0]=> string(6) "tbtest" }
	echo "<hr>";


//4.テーブルの中身を表示(CREATE) 
	//https://phpjavascriptroom.com/?t=mysql&p=default
	//https://www.dbonline.jp/mysql/table/index1.html
	$sql ='SHOW CREATE TABLE tbtest'; //tbtestのテーブルを作って表示
	$result = $pdo -> query($sql);
	foreach ($result as $row){
		echo $row[1];
	}

	//var_dump($row);
	//実行結果
		//array(4) { ["Table"]=> string(6) "tbtest" 
		//[0]=> string(6) "tbtest" 
		//["Create Table"]=> string(174) "CREATE TABLE `tbtest` ( 
			//`id` int(11) NOT NULL AUTO_INCREMENT, :IDカラムにはNULLは格納できず、連番で数字が入る
			//`name` char(32) DEFAULT NULL, ：nameカラムのデフォルト値をNULLにする
			//`comment` text, :commentカラムにはテキストを入れる
			//PRIMARY KEY (`id`) ) ：主キーをIDカラムにする
			//ENGINE=InnoDB :デフォルトのストレージエンジン(テーブルにデータの読み書きをするプログラム)
			//DEFAULT CHARSET=utf8mb4" ：デフォルトの文字セット
		//[1]=> string(174) "CREATE TABLE `tbtest` ( `id` int(11) NOT NULL AUTO_INCREMENT, `name` char(32) DEFAULT NULL, `comment` text, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4" }
		//イメージ
		/*+--------+--------------+------+-----+---------+----------------+
		  | Field  | Type         | Null | Key | Default | Extra          |
		  +--------+--------------+------+-----+---------+----------------+
		  | id     | int(11)      |      | PRI |         | auto_increment |
		  | name   | char(32)     | YES  |     | NULL    |                |
		  | comment| text         | YES  |     |         |                |
		  +--------+--------------+------+-----+---------+----------------+
		*/
	echo "<hr>";


//5.作成したテーブルにデータを入力する(INSERT)
	//https://www.sejuku.net/blog/82957
	//bindParamの引数（:nameなど）は4-2でどんな名前のカラムを設定したかで変える必要がある。
	//なお、意図通り入力が出来ているかどうかは4-6にて確認できる。
	//prepareメソッドは、データベースに送信しようとしているSQL文の準備をする
	$sql = $pdo -> prepare("INSERT INTO tbtest (name, comment) VALUES (:name, :comment)");
		//INSERT INTO table名(カラム名) VALUES(値a1,値a2,...),(値b1,値b2,...) : テーブルのそれぞれのカラムに値を挿入する
	$sql -> bindParam(':name', $name, PDO::PARAM_STR);
	$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
		//bindParam:準備されたSQL文の中で対応するVALUE(パラメータ)に変数を参照として結びつける。
		//PDO::PARAM_STR:SQL CHAR, VARCHAR, または他の文字列データ型を表します。<-パラメータのデータ型を指定
	$name = 'IKKO';
	$comment = 'どんだけー'; 
		//好きな名前、好きな言葉は自分で決めること
	$sql -> execute();
		//execute:クエリを実行


//6.入力したデータを表示する(SELECT)
	//https://bituse.info/php/37
	//$rowの添字（[ ]内）は4-2でどんな名前のカラムを設定したかで変える必要がある。
	$sql = 'SELECT * FROM tbtest';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll(); //結果データを全件まとめて配列で取得
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		echo $row['id'].',';
		echo $row['name'].',';
		echo $row['comment'].'<br>';
	echo "<hr>";
	}

	/*以下注釈のためコード内に含める必要はありません。
	$result = $pdo->query($sql);を利用する方法もありますが、変数の値を直接SQL文に埋め込むのはとても危険！
	やめましょう。
	詳しく知りたい人はSQLインジェクションで検索を！*/

//7.入力したデータを編集する(UPDATE)
	//https://www.atmarkit.co.jp/ait/articles/1210/23/news008_2.html
	//bindParamの引数（:nameなど）は4-2でどんな名前のカラムを設定したかで変える必要がある。
	$id = 2; //変更する投稿番号
	$name = "IKKO";
	$comment = "背負い投げーー"; //変更したい名前、変更したいコメントは自分で決めること
	$sql = 'update tbtest set name=:name,comment=:comment where id=:id';
	//update 表の名前 SET カラム名=値 WHERE 更新する行を特定する条件
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':name', $name, PDO::PARAM_STR);
	$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->execute();

	$sql = 'SELECT * FROM tbtest';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll(); //結果データを全件まとめて配列で取得
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		echo $row['id'].',';
		echo $row['name'].',';
		echo $row['comment'].'<br>';
	echo "<hr>";
	}


//8.入力したデータを削除する(DELETE)
	//https://www.atmarkit.co.jp/ait/articles/1210/23/news008_2.html
	$id = 4;
	$sql = 'delete from tbtest where id=:id';
	//DELETE FROM 表の名前 WHERE 削除する行を特定する条件
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->execute();

	$sql = 'SELECT * FROM tbtest';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll(); //結果データを全件まとめて配列で取得
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		echo $row['id'].',';
		echo $row['name'].',';
		echo $row['comment'].'<br>';
	echo "<hr>";
	}
?>
