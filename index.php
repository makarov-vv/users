<?php
	session_start();
	$pagen=1;
	$sort = 'no';
	if(isset($_SESSION['sort'])){
		$sort = $_SESSION['sort'];
	}
	if(isset($_POST['page'])){
		$pagen=$_POST['page'];
		$_SESSION['page']=$_POST['page'];
	} else {
		$pagen = $_SESSION['page'];
	}

?>  
<!DOCTYPE html>
<html>
<head>
	<title>БД</title>
</head>
<body>
	<?php
	$link = mysqli_connect('192.168.0.5', 'root', 'root', 'testbd');
	if(mysqli_connect_errno()){
		echo 'Error '. mysqli_connect_errno() . ' ' . mysqli_connect_error().'<br>';
	} else {
		$link->set_charset('utf8');
		echo 'DB connected'.'<br>';
	}

	?>
	
	<br>
	<form method="POST" action="./">		
		<input type="submit" name="importxml" value="Импорт из XML"/>
		<input type="submit" name="importcsv" value="Импорт из CSV"/>
	</form>
	<br>
	
	
	<?php

	if(isset($_POST['importxml'])){
		$pagen=1;	
		importXML($link, 'users.xml');
	}
	if(isset($_POST['importcsv'])){
		$pagen=1;	
		importCSV($link, 'CSVTest.csv');
	}
	echo '<br>';

	if(isset($_POST['sort_asc'])){
		$_SESSION['sort'] = "asc";
		$_SESSION['field'] = $_POST['field'];
		$_SESSION['page'] = 1;
		$pagen=1;
		selectDataSort($link, $_SESSION['field'], true, $pagen);
	} else if(isset($_POST['sort_desc'])){	
		$_SESSION['sort'] = "desc";
		$_SESSION['field'] = $_POST['field'];	
		$_SESSION['page'] = 1;
		$pagen=1;
		selectDataSort($link, $_SESSION['field'], false, $pagen);
	} else if(isset($_POST['sort_no'])){
		$_SESSION['field'] = "";	
		$_SESSION['sort'] = "no";
		$_SESSION['page'] = 1;
		$pagen=1;	
		selectData($link, $pagen);
	} else {
		if($sort=='asc'){
			$_SESSION['sort'] = "asc";
			if($pagen)
				selectDataSort($link, $_SESSION['field'], true, $pagen);
			else 
				selectDataSort($link, $_SESSION['field'], true, 1);
		} else if ($sort=='desc'){
			$_SESSION['sort'] = "desc";
			if($pagen)	
				selectDataSort($link, $_SESSION['field'], false, $pagen);
			else
				selectDataSort($link, $_SESSION['field'], true, 1);
		} else{
			$_SESSION['sort'] = "no";
			if($pagen)
				selectData($link, $pagen);
			else
				selectData($link, 1);
		}
	}

	?>

	<form method="POST" action="">
		<select name="field">
			<?php 
				if ($_SESSION['field']=='Login')
					echo "<option selected value=\"Login\">Login</option>";
				else 
					echo "<option value=\"Login\">Login</option>";
				if ($_SESSION['field']=='Password')
					echo "<option selected value=\"Password\">Password</option>";
				else 
					echo "<option value=\"Password\">Password</option>";
				if ($_SESSION['field']=='Name')
					echo "<option selected value=\"Name\">Name</option>";
				else 
					echo "<option value=\"Name\">Name</option>";
				if ($_SESSION['field']=='Email')
					echo "<option selected value=\"Email\">Email</option>";
				else 
					echo "<option value=\"Email\">Email</option>";
			?>			
		</select>
		<input type="submit" name="sort_asc" value="Сортировать по возрастанию"/>
		<input type="submit" name="sort_desc" value="Сортировать по убыванию"/>		
		<input type="submit" name="sort_no" value="Отменить сортировку"/>		
	<?php
	$query = "SELECT COUNT(*) FROM users";
	$statement = mysqli_prepare($link, $query);
	$statement->execute();
	$statement->bind_result($numrows);
	$statement->fetch();
	$statement->close();
	echo "<select name=\"page\">";
	for($i=0; $i<$numrows/20; $i++){
		$j=$i+1;
		if($i==$pagen-1)
			echo "<option selected value=\"$j\">".$j."</option>";
		else
	  		echo "<option value=\"$j\">".$j."</option>";
	}
	echo "</select><input type=\"submit\" name=\"goto\" value=\"Перейти\"/> ";

	?>
	</form>



	<form method="POST" action="/add.php">
		<br>
		<input name="login"/ type="text" placeholder="Логин">
		<input name="password"/ type="text" placeholder="Пароль">
		<input name="name"/ type="text" placeholder="Имя">
		<input name="email"/ type="text" placeholder="Электронная почта">		
		<input type="submit" name="add" value="Добавить"/>
	</form>
	<?php 
		$id = $_GET['id'];
		if($id!=""&&$_SESSION['state']!="importing"){
			$query = "SELECT Login, Password, Name, Email FROM users WHERE Id = $id";
			$statement = mysqli_prepare($link, $query);
			$statement->execute();
			$statement->bind_result($data[0], $data[1], $data[2], $data[3]);
			$statement->fetch();
			$statement->close();
			if($data[0])
			{printf("<form method=\"POST\" action=\"edit.php?id=%s\">
			<br>
			<input name=\"editlogin\"/ type=\"text\" placeholder=\"Логин\" value=\"%s\">
			<input name=\"editpassword\"/ type=\"text\" placeholder=\"Пароль\" value=\"%s\">
			<input name=\"editname\"/ type=\"text\" placeholder=\"Имя\" value=\"%s\">
			<input name=\"editemail\"/ type=\"text\" placeholder=\"Электронная почта\" value=\"%s\">
			<input type=\"submit\" name=\"edit\" value=\"Изменить\"/></form>",	
			$id,$data[0], $data[1],$data[2],$data[3]);
		}
		}
	$_SESSION['page'] = $pagen;
	displayInfo($_SESSION['mes']);
	$_SESSION['mes']="";
	$_SESSION['state']="";
	?>
	
		
		
	
</body>
</html>

<?php

function addData($link, $table, $data){
	$query = "INSERT INTO $table (Login, Password, Name, Email) VALUES (?,?,?,?)";
	$statement = mysqli_prepare($link, $query);
	$statement->bind_param('ssss', $data[0], $data[1], $data[2], $data[3]);
	$statement->execute();
	$statement->close();
}

function selectDataSort($link, $field, $desc, $page){
	$offset = 20 * ($page-1);
	if($desc){
		$query = "SELECT Id, Login, Password, Name, Email FROM users ORDER BY $field ASC LIMIT $offset, 20";
	} else {
		$query = "SELECT Id, Login, Password, Name, Email FROM users ORDER BY $field DESC LIMIT $offset, 20";
	}
	$statement = mysqli_prepare($link, $query);
	$statement->execute();
	$result = array();
	$statement->bind_result($result[0],$result[1],$result[2],$result[3],$result[4]);
	echo "<table><tr><th>Login</th><th>Password</th><th>Name</th><th>Email</th><th></th></tr>";
	while ($statement->fetch()) {
        printf ("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href='/?id=%s'>Изменить</a></td></tr>",$result[1],$result[2],$result[3],$result[4], $result[0]);
    }
    echo "</table>";
    $statement->close();
    displayInfo('Выбраны элементы, страница '.$page);     
}

function selectData($link, $page){
	$offset = 20 * ($page-1);
	$query = "SELECT Id, Login, Password, Name, Email FROM users LIMIT $offset, 20";
	$statement = mysqli_prepare($link, $query);
	$statement->execute();
	$result = array();
	$statement->bind_result($result[0],$result[1],$result[2],$result[3], $result[4]);
	echo "<table><tr><th>Login</th><th>Password</th><th>Name</th><th>Email</th><th></th></tr>";
	while ($statement->fetch()) {
        printf ("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href='/?id=%s'>Изменить</a></td></tr>",$result[1],$result[2],$result[3],$result[4], $result[0]);
    }
    echo "</table>";
    $statement->close();
    displayInfo('Выбраны элементы, страница '.$page);     
}

function importXML($link, $file){
	$_SESSION['page']=1;
	$usersxml = simplexml_load_file($file);
	$i = 0;
	$j = 0;
	$users = array();
	$commonUsers = array();
	foreach($usersxml as $user){
		$users[$i]['login'] = $user->login;
		$users[$i]['password'] = $user->password;
		$users[$i]['name'] = $user->login;
		$users[$i]['email'] = $user->login . '@example.com';


		$query = "SELECT Login FROM users WHERE Login=?";
		$statement = mysqli_prepare($link, $query);
		$statement->bind_param('s', $user->login);
		$statement->execute();
		$statement->bind_result($result);
		$statement->fetch();
		if($result){
			$commonUsers[$j]['login'] = $result;
			$commonUsers[$j]['newpassword'] = $user->password;
			$j++;
		}
		$statement->close();
		$i++;
	}

	$query_count = "SELECT COUNT(*) FROM users";
	$statement = mysqli_prepare($link, $query_count);
	$statement->execute();
	$statement->bind_result($numrows);
	$statement -> fetch();
	$statement->close();


	$query_delete = "DELETE FROM users WHERE ";


	if(count($commonUsers)>0){
		if(count($commonUsers)==1){
			$query_delete .= "Login <> \"" .$commonUsers[0]['login'] ."\"";
		} else if (count($commonUsers)>1){
			$query_delete .= "Login <> \"" .$commonUsers[0]['login'] ."\"";
			for($i=1; $i<count($commonUsers); $i++){
				$query_delete = $query_delete . " and Login <> \"" .$commonUsers[$i]['login'] ."\"";
			}
		}
		$statement = mysqli_prepare($link, $query_delete);
		$statement->execute();
		$statement->close();
	}
	$deleted = $numrows - count($commonUsers);

	//Удаяляем все кроме найденных DELETE FROM users WHERE Login <> val1 and Login <> val2 and ...
	//echo $query_delete;

	//Обновляем данные
	for($i=0; $i<count($commonUsers); $i++){
		$login = $commonUsers[$i]['login'];
		$password = $commonUsers[$i]['newpassword'];
		$query_update = "UPDATE users SET Password=\"$password\" WHERE Login = \"$login\"";
      	$statement = mysqli_prepare($link, $query_update);      	
		$statement->execute();
		$statement->close();
	}
	
	for($i=0; $i<count($users); $i++){
		$query_insert = "INSERT INTO users (Login, Password, Name, Email) SELECT * FROM (SELECT \"". $users[$i]['login'] ."\" AS l, \"". $users[$i]['password'] ."\" AS p, \"". $users[$i]['name'] ."\" AS n, \"". $users[$i]['email'] ."\" AS e) AS tmp WHERE NOT EXISTS (SELECT Login FROM users WHERE Login = \"". $users[$i]['login'] ."\") LIMIT 1";
		$statement = mysqli_prepare($link, $query_insert);

		$statement->execute();
		$statement->close();  
	}
	$_SESSION['page']=1;
	displayInfo('<br>Импортирован файл. Обработано записей - '. ($numrows+count($users)-count($commonUsers)) . '; Обновлено записей - '. count($commonUsers). '; Удалено записей - '. $deleted . '.<br>');

}

function importCSV($link, $file){
	$_SESSION['page']=1;
	$users = array();
	$commonUsers = array();
	$i = 0;
	$j = 0;

	if (($handle = fopen($file, "r")) !== FALSE) {
    	while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
    		if ($i>0){
    			$num = count($data); 
        		$users[$i]['login'] = $data[0];
				$users[$i]['password'] = $data[1];
				$users[$i]['name'] = $data[0];
				$users[$i]['email'] =$data[0] . '@example.com';

				$query = "SELECT Login FROM users WHERE Login=?";
				$statement = mysqli_prepare($link, $query);
				$statement->bind_param('s', $users[$i]['login']);
				$statement->execute();
				$statement->bind_result($result);
				$statement->fetch();
				if($result){
					$commonUsers[$j]['login'] = $result;
					$commonUsers[$j]['newpassword'] = $users[$i]['password'];
					$j++;
				}
				$statement->close();
			}
			$i++;
    	}
    }
    fclose($handle);

	$query_count = "SELECT COUNT(*) FROM users";
	$statement = mysqli_prepare($link, $query_count);
	$statement->execute();
	$statement->bind_result($numrows);
	$statement -> fetch();
	$statement->close();


	$query_delete = "DELETE FROM users WHERE ";


	if(count($commonUsers)>0){
		if(count($commonUsers)==1){
			$query_delete .= "Login <> \"" .$commonUsers[0]['login'] ."\"";
		} else if (count($commonUsers)>1){
			$query_delete .= "Login <> \"" .$commonUsers[0]['login'] ."\"";
			for($i=1; $i<count($commonUsers); $i++){
				$query_delete = $query_delete . " and Login <> \"" .$commonUsers[$i]['login'] ."\"";
			}
		}
		$statement = mysqli_prepare($link, $query_delete);
		$statement->execute();
		$statement->close();
	}
	$deleted = $numrows - count($commonUsers);

	//Удаяляем все кроме найденных DELETE FROM users WHERE Login <> val1 and Login <> val2 and ...
	//echo $query_delete;

	//Обновляем данные
	for($i=0; $i<count($commonUsers); $i++){
		$login = $commonUsers[$i]['login'];
		$password = $commonUsers[$i]['newpassword'];
		$query_update = "UPDATE users SET Password=\"$password\" WHERE Login = \"$login\"";
      	$statement = mysqli_prepare($link, $query_update);      	
		$statement->execute();
		$statement->close();
	}
	
	for($i=1; $i<count($users); $i++){
		$query_insert = "INSERT INTO users (Login, Password, Name, Email) SELECT * FROM (SELECT \"". $users[$i]['login'] ."\" AS l, \"". $users[$i]['password'] ."\" AS p, \"". $users[$i]['name'] ."\" AS n, \"". $users[$i]['email'] ."\" AS e) AS tmp WHERE NOT EXISTS (SELECT Login FROM users WHERE Login = \"". $users[$i]['login'] ."\") LIMIT 1";
		$statement = mysqli_prepare($link, $query_insert);

		$statement->execute();
		$statement->close();  
	}

	displayInfo('<br>Импортирован файл. Обработано записей - '. ($numrows+count($users)-count($commonUsers)) . '; Обновлено записей - '. count($commonUsers). '; Удалено записей - '. $deleted . '.<br>');
	
}


function displayInfo($info){
	echo $info;
}











exit();
?>

