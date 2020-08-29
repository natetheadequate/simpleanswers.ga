<?php if(false){$database=new mysqli("fdb22.awardspace.net","3165326_database",".Data345","3165326_database",3306);
$comments=array();
$datafile=($database->query('Select comments from Questions order by p asc'));
while($data=$datafile->fetch_array()){
        array_push($comments,json_decode($data[0],true));
}
for($i=0;$i<count($comments);$i++){
      for($j=0;$j<count($comments[$i]);$j++){
              $comments[$i][$j]['netvotes']=0;
              var_dump($comments[$i][$j]);
              echo '<br />'*5;
      }
}
$i=0;
foreach($comments as $pageofcomments){
        $database->query('update Questions set comments=\''.json_encode($pageofcomments).'\'where p='.$i);
        $i++;
}}else echo "nope";
?>
