<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Ángel Iglesias Préstamo" />
    <meta name="viewport" content ="width=device-width, initial scale=1.0" />
    <title>Ejercicio4 - PrecioDeLaPlata</title>
    <link rel="stylesheet" href="Ejercicio4.css">
</head>
<body>
    <h1>
        &#128640; Calculadora del precio de la plata
    </h1>

    <?php
        class PrecioPlata {
            // Datos de la petición que queremos realizar
            const APIKEY = "goldapi-7m4x5xtkwetrxyk-io";
            const METAL = "XAG"; // plata
            CONST MONEDA = "EUR"; // euros

            // Atributos
            private $fecha;
            private $url;
            private $contexto;
            private $datos;
            protected $precio;

            public function __construct() {
                // Obtenemos la fecha
                $this->fecha = $this->get_date();

                // URL generada con los datos anteriores
                $this->url = "https://www.goldapi.io/api/" 
                    .self::METAL ."/" 
                    .self::MONEDA . "/" 
                    .$this->fecha;

                // Establecemos la cabecera con nuestra API key
                $this->contexto = stream_context_create(array(
                    'http' => array(
                        'header' => 'x-access-token: ' .self::APIKEY
                    )
                ));

                // Obtenemos los datos tras procesar hacer la petición
                $this->datos = file_get_contents($this->url, false, $this->contexto);
                
                // Procesamos el JSON recibido
                $json = json_decode($this->datos);

                if (isset($json->price))
                    $this->precio = $json->price .' €';
                else
                    $this->precio = 'No hay DATOS';

                echo "
                <form action='#' method='post'> 
                    <h2> Quiero calcular el precio de la plata para el día: </h2>
    
                    <label for='fecha' hidden> Selector de fecha sobre el que calcular el precio de la plata </label>
                    <input type='date' name='date' id='fecha' />
                    <input type='submit' value='Calcular' />
    
                    <!-- Aquí mostraremos los resultados -->
                    <label for='result' hidden> RESULTADO: </label>
                    <input type='text' value='$this->precio' id='result' readonly />
                </form>";
            }

            private function get_date() {
                // Si se han enviado los resultados del formulario
                if (count($_POST) > 0 )
                    return $_POST["date"]; // obtenemos el valor de la fecha
            }
        }

        $precioPlata = new PrecioPlata();
    ?>
</body>
</html>