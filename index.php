<?php if (!array_key_exists('p', $_GET)) {
    $contentstring="&#9;In many cases, curiosity about science, math, school and more is stopped if the answer requires too much prerequisite knowledge. This is especially true for elementary and middle schoolers, who also have the most natural curiosity. We believe that with explanations at the right level, without any assumed knowledge beyond basic arithmetic, kids can understand the answers to their questions and remain curious about the world around them.<br />&#9; The arrows to the right of the title of the question will increase or decrease its positioning on the sidebar.";
    $pageinfo=["page"=>"home","title"=>"Welcome to Simple Answers!","content"=>$contentstring];
}
include "init.php";
