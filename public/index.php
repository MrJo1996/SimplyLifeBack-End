<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 11/05/18
 * Time: 21:11
 */

/* In questo file php vengono elencati tutti gli endpoint disponibili al servizio REST */

//Importiamo Slim e le sue librerie
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../vendor/autoload.php';
require '../DB/DBConnectionManager.php';
require '../DB/DBUtenti.php';

/*require '../Helper/EmailHelper/EmailHelper.php';*/
require '../Helper/EmailHelper/EmailHelperAltervista.php';
require '../Helper/RandomPasswordHelper/RandomPasswordHelper.php';

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

// Instantiate the app -
$settings = require __DIR__ . '/../src/settings.php';
$app = new App($settings); //"Contenitore" per gli endpoint da riempire


$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


/*  Gli endpoint sono delle richieste http accessibili al Client gestite poi dal nostro Server REST.
    Tra i tipi di richieste http, le piÃ¹ usate sono:
    - get (richiesta dati -> elaborazione server -> risposta server)
    - post (invio dati criptati e richiesta dati -> elaborazione server -> risposta server)
    - delete (invio dato (id di solito) e richiesta eliminazione -> elaborazione server -> risposta server)

    Slim facilita per noi la gestione della richiesta http mettendo a disposizione funzioni facili da implementare
    hanno la forma:

    app->"richiesta http"('/nome endpoint', function (Request "dati inviati dal client", Response "dati risposti dal server") {

        //logica del servizio  ---- (COME SI FA IL JS)

        return "risposta";

    }

 */

/*************** LISTA DI ENDPOINT **************/

/* aggiungo ad $app tutta la lista di endpoint che voglio */

/**** ENDPOINT DEL PROGETTO ****/

// endpoint: /login         OK
$app->post('/login', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $email = $requestData['email'];
    $password = $requestData['password'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data']= $db->login($email, $password);
    if ($responseData['data']) { //Se l'utente esiste ed e' corretta la password
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Accesso effettuato'; //Messaggio di esiso positivo


    } else { //Se le credenziali non sono corrette
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Credenziali errate'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


// endpoint: /registrazione     OK
$app->post('/registrazione', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $email = $requestData['email'];
    $password = $requestData['password'];
    $nome = $requestData['nome'];
    $cognome = $requestData['cognome'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseDB= $db->registrazione($email, $password, $nome, $cognome);
    if ($responseDB== 1) { //Se la registrazione è andata a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Registrazione avvenuta con successo'; //Messaggio di esito positivo
        $emailSender = new EmailHelperAltervista();
        $link = 'http://unimolshare.altervista.org/logic/UnimolShare/public/activate.php?email=' . $email;
        $emailSender->sendConfermaAccount($email, $link);
    } else if ($responseDB== 2) { //Se l'email è già presente nel DB
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Account già  esistente!'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

//endpoint visualizza importo per codice categoria      OK - AGGIUSTATO(Danilo+Dorothea)
$app->post('/visualizzaimporto', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData = $request->getParsedBody();
    $codice_scadenza = $requestData['codice_scadenza'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaImportoPerCodice($codice_scadenza);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Importo" => $responseData)));
        //metto in un json e lo inserisco nella risposta del servizio REST
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});



//endpoint visualizza periodo per codice scadenza       OK - AGGIUSTATO(Doro)
$app->post('/visualizzaperiodo', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $requestData= $request->getParsedBody();
    $codice_scadenza = $requestData['codice_scadenza'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaPeriodoPerCodice($codice_scadenza);

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Periodo" => $responseData)));
        //metto in un json e lo inserisco nella risposta del servizio REST
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});


/****** PER ORA NON FUNZIONA COL L'ENDPOINT MA TRAMITE LINK DIRETTO COL FILE ACRTIVATE.PHP NELLA CARTELLA PUBLIC ***/
// endpoint: /conferma
$app->get('/conferma', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $email = $requestData['email'];
    $codice_utente = $requestData['codice_utente'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseData=$db->confermaProfilo($email, $codice_utente);
    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($responseData) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Profilo confermato'; //Messaggio di esiso positivo
    } else { //Se c'è stato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = "Impossibile confermare il profilo"; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


// endpoint: /modificascadenza
$app->post('/modificascadenza', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_scadenza = $requestData['codice_scadenza'];
    $nome = $requestData['nome'];
    $data_ricezione = $requestData['data_ricezione'];
    $data_scadenza = $requestData['data_scadenza'];
    $periodo = $requestData['periodo'];
    $importo = $requestData['importo'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
    $responseDB=$db->modificaScadenza($codice_scadenza, $nome, $data_ricezione, $data_scadenza, $periodo, $importo);
    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($responseDB) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Modifica effettuata'; //Messaggio di esiso positivo

    } else { //Se c'è stato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = "Impossibile effettuare la modifica"; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


//endpoint /recupero password/modifica password             OK
$app->post('/recupero', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $email = $requestData['email'];

    //Risposta del servizio REST
    $responseData = array();
    $emailSender = new EmailHelperAltervista();
    $randomizerPassword = new RandomPasswordHelper();

    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->recupero($email)) { //Se l'email viene trovata
        $nuovaPassword = $randomizerPassword->generatePassword(4);

        if ($db->modificaPassword($email, $nuovaPassword)) {
            if ($emailSender->sendResetPasswordEmail($email, $nuovaPassword)) {
                $responseData['error'] = false; //Campo errore = false
                $responseData['message'] = "Email di recupero password inviata"; //Messaggio di esito positivo
            } else {
                $responseData['error'] = true; //Campo errore = true
                $responseData['message'] = "Impossibile inviare l'email di recupero"; //Messaggio di esito negativo
            }
        } else { //Se le credenziali non sono corrette
            $responseData['error'] = true; //Campo errore = true
            $responseData['message'] = 'Impossibile comunicare col Database'; //Messaggio di esito negativo
        }

    } else { //Se le credenziali non sono corrette
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Email non presente nel DB'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


//endpoint /visualizza scadenze per categoria       OK
$app->post('/visualizzascadenzepercategoria', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $categoria = $requestData['nome_categoria'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaScadenzePerCategoria($categoria);
    $contatore = (count($responseData));
    if ($responseData['data'] != null) {
        $responseData['contatore'] = $contatore;
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("scadenze" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});


//endpoint /Visualizza categoria        OK
$app->post('/visualizzacategoria', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaCategorie();
    $contatore = (count($responseData));
    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['contatore'] = $contatore;
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("Categorie" => $responseData)));
        //metto in un json e lo inserisco nella risposta del servizio REST
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});


//endpoint rimuovi scadenza--------------Non rimuove
$app->delete('/rimuoviscadenza/{codice_scadenza}', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $codice_scadenza = $request->getAttribute('codice_scadenza');
    //Risposta del servizio REST
    $responseData = array(); //La risposta è un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $esito = $db->rimuoviScadenza($codice_scadenza);
    if ($esito) { //Se è stato possibile rimuovere la scadenza
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Scadenza rimossa'; //Messaggio di esito positivo

    } else { //Se si è verificato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Non Ã¨ stato possibile rimuovere la scadenza'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


//endpoint /visualizzanomeutente            OK
$app->post('/visualizzanomeutente', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_utente = $requestData['codice_utente'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data'] = $db->visualizzaNomeUtente($codice_utente);
    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo

        $response->getBody()->write(json_encode(array("Utente" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client

    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto'; //Messaggio di esiso negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


//visualizza stato pagamento   OK
$app->post('/visualizzapagamento', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $codice_scadenza = $requestData['codice_scadenza'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data']= $db->visualizzaPagamento($codice_scadenza);
    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo

        $response->getBody()->write(json_encode(array("Scadenza statO" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client

    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto'; //Messaggio di esiso negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


// endpoint: /inserisci scadenza
$app->post('/inserisciscadenza', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $nome = $requestData['nome'];
    $data_ricezione = $requestData['data_ricezione'];
    $data_scadenza = $requestData['data_scadenza'];
    $periodo = $requestData['periodo'];
    $cod_categoria = $requestData['cod_categoria'];
    $cod_utente = $requestData['cod_utente'];
    $importo = $requestData['importo'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->inserisciScadenza($nome, $data_ricezione, $data_scadenza, $periodo, $cod_categoria, $cod_utente, $importo)) { //Se l'inserimento Ã¨ andato a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Inserimento avvenuto con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Inserimento non effettuato'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});


// visualizza scadenza per data    OK  - AGGIUSTATA (JO)
$app->post('/visualizzascadenzaperdata', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $cod_utente = $requestData['cod_utente'];

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData['data']= $db->visualizzaScadenzaPerData($cod_utente);
    $contatore = (count($responseData));

    if ($responseData['data'] != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("Scadenze:" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
});

//conferma pagamento            OK
$app->post('/confermapagamento', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $codice_scadenza = $requestData['codice_scadenza'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseDB = $db->confermaPagamentoScadenza($codice_scadenza);
    if ($responseDB == 1) { //Se l'azione è andata a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Nessun errore'; //Messaggio di esito positivo
    } else { //Se la bolletta non è presente nel DB
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Impossibile completare';
    }
    return $response->withJson($responseData); //Messaggio di esito negativo
});

//annulla pagamento OK
$app->post('/annullapagamento', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $codice_scadenza = $requestData['codice_scadenza'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData= $db->annullaPagamentoScadenza($codice_scadenza);
    if ($responseData == 1) { //Se l'azione è andata a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Nessun errore'; //Messaggio di esito positivo
    } else { //Se la bolletta non è presente nel DB
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Impossibile completare';
    }
    return $response->withJson($responseData); //Messaggio di esito negativo
});


// Run app = ho riempito $app e avvio il servizio REST

$app->run();

?>
