<?php

class DBUtenti
{
    //Variabili di classe
    private $connection;
    private $tabelleDB = [ //Array di tabelle del db
        "utente", //0
        "categoria", //1
        "scadenza" //2
    ];

    private $campiTabelleDB = [ //Campi delle tabelle (array bidimensionale indicizzato con key)
        "utente" => [
            "codice_utente",
            "email",
            "password",
            "nome",
            "cognome",
            "attivo"
        ],
        "categoria" => [
            "codice_categoria",
            "nome_categoria"
        ],
        "scadenza" => [
            "codice_scadenza",
            "nome",
            "data_ricezione",
            "data_scadenza",
            "periodo",
            "cod_categoria",
            "cod_utente",
            "importo",
            "confermato"
        ]
    ];

    //Costruttore
    public function __construct()
    {
        //Setup della connessione con il DB
        $db = new DBConnectionManager();
        $this->connection = $db->runConnection();
    }

    //---- METODI PER GESTIRE LE QUERY ----

    //Funzione di accesso
    public function login($email, $password)
    {
        $utenteTab = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$utenteTab];

        $query = (
            "SELECT " .
            $campi[0] . ", " .
            $campi[1] . " " .

            "FROM " .
            $utenteTab . " " .
            "WHERE " .
            $campi[1] . " = ? AND " .
            $campi[2] . " = ? "
        );

        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $email, $password); //ss se sono 2 stringhe, ssi 2 string e un int (sostituisce ? della query)
        $stmt->execute();
        echo $query; //stampa query
        //Ricevo la risposta del DB
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_utente, $email);
            $utente = array();

            while ($stmt->fetch()) {
                $temp = array();
                $temp[$campi[0]] = $codice_utente;
                $temp[$campi[1]] = $email;
                array_push($utente, $temp);
            }

            return $utente;

        } else { //Se non ci sono risultati
            return null;
        }
    }

    //Funzione di recupero - Controlla se esiste la mail di cui recuperare la psw
    public function recupero($email)
    {
        $utenteTab = $this->tabelleDB[0]; //Tabella per la query
        $campi = $this->campiTabelleDB[$utenteTab];
        /*  query: "SELECT email FROM utente WHERE email = ? */
        $query = (
            "SELECT " .
            $campi[1] . " " .
            "FROM " .
            $utenteTab . " " .
            "WHERE " .
            $campi[1] . " = ? "
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        //Ricevo la risposta del DB
        $stmt->store_result();
        //Controllo se ha trovato matching tra dati inseriti e campi del db
        return $stmt->num_rows > 0;
    }

    // Funzione conferma Profilo
    public function confermaProfilo($email, $cod_utente)
    {
        $tabella = $this->tabelleDB[0];

        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE utente SET attivo = true WHERE cod_utente = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[5] . " = 1 " .
            "WHERE " .
            $campi[0] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $cod_utente); //s se è 1 stringhe(sostituisce ? della query)
        return ($stmt->execute());
    }

    // Funzione registrazione
    public function registrazione($email, $password, $nome, $cognome)
    {
        $tabella = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$tabella];

        $attivo = 0;

        $query = (
            "INSERT INTO " .
            $tabella . " (" .
            $campi[1] . ", " .
            $campi[2] . ", " .
            $campi[3] . ", " .
            $campi[4] . ", " .
            $campi[5] . ") " .              //mette in automatico attivo a 0

            "VALUES (?,?,?,?,?)"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssssi", $email, $password, $nome, $cognome, $attivo); //ss se sono 2 stringhe, ssi 2 string e un int (sostituisce ? della query)
        $result = ($stmt->execute()) /*? 1 : 2   CONTROLLARE SE SERVE O MENO*/
        ;
        return $result;
    }

    //Funzione rimuovi scadenza
    public function rimuoviScadenza($codice_scadenza)
    {
        $tabella = $this->tabelleDB[2]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        //query:  " DELETE FROM SCADENZA WHERE ID = $codice_scadenza"

        $query = (
            "DELETE FROM " .
            $tabella . " WHERE " .
            $campi[0] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_scadenza);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    //Funzione conferma scadenza
    public function confermaPagamentoScadenza($codice_scadenza)
    {
        $tabella = $this->tabelleDB[2]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];

        //UPDATE Person SET given_names = 'Stefano' WHERE ID = 4

        $query = (
            "UPDATE " .
            $tabella .
            " SET " .
            $campi[8] . " = 1 " .
            " WHERE " .
            $campi[0] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_scadenza);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    //Funzione conferma scadenza
    public function annullaPagamentoScadenza($codice_scadenza)
    {
        $tabella = $this->tabelleDB[2]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];

        //UPDATE Person SET given_names = 'Stefano' WHERE ID = 4

        $query = (
            "UPDATE " .
            $tabella .
            " SET " .
            $campi[8] . " = 0 " .
            " WHERE " .
            $campi[0] . " = ? "
        );

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_scadenza);
        $result = $stmt->execute();
        $stmt->store_result();

        return $result;
    }

    //Funzione visualizza scadenze del determinato utente in ordine cronologico
    public function visualizzaScadenzaPerData($cod_utente)
    {
        $tabella = $this->tabelleDB[2]; //Tabella per la query (Scadenza)
        $tabella1 = $this->tabelleDB[1]; //Tabella per la query (Categ)

        $campi = $this->campiTabelleDB[$tabella];
        $campi1 = $this->campiTabelleDB[$tabella1];

        //query= SELECT nome,data_ric,data_scad,categ,periodo,importo FROM scadenza WHERE cod_utente=$cod_utente
        $query = (
            "SELECT " .
            $campi[1] . ", " .
            $campi[2] . ", " .
            $campi[3] . ", " .
            $campi[4] . ", " .
            $campi[5] . ", " .
            $campi[7] . " " .
            "FROM " .
            $tabella . " " .
            "WHERE " .
            $campi[6] . " = ? " .
            "ORDER BY " .
            $campi[3] /*. //in ordine crescente in base alla data di scadenza
            " UNION " .
            "SELECT " .
            $campi1[1] . ", " .
            NULL . ", " .
            NULL . ", " .
            NULL . ", " .
            NULL . ", " .
            NULL . " " .
            "FROM " .
            $tabella1 . " " .
            "WHERE " .
            $campi[5] . " = " .
            $campi1[0]*/
        );

        echo $query;

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $cod_utente);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "num_rows > 0";
            //Salvo il risultato della query in alcune variabili che andranno a comporre l'array temp //
            $stmt->bind_result($nome, $data_ricezione, $data_scadenza, $periodo, $nome_categoria, $importo);
            $scadenza = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query

                $temp = array(); //Array temporaneo per l'acquisizione dei dati

                //Indicizzo con key i dati nell'array
                $temp[$campi[1]] = $nome;
                $temp[$campi[2]] = $data_ricezione;
                $temp[$campi[3]] = $data_scadenza;
                $temp[$campi[4]] = $periodo;
                $temp[$campi[5]] = $nome_categoria;
                $temp[$campi[7]] = $importo;

                array_push($scadenza, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $scad
            }
            return $scadenza; //ritorno array scad riempito con i risultati della query effettuata.
        } else {
            return null;
        }
    }

    public function modificaPassword($email, $password)
    {
        $password = hash('sha256', $password);
        $tabella = $this->tabelleDB[0];

        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE TABLE SET password = ? WHERE email = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[2] . " = ? " .
            "WHERE " .
            $campi[1] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ss", $password, $email);
        return $stmt->execute();
    }

    public function visualizzaNomeUtente($codice_utente)
    {
        $tabella = $this->tabelleDB[0];
        $campi = $this->campiTabelleDB[$tabella];
        $query = //query: "SELECT id, nome FROM utenti"
            "SELECT " .
            $campi[3] . " " .
            "FROM " .
            $tabella . " " .
            "WHERE " . $campi[0] . " = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_utente);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($nome);

            $utenti = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array(); //Array temporaneo per l'acquisizione dei dati
                //Indicizzo con key i dati nell'array
                $temp[$campi[3]] = $nome;
                array_push($utenti, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $cdl
            }
            return $utenti;
        } else return null;
    }

    public function visualizzaPagamento($codice_scadenza)
    {
        $tabella = $this->tabelleDB[2];
        $campi = $this->campiTabelleDB[$tabella];
        $query = //query: "SELECT id, nome FROM utenti"
            "SELECT " .
            $campi[8] . " " .
            "FROM " .
            $tabella . " " .
            "WHERE " . $campi[0] . " = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_scadenza);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($scadenz);

            $scadenza = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array(); //Array temporaneo per l'acquisizione dei dati
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $scadenz;
                array_push($scadenza, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $cdl
            }
            return $scadenza;
        } else return null;
    }

    public function modificaScadenza($codice_scadenza, $nome, $data_ricezione, $data_scadenza, $periodo, $importo)
    {
        $tabella = $this->tabelleDB[2];
        $campi = $this->campiTabelleDB[$tabella];
        //query:  "UPDATE TABLE SET nome = ?, data_ricezione = ?, data_scadenza = ? WHERE  = ?"
        $query = (
            "UPDATE " .
            $tabella . " " .
            "SET " .
            $campi[1] . " = ?, " .
            $campi[2] . " = ?, " .
            $campi[3] . " = ? " .
            $campi[4] . " = ? " .
            $campi[7] . " = ? " .
            "WHERE " .
            $campi[0] . " = ?"
        );
        //Invio la query
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("isssif", $codice_scadenza, $nome, $data_ricezione, $data_scadenza, $periodo, $importo);

        $result = $stmt->execute();

        //Controllo se ha trovato matching tra dati inseriti e campi del db
        return $result;
    }

    public function inserisciScadenza($nome, $data_ricezione, $data_scadenza, $periodo, $cod_categoria, $cod_utente, $importo)
    {
        $tabella = $this->tabelleDB[2]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        $query = (
            "INSERT INTO  " .
            $tabella . " ( " .

            $campi[1] . ", " .
            $campi[2] . ", " .
            $campi[3] . ", " .
            $campi[4] . ", " .
            $campi[5] . ", " .
            $campi[6] . ", " .
            $campi[7] . " ) " .

            "VALUES (?,?,?,?,?,?,?)"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("sssiiif", $nome, $data_ricezione, $data_scadenza, $periodo, $cod_categoria, $cod_utente, $importo);
        return $stmt->execute();
    }

    /*PER LA LOGOUT, NON SERVE IL SERVIZIO NEL BACK-END MA DIRETTAMENTE DALLA FOLDER DELL'APP
    * <?php
    session_start();
    session_destroy();
    header("location: ./login.php");
    ?>*/
    //VISUALIZZA CATEOGORIE
    public function visualizzaCategorie()
    {
        $tabella = $this->tabelleDB[1]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        $query = //query: "SELECT nome, FROM categoria
            "SELECT " .
            $campi[0] . ", " .
            $campi[1] . " " .
            "FROM " .
            $tabella . " ";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($codice_categoria, $nome_categoria);
            $categorie = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[0]] = $codice_categoria;
                $temp[$campi[1]] = $nome_categoria;
                array_push($categorie, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $categorie
            }
            return $categorie; //ritorno array $categorie riempito con i risultati della query effettuata.
        } else {
            return null;
        }
    }

    //VISUALIZZA SCADENZE PER CATEGORIA
    public function visualizzaScadenzePerCategoria($Categoria)
    {
        $scadenzeTab = $this->tabelleDB[2];
        $campiScadenza = $this->campiTabelleDB[$scadenzeTab];
        $categorieTab = $this->tabelleDB[1];
        $campiCategoria = $this->campiTabelleDB[$categorieTab];
        //query: SELECT scadenza.nome,scadenza.data_ricezione,scadenza.data_scadenza,scadenza.periodo, scadenza.importo,scadenza.confermato FROM scadenza Inner join categoria ON scadenza.cod_categoria = categoria.codice_categoria Where categoria.nome_categoria = "?" AND categoria.codice_categoria=scadenza.cod_materia"
        $query = (
            "SELECT " .
            $scadenzeTab . "." . $campiScadenza[1] . ", " .
            $scadenzeTab . "." . $campiScadenza[2] . ", " .
            $scadenzeTab . "." . $campiScadenza[3] . ", " .
            $scadenzeTab . "." . $campiScadenza[4] . ", " .
            $scadenzeTab . "." . $campiScadenza[7] . ", " .
            $scadenzeTab . "." . $campiScadenza[8] . " " .
            "FROM " . $scadenzeTab . " " .
            "Inner join " .
            $categorieTab . " " .
            "ON " .
            $scadenzeTab . "." . $campiScadenza[5] . " = " .
            $categorieTab . "." . $campiCategoria[0] .
            " WHERE " . $categorieTab . "." . $campiCategoria[1] . '= ? '
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $Categoria);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($nome, $data_ric, $data_scad, $periodo, $importo, $confermato);
            $scadenza = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campiScadenza[1]] = $nome;
                $temp[$campiScadenza[2]] = $data_ric;
                $temp[$campiScadenza[3]] = $data_scad;
                $temp[$campiScadenza[4]] = $periodo;
                $temp[$campiScadenza[7]] = $importo;
                $temp[$campiScadenza[8]] = $confermato;
                array_push($scadenza, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $scadenza
            }
            return $scadenza; //ritorno array $scadenza riempito con i risultati della query effettuata.
        } else {
            return null;
        }
    }

    public function visualizzaImportoPerCodice($codice_scadenza){
            $tabella = $this->tabelleDB[2]; //Tabella per la query
            $campi = $this->campiTabelleDB[$tabella];
        /*  query: "SELECT importo FROM scadenza WHERE codice_scadenza = ?" */
            $query = (
                "SELECT " .
                $campi[7] . " " .
                "FROM " .
                $tabella . " " .
                "WHERE " .
                $campi[0] . " = ?"
            );
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $codice_scadenza);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($importo);
                $importi = array();
                while ($stmt->fetch()) { //Scansiono la risposta della query
                    $temp = array();
                    //Indicizzo con key i dati nell'array
                    $temp[$campi[7]] = $importo;
                    array_push($importi, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $profilo
                }
                return $importi;
            } else {
                return null;
            }
        }


    public function visualizzaPeriodoPerCodice($codice_scadenza){
        $tabella = $this->tabelleDB[2]; //Tabella per la query
        $campi = $this->campiTabelleDB[$tabella];
        /*  query: "SELECT periodo FROM scadenza WHERE codice_scadenza = ?" */
        $query = (
            "SELECT " .
            $campi[4] . " " .
            "FROM " .
            $tabella . " " .
            "WHERE " .
            $campi[0] . " = ?"
        );
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $codice_scadenza);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($periodo);
            $periodi = array();
            while ($stmt->fetch()) { //Scansiono la risposta della query
                $temp = array();
                //Indicizzo con key i dati nell'array
                $temp[$campi[4]] = $periodo;
                array_push($periodi, $temp); //Inserisco l'array $temp all'ultimo posto dell'array $profilo
            }
            return $periodi;
        } else {
            return null;
        }
    }

}

?>
