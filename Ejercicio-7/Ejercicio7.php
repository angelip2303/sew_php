<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Ángel Iglesias Préstamo" />
    <meta name="viewport" content ="width=device-width, initial scale=1.0" />
    <title>Ejercicio7 - Videoclub</title>
    <link rel="stylesheet" href="Ejercicio7.css">
</head>
<body>
    <?php
        session_start();

        if (!isset($_SESSION['es_sesion_iniciada']))
            $_SESSION['es_sesion_iniciada'] = false;

        class Videoclub {

            private $server_name;
            private $username;
            private $password;
            private $db;
            private $db_name;

            private $peliculas;

            public function __construct() {
                $this->server_name = 'localhost';
                $this->username = 'DBUSER2021';
                $this->password = 'DBPSWD2021';
                $this->db_name = 'VIDEOCLUB_DB';

                $this->peliculas = array();

                // Manejamos el menú
                if (count($_POST) > 0) {
                    foreach ($this->peliculas as $pelicula)
                        if(isset($_POST[$pelicula->referencia])) $this->alquilar($pelicula->referencia);

                    if(isset($_POST['iniciar_sesion'])) $this->iniciar_sesion();
                    if(isset($_POST['cerrar_sesion'])) $this->cerrar_sesion();
                }

                if ($_SESSION['es_sesion_iniciada'] === false)
                    $this->iniciar_sesion_gui();
            }

            // Ahora mismo los mensajes están deshabilitados: DEBUG
            private function mensaje_de_exito($mensaje) {
                // echo "<p>" .$mensaje ."</p>";
            }

            private function mensaje_de_error($mensaje, $error) {
                // echo "<p>" .$mensaje .$error ."</p>";
                // exit();
            }

            private function iniciar_sesion_gui() {
                echo "
                <form action='#' method='post'>
                    <h2>Iniciar sesión / Crear cuenta</h2>

                    <label for='nombre_usuario'>Nombre de usuario:</label>
                    <input type='text' id='nombre_usuario' name='nombre_usuario' />

                    <label for='contraseña'>Contraseña:</label>
                    <input type='password' id='contraseña' name='contraseña' />

                    <input type='submit' name='iniciar_sesion' value='Iniciar sesión' />
                </form>
                ";
            }

            private function iniciar_sesion() {
                $_SESSION['es_sesion_iniciada'] = true; // marcamos como que hemos iniciado sesión

                $this->menu_gui();
                
                $this->crear_db();
                $this->crear_tablas();
                $this->importar_datos();
                $this->crear_videoclub();
                $this->crear_cuenta();
            }

            private function cerrar_sesion() {
                $_SESSION['es_sesion_iniciada'] = false; // marcamos como que NO hemos iniciado sesión
            }

            private function menu_gui() {
                echo "
                <header>
                <h1>El Videoclub de Gelín</h1>
                    <form action='#' method='post'> 
                        <button type='submit' name='cerrar_sesion'> Cerrar sesión </button>
                    </form>
                </header>";
            }

            private function crear_db() {
                $this->db = new mysqli($this->server_name,
                                        $this->username,
                                        $this->password);

				$create_db = 'create DATABASE if not exists ' 
                                .$this->db_name 
                                .' collate utf8_spanish_ci';

				if ($this->db->query($create_db) === TRUE)
                    $this->mensaje_de_exito(
                        "Base de datos creada con éxito"
                    );
				else
                    $this->mensaje_de_error(
                        "ERROR creando la base de datos: ",
                        $this->db->error
                    );

				// Cerramos la conexión por el momento
				$this->db->close();
            }

            private function conectarse_db() {
                // Nos conectamos a la base de datos
				$this->db = new mysqli($this->server_name,
                                       $this->username,
                                       $this->password,
                                       $this->db_name);

                // Comprobamos el estado de la conexión
                if ($this->db->connect_error) // si detectamos algún tipo de error
                    $this->mensaje_de_error(
                        'ERROR de conexión: ',
                        $this->db->connect_error
                    );
                else
                    $this->mensaje_de_exito(
                        'Conexión establecida con éxito; HOST: '. $this->db->host_info
                    );
            }

            private function crear_tablas() {
                $this->conectarse_db();

                $tabla_clientes = "
                    create table if not exists Clientes(
                        dni       VARCHAR(9)   NOT NULL,
                        nombre    VARCHAR(32)  NOT NULL,
                        apellidos VARCHAR(64)  NOT NULL,
                        email     VARCHAR(255) NOT NULL,
                        telefono  VARCHAR(13)  NOT NULL,

                        primary key (dni)
                    )
                ";
                $this->check_es_tabla_creada_con_exito($tabla_clientes, 'clientes');

                $tabla_categorias = "
                    create table if not exists Categorias(
                        id   VARCHAR(9)   NOT NULL,
                        tipo VARCHAR(32)  NOT NULL,

                        primary key (id)
                    )
                ";
                $this->check_es_tabla_creada_con_exito($tabla_categorias, 'categorías');

                $tabla_peliculas = "
                    create table if not exists Peliculas(
                        referencia      VARCHAR(9)  NOT NULL,
                        titulo          VARCHAR(32) NOT NULL,
                        categoria_id    VARCHAR(64) NOT NULL,
                        director        VARCHAR(64) NOT NULL,
                        actor_principal VARCHAR(64) NOT NULL,
                        portada         VARCHAR(64),
                        ha_ganado_oscar int         NOT NULL,

                        primary key (referencia),
                        foreign key (categoria_id) REFERENCES Categorias(id)
                    )
                ";
                $this->check_es_tabla_creada_con_exito($tabla_peliculas, 'películas');

                $tabla_alquiler = "
                    create table if not exists Alquiler(
                        cliente_dni         VARCHAR(9)   NOT NULL,
                        pelicula_referencia VARCHAR(9)  NOT NULL,
                        dia_alquilada       DATE NOT NULL,
                        dia_devuelta        DATE,

                        foreign key (cliente_dni)         REFERENCES Clientes(dni),
                        foreign key (pelicula_referencia) REFERENCES Peliculas(referencia),
                        unique(cliente_dni, pelicula_referencia)
                    )
                ";
                $this->check_es_tabla_creada_con_exito($tabla_alquiler, 'alquiler');
            }

            private function check_es_tabla_creada_con_exito($tabla, $tabla_id) {
                if($this->db->query($tabla) === true)
                    $this->mensaje_de_exito(
                        "Tabla $tabla_id creada con éxito"
                    );
				else
                    $this->mensaje_de_error(
                        "ERROR creando la tabla $tabla_id: ",
                        $this->db->error
                    );
            }

            private function importar_datos() {
                $this->importar_clientes();
                $this->importar_categorias();
                $this->importar_peliculas();
                $this->importar_alquileres();
            }
            
            private function importar_clientes() {
                // Importamos los archivos de la tabla CLIENTES
                $this->conectarse_db();

                $archivo = fopen('clientes.csv', "r");
                    
                while(($datos = fgetcsv($archivo, 1000, ";")) !== FALSE) {
                    $query = $this->db->prepare("
                        INSERT INTO Clientes 
                            (dni,
                             nombre,
                             apellidos,
                             email,
                             telefono)
                        VALUES 
                            (?, ?, ?, ?, ?)");
                    
                    $query->bind_param('sssss', $datos[0],
                                                $datos[1],
                                                $datos[2],
                                                $datos[3],
                                                $datos[4]);
                
                    $query->execute();
                    $query->close();
                }

                // IMPORTANTE: cerrar el archivo
                fclose($archivo);

                $this->db->close();
            }

            private function importar_categorias() {
                $this->conectarse_db();

                // Importamos los archivos de la tabla CLIENTES
                $archivo = fopen('categorias.csv', "r");
                    
                while(($datos = fgetcsv($archivo, 1000, ";")) !== FALSE) {
                    $query = $this->db->prepare("
                        INSERT INTO Categorias 
                            (id,
                             tipo)
                        VALUES 
                            (?, ?)");
                    
                    $query->bind_param('ss', $datos[0],
                                            $datos[1]);
                
                    $query->execute();
                    $query->close();
                }

                // IMPORTANTE: cerrar el archivo
                fclose($archivo);

                $this->db->close();
            }

            private function importar_peliculas() {
                // Importamos los archivos de la tabla CLIENTES
                $this->conectarse_db();

                $archivo = fopen('peliculas.csv', "r");
                    
                while(($datos = fgetcsv($archivo, 1000, ";")) !== FALSE) {
                    $query = $this->db->prepare("
                        INSERT INTO Peliculas 
                            (referencia,
                             titulo,
                             categoria_id,
                             director,
                             actor_principal,
                             portada,
                             ha_ganado_oscar) 
                        VALUES 
                            (?, ?, ?, ?, ?, ?, ?)");

                    $query->bind_param('ssssssi', $datos[0],
                                                 $datos[1],
                                                 $datos[2],
                                                 $datos[3],
                                                 $datos[4],
                                                 $datos[5],
                                                 $datos[6]);
                
                    // Añadimos la película a nuestra lista...
                    $this->peliculas[] = new Pelicula (
                        $datos[0],
                        $datos[1],
                        $datos[2],
                        $datos[3],
                        $datos[4],
                        $datos[5],
                        $datos[6]
                    ); 

                    $query->execute();
                    $query->close();
                }

                // IMPORTANTE: cerrar el archivo
                fclose($archivo);

                $this->db->close();
            }

            private function importar_alquileres() {
                $this->conectarse_db();

                // Importamos los archivos de la tabla CLIENTES
                $archivo = fopen('alquileres.csv', "r");
                    
                while(($datos = fgetcsv($archivo, 1000, ";")) !== FALSE) {
                    $query = $this->db->prepare("
                        INSERT INTO Alquiler 
                            (cliente_dni,
                             pelicula_referencia,
                             dia_alquilada,
                             dia_devuelta)
                        VALUES 
                            (?, ?, ?, ?)");
                    
                    $query->bind_param('ssii', $datos[0],
                                               $datos[1],
                                               $datos[2],
                                               $datos[3]);
                
                    $query->execute();
                    $query->close();
                }

                // IMPORTANTE: cerrar el archivo
                fclose($archivo);

                $this->db->close();
            }

            private function crear_videoclub() {
                echo "<ul>";

                foreach($this->peliculas as $pelicula) {
                    echo "
                        <li>
                            <h3> $pelicula->titulo </h3>
                            <h4> $pelicula->director </h4>
                            <img src='$pelicula->portada' alt='$pelicula->titulo'/>
                            <p> $pelicula->actor_principal </p>

                            <form action='#' method='post'>
                                <input type='submit' name='$pelicula->referencia' value='Alquilar' />
                            </form>
                        </li>
                    ";
                }

                echo "</ul>";
            }

            private function crear_cuenta() {
                
            }

            private function alquilar($referencia) {
                $this->conectarse_db();

                $query = $this->db->prepare("
                    INSERT INTO Alquiler 
                        (cliente_dni,
                         pelicula_referencia,
                         dia_alquilada,
                         dia_devuelta)
                    VALUES 
                        (?, ?, ?, ?)");
                    
                $query->bind_param('ssii', $datos[0],
                                           $referencia,
                                           date('Y-m-d'),
                                           '');
            
                $query->execute();
                $query->close();

                $this->db->close();
            }

        }

        class Pelicula {

            public $referencia;
            public $titulo;
            public $categoria_id;
            public $director;
            public $actor_principal;
            public $portada;
            public $ha_ganado_oscar;

            public function __construct($referencia,
                                        $titulo,
                                        $categoria_id,
                                        $director,
                                        $actor_principal,
                                        $portada,
                                        $ha_ganado_oscar) {
                $this->referencia = $referencia;
                $this->titulo = $titulo;
                $this->categoria_id = $categoria_id;
                $this->director = $director;
                $this->actor_principal = $actor_principal;
                $this->portada = $portada;
                $this->ha_ganado_oscar = $ha_ganado_oscar;        
            }

        }

        $videoclub = new Videoclub();
    ?>
</body>
</html>