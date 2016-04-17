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

if (isset($_POST['user_id']) && isset($_POST['conductor_id']) && isset($_POST['busname']) && isset($_POST['starting_stop']) && isset($_POST['ending_stop']) && isset($_POST['number_of_passengers'])) {
    $userId = $_POST['user_id'];
    $conductorId = $_POST['conductor_id'];
    $busName = $_POST['busname'];
    $startingStop = $_POST['starting_stop'];
    $endingStop = $_POST['ending_stop'];
    $numberOfPassengers = $_POST['number_of_passengers'];

    if (empty($userId) || empty($conductorId) || empty($busName) || empty($startingStop) || empty($endingStop) || empty($numberOfPassengers)) {
        showJson(0, "Fields Should not be empty");
    }

    $selectStopDetails = 'match(s1:Stop)-[r:Up]->(s2:Stop) where r.BusName="' . $busName . '" return s1.name AS s1,r.distance as distance,s2.name as s2';
    $response = array();

    try {
        $client->sendCypherQuery($selectStopDetails);
        $result = $client->getRows();
        $stop1 = $result['s1'];
        $stop2 = $result['s2'];
        $distance = $result['distance'];

        $startIndex = -1;
        $distanceTravelled = 0;

        if ($startingStop == $endingStop) {

        }

        if(sizeof($stop1) ==0 || sizeof($stop2)==0 ){
            showJson(0,"Invalid Data");
        }
        //Finding first occurence of starting stop
        for ($i = 0; $i < sizeof($stop1); $i++) {
            if ($stop1[$i] == $startingStop) {
                $startIndex = $i;
                break;
            }
        }
        //calculating total distance
        for ($i = $startIndex; ;) {
            $distanceTravelled = $distanceTravelled + $distance[$i];
            if ($stop2[$i] == $endingStop) {
                break;
            } else {
                $i = ($i + 1) % sizeof($distance);
            }
        }

        $balance = 0;
        $totalAmount = (($distanceTravelled * 2) + 2) * $numberOfPassengers;
        $selectUser = 'match(user:User) where id(user)=' . $userId . ' return user';
        try {
            $result = $client->sendCypherQuery($selectUser)->getResult();
            if ($result->getNodesCount() > 0) {
                $user = $result->getSingleNode();
                $userId = $user->getId();
                $balance = $user->getProperty("balance");
            } else {
                showJson(0, "Cannot verify user\nCheck your Credentials");
            }
        } catch (Exception $e) {
            showJson(0, "Error:" . $e->getMessage());
        }

        $selectUser = 'match(user:User) where id(user)=' . $userId . ' set user.balance=' . ($balance - $totalAmount) . ' return user';
        try {
            $result = $client->sendCypherQuery($selectUser)->getResult();
            if ($result->getNodesCount() > 0) {
                $response['status'] = 1;
                $response['distance'] = $distanceTravelled;
                $response['amount'] = $totalAmount;
                echo json_encode($response);
                die();
            } else {
                showJson(0, "Cannot modify details in database");
            }
        } catch (Exception $e) {
            showJson(0, "Error:" . $e->getMessage());
        }


    } catch (Exception $e) {
        showJson(0, "Error:" . $e->getMessage());
    }
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

<form action="travel.php" method="post">
    <fieldset>
        <legend>Travel Details</legend>
        <label for="user_id">User ID</label><br><input type="text" name="user_id" maxlength="100"><br><br>
        <label for="conductor_id">Conductor ID</label><br><input type="text" name="conductor_id"
                                                                 maxlength="100"><br><br>
        <label for="busname">Bus Name</label><br><input type="text" name="busname" maxlength="100"><br><br>
        <label for="starting_stop">Starting Stop</label><br><input type="text" name="starting_stop" maxlength="100"><br><br>
        <label for="ending_stop">Ending Stop</label><br><input type="text" name="ending_stop" maxlength="100"><br><br>
        <label for="number_of_passengers">Number of Passengers</label><br><input type="text"
                                                                                 name="number_of_passengers"
                                                                                 maxlength="100"><br><br>
        <input type="Submit" name="submit" value="Submit">
    </fieldset>
</form>
