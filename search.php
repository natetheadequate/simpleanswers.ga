<?php
include 'verifier.php';
function subsearch($column)
{
    global $str,$i,$output;
    $datafile=($database->query('SELECT `p`,`'.$column.'` FROM Questions WHERE LOWER(`'.$column.'`) Like "%'.strtolower($str).'%" ORDER BY `netvotes` DESC'));
    while ($i<5&&($data=$datafile->fetch_array())) {
        preg_match_all('/((?>(?:^|\s).{0,20}'.$str.').{0,20}(?:\s|$))/i', $data[$column], $matches);//'titlearray', more like $column.'array
        foreach ($matches[0] as $match) {
            if ($i<5) {
                $output.='<li><a href="?p='.$data['p'].'">'.preg_replace('/'.$str.'/i', '<mark class="searchmark">$0</mark>', $match).'</a></li>';
                $i++;
            }
        }
    }
}
function search()
{
    global $str,$output;
    if ($str=='') {
        return '';
    }
    subsearch('title');
    subsearch('content');
    //subsearch('comments');//problems with picking up p in posted, have to cycle thorugh array and make own array, match with that, etc
    if ($output=='') {
        return "<li><a>Nothing found</a></li>";
    }
    return $output;
}
$output='';
$i=0;
$str=substr(verify($_REQUEST['str'], "n"), 0, -9);
echo search($str);
