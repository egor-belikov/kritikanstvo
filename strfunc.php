<?php
// === Функция ищет последнее вхождение подстроки в строке
function lastIndexOf($string,$item){
	$index=strpos(strrev($string),strrev($item));
	if ($index !== false){
		$index=strlen($string)-strlen($item)-$index;
		return $index;
	} else return -1; }
// === Функция выбирает первую подстроку, которая начинается со $StartSubstring и заканчиваются на $EndSubstring
function SelectSubs($string,$StartSubstring,$EndSubstring){
	if ($StartSubstring){
		$f1=strpos($string,$StartSubstring);
		if ($f1 !== false){
			$f2=strpos($string,$EndSubstring,$f1);
			if ($f2 !== false){
				$string = substr($string,$f1,$f2-$f1+strlen($EndSubstring));	
			}
			else return null;
		}
		else return null;
	}
	return $string;
}
// === Функция выбирает первую подстроку, которая между $StartSubstring и $EndSubstring
function SelectiSubs($string,$StartSubstring,$EndSubstring)
{
	$f1 = strpos($string,$StartSubstring);
	if ($f1 !== false)
	{
		$f2=strpos($string,$EndSubstring,$f1+strlen($StartSubstring));
		if ($f2 !== false)
		{
			$string = substr($string,$f1+strlen($StartSubstring),$f2-$f1-strlen($StartSubstring));	
		} else return null;
	} else return null;
	return $string;
}
// === Функция выбирает все подстроки, которые начинаются со $StartSubstring и заканчиваются на $EndSubstring
function SelectSubsAll($string,$StartSubstring,$EndSubstring,$f2=0){
	$str = null;
	$n = 0;
	while(true)
	{
		$f1=strpos($string,$StartSubstring,$f2);
		if ($f1 !== false){
			$f2=strpos($string,$EndSubstring,$f1);
			if ($f2 !== false){
				$str[$n] = substr($string,$f1,$f2-$f1+strlen($EndSubstring));	
			}
			else return $str;
		}
		else return $str;
		$n++;
	}
}
// === Функция удаляет все подстроки, которые начинаются со $StartSubstring и заканчиваются на $EndSubstring
// == Пример удаляет скрипты: $text = RemoveSubsAll($text,"<script","</script>");
function RemoveSubsAll($string,$StartSubstring,$EndSubstring){
	$p=true;
	while($p){
		if ($StartSubstring){
			$f1=strpos($string,$StartSubstring);
			if ($f1 !== false){
				if ($EndSubstring){
					$f2=strpos($string,$EndSubstring,$f1);
					if ($f2 !== false){
						$string = substr_replace($string,"",$f1,$f2-$f1+strlen($EndSubstring));	
					} else $p = false;
				}
			} else $p = false;
		} else $p = false;
	}
	return $string;
}
// === Функция удаляет первую подстроку, которая начинается со $StartSubstring и заканчиваются на $EndSubstring
function RemoveSubs($string,$StartSubstring,$EndSubstring){
	if ($StartSubstring){
		$f1=strpos($string,$StartSubstring);
		if ($f1 !== false){
			if ($EndSubstring){
				$f2=strpos($string,$EndSubstring,$f1);
				if ($f2 !== false){
					$string = substr_replace($string,"",$f1,$f2-$f1+strlen($EndSubstring));	
				}
			}
		}
	}
	return $string;
}
// === Функция удаляет первую подстроку, которая находится МЕЖДУ $StartSubstring и $EndSubstring
function RemoveiSubs($string,$StartSubstring,$EndSubstring){
	if ($StartSubstring){
		$f1=strpos($string,$StartSubstring);
		if ($f1 !== false){
			if ($EndSubstring){
				$f2=strpos($string,$EndSubstring,$f1);
				if ($f2 !== false){
					$string = substr_replace($string,"",$f1+strlen($StartSubstring),$f2-$f1-strlen($StartSubstring));	
				}
			}
		}
	}
	return $string;
}
//=== Функция заменяет подряд идущие пробелы на один пробел
function ReplaceSpace($text){
	$text = str_replace("\t", " ", $text);
	$p=true;
	while($p){
		if (strpos($text,"  ") === false) $p = false;
		else $text = str_replace("  ", " ", $text);
	}
	$text = trim($text);
	return $text;
}
//=== Функция заменяет подряд идущие "перевод строки" на один "перевод строки"
function ReplaceRN($text){
	$text = str_replace("\r", "", $text);
	$text = str_replace("\n ", "\n", $text);	
	$p=true;
	while($p){
		if (strpos($text,"\n\n") === false) $p = false;
		else $text = str_replace("\n\n", "\n", $text);
	}
	$text = trim($text);
	return $text;
}
//=== Функция ищет позицию HTML-тега с заданным атрибутом начиная с позиции $pos
//== Эта функция нужна для работы функции SelectNode() и RemoveNode()
function NodePos($HTMLtext, $tag, $attribute, $pos){
	$HTMLtext = strtolower($HTMLtext);
	$tag = strtolower($tag);
	if ($attribute !== null) $attribute = strtolower($attribute);
	$p=true;
	$s = $pos;
	while($p){
		$f1 = strpos($HTMLtext,"<$tag", $s);
		if ($f1 !== false){
			if ($attribute!==null){
				$f2  = strpos($HTMLtext,">", $f1);
				if ($f2 !== false) {
					if (strpos(substr($HTMLtext, $f1, $f2-$f1), $attribute) !== false) RETURN $f1; }
			} else RETURN $f1;
		} else $p = false;
		$s = $f1 + 1;
	} RETURN null;
}
//=== Функция делает выборку узла(одного node) с заданным атрибутом из HTML
//== Эта функция нужна для работы функции SelectNodes()
//= пример: $rtext = SelectNode($text, "div", "class=\"text\"");
function SelectNode($HTMLtext, $tag, $attribute=null, $pos=0){
	$LHTMLtext = strtolower($HTMLtext);
	$Ltag = strtolower($tag);
	if ($attribute !== null) $Lattribute = strtolower($attribute);
	$fs = NodePos($HTMLtext, $tag, $attribute, $pos);
	if ($fs!==null){
		$fe = strlen($HTMLtext) - $fs;
		$sp = $fs + 1;
		$p=true;
		$s = 1;
		$e = 0;
		while ($p){
			$f1 = strpos($LHTMLtext,"<$Ltag", $sp);
			$f2 = strpos($LHTMLtext,"</$Ltag", $sp);
			if ($f2 !== false){
				if ($f1 !== false){
					if ($f2<$f1) { $e++; $sp = $f2 + 1; }
					else { $s++; $sp = $f1 + 1; }
				} else { $e++; $sp = $f2 + 1; }
				if ($s === $e) { $fe = $sp + strlen("</$tag>") - 1 - $fs; $p = false; }
			} else $p = false;
		}
		RETURN substr($HTMLtext, $fs, $fe);
	} else RETURN null;
}
//=== Функция делает выборку узлов(массив node) с заданным атрибутом из HTML
function SelectNodes($HTMLtext, $tag, $attribute=null, $pos=0){
	$p=true;
	$nodes = null;
	$n=0;
	while ($p){
		$fs = NodePos($HTMLtext, $tag, $attribute, $pos);
		if ($fs!==null) { $nodes[$n] = SelectNode($HTMLtext, $tag, $attribute, $pos); $n++; $pos = $fs+1; }
		else $p = false;
	}
	RETURN $nodes;
}
//=== Функция делает УДАЛЕНИЕ узла(одного node) с заданным атрибутом из HTML
function RemoveNode($HTMLtext, $tag, $attribute=null, $pos=0){
	$LHTMLtext = strtolower($HTMLtext);
	$Ltag = strtolower($tag);
	if ($attribute !== null) $Lattribute = strtolower($attribute);
	$fs = NodePos($HTMLtext, $tag, $attribute, $pos);
	if ($fs!==null){		
		$fe = strlen($HTMLtext) - $fs;
		$sp = $fs + 1;
		$p=true;
		$s = 1;
		$e = 0;
		while ($p){
			$f1 = strpos($LHTMLtext,"<$Ltag", $sp);
			$f2 = strpos($LHTMLtext,"</$Ltag", $sp);
			if ($f2 !== false){
				if ($f1 !== false){
					if ($f2<$f1) { $e++; $sp = $f2 + 1; }
					else { $s++; $sp = $f1 + 1; }
				} else { $e++; $sp = $f2 + 1; }
				if ($s === $e) { $fe = $sp + strlen("</$tag>") - 1 - $fs; $p = false; }
			} else $p = false;
		}
		RETURN substr_replace($HTMLtext, "", $fs, $fe);
	} else RETURN $HTMLtext;
}
//=== Функция делает УДАЛЕНИЕ ВСЕХ узлов(nodes) с заданным атрибутом из HTML
function RemoveNodes($HTMLtext, $tag, $attribute=null, $pos=0){
	do {
		$HTMLtext = RemoveNode($HTMLtext, $tag, $attribute, $pos);
		$fs = NodePos($HTMLtext, $tag, $attribute, $pos);
	} while ($fs!==null);
	RETURN $HTMLtext;
}

//
function SumOfText($text)
{
  $text = trim($text);
  $slngth = mb_strlen($text, 'UTF-8');
  $subText = $text;
  while(true)
  {
    $f1 = lastIndexOf($subText, "\n");
    if($f1 != -1)
    {
      $summery = trim(substr($text, $f1));
      if(mb_strlen($summery, 'UTF-8') >= 90)
      {
				$subsumm = substr($summery, 0, 15);
				if(is_numeric(stripos($subsumm,"достоинств")) | is_numeric(stripos($subsumm,"плюсы")) | is_numeric(stripos($subsumm,"PS ")) | is_numeric(stripos($subsumm,"PS\r")) | is_numeric(stripos($subsumm,"PS\n")) | is_numeric(stripos($subsumm,"P.S.")) | is_numeric(stripos($subsumm,"PPS")) | is_numeric(stripos($subsumm,"P.P.S.")) | is_numeric(stripos($subsumm,"ПС ")) | is_numeric(stripos($subsumm,"ПС\r")) | is_numeric(stripos($subsumm,"ПС\n")) | is_numeric(stripos($subsumm,"П.С.")) | is_numeric(stripos($subsumm,"ППС")) | is_numeric(stripos($subsumm,"П.П.С.")) | is_numeric(stripos($subsumm,"ЗЫ ")) | is_numeric(stripos($subsumm,"ЗЫ\r")) | is_numeric(stripos($subsumm,"ЗЫ\n")) | is_numeric(stripos($subsumm,"З.Ы.")))
				{
					$text = trim(substr($text, 0, $f1));
          $slngth = mb_strlen($text, 'UTF-8');
          $subText = $text;
				} else
        {
          return $summery;
        }
      } else
      {
        $subText = trim(substr($subText, 0, $f1));
      }
    } else
    {
      return $text;
    }
  }
}

function RusMonthToINT($Month=null) {
  $Month = mb_strtolower($Month,'UTF-8');
	if($Month !== null)
	{
		if($Month == 'января') RETURN "01";
		else if($Month == 'февраля') RETURN "02";
		else if($Month == 'марта') RETURN "03";
		else if($Month == 'Апреля') RETURN "04";
		else if($Month == 'мая') RETURN "05";
		else if($Month == 'июня') RETURN "06";
		else if($Month == 'июля') RETURN "07";
		else if($Month == 'августа') RETURN "08";
		else if($Month == 'сентября') RETURN "09";
		else if($Month == 'октября') RETURN "10";
		else if($Month == 'ноября') RETURN "11";
		else if($Month == 'декабря') RETURN "12";
	}
	RETURN NULL;
}
function DateConverter($datesource)
{
  $datesplit = split(",", $datesource);
  $datesplit = split(" ", $datesplit[0]);
  return $datesplit;
}


?>