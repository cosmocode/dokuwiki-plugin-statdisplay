<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function draw_loading_bar($im, $x, $y, $width, $height) {
    $blue       = ImageColorAllocate($im, 0, 0, 255);
    $clear_blue = ImageColorAllocate($im, 120, 120, 250);
    $size       = min($width, $height);

    while($x < $width) {
        $clear_blue_poly = array(
            $x, $y,
            $x + $size, $y,
            $x, $y + $size,
        );
        ImageFilledPolygon($im, $clear_blue_poly, 3, $clear_blue);

        $blue_poly = array(
            $x + $size, $y,
            $x + $size, $y + $size,
            $x, $y + $size
        );
        ImageFilledPolygon($im, $blue_poly, 3, $blue);

        $blue_poly = array(
            $x + $size, $y,
            $x + $size + $size, $y,
            $x + $size, $y + $size,
        );
        ImageFilledPolygon($im, $blue_poly, 3, $blue);

        $clear_blue_poly = array(
            $x + $size + $size, $y,
            $x + $size + $size, $y + $size,
            $x + $size, $y + $size
        );
        ImageFilledPolygon($im, $clear_blue_poly, 3, $clear_blue);

        $x = $x + 2 * $size;
    }
}

function draw_white_bar($im, $x, $y, $width, $height, $value, $max) {
    $white = ImageColorAllocate($im, 255, 255, 255);
    $x2    = $x + $width;
    $x     = $x + ($value / $max * $width);

    imagefilledrectangle($im, $x, $y, $x2, $y + $height, $white);
}

function loading($img_width, $img_height, $value, $max) {
    header("Content-type: image/png");
    // Create image and Set the colors
    $img_width  = max($img_width, 260);
    $img_height = max($img_height, 30);
    $im         = imagecreatetruecolor(intval($img_width), intval($img_height));
    $blue       = ImageColorAllocate($im, 0, 0, 255);
    $white      = ImageColorAllocate($im, 255, 255, 255);
    $grey       = ImageColorAllocate($im, 155, 155, 155);
    $black      = ImageColorAllocate($im, 0, 0, 0);
    imagefill($im, 0, 0, $white);

    $padding = 20;
    draw_loading_bar($im, 0, 0, $img_width, $img_height - $padding);
    draw_white_bar($im, 0, 0, $img_width, $img_height - $padding, $value, $max);
    imagerectangle($im, 0, 0, $img_width - 1, $img_height - $padding, $grey);
    $caption = "Percentage of logfile analyzed : ".number_format(($value / $max * 100))."%";
    $x       = $img_width / 2 - strlen($caption) * 3.5;
    imagestring($im, 3, $x, $img_height - 15, $caption, $black);

    imagepng($im); //Show the histogram created
    imagedestroy($im); //Free the memory and destroy the histogram

}

loading(10, 10, $_GET['value'], $_GET['max']);
?>


