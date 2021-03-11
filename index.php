<?php if (!array_key_exists('p', $_GET)) {
    $contentstring="Put your text here yo";
    $pageinfo=["page"=>"home","title"=>"College Questions","content"=>$contentstring];
}
include "init.php";
