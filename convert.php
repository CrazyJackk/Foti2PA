<?
/*
	舊的設定檔 ori.conf
	新的設定檔 new.conf
*/
$content = file_get_contents("ori.conf");
$contents = preg_split("/\r\n|\n/",$content);
for ( $i=0;$i<count($contents);$i++){
	$change=0;
	$ret = preg_match("/[\x{4e00}-\x{9fa5}]+/u",$contents[$i]);
	if ( $ret === 1 ){
		$ret = preg_match("/(?<space>^[ ]+)edit[ \"]+(?<name>[^\"]+)/u",$contents[$i],$m);
		if ( $ret === 1 ){
			$name = $m['name'];
			$j=$i;
			$block = "";
			do {
				$j++;
				$chk = preg_match("/^[ ]+next/",$contents[$j]);
				if ( $chk === 0 ){
					$block.=$contents[$j]."\n";
				}
			}while( $chk === 0 );
			$p = "/subnet (?<subnet>[0-9.]+) (?<submask>[0-9.]+)|start-ip (?<range>[0-9.]+)|country \"(?<country>[^\"]+)\"/m";
			$c = preg_match_all($p,$block,$match);
			if ( $c === 1){
				$newname = '';
				if ( $match['subnet'][0] !== "" ) {
					$mask = (int)mask2cidr($match['submask'][0]);
					if ( $mask === 32 ){
						$newname = "IP-".$match['subnet'][0];
					}else{
						$newname = "S-".$match['subnet'][0] . "_" . mask2cidr($match['submask'][0]);
					}
				}
				if ( $match['range'][0] !== "" ) {
					$newname = "R-".$match['range'][0];
				}
				if ( $match['country'][0] !== "" ) {
					$newname = "C-".$match['country'][0];
				}
				$needchange[] = array(
					"ori" => $name,
					"new" => $newname
				);
				$newconf .= $m['space'] . "edit \"$newname\"\n";
				$newconf .= $m['space'] . "    set comment \"123COMMENT123$name\"\n";
				$change=1;
			}else{
				//echo ("Some thing wrong\n");
			}
		}
	}

	if ( $change === 0 ){
	//	echo $contents[$i] . "\n";
		$newconf .= $contents[$i] . "\n";
	}
}
for ( $i=0;$i<count($needchange);$i++){
	$new = "\"".$needchange[$i]['new']."\"";
	$ori = "\"".$needchange[$i]['ori']."\"";
	$newconf = str_replace($ori,$new,$newconf);
}
$newconf = str_replace("123COMMENT123","",$newconf);
file_put_contents("new.conf",$newconf);

$content = file_get_contents("new.conf");
$contents = preg_split("/\r\n|\n/",$content);
for ( $i=0;$i<count($contents);$i++){
        $ret = preg_match("/[\x{4e00}-\x{9fa5}]+/u",$contents[$i]);
        if ( $ret === 1 ){
		if ( preg_match("/comment/",$contents[$i]) !== 1 )
		echo $contents[$i] . "\n";
	}
}

function mask2cidr($mask){
     $long = ip2long($mask);
     $base = ip2long('255.255.255.255');
     return 32-log(($long ^ $base)+1,2);
}
