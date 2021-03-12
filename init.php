<?php
include 'DBconnector.php';//imports $DB, the database object
if (is_null($verifieralreadycalled)) {
    include "verifier.php";
}
if (!isset($additionalinfo)) {
    $additionalinfo='';
}
$conn=(($DB->query("SELECT * FROM `Questions`"))==false)?false:true;//if the DB can be contacted, conn is true, else false
if ($conn&&isset($_POST['dothissql'])) {//this is for the voting system
        $backup=fopen('backup.txt', "a");
    if (isset($_POST['page'])&&strpos($_POST['page'], 'c')===false) {
        switch ((int)$_POST['dothissql']) {
            case 0: fwrite($backup, '¦'.$_POST['page'].'u-');
                $DB->query('UPDATE `Questions` set `upvotes`=`upvotes`-1 where p='.$_POST['page']);
                continue;
            case 1: fwrite($backup, '¦'.$_POST['page'].'d-');
                $DB->query('UPDATE `Questions` set `downvotes`=`downvotes`-1 where p='.$_POST['page']);
                continue;
            case 2: fwrite($backup, '¦'.$_POST['page'].'d-¦'.$_POST['page'].'u+');
                $DB->query('UPDATE `Questions` set `downvotes`=`downvotes`-1,`upvotes`=`upvotes`+1 where p='.$_POST['page']);
                continue;
            case 3: fwrite($backup, '¦'.$_POST['page'].'u+');
                $DB->query('UPDATE `Questions` set `upvotes`=`upvotes`+1 where p='.$_POST['page']);
                continue;
            case 4: fwrite($backup, '¦'.$_POST['page'].'d+¦'.$_POST['page'].'u-');
                $DB->query('UPDATE `Questions` set `upvotes`=`upvotes`-1,`downvotes`=`downvotes`+1 where p='.$_POST['page']);
                continue;
            case 5: fwrite($backup, '¦'.$_POST['page'].'d+');
                $DB->query('UPDATE `Questions` set `downvotes`=`downvotes`+1 where p='.$_POST['page']);
                continue;
        }
        $DB->query('UPDATE `Questions` set netvotes=upvotes-downvotes where p='.$_POST['page']);
    } elseif (isset($_POST['page'])&&strpos($_POST['page'], 'c')!==false) {
        $splitted=explode('c', $_POST['page']);
        $page=$splitted[0];
        $commentid=$splitted[1];
        $change=0;
        $comments=json_decode((($DB->query('Select `comments` from Questions where p='.$page))->fetch_array())[0], true);
        switch ((int)$_POST['dothissql']) {
                        case 0: $change=-1;
                                fwrite($backup, '¦'.$_POST['page'].'u-');
                                continue;
                        case 1: fwrite($backup, '¦'.$_POST['page'].'d-');
                                $change=1;
                                continue;
                        case 2: fwrite($backup, '¦'.$_POST['page'].'d-¦'.$_POST['page'].'u+');
                                $change=2;
                                continue;
                        case 3: fwrite($backup, '¦'.$_POST['page'].'u+');
                                $change=1;
                                continue;
                        case 4: fwrite($backup, '¦'.$_POST['page'].'d+¦'.$_POST['page'].'u-');
                                $change=-2;
                                continue;
                        case 5: fwrite($backup, '¦'.$_POST['page'].'d+');
                                $change=-1;
                                continue;
                }
                
        for ($i=0;$i<count($comments);$i++) {
            if (strval($comments[$i]['id'])===strval($commentid)) {
                $comments[$i]['netvotes']+=$change;
            }
        }
        $DB->query('Update Questions Set comments=\''.json_encode($comments).'\' where p='.$page);
        $additionalinfo.='<script type="text/javascript">document.getElementById("commentidpasser").innerHTML="'.$commentid.'"</script>';
    }
    $additionalinfo.='<script type="text/javascript">document.getElementById("messagepassed").innerHTML="';
    switch ((int)$_POST['dothissql']) {//these are the values of the undo fucntion that must be called when i refresh the page in the js
                case 0:$additionalinfo.='3';continue;
                case 1:$additionalinfo.='5';continue;
                case 2:$additionalinfo.='4';continue;
                case 3:$additionalinfo.='0';continue;
                case 4:$additionalinfo.='2';continue;
                case 5:$additionalinfo.='1';continue;
        }
    $additionalinfo.='";</script>';
    //$additionalinfo.='<script>if(window.history.replaceState){window.history.replaceState(null,null,window.location.href);}</script>';
    $_POST['dothissql']=null;
}
if (isset($_POST['commenttext'])) {//this only triggers on reload following comment submission
    if ($conn) {
        $datafile=$DB->query("SELECT `comments` FROM `Questions` WHERE p=".$_POST['page']);
        $data=$datafile->fetch_array();
        $data=json_decode($data['comments'], true);
        $giveup=false;
        $id='';
        if (isset($_POST['id'])) {
            $id.=strval($_POST['id']);
        }
        $id.="X";
        if (is_null($data)) {
            $data=array();
        } else {//check for duplicates
                foreach ($data as $datum) {//prevent spam
                        if ($datum['content']==verify($_POST['commenttext'], "n", 0)&&substr($datum['id'], 0, -1)==substr($id, 0, -1)) {
                            $additionalinfo.='<script>alert("The comment you submitted was a duplicate and will not be posted")</script>';
                            $giveup=true;
                            break;
                        }
                }
        }
        if (!$giveup) {
            $time=time();
            $newdata=['content'=>verify($_POST["commenttext"], "n", 0),'submitted'=>$time,"posted"=>-1,"id"=>$id,"netvotes"=>0];
            if (isset($_POST['email'])&&$_POST['email']!='') {
                $newdata['email']=verify($_POST['email'], "n", 0);
            };
            //var_dump(json_encode($newdata));
            array_push($data, $newdata);
            /*$matches=array();
            preg_match_all('/\\\u(.[0-9a-z]{4})/',json_encode($data),$matches);
            for($i=0;$i<count($matches);$i++){
                    $data=preg_replace('/\\u[0-9a-z]{4}/','\\u{'.$matches[$i].'}',json_encode($data));
            }*/
            $backup=fopen('backup.txt', "a");
            fwrite($backup, '¦c'.$_POST['page'].'¬'.json_encode($data), 200);
            $DB->query("Update `BackupQuestions2` SET `comments`=`comments`+'".str_replace('\\/', '/', json_encode($newdata))."' WHERE `p`=".$_POST['page']);
            $DB->query("UPDATE `BackupQuestions` SET `comments`='".str_replace('\\/', '/', json_encode($data))."' WHERE `p`=".$_POST['page']);
            $DB->query("UPDATE `Questions` SET `comments`='".str_replace('\\/', '/', json_encode($data))."' WHERE `p`=".$_POST['page']);
            $childnodebefore=2;
            if ($_POST['id']=='') {
                $childnodebefore++;
            }
            sleep(1.5);
            $received=($DB->query('SELECT `comments` FROM `Questions` WHERE `p`='.$_POST['page']))->fetch_array();
            $errormessage=null;
            if (isset($received[0])) {
                if (null!=json_decode($received[0], true)) {
                    $errormessage='ERRORCOMMENTBROKEJSON';
                    foreach (json_decode($received[0], true) as $comment) {
                        if ($comment['submitted']==$time) {
                            $errormessage=null;
                            $additionalinfo.='<script type="text/javascript">document.getElementById("commentfooter'.$_POST['id'].'").insertBefore(document.createElement("p"),document.getElementById("commentfooter'.$_POST['id'].'").childNodes['.$childnodebefore.']).innerHTML="Your comment &ldquo;'.substr(verify($_POST['commenttext'], "n", 0), 0, -1).'&rdquo; has been submitted";</script>';
                            break;//this breaks out of foreach
                        }
                    }
                } else {
                    $errormessage='ERRORCHECKDATABASEJSON';
                }
            } else {
                $errormessage='ERRORDATABASEMALFUNCTION';
            }
            if (isset($errormessage)) {
                $pageinfo=['title'=>"Error receiving submission",'content'=>'Your submission was received, but something happened due to an error on my part.  Please report this error to me using this <a target:"_blank" href="mailto:whitenat@students.holliston.k12.ma.us?subject='.$errormessage.'&body=settedcommentsto'.urlencode(var_export(str_split(json_encode($data)), true)).'page'.urlencode(var_export($_POST['page'], true)).'">link</a> so I can work to fix this immediately.'];
            }
        }
        foreach ($_POST as $key=>$value) {
            $_POST[$key]=null;
        }
    } else {
        $pageinfo=['title'=>"Cannot reach server",'content'=>'The website is temporarily down and the comment could not be posted. The site should be back online in under an hour. I apologize for the inconvenience.'];
    }
}
if ($conn) {
    $datafile=$DB->query("SELECT `p`,`title` FROM `Questions` WHERE `p`>-1 AND `posted`>0 ORDER BY netvotes DESC, submitted DESC");
    $navdata=array();
    while ($data=$datafile->fetch_array()) {
        array_push($navdata, $data);
    };
} else {
    $navdata=[];
};
$navbarhtml='<ul><li><a href="/addquestion.php">Add Your Question</a></li>';
if ($conn) {
    for ($i=0;$i<count($navdata);$i++) {
        $navbarhtml.='<li><a href="/?p='.$navdata[$i]["p"].'">'.$navdata[$i]['title'].'</a></li>';
    }
} else {
    $navbarhtml.="<p>Questions could not be reached</p>";
}
$navbarhtml.='</ul>';
$commentsenabled=false;
if (is_null($pageinfo)) {
    $pageinfo=array();
    if (is_null($_GET['p'])||$_GET['p']<0) {
        $pageinfo=['title'=>'Page not found','content'=>'Sorry, page not found.'];
    } else {
        if ($conn) {
            $datafile=$DB->query("SELECT * FROM `Questions` WHERE p=".$_GET['p']." AND posted>0");
            while ($data=$datafile->fetch_array()) {
                array_push($pageinfo, $data);
            };
            if (!isset($pageinfo[0])) {
                $pageinfo=['title'=>'Page not found','content'=>'Sorry, page not found.'];
            } else {
                $commentsenabled=true;
                $pageinfo=$pageinfo[0];
            }
        } else {
            $pageinfo=['title'=>"Cannot reach server",'content'=>'The website is temporarily down but should be back online in under an hour. I apologize for the inconvenience.'];
        }
    }
}
$DB->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="msvalidate.01" content="624074279E4E98ADF89D1DEB5C322E21" />
    <meta name="description" content="A place for curious kids to have their questions answered."/>
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <link async href="https://fonts.googleapis.com/css?family=Inter|Spartan|Lato|Karla|Patrick+Hand&display=swap" rel="stylesheet"/>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
<script defer id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <link defer rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <link defer rel="icon" href="favicon.ico" type="image/x-icon" />
    <title><?php echo $pageinfo['title']?></title>
    <?php
    echo "<script type='text/javascript'>
            function resetnav(){
            document.getElementById('searchnav').display='none';
            document.getElementById('searchnav').innerHTML='';
            }
            </script>";
    ?>
    <script defer type="text/javascript">
    onload=function(){
    document.getElementById('searchinput').oninput=function(){
        document.getElementById('searchnav').style.display="block";
        var xhp = new XMLHttpRequest();
        xhp.onreadystatechange=function(){
            if(this.readyState==4&&this.status==200){
                document.getElementById("searchnav").innerHTML="<ul>"+this.responseText+"</ul>";
                if(this.responseText==''){resetnav();}
            }
        };
        xhp.open("GET","search.php?str="+document.getElementById('searchinput').value, true);
        xhp.send();
            
    }}
    </script>
    <script>
    function setarrows(page){//called on page load for all pages with a p get header (same logic as used to determine if there is voting)
            var realpage=page.toString();
            //these ifs are for when a counter vote needs to be cast because reload caused an illegal vote.
            if(document.getElementById("messagepassed").innerHTML!=''){//if the present page load was preceded by a cast vote. This was set in the $POST['dothissql'] if clause of the start php
                    if(document.getElementById('commentidpasser').innerHTML!=''){
                            page+='c'+document.getElementById('commentidpasser').innerHTML;
                    }
                    if(document.cookie.search('refreshed'+page+'=')!=-1){//if it isn't first time on this page
                            if(document.cookie.charAt(document.cookie.search('refreshed'+page+'=')+10+page.length)!='n'){//set to n when there is a legal vote
                                    document.getElementById("messagepasser").innerHTML="<input name='dothissql' value="+document.getElementById("messagepassed").innerHTML+" /><input name='page' value="+page+" />";
                                    submitmessagepasser(page);
                                    return null;//so it doesn't continue on reload
                            }
                    }
            }
            document.cookie='refreshed'+page+'=y;';
            if(document.cookie.search('vote'+realpage)!=-1){
                    switch(document.cookie.charAt(document.cookie.search('vote'+page+'=')+5+page.length)){
                                    case 'u':
                            document.getElementById('upvote'+page).innerHTML='&#x2b06;';
                            document.getElementById('upvote'+page).setAttribute("onclick","unupvote('"+page+"');");
                            document.getElementById('downvote'+page).innerHTML='&#x21E9;';
                            document.getElementById('downvote'+page).setAttribute("onclick","downvote('"+page+"');");
                            break;
                                    case 'd':
                            document.getElementById('downvote'+page).innerHTML='&#x2b07;';
                            document.getElementById('upvote'+page).innerHTML='&#x21E7;';
                            document.getElementById('downvote'+page).setAttribute("onclick","undownvote('"+page+"');");
                            document.getElementById('upvote'+page).setAttribute("onclick","upvote('"+page+"');");
                            break;
                                    default:
                            document.getElementById('upvote'+page).innerHTML='&#x21E7;';
                            document.getElementById('upvote'+page).setAttribute("onclick","upvote('"+page+"');");
                            document.getElementById('downvote'+page).innerHTML='&#x21E9;';
                            document.getElementById('downvote'+page).setAttribute("onclick","downvote('"+page+"');");
                            break;
                    }
            }else{
            document.getElementById('upvote'+page).innerHTML='&#x21E7;';
                    document.getElementById('upvote'+page).setAttribute("onclick","upvote('"+page+"');");
                    document.getElementById('downvote'+page).innerHTML='&#x21E9;';
                    document.getElementById('downvote'+page).setAttribute("onclick","downvote('"+page+"');");
            }
            var cookies=document.cookie.split(';');
            for(var i=0;i<cookies.length;i++){
                    if(cookies[i].split('=')[0].search('vote'+realpage+'c')!=-1){
                            var pagec=(cookies[i].split('=')[0]).replace('vote','').substring(1);//substring cuz theres a space at the start of string//its not page, its page+'c'+commentid
                            var fake;//////////everyting seems right, but it refuses to recognize the vote.  The reason one vote displays is that is from up top cuz it was just cast
                            
                            if(document.getElementById('upvote'+pagec)){
                            switch(cookies[i].split('=')[1]){
                                    case 'u':
                                            document.getElementById('upvote'+pagec).innerHTML='&#x2b06;';
                                            document.getElementById('upvote'+pagec).setAttribute("onclick","unupvote('"+pagec+"');");
                                            document.getElementById('downvote'+pagec).innerHTML='&#x21E9;';
                                            document.getElementById('downvote'+pagec).setAttribute("onclick","downvote('"+pagec+"');");
                                            break;
                                    case 'd':
                                            document.getElementById('downvote'+pagec).innerHTML='&#x2b07;';
                                            document.getElementById('upvote'+pagec).innerHTML='&#x21E7;';
                                            document.getElementById('downvote'+pagec).setAttribute("onclick","undownvote('"+pagec+"');");
                                            document.getElementById('upvote'+pagec).setAttribute("onclick","upvote('"+pagec+"');");
                                            break;
                                    default:
                                            document.getElementById('upvote'+pagec).innerHTML='&#x21E7;';
                                            document.getElementById('upvote'+pagec).setAttribute("onclick","upvote('"+pagec+"');");
                                            document.getElementById('downvote'+pagec).innerHTML='&#x21E9;';
                                            document.getElementById('downvote'+pagec).setAttribute("onclick","downvote('"+pagec+"');");
                                            break;
                            }
                            }else{
                                alert(fake);
                                alert('upvote'+pagec);
                                alert('bad');}
                    }
            }
    }
    </script>
    <script defer type="text/javascript">
    function submitmessagepasser(page){
            document.cookie='refreshed'+page+'=n;'
            document.getElementById("messagepasser").submit();
    }
    function unupvote(page){
            document.getElementById("messagepasser").innerHTML="<input name='dothissql' value=0 /><input name='page' value="+page+" />";
            document.cookie='vote'+page+'=n;';
            submitmessagepasser(page);
    }
    function undownvote(page){
            document.cookie='vote'+page+'=n;';
            document.getElementById("messagepasser").innerHTML="<input name='dothissql' value=1 /><input name='page' value="+page+" />";
            submitmessagepasser(page);
    }
    function upvote(page){
            document.cookie='vote'+page+'=u;';
            var sql=3;
            if(document.getElementById('downvote'+page).innerHTML=='\u{2b07}'){
                    sql--;
            };
            document.getElementById("messagepasser").innerHTML="<input name='dothissql' value="+sql+" /><input name='page' value="+page+" />";
            submitmessagepasser(page);
    }
    function downvote(page){
            document.cookie='vote'+page+'=d;';
            var sql=5;
            if(document.getElementById('upvote'+page).innerHTML=='\u{2b06}'){
                    sql--;
            };
            document.getElementById("messagepasser").innerHTML="<input name='dothissql' value="+sql+" /><input name='page' value="+page+" />";
            submitmessagepasser(page);
            }
    function hidecommentform(commentid=''){
            document.getElementById("responseform"+commentid).setAttribute("hidden","");
            document.getElementById("addcomment"+commentid).removeAttribute("hidden");
    }
    function submitonclick(commentid=''){
            if(document.getElementById("responseform"+commentid).checkValidity()){//for email field
            hidecommentform(commentid);
            document.getElementById("submitted"+commentid).removeAttribute("hidden");
            document.getElementById("responseform"+commentid).submit(); 
            }
    }
    function addcommentonclick(commentid=''){
            document.getElementById("responseform"+commentid).removeAttribute("hidden");
            document.getElementById("addcomment"+commentid).setAttribute("hidden","");
    }
    </script>
    <script type='text/javascript'>
    function showComment(commenta,page){
var innerhtml='';
var comment=JSON.parse(commenta);
if(comment['id']==undefined){comment['id']="a";};//so it doesnt throw an error for the autocomment
comment['id']=comment['id'].toString();
var div=document.getElementById('commentfooter'+comment['id'].substring(0,comment['id'].length-1)).appendChild(document.createElement("div"));
if(comment['id']=="a"){comment['id']="";};
if(!!(comment['submitted'])&&!!(comment['posted'])){
        innerhtml='<p><i>Submitted '+new Date(comment['submitted']*1000).toLocaleDateString()+" "+new Date(comment['submitted']*1000).toLocaleTimeString()+". Posted "+new Date(comment['posted']*1000).toLocaleDateString()+" "+new Date(comment['posted']*1000).toLocaleTimeString()+".</i></p>";
        document.getElementById("childcounter"+comment['id'].substring(0,comment['id'].length-1)).value++;
}
innerhtml+='<p>'+comment['content']+'</p>';
innerhtml+='<div class="commentform"><form method="post" action="#" hidden id="responseform'+comment['id']+'">';
//no need for +1 cuz first subcomment can get 0 if there are no kids. This will be updated as kids are added...
innerhtml+='<input type="hidden" name="page" readonly value="'+page+'"/><input type="hidden" readonly id="childcounter'+comment['id']+'" name="children" value=0></input>';
if(comment['id'].length>0){
innerhtml+='<input name="id" type="hidden" readonly value='+comment['id']+' />';
}
innerhtml+='<input type="email" size="40" name="email" placeholder="Email for response notifications (Optional,not posted)" id="email'+comment['id']+'"/><br />';
innerhtml+='<textarea id="textarea'+comment['id']+'" rows="3" cols="20" name="commenttext" placeholder="Enter Comment Here..."></textarea><br />';
innerhtml+='<button type="reset" onclick="hidecommentform(\''+comment['id']+'\')">Cancel</button>';
innerhtml+='<button onclick="submitonclick(\''+comment['id']+'\')">Submit</button></form>';//submitonclick() actually submits the form so i can guarantee the other code runs too
innerhtml+='<p hidden id="submitted'+comment['id']+'">Comment Submitted</p>';
innerhtml+='<button id="addcomment'+comment['id']+'" onclick=\"addcommentonclick(\''+comment['id']+'\')\">Add Reply</button>';
innerhtml+='<div class="votesdiv"><span style="cursor:pointer" id="upvote'+page+'c'+comment['id']+'" onclick="upvote(\''+page+'c'+comment['id']+'\')">&#x21E7;</span><span>'+comment['netvotes']+'</span><span style="cursor:pointer" id="downvote'+page+'c'+comment['id']+'" onclick="downvote(\''+page+'c'+comment['id']+'\')">&#x21E9;</span></div></div>';
            div.setAttribute("id","commentfooter"+comment['id']);
            if((comment['content'].search("~Nate")!=-1)||(comment['content'].search("~Will")!=-1)||(comment['content'].search("-Nate")!=-1)||(comment['content'].search("-Will")!=-1)){
                    div.setAttribute("class","imperial");
            }
            div.innerHTML=innerhtml;
    }
    </script>
    <script defer type="text/javascript">
    changesearchstate.focusneeded=true;
    
    function changesearchstate(){
            var elementids=["desktopsearchbutton","searchicon","closeicon","screenp","search","searchinput"];
            for(var i=0;i<elementids.length;i++){
                    document.getElementById(elementids[i]).classList.toggle("searchable");
            }
            if(changesearchstate.focusneeded){
                    document.getElementById('searchinput').focus();
                    changesearchstate.focusneeded=false;
                    document.getElementById('searchnav').style.display="block";
            }else{
                    document.activeElement.blur();
                    resetnav();
                    changesearchstate.focusneeded=true;
            }
    }
    /*    document.getElementById('searchinput').style.display="inline";
        document.getElementById('aside').style.minWidth='100%';
        document.getElementById('hidesearchbutton').style.display="contents";
        document.getElementById('hidesearchbutton').onclick="hidemenu();";
        document.getElementById('showsearchbutton').style.display="none";
    }*/
    function showmenu(){
        document.getElementById('nav').style.display='block';
        document.getElementById('hidemenubutton').style.display='block';
        document.getElementById('showmenubutton').style.display="none";
    }
    function hidemenu(){
        document.getElementById('nav').style.display='none';
        document.getElementById('showmenubutton').style.display="block";
        document.getElementById('hidemenubutton').style.display='none';
        }
    </script>
</head>
<body>
    <form id="messagepasser" action="#" method="post" hidden></form>
    <form id="messagepassed" hidden></form>
    <form id="commentidpasser" hidden></form>
    <div id="flexgroup">
            <aside id="aside">
                <div id="search">
                        <button onclick="changesearchstate();" class='searchbutton' id='desktopsearchbutton'><p id="searchicon">&#x2315;</p><p id="closeicon" >×</p><p id="screenp"> </p></button> 
                        <!--<button class='searchbutton' id='fakeshowsearchbutton' onclick="showmenu()"><p id="searchicon">&#x2315;</p></button>
                        <button class='searchbutton' id='showsearchbutton' onclick="
                        document.getElementById('search').style.minWidth='100%';
                        document.getElementById('search').style.maxHeight='min-content';
                        document.getElementById('searchinput').removeAttribute('hidden');
                        document.getElementById('hidesearchbutton').removeAttribute('hidden');
                        document.getElementById('showsearchbutton').setAttribute('hidden','');">&#x2315;</button>
                        <button hidden class='searchbutton' id='hidesearchbutton' onclick="
                        document.getElementById('searchinput').setAttribute('hidden','');
                        resetnav();
                        document.getElementById('hidesearchbutton').setAttribute('hidden','');
                        document.getElementById('showsearchbutton').removeAttribute('hidden');">×</button>-->
                        <input id='searchinput'/>
                </div>
                <nav id="searchnav"></nav>
                <button class="menubutton" id="showmenubutton" onclick="showmenu();">≡</button>
                <button hidden class="menubutton" id="hidemenubutton" onclick="hidemenu();">×</button>
                <nav id="nav"><?php echo $navbarhtml;?></nav>
            </aside>
            <div id="notaside">
                    <header>
                         <h1><?php echo '<a href="/">Simple Answers</a>';?></h1>
                    </header>
                    <main>
                        <div id="titleandvotesdiv">
                        <h2><?php echo $pageinfo['title']?></h2>
                        <?php if (isset($pageinfo['p'])) {
        echo "<div class='votesdiv' id='votesdiv'><span style='cursor:pointer' id='upvote".$pageinfo['p']."' onclick='upvote(\'".$pageinfo['p']."\')'>&#x21E7;</span><span>".$pageinfo['netvotes']."</span><span style='cursor:pointer' id='downvote".$pageinfo['p']."' onclick='downvote(\"".$pageinfo['p']."\")'>&#x21E9;</span></div>";
    }?>
                        </div>
                        <?php if (isset($pageinfo['submitted'])) {
        date_default_timezone_set("America/New_York");
        echo "<h3>Submitted ".date('l, M d Y g:i:s A T', $pageinfo['submitted']).".<br />";
    }?><?php if (isset($pageinfo['posted'])) {
        echo "Posted ".date('l, M d Y g:i:s A T', $pageinfo['posted']).".";
        echo "</h3>";
    }?>
                        <p style="white-space:pre-wrap"><?php echo $pageinfo['content']?></p>
                    </main>
<?php if ($commentsenabled) {
        echo '<footer id="commentfooter">
<script type="text/javascript">
showComment(\'{"content": "Feel free to leave a comment or a follow-up question below.", "id": "" }\','.$pageinfo['p'].');';
        if (isset($pageinfo['comments'])) {
            $rawcomments=json_decode($pageinfo['comments'], true);//json of json objects, each represents a comment, with a content, id, posted, submitted
            if (isset($rawcomments)) {
                $comments=array();
                foreach ($rawcomments as $rawcomment) {
                    if ($rawcomment['posted']>0) {
                        array_push($comments, $rawcomment);
                    }
                }
                function sortingbyvotes($a, $b)
                {
                    if (!isset($a['netvotes'])||!isset($b['netvotes'])||$a['netvotes']==$b['netvotes']) {
                        return 0;
                    } else {
                        return ($b['netvotes']>$a['netvotes'])?1:-1;
                    }
                }
                usort($comments, 'sortingbyvotes');
                $commentsdisplayed=0;
                $notyetdisplayed=array();//temporarily holds until end of while loop when it replaces comments
                $commentcount=count($comments);
                $depthlevel=1;//the length of id is the depth level because of the way it is.1 char is top level
                while ($commentsdisplayed<$commentcount) {
                    foreach ($comments as $comment) {
                        if (strlen($comment['id'])==$depthlevel) {
                            echo 'showComment(\''.json_encode($comment).'\',\''.$pageinfo['p'].'\');';
                            $commentsdisplayed++;
                        } else {
                            array_push($notyetdisplayed, $comment);
                        }
                    }
                    $depthlevel++;
                    $comments=$notyetdisplayed;
                    $notyetdisplayed=array();
                }
            } else {
                $additionalinfo.='<p>Error loading comments.Please report this error to me using this <a target="_blank" href="mailto:whitenat@students.holliston.k12.ma.us?subject=ERRORLOADINGCOMMENTS&body=pageis'.urlencode(var_export($pageinfo['p'], true)).'pageinfocommentsis'.urlencode(var_export($pageinfo['comments'], true)).'">link</a> so I can work to fix this immediately.</p>';
            }
        }
        echo 'document.getElementById("addcomment").innerHTML="Add Comment";</script></footer>';
    }
?>
                     <footer id="contact">
                             <div>
                                     <a target="_blank" href="mailto:whitenat@students.holliston.k12.ma.us">Contact</a>
                             </div>
                     </footer>
             </div>
     </div>
     <?php echo $additionalinfo;
     if (isset($pageinfo['p'])) {
         echo '<script type="text/javascript">setarrows("'.$pageinfo['p'].'");</script>';
     };
     ?>
</body>
</html>


