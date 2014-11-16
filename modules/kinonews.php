<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://www.kinonews.ru/reviews/";
	$page=file_get_contents($link);
  	$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, "div", "class=\"anons-arts\"");

	$items = SelectNodes($content_html, "h3");
	foreach ($items as $item)
	{
		$url_div = $item;
		$new_url = 'http://www.kinonews.ru'.SelectiSubs($url_div, "href=\"", "\"");

		$new_text = between(trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
", '&#8212;','&quot;'),array('', '', '', '', '—','"'),strip_tags($url_div)))),'("','")');
		
		if($new_url !== $url)
		{
			$a[$n]['url'] = $new_url;
			$a[$n]['text'] = $new_text;
			$n++;
		} else
		{
			break;
		}
	}
	@$js=json_encode($a);
	echo $js;

	@mysql_query("SET NAMES utf8", con());
	mysql_select_db('kritikanstvo');
	$sql = "UPDATE kritikanstvo.publications_revise
		SET
		  `timestamp` = ".time()."
		WHERE
		  id = ".$id;
	mysql_query($sql);
}

function between ($str, $s1, $s2='', $from=0, $safe=TRUE)
// Функция выдает ту строку, что заключена в строке $str между сроками $s1 и $s2
{
	if ($s1!='') $pos=mb_strpos($str,$s1,$from);
	else $pos=0;
	
	if ($pos===FALSE) {
		if ($safe) return ''; else return $str;
	} else
	{
		$pos+=mb_strlen($s1);
		if ($s2=='') $pos2=mb_strlen($str);
			else $pos2=mb_strpos($str,$s2,$pos);
		if ($pos2==0) $pos2=mb_strlen($str);
		if ($pos===FALSE || $pos==$pos2) return ''; else 
			return mb_substr($str,$pos,$pos2-$pos);
	}
}

?>