<?php
session_start();
require ("./functions.php");

$directory = $_SERVER['DOCUMENT_ROOT'] . "/msk-fotobank/";

if ($_POST && isset($_POST['createdir'])) {
        createDirectory($_POST['directory']);
        header("location: /");
        die;
    };

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Работа с фотографиями</title>
    <link href="/style.css" rel="stylesheet" />
    <script src="./js/script.js" type="module" defer ></script>
</head>
<body>

    <?php     
    if(isset($_SESSION['success'])){
        echo $_SESSION['success'];
        unset($_SESSION['success']);
    }
    if(isset($_SESSION['errors'])){
        echo $_SESSION['errors'];
        unset($_SESSION['errors']);
    }


    if(!isset($_SESSION['auth'])):
    ?>
    
    <form method="post">
        <label>Введите пароль</label>
        <input type="password" name="pass">
        <button type="submit" name="login">Отправить</button>
    </form>
    <?php
    endif;

    if (isset($_POST['pass'])) {
        verifyPassword();
        header("location: /");
        die;
    }
    
    if(isset($_SESSION['auth'])) :

    ?>
    <div class="container">
        <form method="post" class="directory-form">
            <input type="text" name="directory" placeholder="Введите имя директории">
            <button type="submit" name="createdir">Создать директорию</button>
        </form>
        <?php getDirectories($directory); ?>
    </div>
    <div class="cover hidden">
        <div class="popup">
            <button class="gallery-popup_close">close</button>
            <div class="gallery-popup">
                

            </div>
        </div>
    </div>
    <?php endif; ?>
    

</body>
</html>