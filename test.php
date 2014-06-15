<?php 
/* Generate users */
function generate_users($count = 1, $rand = true) {
    
    $users = array(); 
        
    for ($i = 0; $i < $count; $i++) {
        $user = new stdClass();
        $user->username = 'bot'.($rand == false ? $i : rand(0,1000));
        $user->password = 'changeme';
        $user->firstname = $user->username;
        $user->lastname = $user->username;
        $user->email = $user->username.'@botnet.botz123';
        $user->idnumber = ($rand == false ? $i : rand(0,1000));
        $user->country = 'Estonia';
        $user->city = 'Tartu';
        $user->policyagreed = 1;
        $user->confirmed = 1;
        $user->timecreated = time();
        $users[$i] = $user; 
    }
    
    return $users;
}
/* Table of users */
function display_table($users = array()) {
    
    $table = '<table cellpadding="1" cellspacing="1" border="1">';
    $table .= '<tr><td>Username</td><td>Password</td><td>Firstname</td><td>Lastname</td><td>E-mail</td>'
            . '<td>IDNUMBER</td><td>Country</td><td>City</td><td>Policyagreed?</td><td>Confirmed?</td><td>Timecreated</td></tr>';
    foreach ($users as $user) {
        $table .= '<tr>';        
        foreach ($user as $key => $value) {
            $table .= '<td>'.$value.'</td>';
        }
        $table .= '</tr>';
    }
    $table .= '</table>';
    
    $table .= '<form method="post" action="">';
    $table .= 'CourseID:<input type="text" name="courseid" value="">';
    $table .= '<input type="hidden" name="members" value="'.$users.'">';
    $table .= '<input type="submit" name="course" value="Send to Course">';
    $table .= '</form>';
    
    return $table;
}
/* TEST ZE API */
function api_actions($action, $vars) {
    $eekauth = get_auth_plugin('eek');
    $actions = array('synccoursemembers', 'getoutcome', 'getoutcomes');
    $result = '';
    if (in_array($action, $actions)) {
        switch($action) {
            case 'synccoursemembers':
                $result = $eekauth->synccoursemembers($vars['courseid'], $vars['members'], $vars['groups']);
                print_r($result);
                break;
            case 'getoutcome':
                $result = $eekauth->getoutcome($vars['courseid'], $vars['useridnumber']);
                break;
            case 'getoutcomes':
                $result = $eekauth->getoutcomes($vars['courseid']);
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

/* POST AREA */
print_r($_POST);
print_r('<br />');
if (isset($_POST['get_grade'])) {
    $vars = $_POST;
    $result = api_actions('getoutcome', $vars);
} else if (isset($_POST['get_grades'])) {
    $vars = $_POST;
    $result = api_actions('getoutcomes', $vars);
} else if (isset($_POST['synccoursemembers'])) {
    $vars = $_POST;
    $result = api_actions('synccoursemembers', $vars);
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
            <input type="checkbox" name="random" value="true" checked>
            <input type="submit" name="submit" value="Submit">
        </form>
        <?php 
        if (isset($_POST['usercount']) || isset($_POST['random'])) {
            $users = generate_users($_POST['usercount'], $_POST['random']);
            print display_table($users);
        } else {
            $users = generate_users();
            print display_table($users);
        }
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
            Get all course grades by courseid<br />
            <form method="post" action="">
                <input type="text" name="courseid" value="" size="10">
                <input type="submit" name="get_grades" value="Submit">
            </form>
        </div>
    </body>
</html>