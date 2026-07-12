<?php

declare(strict_types = 1);
// Функция для изменения размера изображения с использованием Imagick
function resizeImageImagick($file, int $newWidth, int $newHeight, $outputFile) {
    $image = new Imagick($file);
    $width = $image->getImageWidth();
    $height = $image->getImageHeight();

    // Расчет новой ширины и высоты с сохранением пропорций
    if ($newWidth / $newHeight > $width / $height) {
        $newWidth = ($newHeight / $height) * $width;
    } else {
        $newHeight = ($newWidth / $width) * $height;
    }

    $image->resizeImage( (int) $newWidth, (int) $newHeight, Imagick::FILTER_LANCZOS, 1);
    
    $image->setImageCompressionQuality(90); // Качество 90%
    $image->writeImage($outputFile);
    $image->clear();
}

function resizeImgInFolder(string $folderPath){
    $dirs = explode('/', $folderPath);
    $folder = $dirs[count($dirs)-2];
    $images = scandir($folderPath);
    $changeImages = 0;        
    // var_dump($images);
    // Получаем максимальный номер файла в папке. Благодаря этому можем добалять в папку новые картинки, они будут добавляться с макс. номером в списке
    $existingFiles = array_filter($images, function($image) use ($folder) {
        return preg_match("/^{$folder}-(\d+)\.jpg$/", $image);
    });

    $maxCounter = 0;
    foreach ($existingFiles as $file) {
        preg_match("/^{$folder}-(\d+)\.jpg$/", $file, $matches);
        if (isset($matches[1]) && (int)$matches[1] > $maxCounter) {
            $maxCounter = (int)$matches[1];
        }
    }

    $counter = $maxCounter + 1; // Начинаем с следующего номера

    foreach ($images as $image) {
        if ($image == '.' || $image == '..')  continue; 
        // echo "Берем картинку {$image} {$counter} <br>";

        $imagePath = $folderPath . $image;

        $img = new Imagick($imagePath);
        $width = $img->getImageWidth();
        $height = $img->getImageHeight();             
        
        $newImagePath = $folderPath . $folder . '-' . $counter . '.jpg';

        //вроде бы это должно проверять , чтоб уже изменные фотки не обрабатывались заново
        if($imagePath == $newImagePath) continue;
        $resized = false;

        if ($width > $height && $width > 850) {
            // Горизонтальная фотография
            resizeImageImagick($imagePath, 850, (int) $height, $newImagePath);
            $resized = true;
            $changeImages++;
        } elseif ($height > $width && $height > 750) {
            // Вертикальная фотография
            resizeImageImagick($imagePath, (int) $width, 750, $newImagePath);
            $resized = true;
            $changeImages++;
        } 
        else {
            //неизмененная фотография
            resizeImageImagick($imagePath, (int) $width, (int) $height, $newImagePath);
            $resized = true;
            $changeImages++;
        }

        if ($resized && file_exists($newImagePath)) {
            unlink($imagePath); 
            $counter++; 
        }
        
        $img->clear();
    }
}

function scanAllDirsWithImages($directory)
{
    $folders = scandir($directory);
    foreach ($folders as $folder) {
        if ($folder == '.' || $folder == '..') {
            continue;
        }
        $folderPath = $directory . $folder;
        if (is_dir($folderPath)) {
            resizeImgInFolder($folderPath);
        }
    }
}

function getDirectories( string $directory):void
{
    if(is_dir($directory)){
        $dirs = scandir($directory);
        foreach($dirs as $dir){
            if($dir == "." || $dir == ".." ) continue;

            $fullPath = $directory . $dir;
            
            if (is_dir($fullPath)) {
            echo "
                <div class='form-group' id={$dir}> 
                            <strong> {$dir} </strong> 
                            <form method='post' enctype='multipart/form-data'>
                                <input type='file' name='{$dir}[]' multiple>
                                        <button type='submit' name='sendfile'>Отправить</button>
                                        <div class='button-group'>
                                        <input type='hidden' name='folder' value='{$dir}'>
                                        <button type='submit' name='resizeimages'>Изменить размеры</button>
                                        <button type='submit' name='showimages'>Показать фотографии</button>
                                    </div>
                            </form>
                        </div>
                ";
            }
        }
    } else{
        $_SESSION['errors'] = "<p>Ошибка в переданной директории. <br>{$directory} - не директория</p>";
    }
    
}
function transliteration(string $value):string
{
	$converter = array(
		'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
		'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
		'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
		'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
		'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
		'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
		'э' => 'e',    'ю' => 'yu',   'я' => 'ya',   ' ' => '-'
	);
 
	$value = mb_strtolower($value);
	$value = strtr($value, $converter);
	$value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
	$value = mb_ereg_replace('[-]+', '-', $value);
	$value = trim($value, '-');	

	return $value;
}
function createDirectory(string $dir):void
{
    global $directory;
    $dir = transliteration(htmlspecialchars($dir));
    if (preg_match('/^[a-z0-9-]+$/', $dir)) {
        if (mkdir($directory . $dir, 0775)) {
           $_SESSION['success'] = "<p>Директория <strong>{$dir}</strong> создана</p>";
        } else {
            $_SESSION['errors'] = "<p>Ошибка создания директории</p>";
        }
    } else {
        $_SESSION['errors'] = "<p>Имя директории может содержать только символы a-z 0-9 и дефис (-) </p>";
    }
}
function verifyPassword():void
{
    $pass = $_POST['pass'] ? trim($_POST['pass']) : '';
    if (password_verify($pass, '$2y$10$8w4GYjh1HWenex04L.6zeOI0o0qDLF8w.8pVH0VS0OcsEPiXowJ72')) {
         $_SESSION['success'] = '<p>Вы успешно авторизованы</p>';
         $_SESSION['auth'] = 'Авторизация';
    } else {
        $_SESSION['errors'] = "<p>Неверные данные для входа</p>";   
    }
}
function uploadFiles(string $directory):void
{
    $files = $_FILES;
    if (isset($files)) {
        foreach ($files[key($files)]['name'] as $key => $name) {
            $tmpFileName = $files[key($files)]["tmp_name"][$key];
            $oldFileName = $files[key($files)]['name'][$key];
            $folderName = $directory . key($files);
            $newPath = $folderName . "/" . $oldFileName;
            // print_r([$tmpFileName, $oldFileName, $folderName, $newPath]);
            if (!move_uploaded_file($tmpFileName, $newPath)) {
                throw new \Exception("Файл не загружен");
            } 
        }
    }
}
function getImages(string $discFolder):void
{
    $images = scandir($discFolder);
    $urlPath = str_replace($_SERVER['DOCUMENT_ROOT'], "", $discFolder);
    foreach($images as $img){
        if(!is_file($discFolder . "/" . $img)) continue;
        echo '<img alt="" class="thumb" src="'. $urlPath . "/" . $img . '" />';
    }
}