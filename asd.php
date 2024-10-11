<?php

/*
La URL debe pasarse como parámetro 'url' a través de la barra de direcciones del navegador 
Ejemplo: http://localhost/evento-analisis/index.php?url=https://www.vividseats.com/...
*/

// Verifica si se ha pasado la URL como parámetro 
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    echo "Procesando la URL: " . htmlspecialchars($url) . "<br>";
} else {
    echo "Por favor, pasa la URL del evento como parámetro en la barra de direcciones";
    exit(); // Se detiene la ejecución si no hay una URL
}

// Función para identificar la plataforma partiendo de la URL
function getPlatform($url) {
    if (strpos($url, 'vividseats.com') !== false) {
        return 'VividSeats';
    } elseif (strpos($url, 'seatgeek.com') !== false) {
        return 'SeatGeek';
    } else {
        return 'Desconocida';
    }
}

$platform = getPlatform($url); // Se identifica la plataforma
echo "Plataforma detectada: " . $platform . "<br>";

if ($platform == 'Desconocida') {
    echo "Error: La URL proporcionada no es válida o no se reconoce la plataforma.";
    exit(); // Se detiene la ejecución si la plataforma es desconocida 
}

// Función para obtener el contenido HTML de la URL

/*

Al utilizar este método sale el error '403 Forbidden' cual sale por que se esta trabajando con file_get_contents
y el servidor esta bloqueando la solicitud ya que se esta realizando la peticion desde un script en lugar de un navegador web
para solucionar la misma se implementara una alternativa con cURL que simulara la petición de un navegador, lo que nos permitirá 
obtener el contenido HTML de la página sin bloqueos.


function getHtml($url) {
    // Se intenta obtener el HTML de la página
    $html = file_get_contents($url);

    if ($html === false) {
        echo "Error: No se pudo obtener el contenido de la página";
        exit(); // Se detiene la ejecución si hay un error
    }
    echo "El HTML de la página obtenido  con éxito.<br>";
    return $html; //Devuelve el contenido del HTML

}
    */

// Función para obtener el contenido HTML de la URL usando cURL
function getHtml($url) {
    // Inicializa cURL
    $ch = curl_init();

    // Se establecen las opciones del cURL
    curl_setopt($ch, CURLOPT_URL, $url); // URL a la que se hará la solicitud 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve el resultado como un string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecciones, si las hay

    // Simula un navegador estableciendo el User-Agent
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36');

    // Ejecuta la solicitud
    $html = curl_exec($ch);

    // Verificación de errores
    if (curl_errno($ch)) {
        echo "Error: No se pudo obtener el contenido de la página. " . curl_error($ch);
        curl_close($ch);
        exit();
    }

    // Cierra la conexión con cURL
    curl_close($ch);

    return $html; // Devuelve el HTML de la página
}

$html = getHtml($url); // Obtiene el HTML de la página usando cURL

// Función para procesar el HTML de una página de VividSeats y extraer los datos (entradas, fila, precio)
function processVividSeats($html) {
    // Crea una instancia de DOMDocument
    $dom = new DOMDocument();

    // Carga el HTML en DOMDocument (suprime warnings con @)
    @$dom->loadHTML($html);

    // Crea una instancia de DOMXPath para realizar búsquedas con expresiones XPath
    $xpath = new DOMXPath($dom);

    // Busca los elementos de los tickets según la estructura mencionada
    $tickets = $xpath->query('/html/body/div[1]/div[3]/div[2]/div[2]/div[2]/div[1]/div');

    if ($tickets->length > 0) {
        // Itera sobre los resultados y extrae la información
        foreach ($tickets as $ticket) {
            // Extraer sector
            $sector = $xpath->query(".//span[@class='section']", $ticket);
            // Extraer fila
            $row = $xpath->query(".//span[@class='row']", $ticket);
            // Extraer precio
            $price = $xpath->query(".//span[@class='price']", $ticket);

            // Mostrar los datos extraídos
            echo "Sector: " . ($sector->length > 0 ? $sector[0]->nodeValue : "N/A") . "<br>";
            echo "Fila: " . ($row->length > 0 ? $row[0]->nodeValue : "N/A") . "<br>";
            echo "Precio: " . ($price->length > 0 ? $price[0]->nodeValue : "N/A") . "<br>";
            echo "------------------------------<br>";
        }
    } else {
        echo "No se encontraron entradas disponibles en la página de VividSeats.<br>";
    }
}

// Procesa el HTML según la plataforma detectada
if ($platform == 'VividSeats') {
    processVividSeats($html); // Se procesa para la página VividSeats
}

?>
