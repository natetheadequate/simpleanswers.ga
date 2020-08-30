<html>
<head>
<meta name="robots" content="noindex,nofollow">
<script>
function siblings(id,comments){//just make it submit to a new php file
        var siblings=0;
        for(var k=0;k<comments.length;k++){
              if(((comments[k]['id'].substring(0,comments[k]['id'].length-1))==id.substring(0,id.length-1))&&(comments[k]['id'].charAt(comments[k]['id'].length-1)!='X')){siblings++;}// comments includes all the comments verified and unverified
        }
        return siblings.toString();
}
function getsql(contents,storyemails,storytitles,comments,p){
        contents=JSON.parse(contents);
        storyemails=JSON.parse(storyemails);
        comments=JSON.parse(comments);
        storytitles=JSON.parse(storytitles);
        setTimeout(()=>{
        var sql=''; 
        for(var i=0;!!document.getElementById(i.toString());i++){
                if(document.getElementById(i.toString()).checked){
                        console.log(i,p,contents[i]);
                        sql+='Update `Questions` Set `p`='+p+', `posted`=UNIX_TIMESTAMP() WHERE `content`="'+contents[i]['content']+'" AND `title`="'+contents[i]['title']+'";';
                        p++;
                }else{
                        console.log(i,contents[i]);
                        sql+='DELETE FROM `Questions` WHERE `content`="'+contents[i]['content']+'" AND `title`="'+contents[i]['title']+'";';  
                }
        }
        var mailstring='';
        for(var i=0;i<comments.length;i++){
                if(!!comments[i]){
                for(var j=0;j<comments[i].length;j++){
                        if(!!document.getElementById(i.toString()+'and'+j.toString())){
                                if(document.getElementById(i.toString()+'and'+j.toString()).checked){
                                  var sibs=siblings(comments[i][j]['id'],comments[i])
                                  comments[i][j]['id']=comments[i][j]['id'].substring(0,comments[i][j]['id'].length-1)+sibs;
                                  comments[i][j]['posted']='UNIX_TIMESTAMP()';
                                  if(comments[i][j]['id'].length==1){
                                          if(!!storyemails[i]){
                                             mailstring='mailto:'+storyemails[i]+'?subject=Somebody commented on your post on Simple Science!&body=Somebody commented %22'+comments[i][j]['content']+'%22 on your post titled %22'+storytitles[i]+'%22';
                                             document.getElementById('emails').innerHTML+='<a target="_blank" href="'+mailstring+'">LINK</a>'
                                          }
                                  }else{
                                          for(var g=0;g<comments[i].length;g++){
                                                  if(comments[i][g]['id']==comments[i][j]['id'].substring(0,comments[i][j]['id'].length-1))
                                                          if(!!comments[i][g]['email']){
                                                                  mailstring='mailto:'+comments[i][g]['email']+'?subject=Somebody commented on your post on Simple Science!&body=Somebody replied %22'+comments[i][j]['content']+'%22 to your comment %22'+comments[i][g]['content']+'%22';
                                                                  document.getElementById('emails').innerHTML+='<a target="_blank" href=\"'+mailstring+'\">LINK</a>';
                                                          }else{
                                                                  break;
                                                          }
                                          }
                                  }
                                }else{
                                  comments[i][j]['content']='';//things would be thrown off if outright deleted
                                }
                        }
                }}
        }
        console.log('here');
        for(var i=0;i<comments.length;i++){
        if(!!comments[i]){
        for(var j=0;j<comments[i].length;j++){
                if(comments[i][j]['content']==''){
                        comments[i].splice(j,1);
                        j--;
                        
                }
        
        }}}
        for(var i=0;i<document.getElementById('emails').children.length;i++){
                console.log(document.getElementById('emails').children[i]);
        }
        for(var i=0;i<comments.length;i++){
                if(!!comments[i]){
                sql+="UPDATE `Questions` Set `comments`='"+JSON.stringify(comments[i]).replace(/&/g,"\\&").replace(/;/g,"\\;")+"' where p="+i+";";
                }
        }
        sql+="UPDATE `Questions` Set `comments`=Replace(`comments`,'\"posted\":\"UNIX_TIMESTAMP\(\)\"',Concat('\"posted\":',UNIX_TIMESTAMP()));";
        sql+="UpDATE `Questions` Set `comments`=replace(`comments`,'&#x200A;','');";
        console.log(sql);
        },2000);
};
</script>
</head>
<body>
<?php
include 'verifier.php';
require 'DBconnector.php';
$database=$DB;
$datafile=$database->query("SELECT DISTINCT `title`,`content`,`submitted`,`email` FROM `Questions` WHERE `posted`=-1");//distinct cuz spam
$contents=array();
while ($datum=$datafile->fetch_array()) {
    array_push($contents, $datum);
}
$index=0;
foreach ($contents as $content) {
    echo "<b>".$content['title']."</b><br />".$content['content'];//will be not encoded bc displayed to screen, so cant compare screen output with database
    echo "<input type='checkbox' checked id='".$index."'/><hr />";
    $index++;
}
echo '<br />Comments:<br /><br />';
$datafile=$database->query("SELECT `title`,`email`,`comments` FROM `Questions` WHERE `p`>-1 ORDER BY `p` ASC");
$storyemails=array();
$storytitles=array();
$comments=array();
while ($datum=$datafile->fetch_array()) {
    array_push($storyemails, $datum['email']);
    array_push($storytitles, $datum['title']);
    array_push($comments, json_decode($datum['comments'], true));
}
$index=0;
$i=0;
$j=0;
$notpostedsijs=array();
foreach ($comments as $pageofcomments) {
    if (isset($pageofcomments)) {
        foreach ($pageofcomments as $comment) {
            if ($comment['posted']==-1) {
                $maxdepthlevel=strlen($comment['id']);//should only do below the length of the id of the comment cuz its parents wont be longer
                                $depthlevel=1;//the length of id is the depth level because of the way it is.1 char is top level
                                while ($depthlevel<$maxdepthlevel) {//so it ccan diplsay the parents too
                                    foreach ($pageofcomments as $possibleparentcomment) {
                                        if (strlen($possibleparentcomment['id'])==$depthlevel&&$possibleparentcomment['id']==substr($comment['id'], 0, $depthlevel)) {
                                            echo $possibleparentcomment['content'];
                                            echo '<br />';
                                            break;
                                        }
                                    }
                                    $depthlevel++;
                                }
                echo '<b>'.$comment['content'].'</b>';
                echo "<input type='checkbox' checked id='".$i.'and'.$j."'/>";
                echo '<hr />';
            }
            $j++;
        }
    }
    $i++;
    $j=0;
}
?>
<div id="emails"></div>
<?php
echo
"<script> 
function callgetsql(){
        getsql('".json_encode($contents)."','".json_encode($storyemails)."','".json_encode($storytitles)."','".json_encode($comments)."','".((($database->query('Select p From Questions Order by p DESC'))->fetch_array())[0]+1)."');
}
</script>";
echo'<button onclick="callgetsql();">Submit</button>';?>
</body>
</html>