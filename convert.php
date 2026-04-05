   
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>converted</title>
</head>
<body>
<strong>result :</strong>
<br>
<textarea name="textarea" id="textarea" cols="10000" rows="32">
<?php
$gdFile= $_FILES["userfile"]["name"];
$gdFileVal= $_FILES["userfile"]["tmp_name"];
if (is_uploaded_file($gdFileVal)) 
{
	$gtext = file_get_contents($gdFileVal);
} 
else 
{
	die("bad file upload");
}
class Rec{
	var $i;
	var $level;
	var $glevel;
	var $ref;
	var $tag;
	var $val;
	var $text;
	var $boxtext;
	var $boxlength;
	var $childs=array();
	var $sub=array();
	var $up=array();
	var $down=array();
	var $marital=array();
	


}

function GetLevels($i)
{
global $Records;
if (isset($Records[$i]->glevel))
		{

			$arr = $Records[$i]->down;
			$a=0;
			while($a < count($arr))
			{
				$Records[$arr[$a]]->glevel = $Records[$i]->glevel+1;
				GetLevels($arr[$a]);
				$a++;
			}	


			if (count($Records[$i]->marital) > 0 )
			{
				$a=0;
				while($a < count($arr))
				{
					$Records[$arr[$a]]->glevel = $Records[$Records[$i]->marital[0]]->glevel+1;
					GetLevels($arr[$a]);
					$a++;
				}	
			
			}			


			
		}


}





$glines = explode("\n", $gtext); 


$i=0;
$temp=array();
while($i < count($glines))
{
	if ($glines[$i]!="")
		$temp[]=$glines[$i];
	$i++;
}

$glines=$temp;

$i=0;
$Records = array();
while($i < count($glines))
{
	$parts = explode(" ", $glines[$i]);
	
	$cRec = new Rec();

	$cRec->i=$i;
	$cRec->level = (int)$parts[0];
	$test=trim($parts[1]);
	if ($test[0] == '@' && $test[strlen($test)-1]=='@')
	{
		$cRec->ref = trim($parts[1]);
		$cRec->tag = trim($parts[2]);
		array_shift($parts);	
		array_shift($parts);	
		array_shift($parts);	
	
		
	}
	else
	{
		$cRec->tag = trim($parts[1]);
		array_shift($parts);	
		array_shift($parts);	
	}
	$cRec->val = join(" ", $parts);

	array_push($Records,$cRec);

	$i++;



}

$i=0;
$naid=0;
while($i < count($Records))
{
	$j=$i+1;
	$arr = array();
	while($j < count($Records))
	{
		if ($Records[$i]->level >= $Records[$j]->level)
			break;

		if ($Records[$j]->tag == "NAME")
		{
			$Records[$i]->text = str_replace('/', '', $Records[$j]->val);
			
			$s=$Records[$i]->text;
			$naid++;
			$nasid="".$naid;
			$nalen=strlen($nasid);
			$nast='NA'.(substr('000'.$naid,$nalen,3));			
			//$narrow[$naid].="$s";			
			$s=$nast;
			$c = strlen($s);
			if ($c > 20 ) 
			{ 
				$s=substr($s, 0, 20);
			}
			$c = strlen($s);
			if ($c%2 == 0) 
			{ 
				$s=$s." ";
			}
			$s=" ".$s." ";
			$Records[$i]->boxtext=$s;
			$Records[$i]->boxlength=(strlen($s)*2);



		}


		if ($Records[$i]->level+1 == $Records[$j]->level)
			$arr[] = $Records[$j]->i;
		$j++;
	}
	$Records[$i]->childs=$arr;
	$i++;
	
}

$i=0;
while($i < count($Records))
{
	
	if ($Records[$i]->tag == "FAM")
	{
		$arr = $Records[$i]->childs;
		$a=0;
		while($a < count($arr))
		{	
			if ($Records[$arr[$a]]->tag=="WIFE" || $Records[$arr[$a]]->tag=="HUSB")
			{
				$x=0;
				while($x < count($Records))
				{
					if (trim($Records[$arr[$a]]->val) == trim($Records[$x]->ref))
					{

						array_push($Records[$Records[$x]->i]->sub, $i);
						
						$arr2 =$Records[$i]->childs;
						$a2=0;	
						while($a2 < count($arr2))
						{
							$saved = 0;
							if (trim($Records[$arr2[$a2]]->tag)=="HUSB")
							{
								$y=0;
								while($y < count($Records))
								{
									if (trim($Records[$arr2[$a2]]->val) == trim($Records[$y]->ref))
									{
										$saved = $Records[$y]->i;
										break;							
									}
										
									$y++;
								}
							}
							$a2++;
							if ($saved!=0)
								break;
						}
						$a2=0;	
						while($a2 < count($arr2))
						{
							$flag = 0;
		
							if (trim($Records[$arr2[$a2]]->tag)=="WIFE")
							{
								$z=0;
								while($z < count($Records))
								{
									if (trim($Records[$arr2[$a2]]->val) == trim($Records[$z]->ref))
									{
									if (!in_array($saved, $Records[$Records[$z]->i]->marital))
									{
										array_push($Records[$Records[$z]->i]->marital, $saved);
									$flag=1;
									break;
									}
									}								
									$z++;
								}
										
							}

							$a2++;
							if ($flag!=0)
								break;
						}

						
					}
					$x++;
				}
			}	
			if ($Records[$arr[$a]]->tag=="CHIL")
			{
				$x=0;
				while($x < count($Records))
				{
					if (trim($Records[$arr[$a]]->val) == trim($Records[$x]->ref))
					{

						array_push($Records[$Records[$x]->i]->up, $i);
					}
					$x++;
				}
			}	



			$a++;
		}
	
	}
	$i++;
}
$i=0;
while($i < count($Records))
{
	if (trim($Records[$i]->tag)=="INDI")
	{
	$arr = $Records[$i]->sub;
	$a=0;
	while($a < count($arr))
	{
		$arr2 = $Records[$arr[$a]]->childs;
		$a2=0;
		while($a2 < count($arr2))
		{

		if (trim($Records[$arr2[$a2]]->tag)=="CHIL")
		{
				$x=0;
				while($x < count($Records))
				{
					if (trim($Records[$arr2[$a2]]->val) == trim($Records[$x]->ref))
					{

						array_push($Records[$i]->down, $x);
					}
					$x++;
				}

				
		}
		
		$a2++;
		}	
		$a++;
	}
	}
	
	$i++;
	
}

$i=0;
while($i < count($Records))
{
	if (trim($Records[$i]->tag)=="INDI")
	{
	
	if (count($Records[$i]->up) == 0)
		$Records[$i]->glevel=0;


	}

	$i++;
}

$i=0;
while($i < count($Records))
{
	if (trim($Records[$i]->tag)=="INDI")
	{
		GetLevels($i);
	}
	$i++;
}
$i=0;
while($i < count($Records))
{
	if (trim($Records[$i]->tag)=="INDI")
	{
		$j=0;
		while($j < count($Records))
		{
			if ($i!=$j && trim($Records[$j]->tag)=="INDI")
			{
				/*if (count($Records[$i]->sub) > 0 && $Records[$i]->sub == $Records[$j]->sub && $Records[$i]->down == $Records[$j]->down)
				{
					if ($Records[$i]->glevel > $Records[$j]->glevel)
					{
						$Records[$j]->glevel = $Records[$i]->glevel;
						
					}

					
				}*/
				if (count($Records[$i]->sub) > 0 && in_array($Records[$i]->i, $Records[$j]->marital))
				{
					if ($Records[$i]->glevel > $Records[$j]->glevel)
					{
						$Records[$j]->glevel = $Records[$i]->glevel;
						
					}
				}	
			}	
			$j++;
		}
	}
	
	$i++;
}


$Levels = array();
$Members = array();

$i=0;
while($i < count($Records))
{
	if (trim($Records[$i]->tag)=="INDI")
	{
		if (isset($Records[$i]->glevel))
		{

			$Levels[$Records[$i]->glevel][] = $Records[$i]->i;
			$Members[] = $Records[$i]->i;


			
		}
	}
	
	$i++;
}


ksort($Levels);
$i=0;
while($i < count($Levels))
{
	$level = $Levels[$i];
	if (count($level) > 0)
	{
	$arr = $Records[$level[0]]->up;
	$alevel=array();
	$alevel[] = $level[0];
	$j=1;
	while($j < count($level))
	{
		if ($arr == $Records[$level[$j]]->up)
		{
			$alevel[]=$level[$j];	
		}
		else
		{
			$arr = $Records[$level[$j]]->up;
			$alevel[]=$level[$j];
		}
		$j++;
	}
	}
	$Levels[$i] = $alevel;
	$i++;
}








$i=0;
$MaxLevel=0;
while($i < count($Levels))
{
	$level = $Levels[$i];
	$alevel = array();
	if (count($level) > 0)
	{
		$j=0;
		while($j < count($level))
		{
			//if (count($Records[$level[$j]]->marital)>0)
			//{
				//do nothing
			//}
			//else
			//{
				if (count($alevel) > 0 && !in_array($Records[$level[$j]]->i, $alevel))
					$alevel[] = $Records[$level[$j]]->i;
				elseif (!in_array($Records[$level[$j]]->i, $alevel))
					$alevel[]= $Records[$level[$j]]->i;
				
				
				
				
			//}


			$j++;
		}
		$a=0;
		$blevel=array();
		while($a < count($alevel))
		{
			$blevel[] = $alevel[$a];		
			$jj=0;
			while($jj < count($Levels))
			{
			$j=0;
			while($j < count($Levels[$jj]))
			{
				if (count($Records[$Levels[$jj][$j]]->marital)>0 && in_array($Records[$alevel[$a]]->i, $Records[$Levels[$jj][$j]]->marital))
					$blevel[] = $Levels[$jj][$j];
					
					
			
			

				$j++;
			}
			$jj++;
			}
		$a++;
		}	
		$Levels[$i] = $blevel;

	}
	
	if (count($Levels[$i]) > $MaxLevel)
		$MaxLevel = count($Levels[$i]);
	$i++;
}



$i=0;
while($i < count($Levels))
{
	$j=0;
	$newlevel = array();
	while($j < count($Levels[$i]))
	{

		$k=0;
		while($k < count($Records))
		{
	
			if ($Records[$k]->tag == "FAM")
			{
				$arr = $Records[$k]->childs;
				$a=0;
				$lastfamily='';
				while($a < count($arr))
				{

					if (trim($Records[$arr[$a]]->val) == trim($Records[$Levels[$i][$j]]->ref) )
					{
						$newlevel[trim($Records[$k]->ref)][]=$Levels[$i][$j];
						
					}
					$a++;
				}
			}
			$k++;
	
		}
		$j++;
	}
	ksort($newlevel);
	$adjlevel=array();
	

	foreach($newlevel as $kl=>$vl)
		foreach ($vl as $ks=>$vs)
		{
			if (!in_array($vs, $adjlevel))
			{
				//if (count($Records[$vs]->marital)==0)
				{
				$adjlevel[]=$vs;
				foreach($newlevel as $kt=>$vt)
				{
				foreach($vt as $ki=>$vi)
				{

					if (in_array($vs, $Records[$vi]->marital))
					{
						if (!in_array($vi, $adjlevel))
							$adjlevel[]=$vi;
					}
				}
				}	
				}
			}
		}

	$m=0;
	$adjlevel2 = array();
	while($m < count($adjlevel))
	{
		$flag=false;
		if (count($Records[$adjlevel[$m]]->marital) == 0)
		{
		$n=0;
		$adjlevel2[] = $adjlevel[$m];
		while($n < count($adjlevel))
		{
			if ($m==$n)
			{
				$n++;
				continue;
			}
			if (in_array($adjlevel[$m], $Records[$adjlevel[$n]]->marital))
			{
				if (!in_array($adjlevel[$n], $adjlevel2))
					$adjlevel2[]=$adjlevel[$n];	
			}
			$n++;
		}
		}
		else
		{
			$p=0;
			while($p < count($adjlevel))
			{
				if (in_array($adjlevel[$p], $Records[$adjlevel[$m]]->marital))
				{
					$flag=true;
					break;
				}
				$p++;
			}
			if ($flag==false)
				$adjlevel2[]=$adjlevel[$m];
		}	
		$m++;
	}	

	$Levels[$i]=$adjlevel2;	


	$i++;
}


//print_r($Levels);


function PrintLines()
{
	global $Lines;

$j=2;
	$flag=false;
	while($j < count($Lines[0]))
	{
		$i=0;
		while($i < count($Lines))
		{
			if ($Lines[$i][$j] != "| ")
			{

				$flag=true;
				break;
			}
			$i++;
		}
		if ($flag==true)
			break;
		$j++;
	}

	if ($flag==true && $j > 2)
		{
$j=$j-2;

			$i=0;
			while($i < count($Lines))
			{
				$k=2;
				while($k < count($Lines[$i])-1-$j)
				{	
					$Lines[$i][$k] = $Lines[$i][$k+$j];
					$k++;
				}
				while($k < count($Lines[$i])-1)
				{
					$Lines[$i][$k] = "| ";
		
					$k++;
				}

				$i++;
			}
			
			
		}

        $i=0;
$lastindex=0;
        while($i < count($Lines))
	{
		$j=count($Lines[$i])-1;
		while($j >= 0 )
		{
			if (substr($Lines[$i][$j], 1, 2) == 'NA' )
			{
if ($lastindex < $j)
$lastindex=$j;

break;


			}
			$j--;
		}
		$i++;
	}

	

if ($lastindex>80)
echo "WARNING: The output has greater than the Wikipedia Template:Family tree maximum of 80 \"lines of relatedness\" tiles.\nThe tree may have have an unexpected appearance. For more information, see:\n\nhttp://en.wikipedia.org/wiki/Template:Family_tree\n\n\n";






	$i= count($Lines)-1;
	echo "{{familytree/start}}";
	echo "\n";
	while($i >= 0)
	{
		$j=0;
		while($j < count($Lines[$i]))
		{
			echo $Lines[$i][$j];
			if (substr($Lines[$i][$j], 1, 2) == 'NA')
				$j+=3;
			else
				$j++;
		}
		echo "\n";
		$i--;
	}
	echo "{{familytree/end}}";
	echo "\n";
	
	
}
function ReplaceCodes()
{
	global $Lines;
	global $Members;
	global $Records;
	$i=0;
	$m=1;
		
	while($i < count($Lines))
	{
		$k=0;
		while($k < count($Lines[$i]))
		{
			$j=0;
			while($j < count($Members))
			{



				if ($Members[$j] == $Lines[$i][$k])
				{
					$Lines[$i][count($Lines[$i])-$m] = $Records[$Members[$j]]->boxtext."=[[".trim($Records[$Members[$j]]->text)."]]";
					$Lines[$i][$k] = trim($Records[$Members[$j]]->boxtext);
					
					$m++;
				}
				$j++;
			}
			
			$k++;
		}
		
		$Lines[$i][0] = "{{familytree ".$Lines[$i][0]; 
		$Lines[$i][count($Lines[$i])-1] = $Lines[$i][count($Lines[$i])-1]."}}";

		$i++;
	}



}
function RemoveExtraLinks()
{
	global $Lines;
	$i=0;
	while($i < count($Lines))
	{
		$k=0;
		while($k < count($Lines[$i]))
		{
			if (substr($Lines[$i][$k], 0, 1)=="{")
			{
				$Lines[$i][$k] = "";
				$t=$i+1;
				while($t < count($Lines) && $Lines[$t][$k] != 'y')
				{
					$Lines[$t][$k]="";
					$t++;
				}
				$Lines[$t][$k]=$Lines[$t][$k-1];
		
				
			}
		
			if (substr($Lines[$i][$k], 0, 1)=="[")
			{
				$Lines[$i][$k] = "";
				$t=$i-1;
				while($t >= 0 && $Lines[$t][$k] == '!')
				{
					$Lines[$t][$k]="";
					$t--;
				}
				
		
				
			}
			
			$k++;
		}
		$i++;
	}		
}
function ReplaceSpaces()
{
	global $Lines;
	global $Records;
	
	$i=0;
	while($i < count($Lines))
	{
		$k=0;
		while($k < count($Lines[$i]))
		{
			if ($Lines[$i][$k]=="")
				$Lines[$i][$k] = "| ";
			else 
				if ($k!=0)
					$Lines[$i][$k] = "|".$Lines[$i][$k];
			
			$k++;
		}
		$i++;
	}

}
function JoinLevels()
{
	global $Lines;
$newlines=array();
$defaultline = array();
$i=0;
while($i < 160)
{
	$defaultline[]="";
	$i++;
}

	$i=0;
	while($i < count($Lines))
	{
		$j=count($Lines[$i])-1;
		while($j >= 0)
		{
			if (substr($Lines[$i][$j], 0, 1) == '[')
			{
				if (substr($Lines[$i][$j], strlen($Lines[$i][$j])-1, 1) == ']')
				{
					$fcode = substr($Lines[$i][$j], 1, strlen($Lines[$i][$j])-2);
					$k=0;
					while($k < count($Lines[$i+1]))
					{
					if (substr($Lines[$i+1][$k], 0, 1) == '{')
					{
						if (substr($Lines[$i+1][$k], strlen($Lines[$i+1][$k])-1, 1) == '}')
						{
						$scode = substr($Lines[$i+1][$k], 1, strlen($Lines[$i+1][$k])-2);
							if ($fcode == $scode)
							{
								$newline=$Lines[$i];

								if ($k==$j)
								{
									$Lines[$i][$j]="!";
									$Lines[$i+1][$k]="!";
								}
								elseif ($j < $k)
								{
									$Lines[$i][$j] = ',';
									$m=$j+1;
									while($m < $k)
									{
										$Lines[$i][$m]="-";
										$m++;
									}
									$Lines[$i][$m]="'";
									$Lines[$i+1][$k]="!";
								}
								else
								{
									$Lines[$i][$k] = '`';
									$m=$k+1;
									while($m < $j)
									{
										$Lines[$i][$m]="-";
										$m++;
									}
									$Lines[$i][$m]=".";
									$Lines[$i+1][$k]="!";

								
							
								}
								$n=0;
								while($n < count($Lines[$i]))
								{
									if (substr($Lines[$i][$n], 0, 1) == '[')
									{
										//if ($Lines[$i-1][$n] != "")
										//	$Lines[$i][$n]="!";
										//else
											$Lines[$i][$n]="";
									}
									$n++;
								}

								$newline[$j]="";
								$n=0;
								$newflag=false;
								while($n < count($newline))
								{
									if (substr($newline[$n], 0, 1) == '[')
									{
										$newflag=true;
										break;
									}
									$n++;
								}
								if ($newflag== true)
								{
									$Lines[] = $Lines[count($Lines)-1];
									$p=count($Lines)-2;
									while($p > $i)
									{
										$Lines[$p] = $Lines[$p-1];
										$p--;
									}
									$Lines[$i+1] = $newline;
									
									
								}
								
							}
						}
					}
					$k++;
					}
					
				}	
			}		
			$j--;
		} 
		$i++;	
	}
}

function JoinBrokenLines()
{
	global $Lines;

	$i=count($Lines)-1;
	while($i >= 0)
	{
		$j=0;
		while($j < count($Lines[$i]))
		{

			if ($Lines[$i][$j] == '!')
			{
				$k=$i-1;
				while($k >= 0 && $Lines[$k][$j]=='')
				{
					$Lines[$k][$j]='!';
					$k--;
				}
				$k=$i+1;
				while($k < count($Lines) && $Lines[$k][$j]=='')
				{
					$Lines[$k][$j]='!';
					$k++;
				}
			}
			if ($Lines[$i][$j] == ',' || $Lines[$i][$j] == '.')
			{
				$k=$i-1;
				while($k >= 0 && $Lines[$k][$j]=='')
				{
					$Lines[$k][$j]='!';
					$k--;
				}
				while (isset($Lines[$k][$j]) && $Lines[$k][$j]=='-')
				{
					$Lines[$k][$j]='*';	
					$k--;
				}	
			}
			if ($Lines[$i][$j] == '`' || $Lines[$i][$j] == "'")
			{
				$k=$i+1;
				while($k < count($Lines) && $Lines[$k][$j]=='')
				{
					$Lines[$k][$j]='!';
					$k++;
				}	
				while (isset($Lines[$k][$j]) && $Lines[$k][$j]=='-')
				{
					$Lines[$k][$j]='*';	
					$k++;
				}
			}
			
				//$Lines[$i+1][$j] = '!';
			$j++;
		}
		$i--;	
	}

}

function MakeRelation($f, $line)
{
	global $Records;
	global $Lines;
	$oldlines=array();
	$temp=array();
	$temp = $Lines[$line];
	$temp2 = $Lines[$line];
	$temp3 = $Lines[$line];
	$temp4 = $Lines[$line];
	$lsline = $Lines[$line];
	$i=$line+1;
	$flag=true;


	while($i < count($Lines) && $flag==true )
	{
		$j=0;
		while($j < count($Lines[$i]))
		{	
			if (substr($Lines[$i][$j], 0, 1)=='{')
				$flag=false;
			$j++;
		}
		if ($flag==true)
		{
			$oldlines[] = $Lines[$i];
			$delines[] = $i;
		}
			
		
		$i++;
	}
	if (count($oldlines) > 0)
	{
	$i=0;
	$newlines = array();
	while($i < count($Lines))
	{
		if (!in_array($i, $delines))
			$newlines[] = $Lines[$i];
		$i++;
	}

	$Lines = $newlines;
	//$line = $line - count($oldlines);
	}


	$sonlines = array();
	$sonlines[0] = $Lines[$line];
	$sonlines[1] = $Lines[$line];
	$sonlines[2] = $Lines[$line];
	$sonlines[3] = $Lines[$line];
		


	$mx=-1;
	$mn=100;
	$joints = array();
	$i=0;
	while($i < 160)
	{
		if ($sonlines[0][$i] != "")
		{
			if (in_array($f, $Records[$sonlines[0][$i]]->up)) // && count($Records[$sonlines[0][$i]]->marital) == 0)
			{


				$sonlines[0][$i] = "!";
				
				if ($mx < $i)
					$mx=$i;
				if ($mn > $i)
					$mn=$i;

				$joints[]=$i;
			}
		}
		$i++;
	}
	
	
	if (count($joints) > 0)
	{
		$sonlines[1][$joints[0]]=",";
		$sonlines[1][$joints[count($joints)-1]]=".";

	$i=$mn+1;
	while($i < $mx)
	{
		if ($sonlines[1][$i]!="!")
			$sonlines[1][$i]="-";
		$i++;
	}
	$i=1;
	while($i < count($joints)-1)
	{
		$sonlines[1][$joints[$i]]="v";	
		$i++;
	}	

	if ($sonlines[1][($mn+$mx)/2-1] == "-" && $sonlines[1][($mn+$mx)/2+1] == "-")
	{
		if ($sonlines[1][($mn+$mx)/2]=="v")
			$sonlines[1][($mn+$mx)/2]="+";
		else
			$sonlines[1][($mn+$mx)/2]="^";
	}
	else
	{
		$sonlines[1][($mn+$mx)/2]="!";
	
	}

	$sonlines[2][($mn+$mx)/2]="!";

	

	$sonlines[3][($mn+$mx)/2]='['.$f.']';
	


	}
	
	$i=0;
	while($i < 160)
	{
		$w=0;
		while($w < count($sonlines))
		{
			
			if (!($sonlines[$w][$i]=="!" || $sonlines[$w][$i]=="-" || $sonlines[$w][$i]==":" || $sonlines[$w][$i]=="L" || $sonlines[$w][$i]=="J" || $sonlines[$w][$i]=="," || $sonlines[$w][$i]=="D" || $sonlines[$w][$i]=="." || $sonlines[$w][$i]=="^" || $sonlines[$w][$i]=="v" || $sonlines[$w][$i]=="+" || $sonlines[$w][$i]=="`" || $sonlines[$w][$i]=="'" || $sonlines[$w][$i]=="y" || $sonlines[$w][$i]=="~"  || substr($sonlines[$w][$i], 0, 1)=="["   || substr($sonlines[$w][$i], 0, 1)=="{"   ||  substr($sonlines[$w][$i], 0, 1)=="x"))
			{
				$sonlines[$w][$i]=''; 
			}
			if ( substr($sonlines[$w][$i], 0, 1)=="x")
				  $sonlines[$w][$i] =   substr($sonlines[$w][$i], 1);
			$w++;
		}

		$i++;
	}




if (count($oldlines))
{

	$i=0;
	while($i < count($oldlines))
	{
		if (isset($sonlines[$i]))
		{
			$j=0;
			while($j < count($sonlines[$i]))
			{
				if ($oldlines[$i][$j]!="")
					$sonlines[$i][$j] = $oldlines[$i][$j];
			
				$j++;
			}
		}
		else
		{
			$sonlines[] = $oldlines[$i];
			
		}
			
		$i++;
		
	}


}


//print_r($sonlines);

	
	array_push($Lines,$sonlines[0]);
	array_push($Lines,$sonlines[1]);
	array_push($Lines,$sonlines[2]);
	array_push($Lines,$sonlines[3]);

return $line;


}
function MakeMarital($s, $e, $line)
{
	global $Lines;
	global $Records;
	$lsline = $Lines[$line];
	$oldlines=array();
	$i=$line-1;
	$flag=true;
	$delines=array();
	while($i >= 0 && $flag==true )
	{
		$j=0;
		while($j < count($Lines[$i]))
		{	
			if (substr($Lines[$i][$j], 0, 1)=='[')
				$flag=false;
			$j++;
		}
		if ($flag==true)
		{
			$oldlines[] = $Lines[$i];
			$delines[] = $i;
		}
			
		
		$i--;
	}
	if (count($oldlines) > 0)
	{
	$i=0;
	$newlines = array();
	while($i < count($Lines))
	{
		if (!in_array($i, $delines))
			$newlines[] = $Lines[$i];
		$i++;
	}

	$Lines = $newlines;
	$line = $line - count($oldlines);
	}

	$wifelines = array();
	$wifelines[0] = $Lines[$line];
	$wifelines[1] = $Lines[$line];
	
	$i=0;
	$si=0;
	$ei=0;
	$w=0;
	$ends = array();
			
	while($i < 160)
	{
		if ($wifelines[$w][$i] == $s)
		{
			
			$si=$i;
			$wifelines[$w][$i]=":";
			$wifelines[$w+1][$i]="L";

			$j=160-1;
			$found=0;
			while($j > $i)
			{
				if (in_array($wifelines[$w][$j], $e))
				{
					if ($found==0)
					{
					$wifelines[$w][$j]=":";
					$wifelines[$w+1][$j]="J";
					$ends[]=$j;
					$endlines[]=$w+1;
					$found++;

					$t=$i+1;
					while($t < $j)
					{
						$wifelines[$w+1][$t]="~";
						$t++;
					}
	


					}
					else
					{

						$wifelines[] = $Lines[$line];
						$wifelines[count($wifelines)-1][$i]=":";
						$wifelines[] = $Lines[$line];
						$wifelines[count($wifelines)-1][$i]=":";
						$wifelines[] = $Lines[$line];
						$wifelines[count($wifelines)-1][$i]="L";
						
$wifelines[count($wifelines)-4][$i]="D";
						
						$Lines[$line][$j]="";

						$f=$found;
						$ends[] = $j;
						while($f >= 0)
						{
							$wifelines[count($wifelines)-2-$f][$j] = "";//":";
							$f--;	
						}
						$wifelines[count($wifelines)-2][$j] = "x".$wifelines[count($wifelines)-1][$j];

						$wifelines[count($wifelines)-1][$j] = "J";

						$t=$i+1;
						while($t < $j)
						{
							$wifelines[count($wifelines)-1][$t]="~";
							$t++;
						}

						$endlines[]=count($wifelines)-1;
	
					//$ends[]=$j;
					//$wifelines[$w][$j]=":";
					//$wifelines[$w+1][$j]="J";
					
					}

				}

				$j--;
			}
			break;
		}
		

	
		$i++;
	}


	
	
	$e=0;
	while($e < count($ends))
	{
	
	//$wifelines[$endlines[$e]][($si+$ends[$e])/2+($ends[$e]-($si+$ends[$e])/2)/2] = "y";
	$wifelines[$endlines[$e]][$ends[$e]-1] = "y";
	
	$k=0;
	while($k < count($ends)-$e)
	{
		if (!isset($wifelines[$endlines[$e]+1+$k]))
		{
			$wifelines[] = $Lines[$line];
			
		}
		//$wifelines[$endlines[$e]+1+$k][($si+$ends[$e])/2+($ends[$e]-($si+$ends[$e])/2)/2]="!";
$wifelines[$endlines[$e]+1+$k][$ends[$e]-1]="!";
		$k++;	
	}

	
	if (!isset($wifelines[$endlines[$e]+1+$k]))
		$wifelines[] = $Lines[$line];
	
	//$wifelines[$endlines[$e]+1+$k][($si+$ends[$e])/2+($ends[$e]-($si+$ends[$e])/2)/2]='{'.$Records[$s]->sub[count($ends)-1-$e].'}';
$wifelines[$endlines[$e]+1+$k][$ends[$e]-1]='{'.$Records[$s]->sub[count($ends)-1-$e].'}';
	$e++;
	
	}

	
	
	$i=0;
	while($i < 160)
	{
		$w=0;
		while($w < count($wifelines))
		{
			
			if (!($wifelines[$w][$i]=="!" || $wifelines[$w][$i]=="-" || $wifelines[$w][$i]==":" || $wifelines[$w][$i]=="L" || $wifelines[$w][$i]=="J" || $wifelines[$w][$i]=="," || $wifelines[$w][$i]=="D" || $wifelines[$w][$i]=="." || $wifelines[$w][$i]=="^" || $wifelines[$w][$i]=="v" || $wifelines[$w][$i]=="+" || $wifelines[$w][$i]=="`" || $wifelines[$w][$i]=="'" || $wifelines[$w][$i]=="y" || $wifelines[$w][$i]=="~"  || substr($wifelines[$w][$i], 0, 1)=="["   || substr($wifelines[$w][$i], 0, 1)=="{"   ||  substr($wifelines[$w][$i], 0, 1)=="x"))
			{
				$wifelines[$w][$i]=''; 
			}
			if ( substr($wifelines[$w][$i], 0, 1)=="x")
				  $wifelines[$w][$i] =   substr($wifelines[$w][$i], 1);
			$w++;
		}

		$i++;
	}



if (count($oldlines))
{

	$i=0;
	while($i < count($oldlines))
	{
		if (isset($wifelines[$i]))
		{
			$j=0;
			while($j < count($wifelines[$i]))
			{
				if ($oldlines[$i][$j]!="")
					$wifelines[$i][$j] = $oldlines[$i][$j];
			
				$j++;
			}
		}
		else
		{
			$wifelines[] = $oldlines[$i];
			
		}
			
		$i++;
		
	}


}

$i=0;
while($i < count($wifelines))
{
	$j=0;
	while($j < count($wifelines[$i]))
	{
		if (substr($wifelines[$i][$j], 0, 1)=="{")
		{
			$wifelines[count($wifelines)-1][$j] = $wifelines[$i][$j];
			$k=$i;
			while($k < count($wifelines)-1)
			{
				$wifelines[$k][$j] = "!";	
				$k++;
			}
		}		

		$j++;
	}
	$i++;
}
		
	$tp=array();
	$l=$line;
	while($l < count($Lines))
	{
		$tp[] = $Lines[$l];
		$l++;
	}
	$w=count($wifelines)-1;


	$ln=0;
	while($w >= 0)
	{
		if ($line < $l)
		{
			$Lines[$line+$ln]= $wifelines[$w];
			$l--;
			$ln++;

		}
		else
			$Lines[] = $wifelines[$w];
	
		$w--;
	}

	
$k=0;
while($k < count($tp))
{
	if ($line < $l)
	{
		$Lines[$line+$k+count($wifelines)]=$tp[$k];
		$l--;
	}
	else
		$Lines[] = $tp[$k];
	
	$k++;
}



return $line+count($wifelines);

}

$Lines = array();
$defaultline = array();
$i=0;
while($i < 160)
{
	$defaultline[]="";
	$i++;
}
$i=count($Levels)-1;
while($i >= 0)
{
	$level = $Levels[$i];
	$newlevel=array();	
	$j=0;
	$line=$defaultline;
	$line2=$defaultline;
	$line3=$defaultline;
	$linestart = abs($MaxLevel/2+2-count($level)/2);
	$oldfamily='';
	while($j < count($level))
	{
		$line[$linestart] = $level[$j];	

		$arr = $Records[$level[$j]]->childs;
		$a=0;
		$family='';	
		while($a < count($arr))
		{
			if (trim($Records[$arr[$a]]->tag)=="FAMS")
			{
				$family = $Records[$arr[$a]]->val;
				break;
			}
			$a++;
		}
		if ($family == $oldfamily)
			$linestart+=4;
		else
			$linestart+=6;

		$oldfamily=$family;
		$j++;
	}
	array_push($Lines, $line);
	$j=0;
	$family=array();
	$curline = count($Lines)-1;
	
	while($j < count($level))
	{
		$arr = $Records[$level[$j]]->up;
		$a=0;
		while($a < count($arr))
		{
			if (!in_array($arr[$a], $family))
			{
				$family[] = $arr[$a];
				$curline = MakeRelation($arr[$a], $curline);
	
			}
			$a++;
		}

		if (count($Records[$level[$j]]->marital) > 0)
		{	$husband = $Records[$level[$j]]->marital[0];
			$wifes= array();
			do{
			$wifes[]=$level[$j];
			$husband = $Records[$level[$j]]->marital[0];
			$j++;
			}while($j < count($level) && count($Records[$level[$j]]->marital) > 0 && $Records[$level[$j]]->marital[0]==$husband);

			$curline = MakeMarital($husband, $wifes, $curline); 

	
		}
		
		$j++;
	}
	//$line = MergeLine($line2, $line3);
	/*
if ($line2!=$defaultline)
		array_push($Lines, $line2);
	if ($line3!=$defaultline)
		array_push($Lines, $line3);
	if ($line=$defaultline)
		array_push($Lines, $line);
*/
	$i--;


}

//print_r($Records);
//print_r($Levels);
JoinLevels();
RemoveExtraLinks();
JoinBrokenLines();

ReplaceCodes();
ReplaceSpaces();


PrintLines();



?>
</textarea>
</body>
</html>