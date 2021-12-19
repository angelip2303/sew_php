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

        echo "
        <header>
        <h1>El Videoclub de Gelín</h1>
            <form action='#' method='post'> 
                <button type='submit' name='iniciar_sesion'> Iniciar sesión </button>
                <button type='submit' name='cerrar_sesion'> Cerrar sesión </button>
                <button type='submit' name='devolver_pelicula'> Devolver película </button>
                <button type='submit' name='filtrar_peliculas'> Filtrar películas </button>
            </form>
        </header>";

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

                $_SESSION['peliculas'] = array();
                $_SESSION['categorias'] = array();

                // MANEJAMOS la SESIÓN
                if (!isset($_SESSION['es_sesion_iniciada']))
                    $_SESSION['es_sesion_iniciada'] = false;
                    
                if (!isset($_SESSION['dni_usuario_logged']))
                    $_SESSION['dni_usuario_logged'] = '';

                if (!isset($_SESSION['hay_que_crear_cuenta']))
                    $_SESSION['hay_que_crear_cuenta'] = false;

                // Manejamos la pila a través de la sesión
                if (!isset($_SESSION['filtrar_peliculas']))
                    $_SESSION['filtrar_peliculas'] = false;

                // MANEJAMOS el menú
                if (count($_POST) > 0) {
                    if (isset($_POST['iniciar_sesion'])) 
                        $this->iniciar_sesion_gui();
                    if (isset($_POST['cerrar_sesion']))
                        $this->cerrar_sesion();
                    if (isset($_POST['iniciar_sesion_form']))
                        $this->iniciar_sesion();
                    if (isset($_POST['devolver_pelicula']))
                        $this->devolver_pelicula_gui();
                    if (isset($_POST['devolver_pelicula_form']))
                        $this->devolver_pelicula(); 
                    if (isset($_POST['crear_cuenta']))
                        $this->crear_cuenta();
                    if (isset($_POST['filtrar_peliculas']))
                        $this->filtrar_peliculas();
                }

                // Inicializamos todo
                $this->init();

                // MANEJAMOS los alquileres
                if (count($_POST) > 0)
                    foreach ($_SESSION['peliculas'] as $pelicula)
                        if (isset($_POST[$pelicula->referencia])) 
                            $this->alquilar($pelicula->referencia);
            }

            private function init() {
                // Inicilizamos la DB
                $this->crear_db();
                $this->crear_tablas();
                $this->importar_datos();

                // Inicializamos la aplicación
                $this->usuario_gui();
                $this->peliculas_gui();
            }

            // +--------------------+
            // |    -*- misc. -*-   |
            // +--------------------+

            private function mensaje_de_exito($mensaje) {
                echo "<p>" .$mensaje ."</p>";
            }

            private function mensaje_de_error($mensaje, $error) {
                echo "<p>" .$mensaje .$error ."</p>";
                exit();
            }
            
            // +-----------------+
            // |    -*- db -*-   |
            // +-----------------+

            // Ahora mismo los mensajes están deshabilitados: DEBUG
            private function conectarse_db() {
                // Nos conectamos a la base de datos
				$this->db = new mysqli($this->server_name,
                                       $this->username,
                                       $this->password,
                                       $this->db_name);

                // Comprobamos el estado de la conexión
                // if ($this->db->connect_error) // si detectamos algún tipo de error
                //     $this->mensaje_de_error(
                //         'ERROR de conexión: ',
                //         $this->db->connect_error
                //     );
                // else
                //     $this->mensaje_de_exito(
                //         'Conexión establecida con éxito; HOST: '. $this->db->host_info
                //     );
            }

            private function crear_db() {
                $this->db = new mysqli($this->server_name,
                                        $this->username,
                                        $this->password);

				$create_db = 'create DATABASE if not exists ' 
                                .$this->db_name 
                                .' collate utf8_spanish_ci';

                $this->db->query($create_db);
                // Voy a desactivar los mensajes de DEBUG
				// if ($this->db->query($create_db) === TRUE)
                //     $this->mensaje_de_exito(
                //         "Base de datos creada con éxito"
                //     );
				// else
                //     $this->mensaje_de_error(
                //         "ERROR creando la base de datos: ",
                //         $this->db->error
                //     );

				// Cerramos la conexión por el momento
				$this->db->close();
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
                    create table if not exists Alquileres(
                        cliente_dni         VARCHAR(9) NOT NULL,
                        pelicula_referencia VARCHAR(9) NOT NULL,
                        dia_alquilada       DATETIME   NOT NULL,
                        dia_devuelta        DATETIME,

                        foreign key (cliente_dni)         REFERENCES Clientes(dni),
                        foreign key (pelicula_referencia) REFERENCES Peliculas(referencia),
                        unique(cliente_dni, pelicula_referencia)
                    )
                ";
                $this->check_es_tabla_creada_con_exito($tabla_alquiler, 'Alquileres');
            }

            private function check_es_tabla_creada_con_exito($tabla, $tabla_id) {
                $this->db->query($tabla);
                // Voy a desactivar los mensajes de DEBUG
                // if($this->db->query($tabla) === true)
                //     $this->mensaje_de_exito(
                //         "Tabla $tabla_id creada con éxito"
                //     );
				// else
                //     $this->mensaje_de_error(
                //         "ERROR creando la tabla $tabla_id: ",
                //         $this->db->error
                //     );
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
                
                    // Añadimos la categoría
                    $_SESSION['categorias'][] = new Categoria($datos[0], $datos[1]);

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
                    $_SESSION['peliculas'][] = new Pelicula (
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
                    $fecha_alquiler = $datos[2];
                    $fecha_devuelta = $datos[3];
                    
                    $query = $this->db->prepare("
                        INSERT INTO Alquileres 
                            (cliente_dni,
                             pelicula_referencia,
                             dia_alquilada,
                             dia_devuelta)
                        VALUES 
                            (?, 
                             ?, 
                             FROM_UNIXTIME($fecha_alquiler), 
                             FROM_UNIXTIME($fecha_devuelta))");
                    
                    $query->bind_param('ss', $datos[0],
                                             $datos[1]);
                
                    $query->execute();
                    $query->close();
                }

                // IMPORTANTE: cerrar el archivo
                fclose($archivo);

                $this->db->close();
            }

            // +------------------------+
            // |    -*- videoclub -*-   |
            // +------------------------+

                // --> GUI

            private function devolver_pelicula_gui() {
                echo "
                <form action='#' method='post'>
                    <h2>Devolver película</h2>

                    <label for='devolver_pelicula_id'>ID de la película:</label>
                    <input type='text' id='devolver_pelicula_id' name='devolver_pelicula_id' />

                    <input type='submit' name='devolver_pelicula_form' value='Devolver película' />
                </form>
                ";
            }

            private function pelicula_gui($pelicula) {
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

            private function peliculas_gui() {
                // Tenemos que mostrar las películas filtradas por categorías
                if ($_SESSION['filtrar_peliculas']) {
                    foreach ($_SESSION['categorias'] as $categoria) {
                        echo "<h2> $categoria->tipo </h2>";

                        echo "<ul>";

                        foreach($_SESSION['peliculas'] as $pelicula)
                            if ($pelicula->categoria_id === $categoria->id)
                                $this->pelicula_gui($pelicula);
                        
                        echo "</ul>";
                    }
                } else {
                    // mostramos todas las películas juntas
                    echo "<ul>";
                    
                    foreach($_SESSION['peliculas'] as $pelicula)
                        $this->pelicula_gui($pelicula);

                    echo "</ul>";
                } 
            }
            
                // --> MODELO

            private function alquilar($referencia) {
                $this->conectarse_db();

                try {
                    if ($_SESSION['es_sesion_iniciada'] === true) {
                        // Si ha sido alquilada...
                        $check_ha_sido_alquilada = $this->db->prepare("
                            SELECT * 
                                FROM Alquileres 
                                WHERE cliente_dni = ? 
                                    and pelicula_referencia = ?"
                        );

                        // Si pese a haber sido alquilada, no setá siéndolo ahora mismo
                        $check_esta_siendo_alquilada = $this->db->prepare("
                            SELECT * 
                                FROM Alquileres 
                                WHERE cliente_dni = ? 
                                    and pelicula_referencia = ? 
                                    and dia_devuelta is NULL"
                        );

                        // Comprobamos si ha sido alquilada alguna vez
                        $check_ha_sido_alquilada->bind_param('ss', $_SESSION['dni_usuario_logged'], $referencia);
                        $check_ha_sido_alquilada->execute();

                        $ha_sido_alquilada = $check_ha_sido_alquilada->get_result();

                        $check_ha_sido_alquilada->close();

                        // Comprobamos si está siendo alquilada ahora mismo
                        $check_esta_siendo_alquilada->bind_param('ss', $_SESSION['dni_usuario_logged'], $referencia);
                        $check_esta_siendo_alquilada->execute();

                        $esta_siendo_alquilada = $check_esta_siendo_alquilada->get_result();

                        $check_esta_siendo_alquilada->close();

                        //
                        // DEBEMOS COMPROBAR:
                        //      1. Si la película está siendo alquilada
                        //          a) Si la estás alquilando ahora mismo : NO PUEDES VOLVER A ALQUILARLA
                        //          b) Si no la estás alquilando ahora mismo...          
                        //              i) La has alquilado alguna vez?
                        //                  --> Sí : UPDATE
                        //                  --> No : INSERT
                        //
                        // Si no ha sido alquilada, ni está siendo alquilada --> INSERT
                        if (empty($ha_sido_alquilada->fetch_assoc()) 
                                && empty($esta_siendo_alquilada->fetch_assoc())) {
                            $insert = $this->db->prepare("
                                INSERT INTO Alquileres 
                                    (cliente_dni,
                                     pelicula_referencia,
                                     dia_alquilada)
                                VALUES 
                                    (?, ?, NOW())
                            ");

                            $insert->bind_param('ss', $_SESSION['dni_usuario_logged'],
                                                      $referencia);

                            $insert->execute();
                            $insert->close();

                            $this->mensaje_de_exito(
                                "Se ha alquilado la película $referencia correctamente!"
                            );
                        } elseif (empty($esta_siendo_alquilada->fetch_assoc())) {
                            // En este caso la película ha sido alquilada --> UPDATE
                            $update = $this->db->prepare("
                                UPDATE Alquileres
                                    SET dia_alquilada = NOW(),
                                        dia_devuelta = NULL
                                    where cliente_dni = ?
                                        and pelicula_referencia = ?
                            ");

                            $update->bind_param('ss', $_SESSION['dni_usuario_logged'],
                                                      $referencia);

                            $update->execute();
                            $update->close();

                            $this->mensaje_de_exito(
                                "Se ha alquilado la película $referencia correctamente!"
                            );
                        } else {
                            $this->mensaje_de_error(
                                "ERROR: ", 
                                "Ya has alquilado esta película"
                            );
                        }
                    } else
                        $this->mensaje_de_error(
                            "ERROR: ", 
                            "No has iniciado sesión"
                        );
                } catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

                $this->db->close();
            }

            private function devolver_pelicula() {
                $this->conectarse_db();

                try {
                    if ($_SESSION['es_sesion_iniciada'] === true) {
                        $query = $this->db->prepare("
                            UPDATE Alquileres
                            SET dia_devuelta = NOW() 
                            WHERE cliente_dni = ? 
                                and pelicula_referencia = ?");
                                
                        $referencia = $_POST['devolver_pelicula_id'];

                        $query->bind_param('ss', $_SESSION['dni_usuario_logged'],
                                                 $referencia);

                        if ($query->execute() === true) 
                            $this->mensaje_de_exito(
                                "Se ha devuelto la película $referencia correctamente!"
                            );
                        else
                            $this->mensaje_de_error(
                                "ERROR: ", 
                                "No habías alquilado la película"
                            );

                        $query->close();
                    } else
                        $this->mensaje_de_error(
                            "ERROR: ", 
                            "No has iniciado sesión"
                        );
                } catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

                $this->db->close();
            }

            private function filtrar_peliculas() {
                $_SESSION['filtrar_peliculas'] = !$_SESSION['filtrar_peliculas'];
            }

            // +-------------------------------------+
            // |    -*- gestión de las cuentas -*-   |
            // +-------------------------------------+

                // --> GUI

            private function usuario_gui() {
                if ($_SESSION['es_sesion_iniciada'] === true) {
                    $usuario = $_SESSION['dni_usuario_logged'];
                    $mensaje = "Has iniciado sesión como: $usuario.";
                } else
                    $mensaje = "No has iniciado sesión.";

                echo "
                    <p>$mensaje</p>
                ";
            }

            private function iniciar_sesion_gui() {
                echo "
                <form action='#' method='post'>
                    <h2>Iniciar sesión</h2>

                    <label for='iniciar_sesion_dni'>DNI:</label>
                    <input type='text' id='iniciar_sesion_dni' name='iniciar_sesion_dni' />

                    <input type='submit' name='iniciar_sesion_form' value='Iniciar sesión' />
                </form>
                ";
            }

            private function crear_cuenta_gui() {
                echo "
                <form action='#' method='post'>
                    <h2>Crear cuenta</h2>

                    <label for='iniciar_sesion_nombre'>Nombre:</label>
                    <input type='text' id='iniciar_sesion_nombre' name='iniciar_sesion_nombre' />

                    <label for='iniciar_sesion_apellidos'>Apellidos:</label>
                    <input type='text' id='iniciar_sesion_apellidos' name='iniciar_sesion_apellidos' />

                    <label for='iniciar_sesion_email'>Correo electrónico:</label>
                    <input type='email' id='iniciar_sesion_email' name='iniciar_sesion_email' />

                    <label for='iniciar_sesion_telefono'>Teléfono:</label>
                    <input type='text' id='iniciar_sesion_telefono' name='iniciar_sesion_telefono' />

                    <input type='submit' name='crear_cuenta' value='Crear cuenta' />
                </form>
                ";
            }

                // --> MODELO

            private function iniciar_sesion() {
                // Guardamos el DNI que acabamos de escrbir en el formulario
                $_SESSION['dni_usuario_logged'] = $_POST['iniciar_sesion_dni'];

                // Comprobamos si la cuenta existe o no
                $this->check_crear_cuenta();
                if ($_SESSION['hay_que_crear_cuenta'])
                    $this->crear_cuenta_gui();
                else
                    $_SESSION['es_sesion_iniciada'] = true;
            }

            private function cerrar_sesion() {
                $_SESSION['es_sesion_iniciada'] = false; // marcamos como que NO hemos iniciado sesión
                $_SESSION['hay_que_crear_cuenta'] = true; // marcamos como que hay que crear la cuenta
            }

            private function check_crear_cuenta() {
                $this->conectarse_db();

                try {
					$select_query = $this->db->prepare("
                        SELECT * FROM Clientes WHERE dni = ?"
                    );

					$select_query->bind_param('s', $_SESSION['dni_usuario_logged']);
					$select_query->execute();

					$resultado = $select_query->get_result();

					$select_query->close();

					if ($resultado->fetch_assoc() === NULL)
                        $_SESSION['hay_que_crear_cuenta'] = true;
                    else 
                        $_SESSION['hay_que_crear_cuenta'] = false;
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

				$this->db->close();
            }

            private function crear_cuenta() {
                $this->conectarse_db();

                try {
                    $query = $this->db->prepare("
                        INSERT INTO Clientes 
                            (dni,
                            nombre,
                            apellidos,
                            email,
                            telefono)
                        VALUES 
                            (?, ?, ?, ?, ?)");
                    
                    $query->bind_param('sssss', $_SESSION['dni_usuario_logged'],
                                                $_POST['iniciar_sesion_nombre'],
                                                $_POST['iniciar_sesion_apellidos'],
                                                $_POST['iniciar_sesion_email'],
                                                $_POST['iniciar_sesion_telefono']);

                    $query->execute();
                    $query->close();

                    // Si hemos llegado hasta aquí es que se ha creado una cuenta...
                    $_SESSION['hay_que_crear_cuenta'] = false;
                    $_SESSION['es_sesion_iniciada'] = true;
                } catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

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

        class Categoria {
            
            public $id;
            public $tipo;

            public function __construct($id, $tipo) {
                $this->id = $id;
                $this->tipo = $id;
            }

        }

        $videoclub = new Videoclub();
    ?>
</body>
</html>