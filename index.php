<?php
#Importando o código TwitterAPIExchange
require_once('/etc/nginx/sites-available/TwitterAPIExchange.php');

#Parametrização dos tokens para construção do protocolo oAuth 1.0 disponibilizados pelo Twitter
$settings = array(
'oauth_access_token' => "1254183070476926976-A3pGScMwTOrelfUjkWqUQ0OkcfXUVz",
'oauth_access_token_secret' => "S38Dwk1hWlfjpva4h3cSx6zOPFUhXK5UIM40KUggyWXW9",
'consumer_key' => "cIhoP6p8Lwg6Ee4naUijEH7EC",
'consumer_secret' => "gkavdks0y0bqiDiRH86Wps6ziqGSo1FtJGiIn9d7xShsgRIE55"
);


#Construção da URL para consulta dos Tweets
echo "Coletando mensagens.... <br /><br />";
$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
$requestMethod = "GET";

#Construção da extração de tweets pelo método GET  baseado no nome de usuário e quantidade de Tweets
if (isset($_GET['user'])) {$user = $_GET['user'];} else {$user = "elonmusk";}
if (isset($_GET['count'])) {$count = $_GET['count'];} else {$count = 2;}
$getfield = "?screen_name=$user&count=$count&tweet_mode=extended";
$twitter = new TwitterAPIExchange($settings);
$string = json_decode($twitter->setGetfield($getfield)

#Construção da requisição do protocolo oAuth e a execução da requisição da URL construída pelo método GET
->buildOauth($url, $requestMethod)
->performRequest(),$assoc = TRUE);
if(array_key_exists("errors", $string)) {echo "<h3>Desculpe, ocorreu algum problema.</h3><p>Twitter retornou a seguinte mensagem:</p><p><em>".$string[errors][0]["message"]."</em></p>";exit();}


#Parâmetros para a conexão ao banco de dados
$servername = "localhost";
$username = "root";
$password = "3002";
$dbname = "mydb";

#Criação do objeto para conexão ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

#Verificação da conexão ao banco de dados
if ($conn->connect_error) {
    die("Não foi possíve conectar ao banco de dados, erro: " . $conn->connect_error);
}

#Após a extração das informações de usuário e dos tweets, exibimos na tela as informações que serão gravadas no banco de dados
foreach($string as $items){
    echo "Tweet: ". $items['full_text']."<br />";
    echo "Mensagem ID: ". $items['id_str']."<br />";
    echo "Data e Hora do Tweet: ".$items['created_at']."<br />";
    echo "User id: ".$items['user']['id']."<br />";
    echo "Tweet de: ". $items['user']['name']."<br />";
    echo "Usuario: ". $items['user']['screen_name']."<br />";
    echo "Seguidores: ". $items['user']['followers_count']."<br />";
    echo "Amigos: ". $items['user']['friends_count']."<br />";
    echo "Conta criada em: ". $items['user']['created_at']."<br />";
    echo "Listed: ". $items['user']['listed_count']."<br />";
    echo "Salvando mensagem.... <br /><br />";


    #Verificação no banco de dados se o usuário da mensagem já possui registro, e em caso de erro sua respectiva tratativa
    $sql = "select * from users where id_usr = ". $items['user']['id'];
    if($result = $conn->query($sql)){
      $row_cnt =$result->num_rows;
      if($row_cnt>0) {
          #Inserção de dados das mensagens no banco para o caso de usuários que ja possuem registros no banco, e em caso de erro sua respectiva tratativa
      		$sql = "INSERT INTO messages values ('". $items['id_str']."',". $items['user']['id'].",'". $items['user']['name']."','". $items['full_text']."','".$items['created_at']."')";
        	if ($conn->query($sql) === TRUE) {
        	    	echo "Registro gravado com sucesso! <br /><hr />";
        	}else{
        	    echo "Erro: " . $sql . "<br>" . $conn->error;
        	}

      }else{
        #Inserção de dados dos usuários caso não possuir seu registro no banco, e em caso de erro sua respectiva tratativa
      	$sql = "INSERT INTO users values (". $items['user']['id'].",'". $items['user']['name']."','". $items['user']['followers_count']."',". $items['user']['friends_count'].")";

    	  if($conn->query($sql) === TRUE){
            #Inserção de dados das mensagens no banco para o caso de usuários que não possuem registros no banco, desta forma as mensagens só serão registradas no banco caso tenha o usuário cadastrado. E em caso de erro sua respectiva tratativa
        		$sql = "INSERT INTO messages values ('". $items['id_str']."',". $items['user']['id'].",'". $items['user']['name']."','". $items['full_text']."','".$items['created_at']."')";
          	if ($conn->query($sql) === TRUE){
          	    	echo "Registro gravado com sucesso! <br /><hr />";
          	}else{
          	    echo "Erro: " . $sql . "<br>" . $conn->error;
          	}
         }else{
    	    echo "Erro: " . $sql . "<br>" . $conn->error;
    	   }
      }
    }else{
      echo "Erro: " . $sql . $conn->error;
    }
}

#Encerramento do objeto de conexão
$conn->close();
?>
