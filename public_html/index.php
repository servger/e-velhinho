<?php




              // Carrega arquivo de configurações do sistema
              require_once ('../settings.php');

              // Carrega arquivo de configurações do sistema
              require_once ('includes/languages/' . $CONFIG_LANG . '.php');




?>
<!DOCTYPE html>
  <html>
    <head>
      <meta charset="utf-8" /> 
      <!--Import Google Icon Font-->
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      <!--Import materialize.css-->
      <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>

      <!--Let browser know website is optimized for mobile-->
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>

    <body>
      <!--Import jQuery before materialize.js-->
      <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
      <script type="text/javascript" src="js/materialize.min.js"></script>




      <div class="container">
        <div class="row">
          <div class="col s12">




            <?php




              // Inicia sessão
              session_start();

              // Carrega bibliotecas necessárias ao sistema de autenticação do Google+
              require_once ('libraries/google/vendor/autoload.php');

              // Define parâmetros para acesso à API do Google
              $client_id = '1052706444033-973lps3dkg4dmcgmqcb2mg0uimpbi13f.apps.googleusercontent.com'; 
              $client_secret = 'Zvgv7Kk_NIOgE9xoiZUlWToN';
              $redirect_uri = 'http://hesfa.hol.es';

              // Define parâmetros para acesso ao banco de dados MySQL
              $db_username = "u655721938_hesfa"; //Database Username
              $db_password = "hesfaufrj2016"; //Database Password
              $host_name = "mysql.hostinger.com.br"; //Mysql Hostname
              $db_name = 'u655721938_hesfa'; //Database Name

              // Encerra sessão caso o usuário tenha solicitado desconexão (logout).
              if (isset($_GET['logout'])) {
                unset($_SESSION['access_token']);
              }




              /************************************************
                Make an API request on behalf of a user. In
                this case we need to have a valid OAuth 2.0
                token for the user, so we need to send them
                through a login flow. To do this we need some
                information from our API console project.
              ************************************************/
              $client = new Google_Client();
              $client->setClientId($client_id);
              $client->setClientSecret($client_secret);
              $client->setRedirectUri($redirect_uri);
              $client->addScope("email");
              $client->addScope("profile");




              /************************************************
                When we create the service here, we pass the
                client to it. The client then queries the service
                for the required scopes, and uses that when
                generating the authentication URL later.
              ************************************************/
              $service = new Google_Service_Oauth2($client);




              /************************************************
                If we have a code back from the OAuth 2.0 flow,
                we need to exchange that with the authenticate()
                function. We store the resultant access token
                bundle in the session, and redirect to ourself.
              ************************************************/
              if (isset($_GET['code'])) {
                $client->authenticate($_GET['code']);
                $_SESSION['access_token'] = $client->getAccessToken();
                header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
                exit;
              }




              /************************************************
                If we have an access token, we can make
                requests, else we generate an authentication URL.
              ************************************************/
              if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
                $client->setAccessToken($_SESSION['access_token']);

              } else {
                $authUrl = $client->createAuthUrl();

              }




              // Apresenta informações sobre o usuário ou o link para que ele possa efetuar seu login no Google.

              if (isset($authUrl)) {

                echo '<h1 class="center-align"><img src="images/logo168x128_HESFA.png"/></h1>';
                echo '<h5>' . $SYS_MESSAGE['wellcome'] . '</h5>';

              } else {

                // Obtém informações sobre o usuário
                $user = $service->userinfo->get();

                // Apresenta as informações obtidas
                echo '<div class="card small">';
                echo '  <div class="card-image waves-effect waves-block waves-light">';
                echo '    <img class="activator" src="' . $user->picture  . '">';
                echo '  </div>';
                echo '  <div class="card-content">';
                echo '    <span class="card-title activator grey-text text-darken-4">' . $user->name . '<i class="material-icons right">more_vert</i></span>';
                echo '    <p><a href="mailto:' . $user->email . '">' . $user->email . '</a></p>';
                echo '  </div>';
                echo '  <div class="card-reveal">';
                echo '    <span class="card-title grey-text text-darken-4">' . $user->name . '<i class="material-icons right">close</i></span>';
                echo '    <pre>';
                print_r($user);
                echo '    </pre>';
                echo '  </div>';
                echo '</div>';

                // Apresenta botão para logout
                echo '<a class="waves-effect waves-light btn" href="' . $redirect_uri . '?logout=1">LOGOUT</a>';

                // Inicia conexão com o banco de dados MySQL
                $mysqli = new mysqli($host_name, $db_username, $db_password, $db_name);
                if ($mysqli->connect_error) {
                  die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
                }

                // Verifica se o usuário já foi cadastrado no banco de dados
                $query = "select count(google_id) as total from google_users where google_id = ?";
                if ($stmt = mysqli_prepare($mysqli, $query))
                 {
                    mysqli_stmt_bind_param($stmt, 's', $user->id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $total);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);
                 }

                 if ($total<>0) {
                   echo '<p>Seja bem-vindo(a)!</p>';
                 } else {
                   $query = "INSERT INTO google_users (google_id, google_name, google_email, google_link, google_picture_link) VALUES (?,?,?,?,?)";
                   if ($stmt = mysqli_prepare($mysqli, $query))
                     {
                       mysqli_stmt_bind_param($stmt, 'sssss', $user->id,  $user->name, $user->email, $user->link, $user->picture);
                       mysqli_stmt_execute($stmt);
                       mysqli_stmt_close($stmt);
                     }
                   echo '<p>Obrigado por se cadastrar</p>';

                 }
                


              }





            ?>




          </div>
        </div>
      </div>


    </body>
  </html>
