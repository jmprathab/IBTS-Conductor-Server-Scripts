<?php
require_once 'vendor/autoload.php';
use Neoxygen\NeoClient\ClientBuilder;

$host = 'localhost';
$port = 7474;
$dbUsername = 'neo4j';
$dbPassword = 'jaihanuman';

$client = ClientBuilder::create()
    ->addConnection('default', 'http', $host, $port, true, $dbUsername, $dbPassword)
    ->setAutoFormatResponse(true)
    ->setDefaultTimeout(20)
    ->build();

if (isset($_POST['name']) && isset($_POST['mobile']) && isset($_POST['password']) && isset($_POST['address'])) {
    //Variables are set
    $name = $_POST['name'];
    $mobileNumber = $_POST['mobile'];
    $password = $_POST['password'];
    $address = $_POST['address'];

    $name = ucwords($name);

    if (empty($name)) {
        showJson(2, "Name should not be empty");
    }
    if (empty($mobileNumber)) {
        showJson(2, "Mobile Number should not be empty");
    }
    if (empty($password)) {
        showJson(2, "Password should not be empty");
    }
    if (empty($address)) {
        showJson(2, "Address should not be empty");
    }

    $password = hash("sha256", $password);
    //Checking whether User had already registered
    $selectUser = 'match(conductor:Conductor{mobile:{mobile},password:{password}}) return conductor';
    $parameters = array('mobile' => $mobileNumber, 'password' => $password);
    //Creating New User
    $name = ucwords($name);
    $createUser = 'create(conductor:Conductor{name:{name},mobile:{mobile},password:{password},address:{address},time:timestamp()}) return conductor';
    $parameters = array('name' => $name, 'mobile' => $mobileNumber, 'password' => $password, 'address' => $address);

    try {
        $result = $client->sendCypherQuery($selectUser, $parameters)->getResult();
        if ($result->getNodesCount() > 0) {
            showJson(3, "You have already registered\nLogin using credentials");
        }
    } catch (Exception $e) {
        showJson(0, "Error:" . $e->getMessage());
    }

    try {
        $result = $client->sendCypherQuery($createUser, $parameters)->getResult();
        $user = $result->getSingleNode();
        $userId = $user->getId();
    } catch (Exception $e) {
        showJson(0, "Error:" . $e->getMessage());
    }

    $response = array();
    $response['status'] = 1;
    $response['message'] = "Account Created Successfully";
    $response['user_id'] = $userId;
    echo json_encode($response);
    die();

}

function showJson($status, $message)
{
    $response = array();
    $response['status'] = $status;
    $response['message'] = $message;
    echo json_encode($response);
    die();
}

?>

<form action="register.php" method="post">
    <fieldset>
        <legend>Create Account</legend>
        <label for="name">Name</label><br><input type="text" name="name" maxlength="100"><br><br>
        <label for="mobile">Mobile Number</label><br><input type="number" name="mobile" maxlength="10"><br><br>
        <label for="password">Password</label><br><input type="password" name="password" maxlength="100"><br><br>
        <label for="address">Address</label><br><input type="text" name="address" maxlength="80"><br><br>
        <input type="Submit" name="submit" value="Register">
    </fieldset>
</form>
