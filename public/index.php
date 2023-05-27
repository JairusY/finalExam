<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/db.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

// GET REQUEST
$app->get('/students', function (Request $request, Response $response, array $args) {
    $db = new DB();
    $sql = "SELECT * FROM tblstudents";
    //Connect Database
    $connect = $db->connect();
    //Execute Query
    $result = mysqli_query($connect, $sql);

    //Check number of row
    if (mysqli_num_rows($result) > 0) {
        // Fetch each data (mysqli_fetch_all get assosiative and value array)
        while($row = mysqli_fetch_all($result, MYSQLI_ASSOC)) {
            $response->getBody()->write(json_encode($row));
        }
    } 
    else {
        $response->getBody()->write("0 results");
    }
    mysqli_free_result($result);
    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

// GET REQUEST (ONE DATA)
$app->get('/students/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $db = new DB();
    $sql = "SELECT * FROM tblstudents WHERE id = $id";
    //Connect Database
    $connect = $db->connect();
    //Execute Query
    $result = mysqli_query($connect, $sql);

    //Check number of row
    if (mysqli_num_rows($result) > 0) {
        // Fetch each data (mysqli_fetch_all get assosiative and value array)
        while($row = mysqli_fetch_all($result, MYSQLI_ASSOC)) {
            $response->getBody()->write(json_encode($row));
        }
    } 
    else {
        $response->getBody()->write("0 results");
    }
    mysqli_free_result($result);
    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

// POST REQUEST (ADD DATA)
$app->post('/students/add', function (Request $request, Response $response, array $args) {
    //Get All Data of JSON from request users
    $data = $request->getBody();
    $value = json_decode($data, TRUE); // Convert to JSON

    $db = new DB();
    $sql = "INSERT INTO tblstudents (name, course, address) VALUES (?,?,?)";

    //Connect Database
    $connect = $db->connect();
    
    //Execute Queryw
    if($stmt = mysqli_prepare($connect, $sql)){
        mysqli_stmt_bind_param($stmt, "sss", $name, $course, $address,);
        
        $name = $value['name'];  
        $course = $value['course'];   
        $address = $value['address']; 
        mysqli_stmt_execute($stmt);
        $response->getBody()->write("Record Added Successfully");
    }
    else{
        $response->getBody()->write("Error: Could not prepare query");
    }
    
    mysqli_stmt_close($stmt);
    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});


//update
$app->put('/students/update/{id}', function (Request $request, Response $response, array $args) {
    $sql = null;

    $id = $args['id'];   
    $data = $request->getBody();
    $value = json_decode($data, TRUE);

    //Execute Query
    if(!(empty($id))){
        $name = $value['name'];  
        $course = $value['course'];   
        $address = $value['address']; 
        $sql = "UPDATE tblstudents SET name = '$name', course = '$course', address = '$address' WHERE id = " . $id;
    }
    else{
        die("Error: ID not Define");
    }

    $db = new DB();

    //Connect Database
    $connect = $db->connect();

    if (mysqli_query($connect, $sql)) {
        $response->getBody()->write("Record Update Successfully");
    } 
    else {
        $response->getBody()->write("Error: Update Record");
    }

    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

// DELETE REQUEST (UPDATE DATA)
$app->delete('/students/delete/{id}', function (Request $request, Response $response, array $args) {
    $sql = null;

    $id = $request->getAttribute('id');   
    $data = $request->getBody();
    $value = json_decode($data, TRUE);

    //Execute Query
    if(!(empty($id))){
        $sql = "DELETE FROM tblstudents WHERE id = " . $id;
    }
    else{
        die("Error: ID not Define");
    }

    $db = new DB();

    //Connect Database
    $connect = $db->connect();

    if (mysqli_query($connect, $sql)) {
        $response->getBody()->write("Record Delete Successfully");
    } 
    else {
        $response->getBody()->write("Error: Delete Record");
    }

    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
});

$app->run();