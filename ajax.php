<?php
declare(strict_types=1);
require "./functions.php";

$directory = $_SERVER['DOCUMENT_ROOT'] . "/msk-fotobank/";
// print_r($_POST);
$action = $_POST['action'];
//===TODO===
//добавить валидацию по размеру файлов и наличию action
if(!$action){
    throw new \Exception("отсутствует action");
}
try{
    switch($action){
        case 'upload':
            uploadFiles($directory);
            echo json_encode(["success" => "Файлы загружены"], JSON_UNESCAPED_UNICODE);
            http_response_code(200);
            break;
        case 'resize':
            $folderPath = $directory . $_POST['folder'] . "/";
            resizeImgInFolder($folderPath);
            echo json_encode(["success" => "Размеры файлов изменены"], JSON_UNESCAPED_UNICODE);
            http_response_code(200);
            break;
        case 'get':
            getImages($directory . $_POST["folder"]);
            http_response_code(200);
    }
} catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['error' => "Произошла ошибка - " . $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    
}