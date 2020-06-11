<?
define('MYSQL_SERVER', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_DB', 'ab20');

function db_connect(){
    $link = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB)
        or die("Error: ".mysqli_error($link));
    if (!mysqli_set_charset($link,"utf8")){
        printf("Error: ".mysqli_error($link));
    }
    return $link;
}

$link = db_connect();


$err = '';

$name = $_POST['name'];$lastname = $_POST['lastname'];$password = $_POST['pas1'];$password_try = $_POST['pas2'];$email = $_POST['email'];

$check_pass = mysqli_query($link,"SELECT * FROM `users` WHERE `email` = '$email'");
if(mysqli_affected_rows($link)){
    $err = 'Данная почта уже зарегистрирована!';
}

if(strlen($name) == 0 || strlen($lastname) == 0 || strlen($password) == 0 || strlen($password_try) == 0 || strlen($email) == 0){
    $err = 'Одно из полей не заполнено';
}elseif($password_try != $password){
    $err = 'Пароли не совпадают!';
}

$hash_p = password_hash($password,1);


if(empty($err)){
    $records = mysqli_query($link,"INSERT INTO `users`(`role`,`login`,`password`,`name`,`sname`,`email`,`tel`,`b_date`,`img`,`lessons`,`status`,`level`,`verified`,`smth`) 
    VALUES(1,'$email','$hash_p','$name','$lastname','$email','0','0','',0,0,'Новичек',0,0)");

    $token = md5($email.time());
    require "mail/sendmail2.php";

    require "mail/sendmail3.php";
}

echo $err;
