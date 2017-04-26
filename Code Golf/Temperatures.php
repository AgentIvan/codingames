<?fgets(STDIN);$t=explode(' ',trim(fgets(STDIN)));sort($t);$m=999;foreach($t as $p)$m=abs($p)<=abs($m)?$p:$m;echo $m?$m:0;
//you may use shorter of your code
//fgets(STDIN);$t=fgetcsv(STDIN,0,' ');sort($t);$m=999;foreach($t as$p)abs($p)>abs($m)||$m=$p;echo$m?$m:0;
