<?php
	session_start();
	$link = mysqli_connect('192.168.0.5', 'root', 'root', 'testbd');
	if(mysqli_connect_errno()){
		echo 'Error '. mysqli_connect_errno() . ' ' . mysqli_connect_error().'<br>';
	} else {
		$link->set_charset('utf8');
	}
	if(isset($_POST['editlogin']) && isset($_POST['editpassword'])&& $_POST['editlogin']!="" && $_POST['editpassword']!=""){
		$query = "SELECT Login FROM users WHERE Login = ?";
		$statement = mysqli_prepare($link, $query);
		$statement->bind_param('s', $_POST['editlogin']);
		$statement->execute();
		$statement->bind_result($temp);
		$statement->fetch();
		$statement->close();

		if(!$temp||$temp==$_POST['editlogin']){
			$data = array();		
			$data[0] = $_POST['editlogin'];
			$data[1] = $_POST['editpassword'];
			if(isset($_POST['editname'])&&$_POST['editname']!="")
				$data[2] = $_POST['editname'];
			else $data[2] = $_POST['editlogin'];
			if(isset($_POST['editemail'])&&$_POST['editemail']!="")
				$data[3] = $_POST['editemail'];
			else $data[3] = $_POST['editlogin'] . "@example.com";
			$query = "UPDATE users SET Login = ?, Password = ?, Name = ?, Email = ? WHERE Id = ?";		
			$statement = mysqli_prepare($link, $query);
			$statement->bind_param('ssssd', $data[0], $data[1], $data[2], $data[3], $_GET['id']);
			$statement->execute();
			$statement->close();
			$_SESSION['mes'] = "Пользователь изменен";
			header('Location: /');
		} else {
			$_SESSION['mes'] = "Такой логин уже есть";
			header('Location: /?id='.$_GET['id']);
		}	

	} else {
		$_SESSION['mes'] = "Пользователь или пароль не введен либо введен не верно!";
		header('Location: /?id='.$_GET['id']);
	}
	
?>