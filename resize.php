#!/usr/bin/env php

<?php

/**
 *
 * brew install jpegoptim
 * brew install optipng
 * Dependencias
 * https://github.com/spatie/image-optimizer#optimization-tools
 */
if(!extension_loaded('gd')){
    echo 'Necessário biblioteca GD';
    return;
}

$extensoes = array("jpg", "gif", "jpeg", "svg", "bmp", "png");
$diretorioImagens = getcwd();
$diretorioMinificados = $diretorioImagens . '/minificados/';



if (!file_exists($filename)) {
    mkdir($diretorioMinificados, 0777);
}

define('WIDTH_POS', 0);
define('HEIGHT_POS', 1);

foreach (new DirectoryIterator($diretorioImagens) as $fileInfo) {

    $extensaoArquivo = $fileInfo->getExtension();

    if (!in_array($extensaoArquivo, $extensoes) ) {
        continue;
    }

    if (!$fileInfo->isFile()) {
        continue;
    }

    $imageFilename = $fileInfo->getFilename();
    $imagefullPath = "{$diretorioImagens}/{$imageFilename}";
    $imageSize = getimagesize($imagefullPath);
    $imageType = $imageSize[2];

    $imageFileSave = $imagefullPath;
    $imageFileSave = $diretorioImagens . '/minificados/'. basename($imageFilename, $extensaoArquivo) . 'min.' . $extensaoArquivo;

    if( $imageType == IMAGETYPE_JPEG ) {
        $funcaoCriarImagem = 'imagejpeg';
        $comando = "jpegoptim --strip-all -m85 -o -p $imageFileSave";
    } elseif( $imageType == IMAGETYPE_GIF ) {
        $funcaoCriarImagem = 'imagegif';
    } elseif( $imageType == IMAGETYPE_PNG ) {
        $funcaoCriarImagem = 'imagepng';
        $comando = "optipng $imageFileSave";
    }

    if ($imageSize[WIDTH_POS] <= 1024) {
        copy( $imageFilename, $imageFileSave);
        $resultado = exec($comando);
        echo $resultado. PHP_EOL;
        continue;
    }

    $ratio = $imageSize[WIDTH_POS]/$imageSize[HEIGHT_POS];

    if( $ratio > 1) {
        $width = 1024;
        $height = 1024/$ratio;
    } else {
        $width = 1024*$ratio;
        $height = 1024;
    }

    $src = imagecreatefromstring(file_get_contents($imagefullPath));
    $dst = imagecreatetruecolor($width,$height);

    imagecopyresampled(
        $dst,
        $src,
        0,
        0,
        0,
        0,
        $width,
        $height,
        $imageSize[WIDTH_POS],
        $imageSize[HEIGHT_POS]
    );

    imagedestroy($src);

    $funcaoCriarImagem($dst, $imageFileSave);
    imagedestroy($dst);

    $resultado = exec($comando);
    echo $resultado. PHP_EOL;
}