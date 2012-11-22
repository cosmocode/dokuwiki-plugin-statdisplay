<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function draw_bar($im, $top_left_x, $top_left_y, $width, $height, $scale, $value, $color) {
    $black = ImageColorAllocate($im, 0, 0, 0);

    $y1 = $top_left_y + $height - ($scale * $value);
    $x2 = $top_left_x + $width;
    $y2 = $top_left_y + $height;

    imagefilledrectangle($im, $top_left_x, $y1, $x2, $y2, $color);
    imagerectangle($im, $top_left_x, $y1, $x2, $y2, $black);
}

function make_total_histogram($im, $top_left_x, $top_left_y, $width, $height, $padding, $datas) {
    $width      = $width - 2 * $padding;
    $height     = $height - 2 * $padding;
    $top_left_x = $top_left_x + $padding;
    $top_left_y = $top_left_y + $padding;

    // Set the colors
    $blue       = ImageColorAllocate($im, 0, 0, 255);
    $dark_green = ImageColorAllocate($im, 0, 125, 0);
    $clear_blue = ImageColorAllocate($im, 100, 255, 240);
    $black      = ImageColorAllocate($im, 0, 0, 0);

    $max = 0;

    //Find all maximums
    foreach($datas as $sub_tab) {
        $max = max($max, $sub_tab['resume']['hits'], $sub_tab['resume']['files'], $sub_tab['resume']['pages']);
    }
    $scale = $height / $max;

    //Show lengend
    imagestringup($im, 3, $top_left_x - $padding - 15, $top_left_y + $height, 'Pages', $clear_blue);
    imagestringup($im, 2, $top_left_x - $padding - 15, $top_left_y + $height - 38, '/', $black);
    imagestringup($im, 3, $top_left_x - $padding - 15, $top_left_y + $height - 48, 'Files', $blue);
    imagestringup($im, 2, $top_left_x - $padding - 15, $top_left_y + $height - 85, '/', $black);
    imagestringup($im, 3, $top_left_x - $padding - 15, $top_left_y + $height - 95, 'Hits', $dark_green);
    imagestringup($im, 2, $top_left_x - $padding - 15, $top_left_y - $padding + strlen($max) * 6, $max, $black);

    $part_width   = $width / count($datas);
    $part_spacing = 0.2 * $part_width;
    $bar_offset   = intval($part_spacing);
    $bar_width    = ($part_width - $part_spacing) - ($bar_offset * 2);

    //Draw the Histogram
    $i = 0;
    foreach($datas as $key => $sub_tab) {
        $key = explode("_", $key);
        $key = $key[0];
        foreach($sub_tab['resume'] as $sub_key => $value) {
            if($sub_key == "hits")
                $hits_value = $value;
            if($sub_key == "files")
                $files_value = $value;
            if($sub_key == "pages")
                $pages_value = $value;
        }
        draw_bar($im, $top_left_x + $i * $part_width, $top_left_y, $bar_width, $height, $scale, $hits_value, $dark_green);
        draw_bar($im, ($top_left_x + $i * $part_width) + $bar_offset, $top_left_y, $bar_width, $height, $scale, $files_value, $blue);
        draw_bar($im, ($top_left_x + $i * $part_width) + 2 * $bar_offset, $top_left_y, $bar_width, $height, $scale, $pages_value, $clear_blue);
        imagestring($im, 2, ($top_left_x + $i * $part_width) + (($part_width - $part_spacing) / 2) - 10, $top_left_y + $height + $padding + 2, $key, $black);
        $i++;
    }
}

function make_total_visits_histogram($im, $top_left_x, $top_left_y, $width, $height, $padding, $datas) {
    $width      = $width - 2 * $padding;
    $height     = $height - 2 * $padding;
    $top_left_x = $top_left_x + $padding;
    $top_left_y = $top_left_y + $padding;

    // Set the colors
    $yellow = ImageColorAllocate($im, 255, 255, 0);
    $black  = ImageColorAllocate($im, 0, 0, 0);

    $max = 0;

    //Find the maximum
    foreach($datas as $sub_tab) {
        $max = max($max, $sub_tab['resume']['visits']);
    }
    $scale = $height / $max;

    //Show lengend
    imagestring($im, 3, $top_left_x + $width - 30, $top_left_y - $padding - 15, 'Visits', $yellow);
    imagestringup($im, 2, $top_left_x + $width + $padding, $top_left_y - $padding + strlen($max) * 6, $max, $black);

    $part_width   = $width / count($datas);
    $part_spacing = 0.2 * $part_width;

    //Draw the Histogram
    $i = 0;
    foreach($datas as $key => $sub_tab) {
        draw_bar($im, $top_left_x + $i * $part_width, $top_left_y, $part_width - $part_spacing, $height, $scale, $sub_tab['resume']['visits'], $yellow);
        $i++;
    }
}

function make_total_bytes_histogram($im, $top_left_x, $top_left_y, $width, $height, $padding, $datas) {
    $width      = $width - 2 * $padding;
    $height     = $height - 2 * $padding;
    $top_left_x = $top_left_x + $padding;
    $top_left_y = $top_left_y + $padding;

    // Set the colors
    $red   = ImageColorAllocate($im, 255, 0, 0);
    $black = ImageColorAllocate($im, 0, 0, 0);

    $max = 0;

    //Find the maximum
    foreach($datas as $sub_tab) {
        $max = max($max, $sub_tab['resume']['bytes']);
    }

    $scale = $height / $max;

    //Show lengend
    imagestring($im, 3, $top_left_x + $width - 30, $top_left_y + $padding + $height, 'KBytes', $red);
    imagestringup($im, 2, $top_left_x + $width + $padding, $top_left_y - $padding + strlen(intval($max)) * 6, intval($max), $black);

    $part_width   = $width / count($datas);
    $part_spacing = 0.2 * $part_width;

    //Draw the Histogram
    $i = 0;
    foreach($datas as $key => $sub_tab) {
        draw_bar($im, $top_left_x + $i * $part_width, $top_left_y, $part_width - $part_spacing, $height, $scale, $sub_tab['resume']['bytes'], $red);
        $i++;
    }
}

function draw_border($im, $top_left_x, $top_left_y, $top_right_x, $top_right_y, $bottom_left_x, $bottom_left_y) {
    // Set the colors
    $hard_grey = ImageColorAllocate($im, 80, 80, 80);
    $white     = ImageColorAllocate($im, 255, 255, 255);
    $black     = ImageColorAllocate($im, 0, 0, 0);

    //A black and white border
    imageline($im, $top_left_x, $top_left_y, $top_right_x, $top_right_y, $white);
    imageline($im, $top_right_x, $top_right_y, $top_right_x, $bottom_left_y, $white);
    imageline($im, $top_right_x, $bottom_left_y, $bottom_left_x, $bottom_left_y, $white);
    imageline($im, $bottom_left_x, $bottom_left_y, $top_left_x, $top_left_y, $white);
    imageline($im, $top_left_x + 1, $top_left_y + 1, $top_right_x - 1, $top_right_y + 1, $black);
    imageline($im, $top_right_x + 1, $top_right_y + 1, $top_right_x + 1, $bottom_left_y + 1, $black);
    imageline($im, $top_right_x + 1, $bottom_left_y + 1, $bottom_left_x + 1, $bottom_left_y + 1, $black);
    imageline($im, $bottom_left_x + 1, $bottom_left_y - 1, $top_left_x + 1, $top_left_y + 1, $black);

    //A scale drawn in the background
    $scale_y = $top_left_y + (($bottom_left_y - $top_left_y) / 3);
    imageline($im, $top_left_x + 2, $scale_y, $top_right_x - 1, $scale_y, $hard_grey);
    $scale_y = $top_left_y + 2 * (($bottom_left_y - $top_left_y) / 3);
    imageline($im, $top_left_x + 2, $scale_y, $top_right_x - 1, $scale_y, $hard_grey);
}

function total_histogram($total_tab, $img_width, $img_height, $caption) {
    header("Content-type: image/png");

    // Create image and Set the colors
    $im          = imagecreatetruecolor(intval($img_width), intval($img_height));
    $grey        = ImageColorAllocate($im, 180, 180, 180);
    $little_grey = ImageColorAllocate($im, 220, 220, 220);
    $hard_grey   = ImageColorAllocate($im, 60, 60, 60);
    $blue        = ImageColorAllocate($im, 0, 0, 255);

    $margin      = 20; //graph margin
    $padding     = 10; //Histogram padding
    $border_size = 3; //main border size
    $ratio       = 0.66;

    //Fill the graph with grey background
    $im = imagecreatetruecolor(intval($img_width), intval($img_height));
    imagefill($im, 0, 0, $grey);

    //Color the first part of the main border
    $borger_top_left = array(
        0, $img_height,
        0, 0,
        $img_width, 0,
        $img_width - $border_size, $border_size,
        $border_size, $border_size,
        $border_size, $img_height - $border_size,
    );
    ImageFilledPolygon($im, $borger_top_left, 6, $little_grey);

    //Color the second part of the main border
    $border_top_right = array(
        0, $img_height,
        $img_width, $img_height,
        $img_width, 0,
        $img_width - $border_size, $border_size,
        $img_width - $border_size, $img_height - $border_size,
        $border_size, $img_height - $border_size,
    );
    ImageFilledPolygon($im, $border_top_right, 6, $hard_grey);

    //writing caption
    imagestring($im, 3, $margin, 6, $caption, $blue);

    //create 3 borders for 3 histograms
    draw_border($im, $margin, $margin, $margin + (($img_width - 2 * $margin) * $ratio), $margin, $margin, $img_height - $margin);
    make_total_histogram($im, $margin, $margin, ($img_width - 2 * $margin) * $ratio, $img_height - 2 * $margin, $padding, $total_tab);
    draw_border($im, $margin + (($img_width - 2 * $margin) * $ratio), $margin, $img_width - $margin, $margin, $margin + (($img_width - 2 * $margin) * $ratio), $margin + (($img_height - 2 * $margin) / 2));
    make_total_visits_histogram($im, $margin + (($img_width - 2 * $margin) * $ratio), $margin, ($img_width - 2 * $margin) * (1 - $ratio), ($img_height - 2 * $margin) / 2, $padding, $total_tab);
    draw_border($im, $margin + (($img_width - 2 * $margin) * $ratio), $margin + (($img_height - 2 * $margin) / 2), $img_width - $margin, $margin + (($img_height - 2 * $margin) / 2), $margin + (($img_width - 2 * $margin) * $ratio), $img_height - $margin);
    make_total_bytes_histogram($im, $margin + (($img_width - 2 * $margin) * $ratio), $margin + (($img_height - 2 * $margin) / 2), ($img_width - 2 * $margin) * (1 - $ratio), ($img_height - 2 * $margin) / 2, $padding, $total_tab);

    imagepng($im); //Show the histogram created
    imagedestroy($im); //Free the memory and destroy the histogram

}

#include 'init_gd.php';
if($_GET['begin'] == "")
    $new_tab = $tableau;
//Cut the table to the interesting part
if($_GET['begin'] != "") {
    $flag     = 0;
    $end_flag = 0;
    foreach($tableau as $key => $sub_tab) {
        if($flag == 0 AND $key == $_GET['begin'])
            $flag = 1;
        if($flag == 1 AND $_GET['end'] != "" AND $key == $_GET['end'])
            $end_flag = 1;
        if($end_flag == 1 AND $key != $_GET['end'])
            $flag = 0;
        if($flag == 1)
            $new_tab[$key] = $sub_tab;
    }
}

total_histogram($new_tab, 550, 300, 'Usage summary for '.$_SERVER['SERVER_NAME']);
?>


