<?php

// Основные настройки:
$recipients = [$email]; // Получатели писем
$subject = $_SERVER['SERVER_NAME'] . ' — Запрос на регистрацию отправлен!'; // Тема письма

$before_table = '<h2 style="color:#222">'. $subject .'</h2><p style="color:#222">Данные отправителя: '.$token.'</p>'; // HTML-содержимое до таблицы
$after_table = '<i style="color:#bbb;font-size:12px">Сообщение отправлено с сайта <b>'.$_SERVER['SERVER_NAME'].'</b></i>'; // HTML-содержимое после таблицы
$sep = ', '; // Разделитель между значениями (использ. при форм. HTML-содержимого письма)

// Настройки SMTP:
$smtp_host     = 'smtp.hostinger.ru'; // SMPT-адрес сервера
$smtp_port     = 465; // TCP-порт
$smtp_secure   = 'ssl'; // SMTP TLS/SSL
$smtp_auth     = true; // SMPT-аутентификация
$smtp_username = 'admin@hockeynation.su'; // Почтовый ящик, с которого будут отправляться письма
$smtp_password = 'Rfhbvjdf94'; // Пароль почтового ящика, с которого будут отправляться письма

// Перенаправления на страницы (если JS отключен):
$success_page = './success.html'; // При успешной отправке
$error_page   = './error.html'; // При Ошибке

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

		require 'phpmailer/src/Exception.php';
		require 'phpmailer/src/PHPMailer.php';
		require 'phpmailer/src/SMTP.php';

		// Формирование HTML-таблицы с введенными данными:
		function createInputsTable($s) {
			return '<h1>Добрый день,вы регистрировались у нас сайте,ваш запрос успешно передан администритору,пожалуйста дождитесь ответа на вашу почту</h1>';
		}

		$mail = new PHPMailer(true);
		$mail->CharSet = 'utf-8';
		$mail->isSMTP();
		$mail->Host = $smtp_host;
		$mail->Port = $smtp_port;
		$mail->SMTPSecure = $smtp_secure;
		$mail->SMTPAuth = $smtp_auth;
		$mail->Username = $smtp_username;
		$mail->Password = $smtp_password;
		$mail->setFrom($smtp_username);
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = createInputsTable($token);
		$mail->setLanguage('ru');

		// Загрузка получателей:
		foreach( $recipients as $rec )
			$mail->addAddress($rec);

		// Загрузка вложений:
		if( $_FILES ) {
			foreach( $_FILES as $file) {
				// Одно вложение
				if( $file['name'] != '' && gettype($file['name']) != 'array') {
					$mail->addAttachment($file['tmp_name'], $file['name']);
				// Несколько вложений
				} else if( $file['name'] != '' && gettype($file['name']) == 'array' && $file['name'][0] != '' ) {
					for( $i=0; $i < count($file['name']); $i++ ) {
						$mail->addAttachment($file['tmp_name'][$i], $file['name'][$i]);
					}
				}
			}
		}

		// Отправка формы
		$mail->send();

		// Успешно: Отправка AJAX
		if( $_POST['js'] === 'on' ) header('sendmail: 1');

		// Успешно: Без AJAX (перенаправление)
		else if( $success_page ) ;

		// Успешно: Без AJAX (по ум.)
		else echo '<strong>Форма успешно отправлена!</strong>';



		
		function createInputsTable2($s) {
            return 'Новая заявка';
		}

		$mail->setFrom($smtp_username);
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = createInputsTable2($token);
		$mail->setLanguage('ru');
		$mail->addAddress('artomik1@mail.ru');
		$mail->send();


	}

} catch (Exception $e) {

	// Ошибка: Отправка AJAX
	if( $_POST['js'] === 'on' ) echo $mail->ErrorInfo;

	// Ошибка: Без AJAX (перенаправление)
	else if( $error_page ) echo $e;

	// Ошибка: Без AJAX (по ум.)
	else echo '<strong>При отправке формы произошла ошибка!</strong><br><br>' . $mail->ErrorInfo;

} ?>