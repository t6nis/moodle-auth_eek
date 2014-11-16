<?php 
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
/* Generate users */

function generate_users($count = 1, $rand = true, $group = false) {
    
    $users = array();    
    $gi = 1;

    for ($i = 1; $i <= $count; $i++) {
        $gi = ceil($i / 10);
        $user = array();        
        $user['idnumber'] = ($rand == false ? $i : rand(0,1000));
        $user['type'] = 'manual';        
        $user['username'] = 'bot'.($rand == false ? $i : rand(0,1000));
        $user['password'] = 'gsdfgsdfg';
        $user['firstname'] = $user['username'];
        $user['lastname'] = $user['username'];
        $user['email'] = $user['username'].'@botnet.botz123';        
        $user['country'] = 'EE';
        $user['city'] = 'Tartu';
        $user['deleted'] = 0;
        $user['policyagreed'] = 1;
        $user['confirmed'] = 1;
        $user['timecreated'] = time();
        $user['group'] = $gi;        
        $users[$i] = $user; 
    }
    
    return $users;
}
/* Table of users */
function display_table($users = array()) {
    
    $table = '<table cellpadding="1" cellspacing="1" border="1">';
    $table .= '<tr><td>IDNUMBER</td><td>Type</td><td>Username</td><td>Password</td><td>Firstname</td><td>Lastname</td><td>E-mail</td>'
            . '<td>Country</td><td>City</td><td>Deleted</td><td>Policyagreed?</td><td>Confirmed?</td><td>Timecreated</td><td>Group</td></tr>';
    foreach ($users as $user) {
        $table .= '<tr>';        
        foreach ($user as $key => $value) {
            $table .= '<td>'.$value.'</td>';
        }
        $table .= '</tr>';
    }
    $table .= '</table>';

    $table .= '<form method="post" action="">';
    $table .= 'CourseShortname:<input type="text" name="courseid" value="">';
    //$table .= '<input type="hidden" name="members" value="'.htmlentities($users).'">';
    $table .= '<input type="submit" name="synccoursemembers" value="Send to Course">';
    $table .= '</form>';
    
    return $table;
}
/* TEST ZE API */
function api_actions($action, $vars) {
    $eekauth = get_auth_plugin('eek');    
    $actions = array('synccoursemembers', 'getoutcome', 'getoutcomes', 'ssologin');
    $result = '';
    if (in_array($action, $actions)) {
        switch($action) {
            case 'synccoursemembers':                
                $eekauth->syncgroups($vars['courseid'], array(1 => 'grupp 1', 2 => 'grupp 2'));
                for ($i = 1; $i < 3; $i++) {
                    $members = generate_users(5, true);
                    $result = $eekauth->synccoursemembers($vars['courseid'], $members, $i, true);
                }
                /*
                $members = generate_users(5, true);
                print_r($members);
                $result = $eekauth->synccoursemembers($vars['courseid'], $members, '', true);
                */
                break;
            case 'getoutcome':
                $result = $eekauth->getoutcome($vars['courseid'], $vars['useridnumber']);
                break;
            case 'getoutcomes':
                $result = $eekauth->getoutcomes($vars['courseid']);
                break;
            case 'ssologin':
                $result = $eekauth->ssologin($vars['username']);
                break;
        }        
    }
    return $result;
}

/* Draw action button */
function action_button($action) {
    $actions = array('synccoursemembers', 'getoutcome', 'getoutcomes');
    if (in_array($action, $actions)) {
        
    }
}

if (isset($_POST['usercount']) || isset($_POST['random'])) {
    $users = generate_users($_POST['usercount'], false);
} else {
    $users = generate_users(); 
}

/* POST AREA */
print_r($_POST);

print_r('<br />');
if (isset($_POST['get_grade'])) {
    $vars = $_POST;
    $result = api_actions('getoutcome', $vars);
    print_r($result);
} else if (isset($_POST['get_grades'])) {
    $vars = $_POST;
    $result = api_actions('getoutcomes', $vars);
    print_r($result);
} else if (isset($_POST['synccoursemembers'])) {
    $vars = $_POST;
    $result = api_actions('synccoursemembers', $vars);
    print_r($result);
} else if (isset($_POST['ssologin'])) {
    $vars = $_POST;
    /*
    $url = 'http://178.62.252.7/moodle27/login/index.php';
    $agent = $_SERVER["HTTP_USER_AGENT"];
    $username = 'user';
    $ckfile = tempnam ("/tmp", "CURLMOODCOOKIE");


    //set POST variables
    $fields_string = 'username='.$username.'&password=test';
    rtrim($fields_string, '&');
    echo $fields_string.'
    ';

    //open connection
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch,CURLOPT_COOKIEJAR, $ckfile);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true );
    curl_setopt($ch,CURLOPT_AUTOREFERER, true );

    //execute post
    $content = curl_exec($ch);
    $response = curl_getinfo( $ch );
    //close connection
    curl_close($ch);
    echo 'response=';
    print_r($response);
    echo 'content='.print_r($_COOKIE);
    setcookie('MDLUSERNAME', 'kala2', 0, '/');
    print_r($_COOKIE);
     * */
}
?>
<html>
    <head>
        <title>Testing EEK sync page</title>
    </head>
    <body>
        Testing EEK Functionalities<br />
        <div class="course_sync">
        Course sync<br />
        <form method="post" action="">
            <input type="text" name="usercount" value="1" size="10">
            <input type="checkbox" name="random" value="true">
            <input type="text" name="groups" value="0" size="5">
            <input type="submit" name="submit" value="Submit">
        </form>
        <?php 
            print display_table($users);
        ?>
        </div>
        <br />
        <div class="course_grade">
            Get course grade for user<br />
            <form method="post" action="">
                <input type="text" name="useridnumber" value="" size="10">
                <input type="text" name="courseid" value="" size="10">
                <input type="submit" name="get_grade" value="Submit">
            </form>
        </div>
        <br />
        <div class="course_grades">
            Get all course grades by course shortname<br />
            <form method="post" action="">
                <input type="text" name="courseid" value="" size="10">
                <input type="submit" name="get_grades" value="Submit">
            </form>
        </div>
        <div class="ssotest">
            Simulate SSO<br />
            <form method="post" action="">
                <input type="text" name="username" value="" size="10">
                <input type="submit" name="ssologin" value="Submit">
            </form>
            gsdfgsdfg<br />
            <a href="http://178.62.252.7/moodle27/login/index.php?auth=eek&username=bot213" target="_blank">GoTo MOodle</a><br />
            <a href="http://178.62.252.7/moodle27/login/index.php?auth=eek&username=bot213&courseid=2" target="_blank">GoTo Course</a>
        </div>
    </body>
</html>