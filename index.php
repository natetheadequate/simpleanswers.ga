<?php
if (!array_key_exists('p', $_GET)) {
    $contentstring="&#9;Kids are curious about the world around them and have lots of questions. However, in many cases, the answers to their questions may seem like they can only be understood by older audiences, standing in the way of their curiosity. We believe that with explanations at the right level, without any assumed knowledge beyond basic arithmetic, kids can understand the answers to their questions and remain curious about the world around them.<br />&#9; The arrows to the right of the title of the question will increase or decrease its positioning on the sidebar.";
    $pageinfo=["page"=>"home","title"=>"Welcome to Simple Answers!","content"=>$contentstring];
}
require 'init.php';
