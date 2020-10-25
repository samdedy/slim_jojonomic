<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;

return function (App $app) {    
    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    //memperbolehkan cors origin 
    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });
    $app->add(function ($req, $res, $next) {
        $response = $next($req, $res);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, token')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    });

    $app->post('/login', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $email=trim(strip_tags($input['email']));
        $account=trim(strip_tags($input['account']));
        $sql = "SELECT id, email  FROM `user` WHERE email=:email AND `account`=:account";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("email", $email);
        $sth->bindParam("account", $account);
        $sth->execute();
        $user = $sth->fetchObject();       
        if(!$user) {
            return $this->response->withJson(['status' => 'error', 'message' => 'These credentials do not match our records email.']);  
        }
        $settings = $this->get('settings');       
        $token = array(
            'id' =>  $user->id, 
            'email' => $user->email
        );
        $token = JWT::encode($token, $settings['jwt']['secret'], "HS256");
        return $this->response->withJson(['status' => 'success','data'=>$user, 'token' => $token]); 
    });

    $app->post('/register', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $first_name=trim(strip_tags($input['first_name']));
        $last_name=trim(strip_tags($input['last_name']));
        $email=trim(strip_tags($input['email']));
        $account=trim(strip_tags($input['account']));
        $company_id=trim(strip_tags($input['company_id']));
        $sql = "INSERT INTO user(first_name, last_name, email, account, company_id) 
                VALUES(:first_name, :last_name, :email, :account, :company_id)";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("first_name", $first_name);             
        $sth->bindParam("last_name", $last_name);            
        $sth->bindParam("email", $email);                
        $sth->bindParam("account", $account);      
        $sth->bindParam("company_id", $company_id); 
        $StatusInsert=$sth->execute();
        if($StatusInsert){
            $IdUser=$this->db->lastInsertId();     
            $settings = $this->get('settings'); 
            $token = array(
                'IdUser' =>  $IdUser, 
                'email' => $email
            );
            $token = JWT::encode($token, $settings['jwt']['secret'], "HS256");
            $dataUser=array(
                'IdUser'=> $IdUser,
                'email'=> $email
                );
            return $this->response->withJson(['status' => 'success','data'=>$dataUser, 'token'=>$token]); 
        } else {
            return $this->response->withJson(['status' => 'error','data'=>'error insert user.']); 
        }
    });

    $app->group('/api', function(\Slim\App $app) {
        //letak rute yang akan kita autentikasi dengan token

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
        $app->get("/getListUser", function (Request $request, Response $response){
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
        $app->get("/getListCompany", function (Request $request, Response $response){
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
        $app->get("/getListBudgetCompany", function (Request $request, Response $response){
            $sql = "SELECT * FROM company_budget";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 200);
        });

        // getLogTransaction 
        $app->get("/getLogTransaction", function (Request $request, Response $response){
            $sql = "SELECT u.first_name, u.account, c.name, t.type, t.date, t.amount, (b.amount-t.amount) as remaining_amount
                    FROM transaction t
                    LEFT JOIN user u ON t.user_id = u.id 
                    LEFT JOIN company c ON u.company_id = c.id
                    LEFT JOIN company_budget b ON b.company_id=c.id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 200);
        });

        // createUser 
        $app->post("/createUser", function (Request $request, Response $response){

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
        $app->post("/createCompany", function (Request $request, Response $response){

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

        // reimburse
        $app->post('/reimburse', function (Request $request, Response $response, array $args) {
            $input = $request->getParsedBody();
            $type='R';
            $date = date('Y-m-d h:i:s');

            // subtract company budget
            $sql = "UPDATE company_budget SET amount = amount-:amount WHERE company_id = (SELECT user.company_id FROM user WHERE id=:user_id)";
            $stmt = $this->db->prepare($sql);  

            // add transaction
            $sql2 = "INSERT INTO transaction(type, user_id, amount, date) VALUES('$type', :user_id, :amount, '$date')";
            $stmt2 = $this->db->prepare($sql2);

            $data = [
                ":user_id" => $input["user_id"],
                ":amount" => $input["amount"]
            ];
        
            if($stmt->execute($data) && $stmt2->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        // disburse
        $app->post("/disburse", function (Request $request, Response $response, $args){
            $input = $request->getParsedBody();
            $type='C';
            $date = date('Y-m-d h:i:s');

            // subtract money, company budget
            $sql = "UPDATE company_budget SET amount = amount-:amount WHERE company_id = (SELECT user.company_id FROM user WHERE id=:user_id)";
            $stmt = $this->db->prepare($sql);  

            // add transaction
            $sql2 = "INSERT INTO transaction(type, user_id, amount, date) VALUES('$type', :user_id, :amount, '$date')";
            $stmt2 = $this->db->prepare($sql2);

            $data = [
                ":user_id" => $input["user_id"],
                ":amount" => $input["amount"]
            ];
        
            if($stmt->execute($data) && $stmt2->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        // close
        $app->put("/close/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_book = $request->getParsedBody();
            $type='S';
            $date = date('Y-m-d h:i:s');

            // add money, company budget
            $sql = "UPDATE company_budget SET amount = amount+(SELECT amount FROM transaction WHERE id=:id) 
                    WHERE company_id = (SELECT user.company_id FROM user WHERE id=(SELECT user_id FROM transaction WHERE id=:id))";
            $stmt = $this->db->prepare($sql);  

            $sql2 = "UPDATE transaction SET type='$type', date='$date' WHERE id=:id";
            $stmt2 = $this->db->prepare($sql2);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data) && $stmt2->execute($data))
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
    });  
};
