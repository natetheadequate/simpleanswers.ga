<?php
/**
 * Template for generating all pages of the website. Included at the end of index.php after $pageinfo for the homepage is defined.
 */
require 'dbcredentials.php'; //defines $CREDENTIALS
$database = new mysqli($CREDENTIALS[0], $CREDENTIALS[1], $CREDENTIALS[2], $CREDENTIALS[3], $CREDENTIALS[4]);
$MYSQL_RETRIEVAL_ERROR = $database->error;
if ($MYSQL_RETRIEVAL_ERROR) {
    mail("natewhite345@gmail.com", "MYSQL_RETRIEVAL_ERROR-init.php", $MYSQL_RETRIEVAL_ERROR);
    if (!isset($pageinfo)) {
        $pageinfo = ["title" => "Cannot retrieve stories", "content" => "The website is temporarily down due to an internal error."];
    }
    $navbarhtml = '<p>Questions could not be reached.</p>';
    $additionalinfo = '<script type="text/javascript">console.error(' . $MYSQL_RETRIEVAL_ERROR . ')</script>';
} else {
    $datafile = $database->query("SELECT `p`,`title` FROM `Questions` WHERE `p`>-1 AND `posted`>0 ORDER BY netvotes DESC, submitted DESC");
    $navdata = array();
    while ($data = $datafile->fetch_array()) {
        array_push($navdata, $data);
    }
    ;
    $navbarhtml = '<ul><li><a href="/addquestion.php">Add Your Question</a></li>';
    for ($i = 0; $i < count($navdata); $i++) {
        $navbarhtml .= '<li><a href="?p=' . $navdata[$i]["p"] . '">' . $navdata[$i]['title'] . '</a></li>';
    }
    $navbarhtml .= '</ul>';
}
$COMMENTS_ENABLED = false;
if (is_null($pageinfo)) {
    if (is_int($_GET)) {
        $datafile = $database->query("SELECT * FROM `Questions` WHERE p=" . $_GET['p'] . " AND posted>0");
        while ($data = $datafile->fetch_array()) {
            array_push($pageinfo, $data);
        }
        if (isset($pageinfo[0])) {
            $commentsenabled = true;
            $pageinfo = $pageinfo[0];
        }
    }
}
if (is_null($pageinfo)) {
    $pageinfo = ['title' => 'Page not found', 'content' => 'Sorry, page not found.'];
}
$database->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="msvalidate.01" content="624074279E4E98ADF89D1DEB5C322E21" />
        <meta name="description" content="A place for curious kids to have their questions answered." />
        <link rel="stylesheet" type="text/css" href="styles.css" />
        <link async href="https://fonts.googleapis.com/css?family=Inter|Spartan|Lato|Karla|Patrick+Hand&display=swap" rel="stylesheet" />
        <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
        <script defer id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
        <link defer rel="shortcut icon" href="http://simpleanswers.dx.am/favicon.ico" type="image/x-icon" />
        <link defer rel="icon" href="http://simpleanswers.dx.am/favicon.ico" type="image/x-icon" />
        <title><?php echo $pageinfo['title'] ?></title>

        <script type='text/javascript'>
                function showComment(commenta, page) {
                        var innerhtml = '';
                        var comment = JSON.parse(commenta);
                        if (comment['id'] == undefined) {
                                comment['id'] = "a";
                        }; //so it doesnt throw an error for the autocomment
                        comment['id'] = comment['id'].toString();
                        var div = document.getElementById('commentfooter' + comment['id'].substring(0, comment['id'].length - 1))
                                .appendChild(document.createElement("div"));
                        if (comment['id'] == "a") {
                                comment['id'] = "";
                        };
                        if (!!(comment['submitted']) && !!(comment['posted'])) {
                                innerhtml = '<p><i>Submitted ' + new Date(comment['submitted'] * 1000).toLocaleDateString() + " " +
                                        new Date(comment['submitted'] * 1000).toLocaleTimeString() + ". Posted " + new Date(comment['posted'] *
                                                1000).toLocaleDateString() + " " + new Date(comment['posted'] * 1000).toLocaleTimeString() +
                                        ".</i></p>";
                                document.getElementById("childcounter" + comment['id'].substring(0, comment['id'].length - 1)).value++;
                        }
                        innerhtml += '<p>' + comment['content'] + '</p>';
                        innerhtml += '<div class="commentform"><form method="post" action="#" hidden id="responseform' + comment['id'] +
                                '">';
                        //no need for +1 cuz first subcomment can get 0 if there are no kids. This will be updated as kids are added...
                        innerhtml += '<input type="hidden" name="page" readonly value="' + page +
                                '"/><input type="hidden" readonly id="childcounter' + comment['id'] + '" name="children" value=0></input>';
                        if (comment['id'].length > 0) {
                                innerhtml += '<input name="id" type="hidden" readonly value=' + comment['id'] + ' />';
                        }
                        innerhtml +=
                                '<input type="email" size="40" name="email" placeholder="Email for response notifications (Optional,not posted)" id="email' +
                                comment['id'] + '"/><br />';
                        innerhtml += '<textarea id="textarea' + comment['id'] +
                                '" rows="3" cols="20" name="commenttext" placeholder="Enter Comment Here..."></textarea><br />';
                        innerhtml += '<button type="reset" onclick="hidecommentform(\'' + comment['id'] + '\')">Cancel</button>';
                        innerhtml += '<button onclick="submitonclick(\'' + comment['id'] +
                                '\')">Submit</button></form>'; //submitonclick() actually submits the form so i can guarantee the other code runs too
                        innerhtml += '<p hidden id="submitted' + comment['id'] + '">Comment Submitted</p>';
                        innerhtml += '<button id="addcomment' + comment['id'] + '" onclick=\"addcommentonclick(\'' + comment['id'] +
                                '\')\">Add Reply</button>';
                        innerhtml += '<div class="votesdiv"><span style="cursor:pointer" id="upvote' + page + 'c' + comment['id'] +
                                '" onclick="upvote(\'' + page + 'c' + comment['id'] + '\')">&#x21E7;</span><span>' + comment['netvotes'] +
                                '</span><span style="cursor:pointer" id="downvote' + page + 'c' + comment['id'] + '" onclick="downvote(\'' +
                                page + 'c' + comment['id'] + '\')">&#x21E9;</span></div></div>';
                        div.setAttribute("id", "commentfooter" + comment['id']);
                        if ((comment['content'].search("~Nate") != -1) || (comment['content'].search("~Will") != -1) || (comment[
                                        'content'].search("-Nate") != -1) || (comment['content'].search("-Will") != -1)) {
                                div.setAttribute("class", "imperial");
                        }
                        div.innerHTML = innerhtml;
                }

                function setpagearrows(page){
                        if (document.cookie.search('vote' + realpage) != -1) {
                                switch (document.cookie.charAt(document.cookie.search('vote' + page + '=') + 5 + page.length)) {
                                        case 'u':
                                                document.getElementById('upvote' + page).innerHTML = '&#x2b06;';
                                                document.getElementById('upvote' + page).setAttribute("onclick", "unupvote('" + page + "');");
                                                document.getElementById('downvote' + page).innerHTML = '&#x21E9;';
                                                document.getElementById('downvote' + page).setAttribute("onclick", "downvote('" + page + "');");
                                                return;
                                        case 'd':
                                                document.getElementById('downvote' + page).innerHTML = '&#x2b07;';
                                                document.getElementById('upvote' + page).innerHTML = '&#x21E7;';
                                                document.getElementById('downvote' + page).setAttribute("onclick", "undownvote('" + page + "');");
                                                document.getElementById('upvote' + page).setAttribute("onclick", "upvote('" + page + "');");
                                                return;
                                }
                        }
                        document.getElementById('upvote' + page).innerHTML = '&#x21E7;';
                        document.getElementById('upvote' + page).setAttribute("onclick", "upvote('" + page + "');");
                        document.getElementById('downvote' + page).innerHTML = '&#x21E9;';
                        document.getElementById('downvote' + page).setAttribute("onclick", "downvote('" + page + "');");
                }
                function setcommentarrows(page){
                        var cookies = document.cookie.split(';');
                        for (var i = 0; i < cookies.length; i++) {
                                if (cookies[i].split('=')[0].search('vote' + page + 'c') != -1) {
                                        var pagec = (cookies[i].split('=')[0]).replace('vote', '').substring(
                                                1); //substring cuz theres a space at the start of string//its not page, its page+'c'+commentid
                                        var fake; //////////everyting seems right, but it refuses to recognize the vote.  The reason one vote displays is that is from up top cuz it was just cast

                                        if (document.getElementById('upvote' + pagec)) {
                                                switch (cookies[i].split('=')[1]) {
                                                        case 'u':
                                                                document.getElementById('upvote' + pagec).innerHTML = '&#x2b06;';
                                                                document.getElementById('upvote' + pagec).setAttribute("onclick", "unupvote('" + pagec +
                                                                        "');");
                                                                document.getElementById('downvote' + pagec).innerHTML = '&#x21E9;';
                                                                document.getElementById('downvote' + pagec).setAttribute("onclick", "downvote('" + pagec +
                                                                        "');");
                                                                break;
                                                        case 'd':
                                                                document.getElementById('downvote' + pagec).innerHTML = '&#x2b07;';
                                                                document.getElementById('upvote' + pagec).innerHTML = '&#x21E7;';
                                                                document.getElementById('downvote' + pagec).setAttribute("onclick", "undownvote('" + pagec +
                                                                        "');");
                                                                document.getElementById('upvote' + pagec).setAttribute("onclick", "upvote('" + pagec +
                                                                        "');");
                                                                break;
                                                        default:
                                                                document.getElementById('upvote' + pagec).innerHTML = '&#x21E7;';
                                                                document.getElementById('upvote' + pagec).setAttribute("onclick", "upvote('" + pagec +
                                                                        "');");
                                                                document.getElementById('downvote' + pagec).innerHTML = '&#x21E9;';
                                                                document.getElementById('downvote' + pagec).setAttribute("onclick", "downvote('" + pagec +
                                                                        "');");
                                                                break;
                                                }
                                        } else {
                                                alert(fake);
                                                alert('upvote' + pagec);
                                                alert('bad');
                                        }
                                }
                        }
                }
                function setarrows(page){
                        setpagearrows(page);
                        setcommentarrows(page);
                }
        </script>
        <script defer type="text/javascript">
                function resetnav() { //like the search never even happened
                        document.getElementById('searchnav').display = 'none';
                        document.getElementById('searchnav').innerHTML = '';
                }

                function searchDB() {
                        var xhp = new XMLHttpRequest();
                        xhp.onreadystatechange = function() {
                                if (this.readyState == 4 && this.status == 200) {
                                        document.getElementById("searchnav").innerHTML = "<ul>" + this.responseText + "</ul>";
                                        if (this.responseText == '') {
                                                resetnav();
                                        }
                                }
                        };
                        xhp.open("GET", "search.php?str=" + document.getElementById('searchinput').value, true);
                        xhp.send();
                }
                function vote(type,page){
                        var xhp = new XMLHttpRequest();
                        xhp.onreadystatechange = function() {
                                if (this.readyState == 4 && this.status == 200) {
                                        switch(type){
                                                case upvote:
                                                        alert("upvoted");
                                                        break;
                                                case downvote:
                                                        alert("downvoted");
                                                        break;
                                                case unupvote:
                                                        alert("unupvoted");
                                                        break;
                                                case undownvote:
                                                        alert("undownvoted");
                                                        break;
                                        }
                                }
                        };
                        xhp.open("GET", "votehandler.php?type=" + type + "&page="+page);
                        xhp.send();
                }
                function hidecommentform(commentid = '') {
                        document.getElementById("responseform" + commentid).setAttribute("hidden", "");
                        document.getElementById("addcomment" + commentid).removeAttribute("hidden");
                }

                function submitonclick(commentid = '') {
                        if (document.getElementById("responseform" + commentid).checkValidity()) { //for email field
                                hidecommentform(commentid);
                                document.getElementById("submitted" + commentid).removeAttribute("hidden");
                                document.getElementById("responseform" + commentid).submit();
                        }
                }

                function addcommentonclick(commentid = '') {
                        document.getElementById("responseform" + commentid).removeAttribute("hidden");
                        document.getElementById("addcomment" + commentid).setAttribute("hidden", "");
                }

                //changesearchstate.focusneeded = true;

                function changesearchstate() {
                        var elementids = ["desktopsearchbutton", "searchicon", "closeicon", "screenp", "search", "searchinput"];
                        for (var i = 0; i < elementids.length; i++) {
                                document.getElementById(elementids[i]).classList.toggle("searchable");
                        }
                        if (changesearchstate.focusneeded) {
                                document.getElementById('searchinput').focus();
                                changesearchstate.focusneeded = false;
                                document.getElementById('searchnav').style.display = "block";
                        } else {
                                document.activeElement.blur();
                                resetnav();
                                changesearchstate.focusneeded = true;
                        }
                }
        </script>
</head>

<body>
        <div id="flexgroup">
                <aside id="aside">
                        <div id="search">
                                <button class='searchbutton' id='desktopsearchbutton'>
                                        <p id="searchicon">&#x2315;</p>
                                        <p id="closeicon">×</p>
                                        <p id="screenp"> </p>
                                </button>
                                <input id='searchinput' />
                        </div>
                        <nav id="searchnav"></nav>
                        <button class="menubutton" id="showmenubutton">≡</button>
                        <button hidden class="menubutton" id="hidemenubutton" >×</button>
                        <nav id="nav"><?php echo $navbarhtml; ?></nav>
                </aside>
                <div id="notaside">
                        <header>
                                <h1><?php echo '<a href="simpleanswers.ga">Simple Answers</a>'; ?></h1>
                        </header>
                        <main>
                                <div id="titleandvotesdiv">
                                        <h2><?php echo $pageinfo['title'] ?></h2>
                                        <?php
                                        if (isset($pageinfo['p'])) {
                                            echo "<div class='votesdiv' id='votesdiv'>";
                                            echo "<span style='cursor:pointer' id='upvote" . $pageinfo['p'] . "'></span>";
                                            echo "<span>" . $pageinfo['netvotes'] . "</span>";
                                            echo "<span style='cursor:pointer' id='downvote" . $pageinfo['p'] . "'></span></div>";
                                        } ?>
                                </div>
                                <?php
                                if (isset($pageinfo['submitted'])) {
                                    date_default_timezone_set("America/New_York");
                                    echo "<h3>Submitted " . date('l, M d Y g:i:s A T', $pageinfo['submitted']) . ".<br />";
                                } ?>
                                <?php
                                if (isset($pageinfo['posted'])) {
                                    echo "Posted " . date('l, M d Y g:i:s A T', $pageinfo['posted']) . ".";
                                    echo "</h3>";
                                } ?>
                                <p style="white-space:pre-wrap">
                                        <?php echo $pageinfo['content'] ?>
                                </p>
                        </main>
                        <?php
                        if ($commentsenabled) {
                            echo '<footer id="commentfooter">';
                            echo '<script type="text/javascript">';
                            echo 'showComment(\'{"content": "Feel free to leave a comment or a follow-up question below.", "id": "" }\',' . $pageinfo['p'] . ');';
                            if (isset($pageinfo['comments'])) {
                                $rawcomments = json_decode($pageinfo['comments'], true); //json of json objects, each represents a comment, with a content, id, posted, submitted
                                if (isset($rawcomments)) {
                                    $comments = array();
                                    foreach ($rawcomments as $rawcomment) {
                                        if ($rawcomment['posted'] > 0) {
                                            array_push($comments, $rawcomment);
                                        }
                                    }
                                    function sortingbyvotes($a, $b)
                                    {
                                        if (!isset($a['netvotes']) || !isset($b['netvotes']) || $a['netvotes'] == $b['netvotes']) {
                                            return 0;
                                        } else {
                                            return ($b['netvotes'] > $a['netvotes']) ? 1 : -1;
                                        }
                                    }
                                    usort($comments, 'sortingbyvotes');
                                    $commentsdisplayed = 0;
                                    $notyetdisplayed = array(); //temporarily holds until end of while loop when it replaces comments
                                    $commentcount = count($comments);
                                    $depthlevel = 1; //the length of id is the depth level because of the way it is.1 char is top level
                                    while ($commentsdisplayed < $commentcount) {
                                        foreach ($comments as $comment) {
                                            if (strlen($comment['id']) == $depthlevel) {
                                                echo 'showComment(\'' . json_encode($comment) . '\',\'' . $pageinfo['p'] . '\');';
                                                $commentsdisplayed++;
                                            } else {
                                                array_push($notyetdisplayed, $comment);
                                            }
                                        }
                                        $depthlevel++;
                                        $comments = $notyetdisplayed;
                                        $notyetdisplayed = array();
                                    }
                                } else {
                                    mail("natewhite345@gmail.com", "ERROR_DECODING_JSON_FOR_COMMENTS-init.php", "bodyidc");
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
        <?php /*
        echo $additionalinfo;
        if (isset($pageinfo['p'])) {
            echo '<script type="text/javascript">setarrows("' . $pageinfo['p'] . '");</script>';
        };*/
        ?>
</body>

</html>