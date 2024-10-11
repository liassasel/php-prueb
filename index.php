<?php

//Verifica si se le ha pasado la URl como parámetro 
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    echo "Procesando la URL: " . htmlspecialchars($url) . "<br>";
}else {
    echo "Por favor, pasa la URL del evento como parametro en la barra de direcciones";
    exit(); //Se detiene la ejecución si no hay una URL
}

//Función para identificar la plataforma partiendo de la URL

function getPlatform($url) {
    if (strpos($url, 'vividseats.com') !== false) {
        return 'VividSeats';
    }elseif (strpos($url, 'seatgeek.com') !== false) {
        return 'SeatGeek';
    }else {
        return 'Desconocida';
    }
}

$platform = getPlatform($url); // Se identifica la plataforma
echo "Plataforma detectada: " . $platform . "<br>";

if ($platform == 'Desconocida') {
    echo "Error: La URL proporcionada no  es válida o no se reconoce la plataforma.";
    exit(); // Se detiene la ejecución si la plataforma es desconocida 

}

// Función para obtener el contenido HTML de la URL

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

$html = getHtml($url); // HTML de la página

// Función para procesar el HTML de una página de VividSeats y extraer sus datos (Entradas disponibles, fila y precio)

function  processVividSeats($html) {
    // Crea una instancia en el DOMDocument
    $dom = new DOMDocument();

    // Carga el HTML en DOMDocument y se usa "@" para suprimir los warnings por errores HTMl mal formateados
    @$dom->loadHTML($html);

    // Crea una instancia del DOMXPath para realizar búsquedas con expresiones XPath
    $xpath = new DOMXPath($dom);

}

// Aqui se debe analizar el HTML de la página VividSeats para encontrar elementos de la tablas que tiene los sectores, precios y filas

$tickets = $xpath->query('//*[@id="start-of-content"]/main/div[1]/div[2]/div[2]/section[2]/div[3]/ul/li');

if ($tickets->length > 0) {
    // Itera los resultados y se extrae la información

    foreach ($tickets as  $ticket) {
        //Extraer sector
        $sector = $xpath->query(".//span[@class='section']", $ticket);
        //Extraer fila 
        $row = $xpath->query(".//span[@class='row']", $ticket);
        //Extraer precio
        $price = $xpath->query(".//span[@class='price']", $ticket);

        //Muestra los datos extraídos

        echo "Sector: " . ($sector->length > 0 ? $sector[0]->nodeValue : "N/A") . "<br>";
        echo  "Fila: " . ($row->length > 0 ? $row[0]->nodeValue : "N/A") . "<br>";
        echo "Precio: " . ($price->length > 0 ? $price[0]->nodeValue : "N/A") . "<br>";
        echo "------------------------------<br>";

    }
}else {
    echo "No se encontraron entradas disponibles en la página de VividSeats.<br>";
}


// Se procesa el HTML según la plataforma detectada
if  ($platform == 'vividseats') {
    processVividSeats($html); // Se procesa para la página VividSeats

}


?>