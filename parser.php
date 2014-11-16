<?php
/* ==========================
+1) AG.RU, http://ag.ru/reviews/p1
+2) 3DNews.ru, http://www.3dnews.ru/games/
+3) GameMag.ru, http://gamemag.ru/reviews/list/1
+4) Gamer-Info.com, http://gamer-info.com/reviews/1/
+5) GamerPro.ru, http://gamerpro.ru/review/?r=1
+6) Games.Cnews.ru, http://games.cnews.ru/articles/2/1index.html
+7) GameTech.ru, http://www.gametech.ru/reviews/?page=1
+8) gameway.com.ua, http://gameway.com.ua/review_category/reviews/page/1
+9) Игромания.ру, http://www.igromania.ru/articles/?emul=1&section=37&page=1
+10) Виртуальные радости, http://vrgames.by/reviews?page=1
+11) Игры@mail.ru, http://games.mail.ru/pc/articles/review/?sort=0&platform=0&ugc=&page=1
+12) PS3-noizless, http://ps3.noizless.ru/category/review/
+13) Stopgame, http://stopgame.ru/review/new/p1
+14) Greatgamer, http://greatgamer.ru/articles/reviews/page_1/
+15) Gamebomb, http://gamebomb.ru/reviews?page=1
-16) Канобу, http://kanobu.ru/articles/resident-evil-revelations-hd-365815/
========================== */

include ("core.php");
include ("core_ajax.php");
include ("strfunc.php");

mb_internal_encoding("UTF-8");

con ();

$url=$_GET['url'];
$justshow=$_GET['justshow'];
$review_id=$_GET['review_id'];
if ($review_id=='')
{

	$text=file_get_contents($url);
	//
	if (mb_strpos($url,'ng.ru')!==FALSE)
	{
	
		$a['publication']='Независимая газета';
		$a['publication_id']=194;
		
		//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;','&#8212;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...','—'),
			$text);		
		//Название
		$a['title']=strip_tags(mb_trim(SelectNode($text,'h1','class="htitle black"'))).'. '.strip_tags(mb_trim(SelectNode($text,'p','class="anonce"')));
		$a['russian']=mb_trim(between($a['title'],'«','»'));
		if ($a['russian']=='')
			$a['russian']=mb_trim(between($a['title'],'"','"'));

		
		//Время
		$tmp=before_first_str(strip_tags(SelectNode($text,'li','class="current_date')),'.').'.';
		$a['date']=before_first_str(mb_trim(strip_tags(SelectNode($text,'p','class="info"')),' '));
		
		//Автор
		$a['author']=mb_trim(strip_tags(RemoveNode(SelectNode($text,'p','class="author"'),'span','class="txt"')));
		
		//Текст
		$a['text']=RemoveNodes(RemoveNodes(SelectNode($text,'article'),'table'),'span');
		//echo $a['text'];
		$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
		
		//На что рецензия
		$a['type']=0;
		
		$a['issue']=$url;


	} else
	
	//Вести FM
	if (mb_strpos($url,'radiovesti.ru')!==FALSE)
	{
		$a['publication']='Вести FM';
		$a['publication_id']=278;
		
		//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;','&#8212;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...','—'),
			$text);		
		//Название
		$a['title']=strip_tags(mb_trim(SelectNode($text,'h1')));
		$a['russian']=mb_trim(between($a['title'],'"','"'));
		
		//Время
		$tmp=before_first_str(strip_tags(SelectNode($text,'li','class="current_date')),'.').'.';
		$a['date']=mb_trim(strip_tags($tmp));
		
		//Автор
		$a['author']=mb_trim(strip_tags(SelectNode(SelectNode(SelectNode($text,'div','class="material"'),'h5'),'a')));
		
		//Текст
		$a['text']=strip_tags(RemoveNode(SelectNode($text,'div','text'),'em'));
		//echo $a['text'];
		$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));

		//На что рецензия
		$a['type']=0;
		
		$a['issue']=$url;


	} else
	//НГС.АФИША
	if (mb_strpos($url,'afisha.ngs.ru')!==FALSE)
	{
		$a['publication']='НГС.АФИША';
		$a['publication_id']=187;
		
		//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;','&#8212;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...','—'),
			$text);		
			
		//Оценка	
		$grade=between($text,'afisha-rating-el_','">');
		//echo $grade;
		if ($grade <= 5)
			$a['grade']=$grade*20;
		else
			$a['grade']=$grade*2;
		
		//Название
		$a['title']=strip_tags(mb_trim(SelectNode($text,'h1')));
		$a['russian']=mb_trim(between($a['title'],'«','»'));
		
		//Время
		$a['date']=mb_trim(strip_tags(SelectNode($text,'span','class="article-date"')));
		
		//Автор
		$a['author']=mb_trim(strip_tags(before_first_str(SelectNode(after_first_str($text,'НГС.АФИША:</i></b>'),'i'),'<br /')));
		
		//Текст
		$a['text']=RemoveNode(before_last_str(before_last_str(SelectNode($text,'div','class="article-text  article-text__with-video"'),'<i>'),'<i>'),'i');
		//echo $a['text'];
		$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));

		//На что рецензия
		$a['type']=0;
		
		$a['issue']=$url;


	} else
	
	//Котонавты
	if (mb_strpos($url,'meownauts.com')!==FALSE)
	{
		$a['publication']='Котонавты';
		$a['publication_id']=226;
		
		//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;','&#8212;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...','—'),
			$text);		
			
		//Оценка	
		preg_match('/wp-content\/uploads\/2013\/.+?\/([0-9]{2,3}).png/si', $text, $match);
		$a['grade']=$match[1]/10;
		
		//Название
		$a['title']=mb_trim(strip_tags(SelectNode($text,'h1','class="entry-title"')));
		$a['russian']=mb_trim(before_last_str(after_first_str($a['title'],'[Рецензия] ', FALSE),'—'));
		//$a['russian']=between($a['title'],'',' —');
		
		//Время
		$category=SelectNode($text,'div','class="full-screen-title"');
		$time=SelectNode($category,'time');
		$a['date']=str_replace('/', '.', mb_trim(strip_tags($time, " "))); 
		$a['date']=substr($a['date'],0,10);
		
		//Автор
		$a['author']=between(SelectNode($text,'div','class="author-box-extra"'),'/">','</a></h5>');
		
		//Текст
		$a['text']=SelectNode($text,'div','class="entry-text"');
		$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));

		//На что рецензия
		if (mb_strstr($category,'/category/movies/'))
			$a['type']=0;
		else
			$a['type']=1;
		
		$a['issue']=$url;


	} else

	if (mb_strpos($url,'lumiere-mag.ru')!==FALSE) 
	{
			$a['publication']='Люмьер';
			$a['publication_id']=207;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...'),
			$text);			
			
			$text=SelectNode($text,'div','id="content"');
			//echo $text;
			// $ruen=explode('/',mb_trim(strip_tags(SelectNode($text,'h1'))));
			
			$a['title']=mb_trim(strip_tags(SelectNode($text,'h1','class="entry-title"')));
			
			/*
			$a['russian']=mb_trim(between($a['title'],'"','"'));
			if ($a['russian']=='')
				$a['russian']=mb_trim(between($a['title'],'«','»'));
			*/
			$a['date']=mb_trim(strip_tags(SelectNode(SelectNode($text,'div','class="entry-meta"'),'span','class="entry-date"')));
						
			$a['author']=mb_trim(strip_tags(between(SelectNode($text,'div','class="entry-meta"'),'Автор:','<span class="main_separator')));
			
			
			preg_match_all('/<h1>(.+)<\/h1>/siU',betweens($text,array('Для сердца','</table>')),$grade_all);
			
			$grade_sum=0;
			foreach ($grade_all[1] as $grall)
			{
				$grade_sum+=floatval(strip_tags($grall));	
			}
			
			$a['grade']=round($grade_sum/sizeof($grade_all[1])*10)/10;
			

			$a['text']=between(SelectNode($text,'td','class="entry-content-right"'),'','<table');
			
			$a['russian']=between($a['text'],'title="','"');
			
			// $a['text']=RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes($text,'div','class="inject-wrapper"'),'div','class="big-inject"'),'div','class="article_embedded_video"'),'div','class="article_illustration"'),'script'),'video'),'div','class="inject_type1_announce"'),'div','class="media_copyright"'),'div','class="article_inject_video');

			// $a['summary']=utf8_ucfirst(mb_trim(after_first_str(strip_tags(between($a['text'],'<em>Вердикт','</p>')),'— ')));
			

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
			
			$a['summary']=utf8_ucfirst(mb_trim(strip_tags(between($a['text'],'Вердикт: ', ''))));

			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'ria.ru')!==FALSE) 
	{
			$a['publication']='РИА Новости';
			$a['publication_id']=120;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...'),
			$text);			
			
			$text=SelectNode($text,'div','id="new_article"');
			//echo $text;
			// $ruen=explode('/',mb_trim(strip_tags(SelectNode($text,'h1'))));
			
			$a['title']=mb_trim(strip_tags(SelectNode($text,'h1','class="article_header_title"')));
			
			$a['russian']=mb_trim(between($a['title'],'"','"'));
			if ($a['russian']=='')
				$a['russian']=mb_trim(between($a['title'],'«','»'));
			
			$a['date']=mb_trim(strip_tags(between(SelectNode($text,'time','class="article_header_date"'),'</span>','')));
						
			$text=SelectNode($text,'div','itemprop="articleBody"');
			
			$a['author']=mb_trim(strip_tags(between($text,'<p><strong>','</strong></p>')));

			if ($a['author']!='')
				$text=after_first_str($text,'<p><strong>'.$a['author'].'</strong></p>');

			//$text=between(after_first_str($text,'Продолжительность —'),'</p>');
			$a['text']=RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes($text,'div','class="inject-wrapper"'),'div','class="big-inject"'),'div','class="article_embedded_video"'),'div','class="article_illustration"'),'script'),'video'),'div','class="inject_type1_announce"'),'div','class="media_copyright"'),'div','class="article_inject_video');

			// $a['summary']=utf8_ucfirst(mb_trim(after_first_str(strip_tags(between($a['text'],'<em>Вердикт','</p>')),'— ')));

			// $a['grade']=between(SelectNode($text,'div','class="author"'),' оценку ',' и');
			

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'newslab.ru')!==FALSE) 
	{
			$a['publication']='Newslab.ru';
			$a['publication_id']=266;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...'),
			$text);			
			
			$text=SelectNode($text,'div','class="entry"');
			
			$ruen=explode('/',mb_trim(strip_tags(SelectNode($text,'h1'))));
			
			//$a['title']=mb_trim(strip_tags(SelectNode($text,'h1','class="title"')));
			
			$a['russian']=mb_trim(str_replace(array('"','«','»'),'',$ruen[0]));
			$a['original']=mb_trim(str_replace(array('"','«','»'),'',$ruen[1]));
			
			$a['date']=mb_trim(preg_replace('/[0-9]{2}:[0-9]{2}/si','',strip_tags(SelectNode($text,'span','class="date"'))));
			
			$text=between(after_first_str($text,'Продолжительность —'),'</p>');
			$a['text']=RemoveNodes(RemoveNodes(RemoveNodes($text,'a','class="thickbox"'),'p','class="video_inner"'),'a','class="go2afisha"');
			
			$a['summary']=utf8_ucfirst(mb_trim(after_first_str(strip_tags(between($a['text'],'<em>Вердикт','</p>')),'— ')));

			if ($a['summary']!='')
				$a['text']=preg_replace('/<em>Вердикт.+<\/p>/siU','',$a['text']);
				
			$a['author']=mb_trim(strip_tags(between($a['text'],'<p><strong>','</strong></p>')));

			if ($a['author']!='')
				$a['text']=before_first_str($a['text'],'<p><strong>'.$a['author'].'</strong></p>');
			
			// $a['grade']=between(SelectNode($text,'div','class="author"'),' оценку ',' и');
			

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'vozduh.afisha.ru')!==FALSE) 
	{
			$a['publication']='Афиша Воздух';
			$a['publication_id']=265;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...'),
			$text);			
			
			$a['title']=mb_trim(strip_tags(SelectNode($text,'h1','class="title"')));
			
			$a['russian']=mb_trim(between($a['title'],'«','»'));
			
			$a['date']=strip_tags(SelectNode($text,'time','class="article_date"')).' '.date('Y');

			$a['author']=mb_trim(strip_tags(between(SelectNode($text,'section','class="wordle"'),'Текст','</li>')));
	
			$text='<p>'.strip_tags(SelectNode($text,'p','class="article_lead"')).'</p>'.SelectNode($text,'div','class="article_body"');
			

			$a['text']=RemoveNodes(RemoveNodes(RemoveNodes($text,'ul','class="impressum"'),'section','class="wordle"'),'div','class="gallery');

			// $a['grade']=between(SelectNode($text,'div','class="author"'),' оценку ',' и');
			

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]/si","",$a['text']));
			
			// $a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'afisha.mail.ru')!==FALSE) 
	{
			$a['publication']='Афиша@mail.ru';
			$a['publication_id']=105;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...'),
			$text);			
			
			$a['russian']=mb_trim(strip_tags(SelectNode($text,'h1','itemprop="name"')));
						
			$text=SelectNode($text,'div','class="b_review');
			$a['title']=mb_trim(strip_tags(SelectNode(SelectNode($text,'article','itemprop="reviewBody"'),'h3')));

			$au=SelectNode($text,'div','class="author');
			$a['author']=mb_trim(strip_tags(SelectNode($au,'a','itemprop="author"')));
			
			if ($a['author']=='Афиша Mail.Ru')
				$a['author']='Редакция';
			
			$a['date']=mb_trim(strip_tags(SelectNode(SelectNode(SelectNode($text,'section','class="review_afisha"'),'noindex'),'span')));
			
$a['text']=RemoveNodes(RemoveNodes(RemoveNodes(SelectNode($text,'article','itemprop="reviewBody"'),'h3'),'xml'),'style');
			$a['text']=preg_replace('/\<\!\-\-.+?\-\-\>/','',$a['text']);

			$a['grade']=between(SelectNode($text,'div','class="author"'),' оценку ',' и');
			
		// $a['text']=RemoveNodes(RemoveNodes(RemoveNodes(SelectNode($text,'div','class="b-article-item-body__text-image'),'div','class="add-to-blog-block"'),'div','class="field'),'iframe');
		
		//$a['text']=SelectNode($text,'div','class="content body wraper');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'rbcdaily.ru')!==FALSE) 
	{
			$a['publication']='РБК daily';
			$a['publication_id']=123;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...'),
			$text);			
			
			$text=SelectNode($text,'div','class="b-article-item');

			$a['russian']=mb_trim(between(strip_tags(SelectNode($text,'h2')),'«','»'));
			if ($a['russian']=='')
				$a['russian']=mb_trim(between(strip_tags(SelectNode($text,'h2')),'"','"'));
			
			$a['title']=mb_trim(strip_tags(SelectNode($text,'h2')));
						
			$au=SelectNode($text,'div','class="author');
			$a['author']=mb_trim(strip_tags(SelectNode($au,'span','name')));
			
			$a['date']=mb_trim(after_first_str(strip_tags(SelectNode($au,'span','date')),', '));
			
			$a['text']=SelectNode(SelectNode($text,'div','class="b-article-item-body__text'),'div','class="b-article-item-body__text-text');
			// $a['grade']='';
		// $a['text']=RemoveNodes(RemoveNodes(RemoveNodes(SelectNode($text,'div','class="b-article-item-body__text-image'),'div','class="add-to-blog-block"'),'div','class="field'),'iframe');
		
		//$a['text']=SelectNode($text,'div','class="content body wraper');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'gmbox.ru')!==FALSE) 
	{
			$a['publication']='GmBox';
			$a['publication_id']=57;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
	
			$a['original']=mb_trim(str_replace('Рецензия на ','',strip_tags(SelectNode($text,'h1'))));
			if (mb_substr($a['original'],-5)==', The')
				$a['original']='The '.mb_substr($a['original'],0,-5);
			if (mb_substr($a['original'],-3)==', A')
				$a['original']='A '.mb_substr($a['original'],0,-3);
			if (mb_substr($a['original'],-4)==', An')
				$a['original']='An '.mb_substr($a['original'],0,-4);
			$a['russian']=$a['original'];

			$a['author']= trim(strip_tags(SelectNode(SelectNode($text,'div','class="gbox_out_data"'),'span','itemprop="reviewer"')));
			$a['date']= trim(strip_tags(before_first_str(SelectNode(SelectNode($text,'div','class="gbox_breadcrumb"'),'ins'),', ')));

			$a['type']=1;
			$a['grade']=trim(strip_tags(SelectNode(SelectNode($text,'ins','itemprop="rating"'),'span','itemprop="value"')));
			
			$a['summary']=strip_tags(SelectNode($text,'cite'));
			
			$text=SelectNode(SelectNode($text,'div','id="alter_body_content"'),'div','class="field-item even');

		 $a['text']=mb_trim(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes($text,'div','class="media_embed'),'div','class="alter_image_wrapper"'),'dfn'),'blockquote'),'a','articles/sistema-ocenok-gmboxru'));

			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;
			
	}
	
	else
	
	if (mb_strpos($url,'riotpixels.com')!==FALSE) 
	{
			$a['publication']='Riot Pixels';
			$a['publication_id']=180;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
	
			$a['original']=mb_trim(str_replace('Рецензия на ','',strip_tags(SelectNode($text,'h1'))));
			if (mb_substr($a['original'],-5)==', The')
				$a['original']='The '.mb_substr($a['original'],0,-5);
			if (mb_substr($a['original'],-3)==', A')
				$a['original']='A '.mb_substr($a['original'],0,-3);
			if (mb_substr($a['original'],-4)==', An')
				$a['original']='An '.mb_substr($a['original'],0,-4);
			$a['russian']=$a['original'];

			$audate=SelectNode($text,'div', 'class="entry-date"');
			// $a['author']='';
			$a['author']= trim(strip_tags(SelectNode($text,'span','class="author vcard"')));
			$a['date']= str_replace('/','.',trim(strip_tags(SelectNode($text,'time'))));

			$a['type']=1;

			$text=SelectNode($text,'div','class="entry-content"');
										
			// $a['summary']=trim(strip_tags(SelectNode($text,'div','class="quote"')));
			$a['grade']=trim(strip_tags(str_replace('%','',between(SelectNode($text,'span','class="new-score'),'<span>','</span>'))));
			
			
				//$a['text']=mb_trim(RemoveNodes($text,'script'));


		 $a['text']=mb_trim(before_last_str(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes($text,'span','class="su-label'),'div','class="su-column'),'a','class="show-more-on-alpha"'),'noscript'),'table'),'script'),'a','href="http://riotpixels.com/forums/'),'div','class="dropshadowboxes-container'),'Оценка Riot Pixels'));

			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;
			
	}
	
	else

	if (mb_strpos($url,'sobesednik.ru')!==FALSE) 
	{
			$a['publication']='Собеседник.ру';
			$a['publication_id']=256;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
		array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...'),
			$text);			
			
			$text=SelectNode($text,'div','class="article"');

			$a['russian']=mb_trim(between(strip_tags(SelectNode($text,'h2')),'"','"'));
			
			// $a['title']=mb_trim(strip_tags(RemoveNodes(SelectNode($text,'h1'),'a')));
						
			$au=explode(' ',preg_replace('/ \(.+?\) /si',' ',mb_trim(strip_tags(SelectNode($text,'p','class="author"')))));	
			$a['author']=$au[1].' '.$au[0];
			
			$a['date']= mb_trim(strip_tags(RemoveNodes(SelectNode($text,'p','class="date"'),'em')));
			
			// $a['grade']='';
		$a['text']=betweens(RemoveNodes(RemoveNodes(RemoveNodes(SelectNode($text,'div','class="resize'),'div','class="add-to-blog-block"'),'div','class="field'),'iframe'),array('','<strong>Смотрите фотогалерею','','<strong>Читайте также'));
		
		//$a['text']=SelectNode($text,'div','class="content body wraper');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'pravda.ru')!==FALSE) 
	{
			$a['publication']='Правда.ру';
			$a['publication_id']=255;

			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&hellip;'),
			array(' ','—','—','«','»','-','"','-','«','»','','...'),
			$text);
			
			
			$text=SelectNode($text,'div','id="article"');

			$a['russian']=mb_trim(between(strip_tags(SelectNode($text,'h1')),'"','"'));
			
			// $a['title']=mb_trim(strip_tags(RemoveNodes(SelectNode($text,'h1'),'a')));
						
			$a['author']= mb_trim(strip_tags(SelectNode($text,'h4')));
			
			$a['date']= mb_trim(strip_tags(SelectNode($text,'div','class="date"')));
			
			// $a['grade']='';
		$a['text']=between(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(SelectNode($text,'div','class="_ga1_on_'),'div','class="article_image_div'),'div','class="actions'),'script'),'div','class="vrez'),'','<h4');
		
		$a['text']=preg_replace('/<p style="text-align: justify;"><strong>(Читайте также|Читайте самое интересное).+?<\/p>/si', '',$a['text']);
		
		//$a['text']=SelectNode($text,'div','class="content body wraper');

			$a['type']=0;

			$a['text']=trim(preg_replace("/[\n\r\s]{2,}/si","\n\n",trim(preg_replace('/ {2,}/si',' ',strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',"\t"),array("\n","\n","\n","\n\n","\n\n"," "),$a['text']))))));
			
			$a['issue']=$url;
			
	}
	
	else
	
	if (mb_strpos($url,'www.rg.ru')!==FALSE || mb_strpos($url,'http://rg.ru')!==FALSE) 
	{
			$a['publication']='Российская газета';
			$a['publication_id']=257;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			
			// $text=SelectNode($text,'div','class="entryBody');

			$a['russian']=mb_trim(between(strip_tags(SelectNode(SelectNode($text,'div','class="content-ar1"'),'h2')),'"','"'));
			if ($a['russian']=='')
				mb_trim(between(strip_tags(SelectNode(SelectNode($text,'div','class="content-ar1"'),'h2')),'«','»'));
				
			$a['title']=mb_trim(strip_tags(RemoveNodes(SelectNode($text,'h1'),'a')));
						
			$a['author']= mb_trim(strip_tags(SelectNode(SelectNode($text,'div','class="n_author_article"'),'a')));
			$a['date']=mb_trim(strip_tags(SelectNode(SelectNode($text,'div','class="article_info'),'span')));
			

			// $a['author']=SelectNode($text,'div','class="n_author_article"');
	

			// $a['grade']='';
		$a['text']=RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(SelectNode($text,'div','class="main-text'),'div','class="insert-materials'),'style'),'div','id="other_version"'),'div','class="tile'),'script');
		
		//$a['text']=SelectNode($text,'div','class="content body wraper');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'kinokadr.ru')!==FALSE) 
	{
			$a['publication']='Кинокадр';
			$a['publication_id']=88;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$text=SelectNode($text,'div','class="hreview');
			
			$a['author']= mb_trim(strip_tags(SelectNode(SelectNode($text,'div','class=author'),'span','class="reviewer"')));

			$a['date']= mb_trim(strip_tags(between(SelectNode(SelectNode(SelectNode($text,'div','class=author'),'span','class="dtreviewed"'),'span','class="value-title"'),' title="','"')));
			
			$a['date']=mb_substr($a['date'],8,2).'.'.mb_substr($a['date'],5,2).'.'.mb_substr($a['date'],0,4);
			
			if ($a['date']=='')
$a['date']=mb_trim(strip_tags(SelectNode(SelectNode($text,'div','class=author'),'span','class="dtreviewed"')));

			$a['russian']=mb_trim(strip_tags(SelectNode(SelectNode($text,'h1'),'span','class="fn"')));
			if ($a['russian']=='')
				$a['russian']=mb_trim(strip_tags(SelectNode(SelectNode($text,'p','class=vrezka'),'a')));

				
			$proshalka=strip_tags(SelectNode($text,'a','title="Прощалки"'));
			if ($proshalka=='Вот такое кино' || $proshalka=='Такое кино')
				$a['grade']='#так себе';
			if ($proshalka=='До встречи в кино')
				$a['grade']='#отлично';
			if ($proshalka=='Любите кино')
				$a['grade']='#плохо';
							
		$a['text']=RemoveNodes(RemoveNodes(betweens($text,array('<p class=vrezka>','title="Прощалки"','</p>')),'div','class="img"'),'center');
		
			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'snob.ru')!==FALSE) 
	{
			$a['publication']='Сноб';
			$a['publication_id']=258;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$text=SelectNode($text,'div','class="entryBody');
			
			$a['author']=mb_trim(strip_tags(SelectNode($text,'a','class="author"')));
			$a['date']=mb_trim(strip_tags(SelectNode(SelectNode($text,'div','class="meta'),'span')));
			
			
			$a['russian']=mb_trim(between(strip_tags(SelectNode($text,'p', 'class="lead"')),'«','»'));
			
			if ($a['russian']=='')
				$a['russian'] = mb_trim(between(strip_tags(SelectNode($text,'h1')),'«','»'));
				
					

			// $a['grade']='';
		$a['text']=before_first_str(RemoveNodes(SelectNode($text,'div','class="content body wraper'),'div','class="imageGallery"'),'<strong>BONUS</strong>');
		
		//$a['text']=SelectNode($text,'div','class="content body wraper');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'snob.ru')!==FALSE) 
	{
			$a['publication']='Сноб';
			$a['publication_id']=258;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$text=SelectNode($text,'div','class="entryBody');
			
			$a['author']=mb_trim(strip_tags(SelectNode($text,'a','class="author"')));
			$a['date']=mb_trim(strip_tags(SelectNode(SelectNode($text,'div','class="meta'),'span')));
			
			
			$a['russian']=mb_trim(between(strip_tags(SelectNode($text,'p', 'class="lead"')),'«','»'));
			
			if ($a['russian']=='')
				$a['russian'] = mb_trim(between(strip_tags(SelectNode($text,'h1')),'«','»'));
				
					

			// $a['grade']='';
		$a['text']=before_first_str(RemoveNodes(SelectNode($text,'div','class="content body wraper'),'div','class="imageGallery"'),'<strong>BONUS</strong>');
		
		//$a['text']=SelectNode($text,'div','class="content body wraper');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'gameru.net')!==FALSE) 
	{
			$a['publication']='GAMEINATOR';
			$a['publication_id']=17;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
											
			$text=SelectNode($text,'table','class="row_default"');
	
			$a['original']=mb_trim(before_first_str(strip_tags(SelectNode(SelectNode($text,'td','class="newsheader"'),'a')),', рецензия'));
			if (mb_substr($a['original'],-5)==', The')
				$a['original']='The '.mb_substr($a['original'],0,-5);
			if (mb_substr($a['original'],-3)==', A')
				$a['original']='A '.mb_substr($a['original'],0,-3);
			if (mb_substr($a['original'],-4)==', An')
				$a['original']='An '.mb_substr($a['original'],0,-4);
			$a['russian']=$a['original'];
			
			$da=SelectNode($text,'td','class="textdetails"');
							
			// $a['author']='';
			$a['author']= trim(strip_tags(SelectNode($da,'a')));
			$a['date']= str_replace('.',' ',trim(strip_tags(betweens($da,array('@ ',' @',', ','')))));
			$a['type']=1;
						
			// $a['summary']=trim(strip_tags(SelectNode($text,'div','class="quote"')));
			
			// $a['text']=$text;
			
			$a['text']=mb_trim(str_replace(']]>','',RemoveNodes(RemoveNodes(RemoveNodes(betweens($text,array('<td class="textdetails"','','<tr>','')),'div', 'center'),'td','class="textdetails"'),'td','class="newsheader"')));

			
			if (mb_strstr($text,' балла из '))
				$a['grade']=trim(between(strip_tags($text),'Оценка:','балла из'));
			else
				$a['grade']=trim(between(strip_tags($text),'Оценка:','/'));

			if (mb_strstr(strip_tags($text),' из 100') || mb_strstr(strip_tags($text),'/100'))
				$a['grade']=$a['grade']/10;
			
			if ($a['grade']!='')
				$a['text']=between($a['text'],'','Оценка:');
			
			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'ps3.noizless.ru')!==FALSE) 
	{
			$a['publication']='PS3 Noizelss ;)';
			$a['publication_id']=26;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
											
			$text=SelectNode($text,'div','id="content"');
	
			$a['original']=mb_trim(between(strip_tags(SelectNode($text,'h1')),'ецензия на ',''));
			if (mb_substr($a['original'],-5)==', The')
				$a['original']='The '.mb_substr($a['original'],0,-5);
			if (mb_substr($a['original'],-3)==', A')
				$a['original']='A '.mb_substr($a['original'],0,-3);
			if (mb_substr($a['original'],-4)==', An')
				$a['original']='An '.mb_substr($a['original'],0,-4);
			$a['russian']=$a['original'];
			
			$da=SelectNode($text,'div','class="entrymeta"');
							
			// $a['author']='';
			$a['author']= trim(strip_tags(between($da,'Автор:','</strong>')));
			$a['date']= trim(strip_tags(between($da,'',' Опубликовано в')));

			$a['type']=1;
			
			// $a['summary']=trim(strip_tags(SelectNode($text,'div','class="quote"')));
			
			$a['text']=mb_trim(RemoveNode(SelectNode($text,'div', 'class="entrybody"'),'div','class="entrymeta"'));

			$a['grade']=trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(str_replace(array('/10','из 10'),'',between($text,'<strong>Оценка</strong>','<')))));
			
			if ($a['grade']!='')
				$a['text']=between($a['text'],'','<strong>Оценка</strong>');
			
			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'timeout.ru')!==FALSE) 
	{
			$a['publication']='TimeOut';
			$a['publication_id']=106;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$a['russian']=mb_trim(strip_tags(SelectNode($text,'h1', 'itemprop="name"')));
			
			$a['grade'] = intval(between (SelectNode (SelectNode (SelectNode ($text,'div','class="headingH2"'), 'span', 'class="stars"'), 'span', 'class="stars-is"'), 'style="width:','px')/21);
					
			$a['author']=mb_trim(strip_tags(SelectNode(SelectNode($text,'div','class="contento-text'),'p','class="signature"')));
			
			$a['date']=date('d.m.Y');
			
			$a['text'] = RemoveNode(SelectNode($text,'div','class="contento-text"'),'p','class="signature"');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
		
	}
	
	else
	
	if (mb_strpos($url,'kommersant.ru')!==FALSE) 
	{
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			if (mb_strstr($text,'Журнал "Огонёк"'))
			{
				$a['publication']='Огонёк';
				$a['publication_id']=238;
			} else
			{
				$a['publication']='КоммерсантЪ';
				$a['publication_id']=124;
			}

			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);

			$intro=SelectNode($text,'span','class="b-article__intro"');
			
			$a['author']=mb_trim(strip_tags(SelectNode($text,'div','class="document_authors vblock')));
			if ($a['author']=='')
				$a['author']=mb_trim(strip_tags(SelectNode($text,'div','class="document_authors2 vblock')));

			if ($a['author']=='')
			{
				if (mb_stristr($intro, 'Лидия Маслова') || mb_stristr($intro, 'Лидия Ъ-Маслова') || mb_stristr($intro, 'Лидии Масловой') || mb_stristr($intro, 'Лидии Ъ-Масловой'))
					 $a['author']='Лидия Маслова';
				else
				if (mb_stristr($intro, 'Михаил Трофименков') || mb_stristr($intro, 'Михаил Ъ-Трофименков') || mb_stristr($intro, 'Михаила Трофименкова') || mb_stristr($intro, 'Михаила Ъ-Трофименкова'))
					 $a['author']='Михаил Трофименков';
			}				
				
			
			
			$a['date']=mb_trim(strip_tags(before_last_str(SelectNode($text,'time','publish_date'),',')));
			
			$a['russian']=mb_trim(between(strip_tags(SelectNode(SelectNode($text,'div', 'class="document"'),'div', 'class="subtitle"')),'«','»'));
			
			if ($a['russian']=='')
				$a['russian']=mb_trim(between(strip_tags(SelectNode(SelectNode($text,'div', 'class="document"'),'div', 'class="subtitle"')),'"','"'));

			if ($a['russian']=='')
				$a['russian'] = mb_trim(str_replace(array('"','«','»'),'',strip_tags(SelectNode(SelectNode($text,'table', 'class="document_photogallery'),'div', 'class="text"'))));				

			if ($a['russian']=='')
				$a['russian'] = mb_trim(between(strip_tags($intro),'"','"'));

			if ($a['russian']=='')
				$a['russian'] = mb_trim(between(strip_tags($intro),'«','»'));
						

			$a['grade']='';
		$a['text']=RemoveNode(SelectNode($text,'div','id="divLetterBranding"'),'div','class="document_inner_title"');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'gq.ru')!==FALSE) 
	{
			$a['publication']='GQ';
			$a['publication_id']=115;
			
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
	
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);

		//	echo $text;	
			
			$desc=SelectNode(SelectNode($text,'div','class="block_author"'),'p');
						
			$a['author']=mb_trim(strip_tags(between($desc,'Автор:',', ')));
			$a['date']=mb_trim(strip_tags(between($desc,', ','')));
			
			$a['russian']=mb_trim(str_replace(', ','',strip_tags(SelectNode(SelectNode(SelectNode($text,'div', 'class="pageheader"'),'div', 'class="article_tag"'),'a'))));
						

			$a['grade']='';
		$a['text']=between(RemoveNode(RemoveNode(RemoveNode(SelectNode($text,'div','class="content_article"'),'a','class="articl_top_comment"'),'h1'),'iframe'),'','<div class="div_poli">');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'horrorzone.ru')!==FALSE) 
	{
			$a['publication']='Horrorzone.ru';
			$a['publication_id']=121;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$desc=SelectNode($text,'div','class="category_desc"');
			
			$a['russian']=mb_trim(strip_tags(SelectNode($desc,'span', 'class="tags"')));
						
			$a['author']=mb_trim(strip_tags(between($desc,'Автор:',', ')));

			$a['grade']=mb_trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(between($desc,'Оценка: ',' из 10'))));
		$a['text']=between(SelectNode(SelectNode($text,'div','class="page_only"'),'div','class="page_top"'),'','<h1 class="home"');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'pressxtowin.com')!==FALSE) 
	{
			$a['publication']='Press X to Win';
			$a['publication_id']=142;
			
			//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
			
			
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
	
			$a['original']=mb_trim(str_replace('Рецензия на ','',strip_tags(SelectNode($text,'h2'))));
			if (mb_substr($a['original'],-5)==', The')
				$a['original']='The '.mb_substr($a['original'],0,-5);
			if (mb_substr($a['original'],-3)==', A')
				$a['original']='A '.mb_substr($a['original'],0,-3);
			if (mb_substr($a['original'],-4)==', An')
				$a['original']='An '.mb_substr($a['original'],0,-4);
			$a['russian']=$a['original'];
			
			$text=SelectNode(SelectNode(SelectNode($text,'div','class="container-fluid"'),'div','class="main"'),'div','class="show-content"');
			
			//echo $text;
						
			// $a['author']='';
			$a['author']= trim(strip_tags(between(SelectNode($text,'div','class="small"'),'Автор:',', ')));
			$a['date']= trim(strip_tags(between(SelectNode($text,'div','class="small"'),'опубликовано ',' | ')));

			$a['type']=1;
			
			// $a['summary']=trim(strip_tags(SelectNode($text,'div','class="quote"')));
			
			$a['text']=mb_trim(SelectNode($text,'div', 'class="show-font"'));

			$a['grade']=trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(between($text,'Наша оценка: ','/10'))));
			
			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'exler.ru')!==FALSE) 
	{
			$a['publication']='Авторский проект Алекса Экслера';
			$a['publication_id']=91;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$text=preg_replace("/[ ]{2,}/si"," ",preg_replace("/[\n\r]+/si","",str_replace('<p align="center">***</p>','',SelectNode($text,'div','id="article"'))));
			
			$a['russian']=trim(strip_tags(between(SelectNode($text,'b'),'"','"')));
						
			$a['author']='Алекс Экслер';

			$a['date']=trim(strip_tags(between($text,'Дата публикации:','</p>')));

			$a['text']=betweens($text,array('<p align="justify">','<table id="FilmsResults"','','<div align="center"'));
			
			$gr=explode('<br>',SelectNode(SelectNode($text,'td','class="FilmsVoteResultsGreen"'),'p'));
			$gra=0;
			$gri=0;
			foreach ($gr as $g)
			{
				$g=mb_trim(preg_replace("/[^0-9\+\-]/si","",$g));
				if ($g!='')
				{
					$gra+=preg_replace("/[^0-9]/si","",$g);
					$gri++;
				}
			}
			
			if ($gra!=0 && $gri!=0)
				$a['grade']=intval($gra/$gri*20)/10;
			else
				$a['grade']='';
			
			//$a['grade']=mb_strlen(trim(strip_tags(SelectNode($gradedate,'font','color="#FF0000"'))));
			//echo SelectNode($gradedate,'font','color="#FF0000"').$a['grade'];
			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'consolelife.ru')!==FALSE) 
	{
			$a['publication']='Consolelife.ru';
			$a['publication_id']=23;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
	
			$text=SelectNode(SelectNode($text,'div','id="content"'),'div','class="article"');
			$a['original']=mb_trim(str_replace('Обзор ','',strip_tags(SelectNode($text,'h1'))));
			if (mb_substr($a['original'],-5)==', The')
				$a['original']='The '.mb_substr($a['original'],0,-5);
			if (mb_substr($a['original'],-3)==', A')
				$a['original']='A '.mb_substr($a['original'],0,-3);
			if (mb_substr($a['original'],-4)==', An')
				$a['original']='An '.mb_substr($a['original'],0,-4);
			$a['russian']=$a['original'];
							
			$a['author']='';
			// $a['author']= trim(strip_tags(SelectNode(SelectNode($text,'div','class="post-info"'),'em','class="fn"')));
			$a['date']= trim(strip_tags(str_replace('•','',RemoveNode(SelectNode($text,'div','class="info"'),'span','class="comments-num"'))));

			$a['type']=1;
			
			$a['summary']=trim(strip_tags(SelectNode($text,'div','class="quote"')));
			
			$a['text']=mb_trim(betweens(SelectNode($text,'div', 'id="news-id-'),array('Обзор (рецензия) игры','<div class="quote">','</div>'))).$a['summary'];

			$a['grade']=trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(between($text,'Наша оценка: ','/10'))));
			
			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'gameslife.ru')!==FALSE) 
	{
			$a['publication']='GamesLife';
			$a['publication_id']=55;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
	
			$text=SelectNode($text,'div','contentpart');

			$a['original']=mb_trim(strip_tags(before_last_str(SelectNode($text,'h1'),'. ')));
			if (mb_substr($a['original'],-5)==', The')
				$a['original']='The '.mb_substr($a['original'],0,-5);
			if (mb_substr($a['original'],-3)==', A')
				$a['original']='A '.mb_substr($a['original'],0,-3);
			if (mb_substr($a['original'],-4)==', An')
				$a['original']='An '.mb_substr($a['original'],0,-4);
			$a['russian']=$a['original'];
			
			// $a['title']=trim(strip_tags(SelectNode($text,'h1','class="publicationTitle"')));
				
			$a['author']= trim(strip_tags(SelectNode(SelectNode($text,'div','class="post-info"'),'em','class="fn"')));
			$a['date']= trim(strip_tags(SelectNode(SelectNode($text,'div','class="post-info"'),'span','class="date"')));

			$a['type']=1;
			
			$a['summary']=trim(strip_tags(between($text,'<strong>Вердикт:','</p>')));
			
			$a['text']=mb_trim(RemoveNode(RemoveNode(RemoveNode(SelectNode($text,'div', 'class="box textbox"'),'div','class="insert"'),'script'),'div','class="bfoot"'));

			$a['grade']=mb_trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(SelectNode(SelectNode($text,'div','class="our_statement"'),'div','class="mark"'))));
			
			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
			
	else
	if (mb_strpos($url,'gamescope.ru')!==FALSE) 
	{
			$a['publication']='GameScope';
			$a['publication_id']=76;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;','&hellip;'),array(' ','—','—','«','»','-','"','-','«','»','','-','...'),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
	
			$text=SelectNode($text,'div','class="article"');
			
			$a['text']='';

			$a['russian']=mb_trim(between(strip_tags(SelectNode($text,'h1')),'«','»'));

			// $a['title']=trim(strip_tags(SelectNode($text,'h1','class="publicationTitle"')));	
						
			$a['date']= trim(strip_tags(between(SelectNode($text,'div','class="single-meta"'),'',' | ')));
			$a['author']= trim(strip_tags(SelectNode(SelectNode($text,'font','class="author"'),'a')));
			$a['author']=str_replace('Alex "Jace" Ivanchenko','Алекс Иванченко',$a['author']);

			$a['type']=0;
			$a['grade']='';
			
			// $a['summary']=trim(strip_tags(between($text,'<strong>Вердикт:','</p>')));
			$i=0;
			
			do {
			
				$a['text'].=mb_trim(betweens($text,array('<em>Продолжительность:</em>','<strong>Ждем Вас</strong>','</p>','')));
				
				if ($a['grade']=='')
					$a['grade']=mb_trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(betweens($text,array('<strong>Оценка Фильма от GameScope:</strong>','</strong>','<strong>','/ 10')))));

				$next=between(SelectNode(SelectNode($text,'div','prev_and_next'),'a','class="next"'),'href="','"');
				if ($next!='')
				{
					$text=file_get_contents($next);
					$text=SelectNode($text,'div','class="article"');
				}
				
			} while ($next!='');
			
			
								


			
			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'playground.ru')!==FALSE) 
	{
			$a['publication']='Playground';
			$a['publication_id']=45;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
			
			$text=SelectNode($text,'article');
	
			$a['title']=trim(strip_tags(SelectNode($text,'h1')));

			$a['original']=before_last_str($a['title'],'. ');
			if (mb_substr($a['original'],-5)==', The')
				$a['original']='The '.mb_substr($a['original'],0,-5);
			if (mb_substr($a['original'],-3)==', A')
				$a['original']='A '.mb_substr($a['original'],0,-3);
			if (mb_substr($a['original'],-4)==', An')
				$a['original']='An '.mb_substr($a['original'],0,-4);
				
			$a['russian']=$a['original'];
			
	
			// $text=SelectNode(SelectNode($text,'div','borderwrap'),'table','ipbtable');
			
			$a['date']= trim(between(strip_tags(SelectNode(SelectNode($text,'div','class="article-metadata"'),'time')),'',' в ').date(' Y'));
			$a['author']= trim(strip_tags(SelectNode(SelectNode($text,'div','class="article-metadata"'),'a')));
			
			$a['author']=str_replace(array('Alexander Kupcewicz'), array('Александр Купцевич'), $a['author']);

			$a['type']=1;
			
			$a['summary']=utf8_ucfirst(mb_trim(strip_tags(between($text,'<strong>Вердикт:','</p>'))));
			
			$a['text']=mb_trim(preg_replace(array('/Рецензируемая версия игры\:.+<\/small>/siU','/(Будем рады комментариям. )*Не комментируете\? Выразите мнение иначе.+<\/p>/siU'), array('',''), str_replace('Загружаю...','',RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(SelectNode($text,'div','id="authorsReview"'),'figure'),'div','class="insetblock"'),'script'),'div','class="our_statement"'))));

			//echo $a['text'];

			$a['grade']=mb_trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(SelectNode(SelectNode($text,'div','class="our_statement"'),'div','class="mark"'))));
			
			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'zoneofgames.ru')!==FALSE) 
	{
			$a['publication']='Zone of Games';
			$a['publication_id']=77;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;','&#8212;'),array(' ','—','—','«','»','-','"','-','«','»','','-'),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
	
			
			$a['original']=mb_trim(strip_tags(mb_strrchr(str_replace(array('[Рецензия] ','«','»'),array(''),SelectNode($text,'title')),' (',true)));
			$a['russian']=$a['original'];
	
			$text=SelectNode(SelectNode($text,'div','borderwrap'),'table','ipbtable');
			
			$a['date']=str_replace(array('Вчера','Сегодня'),array(date('d.m.Y',strtotime("-1 days")),date("d.m.Y")),trim(strip_tags(SelectNode($text,'span','postdetails'))));
			
			$a['author']=trim(strip_tags(SelectNode($text,'span', 'normalname')));

			$a['type']=1;
			
			// $a['summary']=trim(strip_tags(between($text,'<span>Краткий итог</span>','</td>')));
			
			$a['text']=mb_trim(SelectNode(SelectNode($text,'td', 'post-main-'),'div','postcolor'));

			$a['grade']=mb_trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(between($text,'Итоговая оценка ','</b>'))));

			$a['text']=between(preg_replace('/[\-]{3,}/si','',$a['text']),'','Итоговая оценка ');
			
			
			$a['text']=mb_trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>',''),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'ferra.ru')!==FALSE) 
	{
			$a['publication']='Ferra.ru';
			$a['publication_id']=213;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));

			$text=SelectNode($text,'div','class="article"');
			
			$infopost=SelectNode($text,'div','b_infopost');
			
			$a['date']=mb_trim(strip_tags(SelectNode($infopost,'span','class="date"')));
			
			$a['author']=mb_trim(strip_tags(SelectNode($infopost,'span', 'itemprop="reviewer"')));
			
			$a['original']=mb_trim(strip_tags(SelectNode($text,'span','itemprop="itemreviewed"')));
			$a['russian']=$a['original'];
			


			$a['type']=1;
			
			// $a['summary']=trim(strip_tags(between($text,'<span>Краткий итог</span>','</td>')));
			
			$a['text']=mb_trim(betweens(SelectNode($text,'div','itemprop="description"'),array('<b>Название', '','</p>')));
			
			$a['grade']=mb_trim(strip_tags(SelectNode($text,'div','class="score-number"')));
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'maximumgames.ru')!==FALSE) 
	{
			$a['publication']='Maximum Games';
			$a['publication_id']=210;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
	
	
			$a['date']=trim(strip_tags(between($text,'Добавлено: ','</span>')));

			$text=SelectNode($text,'div','class="main-news"');
			
			$a['original']=mb_trim(mb_strrchr(strip_tags(str_replace('–','-',SelectNode($text,'h1'))),' - ',true));
			$a['russian']=$a['original'];
			
			$a['author']=trim(
				preg_replace ('/ ".+?" /si',' ',
					between(strip_tags(SelectNode($text,'div', 'class="main-news-text"')),'Автор:',' | ')
				)
			);

			$a['type']=1;
			
			// $a['summary']=trim(strip_tags(between($text,'<span>Краткий итог</span>','</td>')));
			
			$a['text']=mb_trim(SelectNode(SelectNode($text,'div', 'class="main-news-text"'),'div','id="news-id-'));
			
			$a['grade']=mb_trim(strip_tags(SelectNode($text,'div','class="score-number"')));
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'mgnews.ru')!==FALSE) 
	{
			$a['publication']='Mgnews.ru';
			$a['publication_id']=209;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
			
			$text=SelectNode($text,'div','class="item"');

			$a['original']=mb_trim(mb_strrchr(between(strip_tags(SelectNode($text,'h1')),'Обзор ',''),'. ',true));
			$a['russian']=$a['original'];
			
			$a['date']=trim(strip_tags(between(SelectNode(SelectNode($text,'div','class="item-ph"'),'span','class="post-next"'),'[',']')));
			
			$a['author']=trim(strip_tags(SelectNode($text,'span','itemprop="editor"')));

			$a['type']=1;
			
			// $a['summary']=trim(strip_tags(between($text,'<span>Краткий итог</span>','</td>')));
			
			$a['text']=mb_trim(SelectNode(SelectNode($text,'div', 'class="item-text"'),'div','class="text-cont open"'));
			
			$a['grade']=mb_trim(strip_tags(SelectNode(betweens($a['text'],array('<strong>Итог</strong>','</p>')),'span')));
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'goha.ru')!==FALSE) 
	{
			$a['publication']='GoHa.ru';
			$a['publication_id']=135;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
								
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
			
			$a['original']=trim(strip_tags(SelectNode($text,'div','id="header-2"')));
			$a['russian']=$a['original'];
			
			$text=SelectNode($text,'div','class="news_body"');
			
			$a['date']=trim(strip_tags(SelectNode(SelectNode($text,'span','class="posted"'),'time')));
			
			$a['author']=trim(strip_tags(SelectNode($text,'span','itemprop="editor"')));

			$a['grade']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="mark"'),'span')));


			$a['type']=1;
			
			$a['summary']=trim(strip_tags(between($text,'<span>Краткий итог</span>','</td>')));
			
			$a['text']=SelectNode($text,'span', 'itemprop="articleBody"');
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'vgtimes.ru')!==FALSE) 
	{
			$a['publication']='VGTimes';
			$a['publication_id']=75;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
			
			$text=SelectNode($text,'div','dle-content');
					
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
			
			$a['original']=trim(strip_tags(str_replace('Обзор ','',SelectNode($text,'h1'))));
			$a['russian']=$a['original'];
			
			$a['date']=trim(strip_tags(between(SelectNode($text,'div','class="fnews"'),'Статья</a> написана ',', ')));
			
			$a['author']=trim(strip_tags(SelectNode(between(SelectNode($text,'div','class="fnews"'),'Статья</a> написана ',''),'a')));

			$a['grade']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="mark"'),'span')));


			$a['type']=1;
			
			$a['summary']=trim(strip_tags(between($text,'<span>Краткий итог</span>','</td>')));
			
			$a['text']=SelectNode($text,'div', 'id="news-id-');
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'kino-teatr.ru')!==FALSE) 
	{
			$a['publication']='Кино - Театр';
			$a['publication_id']=100;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$a['russian']=trim(strip_tags(str_replace(array('«','»'),'',SelectNode($text,'h2','itemprop="name"'))));
						
			$a['author']=trim(strip_tags(SelectNode($text,'div','itemprop="author"')));

			$a['date']=trim(strip_tags(SelectNode($text,'div','class="date"')));

			$a['text']=between(SelectNode($text,'div','itemprop="text"'),'','<strong>В прокате с');
			
			//$a['grade']=mb_strlen(trim(strip_tags(SelectNode($gradedate,'font','color="#FF0000"'))));
			//echo SelectNode($gradedate,'font','color="#FF0000"').$a['grade'];
			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'tramvision.ru')!==FALSE) 
	{
			$a['publication']='TramVision';
			$a['publication_id']=94;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$a['russian']=trim(strip_tags(mb_strstr(SelectNode($text,'h3'),'/',true)));
						
			$a['author']=trim(strip_tags(betweens($text,array('<font id="fnt1" color="#990000">© ','<','',', '))));

			$a['text']=between($text,'</h2>','<font id="fnt1"');

			$gradedate=between($text,'Общая оценка:','</p>');
			
			$a['date']=between($gradedate,'<p>','');
			
			$a['grade']=mb_strlen(trim(strip_tags(SelectNode($gradedate,'font','color="#FF0000"'))));
			//echo SelectNode($gradedate,'font','color="#FF0000"').$a['grade'];
			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

if (mb_strpos($url,'mn.ru')!==FALSE) 
	{
			$a['publication']='Московские новости';
			$a['publication_id']=116;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$authordate=SelectNode($text,'div','class="date-author"');
			
			$a['date']=trim(strip_tags(SelectNode($text,'div','class="head_date"')));
			$a['author']=trim(strip_tags(SelectNode(SelectNode($authordate,'span','class="author"'),'span','class="name"')));

			$a['text']='<p>'.SelectNode($text,'div','class="article_lead"').'</p>'.RemoveSubsAll(RemoveSubsAll(SelectNode($text,'div','class="body"'),'<div','</div>'),'<script','</script>');
			//$a['author']=trim(strip_tags(between($text,'Автор: ', '')));
			
			$a['russian']='';
			
			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'kinonews.ru')!==FALSE) 
	{
			$a['publication']='Новости кино';
			$a['publication_id']=99;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$a['date']=trim(strip_tags(SelectNode($text,'div','class="date"')));
			$a['russian']=trim(strip_tags(SelectNode(SelectNode($text,'h2'),'a')));

			$text=SelectNode($text,'div','class="textart"');			
			$a['author']=trim(strip_tags(between($text,'Автор: ', '')));
			
			$a['type']=0;
			
			$a['text']=between($text,'','Автор: ');
						
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	else
	if (mb_strpos($url,'vashdosug.ru')!==FALSE) 
	{
			$a['publication']='ВашДосуг.RU';
			$a['publication_id']=114;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$a['date']=trim(strip_tags(SelectNode($text,'div','class="pdate"')));
			
			$a['author']=trim(strip_tags(str_replace('Текст: ','',SelectNode($text,'div','class="articleautor"'))));

			$a['russian']=mb_trim(strip_tags(SelectNode(SelectNode(between($text,'<h4>В афише</h4>','</div>'),'li'),'a')));
			
			$a['type']=0;
			
			$a['text']=between(RemoveNode(RemoveNode(SelectNode($text,'div','class="body uni_article"'),'h1'),'div','class="pdate"'),'class="anonce">','<div class="articleautor"');
						
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'gazeta.ru')!==FALSE) 
	{
			$a['publication']='Газета.ru';
			$a['publication_id']=118;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$a['date']=trim(strip_tags(SelectNode(SelectNode($text,'div','class="info"'),'time')));
			
			$a['author']=trim(strip_tags(SelectNode($text,'span','rel="author"')));

			$a['russian']=trim(strip_tags(between(SelectNode($text,'h1','class="article_subheader"'),'«','»')));
			
			$a['type']=0;
			
			$a['text']=SelectNode($text,'div','class="text"');
						
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

if (mb_strpos($url,'25-k.com')!==FALSE) 
	{
			$a['publication']='25-й кадр';
			$a['publication_id']=81;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			
			$text=SelectNode ($text,'div','id="maincol"');
			
			$dateauthor=SelectNode($text,'div','id="subtitle"');

			$a['date']=trim(strip_tags(between($dateauthor,'<b>Дата: ','</b>')));
			
			$a['author']=trim(strip_tags(between($dateauthor,'<b>Автор: ','</b>')));
			
			$main=SelectNode ($text,'div','id="main"');
						
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));
			
			$a['russian']=trim(strip_tags(betweens($dateauthor,array('<a href="page.php?id=',' (','">',''))));
			
			if ($a['russian']=='')
				$a['russian']=trim(strip_tags(between(RemoveNode(SelectNode($main,'strong'),'span'),'',' (')));
			
			$a['grade']=trim(strip_tags(between($main,'ОЦЕНКА: ','</span>')));

			$a['type']=0;
			
			$a['text']=betweens($main,array('ОЦЕНКА: ','<div style="text-align:right;">'.$a['author'].'</div>','</strong>',''));
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'progamer.ru')!==FALSE) 
	{
			$a['publication']='ProGamer.Ru';
			$a['publication_id']=25;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
			
			$text=SelectNode($text,'div','class="post"');
					
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));

			$a['russian']=trim(strip_tags(str_replace('Обзор ','',SelectNode($text,'h1'))));
			
			$a['date']=trim(strip_tags(str_replace('| ','',SelectNode($text,'small'))));
			
			$a['author']='';

			$a['grade']=trim(strip_tags(between(SelectNode($text,'div', 'class="ratingScoreOvText"'),'',' из 10')));


			$a['type']=1;
			
			//$a['text']=RemoveNode(between($text,'<hr>','<hr>'),'div', 'class="ratingScoreOvText"');
			
			$a['text']=RemoveNode($text,'div', 'class="ratingScoreOvText"');
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'bestgamer.ru')!==FALSE) 
	{
			$a['publication']='BestGamer.ru';
			$a['publication_id']=56;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
						
			$a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));

			$a['russian']=trim(strip_tags(SelectNode($text,'h1', 'class="gametitle"')));
			
			$arr=mb_convert_encoding (file_get_contents('http://bestgamer.ru/articles/reviews/page_1/'), "UTF-8", "Windows-1251");
			
			$a['date']=betweens($arr,array($a['russian'].'</a>','</tr>','class="tb_bg_right">','</td>'));

			$a['russian']=preg_replace ('/ \([0-9]{4}\)/si','', $a['russian']);

			$a['author']=trim(strip_tags(between($text,'<i>Автор: ', '</i>')));

			$a['grade']=trim(strip_tags(between($text,'Общая оценка: ', '</b>')));


			$a['type']=1;
			
			$a['text']=between($text,'<strong>Рецензия</strong>','<div align="right">');
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'film.ru')!==FALSE) 
	{
			$a['publication']='Фильм.ру';
			$a['publication_id']=96;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
						
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));

			$a['russian']=trim(strip_tags(SelectNode($text,'h2','class="h1"')));
			
			$dateauthor=SelectNode($text,'div','class="info"');
			
			$a['date']=trim(strip_tags(SelectNode($dateauthor,'span')));
			
			$a['author']=trim(strip_tags(SelectNode($dateauthor,'strong')));

			$a['grade']=trim(strip_tags(between($text,'title="Оценка: ',' из 10"')));

			$a['type']=0;
			
			$a['text']=betweens(SelectNode(SelectNode($text,'div','class="text'),'div','id="selectable-content"'),array('<div class="movie-info"','','</div>',''));
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'kino.myvi.ru')!==FALSE) 
	{
			$a['publication']='Муви';
			$a['publication_id']=92;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
						
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));

			$a['russian']=trim(strip_tags(SelectNode(SelectNode($text,'div','id="film-title"'), 'h1')));
			
			$dateauthor=SelectNode($text,'div','class="review-author"');
			
			$a['date']=trim(strip_tags(SelectNode($dateauthor,'div','class="date"')));
			
			$a['author']=trim(strip_tags(SelectNode($dateauthor,'a')));

			$a['grade']='';


			$a['type']=0;
			
			$a['text']=SelectNode($text,'div','class="preview-review');
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'ovideo.ru')!==FALSE) 
	{
			$a['publication']='Ovideo.ru';
			$a['publication_id']=87;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
			
			$text=SelectNode($text,'div', 'class="f-review-in"');
						
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));

			$a['russian']=trim(
			str_replace(array('“','”'),'',between(strip_tags(SelectNode(SelectNode($text,'h2'), 'a')),'',' ('))
			);
			
			$dateauthor=SelectNode($text,'div','style="white-space:nowrap;"');
			
			$a['date']=trim(strip_tags(str_replace('| ','',SelectNode($dateauthor,'span'))));
			
			$a['author']=trim(strip_tags(SelectNode(SelectNode($text,'div','class="copyright"'),'p')));

			$a['grade']='';


			$a['type']=0;
			
			$a['text']=between(SelectNode($text,'div','class="text'),'','<!--');
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'itc.ua')!==FALSE) 
	{
			$a['publication']='ITC.ua';
			$a['publication_id']=60;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
						
			$text=SelectNode($text,'article', 'class="post"');
			
			// $a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));

			$a['original']=trim(strip_tags(SelectNode(SelectNode($text,'div','class="spec1"'),'h1')));
			$a['russian']=$a['original'];
			
			$a['date']=trim(strip_tags(SelectNode(SelectNode($text,'header'),'time')));
			
			$a['author']=trim(strip_tags(SelectNode(SelectNode($text,'span', 'class="avtor"'),'a')));

			if (mb_strstr($text,'-editors-choice-'))
				$a['grade']='#отлично';

			$a['type']=1;
			
			$a['text']=RemoveNode(RemoveNode(RemoveNode(SelectNode($text,'div','class="article-content _ga1_on_"'),'div','class="spec1"'),'div','class="gallery'),'iframe');
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'gameguru.ru')!==FALSE) 
	{
			$a['publication']='GameGuru.ru';
			$a['publication_id']=51;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
						
			$a['summary']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'class="jointCard-review-score-content"'),'div', 'class="jointCard-review-score-text"')));

			$a['russian']=trim(strip_tags(SelectNode($text,'h2', 'itemprop="name"')));
			
			$a['date']=trim(strip_tags(SelectNode($text,'div','class="jointCard-result-review-info"')));
			
			$a['author']=trim(strip_tags(SelectNode($text,'a', 'itemprop="author"')));

			$a['grade']=trim(strip_tags(SelectNode(SelectNode($text,'div', 'itemprop="reviewRating"'),'b','itemprop="ratingValue"')));


			$a['type']=1;
			
			$a['text']='<p>'.between(SelectNode($text,'div','class="jointCard-review-content"'),'<p>');
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'itogi.ru')!==FALSE) 
	{
			$a['publication']='Итоги';
			$a['publication_id']=151;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
			
			// $a['summary']=trim(strip_tags(SelectNode($text,'p', 'class="subhead"')));

			$a['russian']=trim(between(strip_tags(SelectNode($text,'p', 'class="preamb"')),'«','»'));

			$a['date']=trim(between(strip_tags(SelectNode($text,'a', 'class="magazine_number"')),'(',')'));
			
			$a['author']=trim(strip_tags(SelectNode($text,'a', 'class="author"')));

			$a['type']=0;
			
			$a['text']=SelectNode(SelectNode($text,'td','class="article_text"'),'div');
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'vedomosti.ru')!==FALSE) 
	{
			$a['publication']='Ведомости';
			$a['publication_id']=148;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);
			
			
			$a['summary']=trim(strip_tags(SelectNode($text,'p', 'class="subhead"')));

			$a['russian']=trim(between(strip_tags(SelectNode($text,'h1', 'itemprop="name"')),'«','»'));
			
			//$a['russian']=trim(strip_tags(SelectNode($text,'h1', 'itemprop="name"')));

			$a['date']=trim(strip_tags(SelectNode($text,'p', 'itemprop="datePublished"')));
			
			$a['author']=trim(strip_tags(SelectNode($text,'span', 'itemprop="author"')));
			
			$a['grade']='';
			
			$a['type']=0;
			
			$a['text']=SelectNode(SelectNode($text,'div','class="article_text"'),'div','itemprop="articleBody"');
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
			
			$a['issue']=$url;			
			
	}
	
	else
	if (mb_strpos($url,'sibdepo.ru')!==FALSE) 
	{
			$a['publication']='sibdepo.ru';
			$a['publication_id']=201;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);

			$a['russian']=trim(strip_tags(betweens($text,array('<h1>','</h1>','«','»'))));
			if ($a['russian']=='')
				$a['russian']=trim(strip_tags(betweens($text,array('content="Рецензия на','"','«','»'))));			
			if ($a['russian']=='')
				$a['russian']=trim(strip_tags(betweens($text,array('<a_replace>','Продолжительность:','<br','<','>',''))));
			
			$a['type']=0;

			// $text=between($text,'','</a_replace>');

			$a['text']=betweens($text,array('<a_replace>','</a_replace>'));
			
			$a['author']=trim(strip_tags(betweens($text,array('<strong>Автор: ','</strong>'))));

			$a['date']=trim(strip_tags(betweens($text,array('<span class="fix_date2">','</span>'))));
			
			$a['grade']=preg_replace('/[^0-9\,\.]/si','',trim(strip_tags(betweens($text,array('Оценка:',' из 10')))));
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'relax.by')!==FALSE) 
	{

			$a['publication']='Relax.by';
			$a['publication_id']=205;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);

			$a['russian']=trim(strip_tags(betweens($text,array('<h1>','</h1>','«','»'))));
			if ($a['russian']=='')
				$a['russian']=trim(strip_tags(betweens($text,array('content="Рецензия на','"','«','»'))));			
			if ($a['russian']=='')
				$a['russian']=trim(strip_tags(betweens($text,array('<a_replace>','Продолжительность:','<br','<','>',''))));
			
			$a['type']=0;

			// $text=between($text,'','</a_replace>');
			
			$a['text']=SelectNode($text,'div','class="b-journal-main-inner"');
			
			// 			$a['text']=SelectNode($text,'p','class="current"');
			
			$a['text']=betweens($a['text'],array('','<article class="b-feedbacks">'));
			
			$a['text']=RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes(RemoveNodes($a['text'],'div','class="b-journal-breadcrumbs"'),'div','class="b-journal_article-about"'),'div','class="b-journal_article_gallery"'),'div','class="b-journal_article-paginator-top'),'div','class="b-journal_banner-active"'),'div','class="b-journal_interesting-articles"'),'p','color:#0095cc');
			
			$auu=explode(' ',trim(strip_tags(betweens($text,array('<b>Текст:</b>',',')))));
			$a['author']=$auu[1].' '.$auu[0];
						
			$a['date']=trim(strip_tags(betweens(SelectNode(SelectNode($text,'div','b-journal_article-about_dsc'),'span'),array(',','')))).' '.date('Y');
			
			
			$a['grade']=preg_replace('/[^0-9\,\.]/si','',trim(strip_tags(betweens($text,array('Оценка:',' из 10')))));
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
			
			$a['issue']=$url;			
			
	}
	
	else
	
	if (mb_strpos($url,'www.afisha.ru')!==FALSE) 
	{

			$a['publication']='Афиша';
			$a['publication_id']=80;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);

			$a['russian']=trim(strip_tags(betweens($text,array('<span itemprop="name">','</span>'))));
			if ($a['russian']=='')
				$a['russian']=trim(strip_tags(betweens($text,array('<p class="orig-name">','</>'))));
			
			if (mb_strpos(betweens($text,array('<p class="object-type">','</p>')),'Фильм')!==FALSE)
				$a['type']=0;
			else
			if (mb_strpos(betweens($text,array('<p class="object-type">','</p>')),'Игра')!==FALSE)
				$a['type']=1;

			$text=between($text,'<div class="b-review">','<span class="special">Специально для');

			$a['text']=betweens($text,array('<div class="b-entry">','</div>'));
			
			$a['author']=trim(strip_tags(betweens($text,array('<h3>','</h3>'))));

			$a['date']=trim(strip_tags(betweens($text,array('<div class="b-entry-info m-footer">','<span'))));
			
			$a['grade']=trim(strip_tags(str_replace('/10','',betweens($text,array('<em class="mask pngfix"','</em>','>Оценка: ',' из 5')))));
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
			
			$a['issue']=$url;			
			
	}
	
	else

	if (mb_strpos($url,'ru.ign.com')!==FALSE) 
	{

			$a['publication']='IGN';
			$a['publication_id']=185;
			
			// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--'),array(' ','—','—','«','»','-','"','-'),$text);

			$a['russian']=strip_tags(trim(SelectNode(SelectNode($text,'h1'),'a')));
	
			$a['original']=$a['russian'];
			
			$a['title']=strip_tags(mb_trim(SelectNode($text,'h2')));

			$a['type']=1;
			
			$a['text']=SelectNode($text,'div','class="articleContent"');

			$a['author'] = trim(strip_tags(SelectNode(SelectNode($a['text'],'span','class="byline"'),'span','itemprop="reviewer"')));

			$a['date']=trim(strip_tags(SelectNode(SelectNode($a['text'],'span','class="byline"'),'time')));

			$scorecard=SelectNode($text,'div','class="scorecard wide"');
			$a['grade']=trim(strip_tags(SelectNode($text,'div','class="rating"')));
	
	
			if ($a['grade']=='')
				$a['grade']=trim(strip_tags(str_replace('/10','',betweens($text,array('<td class="ratingsBoxScoreOv">','</td>')))));
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),RemoveNodes(RemoveNodes($a['text'],'span','class="byline"'),'h6')))));

		$a['summary']=trim(strip_tags(RemoveNodes(SelectNode($text,'div','class="articleVerdict"'),'h6')));
			
			$a['issue']=$url;			
			

	}
	
	else

	if (mb_strpos($url,'hkcinema.ru')!==FALSE) 
	{

			$a['publication']='Hong Kong Cinema';
			$a['publication_id']=197;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&laquo;','&raquo;','&#8211;'),array(' ','—','«','»','-'),between($text,'<div class="review-body">','<div style="clear:left">'));
						
			$a['russian']=strip_tags(trim(betweens($text,array('<h1','</h1>','class="section-titl">',''))));
			$a['original']=strip_tags(trim(betweens($text,array('<h2','</h2>','class="section-titl-eng">',''))));

			$a['type']=0;
			
			$a['text']=betweens($text,array('<div class="review-review">','<div class="review-rating">','<div class="sec-name">впечатления:</div>'));
			
			$a['author']=trim(strip_tags(betweens($text,array('автор рецензии:','</a>'))));

			$a['date']=trim(strip_tags(betweens($text,array('дата публикации:','</b>','<b>'))));
			
			$a['grade']=trim(strip_tags(betweens($text,array('Общая оценка:','</dl>'))));
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
															
			$a['issue']=$url;			
			
			$sum=preg_split("/\n\n/si",$a['text']);
			foreach ($sum as $su)
			{
				if (mb_strlen($su)>100 && mb_substr($su,0,7)!='Тривия:' && mb_substr($su,0,8)!='Награды:')
					$a['summary']=$su;
			}
	}
	
	else	
	
	if (mb_strpos($url,'kinotom.com')!==FALSE) 
	{

			$a['publication']='Кинотом';
			$a['publication_id']=188;
			
			//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&laquo;','&raquo;','&#8211;'),array(' ','—','«','»','-'),between($text,'<div class="up-shade">','<div>'));
			
			$text=before_first_str($text,'<p>_______');
			
			$a['russian']=strip_tags(trim(betweens($text,array('<h1','</h1>','>',''))));
			if ($a['russian']=='')
				$a['russian']=strip_tags(trim(betweens($text,array('<h2','</h2>','>',''))));

			$a['type']=0;
			
			$a['text']=betweens($text,array('</h3>',''));
			
			$a['author']=trim(betweens($a['text'],array('<h3>Рецензент:','</h3>')));

			$a['date']=trim(strip_tags(betweens($text,array('Добавлено: ','<'))));
			
			//$a['grade']=betweens($a['text'],array('alt="оценка: ','"'));
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
			
			preg_match('/<h3>Рецензент(.*?)src="(.*?)stamp\.jpg"/si',$text,$img);
			if ($img[2]!='')
				$a['image']=$img[2].'stamp.jpg';
			else
				$a['image']='';
															
			$a['issue']=$url;			
			
			$sum=preg_split("/\n\n/si",$a['text']);
			foreach ($sum as $su)
			{
				if (mb_strlen($su)>100)
					$a['summary']=$su;
			}
	}
	
	else
	
	if (mb_strpos($url,'http://www.ekranka.ru')!==FALSE) 
	{

			$a['publication']='Экранка.ру';
			$a['publication_id']=82;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&laquo;','&raquo;'),array(' ','—','«','»'),between($text,'<td class="td2params"','<div align="center">'));
			
			$rusor=trim(betweens ($text,array('<h1>','</h1>','','(')));
			
			$a['russian']=trim(betweens($rusor,array('','/')));
			$a['original']=trim(betweens($rusor,array('/','')));

			$a['type']=0;
			
			$a['text']=betweens($text,array('<div class="hr">','</span>'));			

			if ($test==1)
				echo $a['text'];		

			$a['author']=betweens($a['text'],array('<h3>','</h3>','<a name="','</a>','<a href="','</a>','>'));

			$a['date']=trim(strip_tags(betweens($text,array('<span class="rubric">(',')'))));
			
			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
									
			$a['issue']=$url;			
			
			$sum=preg_split("/\n\n/si",$a['text']);
			foreach ($sum as $su)
			{
				if (mb_strlen($su)>100)
					$a['summary']=$su;
			}
	}
	
	else

	if (mb_strpos($url,'http://relax.ngs.ru')!==FALSE) 
	{

			$a['publication']='НГС.РЕЛАКС';
			$a['publication_id']=187;
			
			//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&laquo;','&raquo;','<b>','</b>','<a href="/tags/Рецензии/news" class="tag ">Рецензии</a>','<a href="/news/music" class="topic">Музыка</a>','<a href="/news/auto" class="topic">Авто</a>'),array(' ','—','«','»','<b>','</b>','','',''),between($text,'<div class="article_block">','<div class="clear">'));

			$a['russian']=trim(betweens($text,array('<h1>','</h1>','«','»')));

			$a['type']=0;
			
			$a['text']=betweens($text,array('<div class="article-text">','<div style="display: none;">'),0,FALSE);			
			$a['text']=preg_replace ("/<i>(.*?)Справка:(.*?)<\/i>/si","",$a['text']);
			$a['text']=preg_replace ('/<div class="float_right">(.*?)<\/div>/si',"",$a['text']);

			if (mb_strpos($a['text'],'Елена Полякова')!==FALSE)
				$a['author']='Елена Полякова';
			else
			if (mb_strpos($a['text'],'Владимир Иткин')!==FALSE)
				$a['author']='Владимир Иткин';
			else
			$a['author']=between($a['text'],'<i>','<br');

			$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text']))));
									
			$a['issue']=$url;			
			
			$a['date']=trim(strip_tags(betweens($text,array('<span class="article-date">','</span>'))));
			
			if ($a['summary']=='')
			{
				$sum=preg_split("/\n\n/si",$a['text']);
				foreach ($sum as $su)
				{
					if (mb_strlen($su)>100)
						$a['summary']=$su;
				}
			}
	}
	
	else

	if (mb_strpos($url,'old-games.ru')!==FALSE)
	{

			$a['publication']='Old-Games.RU';
			$a['publication_id']=166;
			
			$a['issue']=$url;

			$a['type']=1;			
	
			$a['original']=trim(before_first_str(betweens($text,array('<h1 class="game_title">','</h1>')),'['));
			if ($a['original']=='')
				$a['original']=trim(before_first_str(betweens($text,array('<h1 class="title">','</h1>')),'['));
	
			$a['russian']='';
	
			$a['author']=trim(strip_tags(betweens($text,array('<div class="game_review_author">Автор обзора: ','</div>'))));
			if ($a['author']=='')
				$a['author']=trim(strip_tags(betweens($text,array('itemprop="reviewer">','<'))));	
	
			$a['date']=trim(betweens($text,array('Дата:','</span>','<span class="red">','')));
			if ($a['date']=='')
			{
				$imgname=trim(betweens($text,array('<div class="game_info_cover">','</div>','<img src="','"')));
				if ($imgname!='')
				{
					$im = new imagick("http://www.old-games.ru".$imgname);
					$exifArray = $im->getImageProperties("exif:*");
					$baddate=explode(':',before_first_str($exifArray['exif:DateTime'],' '));
					$a['date']=$baddate[2].'.'.$baddate[1].'.'.$baddate[0];
				}
			}
	
			$a['text']=trim(strip_tags(betweens($text,array('<div id="reviewtext"','<div class="game_review_author"','<p>','</p>')),'<p><br><div>'));
			if ($a['text']=='')
				$a['text']=trim(strip_tags(betweens($text,array('itemtype="http://data-vocabulary.org/Review">','<b>Автор:</b>')),'<p><br><div>'));
			
			$a['grade']=trim(betweens($text,array('alt="Оценка рецензента - ',' из 10"')));

	} else

	if (mb_strpos($url,'http://weburg.net')!==FALSE) 
	{

			$a['publication']='Weburg';
			$a['publication_id']=186;
			
			$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&laquo;','&raquo;','<b>','</b>','<a href="/tags/Рецензии/news" class="tag ">Рецензии</a>','<a href="/news/music" class="topic">Музыка</a>','<a href="/news/auto" class="topic">Авто</a>'),array(' ','—','«','»','<strong>','</strong>','','',''),between($text,'<h1>','<div class="socialization_panel-margin">'));

			$a['title']=trim(betweens($text,array('','</h1>','«','»')));
			if ($a['title']=='')
				$a['title']=trim(betweens($text,array('','</h1>','Обзор игры ','')));
			if ($a['title']=='')
				$a['title']=trim(betweens($text,array('','</h1>','',': ')));

			if (mb_strpos(SelectNode($text,'div','class="tags_wrapper"'),'href="/news/hi-tech"')!==FALSE)
			{
				$a['type']=1;
				$a['original']=trim(betweens($a['title'],array('','</h1>')));
				/*
				$grade_vis=betweens($text,array('">Графика</strong>','</tr>','title="Оценка: ',' из 10"'));
				$grade_gam=betweens($text,array('">Геймплей</strong>','</tr>','title="Оценка: ',' из 10"'));
				$grade_scr=betweens($text,array('">Сюжет</strong>','</tr>','title="Оценка: ',' из 10"'));
				$grade_snd=betweens($text,array('">Звук</strong>','</tr>','title="Оценка: ',' из 10"'));
				$a['grade']=round(($grade_vis+$grade_gam+$grade_scr+$grade_snd)/(($grade_vis!=''?1:0)+($grade_gam!=''?1:0)+($grade_scr!=''?1:0)+($grade_snd!=''?1:0))*10)/10;
				*/
				$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<BR>','</p>','</P>'),array("\n","\n","\n\n","\n\n"),betweens($text,array('<div class="description">','Итоговый вердикт','','<strong>'.$a['author'].'</strong>','Графика</strong>',''),0,FALSE)))));
				$a['author']=trim(strip_tags(betweens($text,array('Итоговый вердикт','','<a class="internal_blank"','</a>','<strong>','</strong>'))));
				
			}
			else
			{
				$a['type']=0;
				$a['russian']=trim(betweens($a['title'],array('','</h1>')));
				
				/*
				$grade_vis=betweens($text,array('">Зрелищность</strong>','</tr>','title="Оценка: ',' из 10"'));
				$grade_act=betweens($text,array('">Актерская игра</strong>','</tr>','title="Оценка: ',' из 10"'));
				$grade_scr=betweens($text,array('">Сюжет</strong>','</tr>','title="Оценка: ',' из 10"'));
				$a['grade']=round(($grade_vis+$grade_act+$grade_scr)/(($grade_vis!=''?1:0)+($grade_act!=''?1:0)+($grade_scr!=''?1:0))*10)/10;	
				*/
				
				$a['text']=preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<BR>','</p>','</P>'),array("\n","\n","\n\n","\n\n"),betweens($text,array('<div class="description">','<a class="internal_blank"','','Итоговый вердикт','','<strong>'.$a['author'].'</strong>','Зрелищность</strong>',''),0,FALSE)))));
				$a['author']=trim(strip_tags(betweens($text,array('<div class="tags_wrapper">','</div>','>Кино</a>','</a>','>',''))));
			}

			$grpos=mb_strpos($text,'<td class="wbmagazine__total_table_score_cell-block-description">Итог</td>');
			$a['grade']=trim(strip_tags(between(mb_substr($text,$grpos-30,30),'>','')));

			$a['title']=trim(betweens($a['title'],array('»: ','')));
						
			$a['issue']=$url;			
			
			$a['date']=trim(strip_tags(betweens($text,array('class="newsauthor news_public_date">','</p>'))));
			$ad=explode(' ',$a['date']);
			$add=explode ('.',$ad[0]);
			if (sizeof($add)==2)
			{
				$a['date']=$add[0].'.'.$add[1].'.'.date('y').' '.$ad[1];
				
			}
			
			$a['summary']=trim(str_replace(array('&nbsp;','&mdash;'),array(' ','—'),strip_tags (betweens($text,array('Итоговый вердикт</div>','<table')))));

			if ($a['summary']=='')
			{
				$sum=preg_split("/\n\n/si",$a['text']);
				foreach ($sum as $su)
				{
					if (mb_strlen($su)>100)
						$a['summary']=$su;
				}
			}			
			if ($test==1)
				echo nl2br($a['summary']);

	} else
	
	if (mb_strpos($url,'kinopark.by')!==FALSE) 
	{

			$a['publication']='Кинопарк';
			$a['publication_id']=162;
			
			$text=between($text,'<div id="chtivo">','<br clear="all">');
			
			$a['issue']=$url;
						
			$a['russian']=trim(betweens($text,array('<h1>','</h1>','«','»')));;
			$a['title']=trim(betweens($text,array('<h1>','</h1>','»: ','')));;
			
			$a['author']=trim(betweens($text,array('<p class="gr">','</p>','',',')));
			$a['date']=trim(betweens($text,array('<p class="gr">','</p>',',','')));
			
			$a['summary']=strip_tags (betweens($text,array('<h2>','</h2>')));

	} else

	if (mb_strpos($url,'obzorkino.tv')!==FALSE) 
	{
		// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");

		$a['issue']=$url;

		$a['publication']='Обзоркино';
		$a['namelink']='Обзоркино';
		$a['sitelink']='http://obzorkino.tv';
		
		$a['russian']=trim(betweens($text,array('<h3>','</h3>','<span class="current">','</span>','&#171;','&#187;')));
		if ($a['russian']=='')
			$a['russian']=trim(betweens($text,array('<h3>','</h3>','<span class="current">','</span>','«','»')));
		if ($a['russian']=='')
			$a['russian']=trim(betweens($text,array('<h3>','</h3>','<span class="current">','</span>','&laquo;','&raquo;')));

		$a['original']='';

		$a['title']=trim(betweens($text,array('<h3>','</h3>','<span class="current">','</span>','',': ')));

		$a['date']=trim(betweens($text,array('<li class="calendar">','</li>')));

		$a['author']=trim(betweens($text,array('rel="author">','</a>')));
		
		$a['text']=trim(betweens($text,array('<div class="postcontent">','<p>Понравилась рецензия?')));

		$a['summary']=utf8_ucfirst(trim(betweens($a['text'],array('<p><strong>Вердикт</strong>: ','</p>'))));
		
			$a['text']=trim(strip_tags(before_first_str($a['text'],'<p><strong>Вердикт</strong>:'),'<p><br>'));	
		
		$a['grade']=betweens($text,array('<li class="category">','</li>'));
		if (mb_strpos($a['grade'],'Хорошее кино')!==FALSE)
			$a['grade']='Хорошее кино';
		else
		if (mb_strpos($a['grade'],'Так себе кино')!==FALSE)
			$a['grade']='Так себе кино';
		else
		if (mb_strpos($a['grade'],'Плохое кино')!==FALSE)
			$a['grade']='Плохое кино';
		else $a['grade']='';
		$a['type']=0;

	} else

	if (mb_strpos($url,'sqd.ru')!==FALSE) 
	{
		$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");

		$a['issue']=$url;

		$a['publication']='SQD.ru';
		$a['namelink']='SQD.ru';
		$a['sitelink']='http://sqd.ru';
		
		$a['russian']=trim(betweens($text,array('<h1>',' /')));
		$a['original']=trim(betweens($text,array('<h1>','</h1>',' / ','')));

		$a['date']=trim(betweens($text,array('<span class=ts>','</span>','</a>, ','')));

		$a['author']=trim(betweens($text,array('<a href="users/','</a>','">','')));
		
		$a['text']=trim(strip_tags(betweens($text,array('<index>','</index>')),'<p><br>'));
		
		$a['grade']=trim(betweens($text,array('<b>Оценка автора обзора: ','</b>')));
		$a['type']=0;
		

	} else
	
	if (mb_strpos($url,'uralweb.ru')!==FALSE) 
	{
		//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");

		$a['issue']=$url;

		$a['publication']='Uralweb.ru';
		$a['namelink']='Uralweb.ru';
		$a['sitelink']='http://uralweb.ru';
		
		$a['russian']=mb_trim(betweens($text,array('<h1>','</h1>')),' "');

		$a['date']=trim(betweens($text,array('<div class="ri-nameauth">','</div>',',','')));

		$a['author']=trim(betweens($text,array('<div class="ri-nameauth">',',')));
		
		$a['text']=trim(strip_tags(betweens(RemoveNodes(SelectNode($text,'div','id="article_body"'),'div','class="noted-img"'),array('',' в кинотеатрах Екатеринбурга'),'<p><br>')));
		
		if (preg_match('/<([a-z\/]+)>([0-9]+) балл(.+)</',$a['text'],$matches))
		{
			$a['grade']=$matches[2];
			$a['text']=before_first_str($a['text'],'<'.$matches[1].'>'.$matches[2].' балл');
		} else
		if (preg_match('/([0-9]+) балл/',$a['text'],$matches))
		{
			$a['grade']=$matches[1];
		}
		
		
		$a['type']=0;
		

	} else


	if (mb_strpos($url,'startnovosti.ru')!==FALSE) 
	{
		$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");

		$a['issue']=$url;

		$a['publication']='Старт Новости.ру';
		$a['namelink']='Старт Новости.ру';
		$a['sitelink']='http://startnovosti.ru';
		
		$a['russian']=mb_trim(betweens($text,array('&raquo; <strong>','</strong>')),' "');

		$a['date']=trim(betweens($text,array('<span class="category"> <strong>','</strong>')));

		$a['author']=trim(betweens($text,array('subaction=userinfo&amp;user=Soundwave">','</a>')));
		
		$a['text']=trim(strip_tags(betweens($text,array('<div id="news-id','<strong>Комментарии','<p>','')),'<p><br>'));
		
		if (preg_match('/<br \/><br \/>([0-9]+)\/([0-9]+)/',$a['text'],$matches))
		{
			$a['grade']=$matches[1];
			$a['text']=before_first_str($a['text'],'<br /><br />'.$matches[1].'/'.$matches[2]);
		} else
		{
			if (preg_match('/([0-9]+)\/([0-9]+)/',$a['text'],$matches))
			{
				$a['grade']=$matches[1];
			}
		}
		$a['type']=0;
		
	} else

		
	if (mb_strpos($url,'aif.ru')!==FALSE) 
	{
		// $text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");

		$a['issue']=$url;

		$a['publication']='Аргументы и факты';
		$a['namelink']='Аргументы и факты';
		$a['sitelink']='http://www.aif.ru';
		
		$text=SelectNode($text,'section','class="articl_header');
		
		$a['title']=mb_trim(strip_tags(SelectNode($text,'h1')));

		$intro=SelectNode($text,'div','class="prew_tags"');
		
		$a['text']=SelectNode($intro,'div','class="articl_prew_text');
		
		$a['russian']=mb_trim(after_first_str(strip_tags(SelectNode(SelectNode($intro,'div','class="articl_tag'),'a','href="/tag/film')),'фильм '));
		
		$a['author']=trim(betweens($text,array('<div class="article-meta">','<ul class','<p class="author">Автор: ','</p>')));

		$a['date']=str_replace(array(' (',')'),array(', ',''),trim(betweens($text,array('<div class="article-meta">','<ul class','<p class="date">Опубликовано ','</p>'))));
		
		//$a['text']=betweens($text,array('_ga1_on_" id="article-text"','<div style="height:30px;">','>',''));

		if (preg_match('/<([a-z0-9]+)>(Режиссер|Режиссеры):/',$a['text'],$matches))
		{
			$a['text']=before_first_str($a['text'],'<'.$matches[1].'>'.$matches[2].':');
		}
		
		$a['text']=trim(strip_tags($a['text'],'<p><br>'));
		$a['type']=0;

	} else
	
	if (mb_strpos($url,'kino-govno.com')!==FALSE || mb_strpos($url,'kg-daily.ru')!==FALSE) 
	{
		if (mb_strpos($url,'/movies/')!==FALSE)
		{ 
			$section='movies';
			$a['type']=0;
		}
		else
		if (mb_strpos($url,'/games/')!==FALSE) 
		{
			$section='games';
			$a['type']=1;
		}
		
		$a['publication']='Кино-Говно.ком';
		$a['issue']=$url;
		$a['russian']=betweens($text,array('<div id="movies_hd">','</div>','<h1>','</h1>'));
		$a['original']=betweens($text,array('<div id="movies_hd">','</div>','<h1 class="second">','</h1>'));
		$a['title']=betweens($text,array('<div id="div_headline" class="headline">','</div>'));
		$a['quote']=betweens($text,array('<div id="div_quote" class="quote">','</div>'));
		$a['text']=betweens($text,array('<div id="div_text1" style="text-align: justify;">','</div>'));
		$a['text2']=betweens($text,array('<div id="div_text2" style="text-align: justify;">','</div><div id="div_author"'));
		$a['text'].=$a['text2'];
		$a['author']=betweens($text,array('<div id="div_author" style="text-align: right; text-decoration: italic; font-weight: bold;">','</div>'));
		$a['date']=betweens($text,array('<div id="div_date" style="text-align: right; text-decoration: italic;">','</div>'));
		
		if (mb_strpos($text,'src="/dabest2.gif"')!==FALSE) $a['grade']='Охуительн'.($section=='movies'?'ое кино':($section=='games'?'ая игра':''));
		else
		if (mb_strpos($text,'src="/dabest1.gif"')!==FALSE) $a['grade']='Нехуёв'.($section=='movies'?'ое кино':($section=='games'?'ая игра':''));
		else	
		if (mb_strpos($text,'src="/dabest0_2011.png"')!==FALSE) $a['grade']='Клиника';
		else	
		if (mb_strpos($text,'<span class="grade_active">Кино</span>')!==FALSE) $a['grade']='Кино';
		else	
		if (mb_strpos($text,'<span class="grade_active">Игра</span>')!==FALSE) $a['grade']='Игра';
		else	
		if (mb_strpos($text,'<span class="grade_active">Стерильно</span>')!==FALSE) $a['grade']='Стерильно';
		else	
		if (mb_strpos($text,'<span class="grade_active">Говно</span>')!==FALSE) $a['grade']='Говно';
	} else
	
	if (mb_strpos($url,'gameland.ru')!==FALSE) 
	{
		$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		if (mb_strpos($text,'Источник: Cтрана Игр')!==FALSE) 
		{
			$a['publication']='Страна Игр';
			$ais=explode(' ',str_replace('№','',between($text,'Источник: Cтрана Игр ',' года</a>')));
			$a['issue']=$ais[1].' #'.$ais[0];
			$a['grade']=betweens($text,array('Журнала «Страна Игр»','</tr>','class="green">','</div>'));
		}
		else
		{
			$a['publication']='gameland.ru';
			$a['grade']=betweens($text,array('class="marks">','</tr>','gameland.ru','','class="green">','</div>'));
			$a['date']=trim(betweens($text,array('Комментарии к статье','</table>','<td align="right"><noindex>','</noindex>')));
		}

		$a['author']=trim(betweens($text,array('<noindex>Автор: ','</noindex>')));
		if ($a['author']=='')
			$a['author']=trim(strip_tags(betweens($text,array('<tr class="gray">','</td>','<noindex>','</noindex>'))));
		if (mb_substr($a['author'],0,6)=='Автор:')
			$a['author']=trim(mb_substr($a['author'],6));
		$a['text']=trim(strip_tags(betweens($text,array('<div class="post">','<div id="ctl00_ctl00_ctl00_MainContentPlaceHolder_SingleColumnPlaceHolder_ProfileContentHolder_PostComments" class="bl_comments">')),'<p><div><br>'));
		
		if ($a['text']!='') $a['text'].='</p>';
		$a['original']=trim(str_replace('МНЕНИЕ:','',betweens($text,array('<h1>','</h1>'))));
		$a['russian']=betweens($text,array('<div class="title">АЛЬТЕРНАТИВНЫЕ НАЗВАНИЯ</div><div class="name">Россия: <span>','</span>'));
		$a['type']=1;

	} else
// ==== WWW.AG.RU ====
	if (mb_strpos($url,'www.ag.ru')!==False)
	{
		$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$title = betweens($text,array('<h1 class=logo_art>','</h1>'));
		$title = str_replace(" &mdash; рецензия", "", $title);
		$original = after_first_str($title,'Обзор ');
		
// Оценка
		$grade = strip_tags(betweens($text,array("nd_agmark_number>","<span class=halfbig>")));
// Автор и дата
		$author_info = betweens($text,array("id=nd_author_info_table>",")<br><br>"));
		$date = '';
		$ptrn = "/([0-9]{2}|[0-9]{1})[.]([0-9]{2}|[0-9]{1}).[0-9]{4}/";
		preg_match($ptrn, $author_info, $date);
		$author = betweens($author_info, array("Автор:", "</b>"));
		$author = strip_tags("$author");
// Текст
		
		$rtext = strip_tags(SelectNode($text, "div", "class=\"textblock _reachbanner_\""));
		$table = strip_tags(betweens($text,array("nd_req_table>","</table>")));
		if ($table !== '') $rtext = str_replace($table, '', $rtext);
// Вывод
		$surl = str_replace("/reviews/", "/games/", $url);
		$spage = file_get_contents($surl);
		$spage = mb_convert_encoding ($spage, "UTF-8", "Windows-1251");		
		$summary = strip_tags(SelectNode($spage, "div", "class=\"textblock_mini j\""));
		$summary = str_replace("\r\n", " ", $summary);
		$summary = str_replace("Прочитать рецензию &#187;", "", $summary);
		$rtext  = str_replace("\r\n", " ", $rtext);
		$rtext = ReplaceSpace($rtext);
		$rtext = ReplaceRN($rtext);

		$a['publication'] = 'Absolute Games';
		$a['original'] = @$original;
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['grade'] = trim(@$grade);
		$a['date'] = @$date[0];
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;

	} else
// ==== WWW.3DNEWS.RU ====
	if (mb_strpos($url,'www.3dnews.ru')!==False)
	{
		$text = str_replace("<div class=\"tHeader\">Скриншоты</div>", "", $text);
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$title = betweens($text,array("<title>","</title>"));
		$f1 = strpos($title, " | ");
		if ($f1 !== false) $title = substr($title, 0, $f1);
		$f1 = LastIndexOf($title, "—");
		if ($f1 !== -1)
		{
			$original = substr($title, 0, $f1);
		}
		else {
			$f1 = LastIndexOf($title, "–");
			if ($f1 !== -1)
			{
				$original = substr($title, 0, $f1);
			}
			else $original = $title;
		}
// Автор и дата
		$date = SelectNode($text, "span", "itemprop=\"datePublished\"");
		$date = SelectiSubs($date, "content=\"", "T");
    $date = split("-", $date);
		$htmltext = RemoveNode($text, "span", "itemprop=\"datePublished\"");
		$author = strip_tags(SelectNode($htmltext, "span", "itemprop=\"name\""));
		$text = RemoveNode($htmltext, "span", "itemprop=\"name\"");
// Оценка
		$grade = betweens($text,array("Общее впечатление</strong></td>","</tr>","<td align=\"center\"","</td>"));
		$grade = strip_tags("<$grade");
    
    if($grade == null || $grade == "")
    {
		  $grade = betweens($text,array("<strong>Оценка:","</ul>"));
		  $grade = strip_tags("$grade");
    }
// Текст
		$text = SelectNode($text, "div", "class=\"article-entry\"");
		$text = RemoveNodes($text, "table", "class=\"neat\"");
		$text = RemoveNodes($text, "div", "class=\"tHeader\"");
		$text = RemoveNodes($text, "iframe", null);
		$text = RemoveNodes($text, "H1", null);
		$text = RemoveNodes($text, "a", "class=\"twitter-share-button\"");
		$text = RemoveNodes($text, "div", "class=\"gallery-preview\"");
		$f1 = LastIndexOf($text, "Подробнее о системе оценок");
		if ($f1!==-1) { $text = substr($text, 0, $f1); }
		
		$text = str_replace("<br>", "\r\n", $text);
		$text = strip_tags($text);
		
		$text = str_replace("Скриншоты", "", $text);
		$text = str_replace("Видео:", "", $text);
		$text = str_replace("Ссылки по теме:", "", $text);
		$text = ReplaceSpace($text);
		$text = ReplaceRN($text);
		
// Вывод
		$f1 = LastIndexOf($text, "Достоинства:");
		if ($f1!==-1) { $text = substr($text, 0, $f1);}
		else
		{
			$f1 = LastIndexOf($text,"\n");
			if ($f1!==-1) { $text = substr($text, 0, $f1);}
		}
    
    $summary = SumOfText($text);
    
    $trnodes = SelectNodes($htmltext, "tr");
    foreach($trnodes as $tr)
    {
      if(mb_strpos($tr,'<strong>Общее впечатление</strong>'))
      {
        $tdnodes = SelectNodes($tr, "td");
        if($tdnodes[1] != null)
        {
          $summary = strip_tags($tdnodes[1]);
          $grade = strip_tags($tdnodes[2]);
        }
      }
    }
    
//

		$a['publication'] = '3DNews';
		$a['original'] = trim(@$original);
		$a['russian'] = trim(@$russian);
		$a['text'] = @$text;
		$a['author'] = @$author;
		$a['grade'] = str_replace('/10','',@$grade);
		$a['date'] = @$date[2].".".@$date[1].".".@$date[0];
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;
		
	} else
// ==== GameMag.ru ====
	if (mb_strpos($url,'gamemag.ru')!==False)
	{
		$text = mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			$text=str_replace(array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),array(' ','—','—','«','»','-','"','-','«','»',''),$text);

		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$original = strip_tags(betweens($text,array("<font class=\"post-title1\">", "</font>")));
// Автор и дата
		$author = betweens($text,array("Автор обзора - <strong>", "</strong>"));
		if (strlen($author) < 1) $author = betweens($text,array("Автор - <strong>", "</strong>"));
		if (strlen($author) < 1) $author = betweens($text,array("<font class=\"post-time\">Автор:", "</font>", "<strong>", "</strong></a>"));
		$date = betweens($text,array("<font class=\"post-time\">", "</font>", "Добавлено <strong>", "</strong>"));
// Текст
		$rtext = strip_tags(betweens($text,array("<div align=\"justify\" class=\"post-text\" style=\"padding: 6px;\">", "<fb:like")));
		$rtext = str_replace("Визуальный обзор базируется на основных признаках игры и является дополнением к основному обзору.", "", $rtext);
		$rtext = str_replace("Автор - $author", "", $rtext);
		$rtext = str_replace("Автор обзора - $author", "", $rtext);
		$rtext = str_replace("ВИЗУАЛЬНЫЙ ОБЗОР", "\r\n", $rtext);
		$rtext = trim($rtext);
// Вывод и оценка
		$f1 = LastIndexOf($rtext,"\r\n");
		if ($f1 !== -1) { $summary = substr($rtext, $f1); $rtext = substr($rtext, 0, $f1); }
		
		if ($summary=='')
			$summary=trim(strip_tags(before_first_str(SelectNode($text,'div','id="post2"'),'Автор - ')));
		
		
		$grade = betweens($text,array("<div align=\"center\" class=\"post-title3\">", "</div>"));
//
		$rtext = ReplaceSpace($rtext);
		$rtext = ReplaceRN($rtext);
		$a['publication'] = 'GameMag.ru';
		$a['original'] = @$original;
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['grade'] = @$grade;
		$a['date'] = @$date;
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== Gamer-Info ====
	if (mb_strpos($url,'http://gamer-info.com')!==False)
	{
		$text = str_replace("&nbsp;", " ", $text);
		$text = RemoveSubsAll($text,"<script","</script>");
		$text = RemoveSubsAll($text,"Видеообзор от Gamer-Info","Мб)</a></div>");
// Название
		$original = strip_tags(SelectNode(SelectNode($text, "div", "class=\"info\""),'div','class="hdt"'));
// Автор, дата, оценка
		$author = strip_tags(SelectNode($text, "span", "itemprop=\"reviewer\""));
		$date = strip_tags(SelectNode($text, "span", "itemprop=\"dtreviewed\""));
		$grade = strip_tags(SelectNode($text, "span", "itemprop=\"rating\""));
// Текст и вывод
		$rtext = SelectNode($text, "div", "itemprop=\"description\"");
		$summary = strip_tags(betweens($rtext,array("<p class=\"no-indent\" style=\"text-align: center;\">", "<div class=\"pl-min\">")));
		$rtext = RemoveSubs($rtext,"<p class=\"no-indent\" style=\"text-align: center;\">", "<div class=\"pl-min\">");
		$rtext = strip_tags($rtext);
		$rtext = str_replace($summary, "", $rtext);
		$rtext = ReplaceSpace($rtext);
		$rtext = ReplaceRN($rtext);
		$summary = str_replace("***", "", $summary);
//
		$a['publication'] = 'Gamer Info';
		$a['original'] = @$original;
		$a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['grade'] = @$grade;
		$a['date'] = @$date;
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== GamerPro ====
	if (mb_strpos($url,'http://gamerpro.ru')!==False)
	{
		$text = mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$original = strip_tags(betweens($text,array("<h1><a href=\"/games/", "</h1>","\">","</a>")));
// Автор, дата, оценка
		$author = strip_tags(betweens($text,array("Автор: ", "<br>")));
		$text = RemoveSubs($text,"Автор: ", "<br>");
		if (!$author) { $author = strip_tags(betweens($text,array("Добавил: ", "<br>")));
			$text = RemoveSubs($text,"Добавил: ", "<br>"); }
		$date = strip_tags(betweens($text,array("Дата добавления: ", "<br>")));
		$text = RemoveSubs($text,"Дата добавления: ", "<br>");
		$grade = strip_tags(betweens($text,array("<div style=\"display:inline;\" id=\"average\">", "</div>")));
		$text = RemoveSubs($text,"<div style=\"display:inline;\" id=\"average\">", "</div>");
// Текст
		$rtext  = SelectNode($text, "td", "class=\"content_l\"");
		$rtext  = strip_tags(SelectNode($text, "div", "class=\"lblock\""));
		$rtext = ReplaceSpace($rtext);
		$rtext = ReplaceRN($rtext);
//
		$a['publication'] = 'GamerPro';
		$a['original'] = @$original;
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['grade'] = @$grade;
		$a['date'] = @$date;
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== Боевой народ ====
	if (mb_strpos($url,'http://games.cnews.ru')!==False)
	{
		$text = mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text = RemoveSubsAll($text,"<script","</script>");
		$pages = null;
// Название
		$title = strip_tags(SelectNode($text, "td", "class=bolshoi"));
		$original = str_replace("Обзор ","",$title);
// Текст
		$HTMLtext = SelectNode($text, "td", "main _ga1_on_");
		$f1 = stripos($HTMLtext, "<p class=\"mainsmall2\">");
		if ($f1 !== false) { $f2 = stripos($HTMLtext, "</p>"); if ($f2 !== false) $HTMLtext = substr($HTMLtext, $f2 + 4); }
		$f1 = stripos($HTMLtext, "<p");
		if ($f1 !== false) $HTMLtext = substr($HTMLtext, $f1);
		$f1 = stripos($HTMLtext, "<div align=\"center\"><b><noindex><a href=\"http://forum.cnews.ru/games_art.php?");
		if ($f1 !== false) $HTMLtext = substr($HTMLtext, 0, $f1);
		$f1 = stripos($HTMLtext, "<table class=\"main-2\"");
		if ($f1 !== false) $HTMLtext = substr($HTMLtext, 0, $f1);
		$f1 = lastIndexOf($HTMLtext, "<div align=\"center\">Страницы:");
		if ($f1 !== -1) $HTMLtext = substr($HTMLtext, 0, $f1); 
		$HTMLtext = trim($HTMLtext);
		@$grade = trim(strip_tags(betweens($text,array("Оценка редакции:", "</div>"))));

		$HTMLtext = "<txt class=\"htmlbody\">$HTMLtext";
		$pgs = SelectNode($text, "td", "main _ga1_on_");
		$pgs = betweens($pgs,array("Страницы:", "</div>"));
		$p = true;
		$n=0;
		while($p){
			if (stripos($pgs, "<a")) {
				$pages[$n] = betweens($pgs,array("href=\"", "\""));
				$pgs = RemoveNode($pgs, "a", "href");
			} else $p=false;
		}
		if ($pages !==null){
			foreach ($pages as $p2){
				$text2=file_get_contents($p2);
				$text2 = mb_convert_encoding ($text2, "UTF-8", "Windows-1251");
				$text2 = RemoveSubsAll($text2,"<script","</script>");
				if (!$grade) @$grade = trim(strip_tags(betweens($text2,array("Оценка редакции:", "</div>"))));
				$HTMLtext2 = SelectNode($text2, "td", "main _ga1_on_");
				$f1 = stripos($HTMLtext2, "<p class=\"mainsmall2\">");
				if ($f1 !== false) { $f2 = stripos($HTMLtext2, "</p>"); if ($f2 !== false) $HTMLtext2 = substr($HTMLtext2, $f2 + 4); }
				$f1 = stripos($HTMLtext2, "<p");
				if ($f1 !== false) $HTMLtext2 = substr($HTMLtext2, $f1);
				$f1 = stripos($HTMLtext2, "<div align=\"center\"><b><noindex><a href=\"http://forum.cnews.ru/games_art.php?");
				if ($f1 !== false) $HTMLtext2 = substr($HTMLtext2, 0, $f1);
				$f1 = stripos($HTMLtext2, "<table class=\"main-2\"");
				if ($f1 !== false) $HTMLtext2 = substr($HTMLtext2, 0, $f1);
				$f1 = lastIndexOf($HTMLtext2, "<div align=\"center\">Страницы:");
				if ($f1 !== -1) $HTMLtext2 = substr($HTMLtext2, 0, $f1); 
				$HTMLtext2 = trim($HTMLtext2);
				$HTMLtext = "$HTMLtext\n$HTMLtext2";
                                        }
		}
		$HTMLtext = "$HTMLtext</txt>";
// Вывод
		$f1 = LastIndexOf($HTMLtext,"<p><strong>");
		if ($f1!==-1){
			$f2 = strpos($HTMLtext,"</strong></p>", $f1);
			if ($f2 !== false){
				$summary = strip_tags(substr($HTMLtext, $f1, $f2 - $f1 + 9));
				$HTMLtext = substr_replace($HTMLtext, "", $f1, $f2 - $f1 + 9);
			}
		}
// Цитата
		$quo = SelectNodes($HTMLtext, "p", "main-2");
		if ($quo){
			foreach ($quo as $q) {
				$q = trim(strip_tags($q));
				@$quote = "$quote\n$q";
				$HTMLtext = RemoveNode($HTMLtext, "p", "main-2");
			}
		}
		$quo = SelectNodes($HTMLtext, "div", "main-2");
		if ($quo){
			foreach ($quo as $q) {
				$q = trim(strip_tags($q));
				@$quote = "$quote\n$q";
				$HTMLtext = RemoveNode($HTMLtext, "div", "main-2");
			}
		}
		$quo = SelectNodes($HTMLtext, "td", "mainsmall2");
		if ($quo){
			foreach ($quo as $q) {
				$q = trim(strip_tags($q));
				@$quote = "$quote\n$q";
				$HTMLtext = RemoveNode($HTMLtext, "td", "mainsmall2");
			}
		}
// Автор и дата
		@$author = trim(strip_tags(betweens($text,array("class=\"mainsmall2\">Автор:", "<br>"))));
		@$date = trim(strip_tags(betweens($text,array("Дата публикации:", "<br>"))));
//

		$author=str_replace('Редакция Боевого Народа','Редакция',before_first_str($author,' ('));
		
		$HTMLtext=preg_replace('/<strong>Издатель:<\/strong>.+<\/p>/siU','',$HTMLtext);
		
		@$rtext = strip_tags($HTMLtext);
		@$rtext = ReplaceSpace($rtext);
		@$rtext = ReplaceRN($rtext);
		@$summary = ReplaceSpace($summary);
		@$summary = ReplaceRN($summary);
		@$quote = ReplaceSpace($quote);
		@$quote = ReplaceRN($quote);
//
		$a['publication'] = 'Боевой народ';
		$a['original'] = @$original;
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['grade'] = @$grade;
		$a['date'] = @$date;
		$a['quote'] = trim(@$quote);
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== GameTech ====
	if (mb_strpos($url,'http://www.gametech.ru')!==False)
	{
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$title = strip_tags(SelectNode($text, "title"));
		$f1 = LastIndexOf($title, " - ");
		if ($f1 !== -1)
		{
			$original = mb_substr($title, 0, $f1);
			$rtitle = mb_substr($title, $f1 + 3);
		} else
		{
			$f1 = LastIndexOf($title, " – ");
			if ($f1 !== -1)
			{
				$original = mb_substr($title, 0, $f1);
				$rtitle = mb_substr($title, $f1 + 3);
			} else
			{
				$f1 = LastIndexOf($title, " — ");
				if ($f1 !== -1)
				{
					$original = mb_substr($title, 0, $f1);
					$rtitle = mb_substr($title, $f1 + 3);
				} else $original = $title;
			}
		}
		$original = Trim(str_replace("Обзор", "", $original));
// Текст и Вывод
		$HTMLtext = SelectNode($text, "div", "class=\"news_list\"");
		$HTMLtext = SelectNode($HTMLtext, "div", "class=\"item\"");
		$InfoBlock = SelectNode($HTMLtext, "div", "item_info_block");
		$HTMLtext = RemoveNode($HTMLtext, "div", "item_info_block");
		$HTMLtext = RemoveNode($HTMLtext, "div", "class=\"game\"");
		$HTMLtext= str_replace("<h3>$title</h3>", "", $HTMLtext);
		$rtext = strip_tags($HTMLtext);		
		$f1 = LastIndexOf($rtext,"Diagnosis");
		if ($f1!== -1)
		{
			$summary = substr($rtext,$f1+9);
			$rtext = substr($rtext,0,$f1);
			
			$f1 = LastIndexOf($summary,"Pro:");
			if ($f1!== -1)
			{
				$summary = substr($summary,0,$f1);
			}
		} 

		
// Автор и дата
		$author = preg_replace('/ \(.+?\)/siU','',strip_tags(SelectNode($InfoBlock, "span", "class=\"user\"")));
		$date = strip_tags(SelectNode($InfoBlock, "span", "class=\"date\""));
//
		@$rtext = ReplaceSpace($rtext);
		@$rtext = ReplaceRN($rtext);
		@$summary = ReplaceSpace($summary);
		@$summary = ReplaceRN($summary);
//
		$a['publication'] = 'GameTech';
		$a['title'] = @$rtitle;
		$a['original'] = @$original;
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['date'] = @$date;
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== GameWay ====
	if (mb_strpos($url,'http://gameway.com.ua')!==False)
	{
		$text = RemoveSubsAll($text,"<script","</script>");		
		$gameinfo = SelectNode($text, "div", "post-entry");
		$text = RemoveNode($text, "h6");
		$text = RemoveNodes($text, "ul");

// Название
		$original = mb_trim(strip_tags(between($gameinfo,"Полное название:<strong>", "</strong><")));
		if ($original == '') {
			$original = strip_tags(SelectNode($text, "title"));
			$f1 = LastIndexOf($original," - ");
			if ($f1 !== -1) $original = substr($original,0, $f1); }
// Автор, дата и оценка
		$author = strip_tags(SelectNode($text, "a", "rel=\"author\""));
		$postmeta = SelectNode($text, "p", "post-meta");
		$date = betweens($postmeta, array("Опубликовано ", ","));
		$grade = mb_trim(strip_tags(betweens($text, array("<h6>Вердикт GameWay:", "</h6>",'<strong>','</strong>'))));
// Текст
		$PostEntry = SelectNode($text, "div", "post-entry");
		$f1 = strpos($PostEntry, "<hr");
		if ($f1 !== false) $PostEntry = substr($PostEntry, $f1);
		$rtext = strip_tags($PostEntry);
		$f1 = LastIndexOf($rtext, "Оценка игры");
		if ($f1 !== -1) $rtext = substr($rtext,0,$f1);
		//$f1 = LastIndexOf($rtext, "Все об игре");
		//if ($f1 !== -1) $rtext = substr($rtext,0,$f1);
		$rtext = ReplaceSpace($rtext);
		$rtext = ReplaceRN($rtext);
//
		$a['publication'] = 'GameWay';
		$a['original'] = @$original;
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['grade'] = @$grade;
		$a['date'] = @$date;
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== ИГРОМАНИЯ ====
	if (mb_strpos($url,'http://www.igromania.ru')!==False)
	{
		$text = mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$original = strip_tags(SelectNode($text, "a", "\"artsectname\""));
// Автор, дата
		$meta = SelectNode($text, "div", "\"gray_line_block\"");
		$date = strip_tags(SelectNode($meta, "span"));
		$author = strip_tags(SelectNode($meta, "span", "\"author\""));
// Вердикт и оцека
		$verict_block = SelectNode($text, "div", "\"verdict_block\"");
		$artstat = SelectNode($verict_block, "div", "\"artstat_verdict\"");
		$verict_block = RemoveNode($verict_block, "div", "\"artstat_verdict\"");
		$grade = strip_tags(SelectNode($artstat, "div", "\"artstat_dig\""));
		$grade = ReplaceSpace($grade);
		$grade = ReplaceRN($grade);
		$summary = ReplaceRN(ReplaceSpace(strip_tags($verict_block)));
// Текст
		$text = RemoveNodes(RemoveNodes($text, "div", "verdict_block"),'table','class="mit_table"');
		$mainBlock = SelectNode($text, "div", "awim_container");
		$f1 = strpos($mainBlock, "<div class=\"artstat");
		if($f1 !== false) $mainBlock = substr($mainBlock, 0, $f1);
		$mainBlock = RemoveNodes($mainBlock, "div", "awim_headimg");
		$mainBlock = RemoveNodes($mainBlock, "div", "formula_full");
		$mainBlock = RemoveNodes($mainBlock, "div", "awim_wideimg");
		$mainBlock = RemoveNodes($mainBlock, "div", "awim_video_simple");
		$mainBlock = RemoveNodes($mainBlock, "div", "awim_textimg_right");
		$mainBlock = RemoveNodes($mainBlock, "div", "awim_textimg_left");
		$mainBlock = RemoveNodes($mainBlock, "div", "guide_vr_mini");
		$mainBlock = RemoveNodes($mainBlock, "div", 'culture_context');		
		$rtext = strip_tags($mainBlock);
		$rtext = ReplaceSpace($rtext);
		$rtext = ReplaceRN($rtext);
		
//
		$a['publication'] = 'Игромания.ру';
		$a['publication_id'] = 46;
		$a['original'] = @$original;
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['grade'] = @$grade;
		$a['date'] = @$date;
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== Виртуальные Радости ====
	if (mb_strpos($url,'http://vrgames.by')!==False)
	{
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$original = strip_tags(SelectNode($text, "h1", "class=\"title\""));
		$original = trim(str_replace("Обзор","",$original));
// Автор, дата
		$meta = SelectNode($text, "div", "\"meta\"");
		$meta = SelectNode($meta, "span", "\"submitted\"");
		$author = strip_tags(SelectNode($meta, "a"));
		$date = trim(str_replace("Автор:", "", str_replace("Дата:", "", strip_tags(RemoveNode($meta, "a")))));
// Текст
		$HtmlText = SelectNode($text, "div", "node-inner clear-block");
		$HtmlText = SelectNode($HtmlText, "div", "class=\"content\"");
		$HtmlText = RemoveNode($HtmlText, "div", "class=\"field field-type-filefield field-field-image\"");
		$HtmlText = RemoveNode($HtmlText, "div", "class=\"field field-type-text field-field-head\"");
		$HtmlText = RemoveNode($HtmlText, "div", "class=\"service-links\"");
		$HtmlText = RemoveNode($HtmlText, "div", "class=\"rate-widget");
		$rtext = Strip_tags($HtmlText);
		$grade = "";
		$ptrn = "/(Оценка:)+([ ]+|)+([0-9.]+)/";
		preg_match($ptrn, @$rtext, $grade);
		@$rtext = str_replace($grade[0], "", $rtext);
		$ptrn = "/([0-9.]+)/";
		preg_match($ptrn, @$grade[0], $grade);
		if (@$grade[0] == '') {
			$ptrn = "/(Оценка)+([ ]+|)+([0-9.]+)/";
			preg_match(@$ptrn, @$rtext, $grade);
			@$rtext = str_replace($grade[0], "", $rtext);
			$ptrn = "/([0-9.]+)/";
			preg_match($ptrn, @$grade[0], $grade);
			@$grade[0] = trim($grade[0], '.');
		}
		$summary = "";
		$f1 = LastIndexOf($rtext, "Вывод:");
		if($f1 !== -1) { $summary  = substr($rtext, $f1 + 12); $rtext  = substr($rtext, 0, $f1); }
		$rtext = ReplaceSpace($rtext);
		$rtext = ReplaceRN($rtext);
		$summary = ReplaceSpace($summary);
		$summary = ReplaceRN($summary);
//
		$a['publication'] = 'Виртуальные радости';
		$a['original'] = @$original;
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = @$author;
		$a['grade'] = @$grade[0];
		$a['date'] = @$date;
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== Игры@mail.ru ====
	if (mb_strpos($url,'http://games.mail.ru')!==False)
	{
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$meta = SelectNode($text, "div", "\"page-hdr post-review-01\"");
		$date = trim(before_first_str(strip_tags(SelectNode($meta, "span", "hdr-date")),', '));
		$meta = RemoveNode($meta, "span", "\"nowrap hdr-date\"");
		$original = strip_tags(str_replace("Рецензия", "", $meta));
		$grade = strip_tags(SelectNode($text, "div", "\"score\"")).strip_tags(SelectNode($text, "div", "\"score-max\""));
// Текст
		$HtmlText = SelectNode($text, "div", "class=\"full-01 js_quote_mouseup\"");
		$author = SelectNode($text, "div", "class=\"author-left\"");
		$author = SelectNode($author, "span");
		$author = strip_tags(str_replace("Автор:", "", $author));
    if($author == null || $author == "")
    {
		  $author = strip_tags(SelectNode($text, "div", "class=\"author-right\""));
		  $author = str_replace("Автор:", "", $author);
    }
    
		$HTMLtext= str_replace("</p>", "</p>\r\n", $HTMLtext);

		$f1 = LastIndexOf($HtmlText, "<strong>Купить");
		if($f1 !== -1) { $HtmlText = substr($HtmlText, 0, $f1); }
		$f1 = LastIndexOf($HtmlText, "Автор:");
		if($f1 !== -1) { $HtmlText = substr($HtmlText, 0, $f1); }

		$rtext = Strip_tags($HtmlText);
		
		$rtext = ReplaceSpace($rtext);
		$rtext = ReplaceRN($rtext);
		
		$rtext=str_replace ('Автор в G+','',$rtext);
		
		@$summary = ReplaceSpace($summary);
		@$summary = ReplaceRN($summary);
//
		$a['publication'] = 'Игры@mail.ru';
		$a['original'] = trim(@$original);
    $a['russian'] = $a['original'];
		$a['text'] = trim(@$rtext);
		$a['author'] = trim(@$author);
		$a['grade'] = str_replace('/10','',@$grade);
		$a['date'] = $date;
		$a['summary'] = trim(SumOfText(@$rtext));
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== PS3 Noizelss ;) ====
	if (mb_strpos($url,'http://ps3.noizless.ru')!==False)
	{
		$text = RemoveSubsAll($text,"<script","</script>");
// Название, автор, дата
		$title = SelectNode($text, "div", "\"entry\"");
		$title = strip_tags(SelectNode($title, "h1", "\"entrytitle\""));
		$meta = SelectNode($text, "div", "\"entrymeta\"");
		$text = RemoveNode($text, "div", "\"entrymeta\"");
		$original = strip_tags(SelectNode($meta, "a", "\"tag\""));
		$author = strip_tags(SelectNode($meta, "strong"));
		$date = strip_tags($meta);
		$f1 = strpos($date, "Опубликовано");
		if ($f1 !== false) $date = substr($date, 0, $f1);
// Текст, Оценка
		$htmltext = SelectNode($text, "div", "\"entrybody\"");
		$htmltext = RemoveSubs($htmltext, "Автор", "$author</a>");
		$grade = betweens($htmltext, array("Общий балл –","<"));
		$htmltext = str_replace("Общий балл – $grade", "", $htmltext);
		if ($grade == "") { $grade = betweens($htmltext, array("Итого –","<")); $htmltext = str_replace("Итого – $grade", "", $htmltext); }
		if ($grade == "") { $grade = betweens($htmltext, array("Общий балл &#8212;","<")); $htmltext = str_replace("Итого – $grade", "", $htmltext); }
		if ($grade == "") { $grade = betweens($htmltext, array("Итого &#8212;","<")); $htmltext = str_replace("Итого &#8212; $grade", "", $htmltext); }
		if ($grade == "") { $grade = betweens($htmltext, array("Итоговая оценка","<")); $htmltext = str_replace("Итоговая оценка $grade", "", $htmltext); }
		$rtext = strip_tags($htmltext);
		@$rtext = ReplaceSpace($rtext);
		@$rtext = ReplaceRN($rtext);
//
		$a['publication'] = 'PS3 Noizelss ;)';
		$a['original'] = trim(@$original);
    $a['russian'] = $a['original'];
		$a['title'] = trim(@$title);
		$a['text'] = trim(@$rtext);
		$a['author'] = trim(@$author);
		$a['grade'] = trim(@$grade);
		$a['date'] = trim(@$date);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== StopGame ====
	if (mb_strpos($url,'http://stopgame.ru')!==False)
	{
		//$text = mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$content_head = SelectNodes($text, "div", "\"content_head\"");
		@$original = SelectNode($content_head[0], "h1");
		@$original = strip_tags(SelectNode($original, "a"));
		if ($original=='')
			$original=between($text,'<span class="par">Игра:</span>','</div>');
		$title = strip_tags(SelectNode($content_head[1], "h2"));
// Дата, Автор
		$date = strip_tags(SelectNode($text, "div", 'class="response-date'));
		$date = str_replace("г.", "", $date);
		$author = strip_tags(SelectNode($text, "div", 'class="pubinfo-name'));
// Оценка
		$rate = SelectNode($text, "div", "\"stop_rate\"");
		$grade = "";
		if (strpos($rate, "/izumitelno_cl")) $grade = "Изумительно";
		else if (strpos($rate, "/pohvalno_cl")) $grade = "Похвально";
		else if (strpos($rate, "/prohodnjak_cl")) $grade = "Проходняк";
		else if (strpos($rate, "/musor_cl")) $grade = "Мусор";
// Текст
		$htmltext = SelectNode($text, "div", "\"main_text\"");
		$htmltext = str_replace("<br />", "<br />\n", $htmltext);
		$rtext = strip_tags($htmltext);
		$rtext = str_replace("[о системе оценок игр]", "", $rtext);
		$summary = "";
		$f1 = LastIndexOf($rtext, "***");
		if ($f1 !== -1) { $summary = substr($rtext, $f1+3); $rtext = substr($rtext, 0, $f1); }
		else {	$f1 = LastIndexOf($rtext, "Плюсы:");
			if ($f1 !== -1) { $summary = substr($rtext, $f1); $rtext = substr($rtext, 0, $f1); }
		}
		@$rtext = ReplaceSpace($rtext);
		@$rtext = ReplaceRN($rtext);
		@$summary = ReplaceSpace($summary);
		@$summary= ReplaceRN($summary);
//
		$a['publication'] = 'StopGame';
		$a['original'] = trim(@$original);
    $a['russian'] = $a['original'];
		$a['title'] = trim(@$title);
		$a['text'] = trim(@$rtext);
		$a['author'] = trim(@$author);
		$a['grade'] = trim(@$grade);
		$a['date'] = trim(@$date);
		$a['summary'] = trim(@$summary);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== Great Gamer ====
	if (mb_strpos($url,'http://greatgamer.ru')!==False)
	{
		$text = mb_convert_encoding ($text, "UTF-8", "Windows-1251");
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$original = SelectNode($text, "div", "\"content_game_name_block_body\"");
		$original = SelectNode($original, "div", "\"text_game_name_block\"");
		$original = strip_tags(SelectNode($original, "h1"));
		$game_profile = SelectNode($text, "div", "\"game_profile\"");
		$russian = "";
		$Rus = SelectNodes($game_profile, "p");
		foreach($Rus as $p) {
			$f1 = strpos($p, "Название игры в России:");
			if ($f1 !== false) $russian = str_replace("Название игры в России:", "", strip_tags($p));
		}
// Оценка
		//$rating_block = SelectNode($text, "div", "\"text_content_rating_block_left\"");
		$grade = mb_trim(strip_tags(between($text, 'Оценка игры:','</h2>')));
		$game_articles = SelectNode($text, "div", "\"game_articles\"");
		$game_articles = SelectNode($game_articles, "table");
		$tr = SelectNodes($game_articles, "tr");
		$td = SelectNodes(@$tr[1], "td");
		$date = strip_tags(@$td[1]);
// Текст
		$HtmlText = SelectNode($text, "div", "\"text_game_name_block_3\"");
		$author = strip_tags(preg_replace('/ ["«]{1}.+?["»]{1}/siU','', betweens($HtmlText, array("Автор статьи:","</p>"))));
		$HtmlText = RemoveSubs($HtmlText,"Автор статьи:","</p>");
		$HtmlText = RemoveSubs($HtmlText,"<em>Просмотров:","</em>");
		$rtext = strip_tags($HtmlText);
		$rtext = str_replace("Рецензия на игру:", "", $rtext);
		$f1 = LastIndexOf($rtext, "***");
		if ($f1 !== -1) {
			$summary = substr($rtext, $f1 + 3); $rtext = substr($rtext, 0, $f1);
			$summary=mb_trim(strip_tags(before_first_str($summary,'Оценка игры:')));
		}
		@$rtext = ReplaceSpace($rtext);
		@$rtext = ReplaceRN($rtext);
//
		$a['publication'] = 'Great Gamer';
		$a['type']=1;
		$a['original'] = trim(@$original);
		$a['russian'] = trim(@$russian);
		$a['text'] = trim(@$rtext);
		$a['author'] = trim(@$author);
		$a['grade'] = trim(@$grade);
		$a['date'] = trim(@$date);
		$a['summary'] = trim(@$summary);
		$a['issue']=$url;
		
	
		$a['original']=mb_trim(str_replace('Рецензия на игру ','',strip_tags(SelectNode($text,'h1'))));
		if (mb_substr($a['original'],-5)==', The')
			$a['original']='The '.mb_substr($a['original'],0,-5);
		if (mb_substr($a['original'],-3)==', A')
			$a['original']='A '.mb_substr($a['original'],0,-3);
		if (mb_substr($a['original'],-4)==', An')
			$a['original']='An '.mb_substr($a['original'],0,-4);
		$a['russian']=$a['original'];
		
	} else
// ==== Game Bomb ====
	if (mb_strpos($url,'http://gamebomb.ru')!==False)
	{
		$text = RemoveSubsAll($text,"<script","</script>");
// Название
		$original = strip_tags(SelectSubs($text, "<h1><a href", "</h1>"));
		$original = str_replace("Игра", "", $original);
		$container = SelectNode($text, "div", "\"container-medium container-content games-shortUserReview\"");
		$style = SelectNode($container, "div", "\"margin-left: 115px\"");
		$title = strip_tags(SelectNode($style, "div", "\"title\""));
// Дата, Автор
		$author = strip_tags(SelectNode(SelectNode($style, "small"), "a"));
		$date = SelectNode($style, "small");
		$date = strip_tags(betweens($date, array("от", "</small>")));
// Оценка
		$grade = SelectNode($container, "div", "\"stars-big\"");
		$grade = betweens($grade, array("Оценка игре:", "\">"));
// Текст и Вывод
		$htmltext = SelectNode($text, "div", "\"review_content\"");
		$rtext = strip_tags($htmltext);
		@$rtext = ReplaceSpace($rtext);
		@$rtext = ReplaceRN($rtext);
//
		$a['publication'] = 'Game Bomb';
		$a['original'] = trim(@$original);
    $a['russian'] = $a['original'];
		$a['title'] = trim(@$title);
		$a['text'] = trim(@$rtext);
		$a['author'] = trim(@$author);
		$a['grade'] = trim(@$grade);
		$a['date'] = trim(@$date);
		$a['type']=1;
		$a['issue']=$url;
	} else
// ==== Канобу ====
	if (mb_strpos($url,'kanobu.ru')!==False)
	{
		$text = RemoveSubsAll($text,"<script","</script>");
		
		// $htmltext = RemoveNodes(RemoveNodes(SelectNode($text,"p","class=\"introText\"").SelectNode(SelectNode(SelectNode($text,"div","class=\"wrapContent\""),'div','class="mainColumn"'),'div','id="article"'),'div','class=""'),'figure');
		
		$htmltext=RemoveNodes(SelectNode($text,'div','itemprop="reviewBody"'),'div','class="content-image');
		// $htmltext = str_replace("</p>", "</p>\r\n", $htmltext);
// Название
		$original = strip_tags(SelectNode($text, "a", 'itemprop="itemReviewed"'));
// Оценка
		//$grade = SelectNode($text, "meta", "itemprop=\"ratingValue\"");
		$grade=between($text,'<meta itemprop="ratingValue" content="','">');
		
// Дата, Автор
	    //$date = before_first_str(strip_tags(SelectNode(SelectNode($text,'h4','class="typeMat"'), "span")),', ');
	    //$date = DateConverter($date);
	    $date=str_replace('T',' ',between($text,'<meta itemprop="datePublished" content="','">'));
	    //2014-11-07T14:33:07">
	    $author = strip_tags(SelectNode($text, "span", "itemprop=\"author\""));
	    
	    $title=strip_tags(SelectNode($text,'p','itemprop="alternativeHeadline"'));
  
    /*
		$ptags = SelectNodes($htmltext,"p");
    foreach($ptags as $p)
    {
      if(mb_strpos($p, 'Автор текста'))
      {
        $htmltext = str_replace("$p", "", $htmltext);
        $author = strip_tags($p);
        $author = str_replace("Автор текста:", "", $author);
        $author = str_replace("Автор текста", "", $author);
      } else
      if(mb_strpos($p, 'Текст:'))
      {
        $htmltext = str_replace("$p", "", $htmltext);
        $author = strip_tags($p);
        $author = str_replace("Текст:", "", $author);
      }
    }
    */
/*
		$rtext = strip_tags($htmltext);
		@$rtext = ReplaceSpace($rtext);
		@$rtext = ReplaceRN($rtext);
*/
		$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$htmltext)))));

		$a['publication'] = 'Канобу';
		$a['original'] = trim(@$original);
		$a['russian'] = $a['original'];
		$a['author'] = trim(@$author);
		$a['grade'] = trim(strip_tags(@$grade));
		$a['date'] = $date;
		$a['summary'] = SumOfText(@$rtext);
		$a['type'] = 1;
		$a['issue']=$url;
		$a['title']=$title;
	} else
		$noparser=TRUE;
/*
		$a['publication']='';
		$a['publication_id']=0;
		$a['author']='';
		$a['date']='';
		$a['russian']='';
		$a['original']='';	
		$a['grade']='';
		$a['text']='';
		$a['summary']='';
		$a['type']='';			
		$a['issue']='';
	*/

/*
	if (mb_strpos($url,'rollingstone.ru')!==FALSE) 
	{
			$a['publication']='Rolling Stone';
			$a['publication_id']=104;
			
			//$text=mb_convert_encoding ($text, "UTF-8", "Windows-1251");
			
			$text=str_replace (
			array('&nbsp;','&mdash;','&ndash;','&laquo;','&raquo;','&#8211;','&quot;','--','&#171;','&#187;','&#173;'),
			array(' ','—','—','«','»','-','"','-','«','»',''),
			$text);
			echo $text;
			
			$text=SelectNode(SelectNode($text,'div','class="block-center"'),'div','class="block-left"');
			$dateauthor=SelectNode($text,'div','class="block-data-author"');
			
			$a['date']=mb_trim(strip_tags(SelectNode($dateauthor,'span','class="red-bold"')));
			$a['author']=mb_trim(strip_tags(SelectNode($dateauthor,'span','class="black-bold"')));
			
			$a['russian']=mb_trim(str_replace(array('«','»'),'',strip_tags(SelectNode($text,'h1'))));

			$gr=preg_match_all('/"\/media\/sdes\/star\-red\.gif"/si',SelectNode($text,'ul','class="block-review-inner"'),$grm);
			$a['grade']=sizeof($grm[0]);
			
			// $a['grade']=mb_trim(preg_replace('/[^0-9\,\.]+/si','',strip_tags(between($desc,'Оценка: ',' из 10'))));
		$a['text']=RemoveNode(SelectNode($text,'div','id="divLetterBranding"'),'div','class="document_inner_title"');

			$a['type']=0;
			
			$a['text']=trim(preg_replace("/[\n\r]{2,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
			
			$a['issue']=$url;			
			
	}
	
	else
*/


	
	if (sizeof($a)==0 && $table=='wisdom')
	{
		$a=fq("SELECT * FROM reviews WHERE issue='".mysql_real_escape_string($url)."'");
	}
	
	} else
	{
		$a=fq("SELECT * FROM reviews WHERE id=".intval($review_id));
	}
	
	if (sizeof($a)>0)
	{
		if ($a['date']!='') {
			$a['review_timestamp']=date_to_timestamp($a['date']);
			$a['nicedate']=date('d.m.Y',$a['review_timestamp']);
		} else 
		{
			$a['review_timestamp']=0;
			$a['nicedate']='';
		}
		
		$a['critic_id']=get_critic_id($a['author']);
		
		if ($a['type']==0)
			$a['codename']=faq("SELECT codename FROM movies WHERE russian='".mysql_real_escape_string(trim($a['russian']))."' ORDER BY id DESC","codename");
		else
		if ($a['type']==1)
			$a['codename']=faq("SELECT codename FROM gamesofgeneration WHERE original='".mysql_real_escape_string(trim($a['original']))."' ORDER BY id DESC","codename");
		
		if ($a['codename']==null)
			$a['codename']='';
		
		/* Убить если чо */
		//$a['text']=preg_replace ("/[\r\n]{1}/si","\n",$a['text']);

		$a['text']=preg_replace("/\s*\n+\s*/si","\n",str_replace("\r","\n",$a['text']));
		
		$a['text']=trim(preg_replace("/[\n\r]{1,}/si","\n\n",trim(strip_tags (str_replace(array('<br>','<br />','<BR>','</p>','</P>'),array("\n","\n","\n","\n\n","\n\n"),$a['text'])))));
	
	
		if ($a['summary']=='')
		{
			$sum=preg_split("/\n\n/si",$a['text']);
			foreach ($sum as $su)
			{
				if (mb_strlen($su)>100 && mb_substr($su,0,7)!='Тривия:' && mb_substr($su,0,8)!='Награды:')
					$a['summary']=trim($su);
			}
			if (mb_strlen($a['summary'])>450)
			{
				$a['summary']=trim(rss_short($a['summary'],450));
			}
		}
		
		
		$a['date']=preg_replace('/,* [0-9]{2}\:[0-9]{2}$/siU','',$a['date']);

		/*
		if (!preg_match('/[0-9]{4}/si',$a['date']))
			$a['date'].=' '.date('Y');
		*/
		if (preg_match('/сегодня/siU',$a['date']) || preg_match('/вчера/siU',$a['date']) || preg_match('/позавчера/siU',$a['date']) || preg_match('/Сегодня/siU',$a['date']) || preg_match('/Вчера/siU',$a['date']) || preg_match('/Позавчера/siU',$a['date']))
		{	
			$a['date']=mb_trim(preg_replace(array('/сегодня/siU','/вчера/siU','/позавчера/siU','/Сегодня/siU','/Вчера/siU','/Позавчера/siU'),array(date('d.m.Y',strtotime('today')),date('d.m.Y',strtotime('yesterday')),date('d.m.Y',strtotime('yesterday -1day')),date('d.m.Y',strtotime('today')),date('d.m.Y',strtotime('yesterday')),date('d.m.Y',strtotime('yesterday -1day'))),preg_replace('/[0-9]{4}/siU','',$a['date'])));
		}
	
		//
		/* тут убивать */
	
		if($justshow!='true')
		{
				if (!isset($a['grade']))
					$a['grade']='';
				$js=json_encode($a);
				echo $js;
		} else
		{
			echo 'publication: '.$a['publication']."<br/>";
			echo 'issue: '.$a['issue']."<br/>";
			echo 'russian: '.$a['russian']."<br/>";
			echo 'original: '.$a['original']."<br/>";
			echo 'title: '.$a['title']."<br/>";
			echo 'text: '.nl2br($a['text'])."<br/>";
			echo 'author: '.$a['author']."<br/>";
			echo 'grade: '.$a['grade']."<br/>";
			// echo 'rating: '.get_rating_new($a['grade'],$a['publication_id'])."<br/>";
			echo 'date: '.$a['date']." (оригинал)<br/>";
			echo 'date: '.date('d.m.Y',date_to_timestamp($a['date']))." (после преобразования)<br/>";
			echo 'type: '.$a['type']."<br/>";
			echo 'summary: '.$a['summary']."<br/>";
		}
	
	}
	else
	{
		if ($noparser)
			echo json_encode(array('noparser'=>'true'));
		else
			echo json_encode(array('nodata'=>'true'));
	}



?>