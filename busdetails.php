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

if (isset($_POST['busname'])) {
    $busName = $_POST['busname'];
    $selectBusDetail = 'match(s1:Stop)-[r:Up]->(s2:Stop) where r.BusName="' . $busName . '" return s1,s2';
    $response = array();

    try {
        $client->sendCypherQuery($selectBusDetail);
        $result = $client->getRows();
        $name = array();
        if (!count($result) > 0) {
            showJson(0, "No Rows to Display");
        }
        for ($i = 0; $i < count($result['s1']); $i++) {
            $name[$i] = $result['s1'][$i]['name'];
        }
        $name[count($name)] = $result['s2'][count($result['s2']) - 1]['name'];
        $response['status'] = 1;
        $response['list'] = $name;
        echo json_encode($response);
        die();

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

<form action="busdetails.php" method="post">
    <fieldset>
        <legend>Bus Details</legend>
        <label for="busname">Bus Name</label><br><input type="text" name="busname" maxlength="100"><br><br>
        <input type="Submit" name="submit" value="Submit">
    </fieldset>
</form>
