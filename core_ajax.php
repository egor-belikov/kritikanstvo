<?php

define("MINREVIEWS", 7);
define("MINGRADES", 20);

mb_regex_encoding("UTF-8");
mb_internal_encoding("UTF-8");


function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function remove_dir ($dir)
{
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}


function modify_path_admin ($url)
{
	if (mb_substr($url,0,26)=='http://www.kritikanstvo.ru/')
		$url=mb_substr($url,26);
	if (mb_substr($url,0,1)=='/')
		$url='..'.$url;
	else if (mb_substr($url,0,2)!='..')
		$url='../'.$url;
	return $url;
}


function show_releases ($section, $year, $month)
{
	global $months_rus, $date_days;
	global $loginza_id;
	// $current_year=date('Y');
	$current_year=date('Y');
	$current_month=date('n');
	
	if ($section=='movies')
		$type=0;
	else
	if ($section=='games')
		$type=1;
	
	if (mb_substr($month,0,1)=='q')
	{
		$quarter=mb_substr($month,1);
		$month='';
	} else
		$quarter='';
		
	$timeoftheyear_russian=array('зима', 'весна', 'лето', 'осень');
	$timeoftheyear_english=array('winter', 'spring', 'summer', 'fall');
	
	if ($month=='winter')
	{
		$timeoftheyear=1;
		$month='';
	} else
	if ($month=='spring')
	{
		$timeoftheyear=2;
		$month='';
	} else
	if ($month=='summer')
	{
		$timeoftheyear=3;
		$month='';
	} else
	if ($month=='fall')
	{
		$timeoftheyear=4;
		$month='';
	} else
		$timeoftheyear='';
		
	if ($year=='' && $month=='')
	{
		
		$year=$current_year;
		$month=$current_month;
		$startdate=mktime(0,0,0,$month,1,$year);
		$enddate=mktime(23,59,59,$month,$date_days[$month-1],$year);
		$rq="YEAR(releasedate)=$year AND MONTH(releasedate)=$month";
	} else
	if ($year!='' && $month=='' && $quarter=='' && $timeoftheyear=='')
	{
		$startdate=mktime(0,0,0,1,1,$year);
		$enddate=mktime(23,59,59,1,31,$year);
		//$month=1;
		$rq="YEAR(releasedate)=$year";
	} else
	if ($year!='' && $quarter!='' && $month=='' && $timeoftheyear=='')
	{
		$startdate=mktime(0,0,0,1,1,$year);
		$enddate=mktime(23,59,59,1,31,$year);
		//$rq="YEAR(releasedate)=$year AND MONTH(releasedate)=".($quarter*3)." AND TIME(releasedate)='02:00:00'";
		$rq="YEAR(releasedate)=$year AND MONTH(releasedate)>=".($quarter*3-2)." AND MONTH(releasedate)<=".($quarter*3);
	} else
	if ($year!='' && $quarter=='' && $month=='' && $timeoftheyear!='')
	{
		$startdate=mktime(0,0,0,1,1,$year);
		$enddate=mktime(23,59,59,1,31,$year);
		/*
		if ($timeoftheyear==1)
			$rq="(YEAR(releasedate)=$year AND MONTH(releasedate)>=".($timeoftheyear*3-3)." AND MONTH(releasedate)<=".($timeoftheyear*3-1).") OR (YEAR(releasedate)=".($year-1)." AND MONTH(releasedate)=12))";
		else
		*/
			$rq="YEAR(releasedate)=$year AND MONTH(releasedate)>=".($timeoftheyear*3-3)." AND MONTH(releasedate)<=".($timeoftheyear*3-1);
	} else	if ($year!='' && $month!='' && $quartet=='' && $timeoftheyear=='')
	{
		$startdate=mktime(0,0,0,$month,1,$year);
		$enddate=mktime(23,59,59,$month,$date_days[$month-1],$year);
		$rq="YEAR(releasedate)=$year AND MONTH(releasedate)=$month AND (TIME(releasedate)='00:00:00' OR TIME(releasedate)='01:00:00')";
	}	
		
	// $lastthursday=strtotime("this thursday");
	$lastthursday=strtotime("last thursday");

	// AJAX
	
	
	//echo $year.'#'.$rq;
	
	
	if ($year!='full')
	{
		echo '<div class="releases_datepicker">
		<div class="years">';

		$alldates=array();
		
		if ($section=='movies')
		//	$rel=q("SELECT DISTINCT release_timestamp FROM releases_".mysql_real_escape_string($section)." ORDER BY release_timestamp ASC");
		{
			$rel=q("SELECT DISTINCT release_timestamp FROM movies ORDER BY release_timestamp ASC");
			while ($r=mysql_fetch_array($rel))
			{
				$dy=(int)date('Y',$r['release_timestamp']);
				$dm=(int)date('n',$r['release_timestamp']);
				$alldates[$dy][$dm]='1';

			}
		}
		else
		if ($section=='games')
		{
			//$rel=q("SELECT DISTINCT release_timestamp FROM gamesofgeneration ORDER BY release_timestamp ASC");
			$rel=q("SELECT DISTINCT releasedate FROM gamesofplatform WHERE japanonly=0 ORDER BY releasedate ASC, original ASC");
			while ($r=mysql_fetch_array($rel))
			{
				$dy=(int)date('Y',date_to_timestamp($r['releasedate']));
				$dh=intval(date('H',date_to_timestamp($r['releasedate'])));
				if ($dh==0 || $dh==1)
				{
					$dm=intval(date('n',date_to_timestamp($r['releasedate'])));
					$alldates[$dy][$dm]='1';
					$allquarters[$dy][ceil($dm/3)]='1';
					$alltimeoftheyear[$dy][ceil(($dm+1)/3)]='1';
				}
				else
				if ($dh==2)
				{
					$dm=intval(date('n',date_to_timestamp($r['releasedate'])));
					if ($dm==3 || $dm==6 || $dm==9 || $dm==12)
					{
						$dq=$dm/3;
						$allquarters[$dy][$dq]='1';
				
					} else
					if ($dm==2 || $dm==5 || $dm==8 || $dm==11)
					{
						$dq=($dm+1)/3;
						$alltimeoftheyear[$dy][$dq]='1';
					}

				}
			}
			
		}

	
		foreach ($alldates as $y=>$months)
		{
			if ($y>=2013)
				echo '<a href="/releases/'.$section.'/'.$y.'/" class="'.(trim($y<$current_year?'past ':'').($y==$year?'active ':'')).'" onClick="show_releases(\''.$section.'\',\''.$y.'\',\'\'); return false;"><span>'.$y.'</span></a>';
		}
	
		echo '</div><div class="months">';
	
		reset ($alldates);
		
		// echo $month;
		
		if ($alldates[$year][$month]!=1)
			for ($mm=$month; $mm<=12; $mm++)
			{
				if ($alldates[$year][$mm]==1)
				{
				
					if ($year!='' && $month=='' && $quarter=='' && $timeoftheyear=='')
						$rq="YEAR(releasedate)=$year AND MONTH(releasedate)=$mm";
					$month=$mm;
					$startdate=mktime(0,0,0,$month,1,$year);
					$enddate=mktime(23,59,59,$month,$date_days[$month-1],$year);
					break;
				}
			}

		for ($q=1; $q<=4; $q++)
		{
				
			if ($alltimeoftheyear[$year][$q]==1)
				echo '<a href="/releases/'.$section.'/'.$year.'/'.$timeoftheyear_english[$q-1].'/" class="'.trim((($year<$current_year || $year==$current_year) && $q<$current_timeoftheyear?'past ':'').($q==$timeoftheyear?'active ':'')).' quarter" onClick="show_releases(\''.$section.'\',\''.$year.'\',\''.$timeoftheyear_english[$q-1].'\'); return false;"><span>'.$timeoftheyear_russian[$q-1].'</span></a>';
			else
				echo '<span class="empty quarter">'.$timeoftheyear_russian[$q-1].'</span>';	
					if ($allquarters[$year][$q]==1)
				echo '<a href="/releases/'.$section.'/'.$year.'/q'.$q.'/" class="'.trim((($year<$current_year || $year==$current_year) && $q<$current_quarter?'past ':'').($q==$quarter?'active ':'')).' quarter" onClick="show_releases(\''.$section.'\',\''.$year.'\',\'q'.$q.'\'); return false;"><span>'.$q.' квартал</span></a>';
			else
				echo '<span class="empty quarter">'.$q.' квартал</span>';

		}
		
			echo '<br>';

		for ($m=1; $m<=12; $m++)
			if ($alldates[$year][$m]==1)
				echo '<a href="/releases/'.$section.'/'.$year.'/'.$m.'/" class="'.(($quarter!='' && $m>=$quarter*3-2 && $m<=$quarter*3) || ($timeoftheyear!='' && $m>=$timeoftheyear*3-3 && $m<=$timeoftheyear*3-1)?'subactive':trim((($year<$current_year || $year==$current_year) && $m<$current_month?'past ':'').($m==$month && $timeoftheyear=='' && $quarter==''?'active ':''))).'" onClick="show_releases(\''.$section.'\',\''.$year.'\',\''.$m.'\'); return false;"><span>'.$months_rus[$m-1].'</span></a>';
			else
				echo '<span class="'.(($quarter!='' && $m>=$quarter*3-2 && $m<=$quarter*3) || ($timeoftheyear!='' && $m>=$timeoftheyear*3-3 && $m<=$timeoftheyear*3-1)?'subempty':'empty').'">'.$months_rus[$m-1].'</span>';

	
		echo '</div>
			</div>';
		
		}
	$curtime=0;
	$curyear=0;
	$closed=TRUE;
			
	if ($section=='movies')
//		$releases=q("SELECT * FROM releases_".$section.($year!='full'?" WHERE release_timestamp>=".intval($startdate)." AND release_timestamp<=".intval($enddate):" WHERE release_timestamp>=".strtotime("this year january 1st"))." ORDER BY release_timestamp ASC, screens_ru DESC, rating DESC");
		$releases=q("SELECT * FROM movies".($year!='full'?" WHERE release_timestamp>=".intval($startdate)." AND release_timestamp<=".intval($enddate):" WHERE release_timestamp>=".strtotime("this year january 1st"))." ORDER BY release_timestamp ASC, screens_ru DESC, rating DESC");
	else
	if ($section=='games')
	{
		$releases=q("SELECT * FROM gamesofplatform".($year!='full'?" WHERE japanonly=0 AND ".$rq:" WHERE japanonly=0 AND release_timestamp>=".strtotime("this year january 1st"))." GROUP BY releasedate, original ORDER BY releasedate ASC, original ASC");
	}
	
		// $releases=q("SELECT * FROM gamesofgeneration".($year!='full'?" WHERE release_timestamp>=".intval($startdate)." AND release_timestamp<=".intval($enddate):" WHERE release_timestamp>=".strtotime("this year january 1st"))." ORDER BY release_timestamp ASC, rating DESC");
	
	while ($r=mysql_fetch_array($releases))
	{
		if ($section=='games')
			$r['release_timestamp']=date_to_timestamp($r['releasedate']);
		if ($r['release_timestamp']!=$curtime)
		{
			if (!$closed)
			{
				echo '
				</ul>
				</div>';

				$closed=true;
			}

			if ($year=='full' && $curyear!=date('Y',$r['release_timestamp']))
			{
				echo '<div class="releases_year">'.date('Y',$r['release_timestamp']).'</div>';
				$curyear=date('Y',$r['release_timestamp']);
			}
		//		<div class="releases_year">XXXX</div> или <div class="releases_year past_year">XXXX</div>
		
			echo '
			<div class="clearfix releases_day'.($r['release_timestamp']<$lastthursday?' past_day':'').'" id="week'.$r['release_timestamp'].'">
			<h4'.($r['release_timestamp']<$lastthursday?' onClick="$(\'#week'.$r['release_timestamp'].'\').toggleClass(\'past_day\');" style="cursor: pointer;"':'').'>'.rus_date($r['release_timestamp'],'').'</h4>
			<ul>';

			$curtime=$r['release_timestamp'];
			$closed=false;
		}
		
		if ($r['codename']!='')
		{
			$m=fq("SELECT * FROM ".($section=='movies'?'movies':'').($section=='games'?'gamesofgeneration':'')." WHERE codename='".mysql_real_escape_string($r['codename'])."'");
			$r['original']=$m['original'];
			$r['russian']=$m['russian'];
			$r['rating']=$m['rating'];
			$r['genres']=$m['genres'];
		} else
		{
			
			$r['rating']=0;
		}
		
		$pl='';
		
		if ($section=='games')
		{
			/*
			$plar=explode(',',$m['platforms']);
			foreach ($plar as $pa)
				$pl.='<span class="platform">'.trim($pa).'</span>';
			*/
			
			$plar=explode(',',$m['all_releases']);
			foreach ($plar as $pa)
			{
				$pa=trim($pa);
				if (before_first_str($pa,'(')==$r['releasedate'])
					$pl.='<span class="platform">'.between($pa,'(',')').'</span>';
			}

		}
		echo '<li>
				<a class="poster" href="'.($r['codename']!=''?'/'.$section.'/'.$r['codename'].'/':'javascript:void(0);').'" style="background-image: url(\''.get_image($r['codename'],$type,'poster','s').'\');">&nbsp;</a><div class="info"><h5><a href="'.($r['codename']!=''?'/'.$section.'/'.$r['codename'].'/':'javascript:void(0);').'">'.($section=='movies'?$r['russian']:'').($section=='games'?$r['original']:'').'</a></h5>'.($section=='movies' && $r['original']!=''?'<h6>'.$r['original'].'</h6>':'').($section=='games' && $r['russian']!=''?'<h6>'.$r['russian'].'</h6>':'').($r['genres']!=''?'<p>'.$r['genres'].'</p>':'').
			($pl!=''?$pl:'').
			($r['rating']!=0?'<span class="rating site_rating_'.grade_color($r['rating']).'" style=" margin-right: 5px;">'.nice_grade($r['rating']).'</span>':'').
			($r['screens_ru']>0?'<p style="display: inline;">'.$r['screens_ru'].' копи'.ending($r['screens_ru'],'я','и','й').'</p>':'').
			'</div>
				</li>';
		
	}
	
	echo '
				</ul>
				</div>';

	// AJAX


}

function steal_wos ($id, $russian='')
{
	if (mb_substr($russian,0,4)=='wos#') {
		$id=mb_substr($russian,4);
		$russian='';
	}
	$file=file_get_contents ('http://www.worldofspectrum.org/api/infoseek_select_json.cgi?id='.$id);
 	$file=mb_convert_encoding ($file, "UTF-8", "ISO-8859-1");
	$r = json_decode($file,true);

	$release_timestamp=mktime (0,0,0,1,1,$r['year']);
	
	$new_id=(int)faq("SELECT id FROM gamesofgeneration ORDER BY id DESC LIMIT 1","id")+1;

 	$platforms='ZX';
 	/*
	$file2=mb_convert_encoding (file_get_contents ('http://www.worldofspectrum.org/infoseekid.cgi?id='.$id), "UTF-8", "ISO-8859-1");
 	$other_systems=strip_tags(between($file2,'This title was also advertised for and/or published on the ','</FONT>'));
 	$other_platforms=preg_split('/(, | and )/s',$other_systems);
 	foreach ($other_platforms as $op)
 	{
	 	if ($op=='Amstrad CPC') $platforms.=', CPC';
	 	if ($op=='Commodore 64') $platforms.=', C64';
	 	if ($op=='MSX') $platforms.=', MSX';
	 	if ($op=='Atari 8-bit') $platforms.=', A800';
	 	if ($op=='BBC Micro') $platforms.=', BBC';
	 	if ($op=='Commodore Plus/4') $platforms.=', Plus4';
	 	if ($op=='SAM Coupe') $platforms.=', SAM';
	 	if ($op=='Timex') $platforms.=', Timex';
 	}
 	*/
 	
 	$genres=preg_split('/: /s',$r['type']);

 	foreach ($genres as $ge)
 	{
	 	$tg=fq("SELECT * FROM genres WHERE genre='".mysql_escape_string($ge)."'");
	 	if (!$tg)
	 		q ("INSERT INTO genres SET genre='".mysql_escape_string($ge)."'");
	 	$genre.=$ge.', ';
 	}
 	
 	if (mb_substr($genre,-2)==', ') $genre=mb_substr($genre,0,-2);
 	
	if (mb_substr($r['title'],-3)==', A')
		$r['title']='A '.mb_substr($r['title'],0,-3);
	else
	if (mb_substr($r['title'],-4)==', An')
		$r['title']='An '.mb_substr($r['title'],0,-4);
	else
	if (mb_substr($r['title'],-5)==', The')
		$r['title']='The '.mb_substr($r['title'],0,-5);
	else
	if (mb_substr($r['title'],-4)==', La')
		$r['title']='La '.mb_substr($r['title'],0,-4);
	else
	if (mb_substr($r['title'],-4)==', Le')
		$r['title']='Le '.mb_substr($r['title'],0,-4);
	else
	if (mb_substr($r['title'],-4)==', 3D')
		$r['title']='3D '.mb_substr($r['title'],0,-4);

	$r['title']=str_replace ('&amp;','&',$r['title']);
	
 	$codename=nametocode($r['title']);
 	$test=fq("SELECT * FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($codename)."'");
	if ($test)
	{
		$codename.='_8bit';
		$test=fq("SELECT * FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($codename)."'");
	}
	
	if (!$test)
	{
	//
		q ("INSERT INTO gamesofgeneration SET id=".intval($new_id).", codename='".mysql_real_escape_string($codename)."', original='".mysql_escape_string($r['title'])."', russian='".mysql_escape_string($russian)."', generation='8bit', developer='".mysql_escape_string ($r['author'])."', ukpublisher='".mysql_escape_string ($r['publisher'])."', genre='".mysql_escape_string ($genre)."', platforms='".mysql_real_escape_string($platforms)."', release_timestamp=$release_timestamp, import_url='http://www.worldofspectrum.org/infoseekid.cgi?id=$id', releasedate='".mysql_escape_string($r['year'])."-01-01'");
	//
		$ret=array ('title'=>$r['title'],'codename'=>$codename);
		echo json_encode($ret);
	}
	/*
	$downloads=$r['otherDownloads'];
	foreach ($downloads as $d)
	{
		$ext=strtolower(mb_strrchr($d['link'],'.'));
		if (($ext=='.gif' || $ext=='.jpg' || $ext=='.png') && (mb_stristr($d,' screen') || mb_stristr($d,' inlay') || mb_stristr($d,' poster') || mb_stristr($d,'advertisement')))
		{
			if (mb_stristr($d,' screen'))
				echo ("INSERT INTO images SET original='".mysql_escape_string($r['title'])."', platform='ZX', ");
			//	
			echo '<input type="text" name="wosname['.$i.']" id="wosname['.$i.']" size="50" value="'.($key==-1?$d['type']:$translate_russian[$key]).'"> <a href="'.$d['link'].'" target="_blank">Ссылка</a> <input type="radio" name="wostype['.$i.']" id="wostype_screen['.$i.']" value="images"'.($translate_types[$key]=='images'?' checked':'').'> Кадр</label> <input type="radio" name="wostype['.$i.']" id="wostype_screen['.$i.']" value="posters"'.($translate_types[$key]=='posters'?' checked':'').'><label for="wostype_screen['.$i.']">Постер</label> <input type="radio" name="wostype['.$i.']" id="wostype_screen['.$i.']" value="production"'.($key==-1 || $translate_types[$key]=='production'?' checked':'').'><label for="wostype_screen['.$i.']">Другое</label> <input type="radio" name="wostype['.$i.']" id="wostype_screen['.$i.']" value="none"><label for="wostype_screen['.$i.']">Пропустить</label> <input type="text" name="woslink['.$i.']" id="woslink['.$i.']" size="100" value="'.$d['link'].'"><br>';
			$i++;
			//
		}
	
	}
	*/

}


function short_issue ($issue)
{
	$issue=str_replace ('http://','',$issue);
	if (mb_substr($issue,0,4)=='www.') $issue=mb_substr($issue,4);
	if (mb_substr($issue,-1)=='/') $issue=mb_substr($issue,0,-1);
	if (mb_substr($issue,-11)=='index.shtml') $issue=mb_substr($issue,0,-11);
	return $issue;
}

function show_temp ($table, $r, $edit=FALSE)
{

	echo '
	Что: ';
	
	if ($edit)
	{
		echo '<form method="post">';
		
		if ($r['image']!='') echo '<img src="'.$r['image'].'" align="right" hspace="10">';
		
		echo '<textarea id="russian" rows="1" placeholder="Русское">'.$r['russian'].'</textarea><span class="dashed blue" onClick="$(\'#review'.$r['id'].'\').find(\'#russian\').val(nice_russian($(\'#review'.$r['id'].'\').find(\'#original\').val())); $(\'#review'.$r['id'].'\').find(\'#original\').val(\'\');">&larr;</span><textarea id="original" rows="1" placeholder="Оригинальное">'.$r['original'].'</textarea> <span class="dashed" onclick="$(\'#forajax'.$r['id'].'\').load(\'/admin/actions.php?action=wossearch&id='.$r['id'].'&title=\'+encodeURIComponent($(\'#review'.$r['id'].'\').find(\'#original\').val()));" id="wosbutton'.$r['id'].'">WOS</span> <textarea id="codename" rows="1" placeholder="Мнемокод">'.$r['codename'].'</textarea> <label class="radio inline"><input type="radio" name="type" id="type0" value="0"'.($r['type']==0?' checked':'').'> фильм</label> <label class="radio inline"><input type="radio" name="type" id="type1" value="1"'.($r['type']==1?' checked':'').'> игра</label>';

		echo '<span id="forajax'.$r['id'].'" style="display: block;"></span>';

		
	} else
	{
		if ($r['codename']!='')
			echo '<a href="/'.($r['type']==0?'movies':'games').'/'.$r['codename'].'/" target="_blank">'.($r['type']==0?$r['russian']:$r['original']).'</a>';
		else
		{
			$r['codename']=get_codename($r['russian'],$r['original'],$r['type']);
			if ($r['codename']!='')
			{
				echo '<a href="/'.($r['type']==0?'movies':'games').'/'.$r['codename'].'/" target="_blank">'.($r['type']==0?$r['russian']:$r['original']).'</a>';
				q ("UPDATE ".$table."_temp SET codename='".mysql_real_escape_string($r['codename'])."' WHERE id=".intval($r['id']));
			}
			else
			{
				if ($r['type']==0)
				{
					$mo=fq("SELECT * FROM movies WHERE russian='".mysql_real_escape_string($r['russian'])."' AND (codename='' OR codename IS NULL)");
					if ($mo)
						echo '<a class="red" href="/admin/movies.php?id='.$mo['id'].(mb_substr($r['issue'],0,18)=='http://hkcinema.ru'?'&steal_link='.$r['issue']:'').'" target="_blank">'.$r['russian'].'</a>';
					else
						echo '<span class="red dashed" onClick="if (confirm(\'Добавить фильм с таким названием?\')) add_movie ('.$r['id'].', \''.mysql_real_escape_string($r['russian']).'\');">'.$r['russian'].'</span>';
				}
				else
				{
					echo '<a class="red" target="_blank" href="/admin/games.php?original='.urlencode($r['original']).'">'.$r['original'].'</a>';
				}
			}
		}
		echo ($r['type']==0?' (фильм'.($r['codename']!=''?': <a href="/movies/'.$r['codename'].'/" target="_blank">'.$r['codename'].'</a> - <a href="/admin/movies.php?codename='.$r['codename'].'" target="_blank">админка</a>':'').')':' (игра'.($r['codename']!=''?': <a href="/games/'.$r['codename'].'/" target="_blank">'.$r['codename']:'').'</a> - <a href="/admin/games.php?codename='.$r['codename'].'" target="_blank">админка</a>)');
		
		if ($r['type']==1 && $r['codename']=='')
			echo ' <a href="http://www.kritikanstvo.ru/admin/REACTOR/RedactorGames.php?select=onlynew" target="_blank">Редактор</a> | <a href="http://www.kritikanstvo.ru/admin/REACTOR/index.php?parser=gamerankings&select=onlynew" target="_blank">GameRankings</a> | <span class="green dashed" onClick="$(\'#review'.$r['id'].'\').load(\'/admin/actions.php?action=show_temp&table=reviews&id='.$r['id'].'\');">Обновить</span>';
		else
		if ($r['type']==0 && $r['codename']=='')
			echo ' <span class="green dashed" onClick="$(\'#review'.$r['id'].'\').load(\'/admin/actions.php?action=show_temp&table=reviews&id='.$r['id'].'\');">Обновить</span>';


	}
		
	echo '<br>Где: ';

	if ($edit)
	{
		echo '<textarea id="publication" rows="1">'.$r['publication'].'</textarea><textarea id="publication_id" class="input-mini" rows="1">'.$r['publication_id'].'</textarea><textarea id="author" rows="1">'.$r['author'].'</textarea><textarea id="critic_id" class="input-mini" rows="1">'.$r['critic_id'].'</textarea>';
	} else
	{

		if ($r['publication_id']!=0)
			echo '<a href="/publications/'.$r['publication_id'].'/">'.$r['publication'].'</a>';
		else
		{
			$r['publication_id']=get_publication_id($r['publication']); 
			if ($r['publication_id']!=0)
			{
				echo '<a href="/publications/'.$r['publication_id'].'/">'.$r['publication'].'</a>';
				q ("UPDATE ".$table."_temp SET publication_id=".intval($r['publication_id'])." WHERE id=".intval($r['id']));
			} else
			echo '<span class="red dashed" onClick="if (confirm(\'Добавить издание с таким названием?\')) add_publication ('.$r['id'].', \''.$r['publication'].'\');">'.$r['publication'].'</span>';
		}
		echo ' / ';
		
		if ($r['critic_id']!=0 && $r['critic_id']!='')
			echo get_critics_names($r['critic_id'],$r['author'],$r['id']);
		else
		{
			$r['critic_id']=get_critic_id($r['author']); 
			if ($r['critic_id']!='')
			{
				echo get_critics_names($r['critic_id'],$r['author'],$r['id']);
				q ("UPDATE ".$table."_temp SET critic_id='".$r['critic_id']."' WHERE id=".intval($r['id']));
			}
			else
				echo '<span class="red dashed" onClick="if (confirm(\'Добавить критика с таким именем?\')) add_critic ('.$r['id'].', \''.$r['author'].'\');">'.$r['author'].'</span>';		
		}
	}


	if ($r['issue']!='http://' && $r['issue']!='')
	{
		if (mb_strpos($r['issue'],'#')===FALSE && mb_strpos($r['issue'],'http://')!==FALSE)
		{
			echo ' (<a href="'.$r['issue'].'" target="_blank">'.$r['issue'].'</a>)';
			$rr=fq("SELECT id FROM reviews WHERE issue LIKE '%".mysql_real_escape_string(short_issue($r['issue']))."%'");
			if ($rr) {
				echo ' <a href="/reviews/'.$rr['id'].'/" class="green" target="_blank">Уже есть в базе</a>';
				if ($table=='wisdom' && intval($r['review_id'])==0)
				{
					$r['review_id']=$rr['id'];
					q ("UPDATE wisdom_temp SET review_id=".intval($r['review_id'])." WHERE id=".intval($r['id']));
				}
			}
		}
		else
		{
			echo ' ('.$r['issue'].')';
			$rr=fq("SELECT id FROM reviews WHERE issue='".mysql_real_escape_string(trim($r['issue']))."' AND ".($r['original']!=''?"original='".mysql_real_escape_string($r['original'])."'":"").($r['russian']!=''?"russian='".mysql_real_escape_string($r['russian'])."'":"").($r['author']!=''?" AND author='".mysql_real_escape_string($r['author'])."'":"").($r['publication']!=''?" AND publication='".mysql_real_escape_string($r['publication'])."'":""));
			if ($rr) {
				echo ' <a href="/reviews/'.$rr['id'].'/" class="green" target="_blank">Уже есть в базе</a>';
				if ($table=='wisdom' && intval($r['review_id'])==0)
				{
					$r['review_id']=$rr['id'];
					q ("UPDATE wisdom_temp SET review_id=".intval($r['review_id'])." WHERE id=".intval($r['id']));
				}
			}
		}
	}
	
	echo '<br>Когда: ';

	if ($edit)
	{
		echo '<textarea id="date" rows="1" class="span2">'.date('d.m.Y',$r['review_timestamp']).'</textarea> <textarea id="issue" rows="1" class="span6">'.$r['issue'].'</textarea> <textarea id="review_id" class="input-mini" rows="1">'.$r['review_id'].'</textarea>';		
	} else
	{
//		if (mb_substr($r['issue'],0,7)=='http://')
//		{
			if ($r['review_timestamp']!=0)
				echo date('d.m.Y',$r['review_timestamp']);
			else
				echo '<span class="red">без даты</span>';
			
//		} else
//		{
//			if ($r['issue']!='')
//				echo $r['issue'].' <span class="lightgrey">('.date('d.m.Y',$r['review_timestamp']).')</span>';
//		}
	}
	
	if ($table=='reviews')
	{

		echo '<br>Как: ';
	
		if ($r['publication_id']!=0)
			$r['rating']=get_rating($r['grade'],$r['publication_id'],date('Y',$r['review_timstamp']));
			
		if ($edit)
		{
			echo '<textarea id="grade" rows="1" class="span3">'.$r['grade'].'</textarea> '.($r['rating']!=0?$r['rating'].'%':'<span class="red">рейтинг не подсчитан</span>');
			echo ' <span class="dashed blue" onClick="$(\'#review'.$r['id'].'\').find(\'#grade\').val(\'#отлично\');">отлично</span> <span class="dashed blue" onClick="$(\'#review'.$r['id'].'\').find(\'#grade\').val(\'#хорошо\');">хорошо</span> <span class="dashed blue" onClick="$(\'#review'.$r['id'].'\').find(\'#grade\').val(\'#так себе\');">так себе</span> <span class="dashed blue" onClick="$(\'#review'.$r['id'].'\').find(\'#grade\').val(\'#плохо\');">плохо</span>';		
		} else
		{
			if ($r['grade']!='')
				echo $r['grade'].' ('.($r['rating']!=0?$r['rating'].'%':'<span class="red">рейтинг не подсчитан</span>').')';
			else
				echo '<span class="red">нет оценки</span>';
		}	
			
		if ($edit)
		{
			echo '<br><textarea id="summary" rows="3" class="span9" style="margin-left: 29px;">'.$r['summary'].'</textarea>';		
		} else
		{	
			if ($r['summary']!='')
				echo '<div style="margin-left: 29px;">'.$r['summary'].'</div>';
			else 
				echo '<div class="red" style="margin-left: 29px;">нет итога</div>';
		}
	}

	if ($table=='wisdom')
	{
	
		echo '<br>WTF: ';

		if ($edit)
		{
			echo '<br><textarea id="text" rows="3" class="span9" style="margin-left: 29px;">'.$r['text'].'</textarea>';		
		} else
		{		
			if ($r['text']!='')
				echo '<div style="margin-left: 29px;">'.$r['text'].'</div>';
			else 
				echo '<div class="red" style="margin-left: 29px;">нет «мудрости»</div>';
			
		}
			echo '<br>Почему: ';
			
		if ($edit)
		{
			echo '<br><textarea id="truth" rows="3" class="span9" style="margin-left: 29px;">'.$r['truth'].'</textarea>';		
		} else
		{		
	
			if ($r['truth']!='')
				echo '<div style="margin-left: 29px;">'.$r['truth'].'</div>';
			else 
				echo '<div class="red" style="margin-left: 29px;">нет объяснения</div>';

		}
		
		echo '<input type="hidden" id="timestamp" name="timestamp" value="'.$r['timestamp'].'">';
	}
	
	
	if ($edit)
	{
		echo '<br><span class="green dashed" style="margin: 0 5px;" onClick="save ('.$r['id'].');" id="savebutton'.$r['id'].'">Сохранить</span>';
		echo '</form>';

	} else
	{
		echo '<br>'.($r['codename']!='' && $r['publication_id']!=0 && ($r['review_id']!=0 || $table=='reviews') && ($r['critic_id']!=0 || $r['author']=='')?'<span class="green dashed" style="margin: 0 5px;" id="approve'.$r['id'].'" onClick="good ('.$r['id'].');">Одобрить</span>':'').'<span class="blue dashed" style="margin: 0 5px;" onClick="edit ('.$r['id'].');'.(($r['publication_id']==175 || $r['publication_id']==174) && $r['codename']==''?'setTimeout(function() { $(\'#wosbutton'.$r['id'].'\').click(); }, 50);':'').'">Редактировать</span> <span class="red dashed" style="margin: 0 5px;" onClick="if (confirm(\'Удалить эту '.($table=='reviews'?'рецензию':'').($table=='wisdom'?'мудрость':'').'?\')) delete_object ('.$r['id'].');">Удалить</span>';
	}
}

function update_critic ($id)
{
	$wis=faq("SELECT COUNT(id) AS cnt FROM wisdom WHERE critic_id LIKE '% ".intval($id)." %' AND codename!='' AND review_timestamp!=0 AND review_id!=0","cnt");
	$pubs=q("SELECT * FROM reviews WHERE critic_id LIKE '% ".intval($id)." %' AND codename!=''");
	$pub=mysql_num_rows($pubs);
	
	q ("SELECT * FROM reviews WHERE critic_id LIKE '% ".intval($id)." %' AND codename!=''");
	
	$sum=0;
	$temp_cnt=0;	
	
	while ($pp=mysql_fetch_array($pubs))
		if ($pp['grade']!='')
		{
			$sum+=get_rating($pp['grade'],$pp['publication_id'],date('Y',$pp['review_timstamp']));
			$temp_cnt++;
		}
		
	$average_grade=$sum/$temp_cnt;
	
	if ($average_grade=='') $average_grade=0;
	
	$ww=q("SELECT publication_id, COUNT(*) as reviews_count FROM reviews WHERE critic_id LIKE '% ".intval($id)." %' AND review_timestamp!=0 AND codename!='' GROUP BY publication_id");
	
	//
	$works=q ("SELECT publication_id, MIN(review_timestamp) as begin, MAX(review_timestamp) as end, COUNT(*) as reviews_count FROM reviews WHERE critic_id LIKE '% ".intval($id)." %' AND review_timestamp!=0 AND codename!='' GROUP BY publication_id ORDER BY end DESC");
	/* Удалил date!='' AND и всё заработало */
		
	$worktext='';
	
	while ($w=mysql_fetch_array($works))
	{
		$worktext.=$w['publication_id'].'#'.$w['begin'].'#'.$w['end'].'#'.$w['reviews_count'].'<->';
	}

	if (mb_substr($worktext,-3)=='<->')
		$worktext=mb_substr($worktext,0,-3);

	q ("UPDATE critics SET reviews_count=".intval($pub).", wisdom_count=".intval($wis).", average_grade=".mysql_real_escape_string($average_grade).",  work_places='".mysql_real_escape_string($worktext)."' WHERE id=".intval($id));

	return mysql_affected_rows ();
}

function update_publication ($id)
{
	$revcnt=(int)faq("SELECT COUNT(id) AS cnt FROM reviews WHERE publication_id=".intval($id),"cnt");
	$wiscnt=(int)faq("SELECT COUNT(id) AS cnt FROM wisdom WHERE publication_id=".intval($id)." AND review_id!=0","cnt");
	// $critcnt=(int)faq("SELECT COUNT(DISTINCT critic_id) AS cnt FROM reviews WHERE publication_id=$id","cnt");

	$sum=0;
	$temp_cnt=0;

	$pubs=q("SELECT * FROM reviews WHERE publication_id=".intval($id));
	while ($pp=mysql_fetch_array($pubs))
	if ($pp['grade']!='')
	{
		$sum+=get_rating($pp['grade'],$id,date('Y',$pp['review_timstamp']));
		$temp_cnt++;
	}

	$average_grade=$sum/$temp_cnt;

	$unicrit=q("SELECT critic_id, COUNT(*) as cnt FROM reviews WHERE publication_id=".intval($id)." GROUP BY critic_id ORDER BY cnt DESC");
	
	$critics=array();
	$count=array();
	$i=0;
	
	while ($uc=mysql_fetch_array($unicrit))
	{
		$uca=explode(' ',trim($uc['critic_id']));
		foreach ($uca as $c)
		{
			$c=(int)$c;
			if (in_array($c,$critics))
			{
				$pos=array_search ($c,$critics);
				$count[$pos]+=$uc['cnt'];
			} else
			{
				$critics[$i]=$c;
				$count[$i]=$uc['cnt'];
				$i++;
			}
		}
	}

	$cri=array();
	
	foreach ($critics as $k=>$cr)
	{
		$cri[$k]['critic_id']=$cr;
		$cri[$k]['reviews_count']=$count[$k];
	}
	
	usort($cri, function($a, $b)
	{
	    if ($a['reviews_count'] == $b['reviews_count'])
	    	return 0;
	    else if ($a['reviews_count'] > $b['reviews_count'])
	        return -1;
	    else
	        return 1;
	});
	
	$critics_list='';
	
	foreach ($cri as $k=>$cr)
	{
		$critics_list.=$cr['critic_id'].'#'.$cr['reviews_count'].'<->';
	}
	
	$critcnt=sizeof($cri);

	$critics_list=remove_tail($critics_list,'<->');

	q ("UPDATE publications SET reviews_count=".intval($revcnt).", wisdom_count=".intval($wiscnt).", critics_count=".intval($critcnt).", average_grade=".mysql_real_escape_string($average_grade).", critics_list='".mysql_real_escape_string($critics_list)."' WHERE id=".intval($id));
	
	return mysql_affected_rows ();
}


function steal_hkcinema ($url)
{

	$text=mb_convert_encoding (file_get_contents($url), "UTF-8", "Windows-1251");

	$a['link']=$url;

	$a['russian']=strip_tags(trim(betweens($text,array('<h1','</h1>','class="section-titl">',''))));
	$a['original']=strip_tags(trim(betweens($text,array('<h2','</h2>','class="section-titl-eng">',''))));


	if (mb_substr($a['original'],-5)==', The')
		$a['original']='The '.mb_substr($a['original'],0,-5);
	if (mb_substr($a['original'],-3)==', A')
		$a['original']='A '.mb_substr($a['original'],0,-3);
	if (mb_substr($a['original'],-4)==', An')
		$a['original']='An '.mb_substr($a['original'],0,-4);


		
		$a['releasedate']=trim(strip_tags(betweens($text,array('Год выхода:','</b>','<b>'))));
		$a['country']=trim(strip_tags(betweens($text,array('Страна:','<dt>'))));
		$a['time']=trim(strip_tags(betweens($text,array('Длительность:','<dt>'))));
		$a['genre']=trim(strip_tags(betweens($text,array('Жанры:','</dl>'))));
		$a['director']=trim(strip_tags(betweens($text,array('режиссер:','</nobr>'))));
		$a['operator']=trim(strip_tags(betweens($text,array('оператор:','</nobr>'))));
		$a['painter']=trim(strip_tags(betweens($text,array('художник:','</nobr>'))));
		$a['fight_choreographer']=trim(strip_tags(betweens($text,array('постановщик экшена:','</nobr>'))));
		if ($a['fight_choreographer']=='')
			$a['fight_choreographer']=trim(strip_tags(betweens($text,array('постановка боев:','</nobr>'))));
		$a['screenwriter']=trim(strip_tags(betweens($text,array('сценарист:','</nobr>'))));
		$a['composer']=trim(strip_tags(betweens($text,array('композитор:','</nobr>'))));		
		$a['description']=trim(strip_tags(betweens($text,array('<div class="sec-name">Сюжет:','<div'))));

		// $a['actors']=remove_tail(trim(preg_replace ("/([ \n\r\t]+)/si",' ',strip_tags(preg_replace(array('/<div.+>.+<\/div>/siU','/<b.+>.+<\/b>/siU'),'',str_replace(array('/></a></div>','</a></div>'),array('/>',', '),betweens($text,array('<div class="sec-name">актеры:','<div class="film-text">'))))))),',');
		$a['actors']='';
		$act=SelectNodes(betweens($text,array('<div class="sec-name">актеры:','<div class="film-text">')),'div','film-actor-list');
		foreach ($act as $ac)
		{
			$ac=mb_trim(strip_tags(between(SelectNode(RemoveNodes(RemoveNodes($ac,'div','display'),'b'),'a'),'>','<')));
			$a['actors'].=$ac.', ';
			
		}
		
		$a['actors']=remove_tail($a['actors'],', ');
					
		$codename=nametocode($a['original']);
		$test=fq("SELECT * FROM movies WHERE codename='$codename'");
		if ($test)
		{
			if ($a['releasedate']!='')
			{
				$codename.=(int)$a['releasedate'];
				$test=fq("SELECT * FROM movies WHERE codename='$codename'");
			}
			if ($test)
			{
				$tt=fq("SELECT * FROM movies WHERE codename LIKE '".$codename."\_%'");
				if (!$tt) $add=1;
				else $add=(int)after_last_str($tt['codename'],'_');
				$codename.='_'.$add;
			}
		}
		
		$a['codename']=$codename;

		$a['release_timestamp']=mktime(0,0,0,1,1,$a['releasedate']);
		
		$a['extra_link']=trim(strip_tags(betweens($text,array('http://hkmdb.com/db/',"'"))));
		if ($a['extra_link']!='')
		{
			$a['extra_link']='http://hkmdb.com/db/'.$a['extra_link'];
			$hkdb=mb_convert_encoding (file_get_contents($a['extra_link']), "UTF-8");
			$a['poster']=between($hkdb,'<IMG SRC="/db/images/movies/','-t.jpg');
			if (mb_strlen($a['poster'])>100)
				$a['poster']='';
			if ($a['poster']!='')
				$a['poster']='http://hkmdb.com/db/images/movies/'.$a['poster'].'-b.jpg';
				
			$a['worldpremier']=trim(strip_tags(between($hkdb,'Release Date: ','<')));
			if ($a['worldpremier']=='')
				$a['worldpremier']=trim(strip_tags(betweens($hkdb, array('Theatrical Run: ','<','',' - '))));
			if ($a['worldpremier']!='')
			{
				$a['release_timestamp']=date_to_timestamp($a['worldpremier']);
				$a['worldpremier']=date('Y-m-d',$a['release_timestamp']);
			}
			else
				$a['release_timestamp']=mktime(0,0,0,1,1,$a['releasedate']);
		}
		
		if ($a['poster']=='')
		{
			$a['poster']=betweens($text,array('<div class="film-foto">','</div>','<IMG SRC="','"'));
			if ($a['poster']!='')
				$a['poster']='http://www.hkcinema.ru'.$a['poster'];		
		}
		
		return json_encode($a);

}


function make_codename ($type, $russian, $original, $year='')
{
	if ($original!='')
		$codename=nametocode($original);
	else
		$codename=nametocode($russian);
		
	$test=fq("SELECT * FROM ".($type==0?"movies":"").($type==1?"gamesofgeneration":"")." WHERE codename='".mysql_real_escape_string($codename)."'");
	if ($test)
	{
		
		if ($year!='')
		{
			$codename.=intval($year);
			$test=fq("SELECT * FROM ".($type==0?"movies":"").($type==1?"gamesofgeneration":"")." WHERE codename='$codename'");
		}
		if ($test)
		{
		
			$tt=fq("SELECT * FROM ".($type==0?"movies":"").($type==1?"gamesofgeneration":"")." WHERE codename LIKE '".$codename."\_%'");
			if (!$tt) $add=1;
			else $add=intval(after_last_str($tt['codename'],'_'))+1;
			$codename.='_'.$add;
		
		}
		
	}
	
	return $codename;

}


function update_object ($codename, $type)
{
	$allrating=get_grade_new($codename,$type);
			
	$wisdom_count=faq("SELECT COUNT(*) AS count FROM wisdom WHERE codename='".mysql_real_escape_string($codename)."' AND type=".intval($type),"count");
	
	$add="";
	
	$m=fq("SELECT * FROM ".($type==0?"movies":"gamesofgeneration")." WHERE codename='".mysql_real_escape_string($codename)."'");
	
	// +++ Страны
	
	/*
	if ($type==0)
	{
		// $country=
		$plid='';
		$plex=explode(',',trim($m['country']));
		foreach ($plex as $ex)
		{
			$ex=trim($ex);
			$ge=fq("SELECT * FROM countries WHERE name='$ex'");
			$plid.=$ge['id'].' ';
		}
		if ($plid!='')
			$plid=' '.$plid;
	
		$add.="country_id='$plid', ";
	
	}
	*/
	// --- Страны

	q ("UPDATE ".($type==0?"movies":"gamesofgeneration")." SET ".$add."rating=".mysql_real_escape_string($allrating['rating']).", bayes=".mysql_real_escape_string($allrating['bayes']).", reviews_count=".intval($allrating['reviews_count']).", wisdom_count=".intval($wisdom_count)." WHERE codename='".mysql_real_escape_string($codename)."'");
	
	return mysql_affected_rows ();
}

function update_all ($review_id)
{
	$r=fq("SELECT * FROM reviews WHERE id=".intval($review_id));
	update_review ($review_id);
	update_publication ($r['publication_id']);
	$critics=explode(' ',trim($r['critic_id']));
	foreach ($critics as $c)
		update_critic($c);
	if ($r['codename']!='')
		update_object ($r['codename'],$r['type']);
}

function update_review ($id)
{
	$rating=0;
	$rev=fq("SELECT critic_id, publication_id FROM reviews WHERE id=".intval($id));
	$critics=explode(' ',trim($rev['critic_id']));
	foreach ($critics as $c)
	{
		$c=(int)$c;
		if ($c!='' && $c!=0)
			$rat=faq("SELECT readers_bayes FROM critics WHERE id=".intval($c),"readers_bayes");
		else
			$rat=0;
		if ($rat>$rating)
			$rating=$rat;
	}
	if ($rev['publication_id']!='')
		$pubrating=faq("SELECT readers_bayes FROM publications WHERE id=".intval($rev['publication_id'])."","readers_bayes");
	else
		$pubrating=0;

	q("UPDATE reviews SET critic_rating=".mysql_real_escape_string($rating).", publication_rating=".mysql_real_escape_string($pubrating)." WHERE id=".intval($id));
	
	return mysql_affected_rows ();
}
	
function get_genres ($type, $genre)
{
	$plid='';
	$genres='';
	$plex=explode(',',trim($genre));
	foreach ($plex as $ex)
	{
		$ex=trim($ex);
		if ($type==0)
		{
			$ge=fq("SELECT * FROM genres_movies WHERE kinoteatr='".mysql_real_escape_string($ex)."'");
			if ($ge)
			{
				$plid.=$ge['id'].' ';
				$genres.=$ge['normal'].', ';
			}
		} else
		if ($type==1)
		{
			$ge=fq("SELECT * FROM genres WHERE genre='".mysql_real_escape_string($ex)."'");
			if ($ge)
			{
				$plid.=$ge['id'].' ';
				$genres.=$ge['display'].', ';
			}
		}
	}
	if ($plid!='')
		$plid=' '.$plid;
	$genres=remove_tail ($genres, ', ');
	return array ('genres_id'=>$plid, 'genres'=>$genres);
}


function change_ending ($str, $type)
{

	if ($type==0)
	{
		$from=array ('-1', '-2', '-3', '-4', '-5', '-6', '-7', '-8', '-9', '-10', '-11', '-12', '-13', '-14', '-15', '-16', ' - 1', ' - 2', ' - 3', ' - 4', ' - 5', ' - 6', ' - 7', ' - 8', ' - 9', ' - 10', ' - 11', ' - 12', ' - 13', ' - 14', ' - 15', ' - 16');
		$to=array (' 1', ' 2', ' 3', ' 4', ' 5', ' 6', ' 7', ' 8', ' 9', ' 10', ' 11', ' 12', ' 13', ' 14', ' 15', ' 16', ' 1', ' 2', ' 3', ' 4', ' 5', ' 6', ' 7', ' 8', ' 9', ' 10', ' 11', ' 12', ' 13', ' 14', ' 15', ' 16');
	} else
	if ($type==1)
	{
		$from=array (' I', ' II', ' III', ' IV', ' V', ' VI', ' VII', ' VIII', ' IX', ' X', ' XI', ' XII', ' XIII', ' XIV', ' XV', ' XVI');
		$to=array (' 1', ' 2', ' 3', ' 4', ' 5', ' 6', ' 7', ' 8', ' 9', ' 10', ' 11', ' 12', ' 13', ' 14', ' 15', ' 16');
	}
	
	foreach ($from as $k=>$f)
		if (mb_substr($str,-mb_strlen($f))==$f)
			return mb_substr($str,0,-mb_strlen($f)).$to[$k];
		else
		if (mb_substr($str,-mb_strlen($to[$k]))==$to[$k])
			return mb_substr($str,0,-mb_strlen($to[$k])).$f;

	return $str;		
	
}
	

function get_codename ($russian, $original, $type)
{
	if ($type==0)
	{
		if ($russian!='' && $original!='')
		{
			$codename=faq("SELECT codename FROM movies WHERE (russian='".mysql_real_escape_string(trim($russian))."' OR russian='".mysql_real_escape_string(trim($russian))." в 3D' OR russian='".mysql_real_escape_string(trim($russian))." 3D' OR russian='".mysql_real_escape_string(trim(change_ending($russian,$type)))."') AND (original='".mysql_real_escape_string(trim($original))."' OR original='".mysql_real_escape_string(trim(change_ending($original,$type)))."') ORDER BY release_timestamp DESC","codename");
			if ($codename=='')
			{
				$codename=faq("SELECT codename FROM movies WHERE russian='".mysql_real_escape_string(trim($russian))."' OR russian='".mysql_real_escape_string(trim($russian))." в 3D' OR russian='".mysql_real_escape_string(trim($russian))." 3D' OR russian='".mysql_real_escape_string(trim(change_ending($russian,$type)))."' ORDER BY release_timestamp DESC","codename");
			}
			if ($codename=='')
			{
				$codename=faq("SELECT codename FROM movies WHERE original='".mysql_real_escape_string(trim($original))."' OR original='".mysql_real_escape_string(trim(change_ending($original,$type)))."' ORDER BY release_timestamp DESC","codename");
			}

		} else
		{
			if ($russian!='')
				$codename=faq("SELECT codename FROM movies WHERE (russian='".mysql_real_escape_string(trim($russian))."' OR russian='".mysql_real_escape_string(trim($russian))." в 3D' OR russian='".mysql_real_escape_string(trim($russian))." 3D' OR russian='".mysql_real_escape_string(trim(change_ending($russian,$type)))."') ORDER BY release_timestamp DESC","codename");
			if ($codename=='' && $original!='')
				$codename=faq("SELECT codename FROM movies WHERE original='".mysql_real_escape_string(trim($original))."' OR original='".mysql_real_escape_string(trim(change_ending($original,$type)))."' ORDER BY release_timestamp DESC","codename");
		}
	}
	else
	if ($type==1)
	{
		if ($russian!='' && $original!='')
		{
			$codename=faq("SELECT codename FROM gamesofgeneration WHERE ((russian='".mysql_real_escape_string(trim($russian))."' OR russian='".mysql_real_escape_string(trim(change_ending($russian,$type)))."') AND (original='".mysql_real_escape_string(trim($original))."' OR original='".mysql_real_escape_string(trim(change_ending($original,$type)))."')) ORDER BY release_timestamp DESC","codename");
			if ($codename=='')
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (original='".mysql_real_escape_string(trim($original))."' OR original='".mysql_real_escape_string(trim(change_ending($original,$type)))."') ORDER BY release_timestamp DESC","codename");
			if ($codename=='')
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (gamerankings='".mysql_real_escape_string(trim($original))."' OR gamerankings='".mysql_real_escape_string(trim(change_ending($original,$type)))."' ) AND gamerankings!='' ORDER BY release_timestamp DESC","codename");
			if ($codename=='')
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (vgchartz='".mysql_real_escape_string(trim($original))."' OR vgchartz='".mysql_real_escape_string(trim(change_ending($original,$type)))."' ) AND vgchartz!='' ORDER BY release_timestamp DESC","codename");
			if ($codename=='')
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (aka='".mysql_real_escape_string(trim($original))."' OR aka='".mysql_real_escape_string(trim(change_ending($original,$type)))."' ) AND aka!='' ORDER BY release_timestamp DESC","codename");
			if ($codename=='')
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (russian='".mysql_real_escape_string(trim($russian))."' OR russian='".mysql_real_escape_string(trim(change_ending($russian,$type)))."') ORDER BY release_timestamp DESC","codename");
		} else
		{		
			if ($original!='')
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (original='".mysql_real_escape_string(trim($original))."' OR original='".mysql_real_escape_string(trim(change_ending($original,$type)))."') ORDER BY release_timestamp DESC","codename");
			if ($original!='' && $codename=='')
			{
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (aka='".mysql_real_escape_string(trim($original))."' OR aka='".mysql_real_escape_string(trim(change_ending($original,$type)))."') AND aka!='' ORDER BY release_timestamp DESC","codename");
			}
			if ($original!='' && $codename=='')
			{
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (gamerankings='".mysql_real_escape_string(trim($original))."' OR gamerankings='".mysql_real_escape_string(trim(change_ending($original,$type)))."') AND gamerankings!='' ORDER BY release_timestamp DESC","codename");
			}
			if ($original!='' && $codename=='')
			{
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (vgchartz='".mysql_real_escape_string(trim($original))."' OR vgchartz='".mysql_real_escape_string(trim(change_ending($original,$type)))."') AND vgchartz!='' ORDER BY release_timestamp DESC","codename");
			}
			if ($codename=='' && $russian!='')
				$codename=faq("SELECT codename FROM gamesofgeneration WHERE (russian='".mysql_real_escape_string(trim($russian))."' OR russian='".mysql_real_escape_string(trim(change_ending($russian,$type)))."') ORDER BY release_timestamp DESC","codename");

		}
	}
		
	return $codename;
}
	
function short_summary ($summary, $length=250)
{
	$summary=str_replace('|','',strip_tags($summary));
	if ($length>0)
	{
		if (mb_strlen($summary)>$length)
		{
			$summary=mb_substr($summary,0,$length);
			$summary=trim(mb_strrchr ($summary,' ',true));
			if (mb_substr($summary,-1)==',' || mb_substr($summary,-1)==';' || mb_substr($summary,-1)=='-' || mb_substr($summary,-1)=='—' || mb_substr($summary,-1)=='…')
				$summary=trim(mb_substr($summary,0,-1));
			if (mb_substr($summary,-1)!='.' && mb_substr($summary,-1)!='!' && mb_substr($summary,-1)!='?')
				$summary.='...';
		}
	}
	$summary=preg_replace ('/\s{2,}/',' ',$summary);
	return $summary;
}

function get_critics_names ($id, $names='', $admin=FALSE)
{
	$critics=explode(' ',trim($id));
	$names_array=explode(',',trim($names));
	foreach ($critics as $key=>$critid)
	{
		$crsql=fq("SELECT * FROM critics WHERE id=".intval($critid));
		
		if ($names!='')
			$old_name=($admin===FALSE?$names_array[$key]:'<span class="red dashed" onClick="if (confirm(\'Добавить критика с таким именем?\')) add_critic ('.$admin.', \''.$names_array[$key].'\');">'.$names_array[$key].'</span>');
		else
			$old_name='Редакция';

		if ($crsql)
		{
			$first_column.=($crsql['status']!=0?'<a href="/critics/'.(int)$critid.'/">'.($crsql['name']!=''?$crsql['name']:$old_name).'</a>':($crsql['name']!=''?$crsql['name']:$old_name)).', ';
		}
		else $first_column.=$old_name.', ';
			
	}
	if (mb_substr($first_column,-2)==', ') $first_column=mb_substr($first_column,0,-2);	
	return $first_column;
		

}

function get_publication_id ($name)
{
	$pub=faq ("SELECT id FROM publications WHERE name='".mysql_real_escape_string($name)."' OR aka='".mysql_real_escape_string($name)."'","id");
	return (int)$pub;

}

function get_critic_id ($names)
{
	
	$critic_id='';
	
	$status=1;
	if ($names!='')
	{
		if (mb_substr($names,0,1)=='_')
		{
			$status=0;
			$names=mb_substr($names,1);
		}
		
		if (mb_strpos($names,',')!==FALSE)
		{
			$aus=explode(',',$names);
			$aid='';
			foreach ($aus as $a)
			{	
				$a=trim($a);
				$name=fq ("SELECT * FROM critics WHERE name='".mysql_real_escape_string($a)."' OR aka='".mysql_real_escape_string($a)."' OR aka LIKE '".mysql_real_escape_string($a)."#%' OR aka LIKE '%#".mysql_real_escape_string($a)."#%' OR aka LIKE '%#".mysql_real_escape_string($a)."'");
				if ($name)
					$aid.=$name['id'].' ';
				else
					$aid.='0 ';
			}
			$critic_id=$aid;
			// q ("UPDATE reviews SET critic_id=' ".trim($aid)." ' WHERE id=$r[id]");
			
		} else
		{	
			$a=trim($names);
			$name=fq ("SELECT * FROM critics WHERE name='".mysql_real_escape_string($a)."' OR aka='".mysql_real_escape_string($a)."' OR aka LIKE '".mysql_real_escape_string($a)."#%' OR aka LIKE '%#".mysql_real_escape_string($a)."#%' OR aka LIKE '%#".mysql_real_escape_string($a)."'");
			if ($name)
				$critic_id=$name['id'];
				//q ("UPDATE reviews SET critic_id=' ".$name['id']." ' WHERE id=$r[id]");
		}

	}
	
	$critic_id=mb_trim($critic_id);
	if ($critic_id!='') $critic_id=' '.$critic_id.' ';
	return $critic_id;
}
			
function get_image ($codename, $type, $imagetype='poster', $st='s')
{
	if (empty($type)) {
		$m = fq("SELECT * FROM movies WHERE codename='".mysql_real_escape_string($codename)."'");
		if (!empty($m[$imagetype . 's'])) {
		/*
			if ($m['main_' . $imagetype] == 0) {
				$m['main_' . $imagetype] = $m[$imagetype . 's'];
			}
		*/	
			//  movie_id=$m[id] AND local_image!='' AND type='poster' and downloaded=1 and disabled=0
			
			$pp = fq("SELECT * FROM movies_images WHERE movie_id=".intval($m['id'])." AND id=".intval($m['main_'.$imagetype])." AND local_image!='' AND type='".mysql_real_escape_string($imagetype)."' and downloaded=1 and disabled=0");
			
			if ($pp) {
				$poster = !empty($st)
					? preg_replace('/\.([a-z]+)$/', $st.'.$1', $pp['local_image'])
					: $pp['local_image'];
			}
		}
	}
	else {
		$m = fq("SELECT * FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($codename)."'");
		if (!empty($m[$imagetype . 's']) && !empty($m['main_' . $imagetype])) {
			$pp = fq("SELECT * FROM games_images WHERE id=" . intval($m['main_' . $imagetype]));
			if ($pp) {
				$poster = !empty($st)
					? preg_replace('/\.([a-z]+)$/', $st.'.$1', $pp['local_image'])
					: $pp['local_image'];
			}
		}
	}
	/*
	if ($type==0)
		$m=fq("SELECT * FROM movies WHERE codename='".mysql_real_escape_string($codename)."'");
	else
		$m=fq("SELECT * FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($codename)."'");

	if ($m[$imagetype.'s']>0)
	{
		if ($m['main_'.$imagetype]==0)
			$m['main_'.$imagetype]=$m[$imagetype.'s'];
		$pp=fq ("SELECT * FROM ".($type==0?"movie":"").mysql_real_escape_string($imagetype)."s WHERE id=".intval($m['id'])." AND number=".intval($m['main_'.$imagetype]));

		$poster='/'.($type==1 && $imagetype=='image'?$pp['locallink']:$pp['localimage']).$st.'.jpg';
	} else $poster='/i/nocover.png';
	*/
	return !empty($poster)
		? $poster
		: '/i/nocover.png';
}

function get_image_new ($codename, $type, $imagetype='poster', $st='s')
{
	if ($type==0)
		$m=fq("SELECT * FROM movies WHERE codename='".mysql_real_escape_string($codename)."'");
	else
		$m=fq("SELECT * FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($codename)."'");

	if ($m[$imagetype.'s']>0)
	{
		if ($m['main_'.$imagetype]==0) 
			$m['main_'.$imagetype]=$m[$imagetype.'s'];

		if ($type==0 || ($type==1 && $imagetype=='poster'))
		{
			$pp=fq ("SELECT * FROM ".($type==0?"movie":"").mysql_real_escape_string($imagetype)."s WHERE id=".intval($m['id'])." AND number=".intval($m['main_'.$imagetype]));
			$poster='/'.($type==1 && $imagetype=='image'?$pp['locallink']:$pp['localimage']).$st.'.jpg';
		}
		else
		if ($type==1)
		{
			$pp=fq ("SELECT * FROM games_images WHERE game_id=".intval($m['id'])." ORDER BY id DESC LIMIT 1");
			$poster=$pp['local_image'];

		}

	} else $poster='/i/nocover.png';

	return $poster;

}

function nice_grade ($grade)
{
	$grade=round($grade);
	if ($grade==0) return '−';
		else return $grade;
}

function simple_critics_list ($publication_id, $count, $start)
{
	$critics_list=explode('<->',faq("SELECT critics_list FROM publications WHERE id=".intval($publication_id),"critics_list"));

	$ab=$start;
	while ($ab<$start+5 && $ab<$count)
	{	
		$c=explode('#',$critics_list[$ab]);
		if ($c[0]!=0)
			echo '
<p><b>'.($publication_id==37 && $c[0]==0?'Всеяредакция':get_critics_names ($c[0])).'</b> ('.$c[1].' рецензи'.ending($c[1],'я','и','й').')</p>';
		$ab++;
	}
	if ($count>$ab)
		echo '<div id="critics_more"><p>&nbsp;</p><p><b><a href="javascript:void(0);" onClick="$(\'#critics_more\').loadWith(\'/useractions.php?action=simple_critics_list&id='.$publication_id.'&count='.$count.'&start='.$ab.'\');" class="page_critic_pseudolink">Ещё '.($count-$ab>5?5:$count-$ab).' автор'.ending($count-$ab>5?5:$count-$ab,'','а','ов').' издания</a></b></p></div>';
	
}

function main_critics_list ($count, $start, $num)
{
	
	if ($count==-1)
		$count=faq("SELECT COUNT(*) as count FROM critics WHERE reviews_count>0 AND grades_num>=".MINGRADES." AND readers_bayes>=50","count");

	$critics=q("SELECT * FROM critics WHERE reviews_count>0 AND grades_num>=".MINGRADES." AND readers_bayes>=50 ORDER BY readers_bayes DESC, reviews_count DESC LIMIT ".intval($start).", ".intval($num));
	
	echo '<ul>';
	
	$ab=$start;
	while ($c=mysql_fetch_array($critics))
	{
		$work_places=explode('<->',$c['work_places']);
		if (sizeof($work_places)>0)
		{
			$work_max_reviews=0;
			$work_max=0;
			$work_latest_review=0;
			$work_latest=0;
			foreach ($work_places as $work_place)
			{
				$work=explode ('#',$work_place);
				if ($work[3]>$work_max_reviews)
				{
					$work_max=$work[0];
					$work_max_reviews=$work[3];
				}
				if ($work[2]>$work_latest_review)
				{
					$work_latest=$work[0];
					$work_latest_review=$work[2];
				}				
			}
			$work_max_name=faq ("SELECT name FROM publications WHERE id=".intval($work_max),"name");
			if ($work_max_name=='') $work_max=0;
			
			if ($work_max!=$work_latest)
			{
				$work_latest_name=faq ("SELECT name FROM publications WHERE id=".intval($work_latest),"name");
				if ($work_latest_name=='') $work_latest=0;
			} else $work_latest_name=$work_max_name;

		} else 
			$work_max_name='';
		
		echo '
			<li>
			<h4 class="users_rating_'.grade_color(round($c['readers_bayes'])).'">'.round($c['readers_bayes']).'</h4>
			<h2><a href="/critics/'.$c['id'].'/">'.$c['name'].'</a></h2>
			<p>'.($work_max_name!=''?$work_max_name.($work_latest_name!='' && $work_latest_name!=$work_max_name?', '.$work_latest_name:'').'; ':'').$c['reviews_count'].' рецензи'.ending($c['reviews_count'],'я','и','й').'</p>
			<div class="pos">'.($ab+1).'</div>
			</li>';
		$ab++;
	}
	
	$plus=($count-$ab>$num?$num:$count-$ab);
		
	echo '
		</ul>'.($count>$ab?'<div id="main_critics_list_more"><div class="critics_list_more"><a href="javascript:void(0)" class="pseudolink" onClick="$(\'#main_critics_list_more\').loadWith(\'/useractions.php?action=main_critics_list&count='.$count.'&num='.$num.'&start='.$ab.'\');">Следующи'.ending($plus,'й','е','е').' '.$plus.' критик'.ending($plus,'','а','ов').'</a></div></div>':'');

}


function grade ($id, $type, $gr, $c_member, $action='grade')
{
	$gr=intval($gr);
	$c_member=intval($c_member);
	
	if ($gr%10==0 && $gr>0 and $gr<=100)
	{
	
	if ($type==0)
	{
		$section='movies';
		$table='grades_movies';
		$object_id=intval(faq("SELECT id FROM movies WHERE codename='".mysql_real_escape_string($id)."'","id"));

	}
	else
	if ($type==1)
	{
		$section='gamesofgeneration';
		$table='grades_games';
		$object_id=intval(faq("SELECT id FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($id)."'","id"));

	}
	else
	if ($type==-1)
	{
		$section='critics';
		$table='grades_critics';
		$object_id=intval($id);
	}
	else
	if ($type==-2)
	{
		$section='publications';
		$table='grades_publications';
		$object_id=intval($id);

	}
	else return '';

	if ($object_id>0 && $c_member>0)
	{
		$test=fq("SELECT * FROM ".$table." WHERE object_id=$object_id AND user_id=$c_member");
		if ($test)
			q ("UPDATE ".$table." SET grade=$gr, timestamp=".mktime()." WHERE object_id=$object_id AND user_id=$c_member");
		else
			q ("INSERT INTO ".$table." SET grade=$gr, timestamp=".mktime().", object_id=$object_id, user_id=$c_member");
	}
	
	
	if ($section=='movies' || $section=='gamesofgeneration') $field='codename';
	if ($section=='critics' || $section=='publications') $field='id';

	/*
	else if ($section=='trailers') $field='num';
		else if ($section=='episodes') $field='id';
	global $logged, $loginza;
	*/
 	$link=con();
 	
 	// echo $id.$type.$gr.$c_member.$action;
 	
 	if ($action=='grade')
 	{
 		$var_grades='grades';
 		$var_graders='graders';
 		$var_grades_num='grades_num';
 		$var_grades_sum='grades_sum';
 		$var_readers_grade='readers_grade';
 		$var_readers_bayes='readers_bayes';
 	} else
 	{
 		$var_grades='waits';
 		$var_graders='waiters';
 		$var_grades_num='waits_num';
 		$var_grades_sum='waits_sum';
 		$var_readers_grade='waiters_grade';
 		$var_readers_bayes='waiters_bayes';
 	}
	if ($gr>0 && $c_member>0) {
	
			if ($gr>100) $gr=100;
			$movies=q ("SELECT ".mysql_real_escape_string($var_grades).", ".mysql_real_escape_string($var_graders).", ".mysql_real_escape_string($var_grades_num).", ".mysql_real_escape_string($var_grades_sum)." FROM ".mysql_real_escape_string($section)." WHERE ".mysql_real_escape_string($field)."='".mysql_real_escape_string($id)."'");
			if ($movies) {
				$m=mysql_fetch_array($movies);
				$upd=FALSE;
				if ($m[$var_grades_num]!=0) 
				{
					$graders=explode (',',$m[$var_graders]);
					$grades=explode (',',$m[$var_grades]);
					if (in_array($c_member,$graders)) 
					{
						// Если уже голосовал чувак
						$votepos=array_search($c_member,$graders);
						$usergrade_graders='';
						$usergrade_grades='';
						$usergrade_grades_sum=0;
						for ($i=0; $i<$votepos; $i++)
						{
							$usergrade_graders.=$graders[$i].',';
							$usergrade_grades.=$grades[$i].',';
							$usergrade_grades_sum+=$grades[$i];
						}
						$usergrade_graders.=$c_member;
						$usergrade_grades.=$gr;
						$usergrade_grades_sum+=$gr;
						for ($i=$votepos+1; $i<sizeof($graders); $i++)
						{
							$usergrade_graders.=','.$graders[$i];
							$usergrade_grades.=','.$grades[$i];
							$usergrade_grades_sum+=$grades[$i];
						}
						$grades_num=sizeof($grades);
					} else
					{
						// Если еще не голосовал
						$usergrade_graders=$m[$var_graders].','.$c_member;
						$usergrade_grades=$m[$var_grades].','.$gr;
						$usergrade_grades_sum=array_sum($grades)+$gr;
						$grades_num=sizeof($grades)+1;
					}
				} else {
					$usergrade_graders=$c_member;
					$usergrade_grades=$gr;
					$usergrade_grades_sum=$gr;
					$grades_num=1;
				}

					$readers_grade=$usergrade_grades_sum/$grades_num;
					
		if ($readers_grade>75)
			$av=BAYESTOP;
		else
		if ($readers_grade>50)
			$av=BAYESMIDDLETOP;
		else
		if ($readers_grade>25)
			$av=BAYESMIDDLEBOTTOM;
		else
		if ($readers_grade>0)
			$av=BAYESBOTTOM;		
		else
			$av=0;

/*					
					if ($readers_grade>=50)
						$av=BAYESMIDDLETOP;
					else
						$av=BAYESMIDDLEBOTTOM;
*/
					
					$readers_bayes=$grades_num/($grades_num+MINPERSONBAYES)*$readers_grade+MINPERSONBAYES/($grades_num+MINPERSONBAYES)*$av;
										
					q ("UPDATE ".mysql_real_escape_string($section)." SET ".mysql_real_escape_string($var_graders)."='".mysql_real_escape_string($usergrade_graders)."', ".mysql_real_escape_string($var_grades)."='".mysql_real_escape_string($usergrade_grades)."', ".mysql_real_escape_string($var_grades_num)."='".mysql_real_escape_string($grades_num)."', ".mysql_real_escape_string($var_grades_sum)."='".mysql_real_escape_string($usergrade_grades_sum)."', ".mysql_real_escape_string($var_readers_grade)."='".mysql_real_escape_string($readers_grade)."', ".mysql_real_escape_string($var_readers_bayes)."='".mysql_real_escape_string($readers_bayes)."' WHERE ".mysql_real_escape_string($field)."='".mysql_real_escape_string($id)."'");
					$readers_text=''; 
					
					if ($section=='critics' || $section=='publications')
					{
						
						$place=faq ("select a.place+b.place as place from (SELECT count(*)+1 as place FROM ".mysql_real_escape_string($section)." where grades_num>=".MINGRADES." AND readers_bayes > (select readers_bayes from ".mysql_real_escape_string($section)." where id = ".intval($id).")) as a straight_join (SELECT count(*) as place FROM ".mysql_real_escape_string($section)." where grades_num>=".MINGRADES." AND readers_bayes = (select readers_bayes from ".mysql_real_escape_string($section)." where id = ".intval($id).") and id < ".intval($id)." ) as b","place");
						
						if ($place==0) $place=1;
					}
					
					if ($grades_num>0) {
						if ($section!='critics' && $section!='publications')
							$readers_text=json_encode(array('rating'=>round($readers_grade),'reviews'=>$grades_num));
						else
							$readers_text=json_encode(array('rating'=>round($readers_grade), 'place'=>$place, 'votes'=>$grades_num));
						return $readers_text;

					}
			}

	}
	
	return $readers_text;
	}
		
}


	function sort_table ($codename, $page, $table, $type, $sort, $reviews_count, $rating, $num)
	{
		echo ($reviews_count>0?
				'<div class="source">Сортировка по '.
				($sort!='date'?
					'<a href="javascript:void(0)" onClick="sort_table(\''.$codename.'\', \''.$page.'\', \''.$table.'\', \''.$type.'\', \'date\', \''.$reviews_count.'\', \''.$rating.'\', \''.$num.'\');" class="" title="Самые новые — вверху">дате</a>, '
				:'<span title="Самые новые — вверху">дате</span>, ').
				($page=='movie' || $page=='critic'?
					($sort!='publication'?
						'<a href="javascript:void(0)" onClick="sort_table(\''.$codename.'\', \''.$page.'\', \''.$table.'\', \''.$type.'\', \'publication\', \''.$reviews_count.'\', \''.$rating.'\', \''.$num.'\');" class="" title="Самые популярные издания — вверху">популярности издания</a>'
					:'<span title="Самые популярные издания — вверху">популярности издания</span>')
				:'').
				/*
				($page=='publication'?
					($sort!='critic'?
						'<a href="javascript:void(0)" onClick="sort_table(\''.$codename.'\', \''.$page.'\', \''.$table.'\', \''.$type.'\', \'critic\', \''.$reviews_count.'\', \''.$rating.'\', \''.$num.'\');" class="" title="Самые популярные — вверху">популярности критика</a>'
					:'<span title="Самые популярные — вверху">популярности критика</span>')
				:'').
				*/
				($page!='publication'?', ':'').
				($page=='publication' || $page=='movie'?

					($sort!='critic_rating'?
						'<a href="javascript:void(0)" onClick="sort_table(\''.$codename.'\', \''.$page.'\', \''.$table.'\', \''.$type.'\', \'critic_rating\', \''.$reviews_count.'\', \''.$rating.'\', \''.$num.'\');" class="" title="Самые популярные критики — вверху">популярности критика</a>'
					:'<span title="Самые популярные критики — вверху">популярности критика</span>')
				:'').
				'</div>'.
				($table=='reviews'?'<div class="grade">'.
					($sort!='rating'?
						'<a href="javascript:void(0)" onClick="sort_table(\''.$codename.'\', \''.$page.'\', \''.$table.'\', \''.$type.'\', \'rating\', \''.$reviews_count.'\', \''.$rating.'\', \''.$num.'\');" class="" title="Самые лучшие — вверху">Оценка критика</a>'
					:'<span title="Самые лучшие — вверху">Оценка критика</span>').
				'</div>':'')
			:'&nbsp;');
	}
	
	function get_publication_codename ($id)
	{
		$code=faq("SELECT codename FROM publications WHERE id=".intval($id),"codename");
		return $code;
	}

	function more ($codename, $page, $table, $type, $sort, $reviews_count, $rating, $start, $num=10, $wisdomstart=0)
	{

		if ($type=='') $type=0;
		if ($table=='reviews')
		{
			if ($page=='movie')	
				$reviews=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE type=".mysql_real_escape_string($type)." AND codename='".mysql_real_escape_string($codename)."' ORDER BY ".($sort=='critic_rating'?"critic_rating DESC, review_timestamp DESC":"").($sort=='rating'?"rating DESC":"").($sort=='date'?"review_timestamp DESC":"").($sort=='publication'?"publication ASC":"").($sort=='critic'?"author ASC":"")." LIMIT ".intval($start).",".intval($num));
			else
			if ($page=='publication')	
				$reviews=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE codename!='' AND publication_id=".intval($codename)." ORDER BY ".($sort=='critic_rating'?"critic_rating DESC, review_timestamp DESC":"").($sort=='rating'?"rating DESC":"").($sort=='date'?"review_timestamp DESC":"").($sort=='publication'?"publication ASC":"").($sort=='critic'?"author ASC":"")." LIMIT ".intval($start).",".intval($num));
			else
			if ($page=='critic')	
				$reviews=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE codename!='' AND (critic_id LIKE '% ".mysql_real_escape_string($codename)." %') ".($type>0?"AND publication_id=".intval($type)." ":"")."ORDER BY ".($sort=='critic_rating'?"critic_rating DESC, review_timestamp DESC":"").($sort=='rating'?"rating DESC":"").($sort=='date'?"review_timestamp DESC":"").($sort=='publication'?"publication ASC":"").($sort=='critic'?"author ASC":"")." LIMIT ".intval($start).",".intval($num));
		} else
		if ($table=='wisdom')
		{
			if ($page=='movie')	
				$reviews=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE review_id!=0 AND type=".intval($type)." AND codename='".mysql_real_escape_string($codename)."' GROUP BY issue, codename, critic_id ORDER BY ".($sort=='critic_rating'?"critic_rating DESC, timestamp DESC":"").($sort=='rating'?"rating DESC":"").($sort=='date'?"timestamp DESC":"").($sort=='publication'?"publication ASC":"").($sort=='critic'?"author ASC":"")." LIMIT ".intval($start).",".intval($num));
			else
			if ($page=='publication')
			{
				$reviews=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE review_id!=0 AND publication_id='".mysql_real_escape_string($codename)."' GROUP BY issue, codename, critic_id ORDER BY ".($sort=='critic_rating'?"critic_rating DESC, timestamp DESC":"").($sort=='rating'?"rating DESC":"").($sort=='date'?"timestamp DESC":"").($sort=='publication'?"publication ASC":"").($sort=='critic'?"author ASC":"")." LIMIT ".intval($start).",".intval($num));
			}
			else
			if ($page=='critic') {
				$reviews=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE review_id!=0 AND critic_id LIKE '% ".mysql_real_escape_string($codename)." %' ".($type>0?"AND publication_id=".intval($type)." ":"")."GROUP BY issue, codename, critic_id ORDER BY ".($sort=='critic_rating'?"critic_rating DESC, timestamp DESC":"").($sort=='rating'?"rating DESC":"").($sort=='date'?"timestamp DESC":"").($sort=='publication'?"publication ASC":"").($sort=='critic'?"author ASC":"")." LIMIT ".intval($start).",".intval($num));
				//echo mysql_num_rows ($reviews);
				}
		}
		
		$ab=$start;
		$abw=$wisdomstart; // Начало для мудростей
		$bc=$start;
						
		while ($r=mysql_fetch_array($reviews))
		{	
			if ($page=='movie' || $page=='critic') 
				$publ=fq("SELECT * FROM publications WHERE id=".intval($r['publication_id']));

			if ($page=='publication' || $page=='critic' || $page=='movie')
			{
				if ($r['type']==0)
					$mov=fq("SELECT * FROM movies WHERE codename='".mysql_real_escape_string($r['codename'])."'");

				else
				if ($r['type']==1)
				{
					$mov=fq("SELECT * FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($r['codename'])."'");
				}
			}
			
			if ($page=='movie' || $page=='publication')
			{
				$crit=fq("SELECT * FROM critics WHERE id=".intval($r['critic_id']));
				
			}
			

			// $rt=rating($r['grade'],$r['publication']);
			
			$rt=$r['rating'];
			
			if ($table=='reviews')
			{
				$comments=$r['comments'];
			} else
			if ($table=='wisdom')
			{
				$wisrev=fq("SELECT id, comments FROM reviews WHERE id=".intval($r['review_id']));
				$comments=$wisrev['comments'];
			}
			$author_wisdom=wisdom_level($crit['wisdom_count']);

			// Тут был костыль, но уже поменял
			//$review_wisdom=wisdom_level($r['wisdom_count']);
			$review_wisdom=$r['wisdom_count'];
			if ($review_wisdom>3)
				$review_wisdom=3;
			// Конец костыля
			
			$r['grade']=str_replace('.',',',$r['grade']);
						
			if ($page=='movie')
				$first_column='<a href="/publications/'.($publ['codename']!=''?$publ['codename']:$r['publication_id']).'/">'.$publ['name'].'</a>';
			else
			if ($page=='publication')
				$first_column=get_critics_names($r['critic_id']);
			else	
			if ($page=='critic')
				$first_column=($r['publication_id']!=''?'<a href="/publications/'.($publ['codename']!=''?$publ['codename']:$r['publication_id']).'/">'.$publ['name'].'</a>':'Неизвестное издание');
			
			echo '
				<div class="row_'.($table=='reviews'?($ab%2==0?'a':'b'):($bc%2==0?'c':'b')).' clearfix">
					<div class="source">'.$first_column.'<i>'.(mb_substr($r['issue'],0,7)!='http://'?$r['issue']:($r['review_timestamp']>0?rus_date(($table=='wisdom'?$r['timestamp']:$r['review_timestamp'])):'&nbsp;')).'</i></div>
					<div class="'.($table=='wisdom'?'wisdom':'author').'">
						<p>'.
						($page=='movie'?'<span class="wisdom_grade" style="'.($author_wisdom>0?'background-image: url(\'/i/wisdom_grade'.$author_wisdom.'.png\');':'').'" title="'.($author_wisdom>0?'Критик '.($author_wisdom==1?'умеренной':'').($author_wisdom==2?'повышенной':'').($author_wisdom==3?'исключительной':'').' «мудрости»':'').'">&nbsp;</span>'.get_critics_names ($r['critic_id']):'').
						($page=='publication' || $page=='critic'?'<span class="wisdom_grade" style="" title="">&nbsp;</span>'.
				($r['codename']!=''?'<a href="/'.($r['type']==0?'movies':'').($r['type']==1?'games':'').'/'.$r['codename'].'/">'.($r['type']==0?$mov['russian']:'').($r['type']==1?$mov['original']:'').'</a>':($r['russian']!=''?$r['russian']:$r['original']))
				
				:'').
					// ИСПРАВИТЬ на original!!!
						'</p>';
						
			if ($table=='reviews')
				echo ($r['summary']!=''?'<p class="quote">'.short_summary($r['summary']).'</p>':'');
			else
			if ($table=='wisdom')
			{
				echo '<ul class="wisdom_quotes">';
				if ($page=='movie')	
					$wisd=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE type=".intval($type)." AND codename='".mysql_real_escape_string($codename)."' AND issue='".mysql_real_escape_string($r['issue'])."' ORDER BY timestamp DESC");
				else
				if ($page=='critic')	
					$wisd=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE critic_id LIKE '% ".mysql_real_escape_string($codename)." %' AND issue='".mysql_real_escape_string($r['issue'])."' AND codename='".mysql_real_escape_string($r['codename'])."' ORDER BY timestamp DESC");
				else
				if ($page=='publication')
				{	
					$wisd=q("SELECT * FROM ".mysql_real_escape_string($table)." WHERE publication_id='".mysql_real_escape_string($codename)."' AND issue='".mysql_real_escape_string($r['issue'])."' AND codename='".mysql_real_escape_string($r['codename'])."' ORDER BY timestamp DESC");
				}
				
				$abw+=mysql_num_rows($wisd);
				
				while ($wi=mysql_fetch_array($wisd))
					echo '<li'.($wi['truth']!=''?' title="'.str_replace('"','&quot;',$wi['truth']).'"':'').'>'.$wi['text'].'</li>';
				echo '</ul>';				

			} else
			{
				$ab++;
			}
			
			echo '<p class="review_links">'.
						($review_wisdom>0?'<span class="wisdom_grade" style="'.($review_wisdom>0?'background-image: url(\'/i/wisdom_grade'.$review_wisdom.'.png\');':'').'" title="'.($review_wisdom>0?'Градус «мудрости» в рецензии '.($review_wisdom==1?'умеренный':'').($review_wisdom==2?'повышенный':'').($review_wisdom==3?'критический':''):'').'">&nbsp;</span>':'').
				($table=='reviews'?
				($comments>0?'<a href="/reviews/'.$r['id'].'/">Обсудить рецензию</a><span class="comments_count"> ('.$comments.')</span>':'<a href="/reviews/'.$r['id'].'/">Начать обсуждение</a><span class="comments_count"></span>'):'').
				($table=='wisdom'?
				($comments>0?'<a href="/reviews/'.$wisrev['id'].'/wisdom/">Обсуждение и обоснование «мудрости»</a><span class="comments_count"> ('.$comments.')</span>':'<a href="/reviews/'.$wisrev['id'].'/wisdom/">Обсуждение и обоснование «мудрости»</a><span class="comments_count"></span>'):'').
				(mb_substr($r['issue'],0,7)=='http://'?' <a href="'.$r['issue'].'" target="_blank" rel="nofollow">Прочесть рецензию</a><span class="newwindow">&nbsp;</span>':'').'</p>
					</div>'.
					($table=='reviews'?'<div class="rating site_rating_'.grade_color($rt).'"><h4>'.$rt.'</h4>'.($r['grade']=='#хорошо' || $r['grade']=='#отлично' || $r['grade']=='#плохо' || $r['grade']=='#так себе'?'<span class="help" title="Поскольку издание не выставило явную оценку, она была определена редакцией «Критиканства» исходя из тона рецензии">'.utf8_ucfirst(mb_substr($r['grade'],1)).'</span>':($publ['topgrade']==100?'&nbsp;':'<span class="" title="">'.$r['grade'].(($publ['topgrade']<100 && $publ['topgrade']!=0 && $r['publication_id']!=91) || ($r['publication_id']==91 && (int)$r['grade']>0)?' из '.$publ['topgrade']:'')).'</span>').'</div>':'').
				'</div>';
	
			$ab++;
			$bc++;
		}
		
		//echo $reviews_count.'-'.$ab;
		
		if (mysql_num_rows($reviews)>0)
		{
			if ($table=='wisdom')
				$more_count=$reviews_count-$abw>$num?$num:$reviews_count-$abw;
			else
				$more_count=$reviews_count-$ab>$num?$num:$reviews_count-$ab;

			echo '
			<div id="'.$table.'_more">'.
				($page=='movie' && $table=='reviews'?'<div class="item_reviews_list_summary site_rating_'.grade_color ($rating).'"><div class="overall"><span>=</span><h4>'.$rating.'</h4></div><h2>Всего '.$reviews_count.' рецензи'.ending($reviews_count,'я','и','й').' на '.($type==0?'фильм «'.$mov['russian'].'»':'').($type==1?'игру '.($mov['russian']!=''?'«'.$mov['russian'].'»':$mov['original']):'').'</h2><div class="clearfix">&nbsp;</div></div>':'').
				($more_count>0?'<div class="item_reviews_list_more"><a href="javascript:void(0)" class="pseudolink" onClick="$(\'#'.$table.'_more\').loadWith(\'/useractions.php?action=more&table='.$table.'&page='.$page.'&codename='.$codename.'&type='.$type.'&reviews_count='.$reviews_count.'&rating='.$rating.'&sort='.$sort.'&start='.$ab.'&wisdomstart='.$abw.'&num='.($reviews_count-$ab>$num?$num:$reviews_count-$ab).'\');">Ещё '.($more_count).($table=='reviews'?' рецензи'.ending($more_count,'я','и','й'):'').($table=='wisdom'?' названи'.ending($more_count,'е','я','й'):'').'</a>'.($page=='movie'?'&bull; <a href="javascript:void(0)" class="pseudolink" onClick="$(\'#'.$table.'_more\').loadWith(\'/useractions.php?action=more&table='.$table.'&page='.$page.'&codename='.$codename.'&type='.$type.'&reviews_count='.$reviews_count.'&rating='.$rating.'&sort='.$sort.'&start='.$ab.'&num='.($reviews_count-$ab).'\');'.'">Показать '.ending($reviews_count,'всю','все','все').' '.$reviews_count.' рецензи'.ending($reviews_count,'ю','и','й').'</a>':'').'</div>':'').'
			</div>';
			
		} else
		if ($table=='reviews')
		{
			echo '		
				<div class="noreviews clearfix"><a href="#mf_default" class="button_add main_form" title="Добавить ссылку на рецензию" onClick="$(\'#type_review\').click();">Добавить&nbsp;<b>&#43;</b></a><u>Рецензий пока нет<i>Если вы нашли обзор, можете добавить его на сайт.</i></u></div>
	';		
		} else
		if ($table=='wisdom')
			echo '<div class="nowisdom clearfix"><a href="#mf_default" class="button_add main_form" title="Добавить «мудрость»" onClick="$(\'#type_wisdom\').click();">Добавить&nbsp;<b>&#43;</b></a><u>Ни одной «мудрости» пока не зафиксировано<i>Если вы нашли в рецензии неточность или нелепость, можете добавить её на сайт.</i></u></div>';
	}

/*
<a href="#mf_default" class="button_add main_form" data-fancybox-group="main_form" title="Рецензию или «мудрость»">Добавить&nbsp;<b>&#43;</b></a>
*/

function top_authors ($type, $sort, $count, $num, $start)
{
	global $loginza_id;
	echo '
	<ul>';
	if ($count==-1)
	{
		if ($sort=='best' || $sort=='worst')
			$count=faq ("SELECT COUNT(*) as count FROM ".$type." WHERE grades_num>=".($type=='critics' || $type=='publications'?MINGRADES:1)." AND readers_bayes".($sort=='best'?">=":"<")."50","count");
		else
		if ($sort=='active')
			$count=faq ("SELECT COUNT(*) as count FROM ".$type." WHERE reviews_count>0","count");
		else
		if ($sort=='popular')
			$count=faq ("SELECT COUNT(*) as count FROM ".$type." WHERE reviews_count>0","count");
		else
		if ($sort=='wiseguys')
			$count=faq ("SELECT COUNT(*) as count FROM ".$type." WHERE wisdom_count>0","count");
		else
		if ($sort=='topwisdom')
			$count=faq ("SELECT COUNT(*) as count FROM wisdom WHERE grades_num>=".MINGRADES." AND review_id!=0","count");
		else
		if ($sort=='wisdom')
			$count=faq ("SELECT COUNT(*) as count FROM wisdom WHERE review_id!=0".($type!=""?" AND type=".$type:""),"count");
		
	}
	if ($sort=='best' || $sort=='worst')
		$publications=q ("SELECT * FROM ".$type." WHERE grades_num>=".($type=='critics' || $type=='publications'?MINGRADES:1)." AND readers_bayes".($sort=='best'?">=":"<")."50 ORDER BY readers_bayes ".($sort=='best'?"DESC":"ASC").", grades_num DESC, reviews_count DESC, name ASC LIMIT ".intval($start).", ".intval($num));
	else
	if ($sort=='active')
		$publications=q ("SELECT * FROM ".$type." WHERE reviews_count>0 ORDER BY reviews_count DESC, grades_num DESC LIMIT ".intval($start).", ".intval($num));
	else
	if ($sort=='popular')
		$publications=q ("SELECT * FROM ".$type." WHERE reviews_count>0 ORDER BY grades_num DESC, readers_bayes DESC LIMIT ".intval($start).", ".intval($num));
	else
	if ($sort=='wiseguys')
		$publications=q ("SELECT * FROM ".$type." WHERE wisdom_count>0 ORDER BY wisdom_count DESC, grades_num DESC LIMIT ".intval($start).", ".intval($num));
	else
	if ($sort=='topwisdom')
		$publications=q ("SELECT * FROM wisdom WHERE grades_num>=".MINGRADES." AND review_id!=0 ORDER BY readers_bayes DESC, grades_num DESC LIMIT ".intval($start).", ".intval($num));
	else
	if ($sort=='wisdom')
		$publications=q ("SELECT * FROM wisdom WHERE review_id!=0 AND codename!=''".($type!=""?" AND type=".$type:"")." ORDER BY timestamp DESC LIMIT ".intval($start).", ".intval($num));
	
	$ab=$start;
	
	while ($p=mysql_fetch_array($publications))
	{
		
		$wisdom_level=wisdom_level($p['wisdom_count']);
		if ($type=='critics')
		{
			$work_places=explode('<->',$p['work_places']);
			if (sizeof($work_places)>0)
			{
				$work_max_reviews=0;
				$work_max='';
				$work_latest_review=0;
				$work_latest='';

				foreach ($work_places as $work_place)
				{
					$work=explode ('#',$work_place);
					if ($work[3]>$work_max_reviews)
					{
						$work_max=$work[0];
						$work_max_reviews=$work[3];
					}				
					if ($work[2]>$work_latest_review)
					{
						$work_latest=$work[0];
						$work_latest_review=$work[2];
					}				
				}
				
				$work_max_names=fq ("SELECT * FROM publications WHERE id=".intval($work_max));
				$work_max_name=$work_max_names['name'];
				if ($work_max_name=='') $work_max='';
					
				if ($work_max!=$work_latest)
				{
					$work_latest_names=fq ("SELECT * FROM publications WHERE id=".intval($work_latest));
					$work_latest_name=$work_latest_names['name'];
					if ($work_latest_name=='') $work_latest='';
				//if ($loginza_id==620) echo $work_latest_name;
	
					if ($work_max_names['codename']!='')
						$work_max=$work_max_names['codename'];
					if ($work_latest_names['codename']!='')
						$work_latest=$work_latest_names['codename'];
						
				} else {
					if ($work_max_names['codename']!='')
						$work_max=$work_max_names['codename'];
					$work_latest_name=$work_max_name;
					$work_latest=$work_max;
				}

				
				//if ($loginza_id==620) echo $work_max.'-'.$work_latest;
				
			} else {
				$work_max_name='';
				$work_max='';
				$work_latest='';
				$work_latest_name='';
			}
		}
					
		$voted='';

		if ($loginza_id==620 || $loginza_id==9 || $loginza_id==814)
		{
			// echo "SELECT grade FROM grades_".$type." WHERE object_id=".intval($p['id'])." AND user_id=".intval($loginza_id);
			$voted=intval(faq("SELECT grade FROM grades_".$type." WHERE object_id=".intval($p['id'])." AND user_id=".intval($loginza_id),"grade"));
			if ($voted!=0)
				$voted='<div class="userqb"><div class="user_rated" title="Ваша оценка: '.round($voted/10).' из 10">&nbsp;</div><div class="user_faved active" title="Удалить из избранного">&nbsp;</div></div>';
			else
				$voted='';
		}
		
		if ($sort=='wisdom')
		{
			$cri=fq("SELECT * FROM critics WHERE id=".intval(trim($p['critic_id'])));
			$mov=fq("SELECT * FROM ".($p['type']==0?'movies':'').($p['type']==1?'gamesofgeneration':'')." WHERE codename='".mysql_real_escape_string($p['codename'])."'");
			$pub=fq("SELECT * FROM publications WHERE id=".$p['publication_id']);
			
			$wisdom_level=($mov['wisdom_count']>5?3:($mov['wisdom_count']>3?2:($mov['wisdom_count']>0?1:0)));
		}
		echo '		
		<li class="row_'.($ab%2==0?'a':'b').' '.($sort=='best' || $sort=='worst'?'users_rating_'.grade_color($p['readers_bayes']):'').($sort=='active'?'critic_reviews_count':'').($sort=='popular'?'critic_reviews_count':'').
		($sort=='wiseguys' || $sort=='topwisdom'?'critic_wisdom_'.wisdom_level_word($wisdom_level):'').
		($sort=='wisdom'?'critic_wisdom_'.wisdom_level_word($wisdom_level):'').
		' clearfix">
			<div class="left"'.($sort=='wisdom'?' style="width: 100% !important;"':'').'>'.
		($sort=='wisdom'?
		'<a href="/critics/'.$cri['id'].'/"><div class="cover" style="background-image: url(\''.get_image($p['codename'],$p['type']).'\');"></div></a>':
		'<a href="/'.$type.'/'.($type=='publications'?($p['codename']!=''?$p['codename']:$p['id']):$p['id']).'/"><div class="photo" style="background-image: url(\''.($p['image']!=0?'/'.$type.'/'.$p['id'].'/small.jpg':'/i/nophoto.png').'\');"></div></a>').
		
			($sort=='best' || $sort=='worst'?'<h4>'.nice_grade($p['readers_bayes']).'</h4>':'').($sort=='active'?'<h4>'.$p['reviews_count'].'</h4>':'').($sort=='popular'?'<h4 title="Количество оценок">'.$p['grades_num'].'</h4>':'').
			($sort=='wiseguys' || $sort=='topwisdom'?'<h4 title="'.($wisdom_level==0?'«Мудрость» не зафиксирована':'').($wisdom_level==1?'Критик умеренной «мудрости»':'').($wisdom_level==2?'Критик повышенной «мудрости»':'').($wisdom_level==3?'Критик исключительной «мудрости»':'').'">&nbsp;</h4>':'').
			
			($sort=='wisdom'?'<h4 title="'.($wisdom_level>0?($p['type']==0?'Фильм':'Игра').' '.($wisdom_level==1?'умеренной':'').($wisdom_level==2?'повышенной':'').($wisdom_level==3?'исключительной':'').' сложности, на '.($p['type']==0?'котором':'которой').' '.($wisdom_level==1?'спотыкнулось совсем мало':'').($wisdom_level==2?'сломалось преизрядно':'').($wisdom_level==3?'сломалась целая толпа':'').' «мудрецов»':'').'">&nbsp;</h4>':'').

			
			/*($sort=='wisdom'?'<h4 title="'.($wisdom_level==0?'«Мудрость» не зафиксирована':'').($wisdom_level==1?'Критик умеренной «мудрости»':'').($wisdom_level==2?'Критик повышенной «мудрости»':'').($wisdom_level==3?'Критик исключительной «мудрости»':'').'">&nbsp;</h4>':'').*/
			($sort=='wisdom'?
			'<h2><a href="/'.($p['type']==0?'movies':'').($p['type']==1?'games':'').'/'.$p['codename'].'/">'.($p['type']==0?$mov['russian']:'').($p['type']==1?$mov['original']:'').'</a><p class="superright">'.rus_date($p['timestamp'],'').'</p>':
			'<h2><a href="/'.$type.'/'.($type=='publications'?($p['codename']!=''?$p['codename']:$p['id']):$p['id']).'/">'.$p['name'].'</a>').
			($sort!='wiseguys' && $sort!='topwisdom' && $sort!='wisdom'?'<span class="wisdom_grade_inset" style="'.($wisdom_level>0?'background-image: url(\'/i/wisdom_grade'.$wisdom_level.'.png\');" title="'.($wisdom_level>0?($type=='publications'?'Издание':'Критик').' '.($wisdom_level==1?'умеренной':'').($wisdom_level==2?'повышенной':'').($wisdom_level==3?'исключительной':'').' «мудрости»':'«Мудрость» не зафиксирована'):'').'">&nbsp;</span>':'').
			'</h2>'.
			
			($sort=='wisdom'?
			'<p style="margin-top: 5px; color: #394146;" title="'.str_replace('"','&quot;',$p['truth']).'">'.$p['text'].'</p>
			<p class="superright"><a href="/reviews/'.$p['review_id'].'/wisdom/">Обсудить</a></p>
			<p style="margin-top: 5px;">'.get_critics_names ($p['critic_id']).' / <a href="/publications/'.($pub['codename']!=''?$pub['codename']:$p['publication_id']).'/">'.$pub['name'].'</a></p>'
			:'').			
		($type=='publications'?
			($p['critics_count']!=0?'<p><i>'.$p['critics_count'].' критик'.ending($p['critics_count'],'','а','ов').'</i></p>':''):'').
		($type=='critics'?
			($p['aka']!=''?'<p><i>'.str_replace('#',', ',$p['aka']).'</i></p>':'').
			($work_max!=''?'<p><a href="/publications/'.$work_max.'/">'.$work_max_name.'</a>'.($work_latest!='' && $work_latest!=$work_max?', <a href="/publications/'.$work_latest.'/">'.$work_latest_name.'</a>':'').'</p>':''):'').
			'</div>'.
			($sort!='wisdom'?'<div class="center">'.($sort=='best' || $sort=='worst' || $sort=='wiseguys' || $sort=='topwisdom' || $sort=='popular' || $sort=='wisdom'?'<p>'.$p['reviews_count'].' рецензи'.ending($p['reviews_count'],'я','и','й').'</p>':'').
			($sort=='wiseguys' || $sort=='topwisdom'?'<p>Мудрость проявлена '.$p['wisdom_count'].' раз'.ending($p['wisdom_count'],'','а','').'</p>':'').
			($sort=='wisdom'?'<p>Мудрость проявлена '.$p['wisdom_count'].' раз'.ending($p['wisdom_count'],'','а','').'</p>':'').
			($sort=='popular' || $sort=='active' || $sort=='wiseguys' || $sort=='topwisdom' || $sort=='wisdom'?'<b>Оценка пользователей:<span class="users_rating_small_'.grade_color($p['readers_bayes']).'">'.nice_grade($p['readers_bayes']).'</span></b>':'').($sort!='wiseguys' && $sort!='topwisdom' && $sort!='wisdom'?'<b>Средняя оценка в рецензиях:<span class="site_rating_small_'.grade_color($p['average_grade']).'">'.nice_grade($p['average_grade']).'</span></b>':'').'</div><div class="right">'.($ab+1).'</div>'.$voted:'').'
		</li>';
		$ab++;
	}

	$plus=($count-$ab>$num?$num:$count-$ab);
		
	echo '
		</ul>'.($count>$ab?'<div id="'.$sort.'_more'.($sort=='wisdom' && $type!=''?'_'.$type:'').'"><div class="page_lists_more"><a href="javascript:void(0)" class="pseudolink" onClick="$(\'#'.$sort.'_more'.($sort=='wisdom' && $type!=''?'_'.$type:'').'\').loadWith(\'/useractions.php?action=more_top_authors&type='.$type.'&count='.$count.'&sort='.$sort.'&num='.$num.'&start='.$ab.'\');">Следующи'.ending($plus,'й','е','е').' '.$plus.' '.
($sort=='wisdom'?'«мудрост'.ending($plus,'ь','и','ей').'»':
($type=='critics'?'критик'.ending($plus,'','а','ов'):'').($type=='publications'?'издани'.ending($plus,'е','я','й'):'')).
'</a></div></div>':'');

}
	
			
function display_ids ($ids, $names, $link)
{
	$genres='';
	$ids_a=explode(' ',trim($ids));
	$names_a=explode(',',trim($names));
	foreach ($ids_a as $key=>$ia)
		if ($link!='')
			$genres.='<a href="/'.$link.'/'.$ia.'/">'.trim($names_a[$key]).'</a>, ';
		else
			$genres.=trim($names_a[$key]).', ';
	if (mb_substr($genres,-2)==', ')
		$genres=mb_substr($genres,0,-2);
	return $genres;
}

function release_timestamp ($codename, $type)
{
	$date=0;
	
	if ($type==0)
	{
		$m=fq("SELECT * FROM movies WHERE codename='".mysql_real_escape_string($codename)."'");
		
		if ($m['premierinrussia']!='')
			$date=date_to_timestamp($m['premierinrussia']);
		
		if ($date==0 && $m['worldpremier']!='')
			$date=date_to_timestamp($m['worldpremier']);
	}
	
	if ($type==1)
	{
		$m=fq("SELECT * FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($codename)."'");
		
		if ($m['releasedate']!='')
			$date=date_to_timestamp($m['releasedate']);
	}
	
	return $date;
}

function releases_list ($type, $from, $curtime, $count, $start, $num=10, $morenum=10)
{
	global $loginza_id;

	/*
	if ($type==0)
		$releases=q("SELECT * FROM movies WHERE codename!='' AND release_timestamp>=".$from." ORDER BY release_timestamp ASC LIMIT ".$start.",".$num);
	else
	if ($type==1)
		$releases=q("SELECT * FROM gamesofgeneration WHERE codename!='' AND release_timestamp>=".$from." ORDER BY release_timestamp ASC LIMIT ".$start.",".$num);
	*/
	
	//
	$releases=q ("SELECT * FROM ".($type==0?"movies":"").($type==1?"gamesofgeneration":"")." WHERE codename!='' AND release_timestamp>=".$from." ORDER BY release_timestamp ASC".($type==0?", screens_ru DESC":"")." LIMIT ".intval($start).",".intval($num));
	//
	
	$ab=$start;
	
	$ulclosed=TRUE;
		
	while ($r=mysql_fetch_array($releases))
	{
		if ($r['release_timestamp']!=$curtime)
		{
			if (!$ulclosed) {
				echo '</ul>';
				$ulclosed=TRUE;
			}
			echo '<div class="subhd">'.rus_date($r['release_timestamp'],'').(date('Y',$r['release_timestamp'])!=date('Y')?' '.date('Y',$r['release_timestamp']).' г.':'').'</div>';
			$curtime=$r['release_timestamp'];

		}

		if ($ulclosed || $ab==$start)
		{
			echo '<ul>';
			$ulclosed=FALSE;
		}
		
		$voted='';

		if ($loginza_id==620 || $loginza_id==9 || $loginza_id==814)
		{
			$voted=intval(faq("SELECT grade FROM grades_".($type==0?"movies":"").($type==1?"games":"")." WHERE object_id=".intval($r['id'])." AND user_id=".intval($loginza_id),"grade"));
			if ($voted!=0)
				$voted='<div class="userqb"><div class="user_rated" title="Ваша оценка: '.round($voted/10).' из 10">&nbsp;</div><div class="user_faved active" title="Удалить из избранного">&nbsp;</div></div>';
			else
				$voted='';
		}

		echo '<li class="row_'.($ab%2==0?'a':'b').'">
					<a href="/'.($type==0?"movies":"").($type==1?"games":"").'/'.$r['codename'].'/"><div class="cover" style="background-image: url(\''.get_image ($r['codename'],$type, 'poster', 's').'\');"></div></a>
					<h2><a href="/'.($type==0?"movies":"").($type==1?"games":"").'/'.$r['codename'].'/">'.($type==0?$r['russian']:'').($type==1?$r['original']:'').'</a></h2>'.
					($type==1 && $r['russian']!=''&& $r['original']!=''?'<i>'.$r['russian'].'</i>':'').
					($type==0 && $r['russian']!=''&& $r['original']!=''?'<i>'.$r['original'].'</i>':'').
					($r['platforms_full']!=''?'<p>'.$r['platforms_full'].'</p>':'').
					($r['genres']!=''?'<p>'.$r['genres'].'</p>':'').
					($r['rating']>0?'<p><u>Рейтинг:<span class="site_rating_small_'.grade_color ($r['rating']).'">'.$r['rating'].'</span></u></p>':'').$voted.'</li>';
		
		$ab++;
		
	}
	
	echo '</ul>';
	
	$plus=($count-$ab>$morenum?$morenum:$count-$ab);
		
	echo ($count>$ab?'<div id="releases_more"><div class="titles_list_more"><a href="javascript:void(0)" class="pseudolink" onClick="$(\'#releases_more\').loadWith(\'/useractions.php?action=releases_list&type='.$type.'&count='.$count.'&start='.$ab.'&num='.$plus.'&morenum='.$morenum.'&curtime='.$curtime.'&from='.$from.'\');">Ещё '.$plus.($type==0?' премьер'.ending($plus,'а','ы',''):'').($type==1?' релиз'.ending($plus,'','а','ов'):'').'</a>&bull;<a href="/releases/'.($type==0?'movies':'').($type==1?'games':'').'/" class="link">Все '.($type==0?'премьеры':'').($type==1?'релизы':'').'</a></div></div>':'');
			
} 


function get_platform_id ($pl)
{
	$p=get_platform ($pl);
	if ($p)
		return $p['id'];
	else
		return false;
}

function get_platform ($pl)
{
	if ($pl!='')
	{
		$plat=fq("SELECT * FROM platforms WHERE platform='".mysql_real_escape_string($pl)."' OR display='".mysql_real_escape_string($pl)."' OR aka='".mysql_real_escape_string($pl)."'");
		if ($plat)
			return $plat;
		else
			return false;
	} else
		return false;
		
}

function get_platform_name ($id, $field='full')
{
	
	$platform=fq("SELECT * FROM platforms WHERE id=".intval($id));
	if ($field=='full')
		return $platform['display'];
	else
	if ($field=='short')
		return $platform['platform'];
}


function steal_vgchartz ($original, $releases_needed=TRUE)
{
	
		$japanonly_all=TRUE;
		
		$file=SelectNodes(SelectNode(file_get_contents('http://www.vgchartz.com/gamedb/?name='.urlencode($original)),'table','class="chart"'),'tr');
		
		$ter_names=array('North America', 'Europe', 'Japan', 'Rest of World', 'Global');
		$ter_codes=array('salesnorthamerica', 'saleseurope', 'salesjapan', 'salesrestofworld', 'salesglobal');
		$ter_pos=array (6, 7, 8, 9, 10);
		
		$sales_sum=0;
		$all_releases='';
		$all_uspublisher='';
		$all_ukpublisher='';
		
		$all_sales=array();
		
		$all_release_timestamp=0;
		
		$found=false;
				
		foreach ($file as $ff)
		{
			$japanonly=TRUE;
			$f=SelectNodes($ff,'td');
			$nn=strip_tags(SelectNode($f[1],'a'));
			if ($nn==$original)
			{
				$found=true;
				$linkvg=between(SelectNode($f[1],'a'),'href="','"');
				
				if ($releases_needed)				
				{
				
					$vgf=SelectNodes(SelectNode(between(file_get_contents($linkvg),'>Release History</a>'),'table'),'tr');
					
					foreach ($vgf as $vg)
					{
						$v=SelectNodes($vg,'td');
						if (strip_tags($v[2])=='Japan')
							$releasedate['jap']=date_to_timestamp(strip_tags($v[3]));
						else
						if (strip_tags($v[2])=='North America')
						{
							$japanonly=FALSE;
							$japanonly_all=FALSE;
							$releasedate['us']=date_to_timestamp(strip_tags($v[3]));
							$uspublisher=strip_tags($v[1]);
							if ($uspublisher!='')
								$all_uspublisher=$uspublisher;
						}
						else
						if (strip_tags($v[2])=='Europe')
						{
							$japanonly=FALSE;
							$japanonly_all=FALSE;
							$releasedate['uk']=date_to_timestamp(strip_tags($v[3]));
							$ukpublisher=strip_tags($v[1]);
							if ($ukpublisher!='')
								$all_ukpublisher=$ukpublisher;
						}
	
					}
					
					if ($releasedate['uk']!='')
						$release_timestamp=$releasedate['uk'];
					else
					if ($releasedate['us']!='')
						$release_timestamp=$releasedate['us'];
					else
					if ($releasedate['jap']!='')
						$release_timestamp=$releasedate['jap'];
					else
						$release_timestamp=0;
						
					if (($release_timestamp!=0 && $release_timestamp<$all_release_timestamp) || ($release_timestamp!=0 && $all_release_timestamp==0))
						$all_release_timestamp=$release_timestamp;

				}
				
				$pl=get_platform (strip_tags($f[2]));
				$platform=$pl['platform'];
				$platform_id=$pl['id'];
				$generation=$pl['generation'];

				$genre=strip_tags($f[3]);
				foreach ($ter_pos as $k=>$i)
				{
					$sales[$ter_codes[$k]]=floatval(strip_tags($f[$i]));
					$all_sales[$ter_codes[$k]]+=floatval(strip_tags($f[$i]));
				}
				
				if ($releases_needed)				
					$r['entries'][]=array ('original'=>$original, 'platform'=>$platform, 'platform_id'=>$platform_id, 'ukpublisher'=>$ukpublisher, 'uspublisher'=>$uspublisher, 'linkvg'=>$linkvg, 'salesnorthamerica'=>$sales['salesnorthamerica'], 'saleseurope'=>$sales['saleseurope'], 'salesjapan'=>$sales['salesjapan'], 'salesrestofworld'=>$sales['salesrestofworld'], 'salesglobal'=>$sales['salesglobal'], 'releasedate'=>date('Y-m-d 00:00:00',$release_timestamp), 'release_timestamp'=>$release_timestamp, 'japanonly'=>($japanonly?1:0));
				else
					$r['entries'][]=array ('original'=>$original, 'platform'=>$platform, 'platform_id'=>$platform_id, 'linkvg'=>$linkvg, 'salesnorthamerica'=>$sales['salesnorthamerica'], 'saleseurope'=>$sales['saleseurope'], 'salesjapan'=>$sales['salesjapan'], 'salesrestofworld'=>$sales['salesrestofworld'], 'salesglobal'=>$sales['salesglobal']);
					

				$sales_sum+=$sales['salesglobal'];
				$all_releases.=date('Y-m-d 00:00:00',$release_timestamp).'('.$platform.'), ';
			
			}

		}	
		
		if ($found)
		{	
			if ($releases_needed)				
			{
				$r['all_releases']=remove_tail($all_releases,', ');
				$r['uspublisher']=$all_uspublisher;
				$r['ukpublisher']=$all_ukpublisher;
				$r['release_timestamp']=$all_release_timestamp;
				$r['releasedate']=date('Y-m-d 00:00:00',$all_release_timestamp);
				$r['japanonly']=($japanonly_all?1:0);
			}
			
			$r['salesnorthamerica']=$all_sales['salesnorthamerica'];
			$r['saleseurope']=$all_sales['saleseurope'];
			$r['salesjapan']=$all_sales['salesjapan'];
			$r['salesrestofworld']=$all_sales['salesrestofworld'];
			$r['salesglobal']=$all_sales['salesglobal'];
			$r['status']='success';
			return $r;
		} else
			return false;
		
}

	function group_game ($codename)
	{
		$sales_sum=0;
		$all_releases='';
		$all_uspublisher='';
		$all_ukpublisher='';
		
		$all_sales=array();
		
		$all_release_timestamp=0;

		$ter_codes=array('salesnorthamerica', 'saleseurope', 'salesjapan', 'salesrestofworld', 'salesglobal');
		
		$game=q("SELECT * FROM gamesofplatform WHERE codename='".mysql_real_escape_string($codename)."'");
		
		while ($g=mysql_fetch_array($game))
		{
	
			$pl=fq("SELECT * FROM platforms WHERE id=".intval($g['platform_id']));
			$platform=$pl['platform'];
			$platform_id=$pl['id'];
			$platform_full=$pl['display'];

			//$genre=strip_tags($f[3]);

			foreach ($ter_codes as $tc)
			{
				$all_sales[$tc]+=floatval($g[$tc]);
			}
			
			$sales_sum+=$sales['salesglobal'];
			$all_releases.=$g['releasedate'].'('.$platform.'), ';
			
			$rtd=explode(' ',$g['releasedate']);
			$rd=explode('-',$rtd[0]);
			$rt=explode('-',$rtd[1]);
			
			if ($g['releasedate']=='0000-00-00 00:00:00')
				$release_timestamp=0;
			else
				$release_timestamp=mktime($rt[0],$rt[1],$rt[2],$rd[1],$rd[2],$rd[0]);
			
			if (($release_timestamp!=0 && $release_timestamp<$all_release_timestamp) || ($release_timestamp!=0 && $all_release_timestamp==0))
				$all_release_timestamp=$release_timestamp;

			$allrat+=$g['rating']*$g['reviews'];
			$allrev+=$g['reviews'];
			
			$plat.=$platform.', ';
			$plat_full.=$platform_full.', ';
			$plat_id.=$platform_id.' ';
			$dev.=($g['developer']!=''?$g['developer'].($platform!=''?'('.$platform.')':'').', ':'');
			$pub.=($g['uspublisher']!=''?$g['uspublisher'].($platform!=''?'('.$platform.')':'').', ':'');
			$ukpub.=($g['ukpublisher']!=''?$g['ukpublisher'].($platform!=''?'('.$platform.')':'').', ':'');

		}
		
		reset ($ter_codes);
		
		foreach ($ter_codes as $tc)
		{
			$r[$tc]=$all_sales[$tc];
		}

		$plat=remove_tail($plat,', ');
		$plat_full=remove_tail($plat_full,', ');
		$plat_id=' '.mb_trim($plat_id).' ';
		$dev=remove_tail($dev,', ');
		$pub=remove_tail($pub,', ');
		$ukpub=remove_tail($ukpub,', ');
		
		$r['foreign_rating']=$allrat/$allrev;
		$r['foreign_reviews']=$allrev;
		$r['platforms']=$plat;
		$r['platforms_full']=$plat_full;
		$r['platforms_id']=$plat_id;
		$r['developer']=$dev;
		$r['uspublisher']=$pub;
		$r['ukpublisher']=$ukpub;

		$r['all_releases']=remove_tail($all_releases,', ');
		$r['release_timestamp']=$all_release_timestamp;
		$r['releasedate']=date('Y-m-d H:i:s',$all_release_timestamp);
		
		q ("UPDATE gamesofgeneration SET platforms='".mysql_real_escape_string($r['platforms'])."', platforms_full='".mysql_real_escape_string($r['platforms_full'])."', platforms_id='".mysql_real_escape_string($r['platforms_id'])."', uspublisher='".mysql_real_escape_string($r['uspublisher'])."', ukpublisher='".mysql_real_escape_string($r['ukpublisher'])."', developer='".mysql_real_escape_string($r['developer'])."', foreign_rating=".floatval($r['foreign_rating']).", foreign_reviews=".intval($r['foreign_reviews']).", all_releases='".mysql_real_escape_string($r['all_releases'])."', releasedate='".mysql_real_escape_string($r['releasedate'])."', release_timestamp=".intval($r['release_timestamp']).", salesnorthamerica=".floatval($r['salesnorthamerica']).", saleseurope=".floatval($r['saleseurope']).", salesjapan=".floatval($r['salesjapan']).", salesrestofworld=".floatval($r['salesrestofworld']).", salesglobal=".floatval($r['salesglobal'])." WHERE codename='".mysql_real_escape_string($codename)."'");
		
		return $r;
	}


	function echo_platform ($g)
	{
		echo '<div id="platform_'.$g['platform_id'].'" style="border-top: 2px solid #000000; margin-top: 5px;">';
		
		echo '<table width="600"><tr><td>';
		
		shortfield ('Платформа', 'platform['.$g['platform_id'].']', $g['platform']);
		
		/*
		$gen=faq("SELECT generation FROM platforms WHERE platform='".$g['platform']."'","generation");
		
		if ($gen!='')
			echo ' '.$gen.'-е поколение';
		*/
		
		echo ' <span class="dashed red" onClick="delete_platform(\''.$g['codename'].'\', '.$g['platform_id'].');">Удалить</span>';
		echo '</td><td>';
		
		field ('Дата', 'releasedate['.$g['platform_id'].']', $g['releasedate']);
		echo '</td></tr></table>';
		
		
		echo '<table width="400"><tr><td>';
		shortfield ('Разработчик', 'developer['.$g['platform_id'].']', $g['developer']);
		echo '</td><td>';
		shortfield ('Издатель в США', 'uspublisher['.$g['platform_id'].']', $g['uspublisher']);
		echo '</td><td>';
		shortfield ('Издатель в Англии', 'ukpublisher['.$g['platform_id'].']', $g['ukpublisher']);
		echo '</td></tr></table>';

		echo '<table width="280"><tr><td>';
		shortfield ('Рейтинг GR', 'rating['.$g['platform_id'].']', $g['rating']);
		echo '</td><td>';
		shortfield ('Кол-во рецензий', 'reviews['.$g['platform_id'].']', $g['reviews']);
		echo '</td></tr></table>';
		
		echo '<table width="400"><tr><td>';
		shortfield ('США', 'salesnorthamerica['.$g['platform_id'].']', notzero($g['salesnorthamerica']));
		echo '</td><td>';
		shortfield ('Япония', 'salesjapan['.$g['platform_id'].']', notzero($g['salesjapan']));
		echo '</td><td>';
		shortfield ('Остальной мир', 'salesrestofworld['.$g['platform_id'].']', notzero($g['salesrestofworld']));
		echo '</td><td>';
		shortfield ('Европа', 'saleseurope['.$g['platform_id'].']', notzero($g['saleseurope']));
		echo '</td><td>';
		shortfield ('Всего', 'salesglobal['.$g['platform_id'].']', notzero($g['salesglobal']));
		echo '</td></tr></table>';
		
/*		shortfield ('Дата-timestamp', 'date_timestamp['.$g['platform_id'].']', ($g['release_timestamp']==0?'':date('d.m.Y',$g['release_timestamp'])));
*/
		echo '</div>';
	
	}

	$bad_genres=array ('Breeding/Constructing', 'Civilian Plane', 'Compilation', 'Demolition Derby', 'Edutainment', 'Futuristic Jet', 'Futuristic Sub', 'Gambling', 'General', 'Matching', 'Modern Jet', 'Mission-based', 'Miscellanous', 'Hidden Object', 'Music Maker', 'Old Jet', 'On-foot', 'Olympic Sports', 'Other', 'Parlor', 'Party', 'Maze', 'Scrolling', 'Small Spaceship', 'Large Spaceship', 'Snow / Water', 'Stacking', 'Static', 'Stock Car', 'Street', 'Traditional', 'WWI', 'WWII', "Race 'n' Chase", 'Solo beat-em-up', 'Tactical Combat', 'Graphic', 'War', 'Gang beat-em-up', 'Puzzle (ADULT)', 'Games', 'Game Editor', 'Games (ADULT)', 'Media Admin', 'Text (ADULT)', 'Horse Racing');
	
	$genre_from=array ('Race', 'Quiz', 'RPG', 'Sport', 'Vehicle Combat', 'Platform', 'Card Game', 'Board Game');
	
	$genre_to=array ('Racing', 'Trivia / Game Show', 'Role-playing', 'Sports', 'Car Combat', 'Platformer', 'Card Battle', 'Board Games');

	function steal_gamerankings_platform ($linkgr)
	{
		$txt=SelectNode(file_get_contents($linkgr),'div','id="content"');
		$original=mb_trim(strip_tags(SelectNode($txt,'h1')));
		$genres_array=explode(' &raquo; ',strip_tags(SelectNode($txt,'div','class="crumbs"')));
		$pl=get_platform(mb_trim(strip_tags($genres_array[0])));
		
		$genres='';
		for ($i=1; $i<sizeof($genres_array); $i++)
		{
			$gg=mb_trim(strip_tags($genres_array[$i]));
			if ($gg!='' && !in_array($gg, $bad_genres))
			{
				$gg=str_replace($genre_from, $genre_to, $gg);
				$genres.=$gg.', ';				
			}
		}
		
		
		$genres=remove_tail($genres,', ');
		
		$r['platform']=$pl['platform'];
		$r['platform_id']=$pl['id'];
		$r['linkgr']=$linkgr;
		
		$r['genres']=$genres;
		
		$genres_array=array_shift($genres_array);
		
		$ranktxt=mb_trim(SelectNode(between($txt,'<h1>The Ranking</h1>',''),'table'));
		if ($ranktxt!='')
		{
			$r['rating']=floatval(strip_tags(between($ranktxt,'<b>','%</b>')));
			$r['reviews']=intval(strip_tags(between($ranktxt,'Score based on',' review')));
		} else
		{
			$r['rating']=0;
			$r['reviews']=0;
		}
		
		$datetxt=mb_trim(strip_tags(betweens($txt,array('<h1>Description</h1>','','<b>Release Date: ','</b>'))));
		if ($datetxt!='')
		{
			$r['release_timestamp']=date_to_timestamp($datetxt);
			$r['releasedate']=date('Y-m-d H:i:s',$r['release_timestamp']);
		}
		
		return $r;

	}


	function steal_gamerankings ($original)
	{
		$txt=file_get_contents('http://www.gamerankings.com/browse.html?search='.urlencode($original).'&numrev=3');
		$txt=betweens($txt,array('<h1>Game List</h1>','</table>','<table>',''));
		// echo $txt;
		$arr=SelectNodes($txt,'tr');
		$genres='';
		
		foreach ($arr as $ar)
		{
			$a=SelectNodes($ar,'td');
			
			$ahref=SelectNode($a[2],'a');
			$name=strip_tags($ahref);
			$url=between($ahref,'href="','"');
			if ($name==$original)
			{
				$pl=get_platform(mb_trim(strip_tags($a[1])));
				$platform=$pl['platform'];
				$platform_full=$pl['display'];
				$platform_id=$pl['id'];
				$generation=$pl['generation'];
				$linkgr='http://www.gamerankings.com'.$url;
				
				$genre=steal_gamerankings_platform($linkgr);
				if ($genre!='')
					$genres=$genre['genres'];
				
				$dpy=explode(',',mb_trim(strip_tags(between($a[2],'</a>',''))));
				$dpy[0]=mb_trim($dpy[0]);
				$dpy[1]=mb_trim($dpy[1]);
				if (preg_match('/^[0-9]{4}/$',$dpy[0]))
				{
					$developer='';
					$uspublisher='';
					$year=$dpy[0];
				} else
				if (mb_stristr($dpy[0],'/'))
				{
					
					$dp=explode('/',$dpy[0]);
					$developer=$dp[0];
					$uspublisher=$dp[1];
					$year=intval($dpy[1]);
				} else
				{
					$uspublisher=$dpy[0];
					$developer='';
					$year=intval($dpy[1]);
				}
				
				$rat=mb_trim(str_replace('%','',strip_tags(between($a[3],'','<br'))));
				$rev=mb_trim(strip_tags(betweens($a[3],array('<br','','>','Revie'))));
				if ($rat=='n/a' || $rev=='No')
				{
					$rat=0;
					$rev=0;
				} else
				{
					$rat=floatval($rat);
					$rev=intval($rev);
				}
				
				$allrat+=$rat*$rev;
				$allrev+=$rev;
				
				$test=fq("SELECT * FROM gamesofplatform WHERE original='".mysql_real_escape_string($original)."' AND (platform='".mysql_real_escape_string($platform)."' OR platform_id=".mysql_real_escape_string($platform_id).") AND linkgr='".mysql_real_escape_string($linkgr)."'");
				
				if (!$test)
				{
					$realoriginal=faq("SELECT original FROM gamesofgeneration WHERE gamerankings='".mysql_real_escape_string($original)."'","original");
					$test=fq("SELECT * FROM gamesofplatform WHERE original='".mysql_real_escape_string($realoriginal)."' AND (platform='".mysql_real_escape_string($platform)."' OR platform_id=".mysql_real_escape_string($platform_id).") AND linkgr='".mysql_real_escape_string($linkgr)."'");
					
				}

				
				$r['entries'][]=array ('original'=>$original, 'platform'=>$platform, 'platform_id'=>$platform_id, 'developer'=>$developer, 'uspublisher'=>$uspublisher, 'linkgr'=>$linkgr, 'rating'=>$rat, 'reviews'=>$rev, 'exists'=>($test?true:false));
				
				$plat.=$platform.', ';
				$plat_full.=$platform_full.', ';
				$plat_id.=$platform_id.' ';
				$dev.=($developer!=''?$developer.($platform!=''?'('.$platform.')':'').', ':'');
				$pub.=($uspublisher!=''?$uspublisher.($platform!=''?'('.$platform.')':'').', ':'');

			}
		}
		
		$plat=remove_tail($plat,', ');
		$plat_full=remove_tail($plat_full,', ');
		$plat_id=' '.mb_trim($plat_id).' ';
		$dev=remove_tail($dev,', ');
		$pub=remove_tail($pub,', ');
		
		$r['rating']=$allrat/$allrev;
		$r['reviews']=$allrev;
		$r['platforms']=$plat;
		$r['platforms_full']=$plat_full;
		$r['platforms_id']=$plat_id;
		$r['developer']=$dev;
		$r['uspublisher']=$pub;
		$r['genres']=$genres;
		
		return $r;
		
	}
	

function top ($sort, $type, $year_start, $year_end, $count, $start, $platforms='', $genres='', $countries='')
{
		global $loginza_id;
		
		$filters=array('platforms'=>$platforms, 'genres'=>$genres, 'countries'=>$countries);
		
		foreach ($filters as $filter=>$value)
		{
			if ($value!='')
			{
				$arr=explode('-', $value);
				
				$sql[$filter]="";
				foreach ($arr as $ca)
					$sql[$filter].=str_replace('countries','country',$filter)."_id LIKE '% ".mysql_real_escape_string($ca)." %' OR ".str_replace('countries','country',$filter)."_id LIKE '".mysql_real_escape_string($ca)." %' OR ".str_replace('countries','country',$filter)."_id LIKE '% ".mysql_real_escape_string($ca)."' OR ";		
				$sql[$filter]=remove_tail($sql[$filter]," OR ");
			}
		}
		
		if ($count==-1)
		{
			if ($sort=='best' || $sort=='worst')
				$count=faq("SELECT COUNT(*) as count FROM ".($type==0?'movies':'gamesofgeneration')." WHERE codename!='' AND reviews_count>=".($platforms=='' && $genres=='' && $countries==''?MINREVIEWS:1)." AND release_timestamp>=".mktime(0,0,0,1,1,$year_start)." AND release_timestamp<=".mktime(23,59,59,12,31,$year_end)." AND ".($sort=='best'?"bayes>=50":"bayes<50").
				($platforms!=''?" AND (".$sql['platforms'].")":"").
				($genres!=''?" AND (".$sql['genres'].")":"").
				($countries!=''?" AND (".$sql['countries'].")":"")
				,"count");
			else
			if ($sort=='usersbest' || $sort=='usersworst')
				$count=faq("SELECT COUNT(*) as count FROM ".($type==0?'movies':'gamesofgeneration')." WHERE codename!='' AND grades_num>=".($platforms=='' && $genres=='' && $countries==''?MINGRADES:1)." AND release_timestamp>=".mktime(0,0,0,1,1,$year_start)." AND release_timestamp<=".mktime(23,59,59,12,31,$year_end)." AND ".($sort=='usersbest'?"readers_bayes>=50":"readers_bayes<50").
				($platforms!=''?" AND (".$sql['platforms'].")":"").
				($genres!=''?" AND (".$sql['genres'].")":"").
				($countries!=''?" AND (".$sql['countries'].")":"")
				,"count");
			
			/*
			if ($start>0)
			{		
				$plustop=($count-$start>10?10:$count-$start);
				echo '<div id="'.$sort.'_more_top"><div class="page_lists_more"><a href="javascript:void(0)" class="pseudolink" onClick="$(\'#'.$sort.'_more_top\').loadWith(\'/useractions.php?action=more_top&type='.$type.'&count='.$count.'&sort='.$sort.'&year_start='.$year_start.'&year_end='.$year_end.'&start='.($start-$plustop).'&platforms='.$platforms.'&genres='.$genres.'&countries='.$countries.'\');">'.($type==0?'Предыдущи'.ending($plustop,'й','е','е').' '.$plustop.' фильм'.ending($plustop,'','а','ов'):'').($type==1?'Предыдущи'.ending($plustop,'ая','ие','ие').' '.$plustop.' игр'.ending($plustop,'а','ы',''):'').'</a></div></div>';		
			}
			*/

							
		}

		if ($sort=='best' || $sort=='worst')
		{
			$movies=q("SELECT * FROM ".($type==0?'movies':'gamesofgeneration')." WHERE codename!='' AND reviews_count>=".($platforms=='' && $genres=='' && $countries==''?MINREVIEWS:($platforms=='46'?1:MINREVIEWS))." AND release_timestamp>=".mktime(0,0,0,1,1,$year_start)." AND release_timestamp<=".mktime(23,59,59,12,31,$year_end)." AND ".($sort=='best'?"bayes>=50":"bayes<50").
				($platforms!=''?" AND (".$sql['platforms'].")":"").
				($genres!=''?" AND (".$sql['genres'].")":"").
				($countries!=''?" AND (".$sql['countries'].")":"").
				" ORDER BY ".($platforms=='' && $genres=='' && $countries==''?"bayes":"bayes")." ".($sort=='best'?"DESC":"ASC")." LIMIT ".intval($start).",10");

		}
		else
		if ($sort=='usersbest' || $sort=='usersworst')
		{

			$movies=q("SELECT * FROM ".($type==0?'movies':'gamesofgeneration')." WHERE codename!='' AND grades_num>=".($platforms=='' && $genres=='' && $countries==''?MINGRADES:($platforms=='46'?1:MINGRADES))." AND release_timestamp>=".mktime(0,0,0,1,1,$year_start)." AND release_timestamp<=".mktime(23,59,59,12,31,$year_end)." AND ".($sort=='usersbest'?"readers_bayes>=50":"readers_bayes<50").				
				($platforms!=''?" AND (".$sql['platforms'].")":"").
				($genres!=''?" AND (".$sql['genres'].")":"").
				($countries!=''?" AND (".$sql['countries'].")":"")." ORDER BY readers_bayes ".($sort=='usersbest'?"DESC":"ASC")." LIMIT ".intval($start).",10");
		}
		
		$ab=$start;		
		
		echo '
		<ul>';

		while ($m=mysql_fetch_array($movies))
		{
			foreach ($m AS $keym=>$mm)
				$m[strtolower($keym)]=$mm;
			
			// $rating=round($platforms=='' && $genres=='' && $countries==''?$m['bayes']:$m['rating']);
			$rating=round($m['bayes']);
			
			$rating_color=grade_color($rating);
			$reviews_count=$m['reviews_count'];
			
			$user_grade=round($m['readers_bayes']);
			$user_grade_color=grade_color($user_grade);
			$user_grade_count=$m['grades_num'];
			$user_comments_count=$m['comments'];
			
			//if ($type==0)
				$poster=get_image ($m['codename'], $type, 'poster', 's');
			//else
			//	$poster=get_image ($m['original'], $type, 'posters', 's');
				
			if ($type==1)
				$releasedate=date_to_timestamp($m['releasedate']);
			else
			{ 
				if ($m['premiereinrussia']!='')
					$releasedate=date_to_timestamp($m['premiereinrussia']);
				else
					$releasedate=date_to_timestamp($m['worldpremier']);
			}
			
			$genres_text=display_ids ($m['genres_id'], $m['genres'], 'top/'.($type==0?'movies':'').($type==1?'games':'').'/best/alltime/genres');
			
			if ($type==1)
			{
				if ($page=='main')
					$addinfo=display_ids ($m['platforms_id'], $m['platforms_full'], 'top/games/best/alltime/platforms');
				else
					$addinfo=display_ids ($m['platforms_id'], $m['platforms_full'], '');
	
			} else 
			{
				$addinfo='';
			}
			
			if (mb_substr($addinfo,-2)==', ')
				$addinfo=mb_substr($addinfo,0,-2);
			
			$voted_text='';

			if ($loginza_id==620 || $loginza_id==9 || $loginza_id==814 || $loginza_id==3)
			{
				$voted=intval(faq("SELECT grade FROM grades_".($type==0?"movies":"").($type==1?"games":"")." WHERE object_id=".intval($m['id'])." AND user_id=".intval($loginza_id),"grade"));
				$favorite=fq("SELECT * FROM favorites_movies WHERE object_id=".intval($m['id'])." AND user_id=".intval($loginza_id));
				$voted_text='<div class="userqb">'.($voted!=0?'<div class="user_rated" title="Ваша оценка: '.round($voted/10).' из 10">&nbsp;</div>':'').($favorite?'<div class="user_faved active" id="favorite_'.($type==0?'movies':'').($type==1?'games':'').'_'.$m['id'].'" title="Удалить из избранного" onClick="favorites(\''.($type==0?'movies':'').($type==1?'games':'').'\','.$m['id'].');">&nbsp;</div>':'<div id="favorite_'.($type==0?'movies':'').($type==1?'games':'').'_'.$m['id'].'" class="user_faved" title="Добавить в избранное" style="display: none;" onClick="favorites(\''.($type==0?'movies':'').($type==1?'games':'').'\','.$m['id'].');">&nbsp;</div>').'</div>';

			}

			echo '
			<li class="row_'.($ab%2==0?'a':'c').
				($sort=='best' || $sort=='worst'?' site_rating_'.$rating_color:'').
				($sort=='usersbest' || $sort=='usersworst'?' users_rating_'.$user_grade_color:'').
				' clearfix">
				<div class="left"><a href="/'.($type==0?'movies':'').($type==1?'games':'').'/'.$m['codename'].'/"><div class="cover" style="background-image: url(\''.$poster.'\');"></div></a><h4 title="'.($reviews_count!=0?'<p>'.$reviews_count.' рецензи'.ending($reviews_count,'я','и','й').'</p>':'').'">'.
				($sort=='best' || $sort=='worst'?($rating==0 && $reviews_count<1?'−':$rating):'').
				($sort=='usersbest' || $sort=='usersworst'?($user_grade==0 && $user_grade_count<1?'−':$user_grade):'').
				'</h4><h2><a href="/'.($type==0?'movies':'').($type==1?'games':'').'/'.$m['codename'].'/">'.($m['russian']!=''?$m['russian']:$m['original']).'</a></h2>'.($m['original']!='' && $m['russian']!=''?'<p>'.$m['original'].'</p>':'').
				($m['country']!='' || $releasedate!=0?
				'<p><i>'.($m['country']!=''?$m['country']:'').($releasedate!=0?($m['country']!=''?', ':'').rus_date($releasedate):'').
				'</i></p>':'').
				($addinfo!=''?'<p>'.$addinfo.'</p>':'').
				($genres_text!=''?'<p>'.$genres_text.'</p>':'').'
				</div>
				<div class="center">'.
				($reviews_count!=0?'<p>'.$reviews_count.' рецензи'.ending($reviews_count,'я','и','й').'</p>':'').
				'<b>Оценка '.
				($sort=='best' || $sort=='worst'?'пользователей:<span class="users_rating_'.$user_grade_color.'">'.($user_grade==0 && $user_grade_count<1?'−':$user_grade):'').
				($sort=='usersbest' || $sort=='usersworst'?'русских изданий:<span class="site_rating_small_'.$rating_color.'">'.($rating==0 && $reviews_count<1?'−':$rating):'').
				'</span></b>'.($user_comments_count!=0?'<p><a href="/'.($type==0?'movies':'').($type==1?'games':'').'/'.$m['codename'].'/#comments">'.$user_comments_count.' комментари'.ending($user_comments_count,'й','я','ев').'</a></p>':'<a href="/'.($type==0?'movies':'').($type==1?'games':'').'/'.$m['codename'].'/#comments">Начать обсуждение</a>').'</div>
				<div class="right">'.($ab+1).'</div>'.$voted_text.'</li>';
			
			$ab++;
		}

		$plus=($count-$ab>10?10:$count-$ab);
		
		echo '
		</ul>'.($count>$ab?'<div  id="'.$sort.'_more"><div class="page_lists_more"><a href="javascript:void(0)" class="pseudolink" onClick="$(\'#'.$sort.'_more\').loadWith(\'/useractions.php?action=more_top&type='.$type.'&count='.$count.'&sort='.$sort.'&year_start='.$year_start.'&year_end='.$year_end.'&start='.$ab.'&platforms='.$platforms.'&genres='.$genres.'&countries='.$countries.'\'); change_url_start('.$ab.');">'.($type==0?'Следующи'.ending($plus,'й','е','е').' '.$plus.' фильм'.ending($plus,'','а','ов'):'').($type==1?'Следующ'.ending($plus,'ая','ие','ие').' '.$plus.' игр'.ending($plus,'а','ы',''):'').'</a></div></div>':'');
		
		return $count;
	}
	
	function ending ($num, $e1,$e2,$e3)
	// Формирует окончание в соответствии с числом
	// $num - число
	// $e1,$e2,$3 - окончания существительного. Например - 1 мяч (''), 2 мяча ('а'), 6 мячей ('ей')
	{
		if ((($num%100)>10)&&(($num%100)<20)) return $e3;
		if (($num%10)==1) return $e1;
		if ((($num%10)>1)&&(($num%10)<5)) return $e2;
		return $e3;
	}



	function grade_color ($grade)
	{
		$grade=round($grade);
		if ($grade==0) return 'none';
		else		
			if ($grade<25) return 'awful';
		else
			if ($grade<50) return 'bad';
		else
			if ($grade<75) return 'average';
		else
			return 'good';
	}
		
	function wisdom_level ($wisdom)
	{
		if ($wisdom==0) return 0;
		else
		if ($wisdom<10) return 1;
		else
		if ($wisdom<20) return 2;
		else return 3;
	}
	
	function wisdom_level_word ($wisdom_level)
	{
		if ($wisdom_level==0) return 'none';
		else
		if ($wisdom_level==1) return 'light';
		else
		if ($wisdom_level==2) return 'moderate';
		else return 'heavy';
	}
	
	function nice_integer ($number)
	{
		return number_format($number, 0, ',', ' ');
	}
	
	function lastreviews ($type, $sort, $count, $start, $num=5)
	{
		$ab=$start;
		echo '<ul>';
		$reviews_games=q("SELECT * FROM reviews WHERE codename!='' AND critic_id!='' AND publication_id!=0 AND type=".intval($type)." AND grade!='' AND rating>0 ORDER BY ".($sort=='date'?"timestamp DESC":"")." LIMIT ".intval($start).", ".intval($num));
	
		while ($rm=mysql_fetch_array($reviews_games))
		{
			$publ=fq("SELECT * FROM publications WHERE id=".intval($rm['publication_id'])."");
			$mov=fq("SELECT * FROM ".($type==0?"movies":"gamesofgeneration")." WHERE codename='".mysql_real_escape_string($rm['codename'])."'");
			$rt=$rm['rating'];
			
			if (sizeof(explode(' ',trim($rm['critic_id'])))>1)
				$word='оценивают';
			else
				$word='оценивает';
			
			//
			echo '<li class="row'.($ab%2==0?'a':'b').'"><a href="/reviews/'.$rm['id'].'/"><b>'.(trim($rm['critic_id'])==0 || trim($rm['critic_id'])==''?$publ['name']:strip_tags(get_critics_names($rm['critic_id'])).'</b> ('.$publ['name'].')').' <u>'.$word.'</u> '.($type==0?"фильм":"игру").' <b>'.($type==0?$mov['russian']:$mov['original']).'</b> <nobr>на <span class="lastreviews site_rating_small_'.grade_color($rt).'">'.($rm['grade']=='#хорошо' || $rm['grade']=='#отлично' || $rm['grade']=='#плохо' || $rm['grade']=='#так себе'?utf8_ucfirst(mb_substr($rm['grade'],1)):$rm['grade'].($publ['topgrade']<100 && $publ['topgrade']!=0?' из '.$publ['topgrade']:'')).'</span></nobr></a></li>';
				//

/*
			echo '
				<li><a href="/reviews/'.$rm['id'].'/">'.(trim($rm['critic_id'])==0 || trim($rm['critic_id'])==''?$publ['name']:strip_tags(get_critics_names($rm['critic_id'])).' ('.$publ['name'].')').' '.$word.' '.($type==0?"фильм":"игру").' <span>'.($type==0?$mov['russian']:$mov['original']).'</span> <nobr>на <span class="lastreviews site_rating_small_'.grade_color($rt).'">'.($rm['grade']=='#хорошо' || $rm['grade']=='#отлично' || $rm['grade']=='#плохо' || $rm['grade']=='#так себе'?utf8_ucfirst(mb_substr($rm['grade'],1)):$rm['grade'].($publ['topgrade']<100 && $publ['topgrade']!=0?' из '.$publ['topgrade']:'')).'</span></nobr></a></li>';
*/
			$ab++;
		}
		
		echo '</ul>';
		
		if ($count>$ab)
			echo '<div class="reviews_list_more" id="reviewslist_more_'.$type.'"><a href="javascript:void(0);" class="pseudolink" onClick="$(\'#reviewslist_more_'.$type.'\').hide().css(\'text-align\', \'left\').loadWith(\'/useractions.php?action=lastreviews&type='.$type.'&sort='.$sort.'&count='.$count.'&start='.$ab.'&num='.$num.'\');">Ещё '.($count-$ab>$num?$num:$count-$ab).' рецензи'.ending($count-$ab>$num?$num:$count-$ab,'я','и','й').'</a></div>';
		
	}
	
	
	function searchlist ($string, $page, $count, $start, $num=5, $morenum=10)
	{
		if ($page=='movies')
			$type=0;
		else
		if ($page=='games')
			$type=1;
			
		$movies=q("SELECT * FROM ".($type==0?"movies":"gamesofgeneration")." WHERE codename!='' AND codename IS NOT NULL AND (original LIKE '%".mysql_real_escape_string($string)."%' OR russian LIKE '%".mysql_real_escape_string($string)."%') ORDER BY rating DESC LIMIT ".intval($start).", ".intval($num));

		echo '<ul>';
		
		$ab=$start;

		while ($m=mysql_fetch_array($movies))
		{

			if ($type==0)
				$addinfo=display_ids ($m['genres_id'], $m['genres']);
			else
				$addinfo=display_ids ($m['platforms_id'], $m['platforms_full']);

			$rat_color=grade_color($m['rating']);
						
			if ($type==1 && $m['releasedate']!='')
				$prem=rus_date(date_to_timestamp($m['releasedate']));
			else
			if ($type==0 && $m['premierinrussia']!='')
				$prem=rus_date(date_to_timestamp($m['premierinrussia']));
			else $prem=0;
						
			echo '<li>
					<a href="/'.($type==0?'movies':'games').'/'.$m['codename'].'/" class="cover" style="background-image: url(\''.get_image ($m['codename'], $type, 'poster', 's').'\');"><span class="rating_small_'.grade_color($m['rating']).'">'.($m['rating']==0 && $m['reviews_count']==0?'&minus;':$m['rating']).'</span></a>
					<div class="info"><h5><a href="/'.($type==0?'movies':'games').'/'.$m['codename'].'/">'.($m['russian']!=''?$m['russian']:$m['original']).'</a></h5>'.($m['original']!='' && $m['original']!=$m['russian']?$m['original']:'').'</h6>'.
					($prem!=''?'<p>'.$prem.'</p>':'').
					($addinfo!=''?'<p>'.$addinfo.'</p>':'').
					($m['reviews_count']>0?'<p>'.$m['reviews_count'].' рецензи'.ending($m['reviews_count'],'я','и','й').'</p>':'').
					'</div></li>';
			$ab++;
		}
		
	$plus=($count-$ab>$morenum?$morenum:$count-$ab);
		
	echo '</ul>';
		
	echo ($count>$ab?'<div id="'.$page.'_more"><div class="search_more"><a href="javascript:void(0)" class="pseudolink" onClick="$(\'#'.$page.'_more\').loadWith(\'/useractions.php?action=searchlist&page='.$page.'&count='.$count.'&start='.$ab.'&num='.$morenum.'&morenum='.$morenum.'&string='.urlencode($string).'\');">Ещё '.$plus.($page=='movies'?' фильм'.ending($plus,'','а','ов'):'').($page=='games'?' игр'.ending($plus,'а','ы',''):'').'</a></div></div>':'');


	}
	
	
function searchlist_authors ($string, $page, $count, $start, $num=5, $morenum=10)
{

	echo '
	<ul>';
	
	$publications=q("SELECT * FROM ".mysql_real_escape_string($page)." WHERE name LIKE '%".mysql_real_escape_string($string)."%' OR aka LIKE '%".mysql_real_escape_string($string)."%' ORDER BY readers_grade DESC LIMIT ".intval($start).", ".intval($num));
	
	$ab=$start;
	
	while ($p=mysql_fetch_array($publications))
	{
		$wisdom_level=wisdom_level($p['wisdom_count']);
		
		if ($page=='critics')
		{
			$work_places=explode('<->',$p['work_places']);
			if (sizeof($work_places)>0)
			{
				$work_max_reviews=0;
				$work_max=0;
				$work_latest_review=0;
				$work_latest=0;

				foreach ($work_places as $work_place)
				{
					$work=explode ('#',$work_place);
					if ($work[3]>$work_max_reviews)
					{
						$work_max=$work[0];
						$work_max_reviews=$work[3];
					}				
					if ($work[2]>$work_latest_review)
					{
						$work_latest=$work[0];
						$work_latest_review=$work[2];
					}				
				}
				$work_max_name=faq ("SELECT name FROM publications WHERE id=".intval($work_max),"name");
				if ($work_max_name=='') $work_max=0;
				
				if ($work_max!=$work_latest)
				{
					$work_latest_name=faq ("SELECT name FROM publications WHERE id=".intval($work_latest),"name");
					if ($work_latest_name=='') $work_latest=0;
				} else $work_latest_name=$work_max_name;

				
				
			} else {
				$work_max_name='';
				$work_max=0;
			}
		}
		
		
		
		echo '<li>
			<a href="/'.$page.'/'.$p['id'].'/" class="cover" style="background-image: url(\''.($p['image']!=0?'/'.$page.'/'.$p['id'].'/small.jpg':'/i/nophoto.png').'\');"><span class="rating_small_'.grade_color($p['readers_grade']).'">'.nice_grade($p['readers_grade']).'</span></a>
			<div class="info"><h5><a href="/'.$page.'/'.$p['id'].'/">'.$p['name'].'</a><span class="search_wisdom_grade" style="'.($wisdom_level>0?'background-image: url(\'/i/wisdom_grade'.$wisdom_level.'.png\');" title="'.($page=='publications'?'Издание':'Критик').' '.($wisdom_level==1?'умеренной':'').($wisdom_level==2?'повышенной':'').($wisdom_level==3?'исключительной':'').' «мудрости»':'').'">&nbsp;</span></h5>'.
		($page=='critics'?
			($p['aka']!=''?'<p style="font-style: italic;">'.str_replace('#',', ',$p['aka']).'</p>':'').
			($work_max!=0?'<p>'.$work_max_name.'</a>'.($work_latest!=0 && $work_latest!=$work_max?', '.$work_latest_name.'</a>':''):''):'').
			($p['reviews_count']>0?'<p>'.$p['reviews_count'].' рецензи'.ending($p['reviews_count'],'я','и','й').'</p>':'').
			'</li>';
		$ab++;
	}

	$plus=($count-$ab>$morenum?$morenum:$count-$ab);
		
	echo '</ul>';
	
	echo ($count>$ab?'<div id="'.$page.'_more"><div class="search_more"><a href="javascript:void(0)" class="pseudolink" onClick="$(\'#'.$page.'_more\').loadWith(\'/useractions.php?action=searchlist_authors&page='.$page.'&count='.$count.'&start='.$ab.'&num='.$morenum.'&morenum='.$morenum.'&string='.urlencode($string).'\');">Ещё '.$plus.($page=='critics'?' критик'.ending($plus,'','а','ов'):'').($page=='publications'?' издани'.ending($plus,'е','я','й'):'').'</a></div></div>':'');

}
	
	
	function mainlist ($page, $type, $sort, $count, $start, $num=6)
	{
		global $loginza_id;
		
		// mktime()-2600000 — это месяц
		/*
		$movies=q("SELECT * FROM ".($type==0?"movies":"gamesofgeneration")." ".
		($sort=='date'?"WHERE release_timestamp<=".strtotime("next sunday")." and codename!=''".($page=='main'?" AND reviews_count>0":"")." ORDER BY release_timestamp DESC":"").
		($sort=='bestmonth'?"WHERE release_timestamp>=".(mktime()-10000000)." AND release_timestamp<=".mktime()." ORDER BY rating DESC":"")." LIMIT $start, $num");
		*/
		if ($count<0)
		{
			$count=faq("SELECT COUNT(*) as count FROM ".($type==0?"movies":"gamesofgeneration")." ".
			($sort=='date'?"WHERE release_timestamp<=".strtotime("next sunday")." and codename!='' AND reviews_count>0 ORDER BY release_timestamp DESC":"").
			($sort=='bestmonth'?"WHERE release_timestamp>=".(mktime()-2600000*2)." AND release_timestamp<=".mktime()." AND reviews_count>=".MINREVIEWS." ORDER BY rating DESC":""),"count");

		}
		
		
		
		$movies=q("SELECT * FROM ".($type==0?"movies":"gamesofgeneration")." ".
		($sort=='date'?"WHERE release_timestamp<=".strtotime("next sunday")." and codename!='' AND reviews_count>0 ORDER BY release_timestamp DESC":"").
		($sort=='bestmonth'?"WHERE release_timestamp>=".(mktime()-2600000*2)." AND release_timestamp<=".mktime()." AND reviews_count>=".MINREVIEWS." ORDER BY rating DESC":"")." LIMIT ".intval($start).", ".intval($num));

		
		$ab=$start;

		echo '<ul>';

		while ($m=mysql_fetch_array($movies))
		{
		
			if ($type==0)
				$addinfo=display_ids ($m['genres_id'], $m['genres'], 'top/'.($type==0?'movies':'').($type==1?'games':'').'/best/alltime/genres');
			else
			if ($type==1)	
			$addinfo=display_ids ($m['platforms_id'], $m['platforms_full'], 'top/'.($type==0?'movies':'').($type==1?'games':'').'/best/alltime/platforms');

			$rat_color=grade_color($m['rating']);
						
			if ($type==1 && $m['releasedate']!='')
				$prem=rus_date(date_to_timestamp($m['releasedate']));
			else
			if ($type==0 && $m['premierinrussia']!='')
				$prem=rus_date(date_to_timestamp($m['premierinrussia']));
			else $prem=0;
					
			echo '
					<li'.($page=='main'?' class="site_rating_'.$rat_color.'"':'').'>
					<a href="/'.($type==0?'movies':'games').'/'.$m['codename'].'/"><div class="cover" style="background-image: url(\''.get_image ($m['codename'], $type, 'poster', 's').'\');"></div></a><h4'.($page=='movies'?' class="site_rating_'.grade_color($m['rating']).'"':'').'>'.($m['rating']==0 && $m['reviews_count']==0?'&minus;':round($m['rating'])).'</h4>
					<h2><a href="/'.($type==0?'movies':'games').'/'.$m['codename'].'/">'.($type==0?$m['russian']:$m['original']).'</a></h2>'.
					($page=='movies' && $sort=='date' && ($m['country']!='' || $prem!='')?
				'<p><span class="parent_list_important">'.($m['country']!=''?$m['country']:'').($prem!=''?($m['country']!=''?', ':'').$prem.'</span></p>':''):'').
					'<p>'.$addinfo.'</p>
					<p>'.($m['reviews_count']>0?$m['reviews_count'].' рецензи'.ending($m['reviews_count'],'я','и','й'):'<a href="#mf_default" class="main_form" data-fancybox-group="main_form">Добавить рецензию</a>').'</p>'.
					($page=='main'?'<b>Оценка пользователей:<span class="users_rating_'.grade_color($m['readers_grade']).'">'.($m['readers_grade']==0 && $m['grades_num']==0?'&minus;':round($m['readers_grade'])).'</span></b>':'').
					($page=='movies' && $sort=='bestmonth'?'<p><u>Оценка пользователей:<span class="users_rating_small_'.grade_color($m['readers_grade']).'">'.($m['readers_grade']==0 && $m['grades_num']==0?'&minus;':round($m['readers_grade'])).'</span></u></p>
					<div class="parent_list_pos">'.($ab+1).'</div>':'').
					'</li>
			';
			$ab++;
		}
		
		echo '
				</ul>';

		$plus=($count-$ab>$num?$num:$count-$ab);

		if ($count>$ab)
			echo '<div class="titles_list_more" id="mainlist_more_'.$sort.'_'.$type.'"><a href="javascript:void(0);" class="pseudolink" onClick="$(\'#mainlist_more_'.$sort.'_'.$type.'\').html(\'&nbsp;\').css(\'text-align\', \'left\').loadWith(\'/useractions.php?action=mainlist&page='.$page.'&type='.$type.'&sort='.$sort.'&count='.$count.'&start='.$ab.'&num='.$plus.'\');">Ещё '.$plus.' '.
		($sort=='date'?'новин'.ending($plus,'ка','ки','ок'):($type==0?'фильм'.ending($plus,'','а','ов'):'').($type==1?'игр'.ending($plus,'а','ы',''):'')).
		'</a></div>';

	}

	
	function boxlist ($type, $date, $count, $start, $num=10)
	{
	
		echo '<ul>';
		
		if ($type==0)
			$lastbox=q("SELECT * FROM boxofficerussia WHERE date='".mysql_real_escape_string($date)."' ORDER BY place ASC, gross DESC LIMIT ".intval($start).", ".intval($num));
		else
		if ($type==1)
			$lastbox=q("SELECT * FROM boxofficegames WHERE date='".mysql_real_escape_string($date)."' ORDER BY place ASC, gross DESC LIMIT ".intval($start).", ".intval($num));
		
		$ab=$start;
		while ($lb=mysql_fetch_array($lastbox))
		{
			if ($lb['codename']!='')
			{
				if ($type==0)
				{
					$m=fq("SELECT * FROM movies WHERE codename='".mysql_real_escape_string($lb['codename'])."'");
					$title=$m['russian'];
					$rating=$m['rating'];
					$user_rating=$m['readers_grade'];
				}
				else		
				if ($type==1)
				{
					$m=fq("SELECT * FROM gamesofgeneration WHERE codename='".mysql_real_escape_string($lb['codename'])."'");
					$title=$m['original'];
					$rating=$m['rating'];
					$user_rating=$m['readers_grade'];									
				}
			} else
			{
				if ($type==0)
					$title=nice_russian($lb['russian']);
				else		
				if ($type==1)
					$title=$lb['original'];
				$rating=0;
				$user_rating=0;
			}
			
			
			echo '
					<li>'.
						($lb['codename']!=''?'<a href="/'.($type==0?'movies':'').($type==1?'games':'').'/'.$lb['codename'].'/">':'').'<div class="cover" style="background-image: url(\''.get_image ($lb['codename'], $type).'\');"></div>'.($lb['codename']!=''?'</a>':'').
						'<div class="parent_list_box">'.($type==0?'$'.number_format($lb['gross']/1000000,2,',',' ').'<span>млн</span>':'').($type==1?number_format($lb['gross']/1000000,3,',',' ').'<span>млн копий</span>':'').'</div>
						<h2>'.($lb['codename']!=''?'<a href="/'.($type==0?'movies':'').($type==1?'games':'').'/'.$lb['codename'].'/">'.$title.'</a>':$title).'</h2>
						<p><span class="parent_list_important_box">Всего за '.$lb['weeks'].' нед.: '.($type==0?'$'.number_format($lb['allgross']/1000000,2,',',' ').' млн':'').($type==1?number_format($lb['allgross'],0,',',' ').' копи'.ending($lb['allgross'],'я','и','й'):'').'</span></p>
						<p><u>Рейтинг:<span class="site_rating_small_'.grade_color($rating).'">'.nice_grade($rating).'</span></u></p>
						<p><u>Оценка пользователей:<span class="users_rating_small_'.grade_color($user_rating).'">'.nice_grade($user_rating).'</span></u></p>
						<div class="parent_list_pos">'.$lb['place'].'</div>
					</li>';
			$ab++;
		}
	
		echo '
				</ul>';
	
		if ($count>$ab)
			echo '<div class="titles_list_more" id="boxlist_more_'.$type.'"><a href="javascript:void(0);" class="pseudolink" onClick="$(\'#boxlist_more_'.$type.'\').hide().css(\'text-align\', \'left\').loadWith(\'/useractions.php?action=boxlist&type='.$type.'&date='.$date.'&count='.$count.'&start='.$ab.'&num='.$num.'\');">Ещё '.($count-$ab>$num?$num:$count-$ab).' позици'.ending($count-$ab,'я','и','й').'</a></div>';
	
	/*
		echo '
				<div class="titles_list_more"><a href="javascript:void(0)" class="pseudolink">Предыдущая неделя</a>&bull;<a href="#" class="link">Весь бокс-офис</a></div>';
	*/
		
	}


	function nice_russian ($russian)
	{
		$russian=mb_trim($russian);
		$rus=explode(' ',$russian);
		$nice=utf8_ucfirst(tolower($rus[0]));
		for ($i=1; $i<sizeof($rus); $i++)
		{
			if ($rus[$i]=='3D')
			{
				$nice.=' '.$rus[$i];
			}
			else
			{
				$pl=mb_substr($rus[$i-1],-1);
				if ($pl=='.' || $pl=='-' || $pl=='!' || $pl=='?' || $pl==':' || $pl==';')
					$nice.=' '.tolower($rus[$i],1);
				else
					$nice.=' '.tolower($rus[$i]);
			}
			
		}
		return $nice;
		
	}
	
	function transform_text ($text, $header)
	{
		$text=preg_replace ('/\[image=([^ \]]+)\]/','<div class="blog_img_center"><img src="\\1" alt="'.$header.'" title="'.$header.'"></div>',$text);
		$text=preg_replace ('/\[image=([^ ]+) +([^]]+)\]/','<div class="blog_img_center"><img src="\\1" alt="\\2" title="\\2"></div>',$text);
		$text=preg_replace ('/\[imageleft=([^ \]]+)\]/','<div class="blog_img_left"><img src="\\1" alt="'.$header.'" title="'.$header.'"></div>',$text);
		$text=preg_replace ('/\[imageleft=([^ ]+) +([^]]+)\]/','<div class="blog_img_left"><img src="\\1" alt="\\2" title="\\2"></div>',$text);
		$text=preg_replace ('/\[imageright=([^ \]]+)\]/','<div class="blog_img_right"><img src="\\1" alt="'.$header.'" title="'.$header.'"></div>',$text);
		$text=preg_replace ('/\[imageright=([^ ]+) +([^]]+)\]/','<div class="blog_img_right"><img src="\\1" alt="\\2" title="\\2"></div>',$text);
		return Markdown($text);
	}
	
	function external_link ($links)
	{
		$links_array=explode(',',$links);
		$links_text='';
		foreach ($links_array as $link)
		{
			$link=trim($link);
			if (mb_strstr($link,'#'))
			{
				$lnk=explode('#',$link);
				$links_text.='<a href="'.$lnk[0].'" rel="nofollow" target="_blank">'.$lnk[1].'</a>, ';	
			}
			else
				$links_text.='<a href="'.$link.'" rel="nofollow" target="_blank">'.$link.'</a>, ';
		}
		return remove_tail ($links_text,', ');
		
	}

	function newskg ($type, $sort)
	{
		$ab=$start;
		echo '<ul>';
		$reviews_games=json_decode(file_get_contents('http://www.kino-govno.com/api_json.php?action=getnews&section='.($type==0?'movies':($type==1?'games':''))),true);
			
		foreach ($reviews_games as $rm)
		{
			echo '<li class="row'.($ab%2==0?'a':'b').'"><a href="'.$rm['url'].'/" target="_blank"><b>'.$rm['header'].'</b></a></li>';
			$ab++;
		}
		
		echo '</ul>';
		
		/*
		if ($count>$ab)
			echo '<div class="reviews_list_more" id="reviewslist_more_'.$type.'"><a href="javascript:void(0);" class="pseudolink" onClick="$(\'#reviewslist_more_'.$type.'\').hide().css(\'text-align\', \'left\').loadWith(\'/useractions.php?action=lastreviews&type='.$type.'&sort='.$sort.'&count='.$count.'&start='.$ab.'&num='.$num.'\');">Ещё '.($count-$ab>$num?$num:$count-$ab).' рецензи'.ending($count-$ab>$num?$num:$count-$ab,'я','и','й').'</a></div>';
		*/
		
	}

	function notzero($num)
	{
		if ($num==0)
			return '';
		else
			return $num;
	}
	
	
	function sort_gross ($a, $b)
	{
	    if ($a['gross'] == $b['gross']) {
	        return 0;
	    }
	    return ($a['gross'] > $b['gross']) ? -1 : 1;
	}
	
	function get_vgchartz_chart ($url)
	{

		mb_internal_encoding("UTF-8");
	
		$box_text_first=betweens(file_get_contents($url),array ('<div id="chart_body">','<!-- end chart_body -->'));
		$box_text=betweens($box_text_first,array ('<div class="addClear">',''));
	
		preg_match_all('/<tr.*?>.*?<td>([0-9]+)<\/td>.*?<a href=".*?" style=".*?">(.+?)<\/a>.+?\(<a href=".*?">(.+?)<\/a>\).*?<td align="right">([0-9\,\.]+)<\/td>.*?<td align="right">([0-9\,\.]+)<\/td>.*?<td align="center" width=".*?">([0-9\-\,]+)<\/td>?/s',$box_text, $matches);
		
		preg_match ('/<select name="date".*?><option.*?selected.*?>(.+?)<\/option>/s',$box_text_first,$date);
		
		$timestamp=date_to_timestamp($date[1]);
		
		$date=date('dmy',$timestamp);
		
		$typetxt=utf8_lowercase(betweens ($box_text_first,array('<table class="chart_region_selector">','</table>', ' class="selected">', '</a>')));

		switch ($typetxt) {
		    case 'global':
		        $type=0;
		        break;
		    case 'usa':
		        $type=1;
		        break;
		    case 'russia':
		        $type=2;
		        break;
		    case 'europe':
		        $type=3;
		        break;
		    case 'japan':
		        $type=4;
		        break;
		    case 'uk':
		        $type=5;
		        break;
		    case 'germany':
		        $type=6;
		        break;
		    case 'france':
		        $type=7;
		        break;
		}
		

		$ft=fq("SELECT id from boxofficegames_text WHERE timestamp=$timestamp AND type=".intval($type).";");
		if (!$ft) q("INSERT INTO boxofficegames_text SET text='', type=".intval($type).", timestamp=$timestamp, date='".$date."'");
	
		q("DELETE FROM boxofficegames WHERE type=".intval($type)." AND timestamp=$timestamp;");
	
		$ai=faq("SELECT id FROM boxofficegames ORDER BY id DESC LIMIT 1","id")+1;
		q ("ALTER TABLE boxofficegames AUTO_INCREMENT=$ai");

		$i=0;
			
		foreach ($matches[1] as $key=>$ma)
		{
			$original=trim(strip_tags($matches[2][$key]));
			$platform_ar=get_platform(trim(strip_tags($matches[3][$key])));
			$platform=$platform_ar['id'];
			$gross=preg_replace('/[^0-9]+/','',trim(strip_tags($matches[4][$key])));
			$allgross=preg_replace('/[^0-9]+/','',trim(strip_tags($matches[5][$key])));
			$week=preg_replace('/[^0-9]+/','',trim(strip_tags($matches[6][$key])));
						
			$codename=get_codename ('', $original, 1);
			
			if ($codename=='fifa14' && $platform==55)
				$codename='fifa14nextgen';

				$box[$i]['codename']=$codename;
				$box[$i]['original']=$original;
				$box[$i]['platform']=$platform;
				$box[$i]['gross']=$gross;
				$box[$i]['allgross']=$allgross;
				$box[$i]['week']=$week;
				$i++;
			
		}
	
		usort($box,'sort_gross');
	
		$sum=0;
	
		$i=0;
		
		$first_codename='';
		$first_gross=0;
		
		foreach ($box as $b)
		{
			
			$codename=$b['codename'];
			
			if ($codename=='')
			{
				$testoriginal=fq("SELECT * FROM boxofficegamesworld WHERE original='".mysql_real_escape_string($b['original'])."' AND original!='' ORDER BY id DESC");
				if ($testoriginal)
			 		$codename=$testoriginal['codename'];
			}
	
			$i++;
			
			if ($first_gross==0)
			{
				$first_gross=$b['gross'];
				$first_codename=$codename;
			}
			
			q ("INSERT INTO boxofficegames SET place=".intval($i).", codename='$codename', gross='$b[gross]', allgross='$b[allgross]', platform='$b[platform]', timestamp=$timestamp, weeks='$b[week]', original='".mysql_real_escape_string($b['original'])."', type=".intval($type).";");
		
			$sum+=$b['gross'];
		}
	
	 	q ("UPDATE boxofficegames_text SET sum='$sum', codename='".mysql_real_escape_string($first_codename)."', gross=".intval($first_gross)." WHERE timestamp=$timestamp;");
	 	
	 	return json_encode(array('date'=>$date, 'type'=>$type));
		
	}

?>