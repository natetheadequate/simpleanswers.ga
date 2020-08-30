<?php
function verify($string, $withquotes='"',$slashcount=1){
        if($string==''){return '';}
        if($withquotes!="n"){$newstring=$withquotes;}
        $length=strlen($string);
        $slashes='';
        for($i=0;$i<$slashcount;$i++){
                $slashes.="\\";
        }
        $malicious=((substr_count($string,'<script')!=0)||(substr_count($string,'</script')!=0));
        for($i=0;$i<$length;$i++){
                switch(substr($string,$i,1)){
                        case '"': $newstring.=$slashes."&quot;";continue;
                        case "'": $newstring.=$slashes."&apos;";continue;
                        case "\\": $newstring.=$slashes."&#x005C;";continue;
                        case "<":if($malicious){$newstring.=$slashes."&lt;";continue;}
                                 else{$newstring.=substr($string,$i,1);continue;}
                        case ">":if($malicious){$newstring.=$slashes."&rt;";continue;}
                                 else{$newstring.=substr($string,$i,1);continue;}
                        case "{":$newstring.=$slashes."&#x007B;";continue;
                        case "}":$newstring.=$slashes."&#x007D;";continue;
                        case "¦":$newstring.=$slashes."&brvbar;";continue;
                        case "«":$newstring.=$slashes."&laquo;";continue;
                        case "»":$newstring.=$slashes."&raquo;";continue;
                        case "¬":$newstring.=$slashes."&not;";continue;
                        case "!":$newstring.=$slashes."&#33;";continue;
                        default: $newstring.=substr($string,$i,1);continue;
                }
        }
        $newstring=str_replace("\u{000D}\u{000A}",'<br />',$newstring);
        $newstring=str_replace("\u{201d}",$slashes.'&quot;',$newstring);
        $newstring=str_replace("\u{201c}",$slashes.'&quot;',$newstring);
        $newstring=str_replace(["\u{000A}","\u{000B}","\u{000C}","\u{000D}"],'<br />',$newstring);
        $newstring=str_replace(["\f",'\r'],'<br />',$newstring);
        $newstring.=$slashes.'&#x200A;';//makes sure quote is not escaped by ensuring an hairspace char at the end
        if($withquotes!="n"){
                $newstring.=$withquotes;
        }
        return $newstring;
}
?>