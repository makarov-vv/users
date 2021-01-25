<?php
	session_start();
	$link = mysqli_connect('192.168.0.5', 'root', 'root', 'testbd');
	if(mysqli_connect_errno()){
		echo 'Error '. mysqli_connect_errno() . ' ' . mysqli_connect_error().'<br>';
	} else {
		$link->set_charset('utf8');
	}
	if(isset($_POST['login']) && isset($_POST['password']) && $_POST['login']!="" && $_POST['password']!=""){

		$query = "SELECT Login FROM users WHERE Login = ?";
		$statement = mysqli_prepare($link, $query);
		$statement->bind_param('s', $_POST['login']);
		$statement->execute();
		$statement->bind_result($temp);
		$statement->fetch();
		$statement->close();

		if(!$temp){
			$data = array();		
			$data[0] = $_POST['login'];
			$data[1] = $_POST['password'];
			if($_POST['name']!="")
				$data[2] = $_POST['name'];
			else $data[2] = $_POST['login'];
			if($_POST['email']!="")
				$data[3] = $_POST['email'];
			else $data[3] = $_POST['login'] . "@example.com";
			$query = "INSERT INTO users (Login, Password, Name, Email) VALUES (?,?,?,?)";
			$statement = mysqli_prepare($link, $query);
			$statement->bind_param('ssss', $data[0], $data[1], $data[2], $data[3]);
			$statement->execute();
			$statement->close();
			$_SESSION['mes'] = "Добавление успешно";
		} else {
			$_SESSION['mes'] = "Такой логин уже есть";
		}	
	} else 
		$_SESSION['mes'] = "Пользователь или пароль не введен либо введен не верно!";	
	
	header('Location: /');
?>