<?php

// Основные настройки:
$recipients = [$email]; // Получатели писем
$subject = $_SERVER['SERVER_NAME'] . ' — Подтвердите вашу почту!'; // Тема письма

$before_table = '<h2 style="color:#222">'. $subject .'</h2><p style="color:#222">Данные отправителя: '.$token.'</p>'; // HTML-содержимое до таблицы
$after_table = '<i style="color:#bbb;font-size:12px">Сообщение отправлено с сайта <b>'.$_SERVER['SERVER_NAME'].'</b></i>'; // HTML-содержимое после таблицы
$sep = ', '; // Разделитель между значениями (использ. при форм. HTML-содержимого письма)


// Настройки SMTP:
$smtp_host     = 'smtp.mail.ru'; // SMPT-адрес сервера
$smtp_port     = 465; // TCP-порт
$smtp_secure   = 'ssl'; // SMTP TLS/SSL
$smtp_auth     = true; // SMPT-аутентификация
$smtp_username = 'hockeynation.bot@mail.ru'; // Почтовый ящик, с которого будут отправляться письма
$smtp_password = 'stenad5a'; // Пароль почтового ящика, с которого будут отправляться письма

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
            return '<div>Вы недавно отправили заявку на смену почты на сайте'.$_SERVER['SERVER_NAME'].'. Не переходите по ссылке,если вы этого не делали <a href="'.$_SERVER['SERVER_NAME'].'/changemail.php?token='.$s.'">'.$_SERVER['SERVER_NAME'].'/changemail.php?token='.$s.'</div>';
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
        else if( $success_page ) header('Location: ' . $success_page);

        // Успешно: Без AJAX (по ум.)
        else echo '<strong>Форма успешно отправлена!</strong>';

    }

} catch (Exception $e) {

    // Ошибка: Отправка AJAX
    if( $_POST['js'] === 'on' ) echo $mail->ErrorInfo;

    // Ошибка: Без AJAX (перенаправление)
    else if( $error_page ) header('Location: ' . $error_page);

    // Ошибка: Без AJAX (по ум.)
    else echo '<strong>При отправке формы произошла ошибка!</strong><br><br>' . $mail->ErrorInfo;

} ?>
