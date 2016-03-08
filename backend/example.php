<?php
/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/27/16
 * Time: 9:43 PM
 */

//require 'connection.php';
require 'Subscription.php';

//$pdo = PDO_DB::factory();

$str = 'aeioubcdf';
$username = str_shuffle($str);

// prepared statement query
//$sql_query = "INSERT INTO users (username) VALUES (?)";
//$sql_reg = $pdo->prepare($sql_query);
//$sql_reg->execute(array($username));

// select query
/*$sql = 'SELECT id, username
        FROM users
        WHERE id = :id';
$sth = $pdo->prepare($sql);
$sth->execute(array(':id' => $_GET['id']));
*/
//$sql = 'SELECT id, username
//        FROM users';
//$sth = $pdo->prepare($sql);
//$sth->execute();

//$user_obj = User::read(102);
//
//$user = new User();
//$user->setUserId($user_obj->id);
//$user->setName($user_obj->name);
//$user->setEmail($user_obj->email);
//$user->setPassword($user_obj->password);
//$user->setDateRegistered($user_obj->date_registered);
//
//$user2 = new User();
//$user2->setUserId(102);
//$user2->setName("Pomf Pomf Pomf");
//$user2->delete();
//$user2->update();
//
//echo '<br>';
//var_dump($user);
//
//echo '<br>';
//var_dump($user2);
//
//$user->setName("Alao =3");
//$user->update();
//
//echo '<br>';
//var_dump($user);

//$new_user->setName(str_shuffle($str) . ' ' . str_shuffle($str) . ' ' . str_shuffle($str));
//$new_user->setEmail(str_shuffle($str) . '@sugoi.com');
//$new_user->setPassword(str_shuffle('dsahduweqjeasdksmdaksdmalksdmsakdmeqiwoeasdkpqowe'));
//
//$new_user->create();

echo '<br>';

//echo $user_obj->name;

//while ($user = $sth->fetch(PDO::FETCH_OBJ)) {
//    echo '<br>';
//    echo 'ID: '. $user->id . ', ' . 'Username: ' . $user->username;
//    //var_dump($user);
//}

$project = new Project();
$project->setProjectId(29);
$project->unsubscribe(101);


echo 'Done';