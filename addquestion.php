<?php
$additionalinfo='<script type="text/javascript">
var elements=document.getElementById("storyadder").elements;
var notsubmitted=true;
for(var i=0;i<elements.length;i++){
        if(!!elements[i].name){
                if(document.cookie.search(elements[i].name)>-1){
                        var regex= new RegExp("(?:(?:^|.*;\\\s*)"+elements[i].name+"\\\s*\\\=\\\s*([^;]*).*$)|^.*$");
                        elements[i].value=document.cookie.replace(regex, "$1");
                }
        }
}
document.getElementsByTagName("body")[0].onbeforeunload=function(){
if(notsubmitted){
var elements=document.getElementById("storyadder").elements;
for(var i=0;i<elements.length;i++){
        if(!!elements[i].name){
               document.cookie=elements[i].name+"="+elements[i].value+";";
        }
}
return undefined;
}};
function clearcookiesandsubmit(){
        if(document.getElementById("storyadder").checkValidity()){
        notsubmitted=false;
        var elements=document.getElementById("storyadder").elements;
for(var i=0;i<elements.length;i++){
        if(!!elements[i].name){
               document.cookie=elements[i].name+"=";
        }
}
        document.getElementById("storyadder").submit();
        }
}
</script>';
$pageinfo=['page'=>'addstory','title'=>"Add Your Question",'content'=>"<form id='storyadder' action='/questionadder.php' method='post'><label> Title: <input size='30' name='title' required='required' maxlength='63'/></label><br /><label>Email (optional, for if you want notifications if someone responds): <input type='email' name='email' /></label><br /><label>Question:<br /><textarea required='required'  rows='18' style='overflow:scroll;' name='content'></textarea></label><br /><button type='reset'>Clear</button><button onclick='clearcookiesandsubmit();'>Submit</button></form>"];
include "init.php";
?>
