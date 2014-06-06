<?php 

function generate_users($count = 1) {
    
    $users = array(); 
    
    for ($i = 0; $i < $count; $i++) {
        $user = new stdClass();
        $user->username = 'bot'.rand(0,1000);
        $user->password = 'changeme';
        $user->firstname = $user->username;
        $user->lastname = $user->username;
        $user->email = $user->username.'@botnet.botz123';
        $user->idnumber = rand(0,1000);
        $user->country = 'Estonia';
        $user->city = 'Tartu';
        $user->policyagreed = 1;
        $user->confirmed = 1;
        $user->timecreated = time();
        $users[$i] = $user; 
    }
    
    return $users;
}


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
    
    return $table;
}

?>
<html>
    <head>
        <title>Testing EEK sync page</title>
    </head>
    <body>
        Testing EEK Functionalities<br />
        <form method="post" action="">
            <input type="text" name="usercount" value="1" size="10">
            <input type="submit" name="submit" value="Submit">
        </form>
        <?php 
        if (isset($_POST['usercount'])) {
            $users = generate_users($_POST['usercount']);
            print display_table($users);
        } else {
            $users = generate_users();
            print display_table($users);
        }
        ?>
    </body>
</html>