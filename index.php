<?php

require_once 'vendor/autoload.php';
error_reporting(E_ALL ^ E_DEPRECATED);

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', '', 'curso_angular');

//Configuracion de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}


$app->get("/pruebas", function () use ($app) {
    echo "Hola mundo en parte Pruebas";
});
$app->get("/probando", function () use ($app, $db) {
    echo "Hola mundo en parte Probando";
    var_dump($db);
});
//LISTAR TODOS LOS PRODUCTOS
$app->get('/productos', function () use ($db, $app) {
    $sql = 'SELECT * FROM productos ORDER BY id DESC;';
    $query = $db->query($sql);
    // var_dump($query->fetch_all());

    $productos = array();
    while ($producto = $query->fetch_assoc()) {
        $productos[] = $producto;
    }
    $result = array(
        'status' => 'success',
        'code' => 200,
        'data' => $productos,
    );
    echo json_encode($result);
});
//DEVOLVER UN PRODUCTO
$app->get('/producto/:id', function ($id) use ($db, $app) {
    $sql = 'SELECT * FROM productos WHERE id = ' . $id;
    $query = $db->query($sql);
    // var_dump($query->fetch_all());
    $result = array(
        'status' => 'error',
        'code' => 404,
        'data' => 'producto NO disponible'
    );
    if ($query->num_rows == 1) {
        $producto = $query->fetch_assoc();

        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => $producto
        );
    }
    echo json_encode($result);
});

//ELIMINAR UN PRODUCTO
$app->get('/delete-producto/:id', function ($id) use ($db, $app) {
    $sql = 'DELETE FROM productos WHERE id = ' . $id;
    $query = $db->query($sql);
    // var_dump($query->fetch_all());
    $result = array(
        'status' => 'error',
        'code' => 404,
        'data' => 'producto NO se ha eliminado correctamente'
    );
    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => 'El producto se ha eliminado correctamente'
        );
    }
    echo json_encode($result);
});
//ACTUALIZAR UN PRODUCTO
$app->post("/update-producto/:id", function ($id) use ($app, $db) {
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    
    if (!isset($data['nombre'])) {
        $data['nombre'] = null;
    }
    if (!isset($data['descripcion'])) {
        $data['descripcion'] = null;
    }
    if (!isset($data['precio'])) {
        $data['precio'] = null;
    }
    if (!isset($data['imagen'])) {
        $data['imagen'] = null;
    }

    $sql = "UPDATE productos SET ".
    "nombre = '{$data['nombre']}', " .
    "descripcion = '{$data['descripcion']}', " ;
    if(isset($data['imagen'])){
        $sql .= "imagen = '{$data['imagen']}', ";
    }
    $sql .="precio = '{$data['precio']}' WHERE id = {$id}";
    $query = $db->query($sql);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'data' => 'producto NO se ha actualizado correctamente'
    );
    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => 'El producto se ha actualizado correctamente'
        );
    }
    echo json_encode($result);

});
//SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/upload-file', function() use($db, $app){
    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'El archivo no ha podido subirse'
    );

    if(isset($_FILES['uploads'])){
        echo "Llegan los datos";
        $piramideUploader = new PiramideUploader();
        $upload = $piramideUploader->upload('image','uploads','uploads',array('image/jpeg', 'image/png', 'image/gif'));
        $file = $piramideUploader->getInfoFile();
        $file_name = $file['complete_name'];

        if(isset($upload) && $upload["uploaded"]){
            $result = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El archivo se ha subido',
                'filename' => $file_name
            );
        }

        var_dump($file);
    }

    echo json_encode($result);

});
//GUARDAR PRODUCTO
$app->post("/producto", function () use ($app, $db) {
    $json = $app->request->post('json');
    $data = json_decode($json, true);
    echo "Json request";
    var_dump($json);
    echo "json decode";
    var_dump($data);

    if (!isset($data['nombre'])) {
        $data['nombre'] = null;
    }
    if (!isset($data['descripcion'])) {
        $data['descripcion'] = null;
    }
    if (!isset($data['precio'])) {
        $data['precio'] = null;
    }
    if (!isset($data['imagen'])) {
        $data['imagen'] = null;
    }
    $query = "INSERT INTO productos VALUES(NULL," .
        "'{$data['nombre']}'," .
        "'{$data['descripcion']}'," .
        "'{$data['precio']}'," .
        "'{$data['imagen']}'" .
        ");";

    var_dump("query: ", $query);

    $insert = $db->query($query);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Producto NO se ha creado correctamente'
    );

    echo "json decode";
    var_dump("insert: ", $insert);

    if ($insert) {
        $result = array(
            'status' => 'succes',
            'code' => 200,
            'message' => 'Producto creado correctamente'
        );
    }

    echo json_encode($result);
});
$app->get("/pruebas", function () use ($app, $db) {
    echo "Hola mundo en parte Pruebas";
});

$app->run();
