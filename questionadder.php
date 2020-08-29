<?php
        include 'verifier.php';
        if(isset($_POST['content'])&&isset($_POST['title'])){
                $received=$database->query('SELECT * FROM `Questions` WHERE `content`='.$_POST['content']));
                $received=$received->fetch_array();
                if(isset($received[0])){$additionalinfo="<script>alert('Your submission was not posted because it is a duplicate of an already posted question);</script>";}
                else{
                        $backup=fopen('backup.txt',"a");
                        $time=time();
                        $i=0;
                        fwrite($backup,'¦p¬«content»'.$_POST['content']).'«submitted»'.$time.'«title»'.$_POST['title']).'«email»'.$_POST['email']),200);
                        $query='INSERT INTO Questions(`p`,`content`,`submitted`,`posted`,`title`,`email`) VALUES (-1,'.$_POST['content']).','.$time.',-1,'.$_POST['title']);
                        if(null!=$_POST['email']&&strlen($_POST['email'])>4){$query.=','.$_POST['email']);}
                        else{$query.=",NULL";}
                        $query.=')';
                        $database->query($query);
                        sleep(1.5);
                        $errormessage=null;
                        $received=$database->query('SELECT * FROM `Questions` WHERE `submitted`='.$time/*.' AND `content`='.$_POST['content']).' AND `posted`=-1 AND `title`='. $_POST['title'])*/);
                        $received=$received->fetch_array();
                        if(isset($received[0])){
                               $pageinfo=['title'=>"Thank you for your submission",'content'=>"Thank you for your submission. The story will be posted pending review."]; 
                        }else{
                                $pageinfo=['title'=>"Error receiving submission",'content'=>'Your submission was not received due to an error on my part.  Please report this error to me using this <a href="mailto:whitenat@students.holliston.k12.ma.us?subject=ERRORCONTENTNOTRECEIVED&body='];
                                if(null!=$_POST['title'], 'n')){$pageinfo['content'].='title is '.$_POST['title'], 'n');}
                                if(null!=$_POST['content'], 'n')){$pageinfo['content'].=' and content is '.$_POST['content'], 'n');}
                                if(null!=$_POST['email'], 'n')){$pageinfo['content'].=' and emails is '.$_POST['email'], 'n');}
                                $pageinfo['content'].='">link</a> so I can work to fix this immediately.';
                        }
                }
                $database->close();
                foreach($_POST as $key=>$value){//so it doesnt go into comment adder script
                        $_POST[$key]=null;
                }
        }else{
                $pageinfo=['title'=>'Intentions Unclear','content'=>'Looking to submit a question?  Click <a href="addquestion.php">here</a>!<br />Just submitted a question? Your submission was not received due to an error on my part.  Please report this error to me using this <a href="mailto:whitenat@students.holliston.k12.ma.us?subject=ERRORSUBMITTINGSTORYNOHTTPHEADERS&body=(`content`,`submitted`,`posted`,`title`) VALUES ('];
                if(null!=$_POST['title'], 'n')){$pageinfo['content'].=$_POST['title'], 'n');}
                if(null!=$_POST['content'], 'n')){$pageinfo['content'].=$_POST['content'],'n');}
                $pageinfo['content'].=')">link</a> so I can work to fix this immediately.';
        }
        include "init.php";
?>


