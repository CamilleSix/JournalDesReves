<?php
function svg(string $name, string $color = "Black"):string{
    $svgContent = file_get_contents("images/icons/{$name}.svg") ;
    return str_replace("{color}", $color, $svgContent) ;
}

