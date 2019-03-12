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

/*************** LISTA DI ENDPOINT ***************/

/* aggiungo ad $app tutta la lista di endpoint che voglio */

/**** ENDPOINT DEL PROGETTO ****/

// endpoint: /login
$app->post('/login', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $email = $requestData['email'];
    $password = $requestData['password'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $utente = $db->login($email, $password);
    if ($utente) { //Se l'utente esiste ed e' corretta la password
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Accesso effettuato'; //Messaggio di esiso positivo
        $responseData['utente'] = $utente[0]; //CAPIRE A COSA SERVE

    } else { //Se le credenziali non sono corrette
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Credenziali errate'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

// endpoint: /registration
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
    $responseDB = $db->registrazione($email, $password, $nome, $cognome);
    if ($responseDB == 1) { //Se la registrazione è andata a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Registrazione avvenuta con successo'; //Messaggio di esito positivo
        $emailSender = new EmailHelperAltervista();
        $link = 'http://unimolshare.altervista.org/logic/UnimolShare/public/activate.php?email=' . $email;
        $emailSender->sendConfermaAccount($email, $link);
    } else if ($responseDB == 2) { //Se l'email è già presente nel DB
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Account già  esistente!'; //Messaggio di esito negativo
    }

    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

$app->post('/visualizzaimporto', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $codice_scadenza = $requestData['codice_scadenza'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseDB = $db->visualizzaImportoPerCodice($codice_scadenza);
    if ($responseDB == 1) { //Se la registrazione è andata a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Nessun errore'; //Messaggio di esito positivo
        $response->getBody()->write(json_encode(array("importo" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse;
    } else if ($responseDB == 2) { //Se la bolletta non è presente nel DB
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Bolletta non trovata!';
        return $response->withJson($responseData); //Messaggio di esito negativo
    }
});

$app->post('/visualizzaperiodo', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST

    $codice_scadenza = $requestData['codice_scadenza'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseDB = $db->visualizzaPeriodoPerCodice($codice_scadenza);
    if ($responseDB == 1) { //Se la registrazione è andata a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Nessun errore'; //Messaggio di esito positivo
        $response->getBody()->write(json_encode(array("importo" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse;
    } else if ($responseDB == 2) { //Se la bolletta non è presente nel DB
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Bolletta non trovata!';
        return $response->withJson($responseData); //Messaggio di esito negativo
    }
});


/****** PER ORA NON FUNZIONA COL L'ENDPOINT MA TRAMITE LINK DIRETTO COL FILE ACRTIVATE.PHP NELLA CARTELLA PUBLIC ***/
// endpoint: /conferma (Andrea)
$app->get('/conferma', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $email = $requestData['email'];
    $matricola = $requestData['matricola'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->confermaProfilo($email, $matricola)) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Profilo confermato'; //Messaggio di esiso positivo
    } else { //Se c'è stato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = "Impossibile confermare il profilo"; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

// endpoint: /update (Gigi)
$app->post('/update', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $matricola = $requestData['matricola'];
    $nome = $requestData['nome'];
    $cognome = $requestData['cognome'];
    $password = $requestData['password'];
    $tabella = $requestData['tabella'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->modificaProfilo($matricola, $nome, $cognome, $password, $tabella)) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Update effettuato'; //Messaggio di esiso positivo

    } else { //Se c'è stato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = "Impossibile effettuare l'update"; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

//endpoint /recover (Danilo) OK
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

//endpoint /visualizzamateriapercdl (danilo)ok
$app->post('/visualizzamateriapercdl', function (Request $request, Response $response) {

    $db = new DBDocenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $cod_cdl = $requestData['cod_cdl'];

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaMateriaPerCdl($cod_cdl);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("nomi_materie" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
    //Invio la risposta del servizio REST al client
});

//endpoint /visualizzadocumentopermateria (Danilo)ok
$app->post('/visualizzadocumentopermateria', function (Request $request, Response $response) {

    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $materia = $requestData['materia'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaDocumentoPerMateria($materia);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['contatore'] = $contatore;
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("documenti" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});

$app->post('/visualizzadocumentoperid', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $matricola = $requestData['matricola'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaDocumentoPerId($matricola);
    $contatore = (count($responseData));

    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo';//Messaggio di esiso positivo

        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("documenti" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});

$app->post('/visualizzacdlperiddocente', function (Request $request, Response $response) {

    $db = new DBDocenti();

    $requestData = $request->getParsedBody();
    $iddoc = $requestData['iddoc'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaCdlPerCodDoc($iddoc);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['contatore'] = $contatore;
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("CDL" => $responseData)));
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

$app->post('/visualizzacdlperidstudente', function (Request $request, Response $response) {

    $db = new DBStudente();

    $requestData = $request->getParsedBody();
    $matricola = $requestData['matricola'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaCdlStudente($matricola);

    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['contatore'] = $contatore;
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("CDL" => $responseData)));
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

//endpoint /VisualizzaCDL (Danilo)
$app->post('/visualizzacdlperid', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();
    $idcdl = $requestData['idcdl'];
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaCdlPerid($idcdl);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['contatore'] = $contatore;
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("CDL" => $responseData)));
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

$app->post('/visualizzacdl', function (Request $request, Response $response) {

    $db = new DBUtenti();

    $requestData = $request->getParsedBody();
//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaCdl();
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['contatore'] = $contatore;
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("CDL" => $responseData)));
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


//endpoint /visualizzaannunciopermateria (danilo)ok
$app->post('/visualizzaannunciopermateria', function (Request $request, Response $response) {

    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $materia = $requestData['materia'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaAnnuncioPerMateria($materia);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("libri" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});

$app->post('/visualizzaannuncioperid', function (Request $request, Response $response) {

    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $matricola = $requestData['matricola'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaAnnuncioPerId($matricola);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("annunci" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});

$app->post('/visualizzalibropermateria', function (Request $request, Response $response) {

    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $materia = $requestData['materia'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaLibroPerMateria($materia);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("libri" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});

$app->post('/visualizzalibropercognomedocente', function (Request $request, Response $response) {

    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $cognome = $requestData['cognome'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaLibroPerCognomeDocente($cognome);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("libri" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});


// Run app = ho riempito $app e avvio il servizio REST

$app->post('/visualizzadocumentistudenti', function (Request $request, Response $response) {

    $db = new DBStudente();

//Controllo la risposta dal DB e compilo i campi della risposta ok
    $responseData = $db->visualizzaDocumentistudenti();
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esito positivo
        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("documenti" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }

});
$app->post('/visualizzatutticdl', function (Request $request, Response $response) {

    $db = new DBUtenti();

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaCdl();
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['contatore'] = $contatore;
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $response->getBody()->write(json_encode(array("CDL" => $responseData)));
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
//endpoint rimuovi by jo dom
$app->delete('/rimuovidocumento/{id}', function (Request $request, Response $response) {
    $db = new DBUtenti();
    $idDocumento = $request->getAttribute('id');
    //Risposta del servizio REST
    $responseData = array(); //La risposta è un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $esito = $db->rimuoviDocumento($idDocumento);
    if ($esito) { //Se è stato possibile rimuovere il documento
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Documento rimosso'; //Messaggio di esito positivo

    } else { //Se si è verificato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Non Ã¨ stato possibile rimuovere il documento'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});
$app->delete('/rimuoviAnnuncio{id}', function (Request $request, Response $response) {
    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $id = $request->getAttribute('id');


    //Risposta del servizio REST
    $responseData = array(); //La risposta è un array di informazioni da compilare


    if ($db->rimuoviAnnuncio($id)) { //Se l'utente esiste ed è corretta la password
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Documento eliminato'; //Messaggio di esiso positivo


    } else { //Se si è verificato un errore imprevisto
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Erorre imprevisto'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});
//endpoint /visualizzaprofilostudente (Michela) OK
$app->post('/visualizzaprofilostudente', function (Request $request, Response $response) {

    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $matricola = $requestData['matricola'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaProfiloStudente($matricola);
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo

        $response->getBody()->write(json_encode(array("Studente" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client

    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto'; //Messaggio di esiso negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

//endpoint /visualizzaprofilodocente (Michela) ok
$app->post('/visualizzaprofilodocente', function (Request $request, Response $response) {

    $db = new DBDocenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $matricola = $requestData['matricola'];

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaProfiloDocente($matricola);
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo';//Messaggio di esiso positivo

        $response->getBody()->write(json_encode(array("profilo" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto'; //Messaggio di esiso negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

// endpoint: /caricaDocumento (Jonathan) ok
$app->post('/caricadocumento', function (Request $request, Response $response) {
    $db = new DBUtenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $titolo = $requestData['titolo'];
    $codice_docente = $requestData['codice_docente'];
    $codice_studente = $requestData['codice_studente'];
    $codice_materia = $requestData['codice_materia'];
    $link = $requestData['link'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->caricaDocumento($titolo, $codice_docente, $codice_studente, $codice_materia, $link)) { //Se il caricamento del doc Ã¨ andata a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Caricamento avvenuto con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Caricamento non effettuato'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});
$app->post('/caricacdldocente', function (Request $request, Response $response) {
    $db = new DBDocenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $matricola = $requestData['matricola'];
    $id = $requestData['id'];


    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->caricaCdl($id, $matricola)) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Caricamento avvenuto con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Caricamento non effettuato'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

$app->post('/caricaannuncio', function (Request $request, Response $response) {
    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $titolo = $requestData['titolo'];
    $contatto = $requestData['contatto'];
    $prezzo = $requestData['prezzo'];
    $edizione = $requestData['edizione'];
    $casa_editrice = $requestData['casa_editrice'];
    $cod_studente = $requestData['cod_studente'];
    $autori = $requestData['autori'];
    $cod_materia = $requestData['cod_materia'];


    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
//$query=$db->caricaAnnuncio($titolo, $contatto, $prezzo, $edizione, $casa_editrice, $cod_studente, $autori, $cod_materia, $link);
    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->caricaAnnuncio($titolo, $contatto, $prezzo, $edizione, $casa_editrice, $cod_studente, $autori, $cod_materia)) { //Se il caricamento del doc Ã¨ andata a buon fine

        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Caricamento avvenuto con successo '; //Messaggio di esito positivo


    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Caricamento non effettuato'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});
$app->post('/caricalibro', function (Request $request, Response $response) {
    $db = new DBDocenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $titolo = $requestData['titolo'];
    $edizione = $requestData['edizione'];
    $casa_editrice = $requestData['casa_editrice'];
    $cod_docente = $requestData['cod_studente'];
    $autori = $requestData['autori'];
    $cod_materia = $requestData['cod_materia'];
    $link = $requestData['link'];


    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare
//$query=$db->caricaAnnuncio($titolo, $contatto, $prezzo, $edizione, $casa_editrice, $cod_studente, $autori, $cod_materia, $link);
    //Controllo la risposta dal DB e compilo i campi della risposta
    if ($db->caricaLibro($titolo, $edizione, $casa_editrice, $cod_docente, $autori, $cod_materia, $link)) { //Se il caricamento del doc Ã¨ andata a buon fine

        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Caricamento avvenuto con successo '; //Messaggio di esito positivo


    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Caricamento non effettuato'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});
// endpoint: /downloadDocumento (Andrea) OK
$app->post('/downloadDocumento', function (Request $request, Response $response) {
    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $id = $requestData['id'];

    //Risposta del servizio REST
    $responseData = array(); //La risposta e' un array di informazioni da compilare

    //Controllo la risposta dal DB e compilo i campi della risposta
    $link = $db->downloadDocumento($id);
    if ($link != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'In download'; //Messaggio di esiso positivo
        $responseData['link'] = $link;
    } else { //Se si verifica un errore
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Impossibile scaricare il file'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

//contattavenditore by domenico ok
$app->post('/contattavenditore', function (Request $request, Response $response) {

    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $idAnnuncio = $requestData['id'];

//Risposta del servizio REST
    $responseData = array();

//Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->contattaVenditore($idAnnuncio);

    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elementi visualizzati con successo'; //Messaggio di esito positivo
        $response->getBody()->write(json_encode(array("contatti" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Errore imprevisto'; //Messaggio di esito negativo
        return $response->withJson($responseData);
    }
    //Invio la risposta del servizio REST al client
});

// endpoint: /valutazionedocumento (Andrea) ok
$app->post('/valutazionedocumento', function (Request $request, Response $response) {
    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $valutazione = $requestData['valutazione'];
    $cod_documento = $requestData['cod_documento'];

    //Risposta del servizio REST
    $responseData = array();

    //Controllo la risposta dal DB
    if ($db->valutazioneDocumento($valutazione, $cod_documento)) { //Se il caricamento della valutaizone è andato a buon fine
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Valutazione avvenuta con successo'; //Messaggio di esito positivo

    } else {
        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = 'Valutaizone non effettuata'; //Messaggio di esito negativo
    }
    return $response->withJson($responseData); //Invio la risposta del servizio REST al client
});

$app->post('/ricerca', function (Request $request, Response $response) {
    $db = new DBStudente();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $key = $requestData['key'];

    $responseData = $db->ricerca($key);//Risposta del DB
    $contatore = (count($responseData));
    //metto in un json e lo inserisco nella risposta del servizio REST
    $responseData['contatore'] = $contatore;
    $response->getBody()->write(json_encode(array("lista" => $responseData)));
    //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
    $newResponse = $response->withHeader('Content-type', 'application/json');
    return $newResponse; //Invio la risposta del servizio REST al client
});

$app->post('/segnalazione', function (Request $request, Response $response) {
    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $nome = $requestData['nome'];
    $cognome = $requestData['cognome'];
    $motivo = $requestData['motivo'];
    $contatto = $requestData['contatto'];
    $email = $requestData['email'];
    //Risposta del servizio REST
    $responseData = array();
    $emailSender = new EmailHelperAltervista();
    if ($emailSender->sendSegnalazione($nome, $cognome, $motivo, $contatto, $email)) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = "Segnalazione inviata"; //Messaggio di esito positivo
    } else {

        $responseData['error'] = true; //Campo errore = true
        $responseData['message'] = "impossibile inviare la segnalazione"; //Messaggio di esito negativo
    }
    return $response->withJson($responseData);
});

$app->post('/visualizzamateriaperid', function (Request $request, Response $response) {

    $db = new DBDocenti();

    $requestData = $request->getParsedBody();//Dati richiesti dal servizio REST
    $id = $requestData['id'];

    //Controllo la risposta dal DB e compilo i campi della risposta
    $responseData = $db->visualizzaMateriaPerid($id);
    $contatore = (count($responseData));
    if ($responseData != null) {
        $responseData['error'] = false; //Campo errore = false
        $responseData['message'] = 'Elemento visualizzato con successo'; //Messaggio di esiso positivo
        $responseData['contatore'] = $contatore;
        $response->getBody()->write(json_encode(array("nomi_materie" => $responseData)));
        //Definisco il Content-type come json, i dati sono strutturati e lo dichiaro al browser
        $newResponse = $response->withHeader('Content-type', 'application/json');
        return $newResponse; //Invio la risposta del servizio REST al client
    } else {
        $responseData['error'] = true; //Campo errore = false
        $responseData['message'] = 'Errore imprevisto';
        return $response->withJson($responseData);
    }
    //Invio la risposta del servizio REST al client
});

$app->run();

?>
