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

if (isset($_POST['mobile']) && isset($_POST['password'])) {
    //Variables are set
    $mobileNumber = $_POST['mobile'];
    $password = $_POST['password'];

    if (empty($mobileNumber)) {
        showJson(2, "Mobile Number should not be empty");
    }
    if (empty($password)) {
        showJson(2, "Password should not be empty");
    }
    $password = hash("sha256", $password);
    $selectUser = 'match(conductor:Conductor{mobile:{mobile},password:{password}}) return conductor';
    $parameters = array('mobile' => $mobileNumber, 'password' => $password);

    try {
        $result = $client->sendCypherQuery($selectUser, $parameters)->getResult();
        if ($result->getNodesCount() > 0) {
            $user = $result->getSingleNode();
            $userId = $user->getId();
        } else {
            showJson(0, "Cannot login\nCheck your Credentials");
        }
    } catch (Exception $e) {
        showJson(0, "Error:" . $e->getMessage());
    }

    $response = array();
    $response['status'] = 1;
    $response['message'] = "Successfully Logged In";
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

<form action="login.php" method="post">
    <fieldset>
        <legend>Login</legend>
        <label for="mobile">Mobile Number</label><br><input type="number" name="mobile" maxlength="10"><br><br>
        <label for="password">Password</label><br><input type="password" name="password" maxlength="100"><br><br>
        <input type="Submit" name="submit" value="Login">
    </fieldset>
</form>