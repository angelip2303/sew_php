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
                <button type='submit' name='filtrar_peliculas_categoria'> Filtrar películas por categoría </button>
                <button type='submit' name='filtrar_peliculas_oscar'> Filtrar películas que han ganado un Óscar </button>
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
                if (!isset($_SESSION['filtrar_peliculas_categoria']))
                    $_SESSION['filtrar_peliculas_categoria'] = false;
                
                // Manejamos la pila a través de la sesión
                if (!isset($_SESSION['filtrar_peliculas_oscar']))
                    $_SESSION['filtrar_peliculas_oscar'] = false;

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
                    if (isset($_POST['filtrar_peliculas_categoria']))
                        $this->filtrar_peliculas_categoria();
                    if (isset($_POST['filtrar_peliculas_oscar']))
                        $this->filtrar_peliculas_oscar();
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
                // Inicilizamos la DB --> esto se hará directamente desde un archivo
                $this->añadir_categorias();
                $this->añadir_peliculas();

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
            }

            private function añadir_categorias() {
                $this->conectarse_db();

                try {
					$select_query = $this->db->prepare("
                        SELECT * FROM Categorias"
                    );

					$select_query->execute();
					$resultado = $select_query->get_result();
					$select_query->close();

                    if ($resultado->num_rows > 0)
                        while($fila = $resultado->fetch_assoc()) // Añadimos la categoría
                            $_SESSION['categorias'][] = new Categoria(
                                $fila['id'], 
                                $fila['tipo']
                            );
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

				$this->db->close();
            }

            private function añadir_peliculas() {
                $this->conectarse_db();

                try {
					$select_query = $this->db->prepare("
                        SELECT * FROM Peliculas"
                    );

					$select_query->execute();
					$resultado = $select_query->get_result();
					$select_query->close();

                    if ($resultado->num_rows > 0)
                        while($fila = $resultado->fetch_assoc()) // Añadimos la película a nuestra lista...
                            $_SESSION['peliculas'][] = new Pelicula (
                                $fila['referencia'],
                                $fila['titulo'],
                                $fila['categoria_id'],
                                $fila['director'],
                                $fila['actor_principal'],
                                $fila['portada'],
                                $fila['ha_ganado_oscar']
                            );
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

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
                // Comprobamos si hay que filtrar por Óscar
                $peliculas = array();

                if ($_SESSION['filtrar_peliculas_oscar'])
                    foreach($_SESSION['peliculas'] as $pelicula) {
                        if ($pelicula->ha_ganado_oscar === 1)
                            $peliculas[] = $pelicula;
                    }
                else
                    $peliculas = $_SESSION['peliculas'];

                // Tenemos que mostrar las películas filtradas por categorías
                if ($_SESSION['filtrar_peliculas_categoria']) {
                    foreach ($_SESSION['categorias'] as $categoria) {
                        // Comprobamos si se va a mostrar alguna película de este tipo
                        $numero_de_peliculas = 0;

                        foreach($peliculas as $pelicula)
                            if ($pelicula->categoria_id === $categoria->id) {
                                // Comprobamos que sea la primera vez que aparece una película de este tipo
                                if ($numero_de_peliculas === 0) {
                                    echo "<h2> $categoria->tipo </h2>";
                                    echo "<ul>";
                                }
                                
                                // Mostramos la película normalmente
                                $this->pelicula_gui($pelicula);
                                $numero_de_peliculas++;
                            }
                        
                        // Hemos mostrado alguna película para este tipo de categoría
                        if ($numero_de_peliculas > 0)  echo "</ul>";
                    }
                } else {
                    // mostramos todas las películas juntas
                    echo "<ul>";
                    
                    foreach($peliculas as $pelicula)
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

            private function filtrar_peliculas_categoria() {
                $_SESSION['filtrar_peliculas_categoria'] = !$_SESSION['filtrar_peliculas_categoria'];
            }

            private function filtrar_peliculas_oscar() {
                $_SESSION['filtrar_peliculas_oscar'] = !$_SESSION['filtrar_peliculas_oscar'];
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