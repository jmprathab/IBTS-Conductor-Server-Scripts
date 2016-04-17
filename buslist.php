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

    $selectBuses = 'match(bus:Bus) return bus order by bus.name';
    $response = array();

    try {
        $result = $client->sendCypherQuery($selectBuses)->getResult();
        $user=$result->getTableFormat();
        $name=array();
        for($i=0;$i<count($user);$i++){
            $name[$i]=$user[$i]['bus']['name'];
        }

        $response['status']=1;
        $response['list']=$name;
        echo json_encode($response);
        die();

    } catch (Exception $e) {
        showJson(0, "Error:" . $e->getMessage());
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
