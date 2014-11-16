<?php
include ("../../strfunc.php");
include ("../../../../connect.php");

@$id = $_GET['id'];
@$url = $_GET['url'];
$n = 0;

if($id!= null & $url != null)
{
	$link = "http://weekend.ria.ru/cinema/";
	$page=file_get_contents($link);
  	//$page = mb_convert_encoding($page, "UTF-8", "windows-1251");
	$content_html = SelectNode($page, "div", 'class="rubric_blog"');
	
		$items = SelectNodes($page, "article");
		
		foreach ($items as $item)
		{
			$url_div = SelectNode($item, "h2");
			if ($url_div!='')
			{
				$new_url = "http://weekend.ria.ru".SelectiSubs($url_div, "href=\"", "\"");
			$new_text = trim(htmlspecialchars_decode(str_replace(array('&laquo;','&raquo;', '&nbsp;', "
", '&#8212;', '&mdash;', '&quot;', '&#8217;', '&#39;',
			''),array('', '', ' ', '', '—', '—', '"', "'", "'",
			''),strip_tags($url_div))));	
				
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

?>