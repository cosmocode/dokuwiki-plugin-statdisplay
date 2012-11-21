<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */
include 'init_gd.php';

function draw_bar($im,$top_left_x,$top_left_y,$width,$height,$scale,$value,$color)
{
	$black=ImageColorAllocate($im,0,0,0);
	
	$y1=$top_left_y+$height-($scale*$value);
	$x2=$top_left_x+$width;
	$y2=$top_left_y+$height;
	
	imagefilledrectangle($im,$top_left_x,$y1,$x2,$y2,$color);
	imagerectangle($im,$top_left_x,$y1,$x2,$y2,$black);
}

function make_total_histogram($im,$top_left_x,$top_left_y,$width,$height,$padding,$datas,$size)
{	
	$width=$width-2*$padding;
	$height=$height-2*$padding;
	$top_left_x=$top_left_x+$padding;
	$top_left_y=$top_left_y+$padding;


	// Set the colors
	$blue=ImageColorAllocate($im,0,0,255);
	$dark_green=ImageColorAllocate($im,0,125,0);
	$clear_blue=ImageColorAllocate($im,100,255,240);
	$black=ImageColorAllocate($im,0,0,0);

	$max=0;

	//Find all maximums
	$max=max($max,max($datas['hits']),max($datas['pages']),max($datas['files']));
	$scale=$height/$max;

	//Show lengend
	imagestringup($im,3,$top_left_x-$padding-15,$top_left_y+$height,'Pages',$clear_blue);
	imagestringup($im,2,$top_left_x-$padding-15,$top_left_y+$height-38,'/',$black);
	imagestringup($im,3,$top_left_x-$padding-15,$top_left_y+$height-48,'Files',$blue);
	imagestringup($im,2,$top_left_x-$padding-15,$top_left_y+$height-85,'/',$black);
	imagestringup($im,3,$top_left_x-$padding-15,$top_left_y+$height-95,'Hits',$dark_green);
	imagestringup($im,2,$top_left_x-$padding-15,$top_left_y-$padding+strlen($max)*6,$max,$black);

	$part_width=$width/($size+1-$GLOBALS['begin_for']);
	$part_spacing=0.2*$part_width;
	$bar_offset=intval($part_spacing);
	$bar_width=($part_width-$part_spacing)-($bar_offset*2);

	//Draw the Histogram
	for($i=$GLOBALS['begin_for'];$i<=$size;$i++)
	{
		if(!isset($datas['hits'][$i]))
		{
			$datas['hits'][$i]=0;
			$datas['pages'][$i]=0;
			$datas['files'][$i]=0;
		}
		draw_bar($im,$top_left_x+($i-$GLOBALS['begin_for'])*$part_width,$top_left_y,$bar_width,$height,$scale,$datas['hits'][$i],$dark_green);
		draw_bar($im,($top_left_x+($i-$GLOBALS['begin_for'])*$part_width)+$bar_offset,$top_left_y,$bar_width,$height,$scale,$datas['files'][$i],$blue);
		draw_bar($im,($top_left_x+($i-$GLOBALS['begin_for'])*$part_width)+2*$bar_offset,$top_left_y,$bar_width,$height,$scale,$datas['pages'][$i],$clear_blue);
	}
}

function make_total_visits_histogram($im,$top_left_x,$top_left_y,$width,$height,$padding,$datas,$size)
{	
	$width=$width-2*$padding;
	$height=$height-2*$padding;
	$top_left_x=$top_left_x+$padding;
	$top_left_y=$top_left_y+$padding;


	// Set the colors
	$yellow=ImageColorAllocate($im,255,255,0);
	$black=ImageColorAllocate($im,0,0,0);

	$max=0;

	//Find the maximum
	$max=max($datas['visits']);
	$scale=$height/$max;

	//Show lengend
	imagestringup($im,3,$top_left_x-$padding-15,$top_left_y+$padding+$height,'Visits',$yellow);
	imagestringup($im,2,$top_left_x-$padding-15,$top_left_y-$padding+strlen($max)*6,$max,$black);

	$part_width=$width/($size+1-$GLOBALS['begin_for']);
	$part_spacing=0.2*$part_width;

	//Draw the Histogram
	for($i=$GLOBALS['begin_for'];$i<=$size;$i++)
	{
		if(!isset($datas['visits'][$i]))
			$datas['visits'][$i]=0;
		draw_bar($im,$top_left_x+($i-$GLOBALS['begin_for'])*$part_width,$top_left_y,$part_width-$part_spacing,$height,$scale,$datas['visits'][$i],$yellow);
	}
}

function make_total_bytes_histogram($im,$top_left_x,$top_left_y,$width,$height,$padding,$datas,$size)
{	
	$width=$width-2*$padding;
	$height=$height-2*$padding;
	$top_left_x=$top_left_x+$padding;
	$top_left_y=$top_left_y+$padding;


	// Set the colors
	$red=ImageColorAllocate($im,255,0,0);
	$black=ImageColorAllocate($im,0,0,0);

	$max=0;

	//Find the maximum
	$max=max($datas['bytes']);
	$scale=$height/$max;

	//Show lengend
	imagestringup($im,3,$top_left_x-$padding-15,$top_left_y+$padding+$height,'KBytes',$red);
	imagestringup($im,2,$top_left_x-$padding-15,$top_left_y-$padding+strlen(intval($max))*6,intval($max),$black);

	$part_width=$width/($size+1-$GLOBALS['begin_for']);
	$part_spacing=0.2*$part_width;
	
	//Draw the Histogram
	for($i=$GLOBALS['begin_for'];$i<=$size;$i++)
	{
		if(!isset($datas['bytes'][$i]))
			$datas['bytes'][$i]=0;
		draw_bar($im,$top_left_x+($i-$GLOBALS['begin_for'])*$part_width,$top_left_y,$part_width-$part_spacing,$height,$scale,$datas['bytes'][$i],$red);
		$position=$top_left_x+($i-$GLOBALS['begin_for'])*$part_width+$part_width/2-strlen($i)*4;
		imagestring($im,2,$position,$top_left_y+$padding+$height,$i,$black);
	}
}

function draw_border($im,$top_left_x,$top_left_y,$top_right_x,$top_right_y,$bottom_left_x,$bottom_left_y)
{
	// Set the colors
	$hard_grey=ImageColorAllocate($im,80,80,80);
	$white=ImageColorAllocate($im,255,255,255);
	$black=ImageColorAllocate($im,0,0,0);

	//A black and white border
	imageline($im,$top_left_x,$top_left_y,$top_right_x,$top_right_y,$white);
	imageline($im,$top_right_x,$top_right_y,$top_right_x,$bottom_left_y,$white);
	imageline($im,$top_right_x,$bottom_left_y,$bottom_left_x,$bottom_left_y,$white);
	imageline($im,$bottom_left_x,$bottom_left_y,$top_left_x,$top_left_y,$white);
	imageline($im,$top_left_x+1,$top_left_y+1,$top_right_x-1,$top_right_y+1,$black);
	imageline($im,$top_right_x+1,$top_right_y+1,$top_right_x+1,$bottom_left_y+1,$black);
	imageline($im,$top_right_x+1,$bottom_left_y+1,$bottom_left_x+1,$bottom_left_y+1,$black);
	imageline($im,$bottom_left_x+1,$bottom_left_y-1,$top_left_x+1,$top_left_y+1,$black);

	//A scale drawn in the background
	$scale_y=$top_left_y+(($bottom_left_y-$top_left_y)/3);
	imageline($im,$top_left_x+2,$scale_y,$top_right_x-1,$scale_y,$hard_grey);
	$scale_y=$top_left_y+2*(($bottom_left_y-$top_left_y)/3);
	imageline($im,$top_left_x+2,$scale_y,$top_right_x-1,$scale_y,$hard_grey);
}

function total_histogram($total_tab,$img_width,$img_height,$caption,$size)
{
header("Content-type: image/png");

	// Create image and Set the colors
	$im=imagecreatetruecolor(intval($img_width),intval($img_height));
	$grey=ImageColorAllocate($im,180,180,180);
	$little_grey=ImageColorAllocate($im,220,220,220);
	$hard_grey=ImageColorAllocate($im,60,60,60);
	$blue=ImageColorAllocate($im,0,0,255);

	$margin=20; //graph margin
	$padding=10; //Histogram padding
	$border_size=3; //main border size
	$ratio=0.45;

	//Fill the graph with grey background
	$im=imagecreatetruecolor(intval($img_width),intval($img_height));
	imagefill($im,0,0,$grey);

	//Color the first part of the main border
	$borger_top_left=array(0,$img_height,
		0,0,
		$img_width,0,
		$img_width-$border_size,$border_size,
		$border_size,$border_size,
		$border_size,$img_height-$border_size,
       );
	ImageFilledPolygon($im, $borger_top_left, 6, $little_grey);
	
	//Color the second part of the main border
	$border_top_right=array(0,$img_height,
		$img_width,$img_height,
		$img_width,0,
		$img_width-$border_size,$border_size,
		$img_width-$border_size,$img_height-$border_size,
		$border_size,$img_height-$border_size,
       );
	ImageFilledPolygon($im,$border_top_right,6,$hard_grey);

	//writing caption
	imagestring($im, 3, $margin, 6, $caption, $blue);

	//create 3 borders for 3 histograms
	draw_border($im,$margin,$margin,$img_width-$margin,$margin,$margin,$margin+($img_height-2*$margin)*$ratio);
	make_total_histogram($im,$margin,$margin,$img_width-2*$margin,($img_height-2*$margin)*$ratio,$padding,$total_tab,$size);
	$y_foo=$margin+($img_height-2*$margin)*$ratio+($img_height-2*$margin)*(1-$ratio)/2;
	draw_border($im,$margin,$margin+($img_height-2*$margin)*$ratio,$img_width-$margin,$margin+($img_height-2*$margin)*$ratio,$margin,$y_foo);
	make_total_visits_histogram($im,$margin,$margin+($img_height-2*$margin)*$ratio,($img_width-2*$margin),($img_height-2*$margin)*(1-$ratio)/2,$padding,$total_tab,$size);
	draw_border($im,$margin,$y_foo,$img_width-$margin,$y_foo,$margin,$img_height-$margin);
	make_total_bytes_histogram($im,$margin,$y_foo,$img_width-2*$margin,($img_height-2*$margin)*(1-$ratio)/2,$padding,$total_tab,$size);
	
	imagepng($im); //Show the histogram created
	imagedestroy($im); //Free the memory and destroy the histogram

}
$caption=$_GET['title'].' for '.$_SERVER['SERVER_NAME'];

if ($_GET['type']==24)
	{
		$tabindex='heure';
		$GLOBALS['begin_for']=0;
		$_GET['type']--;
	}
else	
	{
	 $tabindex='jour';
	$GLOBALS['begin_for']=1;
   }
   
	total_histogram($tableau[$_GET['month']][$tabindex],600,550,$caption,$_GET['type']);
?>
