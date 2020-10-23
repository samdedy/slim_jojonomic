<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {    
    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    // getUser 
    $app->get("/getUser/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $sql = "SELECT * FROM user WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":id" => $id]);
        $result = $stmt->fetch();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    // getListUser 
    $app->get("/getListUser/", function (Request $request, Response $response){
        $sql = "SELECT * FROM user";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    // getCompany 
    $app->get("/getCompany/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $sql = "SELECT * FROM company WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":id" => $id]);
        $result = $stmt->fetch();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    // getListCompany 
    $app->get("/getListCompany/", function (Request $request, Response $response){
        $sql = "SELECT * FROM company";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    // getBudgetCompany
    $app->get("/getBudgetCompany/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $sql = "SELECT * FROM company_budget WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":id" => $id]);
        $result = $stmt->fetch();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    // getListBudgetCompany 
    $app->get("/getListBudgetCompany/", function (Request $request, Response $response){
        $sql = "SELECT * FROM company_budget";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    // getLogTransaction 

    // createUser 
    $app->post("/createUser/", function (Request $request, Response $response){

        $new_book = $request->getParsedBody();
    
        $sql = "INSERT INTO user (first_name, last_name, email, account, company_id) VALUE (:first_name, :last_name, :email, :account, :company_id)";
        $stmt = $this->db->prepare($sql);
    
        $data = [
            ":first_name" => $new_book["first_name"],
            ":last_name" => $new_book["last_name"],
            ":email" => $new_book["email"],
            ":account" => $new_book["account"],
            ":company_id" => $new_book["company_id"]
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });

    // createCompany 
    $app->post("/createCompany/", function (Request $request, Response $response){

        $new_book = $request->getParsedBody();
    
        $sql = "INSERT INTO company (name, address) VALUE (:name, :address)";
        $stmt = $this->db->prepare($sql);
    
        $data = [
            ":name" => $new_book["name"],
            ":address" => $new_book["address"]
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });

    // updateCompany
    $app->put("/updateCompany/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $new_book = $request->getParsedBody();
        $sql = "UPDATE company SET name=:name, address=:address WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":id" => $id,
            ":name" => $new_book["name"],
            ":address" => $new_book["address"]
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });

    // updateUser 
    $app->put("/updateUser/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $new_book = $request->getParsedBody();
        $sql = "UPDATE user SET first_name=:first_name, last_name=:last_name, email=:email, account=:account, company_id=:company_id  WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":id" => $id,
            ":first_name" => $new_book["first_name"],
            ":last_name" => $new_book["last_name"],
            ":email" => $new_book["email"],
            ":account" => $new_book["account"],
            ":company_id" => $new_book["company_id"]
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });

    // deleteCompany
    $app->delete("/deleteCompany/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $sql = "DELETE FROM company WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":id" => $id
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });

    // deleteUser 
    $app->delete("/deleteUser/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $sql = "DELETE FROM user WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":id" => $id
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });    
};
