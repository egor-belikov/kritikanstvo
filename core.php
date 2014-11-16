<?php

include ('connect.php');

define("IMG_CONST", 8);
define("MINREVIEWS", 7);
//define("MINBAYES", 20);
define("BAYESTOP", 85);
define("BAYESMIDDLETOP", 65);
define("BAYESMIDDLEBOTTOM", 40);
define("BAYESBOTTOM", 20);


define("MINBAYES", 7);
define("MINPERSONBAYES", 50);

mb_regex_encoding("UTF-8");
mb_internal_encoding("UTF-8");

$errors_types=array('wrong_rating'=>'Неверная оценка', 'wrong_url'=>'Неправильная ссылка', 'technical_bug'=>'Техническая ошибка', 'description_error'=>'Ошибка в описании фильма/игры', 'summary_error'=>'Неверная выдержка из рецензии', 'typo'=>'Опечатка', 'other'=>'Другое');

$date_eng=array ('January','February','March','April','May','June','July','August','September','October','November', 'December');
$date_rss=array ('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov', 'Dec');
$date_rus=array ('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября', 'декабря');
$months_rus=array ('январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь', 'декабрь');
$date_weekdays=array ('воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье');
$weekdays_rus=array ('понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье');
$weekdays_rus_vp=array ('понедельник', 'вторник', 'среду', 'четверг', 'пятницу', 'субботу', 'воскресенье');
$weekdays_eng=array ('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
$date_days=array (31,28,31,30,31,30,31,31,30,31,30,31);
$numbers_m=array ('ноль','один','два','три','четыре','пять','шесть','семь','восемь','девять','десять');
$numbers_s=array ('ноль','одно','два','три','четыре','пять','шесть','семь','восемь','девять','десять');
$numbers_zh=array ('ноль','одна','две','три','четыре','пять','шесть','семь','восемь','девять','десять');
$numbers_padezh_rod=array ('нуля','одного','двух','трех','четырех','пяти','шести','семи','восьми','девяти','десяти');

$countries_translate=array ('USA'=>'США', 'UK'=>'Великобритания', 'Russia'=>'Россия', 'Germany'=>'Германия', 'Italy'=>'Италия', 'Japan'=>'Япония', 'China'=>'Китай', 'Australia'=>'Австралия', 'Belarus'=>'Белоруссия', 'Canada'=>'Канада', 'France'=>'Франция', 'Belgium'=>'Бельгия', 'Portugal'=>'Португалия', 'Bulgaria'=>'Болгария');

function nice_number_format ($number, $decimal=1)
{
	if ($number==intval($number))
		return number_format($number,0,',',' ');
	else
		return number_format($number,$decimal,',',' ');	
}

function imdbsearch ($title, $form_to_insert_link, $form_to_insert_english, $form_to_insert_code)
{
	$echo='';
	$imdb=file_get_contents('http://www.imdb.com/find?s=tt&q='.urlencode($title));
	$imdbfnd=array ('Popular Titles', 'Titles (Exact Matches)', 'Titles (Approx Matches)');
	$imdbrus=array ('Популярные названия', 'Точные совпадения', 'Примерные совпадения');
	$fnd=0;
	foreach ($imdbfnd as $key=>$im)
	{
		$i=betweens($imdb,array('<b>'.$im.'</b>','</table>','<table>'));
		if ($i!='') {

			$i=preg_replace ('/<a href="\/title\/tt([0-9]+)\/"/', '<span onClick="$(\'#'.$form_to_insert_link.'\').val(\'http://www.imdb.com/title/tt\\1\');" class="dashedlink">&uarr;</span> <a target="_blank" href="http://www.imdb.com/title/tt\\1"', $i);
			$correct_title=between($i,'">','<');
			// echo $correct_title;
			
			$i.=' <span class="dashedlink" onClick="$(\'#'.$form_to_insert_english.'\').val(\''.$correct_title_shorten.'\'); $(\'#'.$form_to_insert_code.'\').val(\''.nametocode($correct_title_shorten).'\'); $(\'#'.$form_to_insert_link.'\').val(\'http://www.imdb.com/title/tt'.$imdbnum.'\');">&uarr; название + код &uarr;</span>';
			
			$echo.='<b>'.$imdbrus[$key].'</b><br><br><table>'.$i.'</table>';
			$fnd++;
		}
	}
	if ($fnd==0)
	{	
		$imdbnum=betweens ($imdb, array ('title/tt', '/'));
		if ($imdbnum!='') {
			$correct_title=str_replace(' - IMDb', '', betweens ($imdb, array ('<title>', '</title>')));
			if (mb_strpos($correct_title,' (')!==FALSE)
				$correct_title_shorten=mb_strrchr($correct_title,' (',true);
			else
				$correct_title_shorten=$correct_title;
			$echo.='<div><b>Нашлось ровно одно точное совпадение:</b><br><span class="dashedlink" onClick="$(\'#'.$form_to_insert_link.'\').val(\'http://www.imdb.com/title/tt'.$imdbnum.'\');">&uarr;&uarr;&uarr;</span> <a href="http://www.imdb.com/title/tt'.$imdbnum.'" target="_blank">'.$correct_title.'</a> <span class="dashedlink" onClick="$(\'#'.$form_to_insert_english.'\').val(\''.$correct_title_shorten.'\'); $(\'#'.$form_to_insert_code.'\').val(\''.nametocode($correct_title_shorten).'\'); $(\'#'.$form_to_insert_link.'\').val(\'http://www.imdb.com/title/tt'.$imdbnum.'\');">&uarr; название + код &uarr;</span><br><br></div>';
		}

	}
	echo $echo;
}

	
	
function pages_bs ($page, $nr, $onpage=30, $max=7, $title='', $left='<', $right='>', $first='<<', $last='>>', $baseurl='', $pageurl='', $addon1='')
{

	// Для reviews это:
	// pages ($page, $nr, 30, 7, 'Страницы', '«', '»', '« Первая', 'Последняя »', '/reviews/'.$section, 'p_', '/award'
	$pages=(int)(($nr-1)/$onpage)+1;
	
	$middle=ceil($max/2);

	if ($nr>$onpage)
	{

		echo '<div class="pagination">'.($title!=''?'<span>'.$title.' ('.$pages.'):</span>':'').'<ul>';

		if ($page>1) echo '<li><a href="'.$baseurl.$pageurl.($page-1).($addon1!=''?$addon1:'').'"">'.$left.'</a></li>';
		
		if ($page>=$middle+1 && $pages>$max)
		{
			echo '<li><a href="'.$baseurl.$pageurl.'1'.($addon1!=''?$addon1:'').'">1</a></li><li class="disabled"><a href="#">...</a></li>';
			$begin=$page-$middle+1;
		} else $begin=1;
		
		
		if ($page<=$pages-$middle && $pages>$max)
		    $end=$page+$middle-1;
		else $end=$pages;
		
		for ($i=$begin; $i<=$end; $i++)
			if ($i!=$page) echo '<li><a href="'.$baseurl.$pageurl.$i.($addon1!=''?$addon1:'').'">'.$i.'</a></li>';
				else echo '<li class="active"><a href="#">'.$i.'</a></li>';

		if ($page<=$pages-$middle && $pages>$max)
			echo '<li class="disabled"><a href="#">...</a></li><li><a href="'.$baseurl.$pageurl.$pages.($addon1!=''?$addon1:'').'"">'.$pages.'</a></li>';

		if ($page<$pages) echo '<li><a href="'.$baseurl.$pageurl.($page+1).($addon1!=''?$addon1:'').'"">'.$right.'</a></li>';

		echo '</ul></div>';
	}
}


function upload_image ($webfile, $userfile, $imagefile, $what)
{

		// $imagefile - полный путь к картинке на нашем серваке, типа "img/20345_big.jpg"
		// $thumbfile - полный путь к миниатюре на нашем серваке, типа "img/20345.jpg"
		// $what - images или posters
		
		
		$working=TRUE;
		
		if ($userfile!='') {
			@$res=copy($userfile,$imagefile);
		} else 
		{
			if (mb_substr($webfile,0,7)=='http://' || mb_substr($webfile,0,6)=='ftp://')
			{
				$wg=getimagesize($webfile);
				if ($wg[0]!=0) @$res=copy($webfile,$imagefile);
					else $working=FALSE;
			} else $working=FALSE;
		}
		
		if ($working && $what!='images')
		{
			chmod ($imagefile,0777);
			
			$thumb = new Imagick($imagefile);
			$thumbsmall = new Imagick($imagefile);
			
			
			if ($thumb->getImageWidth()/$thumb->getImageHeight()>180/260)
			{
				$h=260;
				$w=round($thumb->getImageWidth()*260/$thumb->getImageHeight());
			} else
			{
				$w=180;
				$h=round($thumb->getImageHeight()*180/$thumb->getImageWidth());
			}
			
			if ($thumb->getImageWidth()/$thumb->getImageHeight()>48/68)
			{
				$hs=68;
				$ws=round($thumb->getImageWidth()*68/$thumb->getImageHeight());
			} else
			{
				$ws=48;
				$hs=round($thumb->getImageHeight()*48/$thumb->getImageWidth());
			}
			

			$thumb->setImageCompression(imagick::COMPRESSION_JPEG);
			$thumb->setImageCompressionQuality(95);
			$thumb->resizeImage($w, $h, Imagick::FILTER_LANCZOS,1);
			$thumb->writeImage(str_replace('.jpg','t.jpg',$imagefile));
			chmod (str_replace('.jpg','t.jpg',$imagefile),0777);
			
			$thumbsmall->setImageCompression(imagick::COMPRESSION_JPEG);
			$thumbsmall->setImageCompressionQuality(95);
			$thumbsmall->resizeImage($ws, $hs, Imagick::FILTER_LANCZOS,1);
			$thumbsmall->writeImage(str_replace('.jpg','s.jpg',$imagefile));
			chmod (str_replace('.jpg','s.jpg',$imagefile),0777);
			
		}
		
}


function upload_image_local ($webfile, $userfile, $thumbfile, $imagefile, $w, $h=0)
{

		// $imagefile - полный путь к картинке на нашем серваке, типа "img/20345_big.jpg"
		// $thumbfile - полный путь к миниатюре на нашем серваке, типа "img/20345.jpg"
		
		$working=TRUE;
		$fromgallery=FALSE;
		
		if ($userfile!='') {
			@$res=copy($userfile,$thumbfile);
			// echo 'done';
		} else 
		{
			if (mb_substr($webfile,0,7)=='http://')
			{
				$wg=getimagesize($webfile);
				if ($wg[0]==0) $working=FALSE;
				else @$res=copy($webfile,$thumbfile);
			} else
			{
				$webfile=trim($webfile,'/');
				$pieces=explode('/',$webfile);
				if (sizeof($pieces)<5) $working=FALSE;
				else $fromgallery=TRUE;
			}
		}
		if ($working)
		{
			if (!$fromgallery)
			{
				chmod ($thumbfile,0777);
				
				$thumb = new Imagick($thumbfile);
				
				if (substr($w,0,1)=='h') {$h=substr($w,1); $w=0; }
					else if (substr($w,0,1)=='w') { $w=substr($w,1); $h=0; }
						//else { $w=$pixels; $h=0; }

				if ($thumb->getImageWidth()>$w || $thumb->getImageHeight()>$h || $thumb->getImageFormat()!='JPEG')
				{
					$thumb->setImageCompression(imagick::COMPRESSION_JPEG);
					$thumb->setImageCompressionQuality(95);
					
					//
					if ($w!=0 && $h!=0)
					{
						$thumb->resizeImage($w, $h, Imagick::FILTER_LANCZOS,1,TRUE);
						$thumb->cropImage($w, $h, ($thumb->getImageWidth()-$w)/2, ($thumb->getImageHeight()-$h)/2);
					}
					//
					
					else $thumb->resizeImage($w, $h, Imagick::FILTER_LANCZOS,1,FALSE);
					$thumb->writeImage($thumbfile);
					if ($imagefile!='') 
					{
						if ($userfile!='') @$res=copy($userfile,$imagefile);
									  else @$res=copy($webfile,$imagefile);
						chmod ($imagefile,0777);
					}
				} 
			} else
			{
				$imagefile='http://www.kino-govno.com/'.$webfile;
				$thumbfile = 'http://media.kino-govno.com/'.$pieces[0].'/'.mb_substr($pieces[1],0,1).'/'.$pieces[1].'/'.$pieces[3].'/'.$pieces[1].'_'.$pieces[4].'s.jpg';
			}
			
		}
		
}

function correctphone ($phone)
{
	$phone=eregi_replace('[^0-9]+','',$phone);
	if (mb_strlen($phone)==10) $phone='+7'.$phone;
	if (mb_strlen($phone)==11 && (mb_substr($phone,0,1)=='8' || mb_substr($phone,0,1)=='7')) $phone='+7'.mb_substr($phone,1);
	return $phone;
}

function nicephone ($phone)
{
	$nice='';
	$phone=correctphone($phone);
	if (mb_strlen($phone)>=7) $nice.=mb_substr($phone,-7,3).' '.mb_substr($phone,-4);
	if (mb_strlen($phone)>=10) $nice=mb_substr($phone,-10,3).' '.$nice;
	if (mb_strlen($phone)>=11) $nice=mb_substr($phone,0,-10).' '.$nice;
	return $nice;
}

function natural_list_to_array ($list)
// Переводит список цифр типа "1,2, 3-10" в массив
{
	$a=array ();
	//$head=preg_split('(\-|\,)',$list);
	$head=preg_split('(\,)',$list);
	foreach ($head as $h)
	{
		$li=preg_split('(\-)',$h);
		if (sizeof($li)>1)
		{
			if (mb_strpos($li[0],'x')!==FALSE)
			{
				$tempseason=mb_strstr($li[0],'x',TRUE);
				$tempa=mb_substr(mb_strstr($li[0],'x'),-1);
				if (mb_strpos($li[1],'x')!==FALSE)
					$tempb=mb_substr(mb_strstr($li[1],'x'),-1);
				else $tempb=$li[1];
				for ($i=(int)$tempa; $i<=(int)$tempb; $i++)
					$a[]=$tempseason.'x'.$i;

			} else {
				for ($i=(int)$li[0]; $i<=(int)$li[1]; $i++)
					$a[]=$i;
			}
		}
		else $a[]=(int)$h;
	}
	return $a;
}

function iscaps ($str)
{

	if (utf8_uppercase($str)==$str) return TRUE;
		else return FALSE;
}


// Закрывает теги, если не закрыты вдруг
function closehtmltags ($text, $tags=array ('<span>', '<div>', '<p>'))
{
	foreach ($tags as $t)
	{
		$t=mb_trim($t,'<> ');
		$num_open=mb_substr_count(utf8_lowercase($text),utf8_lowercase('<'.$t));
		$num_close=mb_substr_count(utf8_lowercase($text),utf8_lowercase('</'.$t.'>'));
		if ($num_close>$num_open)
			$text=str_repeat('<'.$t.'>',$num_close-$num_open).$text;
		else
		if ($num_close<$num_open)
			$text.=str_repeat('</'.$t.'>',$num_open-$num_close);
	}
	return $text;
}

// Случайное число за исключением тех, что в массиве
function rand_except($min, $max, $excepting = array())
{
	if (sizeof($excepting)<$max-$min+1)
	{
    	$num = mt_rand($min, $max);
    	return in_array($num, $excepting) ? rand_except($min, $max, $excepting) : $num;
    } else return '';
}

// Ровно одна замена через str_replace

function str_replace_once ($search, $replace, $data) { 
    $res = mb_strpos($data, $search); 
    if($res === false) { 
        return $data; 
    } else { 
        // There is data to be replaced 
        $left_seg = mb_substr($data, 0, mb_strpos($data, $search)); 
        $right_seg = mb_substr($data, (mb_strpos($data, $search) + mb_strlen($search))); 
        return $left_seg . $replace . $right_seg; 
    } 
}  


// Есть ли строка в тексте?

function intext ($needle, $haystack, $case=FALSE)
{
	if ($case) // Если регистр без разницы (по умолчанию)
	{
		if (mb_strpos($haystack,$needle)!==FALSE) return TRUE;
			else return FALSE;
	} else // Если регистр важен
	{
		if (mb_strpos(utf8_lowercase($haystack),utf8_lowercase($needle))!==FALSE) return TRUE;
			else return FALSE;
	
	}
} 


function mb_trim ($string, $charlist='\\\\s', $ltrim=true, $rtrim=true) 
{ 
    $both_ends = $ltrim && $rtrim; 

    $char_class_inner = preg_replace( 
        array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ), 
        array( '\\\\\\0', '\\' ), 
        $charlist 
    ); 

    $work_horse = '[' . $char_class_inner . ']+'; 
    $ltrim && $left_pattern = '^' . $work_horse; 
    $rtrim && $right_pattern = $work_horse . '$'; 

    if($both_ends) 
    { 
        $pattern_middle = $left_pattern . '|' . $right_pattern; 
    } 
    elseif($ltrim) 
    { 
        $pattern_middle = $left_pattern; 
    } 
    else 
    { 
        $pattern_middle = $right_pattern; 
    } 

    return preg_replace("/$pattern_middle/usSD", '', $string); 
} 

function textarea ($text, $id, $value='', $width='300', $height='50')
{
	field ($text, $id, $value, 'textarea', '', '', (mb_substr($width,-1)!='%' && $width!=''?$width.'px':''), (mb_substr($height,-1)!='%' && $height!=''?$height.'px':''));
}

function shortfield ($text, $id, $value='')
{
	field ($text, $id, $value, 'text', '', '', '100px;', '50px;');
}

function field ($text, $id, $value='', $type='text', $subheaders='', $subvalues='', $width='300px;', $height='50px;', $class='')
{
		// $headers - для radio и checkbox, подзаголовки
		// $subvalues - для radio, чтобы знать, что делать checked
		
		echo ($text!='' && mb_substr($text,0,6)!='<nobr>'?'<br>'.$text:mb_substr($text,6)).(mb_substr($text,0,6)=='<nobr>'?'':'<br>').
		($type=='textarea'?'<textarea name="'.$id.'" id="'.$id.'" style="'.(mb_substr($text,0,6)!='<nobr>'?'margin-left: 20px; ':'').'width: '.$width.'; height: '.$height.';"'.($class!=''?' class="'.$class.'"':'').'>':'').
		($type!='textarea' && $type!='radio' && $type!='checkbox' && $type!='select'?'<input type="'.$type.'" value="'.$value.'" name="'.$id.'" id="'.$id.'" style="'.(mb_substr($text,0,6)!='<nobr>'?'margin-left: 20px; ':'').'width: '.$width.'"'.($class!=''?' class="'.$class.'"':'').'>':'').
		($type=='textarea'?$value.'</textarea>':'');
		$i=0;
		if ($type=='radio' || $type=='checkbox')
			foreach ($subvalues as $key=>$val)
				{
					echo '<nobr><input type="'.$type.'" name="'.$id.
					($type=='checkbox'?'['.$i.']':'').'" id="'.
					$id.($type=='radio'?$val:'').
					($type=='checkbox'?'['.$i.']':'').
					'" value="'.$val.
					'" style="margin-left: 20px;"'.
					($val==$value && $type=='radio'?' checked':'').
					($val==$value[$key] && $type=='checkbox'?' checked':'').
					'> '.$subheaders[$key].'</nobr> ';
				$i++;
				}		
}

function button ($text, $id, $icon='', $type='button', $onclick='', $tabindex='')
{
		echo '<button class="'.$type.'" style="margin-right: 10px;" name="'.$id.'" id="'.$id.'" value="'.$id.'" '.($onclick!=''?'onclick="'.$onclick.'" ':'').'type="submit"'.($tabindex!=''?' tabindex="'.$tabindex.'"':'').'>'.($icon!=''?'<span class="'.$icon.' icon"></span>':'').$text.'</button>';

}


function br ($num=1)
{
	for ($i=1; $i<=$num; $i++)
		echo '<br />';
}


function str_trim ($text,$str=' ')
{
	while (mb_substr($text,0,mb_strlen($str))==$str)
		$text=mb_substr($text,mb_strlen($str));
		
	while (mb_substr($text,-mb_strlen($str))==$str)
		$text=mb_substr($text,0,-mb_strlen($str));
	
	return $text;

}

function is_tags ($str, $tags)
{
	$str=' '.mb_trim($str).' ';
	$tags=' '.mb_trim($tags).' ';
	if (mb_strpos($str, $tags)!==FALSE) return TRUE;
	else return FALSE;
}

function insert_tags ($tags_old, $tags_new, $tc=' ')
{
	$tags_old=$tc.str_trim($tags_old,$tc).$tc;
	$tags_new_array=mb_split ($tc,str_trim($tags_new,$tc));
	
	$tags_to_add='';
	
	foreach ($tags_new_array as $tna)
		if (mb_strpos($tags_old,$tc.$tna.$tc)===FALSE) $tags_to_add.=$tna.$tc;
	
	if (str_trim($tags_to_add,$tc)!='')
		$tags_old.=str_trim($tags_to_add,$tc).$tc;
	
	$ret=str_trim($tags_old,$tc);
	if ($ret!='') return $tc.$ret.$tc;
	else return $ret;
}

function delete_tags ($tags_old, $tags_new, $tc=' ')
{
	$tags_old=$tc.str_trim($tags_old,$tc).$tc;
	$tags_new_array=mb_split ($tc,str_trim($tags_new,$tc));
	
	foreach ($tags_new_array as $tna)
		$tags_old=$tc.str_trim(str_replace($tc.$tna.$tc,' ',$tags_old),$tc).$tc;
	
	$ret=str_trim($tags_old,$tc);
	if ($ret!='') return $tc.$ret.$tc;
	else return $ret;
}


function utf8_ord($char) # = utf8_to_unicode()
{
    static $cache = array();
    if (array_key_exists($char, $cache)) return $cache[$char]; #speed improve

    switch (strlen($char))
    {
        case 1 : return $cache[$char] = ord($char);
        case 2 : return $cache[$char] = (ord($char{1}) & 63) |
                                        ((ord($char{0}) & 31) << 6);
        case 3 : return $cache[$char] = (ord($char{2}) & 63) |
                                        ((ord($char{1}) & 63) << 6) |
                                        ((ord($char{0}) & 15) << 12);
        case 4 : return $cache[$char] = (ord($char{3}) & 63) |
                                        ((ord($char{2}) & 63) << 6) |
                                        ((ord($char{1}) & 63) << 12) |
                                        ((ord($char{0}) & 7)  << 18);
        default :
            trigger_error('Character is not UTF-8!', E_USER_WARNING);
            return false;
    }#switch
}

function utf8_chr($cp) # = utf8_from_unicode()
{
    static $cache = array();
    $cp = intval($cp);
    if (array_key_exists($cp, $cache)) return $cache[$cp]; #speed improve

    if ($cp <= 0x7f)     return $cache[$cp] = chr($cp);
    if ($cp <= 0x7ff)    return $cache[$cp] = chr(0xc0 | ($cp >> 6))  .
                                              chr(0x80 | ($cp & 0x3f));
    if ($cp <= 0xffff)   return $cache[$cp] = chr(0xe0 | ($cp >> 12)) .
                                              chr(0x80 | (($cp >> 6) & 0x3f)) .
                                              chr(0x80 | ($cp & 0x3f));
    if ($cp <= 0x10ffff) return $cache[$cp] = chr(0xf0 | ($cp >> 18)) .
                                              chr(0x80 | (($cp >> 12) & 0x3f)) .
                                              chr(0x80 | (($cp >> 6) & 0x3f)) .
                                              chr(0x80 | ($cp & 0x3f));
    #U+FFFD REPLACEMENT CHARACTER
    return $cache[$cp] = "\xEF\xBF\xBD";
}

function utf16win ($strin)  { 
$strin = ereg_replace("%u0430","а",$strin); 
$strin = ereg_replace("%u0431","б",$strin); 
$strin = ereg_replace("%u0432","в",$strin); 
$strin = ereg_replace("%u0433","г",$strin); 
$strin = ereg_replace("%u0434","д",$strin); 
$strin = ereg_replace("%u0435","е",$strin); 
$strin = ereg_replace("%u0451","ё",$strin); 
$strin = ereg_replace("%u0436","ж",$strin); 
$strin = ereg_replace("%u0437","з",$strin); 
$strin = ereg_replace("%u0438","и",$strin); 
$strin = ereg_replace("%u0439","й",$strin); 
$strin = ereg_replace("%u043A","к",$strin); 
$strin = ereg_replace("%u043B","л",$strin); 
$strin = ereg_replace("%u043C","м",$strin); 
$strin = ereg_replace("%u043D","н",$strin); 
$strin = ereg_replace("%u043E","о",$strin); 
$strin = ereg_replace("%u043F","п",$strin); 
$strin = ereg_replace("%u0440","р",$strin); 
$strin = ereg_replace("%u0441","с",$strin); 
$strin = ereg_replace("%u0442","т",$strin); 
$strin = ereg_replace("%u0443","у",$strin); 
$strin = ereg_replace("%u0444","ф",$strin); 
$strin = ereg_replace("%u0445","х",$strin); 
$strin = ereg_replace("%u0446","ц",$strin); 
$strin = ereg_replace("%u0448","ш",$strin); 
$strin = ereg_replace("%u0449","щ",$strin); 
$strin = ereg_replace("%u044A","ъ",$strin); 
$strin = ereg_replace("%u044C","ь",$strin); 
$strin = ereg_replace("%u044D","э",$strin); 
$strin = ereg_replace("%u044E","ю",$strin); 
$strin = ereg_replace("%u044F","я",$strin); 
$strin = ereg_replace("%u0447","ч",$strin); 
$strin = ereg_replace("%u044B","ы",$strin); 
$strin = ereg_replace("%u0410","А",$strin); 
$strin = ereg_replace("%u0411","Б",$strin); 
$strin = ereg_replace("%u0412","В",$strin); 
$strin = ereg_replace("%u0413","Г",$strin); 
$strin = ereg_replace("%u0414","Д",$strin); 
$strin = ereg_replace("%u0415","Е",$strin); 
$strin = ereg_replace("%u0416","Ж",$strin); 
$strin = ereg_replace("%u0417","З",$strin); 
$strin = ereg_replace("%u0418","И",$strin); 
$strin = ereg_replace("%u0419","Й",$strin); 
$strin = ereg_replace("%u041A","К",$strin); 
$strin = ereg_replace("%u041B","Л",$strin); 
$strin = ereg_replace("%u041C","М",$strin); 
$strin = ereg_replace("%u041D","Н",$strin); 
$strin = ereg_replace("%u041E","О",$strin); 
$strin = ereg_replace("%u041F","П",$strin); 
$strin = ereg_replace("%u0420","Р",$strin); 
$strin = ereg_replace("%u0421","С",$strin); 
$strin = ereg_replace("%u0422","Т",$strin); 
$strin = ereg_replace("%u0423","У",$strin); 
$strin = ereg_replace("%u0424","Ф",$strin); 
$strin = ereg_replace("%u0425","Х",$strin); 
$strin = ereg_replace("%u0426","Ц",$strin); 
$strin = ereg_replace("%u0428","Ш",$strin); 
$strin = ereg_replace("%u0429","Щ",$strin); 
$strin = ereg_replace("%u042A","Ъ",$strin); 
$strin = ereg_replace("%u042C","Ь",$strin); 
$strin = ereg_replace("%u042D","Э",$strin); 
$strin = ereg_replace("%u042E","Ю",$strin); 
$strin = ereg_replace("%u042F","Я",$strin); 
$strin = ereg_replace("%u0427","Ч",$strin); 
$strin = ereg_replace("%u042B","Ы",$strin); 
$strin = ereg_replace("%u041","Ё",$strin); 
return $strin; 
}

function lastof ($array)
{
	return $array[sizeof($array)-1];
}


function getfile ($url,$headers=false) {
    $url = parse_url($url);

    if (!isset($url['port'])) {
      if ($url['scheme'] == 'http') { $url['port']=80; }
      elseif ($url['scheme'] == 'https') { $url['port']=443; }
    }
    $url['query']=isset($url['query'])?$url['query']:'';

    $url['protocol']=$url['scheme'].'://';
    $eol="\r\n";

    $headers =  "POST ".$url['protocol'].$url['host'].$url['path']." HTTP/1.0".$eol. 
                "Host: ".$url['host'].$eol. 
                "Referer: ".$url['protocol'].$url['host'].$url['path'].$eol. 
                "Content-Type: application/x-www-form-urlencoded".$eol. 
                "Content-Length: ".strlen($url['query']).$eol.
                $eol.$url['query'];

    
    
    $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30); 
    if($fp) {
      fputs($fp, $headers);
      $result = '';
      while(!feof($fp)) { $result .= fgets($fp, 128); }
      fclose($fp);
      if (!$headers) {
        //removes headers
        $pattern="/^.*\r\n\r\n/s";
        $result=preg_replace($pattern,'',$result);
      }
      return $result;
    }
}


function utf8_convert_case($s, $mode)
{

    #таблица конвертации регистра
    static $trans = array(
        #en (английский латиница)
        #CASE_UPPER => case_lower
        "\x41" => "\x61", #A a
        "\x42" => "\x62", #B b
        "\x43" => "\x63", #C c
        "\x44" => "\x64", #D d
        "\x45" => "\x65", #E e
        "\x46" => "\x66", #F f
        "\x47" => "\x67", #G g
        "\x48" => "\x68", #H h
        "\x49" => "\x69", #I i
        "\x4a" => "\x6a", #J j
        "\x4b" => "\x6b", #K k
        "\x4c" => "\x6c", #L l
        "\x4d" => "\x6d", #M m
        "\x4e" => "\x6e", #N n
        "\x4f" => "\x6f", #O o
        "\x50" => "\x70", #P p
        "\x51" => "\x71", #Q q
        "\x52" => "\x72", #R r
        "\x53" => "\x73", #S s
        "\x54" => "\x74", #T t
        "\x55" => "\x75", #U u
        "\x56" => "\x76", #V v
        "\x57" => "\x77", #W w
        "\x58" => "\x78", #X x
        "\x59" => "\x79", #Y y
        "\x5a" => "\x7a", #Z z

        #ru (русский кириллица)
        #CASE_UPPER => case_lower
        "\xd0\x81" => "\xd1\x91", #Ё ё
        "\xd0\x90" => "\xd0\xb0", #А а
        "\xd0\x91" => "\xd0\xb1", #Б б
        "\xd0\x92" => "\xd0\xb2", #В в
        "\xd0\x93" => "\xd0\xb3", #Г г
        "\xd0\x94" => "\xd0\xb4", #Д д
        "\xd0\x95" => "\xd0\xb5", #Е е
        "\xd0\x96" => "\xd0\xb6", #Ж ж
        "\xd0\x97" => "\xd0\xb7", #З з
        "\xd0\x98" => "\xd0\xb8", #И и
        "\xd0\x99" => "\xd0\xb9", #Й й
        "\xd0\x9a" => "\xd0\xba", #К к
        "\xd0\x9b" => "\xd0\xbb", #Л л
        "\xd0\x9c" => "\xd0\xbc", #М м
        "\xd0\x9d" => "\xd0\xbd", #Н н
        "\xd0\x9e" => "\xd0\xbe", #О о
        "\xd0\x9f" => "\xd0\xbf", #П п

        #CASE_UPPER => case_lower
        "\xd0\xa0" => "\xd1\x80", #Р р
        "\xd0\xa1" => "\xd1\x81", #С с
        "\xd0\xa2" => "\xd1\x82", #Т т
        "\xd0\xa3" => "\xd1\x83", #У у
        "\xd0\xa4" => "\xd1\x84", #Ф ф
        "\xd0\xa5" => "\xd1\x85", #Х х
        "\xd0\xa6" => "\xd1\x86", #Ц ц
        "\xd0\xa7" => "\xd1\x87", #Ч ч
        "\xd0\xa8" => "\xd1\x88", #Ш ш
        "\xd0\xa9" => "\xd1\x89", #Щ щ
        "\xd0\xaa" => "\xd1\x8a", #Ъ ъ
        "\xd0\xab" => "\xd1\x8b", #Ы ы
        "\xd0\xac" => "\xd1\x8c", #Ь ь
        "\xd0\xad" => "\xd1\x8d", #Э э
        "\xd0\xae" => "\xd1\x8e", #Ю ю
        "\xd0\xaf" => "\xd1\x8f", #Я я

        #tt (татарский, башкирский кириллица)
        #CASE_UPPER => case_lower
        "\xd2\x96" => "\xd2\x97", #Ж ж с хвостиком    &#1174; => &#1175;
        "\xd2\xa2" => "\xd2\xa3", #Н н с хвостиком    &#1186; => &#1187;
        "\xd2\xae" => "\xd2\xaf", #Y y                &#1198; => &#1199;
        "\xd2\xba" => "\xd2\xbb", #h h мягкое         &#1210; => &#1211;
        "\xd3\x98" => "\xd3\x99", #Э э                &#1240; => &#1241;
    "\xd3\xa8" => "\xd3\xa9", #О o перечеркнутое  &#1256; => &#1257;

        #uk (украинский кириллица)
        #CASE_UPPER => case_lower
        "\xd2\x90" => "\xd2\x91",  #г с хвостиком
        "\xd0\x84" => "\xd1\x94",  #э зеркальное отражение
        "\xd0\x86" => "\xd1\x96",  #и с одной точкой
        "\xd0\x87" => "\xd1\x97",  #и с двумя точками

        #be (белорусский кириллица)
        #CASE_UPPER => case_lower
        "\xd0\x8e" => "\xd1\x9e",  #у с подковой над буквой

        #tr,de,es (турецкий, немецкий, испанский, французский латиница)
        #CASE_UPPER => case_lower
        "\xc3\x84" => "\xc3\xa4", #a умляут          &#196; => &#228;  (турецкий)
        "\xc3\x87" => "\xc3\xa7", #c с хвостиком     &#199; => &#231;  (турецкий, французский)
        "\xc3\x91" => "\xc3\xb1", #n с тильдой       &#209; => &#241;  (турецкий, испанский)
        "\xc3\x96" => "\xc3\xb6", #o умляут          &#214; => &#246;  (турецкий)
        "\xc3\x9c" => "\xc3\xbc", #u умляут          &#220; => &#252;  (турецкий, французский)
        "\xc4\x9e" => "\xc4\x9f", #g умляут          &#286; => &#287;  (турецкий)
        "\xc4\xb0" => "\xc4\xb1", #i c точкой и без  &#304; => &#305;  (турецкий)
        "\xc5\x9e" => "\xc5\x9f", #s с хвостиком     &#350; => &#351;  (турецкий)

        #hr (хорватский латиница)
        #CASE_UPPER => case_lower
        "\xc4\x8c" => "\xc4\x8d",  #c с подковой над буквой
        "\xc4\x86" => "\xc4\x87",  #c с ударением
        "\xc4\x90" => "\xc4\x91",  #d перечеркнутое
        "\xc5\xa0" => "\xc5\xa1",  #s с подковой над буквой
        "\xc5\xbd" => "\xc5\xbe",  #z с подковой над буквой

        #fr (французский латиница)
        #CASE_UPPER => case_lower
        "\xc3\x80" => "\xc3\xa0",  #a с ударением в др. сторону
        "\xc3\x82" => "\xc3\xa2",  #a с крышкой
        "\xc3\x86" => "\xc3\xa6",  #ae совмещенное
        "\xc3\x88" => "\xc3\xa8",  #e с ударением в др. сторону
        "\xc3\x89" => "\xc3\xa9",  #e с ударением
        "\xc3\x8a" => "\xc3\xaa",  #e с крышкой
        "\xc3\x8b" => "\xc3\xab",  #ё
        "\xc3\x8e" => "\xc3\xae",  #i с крышкой
        "\xc3\x8f" => "\xc3\xaf",  #i умляут
        "\xc3\x94" => "\xc3\xb4",  #o с крышкой
        "\xc5\x92" => "\xc5\x93",  #ce совмещенное
        "\xc3\x99" => "\xc3\xb9",  #u с ударением в др. сторону
        "\xc3\x9b" => "\xc3\xbb",  #u с крышкой
        "\xc5\xb8" => "\xc3\xbf",  #y умляут

        #xx (другой язык)
        #CASE_UPPER => case_lower
        #"" => "",  #

    );
    #d($trans);

    if ($mode == CASE_UPPER)
    {
        if (function_exists('mb_strtoupper'))   return mb_strtoupper($s, 'utf-8');
        if (preg_match('/^[\x00-\x7e]*$/', $s)) return strtoupper($s); #может, так быстрее?
        return strtr($s, array_flip($trans));
    }
    elseif ($mode == CASE_LOWER)
    {
        if (function_exists('mb_strtolower'))   return mb_strtolower($s, 'utf-8');
        if (preg_match('/^[\x00-\x7e]*$/', $s)) return strtolower($s); #может, так быстрее?
        return strtr($s, $trans);
    }
    else
    {
        trigger_error('Parameter 2 should be a constant of CASE_LOWER or CASE_UPPER!', E_USER_WARNING);
        return $s;
    }
    return $s;
}

function utf8_lowercase($s)
{
    return utf8_convert_case($s, CASE_LOWER);
}

function utf8_uppercase($s)
{
    return utf8_convert_case($s, CASE_UPPER);
}

function utf8_ucfirst($s, $is_other_to_lowercase = true)
{
    if ($s === '' or ! is_string($s)) return $s;
    if (preg_match('/^(.)(.*)$/us', $s, $m) === false) return false;
    return utf8_uppercase($m[1]) . ($is_other_to_lowercase ? utf8_lowercase($m[2]) : $m[2]);
}

function utf8_lcfirst($s)
{
    if ($s === '' or ! is_string($s)) return $s;
    if (preg_match('/^(.)(.*)$/us', $s, $m) === false) return false;
    return utf8_lowercase($m[1]) . $m[2];
}


function toupper ($str, $n=0)
{
	if ($n==0) return utf8_convert_case($str, CASE_UPPER);
	else if ($n==1) return utf8_ucfirst($str);
/*
if ($n<0) $str = strtr($str, 'абвгдеёжзийклмнорпстуфхцчшщъьыэюя', 'АБВГДЕЁЖЗИЙКЛМНОРПСТУФХЦЧШЩЪЬЫЭЮЯ');
else $str[$n] = strtr($str[$n], 'абвгдеёжзийклмнорпстуфхцчшщъьыэюя', 'АБВГДЕЁЖЗИЙКЛМНОРПСТУФХЦЧШЩЪЬЫЭЮЯ');
return $str;
*/
}

function tolower ($str, $n=0)
{
	$str=utf8_convert_case($str, CASE_LOWER);
	if ($n==0) return $str;
	else if ($n==1) return utf8_ucfirst($str);
	/*
if ($n<0) $str = strtr($str, 'АБВГДЕЁЖЗИЙКЛМНОРПСТУФХЦЧШЩЪЬЫЭЮЯ', 'абвгдеёжзийклмнорпстуфхцчшщъьыэюя');
else $str[$n] = strtr($str[$n], 'АБВГДЕЁЖЗИЙКЛМНОРПСТУФХЦЧШЩЪЬЫЭЮЯ', 'абвгдеёжзийклмнорпстуфхцчшщъьыэюя');
return $str;
*/
}


function shorten_header ($tn_header,$len=50)
{
		if ( !function_exists('htmlspecialchars_decode') )
		{
    		function htmlspecialchars_decode($text)
    		{
        		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
    		}
		}
		$ss1=array('&laquo;','&raquo;');
		$ss2=array('«','»');
		$tn_header=str_replace($ss1,$ss2,htmlspecialchars_decode($tn_header));
		//if (strlen($tn_header)>($len+3)) $tn_header=substr($tn_header,0,$len).'...';
		return $tn_header;
}
function spam ($text, $name='')
{
	$spam=array 
	(
		'\.[0-9a-z]{6}\.cn\/',
		'Buenas dias - <a href',
		'angelfire.com',
		'http://www.volny.cz',
		'http://all-about-massage',
		'http://adullt.freewebspace.net.au',
		'anyboard.net',
		'http://www.wii-uk.net',
		'This site about beautiful model',
		'mbesdura.info',
		'stoog.cn',
		'blogginnetwork.com',
		'DRT710SA',
		'http://nahalka.ru',
		'Hello i wish you healthy !!!!',
		'Voluptuous Vixens',
		'Ottenerlo',
		'\[\/url\]',
		'free-porn.blogspot.com',
		'http://carisoprodol',
		'http://www.desiurl.com',
		'ringo.blogspot.com',
		'http://rex1.org',
		'Hi! Very nice site! Thanks you very much!',
		'Interesting info about pheromones used to attract opposite sex',
		'.wapdr.info',
		'http://www.geocities.com/kimdegrella',
		'.awosv.info',
		'http://spdimon.info',
		'http://myurl.com.tw',
		'http://[0-9]{1-2}.[a-z]{10-20}.info',
		'hostingtree.org',
		'Рассылки по форумам',
		'freesite.blogspot.com',
		'http://www.ab-concept.at',
		'http://gourl.org',
		'metal-cd.ru',
		'http://phentermin',
		'http://ggoxgmwx.com',
		'viagra',
		'www.greatourdating.com',
		'www.apartments.waw.pl',
		'Interesting information you have',
		'Your site blog is interesting and has good info',
		'www.bignews.com',
		'wow gold',
		'cheap soma',
		'www.bestrxpills.com',
		'www.viagra4u.info',
		'The text was good, but i stil cant find the play ipdates');
	$sp=FALSE;
	for ($i=0;$i<sizeof($spam);$i++) if (eregi ($spam[$i],$text)) $sp=TRUE;
	if (eregi ("([0-9a-f]{32})", substr($text,0,32))) $sp=TRUE;
	
	// preg_match ('/[0-9]{3,}/i',$text) && 
	
	if (preg_match ('/(videosexe|bestporn|nudity|nymphets|cunt|ebony|anal|masturbating|boobs|sexvideo|blackass|bestiality|pedosex|preteen|girlsex|erotic|preteen|nude|raping|topless|lolita|Porn|Orgy|pedo|loli|naked|Blowjob|Incest|Hentai)/i',$text) && preg_match ('/^[A-Z]{1}[a-z]+$/',$name)) $sp=TRUE;
	
	return $sp;
	
}


function remoteserver_connect ($ftp_server, $login, $password)
{
	$conn_id = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server");
	$login_result = ftp_login ($conn_id, $login, $password); 
	if ((!$conn_id) || (!$login_result)) { echo "Ftp-connect failed!"; die; }
	ftp_pasv($conn_id, true);
	return $conn_id;

} 


function mediaserver_connect ()
{
	$conn_id=remoteserver_connect ("media.kino-govno.com", "mkgadminftp", "MedPassNew");
	if (!$conn_id) echo 'Не могу законнектиться к медиа-серверу!';
	return $conn_id;
}


//
function mediaserver_changepath ($conn_id, $path)
{
	$path=trim ($path,'/');
	if (substr($path,0,21)!='htdocs/') $path='htdocs/'.$path;
	$dirs=explode ('/',$path);	
	foreach ($dirs AS $d)
	{
		//echo $d.'/';
	    if (!@ftp_chdir($conn_id, $d)) {
	        if (ftp_mkdir($conn_id, $d))
	        {
	            if (ftp_chmod($conn_id, 0777, $d) === false) echo "Не могу изменить права каталога $d\n";
	            ftp_chdir($conn_id, $d);
	            //echo "создал $d";
	        }
	        else die("Не смог создать каталог $path\n");
		}
	}	
}
//


function rus_date ($timestamp, $showyear='года')
{
	global $months_rus;
	if (date('H:i',$timestamp)=='01:00' && date('t',$timestamp)==date('j',$timestamp))
		$date=utf8_ucfirst($months_rus[date('n',$timestamp)-1]).' '.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='02:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==3)
		$date='1 квартал '.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='02:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==6)
		$date='2 квартал '.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='02:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==9)
		$date='3 квартал '.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='02:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==12)
		$date='4 квартал '.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='02:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==2)
		$date='Зима '.(intval(date('Y',$timestamp))-1).'-'.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='02:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==5)
		$date='Весна '.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='02:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==8)
		$date='Лето '.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='02:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==11)
		$date='Осень '.date('Y',$timestamp).' года';
	else
	if (date('H:i',$timestamp)=='03:00' && date('t',$timestamp)==date('j',$timestamp) && date('m',$timestamp)==12)
		$date=date('Y',$timestamp).' год';
	else
 		$date=($showyear!=''?dat(date('d.m.y',$timestamp)).' '.$showyear:dat(date('d.m.y',$timestamp),1));
	return $date;
	
}

function smart_date ($timestamp, $short=FALSE)
{
	$date='';
 	if (date("d.m.y")==date('d.m.y',$timestamp)) $date='Сегодня'; else
 		if (datechange(date("d.m.y"),-1)==date('d.m.y',$timestamp)) $date='Вчера'; else
 	 			if ($short) $date=date('d.m.y',$timestamp);
 					else $date=dat(date('d.m.y',$timestamp),1);
	 if (date("y")!=date("y",$timestamp) && !$short) $date.=' 20'.date('y',$timestamp).' года';
	 return $date;
	
}



function xml_replace ($d)
{
	$d=eregi_replace ("<strong>", "<b>", $d);
	$d=eregi_replace ("</strong>", "</b>", $d);
	$d=eregi_replace ("<italic>", "<i>", $d);
	$d=eregi_replace ("</italic>", "</i>", $d);
	$d=eregi_replace ("<a( )>", "<b>", $d);
	$d=eregi_replace ("</a>", "</b>", $d);

	$s1=array('&amp;','<br>', '<BR>','<B>','</B>','<I>','</I>','<U>','</U>','</P>','<P>','&quot;','&laquo;','&raquo;','&#151;','&minus;','&#8722;','&#8212;','&nbsp;','&#132;','&#147;','&#133;','&#146;');
	$s2=array('&','<br />', '<br />','<b>','</b>','<i>','</i>','<u>','</u>','</p>','<p>',"'","'","'",'-','-','-','-',' ',"'","'",'...',"'");
	$d=str_replace ($s1,$s2, $d);

	//$d=str_replace('&','&amp;',$d);
	$d=str_replace('"',"&quot;",$d);
	$d=str_replace('$','$$',$d);
	$d=str_replace ("</p>", "<br /><br />", $d);
	//$d=str_replace($s1,$s2,$d);

	$tags='<br>';
	$d=strip_tags($d,$tags);

	//$s1=array('>','<');
	//$s2=array('&gt;','&lt;');
	//$d=str_replace ($s1,$s2, $d);
		
	return ($d);	
}

// correct_english (строка) - приводит ЛЮБЫЕ английские строки к виду The Lord of the Rings: The Return of the King

function correct_english ($str)
{
	$str=trim(tolower($str));
	$s=explode(' ',$str);
	// Слова, которые надо писать БОЛЬШИМИ БУКВАМИ целиком
	$upall=array ('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'fifa', 'gp', 'wwe', 'wwf', 'nba', 'nhl', 'thq');
	// Символы, после которых надо начинать с большой буквы
	$nextlow=array ('-', ':', '.', ',');
	// Слова, которые надо писать мелкими буквами целиком
	$lowall=array ('the' , 'a' , 'for' , 'of' , 'and' , 'in' , 'vs' , 'in' , 'on');
	$txt='';
	for ($i=0;$i<sizeof($s);$i++) {
		$p=substr($s[$i-1],-1);
		$c=$s[$i];
		if (in_array ($c, $upall)) $c=toupper($c); else
		if (!in_array ($c, $lowall)) $c=toupper($c,0);
		if (in_array ($p, $nextlow) || $i==0) $c=toupper($c,0);
		if ($c!='-')
		{
			$defis=strpos ($c,'-');
			if (!($defis===FALSE)) $c=toupper($c,$defis+1);
		}
		$txt.=' '.$c;
	}
	return trim ($txt);
}

function datechange ($date,$change)
{
	if (strpos($date,'.')===FALSE) $changed=date ('dmy',mktime(1,1,1,substr($date,2,2),substr($date,0,2),substr($date,4,2))+$change*86400);
		else $changed=date ('d.m.y',mktime(1,1,1,substr($date,3,2),substr($date,0,2),substr($date,6,2))+$change*86400);
		
	return $changed.mb_strstr($date,'_');
}

function boxdate ($rd, $year='', $days=3, $lang='ru')
{
 
 global $date_days, $date_rus, $date_eng;
 
 if ($lang=='en') $date_rus=$date_eng;
 
 if ($days==3)
 	if (strpos($rd,'_')===FALSE) $days=3;
      			           else $days=substr($rd,strpos($rd,'_')+1);
 if ($days==7)
 	if (strpos($rd,'_')===FALSE) $days=7;
      			           else $days=substr($rd,strpos($rd,'_')+1);

 $curday=(int)substr($rd,0,2);
 $curmon=(int)substr($rd,2,2);
 $curyear=(int)substr($rd,4,2);
 if ($curday-$days<0)
 {
  $curmon_pr=$curmon-1;
  $year_pr='';
  if ($curmon_pr==0) {$curmon_pr=12; $curyear_pr=1999+$curyear; $year_pr=' '.($lang=='ru'?$curyear_pr:', '.$curyear_pr);}
  $curday_pr=$curday-$days+1+$date_days[$curmon_pr-1];
  $normaldate=$curday_pr.' '.$date_rus[$curmon_pr-1].$year_pr.'<nobr> &#151; '.$curday.' '.$date_rus[$curmon-1].'</nobr>';
 }
  else {
  	if ($lang=='ru') $normaldate=($curday-$days+1).'&minus;'.$curday.' '.$date_rus[$curmon-1];
  	else $normaldate=$date_rus[$curmon-1].' '.($curday-$days+1).'&minus;'.$curday;
  }
 if ($year!='')
 {
 	if ($lang=='ru')
 		return $normaldate.' '.(2000+$curyear).mb_trim(' '.$year);
 	else return $normaldate.', '.(2000+$curyear).mb_trim(' '.$year);
 }
         else return $normaldate;
}


function numform ($num,$point=2)
// Выдает число в нужном формате (пробелы между тысячяам, запятая в качестве десятичного знака).
// Максимальное кол-во десятичных знаков - 2. Старается обойтись вообще без них или хотя бы одним.
{
 if ($num==round($num)) return number_format($num,0,',',' '); else
   if ($num*10==round($num*10) || $point==1) return number_format($num,1,',',' '); else
                                             return number_format($num,2,',',' ');
}

function trimspaces ($str)
{
while (strpos($str,'  ')) $str=str_replace('  ',' ',$str);
return $str;	
}

function htmldecode ($en)
{ 
 for ($i=1039;$i<=1071;$i++)
    $en=str_replace('&#'.$i.';',chr($i-848),$en);
 $name=str_replace ('&amp;','',$name);
 return $en;
} 


function nametocode ($name, $dashes=false)
{
	$name=trim($name);
	$r=array ('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ъ', 'Э', 'Ю', 'Я');
	$rs=array ('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ъ', 'э', 'ю', 'я');
	$e=array ('a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'ju', 'ja');
 	$rome=array('I', 'II', 'III', 'IV', 'V', 'VI', 'VI', 'VII', 'IX', 'X', 'XI', 'XII');

	foreach ($rome as $krom=>$rom)
		$name=mb_eregi_replace (' '.$rom.'$',$krom+1,$name);

	$strange=array ('&#xE9;','ū','ó','é','Ō','ô','Ô','é','&#xe9;');
	$normal=array ('e','u','o','e','o','o','o','e','e');
	
	$name=utf8_lowercase(trim($name));
	$name=str_replace ($r,$e,$name);
	$name=str_replace ($rs,$e,$name);
	$name=str_replace ($strange,$normal,$name);
	if (mb_substr($name,0,2)=='an ') $name=mb_substr ($name,2);
	if (mb_substr($name,0,2)=='an ') $name=mb_substr ($name,3);
	if (mb_substr($name,-3)==', a') $name=mb_substr ($name,0,-3);
	if (mb_substr($name,-3)==', an') $name=mb_substr ($name,0,-4);
	if (mb_substr($name,0,4)=='the ') $name=mb_substr ($name,4);
	if (mb_substr($name,-5)==', the') $name=mb_substr ($name,0,-5); 
	if ($dashes)
		$name=str_replace(' ','-',trim(ereg_replace('[^a-z0-9 ]','',$name)));
	else
		$name=ereg_replace('[^a-z0-9]','',$name);
	return $name;
}

function strtonum ($str,$f='i')
{
// Добавление ноликов пригодится только для целых чисел. Да и то не всегда. Короче, лишь для ублюдского kinobusiness.com

	$str=mb_eregi_replace('[^0-9mM\-\-\+\,\.]','',$str);

	if ($f=='i')
	{
		
		$str=mb_ereg_replace('[^0-9\,\-\-]','',$str);
		$str=mb_trim($str);
		
		$pos=mb_strrpos($str,',');
		if ($pos!=0)
		{
			$z=mb_strlen($str)-$pos;
			if ($z==3) $str.='0';
			if ($z==2) $str.='00';
		}
	}
	
	
	if (strtolower(mb_substr($str,-1))=='m') $mln=TRUE;
		
	$str=mb_ereg_replace('[^0-9\.\-\-]','',$str);
	
	if ($str=='-0') $str='0';
	
	if ($mln==TRUE) $str=1000000*(float)$str;
	
	if ($f=='i') $str=(int)$str; else
	if ($f=='f') $str=(float)$str;
	
	return $str;

}

function between ($str, $s1, $s2='', $from=0, $safe=TRUE)
// Функция выдает ту строку, что заключена в строке $str между сроками $s1 и $s2
{
	if ($s1!='') $pos=mb_strpos(utf8_lowercase($str),utf8_lowercase($s1),$from);
	else $pos=0;
	
	if ($pos===FALSE) {
		if ($safe) return ''; else return $str;
	} else
	{
		$pos+=mb_strlen($s1);
		if ($s2=='') $pos2=mb_strlen($str);
			else $pos2=mb_strpos(utf8_lowercase($str),utf8_lowercase($s2),$pos);
		if ($pos2==0) $pos2=mb_strlen($str);
		if ($pos===FALSE || $pos==$pos2) return ''; else 
			return mb_substr($str,$pos,$pos2-$pos);
	}
}

function getip() { 
if (getenv("HTTP_CLIENT_IP")) 
$ip = getenv("HTTP_CLIENT_IP"); 
else if(getenv("HTTP_X_FORWARDED_FOR")) 
$ip = getenv("HTTP_X_FORWARDED_FOR"); 
else if(getenv("REMOTE_ADDR")) 
$ip = getenv("REMOTE_ADDR"); 
else 
$ip = "";
return $ip; 

}

function tabletoarray ($html,$strip=1)
{
// Функция принимает строку $html, состоящую из таблицы "<table..... </table>" и преобразует ее в массив.
// Элементы начинаются с 1 строки (а не с 0).
// В ячейку $ar[1][0] записывается количество строк
// В ячейку $ar[0][1] записывается количество столбцов
// $strip - вырезать или не вырезать HTML-теги

//
 $html= str_replace ('<TABLE','<table',$html);
 $html= str_replace ('<Table','<table',$html);
 $html= str_replace ('<TD','<td',$html);
 $html= str_replace ('<th','<td',$html);
 $html= str_replace ('<TH','<td',$html);
 $html= str_replace ('<TR','<tr',$html);
 $html= str_replace ('</TABLE','</table',$html);
 $html= str_replace ('</Table','</table',$html);
 $html= str_replace ('</TD','</td',$html);
 $html= str_replace ('</TH','</td',$html);
 $html= str_replace ('</th','</td',$html);
 $html= str_replace ('</td ','</td',$html);
 $html= str_replace ('</TR','</tr',$html);
 $html= str_replace ('<tbody>','',$html);
 $html= str_replace ('<Tbody>','',$html);
 $html= str_replace ('<TBODY>','',$html);
 $html= str_replace ('</tbody>','',$html);
 $html= str_replace ('</TBODY>','',$html);
 $html= str_replace ('</Tbody>','',$html);
 $html=str_replace("\r","",$html);
 $html=str_replace("\n","",$html);
 $html=str_replace("<br>"," ",$html);
 $html=str_replace("<BR>"," ",$html);
 $html=str_replace("&amp;","&",$html);
 $beg=mb_strpos($html,'<table');
 if ($beg===FALSE) $beg=0;
 	          else $beg=mb_strpos($html,'>',$beg)+1;
 $beg=mb_strpos($html,'<tr',$beg);
 $end=mb_strpos($html,'</table>',$beg);
 if ($end===FALSE) $end=mb_strlen ($html)-1;
 $html=trim (mb_substr ($html,$beg,$end-$beg));
 $i=1;
 $j=1;
 $p=0;
 $ar=array();

 $ar[$i][$j]='';

while ($p + 5 < mb_strlen ($html))
 {

   $rowbeg=mb_strpos($html,'<tr',$p);
  $rowbeg=mb_strpos($html,'>',$rowbeg)+1;
  $rowend=mb_strpos($html,'</tr>',$rowbeg);
  $row=trim (mb_substr($html,$rowbeg,$rowend-$rowbeg));
  $j=1;
  $p=0;
  //
  while ($p< mb_strlen ($row))
  {
   $colbeg=mb_strpos($row,'<td',$p);
   $colbeg=mb_strpos($row,'>',$colbeg)+1;
   $colend=mb_strpos($row,'</td>',$colbeg);
   if ($strip) $col= trim (strip_tags (mb_substr($row,$colbeg,$colend-$colbeg)));
          else $col= trim(mb_substr($row,$colbeg,$colend-$colbeg));
   $ar[$i][$j]=$col;
   $p=$colend+5;
   $j++;
  }
  //
  $p=$rowend+5;
  $i++;

 }
//
 $ar[1][0]=$i-1;
 $ar[0][1]=$j-1;

 return $ar;


}


	
function betweens ($str, $arr, $trim=TRUE, $safe=TRUE)
{
	$deep=sizeof ($arr);
	for ($i=0; $i<$deep; $i=$i+2)
	{
		$str=between ($str, $arr[$i], $arr[$i+1],0,$safe);
	}
	if ($trim) $str=mb_trim ($str);
	return $str;
}

function normalize_date ($date)
{
	global $date_eng, $date_rus;

	if (mb_eregi("^([a-zA-Z]+) ([0-9]+), ([0-9]{4})(.*)", $date, $dd))
	{
		$month=array_search ($dd[1],$date_eng)+1;
		$date=sprintf('%02d',$dd[2]).'.'.sprintf('%02d',$month).'.'.$dd[3];
	} else
	if (mb_eregi("^([0-9]+) ([a-zA-Z]+) ([0-9]{4})(.*)", $date, $dd))
	{
		$month=array_search ($dd[2],$date_eng)+1;
		$date=sprintf('%02d',$dd[1]).'.'.sprintf('%02d',$month).'.'.$dd[3];
	} else
    if ($date=='TBA') $date='';
    $date=str_replace('limited','ограниченный прокат',$date);
    return $date;
}

function explode2string ($txt, $divider, $zpt=', ', $betweens='', $limit=9999)
{
	$str='';
	$i=0;
	$arr=explode ($divider, $txt);
	foreach ($arr as $a)
	{
		$a=mb_trim($betweens!=''?betweens($a,$betweens):$a);
		if ($a!='' && $i<$limit) 
			$str.=$a.$zpt;
		$i++;
	}
	if (mb_substr($str,-mb_strlen($zpt))==$zpt) $str=mb_substr($str,0,-mb_strlen($zpt));
	return $str;
}

function from_wos ($com, $codename='')
{
	$id=between ($com,'id=','');
	$file=file_get_contents ('http://www.worldofspectrum.org/api/infoseek_select_json.cgi?id='.$id);
 	$file=mb_convert_encoding ($file, "UTF-8", "ISO-8859-1");
 	mb_internal_encoding("UTF-8");

	$r = json_decode($file,true);
	
	// print_array ($r);
	
	$republisher='';
	if ($r['rePublishers']!='')
		foreach ($r['rePublishers'] as $rep)
			$republisher.=$rep['rePublisher'].'<->';
	if (mb_substr($republisher,-3)=='<->')
		$republisher=mb_substr($republisher,0,-3);

	$price='';
	if ($r['prices']!='')
		foreach ($r['prices'] as $pr)
			$price.=$pr['price'].'<->';
	if (mb_substr($price,-3)=='<->')
		$price=mb_substr($price,0,-3);

	// echo $price.'-'.$r['joysticks'];
	
	con ();
	
   $q="english='".mysql_escape_string($r['title'])."', developer='".mysql_escape_string ($r['author'])."', publisher='".mysql_escape_string ($r['publisher'])."', genre='".mysql_escape_string ($r['type'])."', site='http://www.worldofspectrum.org/infoseekid.cgi?id=$id', platforms='zx', system='".mysql_escape_string($r['machineType'])."', playersnumber='".mysql_escape_string($r['maxPlayers'])."', language='".mysql_escape_string($r['language'])."', publication='".mysql_escape_string($r['publication'])."', republisher='".mysql_escape_string($republisher)."', price='".mysql_escape_string($price)."', zx_controls='".mysql_escape_string($r['joysticks'])."', date='".mysql_escape_string($r['year'])."'";;

   
	
	$translate_english=array ('Loading screen', 'In-game screen', 'Game map', 'Cassette inlay', 'Advertisement');
	$translate_russian=array ('Экран загрузки', 'Игровой экран', 'Карта игры', 'Кассетный вкладыш', 'Реклама');
	$translate_types=array ('images', 'images', 'production', 'production', 'production');

	
	$downloads=$r['otherDownloads'];
	echo '<form method="post"'.($codename!=''?' action="games_ad.php?id='.$codename.'"':'').'><input type="hidden" name="wosgameid" id="wosgameid" value="'.$id.'">';
	$i=0;
	foreach ($downloads as $d)
	{
		$ext=strtolower(mb_strrchr($d['link'],'.'));
		$key=(in_array($d['type'],$translate_english)?array_search($d['type'],$translate_english):-1);
		if ($ext=='.gif' || $ext=='.jpg' || $ext=='.png')
		{
			echo '<input type="text" name="wosname['.$i.']" id="wosname['.$i.']" size="50" value="'.($key==-1?$d['type']:$translate_russian[$key]).'"> <a href="'.$d['link'].'" target="_blank">Ссылка</a> <input type="radio" name="wostype['.$i.']" id="wostype_screen['.$i.']" value="images"'.($translate_types[$key]=='images'?' checked':'').'> Кадр</label> <input type="radio" name="wostype['.$i.']" id="wostype_screen['.$i.']" value="posters"'.($translate_types[$key]=='posters'?' checked':'').'><label for="wostype_screen['.$i.']">Постер</label> <input type="radio" name="wostype['.$i.']" id="wostype_screen['.$i.']" value="production"'.($key==-1 || $translate_types[$key]=='production'?' checked':'').'><label for="wostype_screen['.$i.']">Другое</label> <input type="radio" name="wostype['.$i.']" id="wostype_screen['.$i.']" value="none"><label for="wostype_screen['.$i.']">Пропустить</label> <input type="text" name="woslink['.$i.']" id="woslink['.$i.']" size="100" value="'.$d['link'].'"><br>';
			$i++;
		}
	
	}
	
	echo '<input type="submit" name="wossubmit" value="Добавить картинки"></form>';
	
	
	return $q;

	/* 
	if ($r['picLoad']!='') add_image_to_mediaserver('games','dizzy','images','', str_replace ('http://www.worldofspectrum.org/showscreen.cgi?screen=', 'ftp://ftp.worldofspectrum.org/pub/sinclair/',$r['picLoad']), 0,0);
	if ($r['picIngame']!='') add_image_to_mediaserver('games','dizzy','images','', str_replace ('http://www.worldofspectrum.org/showscreen.cgi?screen=', 'ftp://ftp.worldofspectrum.org/pub/sinclair/',$r['picIngame']), 0,0);
	if ($r['picInlay']!='') add_image_to_mediaserver('games','dizzy','posters','', str_replace ('http://www.worldofspectrum.org/showscreen.cgi?screen=', 'ftp://ftp.worldofspectrum.org/pub/sinclair/',$r['picInlay']), 240,240);
	*/
}


function mysql_get_value ($value, $table, $condition)
{
$ret=mysql_fetch_array(q("SELECT $value FROM $table WHERE $value='$condition'"));
if ($ret) return $ret[$value]; else return '';
}


function numending ($num, $word, $e1,$e2,$e3, $rod='zh')
	// $rod = 'm', 's', 'zh' - род мужской, средний или женский
{
	global $numbers_zh, $numbers_m,$numbers_s;
	if ($rod=='zh') $n=$numbers_zh[$num]; else
		if ($rod=='m') $n=$numbers_m[$num]; else
			if ($rod=='s') $n=$numbers_s[$num];
	return ($num>9?$num:$n).' '.$word.ending($num, $e1,$e2,$e3);
}

function dat ($d,$short=0)
{
// $rus: 0 (по умолчанию) - выводится "15 декабря 2003"
//       1 - выводится "15 декабря"
//       2,3,... - выводится 15.12.2003
// Сделаем так: если в дате нет ТОЧКИ ("."), то предполагается, что это формат timestamp. С ним и работаем.

$mon=array('','января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

$a=explode('.',$d);
if (sizeof($a)==3)
{ // Если дата типа 01.02.08 или 01.02.2008
	if ($a[2]<100 && $a[2]>20) $a[2]=$a[2]+1900;
	if ($a[2]>=0 && $a[2]<=20) $a[2]=$a[2]+2000;
	if ($short==0) return (int)$a[0].' '.$mon[(int)$a[1]].' '.(int)$a[2];
	    else if ($short==1) return (int)$a[0].' '.$mon[(int)$a[1]];
	         else return $d;
} else return $d;
{ // Если у нас timestamp
	/*
	
	if ($short==0) return date('j ',$d).$mon[(int)date('m',$d)].date(' Y',$d);
	    else if ($short==1) return date('j ',$d).$mon[(int)date('m',$d)];
	         else return date('d.m.Y',$d);
	 */
}
}


function ddat ($d,$f,$today=0)
	
// $d - дата в виде 09.06.04 или 09.06.2004
// $f - формат даты:
// $today - 1, если надо выводить "сегодня" и "вчера" вместо всякого там
//
// dd - число типа 01
// d - число типа 1
// W - день недели, типа "Пятница"
// w - день недели, типа "пятница"
// _W - день недели, типа "Friday"
// _W3 - день недели, типа "Fri"
// _w - день недели, типа "friday"
// _w3 - день недели, типа "fri"
// mm - месяц типа 06
// m - месяц типа 6
//? M - месяц типа "июня"
//? _M - месяц типа "February"
//? _M3 - месяц типа "Feb
// yy - год типа 04
// yyyy - год типа 2004

{

if (strpos($d,'.')===false) $d=date('d',$d).'.'.date('m',$d).'.'.date('y',$d);

$a=explode('.',$d);
if (strlen($a[2])!=4) if (($a[2]<100)&&($a[2]>50)) $a[2]=$a[2]+1000; else
if (($a[2]>=0)&&($a[2]<=50)) $a[2]=$a[2]+2000;

//return date('d').'.'.date('m').'.'.date('Y').'.'.$a[0]'.'.$a[1]'.'.$a[2];

if (($today==1)&&(date('d')==$a[0])&&(date('m')==$a[1])&&(date('Y')==$a[2])) return 'Сегодня'; else
if (($today==1)&&(date('d',time()-86400)==$a[0])&&(date('m',time()-86400)==$a[1])&&(date('Y',time()-86400)==$a[2])) return 'Вчера'; else
	
{

$mon_days=array (31,28,31,30,31,30,31,31,30,31,30,31);
$week=array('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота');
$week_eng=array ('Sunday', 'Monday', 'Tuesday', 'Wednsday', 'Thursday', 'Friday', 'Saturday');
$mon=array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
$mon_eng=array ('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

$w=(int)(($a[2]-1)*365.25);
for ($i=0;$i<$a[1]-1;$i++) $w+=$mon_days[$i];
if (($a[1]>2)&&($a[2]%4==0)) $w+=1;
$w+=$a[0]-1;

$f=str_replace ('dd',$a[0],$f);
$f=str_replace ('d',(int)$a[0],$f);
$f=str_replace ('mm',$a[1],$f);
$f=str_replace ('m',(int)$a[1],$f);
$f=str_replace ('yyyy',$a[2],$f);
$f=str_replace ('yy',substr($a[2],-2),$f);
$f=str_replace ('_M3',substr($mon_eng[(int)$a[1]],0,3),$f);
$f=str_replace ('_M',$mon_eng[(int)$a[1]],$f);
$f=str_replace ('M',$mon[(int)$a[1]],$f);
$f=str_replace ('_w3',substr(utf8_lowercase($week_eng[$w%7]),0,3),$f);
$f=str_replace ('_w',utf8_lowercase($week_eng[$w%7]),$f);
$f=str_replace ('_W3',substr($week_eng[$w%7],0,3),$f);
$f=str_replace ('_W',$week_eng[$w%7],$f);
$f=str_replace ('w',utf8_lowercase($week[$w%7]),$f);
$f=str_replace ('W',$week[$w%7],$f);

return $f;
}

}

function translate_korean ($korean)
{
	
	mb_internal_encoding("UTF-8");
	mb_regex_encoding("UTF-8");

$begin=array ('pyeong'=>'хён', 'byeong'=>'пён', 'cheung'=>'чхын', 'cheong'=>'чхон', 'gyeong'=>'кён', 'cheom'=>'чхом', 'cheop'=>'чхоп', 'paeng'=>'пхэн', 'pyeom'=>'пхём', 'gyeop'=>'кёп', 'ching'=>'чхин', 'pyeon'=>'пхён', 'byeon'=>'пён', 'chong'=>'чхон', 'goeng'=>'квен', 'seong'=>'сон', 'gwang'=>'кван', 'jeung'=>'чын', 'gyeom'=>'кём', 'byeol'=>'пёль', 'byeok'=>'пёк', 'gyeon'=>'кён', 'cheok'=>'чхок', 'jeong'=>'чон', 'chang'=>'чхан', 'chaek'=>'чхэк', 'deung'=>'тын', 'chung'=>'чхун', 'cheuk'=>'чхык', 'jaeng'=>'чэн', 'gyeol'=>'кёль', 'geung'=>'кын', 'cheol'=>'чхоль', 'saeng'=>'сэн', 'seung'=>'сын', 'cheon'=>'чхон', 'ssang'=>'ссан', 'taeng'=>'тхэн', 'gyeok'=>'кёк', 'chok'=>'чхок', 'bung'=>'пун', 'bing'=>'пин', 'byeo'=>'пё', 'baek'=>'пэк', 'baem'=>'пэм', 'chwi'=>'чхви', 'bang'=>'пан', 'chun'=>'чхун', 'chuk'=>'чхук', 'beon'=>'пон', 'beol'=>'поль', 'choe'=>'чхве', 'chon'=>'чхон', 'cheo'=>'чхо', 'beop'=>'поп', 'beom'=>'пом', 'bong'=>'пон', 'seok'=>'сок', 'seup'=>'сып', 'jung'=>'чун', 'sing'=>'син', 'seum'=>'сым', 'seul'=>'сыль', 'jeul'=>'чыль', 'jeuk'=>'чык', 'ssae'=>'ссэ', 'ssuk'=>'ссук', 'jeom'=>'чом', 'jeol'=>'чоль', 'jeok'=>'чок', 'jang'=>'чан', 'jeop'=>'чоп', 'sseu'=>'ссы', 'jong'=>'чон', 'sung'=>'сун', 'jeum'=>'чым', 'saek'=>'сэк', 'chak'=>'чхак', 'jeon'=>'чон', 'chan'=>'чхан', 'chal'=>'чхаль', 'cham'=>'чхам', 'sang'=>'сан', 'seon'=>'сон', 'seol'=>'соль', 'song'=>'сон', 'swae'=>'свэ', 'jeup'=>'чып', 'jing'=>'чин', 'syeo'=>'сё', 'seom'=>'сом', 'seop'=>'соп', 'chae'=>'чхэ', 'chul'=>'чхуль', 'gwol'=>'кволь', 'gaek'=>'кэк', 'gwon'=>'квон', 'gung'=>'кун', 'tung'=>'тхун', 'tong'=>'тхон', 'gyun'=>'кюн', 'gyul'=>'кюль', 'geup'=>'кып', 'taek'=>'тхэк', 'geum'=>'кым', 'geun'=>'кын', 'geuk'=>'кык', 'chum'=>'чхум', 'teuk'=>'тхык', 'geop'=>'коп', 'gwak'=>'квак', 'gyeo'=>'кё', 'gong'=>'кон', 'pyeo'=>'пхё', 'geom'=>'ком', 'gwan'=>'кван', 'teum'=>'тхым', 'geon'=>'кон', 'gwae'=>'квэ', 'geol'=>'коль', 'gwal'=>'кваль', 'gang'=>'кан', 'geul'=>'кыль', 'deok'=>'ток', 'chim'=>'чхим', 'keun'=>'кхын', 'daek'=>'тэк', 'tang'=>'тхан', 'kwae'=>'кхвэ', 'dwae'=>'твэ', 'dong'=>'тон', 'chip'=>'чхип', 'doen'=>'твен', 'dang'=>'тан', 'chik'=>'чхик', 'deuk'=>'тык', 'deul'=>'тыль', 'chin'=>'чхин', 'chil'=>'чхиль', 'gakk'=>'какк', 'pung'=>'пхун', 'jul'=>'чуль', 'jwi'=>'чви', 'pip'=>'пхип', 'jun'=>'чун', 'jeu'=>'чы', 'pil'=>'пхиль', 'jon'=>'чон', 'jok'=>'чок', 'peo'=>'пхо', 'jol'=>'чоль', 'pyo'=>'пхё', 'pok'=>'пхок', 'pum'=>'пхум', 'pye'=>'пхе', 'joe'=>'чве', 'pik'=>'пхик', 'peu'=>'пхы', 'jwa'=>'чва', 'juk'=>'чук', 'teu'=>'тхы', 'tal'=>'тхаль', 'che'=>'чхе', 'tam'=>'тхам', 'tap'=>'тхап', 'teo'=>'тхо', 'tae'=>'тхэ', 'cho'=>'чхо', 'tan'=>'тхан', 'chu'=>'чху', 'chi'=>'чхи', 'keu'=>'кхы', 'kal'=>'кхаль', 'tak'=>'тхак', 'ton'=>'тхон', 'tol'=>'тхоль', 'jin'=>'чин', 'jil'=>'чиль', 'jik'=>'чик', 'pan'=>'пхан', 'pal'=>'пхаль', 'jim'=>'чим', 'jip'=>'чип', 'twi'=>'тхви', 'toe'=>'тхве', 'cha'=>'чха', 'jom'=>'чом', 'jyo'=>'чё', 'pae'=>'пхэ', 'sul'=>'суль', 'dwi'=>'тви', 'deu'=>'ты', 'jeo'=>'чо', 'bak'=>'пак', 'dun'=>'тун', 'duk'=>'тук', 'don'=>'тон', 'dol'=>'толь', 'doe'=>'тве', 'gok'=>'кок', 'ban'=>'пан', 'bal'=>'паль', 'bon'=>'пон', 'buk'=>'пук', 'bun'=>'пун', 'gyo'=>'кё', 'bok'=>'пок', 'gye'=>'ке', 'bap'=>'пап', 'bae'=>'пэ', 'beo'=>'по', 'dok'=>'ток', 'gon'=>'кон', 'gyu'=>'кю', 'geu'=>'кы', 'gwa'=>'ква', 'gin'=>'кин', 'gwi'=>'кви', 'gut'=>'кут', 'guk'=>'кук', 'gun'=>'кун', 'goe'=>'кве', 'gul'=>'куль', 'gil'=>'киль', 'gim'=>'ким', 'got'=>'кот', 'dae'=>'тэ', 'gol'=>'коль', 'deo'=>'то', 'dap'=>'тап', 'dam'=>'там', 'got'=>'кот', 'dan'=>'тан', 'dal'=>'таль', 'geo'=>'ко', 'bul'=>'пуль', 'sum'=>'сум', 'swi'=>'сви', 'seu'=>'сы', 'gal'=>'каль', 'gam'=>'кам', 'sun'=>'сун', 'suk'=>'сук', 'gap'=>'кап', 'bin'=>'пин', 'sik'=>'сик', 'sin'=>'син', 'sso'=>'ссо', 'ssi'=>'сси', 'gan'=>'кан', 'jan'=>'чан', 'ssa'=>'сса', 'gar'=>'кар', 'sil'=>'силь', 'sim'=>'сим', 'sip'=>'сип', 'gak'=>'как', 'soe'=>'све', 'san'=>'сан', 'sal'=>'саль', 'sam'=>'сам', 'sap'=>'сап', 'sak'=>'сак', 'jap'=>'чап', 'bil'=>'пиль', 'bim'=>'пим', 'jae'=>'чэ', 'sat'=>'сат', 'sae'=>'сэ', 'son'=>'сон', 'sol'=>'соль', 'sot'=>'сот', 'sok'=>'сок', 'gat'=>'кат', 'seo'=>'со', 'gae'=>'кэ', 'jam'=>'чам', 'jak'=>'чак', 'lee'=>'ли', 'pe'=>'пхе', 'ge'=>'ке', 'po'=>'пхо', 'go'=>'ко', 'pu'=>'пху', 'pa'=>'пха', 'pi'=>'пхи', 'ti'=>'тхи', 'di'=>'ти', 'so'=>'со', 'su'=>'су', 'se'=>'сe', 'sa'=>'са', 'bi'=>'пи', 'ji'=>'чи', 'ga'=>'ка', 'jo'=>'чо', 'je'=>'че', 'ja'=>'ча', 'ju'=>'чу', 'si'=>'си', 'bu'=>'пу', 'bo'=>'по', 'gi'=>'ки', 'da'=>'та', 'te'=>'тхе', 'to'=>'тхо', 'tu'=>'тху', 'ta'=>'тха', 'ki'=>'кхи', 'ba'=>'па', 'du'=>'ту', 'ko'=>'кхо', 'do'=>'то', 'gu'=>'ку', 'g'=>'к', 'k'=>'к', 'n'=>'н', 'd'=>'т', 't'=>'т', 'r'=>'л', 'l'=>'л', 'm'=>'м', 'p'=>'п', 'b'=>'п', 's'=>'c', 'j'=>'ч', 'h'=>'х');

$ending=array ('pyeong'=>'пён', 'byeong'=>'пён', 'cheung'=>'тын', 'cheong'=>'тон', 'gyeong'=>'кён', 'cheom'=>'том', 'cheop'=>'топ', 'paeng'=>'пэн', 'pyeom'=>'пём', 'gyeop'=>'кёп', 'ching'=>'тин', 'pyeon'=>'пён', 'byeon'=>'пён', 'chong'=>'тон', 'goeng'=>'квен', 'seong'=>'тон', 'gwang'=>'кван', 'jeung'=>'тын', 'gyeom'=>'кём', 'byeol'=>'пёль', 'byeok'=>'пёк', 'gyeon'=>'кён', 'cheok'=>'ток', 'jeong'=>'тон', 'chang'=>'тан', 'chaek'=>'тэк', 'deung'=>'тын', 'chung'=>'тун', 'cheuk'=>'тык', 'jaeng'=>'тэн', 'gyeol'=>'кёль', 'geung'=>'кын', 'cheol'=>'толь', 'saeng'=>'тэн', 'seung'=>'тын', 'cheon'=>'тон', 'ssang'=>'тан', 'taeng'=>'тэн', 'gyeok'=>'кёк', 'chok'=>'ток', 'bung'=>'пун', 'bing'=>'пин', 'byeo'=>'пё', 'baek'=>'пэк', 'baem'=>'пэм', 'chwi'=>'тви', 'bang'=>'пан', 'chun'=>'тун', 'chuk'=>'тук', 'beon'=>'пон', 'beol'=>'поль', 'choe'=>'тве', 'chon'=>'тон', 'cheo'=>'то', 'beop'=>'поп', 'beom'=>'пом', 'bong'=>'пон', 'seok'=>'ток', 'seup'=>'тып', 'jung'=>'тун', 'sing'=>'тин', 'seum'=>'тым', 'seul'=>'тыль', 'jeul'=>'тыль', 'jeuk'=>'тык', 'ssae'=>'тэ', 'ssuk'=>'тук', 'jeom'=>'том', 'jeol'=>'толь', 'jeok'=>'ток', 'jang'=>'тан', 'jeop'=>'топ', 'sseu'=>'ты', 'jong'=>'тон', 'sung'=>'тун', 'jeum'=>'тым', 'saek'=>'тэк', 'chak'=>'так', 'jeon'=>'тон', 'chan'=>'тан', 'chal'=>'таль', 'cham'=>'там', 'sang'=>'тан', 'seon'=>'тон', 'seol'=>'толь', 'song'=>'тон', 'swae'=>'твэ', 'jeup'=>'тып', 'jing'=>'тин', 'syeo'=>'тё', 'seom'=>'том', 'seop'=>'топ', 'chae'=>'тэ', 'chul'=>'туль', 'gwol'=>'кволь', 'gaek'=>'кэк', 'gwon'=>'квон', 'gung'=>'кун', 'tung'=>'тун', 'tong'=>'тон', 'gyun'=>'кюн', 'gyul'=>'кюль', 'geup'=>'кып', 'taek'=>'тэк', 'geum'=>'кым', 'geun'=>'кын', 'geuk'=>'кык', 'chum'=>'тум', 'teuk'=>'тык', 'geop'=>'коп', 'gwak'=>'квак', 'gyeo'=>'кё', 'gong'=>'кон', 'pyeo'=>'пё', 'geom'=>'ком', 'gwan'=>'кван', 'teum'=>'тым', 'geon'=>'кон', 'gwae'=>'квэ', 'geol'=>'коль', 'gwal'=>'кваль', 'gang'=>'кан', 'geul'=>'кыль', 'deok'=>'ток', 'chim'=>'тим', 'keun'=>'кын', 'daek'=>'тэк', 'tang'=>'тан', 'kwae'=>'квэ', 'dwae'=>'твэ', 'dong'=>'тон', 'chip'=>'тип', 'doen'=>'твен', 'dang'=>'тан', 'chik'=>'тик', 'deuk'=>'тык', 'deul'=>'тыль', 'chin'=>'тин', 'chil'=>'тиль', 'gakk'=>'какк', 'pung'=>'пун', 'jul'=>'туль', 'jwi'=>'тви', 'pip'=>'пип', 'jun'=>'тун', 'jeu'=>'ты', 'pil'=>'пиль', 'jon'=>'тон', 'jok'=>'ток', 'peo'=>'по', 'jol'=>'толь', 'pyo'=>'пё', 'pok'=>'пок', 'pum'=>'пум', 'pye'=>'пе', 'joe'=>'тве', 'pik'=>'пик', 'peu'=>'пы', 'jwa'=>'тва', 'juk'=>'тук', 'teu'=>'ты', 'tal'=>'таль', 'che'=>'те', 'tam'=>'там', 'tap'=>'тап', 'teo'=>'то', 'tae'=>'тэ', 'cho'=>'то', 'tan'=>'тан', 'chu'=>'ту', 'chi'=>'ти', 'keu'=>'кы', 'kal'=>'каль', 'tak'=>'так', 'ton'=>'тон', 'tol'=>'толь', 'jin'=>'тин', 'jil'=>'тиль', 'jik'=>'тик', 'pan'=>'пан', 'pal'=>'паль', 'jim'=>'тим', 'jip'=>'тип', 'twi'=>'тви', 'toe'=>'тве', 'cha'=>'та', 'jom'=>'том', 'jyo'=>'тё', 'pae'=>'пэ', 'sul'=>'туль', 'dwi'=>'тви', 'deu'=>'ты', 'jeo'=>'то', 'bak'=>'пак', 'dun'=>'тун', 'duk'=>'тук', 'don'=>'тон', 'dol'=>'толь', 'doe'=>'тве', 'gok'=>'кок', 'ban'=>'пан', 'bal'=>'паль', 'bon'=>'пон', 'buk'=>'пук', 'bun'=>'пун', 'gyo'=>'кё', 'bok'=>'пок', 'gye'=>'ке', 'bap'=>'пап', 'bae'=>'пэ', 'beo'=>'по', 'dok'=>'ток', 'gon'=>'кон', 'gyu'=>'кю', 'geu'=>'кы', 'gwa'=>'ква', 'gin'=>'кин', 'gwi'=>'кви', 'gut'=>'кут', 'guk'=>'кук', 'gun'=>'кун', 'goe'=>'кве', 'gul'=>'куль', 'gil'=>'киль', 'gim'=>'ким', 'got'=>'кот', 'dae'=>'тэ', 'gol'=>'коль', 'deo'=>'то', 'dap'=>'тап', 'dam'=>'там', 'got'=>'кот', 'dan'=>'тан', 'dal'=>'таль', 'geo'=>'ко', 'bul'=>'пуль', 'sum'=>'тум', 'swi'=>'тви', 'seu'=>'ты', 'gal'=>'каль', 'gam'=>'кам', 'sun'=>'тун', 'suk'=>'тук', 'gap'=>'кап', 'bin'=>'пин', 'sik'=>'тик', 'sin'=>'тин', 'sso'=>'то', 'ssi'=>'ти', 'gan'=>'кан', 'jan'=>'тан', 'ssa'=>'та', 'gar'=>'кар', 'sil'=>'тиль', 'sim'=>'тим', 'sip'=>'тип', 'gak'=>'как', 'soe'=>'тве', 'san'=>'тан', 'sal'=>'таль', 'sam'=>'там', 'sap'=>'тап', 'sak'=>'так', 'jap'=>'тап', 'bil'=>'пиль', 'bim'=>'пим', 'jae'=>'тэ', 'sat'=>'тат', 'sae'=>'тэ', 'son'=>'тон', 'sol'=>'толь', 'sot'=>'тот', 'sok'=>'ток', 'gat'=>'кат', 'seo'=>'то', 'gae'=>'кэ', 'jam'=>'там', 'jak'=>'так', 'pe'=>'пе', 'ge'=>'ке', 'po'=>'по', 'go'=>'ко', 'pu'=>'пу', 'pa'=>'па', 'pi'=>'пи', 'ti'=>'ти', 'di'=>'ти', 'so'=>'то', 'su'=>'ту', 'se'=>'тe', 'sa'=>'та', 'bi'=>'пи', 'ji'=>'ти', 'ga'=>'ка', 'jo'=>'то', 'je'=>'те', 'ja'=>'та', 'ju'=>'ту', 'si'=>'ти', 'bu'=>'пу', 'bo'=>'по', 'gi'=>'ки', 'da'=>'та', 'te'=>'те', 'to'=>'то', 'tu'=>'ту', 'ta'=>'та', 'ki'=>'ки', 'ba'=>'па', 'du'=>'ту', 'ko'=>'ко', 'do'=>'то', 'gu'=>'ку', 'ss'=>'т', 'ng'=>'н', 'g'=>'к', 'k'=>'к', 'n'=>'н', 'd'=>'т', 't'=>'т', 'r'=>'ль', 'l'=>'ль', 'm'=>'м', 'p'=>'п', 'b'=>'п', 's'=>'т', 'j'=>'т', 'h'=>'х');

$middle=array ('kkwaeng'=>'кквэн', 'myeong'=>'мён', 'hyeong'=>'хён', 'cheung'=>'чхын', 'nyeong'=>'нён', 'cheong'=>'чхон', 'gyeong'=>'гён', 'byeong'=>'бён', 'ryeong'=>'рён', 'pyeong'=>'пхён', 'chung'=>'чхун', 'cheuk'=>'чхык', 'chong'=>'чхон', 'kkeut'=>'ккыт', 'taeng'=>'тхэн', 'ryeom'=>'рём', 'ryeop'=>'рёп', 'ryeol'=>'рёль', 'byeol'=>'бёль', 'ching'=>'чхин', 'ryeon'=>'рён', 'geung'=>'гын', 'raeng'=>'рэн', 'ryang'=>'рян', 'ryeok'=>'рёк', 'cheom'=>'чхом', 'seong'=>'сон', 'jeung'=>'чжын', 'deung'=>'дын', 'ttang'=>'ттaн', 'jeong'=>'чжон', 'neung'=>'нын', 'yeong'=>'ён', 'jaeng'=>'чжэн', 'ssang'=>'ссан', 'seung'=>'сын', 'nyeom'=>'нём', 'nyeon'=>'нён', 'cheok'=>'чхок', 'cheon'=>'чхон', 'cheol'=>'чхоль', 'byeon'=>'бён', 'saeng'=>'сэн', 'chaek'=>'чхэк', 'nyeok'=>'нёк', 'chang'=>'чхан', 'naeng'=>'нэн', 'cheop'=>'чхоп', 'reong'=>'рон', 'gyeop'=>'гёп', 'gyeom'=>'гём', 'gyeol'=>'гёль', 'hyeop'=>'хёп', 'reung'=>'рын', 'myeok'=>'мёк', 'hyeol'=>'хёль', 'hyeom'=>'хём', 'gyeon'=>'гён', 'gyeok'=>'гёк', 'hoeng'=>'хвен', 'myeon'=>'мён', 'hyung'=>'хюн', 'myeol'=>'мёль', 'hwaet'=>'хвэт', 'byeok'=>'бёк', 'hwang'=>'хван', 'ryung'=>'рюн', 'maeng'=>'мэн', 'hyeon'=>'хён', 'goeng'=>'гвен', 'pyeom'=>'пхём', 'hyang'=>'хян', 'haeng'=>'хэн', 'gwang'=>'гван', 'hyeok'=>'хёк', 'ryong'=>'рён', 'pyeon'=>'пхён', 'paeng'=>'пхэн', 'heung'=>'хын', 'song'=>'сон', 'yeok'=>'ёк', 'syeo'=>'сё', 'yeon'=>'ён', 'swae'=>'свэ', 'maek'=>'мэк', 'myeo'=>'мё', 'aeng'=>'эн', 'seup'=>'сып', 'ssae'=>'ссэ', 'meok'=>'мок', 'maen'=>'мэн', 'ssuk'=>'ссук', 'seum'=>'сым', 'yang'=>'ян', 'sing'=>'син', 'sseu'=>'ссы', 'seul'=>'сыль', 'bang'=>'бан', 'baem'=>'бэм', 'bong'=>'бон', 'bung'=>'бун', 'bing'=>'бин', 'beon'=>'бон', 'yeol'=>'ёль', 'byeo'=>'бё', 'beop'=>'боп', 'beom'=>'бом', 'beol'=>'боль', 'ppae'=>'ппэ', 'ppeo'=>'ппо', 'seok'=>'сок', 'seon'=>'сон', 'seol'=>'соль', 'seom'=>'сом', 'mong'=>'мон', 'baek'=>'бэк', 'ppeu'=>'ппы', 'sang'=>'сан', 'saek'=>'сэк', 'seop'=>'соп', 'chan'=>'чхан', 'teum'=>'тхым', 'teuk'=>'тхык', 'tung'=>'тхун', 'pyeo'=>'пхё', 'pung'=>'пхун', 'haek'=>'хэк', 'hang'=>'хан', 'tong'=>'тхон', 'taek'=>'тхэк', 'chim'=>'чхим', 'chil'=>'чхиль', 'chin'=>'чхин', 'chip'=>'чхип', 'kwae'=>'кхвэ', 'tang'=>'тхан', 'keun'=>'кхын', 'heon'=>'хон', 'heom'=>'хом', 'heun'=>'хын', 'heuk'=>'хык', 'hyul'=>'хюль', 'heul'=>'хыль', 'heum'=>'хым', 'huin'=>'хуин', 'heup'=>'хып', 'hwon'=>'хвон', 'hoek'=>'хвек', 'hong'=>'хон', 'hyeo'=>'хё', 'hwak'=>'хвак', 'hwan'=>'хван', 'hwae'=>'хвэ', 'hwal'=>'хваль', 'chik'=>'чхик', 'chum'=>'чхум', 'jeop'=>'чжоп', 'jeom'=>'чжом', 'jeol'=>'чжоль', 'jong'=>'чжон', 'jung'=>'чжун', 'jeul'=>'чжыль', 'jeuk'=>'чжык', 'jeon'=>'чжон', 'jeok'=>'чжок', 'wang'=>'ван', 'yeop'=>'ёп', 'yong'=>'ён', 'yung'=>'юн', 'jang'=>'чжан', 'eung'=>'ын', 'jeum'=>'чжым', 'jeup'=>'чжып', 'choe'=>'чхве', 'chon'=>'чхон', 'chok'=>'чхок', 'chwi'=>'чхви', 'chuk'=>'чхук', 'chul'=>'чхуль', 'chun'=>'чхун', 'cheo'=>'чхо', 'chae'=>'чхэ', 'jjae'=>'ччэ', 'jing'=>'чжин', 'chak'=>'чхак', 'mang'=>'ман', 'cham'=>'чхам', 'chal'=>'чхаль', 'yeom'=>'ём', 'sung'=>'сун', 'nyeo'=>'нё', 'neol'=>'ноль', 'nang'=>'нaн', 'kkwo'=>'ккво', 'tteu'=>'тты', 'ttuk'=>'ттук', 'nong'=>'нoн', 'deuk'=>'дык', 'deul'=>'дыль', 'kkum'=>'ккум', 'gang'=>'ган', 'ryeo'=>'рё', 'rong'=>'рoн', 'geup'=>'гып', 'kkae'=>'ккэ', 'kkok'=>'ккок', 'rang'=>'рaн', 'kkoe'=>'ккве', 'kkot'=>'ккот', 'nwae'=>'нвэ', 'gaek'=>'гэк', 'dang'=>'дан', 'daek'=>'дэк', 'deok'=>'док', 'gwae'=>'гвэ', 'gwal'=>'гваль', 'gwak'=>'гвак', 'gong'=>'гон', 'gwan'=>'гван', 'gyeo'=>'гё', 'geop'=>'гоп', 'dwae'=>'двэ', 'neuk'=>'нык', 'doen'=>'двен', 'dong'=>'дон', 'neum'=>'ным', 'geom'=>'гом', 'geol'=>'голь', 'geon'=>'гон', 'geum'=>'гым', 'ttae'=>'ттэ', 'ryul'=>'рюль', 'gwol'=>'гволь', 'gung'=>'гун', 'ryun'=>'рюн', 'gakk'=>'гакк', 'reuk'=>'рык', 'reun'=>'рын', 'reum'=>'рым', 'gwon'=>'гвон', 'ryuk'=>'рюк', 'gyun'=>'гюн', 'geuk'=>'гык', 'geun'=>'гын', 'geul'=>'гыль', 'gyul'=>'гюль', 'jeo'=>'чжо', 'nil'=>'ниль', 'nim'=>'ним', 'gul'=>'гуль', 'jap'=>'чжап', 'jam'=>'чжам', 'jae'=>'чжэ', 'nui'=>'нуи', 'jok'=>'чжок', 'pik'=>'пхик', 'jon'=>'чжон', 'jol'=>'чжоль', 'jwa'=>'чжва', 'neu'=>'ны', 'pil'=>'пхиль', 'tol'=>'тхоль', 'pip'=>'пхип', 'nik'=>'ник', 'jan'=>'чжан', 'toe'=>'тхве', 'gwi'=>'гви', 'nin'=>'нин', 'har'=>'халь', 'yuk'=>'юк', 'peo'=>'пхо', 'yun'=>'юн', 'yul'=>'юль', 'yut'=>'ют', 'dan'=>'дан', 'wol'=>'воль', 'won'=>'вон', 'pae'=>'пхэ', 'gwa'=>'гва', 'pal'=>'пхаль', 'heo'=>'хо', 'pan'=>'пхан', 'ung'=>'ун', 'hae'=>'хэ', 'eun'=>'ын', 'han'=>'хан', 'hal'=>'халь', 'hak'=>'хак', 'ton'=>'тхон', 'ing'=>'ин', 'mak'=>'мак', 'ham'=>'хам', 'hap'=>'хап', 'eum'=>'ым', 'eul'=>'ыль', 'teu'=>'тхы', 'eup'=>'ып', 'goe'=>'гве', 'twi'=>'тхви', 'jak'=>'чжак', 'jul'=>'чжуль', 'che'=>'чхе', 'pyo'=>'пхё', 'cho'=>'чхо', 'tam'=>'тхам', 'tal'=>'тхаль', 'kki'=>'кки', 'nak'=>'нак', 'nan'=>'нан', 'nae'=>'нэ', 'neo'=>'но', 'tap'=>'тхап', 'nap'=>'нап', 'nal'=>'наль', 'nam'=>'нам', 'tan'=>'тхан', 'tak'=>'тхак', 'keu'=>'кхы', 'gim'=>'гим', 'gil'=>'гиль', 'chi'=>'чхи', 'pok'=>'пхок', 'gin'=>'гин', 'kka'=>'ккa', 'kko'=>'кко', 'chu'=>'чху', 'kku'=>'кку', 'kal'=>'кхаль', 'pye'=>'пхе', 'geu'=>'гы', 'tae'=>'тхэ', 'pum'=>'пхум', 'noe'=>'нве', 'peu'=>'пхы', 'guk'=>'гук', 'gyo'=>'гё', 'jin'=>'чжин', 'jik'=>'чжик', 'nun'=>'нун', 'jeu'=>'чжы', 'juk'=>'чжук', 'joe'=>'чжве', 'jun'=>'чжун', 'nul'=>'нуль', 'jwi'=>'чжви', 'jil'=>'чжиль', 'jim'=>'чжим', 'jjo'=>'ччо', 'non'=>'нон', 'gyu'=>'гю', 'jji'=>'ччи', 'nok'=>'нок', 'cha'=>'чха', 'jja'=>'чча', 'got'=>'гот', 'teo'=>'тхо', 'jip'=>'чжип', 'nol'=>'ноль', 'jyo'=>'чжё', 'jom'=>'чжом', 'gun'=>'гун', 'gon'=>'гон', 'bin'=>'бин', 'bul'=>'буль', 'bun'=>'бун', 'bil'=>'биль', 'bim'=>'бим', 'ppa'=>'ппа', 'ram'=>'рaм', 'buk'=>'бук', 'gat'=>'гат', 'gap'=>'гап', 'rye'=>'ре', 'gam'=>'гам', 'heu'=>'хы', 'bok'=>'бок', 'rae'=>'рэ', 'bon'=>'бон', 'ran'=>'ран', 'rak'=>'рак', 'sat'=>'сат', 'tti'=>'тти', 'sap'=>'сап', 'sae'=>'сэ', 'seo'=>'со', 'tta'=>'ттa', 'ttu'=>'тту', 'sam'=>'сам', 'sal'=>'саль', 'hyu'=>'хю', 'ppu'=>'ппу', 'ppo'=>'ппо', 'ppi'=>'ппи', 'hwi'=>'хви', 'san'=>'сан', 'sak'=>'сак', 'rok'=>'рок', 'ron'=>'рон', 'mok'=>'мок', 'hui'=>'хуи', 'reu'=>'ры', 'mol'=>'моль', 'mot'=>'мот', 'myo'=>'мё', 'moe'=>'мве', 'gak'=>'гак', 'meo'=>'мо', 'him'=>'хим', 'mal'=>'маль', 'man'=>'ман', 'mae'=>'мэ', 'rip'=>'рип', 'rin'=>'рин', 'rim'=>'рим', 'muk'=>'мук', 'mun'=>'мун', 'ryu'=>'рю', 'bae'=>'бэ', 'bap'=>'бап', 'gal'=>'галь', 'beo'=>'бо', 'roe'=>'рве', 'ryo'=>'рё', 'bal'=>'баль', 'ban'=>'бан', 'gar'=>'гар', 'meu'=>'мы', 'mul'=>'муль', 'min'=>'мин', 'mil'=>'миль', 'bak'=>'бак', 'gan'=>'ган', 'hwe'=>'хве', 'tto'=>'тто', 'eol'=>'оль', 'eon'=>'он', 'eok'=>'ок', 'eom'=>'ом', 'eop'=>'оп', 'yeo'=>'ё', 'hon'=>'хон', 'hol'=>'холь', 'hop'=>'хоп', 'aek'=>'эк', 'gae'=>'гэ', 'ang'=>'ан', 'dok'=>'док', 'gye'=>'ге', 'yak'=>'як', 'yan'=>'ян', 'deo'=>'до', 'dae'=>'дэ', 'wae'=>'вэ', 'dam'=>'дам', 'wal'=>'валь', 'got'=>'гот', 'oen'=>'вен', 'dal'=>'даль', 'yok'=>'ёк', 'wan'=>'ван', 'gol'=>'голь', 'hok'=>'хок', 'dap'=>'дап', 'hye'=>'хе', 'gok'=>'гок', 'ong'=>'oн', 'gut'=>'гут', 'hoe'=>'хве', 'hwa'=>'хва', 'sul'=>'суль', 'sun'=>'сун', 'suk'=>'сук', 'sum'=>'сум', 'hyo'=>'хё', 'seu'=>'сы', 'ssi'=>'сси', 'soe'=>'све', 'dwi'=>'дви', 'sok'=>'сок', 'hun'=>'хун', 'son'=>'сон', 'sol'=>'соль', 'deu'=>'ды', 'sot'=>'сот', 'dun'=>'дун', 'swi'=>'сви', 'sim'=>'сим', 'doe'=>'две', 'don'=>'дон', 'dol'=>'доль', 'sso'=>'ссо', 'sil'=>'силь', 'sin'=>'син', 'duk'=>'дук', 'geo'=>'го', 'sik'=>'сик', 'ssa'=>'сса', 'sip'=>'сип', 'lee'=>'ли', 'ge'=>'ге', 'he'=>'хе', 'pe'=>'пхе', 'hu'=>'ху', 'go'=>'го', 'ho'=>'хо', 'gu'=>'гу', 'ha'=>'ха', 'pi'=>'пхи', 'hi'=>'хи', 'pu'=>'пху', 'po'=>'пхо', 'ip'=>'ип', 'al'=>'аль', 'am'=>'ам', 'ap'=>'ап', 'an'=>'ан', 'ak'=>'ак', 'ga'=>'га', 'du'=>'ду', 'si'=>'си', 'ap'=>'ап', 'ae'=>'э', 'ok'=>'ок', 'on'=>'он', 'ol'=>'оль', 'ye'=>'е', 'eo'=>'о', 'ya'=>'я', 'do'=>'до', 'su'=>'су', 'so'=>'со', 'mi'=>'ми', 'ba'=>'ба', 'ru'=>'ру', 'mu'=>'му', 'mo'=>'мо', 'ma'=>'ма', 'ri'=>'ри', 'me'=>'мe', 'ro'=>'рo', 're'=>'рe', 'sa'=>'са', 'di'=>'ди', 'se'=>'сe', 'ra'=>'ра', 'bi'=>'би', 'bo'=>'бо', 'bu'=>'бу', 'pa'=>'пха', 'om'=>'ом', 'ju'=>'чжу', 'nu'=>'ну', 'ji'=>'чжи', 'no'=>'но', 'jo'=>'чжо', 'je'=>'чже', 'im'=>'им', 'ja'=>'чжа', 'ni'=>'ни', 'ne'=>'не', 'na'=>'на', 'to'=>'тхо', 'tu'=>'тху', 'ti'=>'тхи', 'te'=>'тхе', 'ta'=>'тха', 'gi'=>'ги', 'ko'=>'кхо', 'ki'=>'кхи', 'il'=>'иль', 'in'=>'ин', 'ul'=>'уль', 'um'=>'ум', 'wa'=>'ва', 'un'=>'ун', 'uk'=>'ук', 'oe'=>'ве', 'yo'=>'ё', 'wi'=>'ви', 'wo'=>'во', 'ik'=>'ик', 'yu'=>'ю', 'ui'=>'уи', 'eu'=>'ы', 'da'=>'да', 'a'=>'а', 'u'=>'у', 'e'=>'е', 'i'=>'и', 'o'=>'о', 'g'=>'г', 'k'=>'г', 'n'=>'н', 'd'=>'д', 't'=>'д', 'r'=>'р', 'l'=>'р', 'm'=>'м', 'p'=>'б', 'b'=>'б', 's'=>'c', 'j'=>'дж', 'h'=>'х');

	$words=mb_split ('[ \-]',$korean);
	
	$russian='';
	
	foreach ($words as $n)
	{
		$w=$n;
		foreach($begin as $i=>$u)
        	$w = mb_eregi_replace('^'.$i,$u,$w); 
		foreach($end as $i=>$u)
        	$w = mb_eregi_replace($i.'$',$u,$w); 
		foreach($middle as $i=>$u)
        	$w = mb_eregi_replace($i,$u,$w);
	    
		if (strlen($n)>1)
		{
			if (ereg('([A-Z]{1})',mb_substr($n,0,1)) && ereg('([a-z\.\:\/]{1})',mb_substr($n,1,1))) $w=utf8_ucfirst($w);
			else if (ereg('([A-Z]{1})',mb_substr($n,0,1)) && ereg('([A-Z\.\:\/]{1})',mb_substr($n,1,1))) $w=utf8_uppercase($w);

		} else {
			if (ereg('([A-ZŌÔ]{1})',$n)) $w=utf8_ucfirst($w);
		}
	    
	    
	    $russian.=$w.' ';
	
	}
	return trim($russian);

} 



function translate_chinese ($chinese)
{
	
	mb_internal_encoding("UTF-8");
	mb_regex_encoding("UTF-8");

$middle=array ('chuang'=>'чуан', 'shuang'=>'шуан', 'zhuang'=>'чжуан', 'chuan'=>'чуань', 'sheng'=>'шэн', 'chuai'=>'чуай', 'xiang'=>'сян', 'zhang'=>'чжан', 'kuang'=>'куан', 'shuan'=>'шуань', 'shuai'=>'шуай', 'chang'=>'чан', 'chong'=>'чун', 'jiong'=>'цзюн', 'huang'=>'хуан', 'zhong'=>'чжун', 'zheng'=>'чжэн', 'guang'=>'гуан', 'qiong'=>'цюн', 'shang'=>'шан', 'zhuan'=>'чжуань', 'qiang'=>'цян', 'zhuai'=>'чжуай', 'jiang'=>'цзян', 'xiong'=>'сюн', 'cheng'=>'чэн', 'liang'=>'лян', 'niang'=>'нян', 'huan'=>'хуань', 'ming'=>'мин', 'huai'=>'хуай', 'xian'=>'сянь', 'zuan'=>'цзуань', 'hong'=>'хун', 'quan'=>'цюань', 'qing'=>'цин', 'meng'=>'мэн', 'neng'=>'нэн', 'jiao'=>'цзяо', 'jian'=>'цзянь', 'qiao'=>'цяо', 'heng'=>'хэн', 'mian'=>'мянь', 'miao'=>'мяо', 'rang'=>'жан', 'geng'=>'гэн', 'gong'=>'гун', 'guai'=>'гуай', 'zhai'=>'чжай', 'sang'=>'сан', 'zhan'=>'чжань', 'gang'=>'ган', 'guan'=>'гуань', 'ying'=>'ин', 'reng'=>'жэн', 'hang'=>'хан', 'nang'=>'нан', 'rong'=>'жун', 'yong'=>'юн', 'ruan'=>'жуань', 'yang'=>'ян', 'jing'=>'цзин', 'xing'=>'син', 'liao'=>'ляо', 'kuai'=>'куай', 'pian'=>'пянь', 'piao'=>'пяо', 'keng'=>'кэн', 'kong'=>'кун', 'kuan'=>'куань', 'lian'=>'лянь', 'lang'=>'лан', 'zang'=>'цзан', 'pang'=>'пан', 'tuan'=>'туань', 'xuan'=>'сюань', 'peng'=>'пэн', 'ling'=>'лин', 'ping'=>'пин', 'juan'=>'цзюань', 'yuan'=>'юань', 'niao'=>'няо', 'zong'=>'цзун', 'nian'=>'нянь', 'leng'=>'лэн', 'mang'=>'ман', 'ning'=>'нин', 'luan'=>'луань', 'zeng'=>'цзэн', 'kang'=>'кан', 'nuan'=>'нуань', 'xiao'=>'сяо', 'nong'=>'нун', 'long'=>'лун', 'qian'=>'цянь', 'weng'=>'вэн', 'shen'=>'шэнь', 'shei'=>'шэй', 'dang'=>'дан', 'chuo'=>'чо', 'bing'=>'бин', 'chui'=>'чуй', 'chun'=>'чунь', 'shao'=>'шао', 'deng'=>'дэн', 'shan'=>'шань', 'ding'=>'дин', 'shai'=>'шай', 'shuo'=>'шо', 'dian'=>'дянь', 'zhua'=>'чжуа', 'teng'=>'тэн', 'chou'=>'чоу', 'chen'=>'чэнь', 'zhuo'=>'чжо', 'cong'=>'цун', 'zhun'=>'чжунь', 'ceng'=>'цэн', 'shui'=>'шуй', 'shun'=>'шунь', 'cang'=>'цан', 'cuan'=>'цуань', 'shua'=>'шуа', 'chao'=>'чао', 'shou'=>'шоу', 'zhao'=>'чжао', 'tang'=>'тан', 'chan'=>'чань', 'zhui'=>'чжуй', 'chai'=>'чай', 'dong'=>'дун', 'diao'=>'дяо', 'tong'=>'тун', 'ngyo'=>'нъё', 'ting'=>'тин', 'wang'=>'ван', 'suan'=>'суань', 'feng'=>'фэн', 'biao'=>'бяо', 'song'=>'сун', 'fang'=>'фан', 'ngyi'=>'нъи', 'ngye'=>'нъе', 'zhei'=>'чжэй', 'zhen'=>'чжэнь', 'zhou'=>'чжоу', 'bang'=>'бан', 'ngya'=>'нъя', 'ngyu'=>'нъю', 'duan'=>'дуань', 'bian'=>'бянь', 'beng'=>'бэн', 'seng'=>'сэн', 'tiao'=>'тяо', 'tian'=>'тянь', 'tan'=>'тань', 'tai'=>'тай', 'nan'=>'нань', 'tou'=>'тоу', 'nao'=>'нао', 'xiu'=>'сю', 'tuo'=>'то', 'nei'=>'нэй', 'tui'=>'туй', 'ten'=>'тэнь', 'xun'=>'сюнь', 'nie'=>'не', 'niu'=>'ню', 'nin'=>'нинь', 'yan'=>'янь', 'tie'=>'те', 'nuo'=>'но', 'tao'=>'тао', 'xue'=>'сюэ', 'tun'=>'тунь', 'nen'=>'нэнь', 'yao'=>'яо', 'xin'=>'синь', 'ren'=>'жэнь', 'rou'=>'жоу', 'wan'=>'вань', 'rui'=>'жуй', 'sui'=>'суй', 'rao'=>'жао', 'xia'=>'ся', 'ran'=>'жань', 'sun'=>'сунь', 'run'=>'жунь', 'sou'=>'соу', 'sao'=>'сао', 'wei'=>'вэй', 'sen'=>'сэнь', 'san'=>'сань', 'sai'=>'сай', 'ruo'=>'жо', 'nai'=>'най', 'wen'=>'вэнь', 'suo'=>'со', 'qun'=>'цюнь', 'pie'=>'пе', 'pin'=>'пинь', 'pou'=>'поу', 'shu'=>'шу', 'pen'=>'пэнь', 'pan'=>'пань', 'pao'=>'пао', 'pei'=>'пэй', 'xie'=>'се', 'shi'=>'ши', 'sha'=>'ша', 'wai'=>'вай', 'que'=>'цюэ', 'qiu'=>'цю', 'qin'=>'цинь', 'qia'=>'ця', 'she'=>'шэ', 'qie'=>'це', 'pai'=>'пай', 'zao'=>'цзао', 'fan'=>'фань', 'eng'=>'эн', 'fei'=>'фэй', 'fen'=>'фэнь', 'fou'=>'фоу', 'zhe'=>'чжэ', 'zhi'=>'чжи', 'duo'=>'до', 'diu'=>'дю', 'die'=>'де', 'dou'=>'доу', 'zhu'=>'чжу', 'dun'=>'дунь', 'dui'=>'дуй', 'gai'=>'гай', 'gan'=>'гань', 'guo'=>'го', 'gun'=>'гунь', 'zuo'=>'цзо', 'hai'=>'хай', 'zun'=>'цзунь', 'han'=>'хань', 'gui'=>'гуй', 'gua'=>'гуа', 'gei'=>'гэй', 'gao'=>'гао', 'gen'=>'гэнь', 'gou'=>'гоу', 'zha'=>'чжа', 'dia'=>'дя', 'dei'=>'дэй', 'ngu'=>'нъу', 'bin'=>'бинь', 'ngo'=>'нъо', 'ngi'=>'нъи', 'cai'=>'цай', 'nge'=>'нъэ', 'bie'=>'бе', 'ben'=>'бэнь', 'ang'=>'ан', 'lee'=>'ли', 'bai'=>'бай', 'ban'=>'бань', 'bei'=>'бэй', 'bao'=>'бао', 'can'=>'цань', 'cao'=>'цао', 'chi'=>'чи', 'che'=>'чэ', 'chu'=>'чу', 'dai'=>'дай', 'dao'=>'дао', 'dan'=>'дань', 'cha'=>'ча', 'cuo'=>'цо', 'cen'=>'цэнь', 'nga'=>'нъа', 'cou'=>'цоу', 'cui'=>'цуй', 'cun'=>'цунь', 'hei'=>'хэй', 'hao'=>'хао', 'lie'=>'ле', 'lia'=>'ля', 'lin'=>'линь', 'liu'=>'лю', 'zai'=>'цзай', 'lou'=>'лоу', 'zan'=>'цзань', 'lei'=>'лэй', 'hen'=>'хэнь', 'kun'=>'кунь', 'lai'=>'лай', 'lan'=>'лань', 'lao'=>'лао', 'yun'=>'юнь', 'lun'=>'лунь', 'mie'=>'ме', 'you'=>'ю', 'min'=>'минь', 'miu'=>'мю', 'yin'=>'инь', 'mou'=>'моу', 'men'=>'мэнь', 'mei'=>'мэй', 'yue'=>'юэ', 'luo'=>'ло', 'mai'=>'май', 'man'=>'мань', 'mao'=>'мао', 'kui'=>'куй', 'kuo'=>'ко', 'jia'=>'цзя', 'zou'=>'цзоу', 'kua'=>'куа', 'jie'=>'цзе', 'zui'=>'цзуй', 'hng'=>'хнг', 'huo'=>'хо', 'hou'=>'хоу', 'hua'=>'хуа', 'hui'=>'хуэй (хой, хуй)', 'hun'=>'хунь', 'jin'=>'цзинь', 'jiu'=>'цзю', 'zen'=>'цзэнь', 'ken'=>'кэнь', 'kou'=>'коу', 'zei'=>'цзэй', 'kao'=>'као', 'kan'=>'кань', 'jun'=>'цзюнь', 'jue'=>'цзюэ', 'kai'=>'кай', 'iu'=>'ю', 'ye'=>'е', 'yi'=>'и', 'wu'=>'у', 'zh'=>'чж', 'xi'=>'си', 'za'=>'цза', 'zi'=>'цзы', 'xu'=>'сюй', 'ze'=>'цзэ', 'ya'=>'я', 'wa'=>'ва', 'yu'=>'юй', 'zu'=>'цзу', 'wo'=>'во', 'na'=>'на', 'ha'=>'ха', 'he'=>'хэ', 'hm'=>'хм', 'hu'=>'ху', 'gu'=>'гу', 'ge'=>'гэ', 'fo'=>'фо', 'fu'=>'фу', 'ga'=>'га', 'ji'=>'цзи', 'ju'=>'цзюй', 'li'=>'ли', 'tu'=>'ту', 'le'=>'люэ', 'le'=>'лэ', 'la'=>'ла', 'ka'=>'ка', 'ke'=>'кэ', 'ku'=>'ку', 'fa'=>'фа', 'er'=>'эр', 'bo'=>'бо', 'bu'=>'бу', 'ca'=>'ца', 'bi'=>'би', 'ba'=>'ба', 'ai'=>'ай', 'an'=>'ань', 'ao'=>'ао', 'ce'=>'цэ', 'ci'=>'цы', 'du'=>'ду', 'ei'=>'эй', 'en'=>'энь', 'di'=>'ди', 'de'=>'дэ', 'cu'=>'цу', 'ch'=>'ч', 'da'=>'да', 'ma'=>'ма', 'lu'=>'лу', 'qi'=>'ци', 'qu'=>'цюй', 're'=>'жэ', 'pu'=>'пу', 'po'=>'по', 'ou'=>'оу', 'pa'=>'па', 'me'=>'мэ', 'ri'=>'жи', 'ru'=>'жу', 'ta'=>'та', 'te'=>'тэ', 'ti'=>'ти', 'sh'=>'ш', 'su'=>'су', 'sa'=>'са', 'se'=>'сэ', 'si'=>'сы', 'ne'=>'нюэ', 'pi'=>'пи', 'ng'=>'нг', 'mm'=>'мм', 'mo'=>'мо', 'mu'=>'му', 'ne'=>'нэ', 'nu'=>'ну', 'ni'=>'ни', 'mi'=>'ми', 'm'=>'м', 'n'=>'н', 's'=>'с', 'z'=>'цз', 'b'=>'б', 'l'=>'л', 'l'=>'люй', 'w'=>'в', 'c'=>'ц', 't'=>'т', 'x'=>'с', 'g'=>'г', 'j'=>'цз', 'y'=>'й', 'p'=>'п', 'h'=>'х', 'n'=>'нюй', 'f'=>'ф', 'q'=>'ц', 'o'=>'о', 'a'=>'а', 'r'=>'ж', 'e'=>'э', 'k'=>'к', 'd'=>'д', 'u'=>'у');

	//$words=mb_split ('[ \-]',$chinese);
	$words=preg_split ('/[ \-]+/',$chinese);
	
	$russian='';
	
	foreach ($words as $n)
	{
		$w=$n;
		foreach($middle as $i=>$u)
        	$w = mb_eregi_replace($i,$u,$w);
	    
		if (mb_strlen($n)>1)
		{
			if (ereg('([A-Z]{1})',mb_substr($n,0,1)) && ereg('([a-z\.\:\/]{1})',mb_substr($n,1,1))) $w=utf8_ucfirst($w);
			else if (ereg('([A-Z]{1})',mb_substr($n,0,1)) && ereg('([A-Z\.\:\/]{1})',mb_substr($n,1,1))) $w=utf8_uppercase($w);

		} else {
			if (ereg('([A-ZŌÔ]{1})',$n)) $w=utf8_ucfirst($w);
		}
	    
	    if (mb_substr($n,-2)=='ng' && mb_substr($w,-2)=='нг') $w=mb_substr($w,0,-2).'н';
	    if (mb_substr($n,-1)=='n' && mb_substr($w,-1)=='н') $w=mb_substr($w,0,-1).'нь';
	    if (mb_substr($n,-2)=='ng' && mb_substr($w,-3)=='ньг') $w=mb_substr($w,0,-1);
	    
	    $russian.=$w.' ';
	
	}
	return trim($russian);

}

function before_first_str ($text, $str, $include=FALSE)
{
	if (mb_strpos ($text,$str)!==FALSE)
		return ($include?mb_strstr ($text,$str,TRUE).$str:mb_strstr ($text,$str,TRUE));
	else return $text;
	
}

function before_last_str ($text, $str, $include=FALSE)
{
	if (mb_strstr ($text,$str))
		return ($include?mb_strrchr ($text,$str,true).$str:mb_strrchr ($text,$str,true));
	else return $text;	
}

function after_first_str ($text, $str, $include=FALSE)
{
	if (mb_strpos ($text,$str)!==FALSE)
		return ($include?mb_substr(mb_strstr ($text,$str),1):mb_substr(mb_strstr ($text,$str),mb_strlen($str)));
	else return $text;
	
}

function after_last_str ($text, $str, $include=FALSE)
{
	if (mb_strstr ($text,$str))
		return ($include?mb_strrchr ($text,$str):mb_substr(mb_strrchr ($text,$str),mb_strlen($str)));
	else return $text;	
}

 function print_array($ar) {
 static $count; 
 $count = (isset($count)) ? ++$count : 0; 

 $colors = array('#FFCB72', '#FFB072', '#FFE972', '#F1FF72', 
     '#92FF69', '#6EF6DA', '#72D9FE', '#FFFFFF', '#EEEEEE', '#DDDDDD', '#CCCCCC'); 

 if ($count > count($colors)) {
 echo "Достигнута максимальная глубина погружения!";
 $count--;
 return;
 } 

 if (!is_array($ar)) {
 echo "Passed argument is not an array!<p>";
 return; } 

 echo "<table border=1 cellpadding=0 cellspacing=0 bgcolor=$colors[$count]>";
 while(list($k, $v) = each($ar)) {
 echo "<tr><td>$k</td><td>$v</td></tr>\n";
 if (is_array($v)) {
 echo "<tr><td> </td><td>";
 print_array($v);
 echo "</td></tr>\n";
 }
 }
 echo "</table>";
 $count--;
 }

class XmlToArray
{
 
    var $xml='';
 
    /**
    * Default Constructor
    * @param $xml = xml data
    * @return none
    */
 
    function XmlToArray($xml)
    {
       $this->xml = $xml;
    }
 
    /**
    * _struct_to_array($values, &$i)
    *
    * This is adds the contents of the return xml into the array for easier processing.
    * Recursive, Static
    *
    * @access    private
    * @param    array  $values this is the xml data in an array
    * @param    int    $i  this is the current location in the array
    * @return    Array
    */
 
    function _struct_to_array($values, &$i)
    {
        $child = array();
        if (isset($values[$i]['value'])) array_push($child, $values[$i]['value']);
 
        while ($i++ < count($values)) {
            switch ($values[$i]['type']) {
                case 'cdata':
                array_push($child, $values[$i]['value']);
                break;
 
                case 'complete':
                    $name = $values[$i]['tag'];
                    if(!empty($name)){
                    $child[$name]= ($values[$i]['value'])?($values[$i]['value']):'';
                    if(isset($values[$i]['attributes'])) {
                        $child[$name] = $values[$i]['attributes'];
                    }
                }
              break;
 
                case 'open':
                    $name = $values[$i]['tag'];
                    $size = isset($child[$name]) ? sizeof($child[$name]) : 0;
                    $child[$name][$size] = $this->_struct_to_array($values, $i);
                break;
 
                case 'close':
                return $child;
                break;
            }
        }
        return $child;
    }//_struct_to_array
 
    /**
    * createArray($data)
    *
    * This is adds the contents of the return xml into the array for easier processing.
    *
    * @access    public
    * @param    string    $data this is the string of the xml data
    * @return    Array
    */
    function createArray()
    {
        $xml    = $this->xml;
        $values = array();
        $index  = array();
        $array  = array();
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parse_into_struct($parser, $xml, $values, $index);
        xml_parser_free($parser);
        $i = 0;
        $name = $values[$i]['tag'];
        $array[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
        $array[$name] = $this->_struct_to_array($values, $i);
        return $array;
    }//createArray
 
}


function rating ($grade, $publication)
{
	$grade=str_replace(',','.',$grade);
	$grade=str_replace('%','',$grade);
	$grade=betweens($grade,array('',' из ','','/'),TRUE,FALSE);
	if ($grade=='#плохо') return 20;
	else
	if ($grade=='#так себе') return 50;
	else
	if ($grade=='#хорошо') return 80;
	else
	if ($grade=='#отлично') return 100;
	else
	if ($publication=='Кино-Говно.ком') {
		if ($grade=='Клиника') return 10;
		else
		if ($grade=='Говно') return 20;
		else
		if ($grade=='Стерильно') return 50;
		else
		if ($grade=='Кино') return 90;
		else
		if ($grade=='Нехуёвое кино') return 95;
		else
		if ($grade=='Охуительное кино') return 100;
		else
		if ($grade=='Игра') return 90;
		else
		if ($grade=='Нехуёвая игра') return 95;
		else
		if ($grade=='Охуительная игра') return 100;
		else return intval($grade);
	}
	else		
	if ($publication=='StopGame') {
		if ($grade=='Мусор') return 20;
		else
		if ($grade=='Проходняк') return 50;
		else
		if ($grade=='Похвально') return 80;
		else
		if ($grade=='Изумительно') return 100;
		else
		if ($grade=='Изумительно + Наш выбор') return 100;
		else return intval($grade);
	}
		
	else		
	if ($publication=='Обзоркино') {
		if ($grade=='Хорошее кино') return 100;
		else
		if ($grade=='Плохое кино') return 20;
		else
		if ($grade=='Так себе кино') return 50;
		else return intval($grade);
	}
		else		
	if ($publication=='Киногуру') {
		if ($grade=='Позор') return 10;
		else
		if ($grade=='Плохо') return 20;
		else
		if ($grade=='Средне') return 50;
		else
		if ($grade=='Хорошо') return 80;
		else
		if ($grade=='Отлично') return 90;
		else
		if ($grade=='Шедевр') return 100;
		else return intval($grade);
	}
		else
	if ($publication=='Авторский проект Алекса Экслера') {
		if ($grade=='Отстой') return 10;
		else
		if ($grade=='Фигня') return 25;
		else
		if ($grade=='Средненько') return 50;
		else
		if ($grade=='Неплохо') return 65;
		else
		if ($grade=='Хорошо') return 80;
		else
		if ($grade=='Отлично') return 90;
		else
		if ($grade=='Шедевр') return 100;
		else return $grade*10;
	}
	else 
	{
		con ();
		$gp=fq("SELECT * FROM publications WHERE name='".mysql_escape_string($publication)."'");
		if ($gp['topgrade']!=0) return $grade/$gp['topgrade']*100;
		else return $grade;
	}
	
	
	
	/*
	if ($publication=='TimeOut' || $publication=='Афиша@mail.ru' || $publication=='25-й кадр' || $publication=='TramVision' || $publication=='Афиша' || $publication=='GamerPro' || $publication=='GameWay') return $grade*20;
	else
	if ($publication=='Кинокадр' || $publication=='PS3 Noizelss ;)' || $publication=='Игры@mail.ru' || $publication=='3DNews' || $publication=='ProGamer.Ru' || $publication=='Great Gamer' || $publication=='4 Gamers' || $publication=='VRgames') return $grade*10;
	else
	if ($publication=='MegaPlay' || $publication=='Absolute Games') return $grade;
	else
	*/		
	
	
}


function get_rating ($grade, $publication_id, $year='')
{
	$grade=str_replace(',','.',$grade);
	$grade=str_replace('%','',$grade);
	$grade=betweens($grade,array('',' из ','','/'),TRUE,FALSE);
	if ($grade=='#плохо') return 20;
	else
	if ($grade=='#так себе') return 50;
	else
	if ($grade=='#хорошо') return 80;
	else
	if ($grade=='#отлично') return 100;
	else
	if ($publication_id==37) {
		if ($grade=='Клиника') return 10;
		else
		if ($grade=='Говно') return 20;
		else
		if ($grade=='Стерильно') return 50;
		else
		if ($grade=='Кино') return 90;
		else
		if ($grade=='Нехуёвое кино') return 95;
		else
		if ($grade=='Охуительное кино') return 100;
		else
		if ($grade=='Игра') return 90;
		else
		if ($grade=='Нехуёвая игра') return 95;
		else
		if ($grade=='Охуительная игра') return 100;
		else return intval($grade);
	}
	else		
	if ($publication_id==27) {
		if ($grade=='Мусор') return 20;
		else
		if ($grade=='Проходняк') return 50;
		else
		if ($grade=='Похвально') return 80;
		else
		if ($grade=='Изумительно') return 100;
		else
		if ($grade=='Изумительно + Наш выбор') return 100;
	}
	else		
	if ($publication_id==140) {
		if ($grade=='Хорошее кино') return 100;
		else
		if ($grade=='Плохое кино') return 20;
		else
		if ($grade=='Так себе кино') return 50;
	}
	else		
	if ($publication_id==91) {
		if ($grade=='Отстой') return 10;
		else
		if ($grade=='Фигня') return 25;
		else
		if ($grade=='Средненько') return 50;
		else
		if ($grade=='Неплохо') return 65;
		else
		if ($grade=='Хорошо') return 80;
		else
		if ($grade=='Отлично') return 90;
		else
		if ($grade=='Шедевр') return 100;
		else return $grade*10;
	}
	else		
	if ($publication_id==161) {
		if ($grade=='Позор') return 10;
		else
		if ($grade=='Плохо') return 20;
		else
		if ($grade=='Средне') return 50;
		else
		if ($grade=='Хорошо') return 80;
		else
		if ($grade=='Отлично') return 90;
		else
		if ($grade=='Шедевр') return 100;
	}
	else		
	if ($publication_id==9) {
		if ($year>=1999) return $grade*20;
		else return $grade;
	}
	/*
	else		
	if ($publication_id==105) {
		if ($year>=2013) return $grade*10;
		else return $grade*20;
	}
	*/
	else 
	{		
		con ();
		$gp=fq("SELECT * FROM publications WHERE id=$publication_id");
		if ($gp['topgrade']!=0) return $grade/$gp['topgrade']*100;
		else return $grade;
	}
	
	
}
	
	function correct_wisdom_weight ($review_id, $review_timestamp, $critic_id)
	{
		$weight=1;
		$wisdom_count=faq("SELECT COUNT(*) as count FROM wisdom WHERE review_id=".intval($review_id),"count");

		$critics=explode (' ',trim($critic_id));
		$wisdom_prev=0;
		foreach ($critics as $c)
		{
			$wisdom_prev+=faq("SELECT SUM(wisdom_count) as sum FROM (SELECT wisdom_count FROM reviews WHERE critic_id LIKE '% ".intval($c)." %' AND review_timestamp<".intval($review_timestamp)." AND id!=".intval($review_id)." ORDER BY review_timestamp DESC LIMIT 10) as sum_table","sum");
		}
		
		$weight=$weight-$wisdom_count*0.25-$wisdom_prev*0.1;
		
		if ($weight<0) $weight=0;
		// echo 'id: '.$review_id.' - '.$weight.'<br>';
		return $weight;
	}
	
		
	function get_grade_new ($codename, $type)
	{
		$numr=0;
		$reviews=q("SELECT * FROM reviews WHERE codename='".mysql_real_escape_string($codename)."' AND type=".intval($type)." AND grade!=''");
		$sum=0;
		$sum_w=0;
		$weights=0;
		$rat=0;
		while ($r=mysql_fetch_array($reviews))
		{
			if (mb_substr($r['issue'],0,6)=='http://') $year=date('Y',date_to_timestamp($r['date']));
			else $year=intval(before_first_str($r['issue'],'#'));
			
			$r['grade']=betweens($r['grade'],array('',' из ','','/'),TRUE,FALSE);
			
			$rating=get_rating($r['grade'],$r['publication_id'],$year);
			$sum+=$rating;			
			if ($rating!=0)
			{
				$numr++;
				$pw=fq("SELECT weight FROM publications WHERE id='$r[publication_id]'");
				$corr=correct_wisdom_weight ($r['id'], $r['review_timestamp'], $r['critic_id']);
				if ($pw['weight']!=0)
				{
					$sum_w+=$rating*$pw['weight']*$corr;
					$weights+=$pw['weight']*$corr;
				}
				else
				{
					$sum_w+=$rating*$corr;
					$weights+=$corr;
				}
			}
		}

		$rat=round($sum_w/$weights);

		$top=250;

		if ($rat>75)
			$bayes_middle=BAYESTOP;
		else
		if ($rat>50)
			$bayes_middle=BAYESMIDDLETOP;
		else
		if ($rat>25)
			$bayes_middle=BAYESMIDDLEBOTTOM;
		else
		if ($rat>0)
			$bayes_middle=BAYESBOTTOM;		
		else
			$bayes_middle=0;

/*			$bayes_middle=faq ("SELECT (sum(t.rating) / count(t.rating)) as middle FROM ( SELECT rating FROM ".($type==0?"movies":"gamesofgeneration")." WHERE reviews_count>=".MINREVIEWS." AND rating>=55 ORDER by rating DESC LIMIT 250 ) as t","middle");*/

/*			$bayes_middle=faq ("SELECT (sum(t.rating) / count(t.rating)) as middle FROM ( SELECT rating FROM ".($type==0?"movies":"gamesofgeneration")." WHERE reviews_count>=".MINREVIEWS." AND rating<55 AND rating>0 ORDER by rating DESC LIMIT 250 ) as t","middle");*/

		if ($bayes_middle>0)
			$bayes=$numr/($numr+MINBAYES)*$rat+MINBAYES/($numr+MINBAYES)*$bayes_middle;
		else
			$bayes=0;		
		
		// echo $rat.' - <b>'.$bayes.'</b> ('.$numr.') - '.$bayes_middle;
		return array ('rating'=>$rat, 'bayes'=>$bayes, 'reviews_count'=>$numr);
	}
	
	function get_grade ($codename, $type)
	{
	
		// СТАРАЯ ФУНКЦИЯ
		// НАДО ИЗБАВЛЯТЬСЯ
		
		if ($type==0)
			$reviews=q("SELECT * FROM reviews WHERE russian='".mysql_real_escape_string($codename)."' AND type=0");
		else
			$reviews=q("SELECT * FROM reviews WHERE original='".mysql_real_escape_string($codename)."' AND type=1");

		$sum=0;
		$sum_w=0;
		$weights=0;
		while ($r=mysql_fetch_array($reviews))
		{
			$numr++;

			$r['grade']=betweens($r['grade'],array('',' из ','','/'),TRUE,FALSE);

			$rating=rating($r['grade'],$r['publication']);
			$sum+=$rating;
			$pw=fq("SELECT weight FROM publications WHERE id='$r[publication_id]'");
			if ($pw['weight']!=0)
			{
				$sum_w+=$rating*$pw['weight'];
				$weights+=$pw['weight'];
			}
			else
			{
				$sum_w+=$rating;
				$weights+=1;
			}
		}
		$rat=round($sum_w/$weights);		
		return $rat;
	}

function date_to_timestamp ($old_date, $test=0)
{

	$months_eng=array ('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov', 'dec');
	$months_rus=array ('янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя', 'дек');
	
	$old_date=preg_replace ('/ \(.+\)/','',$old_date);
	$old_date=preg_replace ('/\(.+\)/','',$old_date);
	$old_date=preg_replace ('/\s{2,}/',' ',$old_date);
	
	$old_date=preg_replace ('/([0-9]+)(th|nd|st|rd) /','\\1 ',$old_date);
	
	$h=0;
	$min=0;
	$s=0;
	
	$d=0;
	$m=0;
	$y=0;
	
	$date=$old_date;
	$date=str_replace (
		array (' г.', ' г', ' года', ' год'),
		array ('', '', '', ''),
		$date);
	$date=mb_trim($date);
	
	if ($date=='' || $date=='00.00.0000' || $date=='0000-00-00' || $date=='0000-00-00 00:00:00')
		$timestamp=0;
	
	else if (preg_match('/^[0-9]{4}$/si',$date))
		$timestamp=mktime (3, 0, 0, 12, 31, $date);
	else
	{	
		$tt=preg_split ('/[ ,]+/',$date);
		if (mb_strpos(end($tt),':')!==FALSE)
		{
			$t=explode (':',end($tt));
			$h=(int)$t[0];
			$min=(int)$t[1];
			$s=(int)$t[2];
			$date=mb_strrchr($date,' ',TRUE);
		}
		
		$date=trim($date);
		if (mb_substr($date,-2)==' в') $date=mb_substr($date,0,-2);
		if (mb_substr($date,-1)==',') $date=mb_substr($date,0,-1);
		$date=trim($date);
		
		if (mb_strpos($date,' ')!==FALSE)
		// Значит, текстовая дата типа "4 сентября 2012"
		{
			$dd=preg_split ('/[ ,]+/',$date);
			//print_r($dd);

			
			if (sizeof($dd)==2 && preg_match('/^[0-9]{4}$/si',$dd[1]))
			{
			
				$y=(int)$dd[1];
				$dd[0]=mb_trim($dd[0]);
				$dd[1]=mb_trim($dd[1]);
				$h=2;
				$min=0;
				$s=0;
				$mt=utf8_lowercase(mb_substr($dd[0],0,3));
				//echo '#'.$mt.'#';
			    switch ($mt)
			    {
			        case 'win':
			        case 'зим':
			        	$m=2;
			        	break;
			        case 'spr':
			        case 'вес':
			        	$m=5;
			        	break;
			        case 'sum':
			        case 'лет':
			        	$m=8;
			        	break;
			        case 'fal':
			        case 'aut':
			        case 'осе':
			        	$m=11;
			        	break;
			        case 'q1':
			        case '1 к':
			        	$m=3;
			        	break;
			        case 'q2':
			        case '2 к':
			        	$m=6;
			        	break;
			        case 'q3':
			        case '3 к':
			        	$m=9;
			        	break;
			        case 'q4':
			        case '4 к':
			        	$m=12;
			        	break;
			    }
			    
				if (in_array ($mt,$months_rus))
				{
					$m=array_search($mt,$months_rus)+1;
					$h=1;
				}
				else
				if (in_array ($mt,$months_eng))
				{
					$m=array_search($mt,$months_eng)+1;
					$h=1;
				}
				else
				if ($mt=='мая')
				{
					$m=5;
					$h=1;
				}
				
				$d=date('t',mktime(0,0,0,$m,1,$y));
				
				//echo $h.$min.$s.$m.$d.$y;
			
			} else
			{
				$dd[0]=mb_trim($dd[0]);
				$dd[1]=mb_trim($dd[1]);
				$dd[2]=mb_trim($dd[2]);

				$y=(int)$dd[2];
				if ($y==0) $y=date('Y');
	
				if (preg_match('/^[0-9]+$/',$dd[0]))
				{
					$d=(int)$dd[0];
					$mt=utf8_lowercase(mb_substr($dd[1],0,3));
				} else
				{
					$d=(int)$dd[1];
					$mt=utf8_lowercase(mb_substr($dd[0],0,3));
				}
	
				if (in_array ($mt,$months_rus))
					$m=array_search($mt,$months_rus)+1;
				else
				if (in_array ($mt,$months_eng))
					$m=array_search($mt,$months_eng)+1;
				else
				if ($mt=='мая')
					$m=5;	
				
			}
			


		} else
		if (mb_strpos($date,'.')!==FALSE || mb_strpos($date,'-')!==FALSE || mb_strpos($date,'/')!==FALSE)
		// Значит, цифровая дата типа "04.02.2012" или типа того
		{
			$dd=preg_split ('/[\.\-\/]+/',$date);
			$d=(int)$dd[0];
			$m=(int)$dd[1];
			$y=(int)$dd[2];
			
			if ($d>31)
			{
				$y=$d;
				$d=(int)$dd[2];
			}
			
			if ($y==0) $y=date('Y');
			
			if ($y<30)
				$y=2000+$y;
			else
			if ($y<100)
				$y=1900+$y;

		}

		$timestamp=mktime ($h, $min, $s, $m, $d, $y);
	}
	return $timestamp;	
}

function nice_companies_list ($list)
{
	$ar=explode (',',$list);
	$i=0;
	$comp=array();
	foreach ($ar as $a)
	{
		$dev='';
		$pl='';
		if (mb_strstr($a,'('))
		{
			$dev=trim(before_first_str($a,'('));
			$pl=trim(between($a,'(',')'));
		} else $dev=trim($a);
		
		if ($dev!='' && $dev!='To Be Announced')
		{
			if (in_array($dev,$companies)) 
			{
				$num=array_search($dev,$companies);
				if ($pl!='')
					$platforms[$num].=$pl.', ';
			} else
			{
				$companies[$i]=$dev;
				$platforms[$i]=$pl.', ';
				$i++;
			}
		}
	}
	
	if (sizeof($companies)>1)
	{
		$newlist='';
		foreach ($companies as $k=>$co)
		{
			$newlist.=$co;
			if ($platforms[$k]!='')
				$newlist.=' ('.(mb_substr($platforms[$k],-2)==', '?mb_substr($platforms[$k],0,-2):$platforms[$k]).'), ';
		}
		if (mb_substr($newlist,-2)==', ') $newlist=mb_substr($newlist,0,-2);
	} else $newlist=$companies[0];
	return $newlist;
}


function remove_tail ($text, $str)
{
	// Убирает подстроку из конца, если она есть. Если нет - оставляет нетронутой
	
	$len=mb_strlen($str);
	if (mb_substr($text,-$len)==$str)
		return mb_substr($text,0,-$len);
	else
		return $text;
}


function remove_head ($text, $str)
{
	// Убирает подстроку из конца, если она есть. Если нет - оставляет нетронутой
	
	$len=mb_strlen($str);
	if (mb_substr($text,0,$len)==$str)
		return mb_substr($text,$len);
	else
		return $text;
}


function generate_password($length=6) {

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";

    $code = "";

    $clen = strlen($chars) - 1;  

    while (strlen($code) < $length) {

            $code .= $chars[mt_rand(0,$clen)];  

    }

    return $code;

}

function rss_short ($d, $len=400)
{

	$d=mb_eregi_replace ("<strong>", "<b>", $d);
	$d=mb_eregi_replace ("</strong>", "</b>", $d);
	$d=mb_eregi_replace ("<italic>", "<i>", $d);
	$d=mb_eregi_replace ("</italic>", "</i>", $d);
	$d=mb_eregi_replace ("<a [^>]+>", "<b>", $d);
	$d=mb_eregi_replace ("</a>", "</b>", $d);
	
	$s1=array('<BR>','<B>','</B>','<I>','</I>','<U>','</U>','</P>','&laquo;','&raquo;','&#151;','&minus;','&#8722;','&#8212;','&nbsp;');
	$s2=array('<br>','<b>','</b>','<i>','</i>','<u>','</u>','</p>','«','»','-','-','-','-',' ');
	
	$d=str_replace ($s1,$s2, $d);
	
	$d=mb_trim($d);
	
	while (mb_substr($d,0,4)=='<br>') $d=mb_trim(mb_substr($d,4));
	while (mb_substr($d,0,6)=='<br />') $d=mb_trim(mb_substr($d,6));
	while (mb_substr($d,-4)=='<br>') $d=mb_trim(mb_substr($d,0,-4));
	while (mb_substr($d,-6)=='<br />') $d=mb_trim(mb_substr($d,0,-6));
	
	$tr=0;
	$breakword=0;
	
	if (!(mb_strpos($d,'<div style="text-align:center;"><div id="container')===false)) $d=mb_substr ($d,0,mb_strpos($d,'<div style="text-align:center;"><div id="container'));
	if (!(mb_strpos($d,'<script')===false)) $d=mb_substr ($d,0,mb_strpos($d,'<script'));
	if (!(mb_strpos($d,'[video=')===false)) $d=mb_substr ($d,0,mb_strpos($d,'[video='));
	if (!(mb_strpos($d,'[videobottom=')===false)) $d=mb_substr ($d,0,mb_strpos($d,'[videobottom='));
	if (!(mb_strpos($d,'[rate=')===false)) $d=mb_substr ($d,0,mb_strpos($d,'[rate='));
	if (!(mb_strpos($d,'[blogs=')===false)) $d=mb_substr ($d,0,mb_strpos($d,'[blogs='));
	if (!(mb_strpos($d,'[cut')===false)) $d=mb_substr ($d,0,mb_strpos($d,'[cut'));
	if (!(mb_strpos($d,'[spoiler')===false)) $d=mb_substr ($d,0,mb_strpos($d,'[spoiler'));
	if (!(mb_strpos($d,'[img')===false)) $d=mb_substr ($d,0,mb_strpos($d,'[img'));
	//if (!(mb_strpos($d,'[image')===false)) $d=mb_substr ($d,0,mb_strpos($d,'[image'));
	if (!(mb_strpos($d,'<table')===false)) $d=mb_substr ($d,0,mb_strpos($d,'<table'));
	if (!(mb_strpos($d,'HD-качество ')===false)) $d=mb_substr ($d,0,mb_strpos($d,'HD-качество '));

	$tags='<b><i><u><br><p>';
	$d=strip_tags($d,$tags);
	
	
	if (mb_strlen($d)>$len)
	{
		if (!(mb_strpos($d,' ',$len)===false)) $d=mb_substr($d,0,mb_strpos($d,' ',$len));
		if (mb_substr_count ($d,'<b>')>mb_substr_count ($d,'</b>')) $d.='</b>';
		if (mb_substr_count ($d,'<i>')>mb_substr_count ($d,'</i>')) $d.='</i>';
		if (mb_substr_count ($d,'<u>')>mb_substr_count ($d,'</u>')) $d.='</u>';
		// $tr=1;
		$breakword=1;
	}
	
//	if ($breakword==1) $d.='...';

	if (mb_substr($d)!='.' && mb_substr($d)!='?' && mb_substr($d)!='!')
		$d.='...';

	return $d;
}

function rss ($page)
{

	$text='<?xml version="1.0" encoding="utf-8"?>
	<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>'.
    ($page=='blog'?
    '<atom:link href="http://www.kritikanstvo.ru/rss/blog.xml" rel="self" type="application/rss+xml" />
	<title>Критиканство — Блог</title>
	<link>http://www.kritikanstvo.ru/blog/</link>
	<description>Новости из мира российской кино- и игровой прессы. Подборка лучших материалов за неделю, обзоры и аналитика.</description>
    <image>
      <url>http://www.kritikanstvo.ru/i/logo.png</url>
      <link>http://www.kritikanstvo.ru/blog/</link>
      <title>Критиканство — Блог</title>
    </image>':'').
    '<lastBuildDate>'.date(DateTime::RSS).'</lastBuildDate>
    ';
    
    
    if ($page=='blog')
    {
	    $blog=q("SELECT * FROM blog ORDER BY timestamp DESC LIMIT 30");
	    while ($b=mysql_fetch_array($blog))
	    {
	    
	    	$text.='
    <item>
      <title><![CDATA['.$b['header'].']]></title>
      <link>http://www.kritikanstvo.ru/blog/'.$b['id'].($b['textlink']!=''?'-'.$b['textlink']:'').'/</link>
      <description><![CDATA['.strip_tags(preg_replace ('/\[cut=([^]]+)\]/','',preg_replace ('/\[cut\]/','',str_replace(' href="/',' href="http://www.kritikanstvo.ru/',transform_text($b['text'], $b['header'])))),'<p><a><br>').']]></description>
      <pubDate>'.date(DateTime::RSS,$b['timestamp']).'</pubDate>
      <guid>http://www.kritikanstvo.ru/blog/'.$b['id'].($b['textlink']!=''?'-'.$b['textlink']:'').'/</guid>
    </item>';
    	} 
	    
    }
    
    
    $text.='  </channel>
</rss>';

	return $text;
}

/*

<a href="#" class="prev">&nbsp;</a><a href="#">1</a><a href="#">2</a><a href="#">3</a><a href="#">4</a><a href="javascript:void(0)" class="current">5 страница</a><a href="#">6</a><a href="#">7</a><a href="#">8</a><span>&#133;</span><a href="#">24</a><a href="#" class="next">&nbsp;</a>
*/

	function pages ($page, $nr, $onpage=30, $max=20, $pageurl='/news/')
	{
		$pages=(int)(($nr-1)/$onpage)+1;
		
		$middle=ceil($max/2+0.5);
		if ($nr>$onpage)
		{
			
			$txt='
			<a href="'.$pageurl.'page/'.($page==1?1:$page-1).'/#comments" class="prev'.($page==1?' disabled':'').'">&nbsp;</a>';
			
			if ($pages<=$max)
				for ($i=1; $i<=$pages; $i++)
					$txt.=($i==$page?'<a href="javascript:void(0)" class="current">'.$i.'</a>':'<a href="'.$pageurl.'page/'.$i.'/#comments">'.$i.'</a>');
				
			else
			
			if ($page<=$max-2)
			{
				for ($i=1; $i<=$max-2; $i++)
					$txt.=($i==$page?'<a href="javascript:void(0)" class="current">'.$i.'</a>':'<a href="'.$pageurl.'page/'.$i.'/#comments">'.$i.'</a>');
				$txt.='<span>&#133;</span><a href="'.$pageurl.'page/'.$pages.'/#comments">'.$pages.'</a>';
			}		

			else
			
			if ($page>=$pages-$max+3)
			{
				$txt.='<a href="'.$pageurl.'page/'.'1/#comments">1</a><span>&#133;</span>';
				
				for ($i=$pages-$max+3; $i<=$pages; $i++)
					$txt.=($i==$page?'<a href="javascript:void(0)" class="current">'.$i.'</a>':'<a href="'.$pageurl.'page/'.$i.'/#comments">'.$i.'</a>');
			}	
			
			else
			{
				$txt.='<a href="'.$pageurl.'page/'.'1/#comments">1</a><span>&#133;</span>';
				//+$max-5
				for ($i=$page-$middle+3; $i<=$page+$middle-3; $i++)
					$txt.=($i==$page?'<a href="javascript:void(0)" class="current">'.$i.'</a>':'<a href="'.$pageurl.'page/'.$i.'/#comments">'.$i.'</a>');

				$txt.='<span>&#133;</span><a href="'.$pageurl.'page/'.$pages.'/#comments">'.$pages.'</a>';

				
			}
			
			
			$txt.='<a href="'.$pageurl.'page/'.($page==$pages?$page:$page+1).'/#comments" class="next'.($page==$pages?' disabled':'').'">&nbsp;</a>';
			
			return $txt;
				
		}

	}
	
	
    function fsize($url) {
        $sch = parse_url($url, PHP_URL_SCHEME);
        if (($sch != "http") && ($sch != "https") && ($sch != "ftp") && ($sch != "ftps")) {
            return 0;
        }
        if (($sch == "http") || ($sch == "https")) {
            $headers = get_headers($url, 1);
            if ((!array_key_exists("Content-Length", $headers))) { return 0; }
            $head=$headers["Content-Length"];
            if (is_array($head)) return $head[1];
            else return $head;
        }
        if (($sch == "ftp") || ($sch == "ftps")) {
            $server = parse_url($url, PHP_URL_HOST);
            $port = parse_url($url, PHP_URL_PORT);
            $path = parse_url($url, PHP_URL_PATH);
            $user = parse_url($url, PHP_URL_USER);
            $pass = parse_url($url, PHP_URL_PASS);
            if ((!$server) || (!$path)) { return 0; }
            if (!$port) { $port = 21; }
            if (!$user) { $user = "anonymous"; }
            if (!$pass) { $pass = "phpos@"; }
            switch ($sch) {
                case "ftp":
                    $ftpid = ftp_connect($server, $port);
                    break;
                case "ftps":
                    $ftpid = ftp_ssl_connect($server, $port);
                    break;
            }
            if (!$ftpid) { return 0; }
            $login = ftp_login($ftpid, $user, $pass);
            if (!$login) { return 0; }
            $ftpsize = ftp_size($ftpid, $path);
            ftp_close($ftpid);
            if ($ftpsize == -1) { return 0; }
            return $ftpsize;
        }
    }

?>