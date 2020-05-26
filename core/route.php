<?php
// Met de route functie wordt bepaald welke controller en welke action er moet worden ingeladen
function route()
{
	// Hier wordt de functie aangeroepen die de URL op splitst op het standaard seperatie teken (in PHP is dit een /)
	$url = splitUrl();
	//Hier wordt gecontroleerd of er een bestand bestaat met opgebouwde variables uit de splitUrl functie
	if (file_exists(ROOT . 'controller/' . $url['controller'] . '.php')) {
		require(ROOT . 'controller/' . $url['controller'] . '.php');
		// Vervolgens wordt er gekeken of er een functie met de naam bestaat die in de key action zit. 
		// Bijvoorbeeld: http://localhost/Students/Edit/1, dan is de action Edit. 
		// De 1 wordt als eerste 'params' geplaatst
		// In de controller Students wordt gekeken of de function Edit bestaat.
		if (function_exists($url['action'])) {
			// Wanneer die bestaat wordt er gekeken of je parameters hebt meegegeven bestaan. Als die bestaan worden die aan de functie meegegeven
			if ($url['params']) {
				call_user_func_array($url['action'], $url['params']);
			} else {
				// Als ze niet bestaan, wordt alleen de functie uitgevoerd
				call_user_func($url['action']);
			}
		} else {
			// Wanneer de action niet bestaat, wordt de errorpagina getoond
			require(ROOT . 'controller/ErrorController.php');
			call_user_func('error_404');
		}
	} else {
		// Wanneer het bestand niet bestaat weten we dat de gebruiker een verkeerde URL heeft ingevuld, enn geven we een foutmelding
		require(ROOT . 'controller/ErrorController.php');
		call_user_func('error_404');
	}
}

// De in de functie Route aangeroepen functie splitUrl
function splitUrl()
{
	// Als er iets in de key url zit van $_GET, wordt de code uitgevoerd
	if ($start_url = getRequestedPath()) {
		// Met trim haal je de zwevende shlashes weg. Bijvoorbeeld:
		// /Students/Edit/1/ wordt Students/Edit/1
		$tmp_url = trim($start_url , "/");
	

		// Dit haalt de vreemde karakters uit de strings weg
		$tmp_url = filter_var($tmp_url, FILTER_SANITIZE_URL);

		// Met explode splits je een string op. Elk gedeelte voor de "/" wordt in een nieuwe index van een array gestopt.
		// Bijvoorbeeld /Students/Edit/1 wordt opgedeeld in: 
		// $temp_url[0] = "Students",
		// $temp_url[1] = "Edit",
		// $temp_url[2] = "1"
		$tmp_url = explode("/", $tmp_url);

		// Hier worden op basis van de eerder opgegeven variable $tmp_url de keys controller en action gevuld
        // Er wordt een variable opgemaakt uit de URL, de eerste variabele wordt geplaatst in de key controller, de tweede wordt in de key action geplaatst.
		$url['controller'] = isset($tmp_url[0]) ? ucwords($tmp_url[0] . 'Controller') : null;
		$url['action'] = isset($tmp_url[1]) ? $tmp_url[1] : 'index';

		// Die twee waarden worden uit de array gehaald
		unset($tmp_url[0], $tmp_url[1]);

		// De overige variabelen worden in de key params gestopt
		$url['params'] = array_values($tmp_url);

		// Dit wordt teruggegeven aan de functie
		return $url;	
	}	
}

// Simpele fix voor NGINX gebruikers
function getRequestedPath(){
	// Controleer of de URL herschreven is
	if(isset($_GET['url'])) {
        // Zo ja, geef de gehele url terug
        return $_GET['url'];
    } else {
	    // zo nee, geef de standaard url terug.
        return DEFAULT_CONTROLLER . '/index';
    }
}
