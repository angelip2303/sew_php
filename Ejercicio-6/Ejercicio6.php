<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Ángel Iglesias Préstamo" />
    <meta name="viewport" content ="width=device-width, initial scale=1.0" />
    <title>Ejercicio6 - PruebasDeUsabilidad</title>
    <link rel="stylesheet" href="Ejercicio6.css">
</head>
<body>
    <?php
        echo "
        <header>
            <h1> Pruebas de Usabilidad </h1>
            <form action='#' method='post'> 
                <button type='submit' name='crear_db'> Crear DB </button>
                <button type='submit' name='crear_tabla'> Crear Tabla </button>
                <button type='submit' name='insertar_en_tabla'> Insertar en una Tabla </button>
                <button type='submit' name='buscar_en_tabla'> Buscar en una Tabla </button>
                <button type='submit' name='modificar_en_tabla'> Modificar datos en una Tabla </button>
                <button type='submit' name='eliminar_en_tabla'> Eliminar datos de una Tabla </button>
                <button type='submit' name='generar_informe'> Generar informe </button>
                <button type='submit' name='importar_csv'> Importar desde .CSV </button>
                <button type='submit' name='exportar_csv'> Exportar a .CSV </button>
            </form>
        </header>";

        class BaseDatos {

            private $server_name;
            private $username;
            private $password;
            private $db;
            private $db_name;

            public function __construct() {
                $this->server_name = 'localhost';
                $this->username = 'DBUSER2021';
                $this->password = 'DBPSWD2021';
                $this->db_name = 'SEW_DB';

                // Manejamos el menú
                if (count($_POST) > 0) {
                    if(isset($_POST['crear_db'])) $this->crear_db();
                    if(isset($_POST['crear_tabla'])) $this->crear_tabla();
                    if(isset($_POST['insertar_en_tabla'])) $this->insertar_en_tabla_gui();
                    if(isset($_POST['buscar_en_tabla'])) $this->buscar_en_tabla_gui();
                    if(isset($_POST['modificar_en_tabla'])) $this->modificar_en_tabla_gui();
                    if(isset($_POST['eliminar_en_tabla'])) $this->eliminar_en_tabla_gui();
                    if(isset($_POST['generar_informe'])) $this->generar_informe();
                    if(isset($_POST['importar_csv'])) $this->importar_csv_gui();
                    if(isset($_POST['exportar_csv'])) $this->exportar_csv();

                    if(isset($_POST['insertar'])) $this->insertar_en_tabla();
                    if(isset($_POST['buscar'])) $this->buscar_en_tabla();
                    if(isset($_POST['modificar'])) $this->modificar_en_tabla();
                    if(isset($_POST['eliminar'])) $this->eliminar_en_tabla();
                    if(isset($_POST['importar'])) $this->importar_csv();
                }
            }
            
            private function mensaje_de_exito($mensaje) {
                echo "<p>" .$mensaje ."</p>";
            }

            private function mensaje_de_error($mensaje, $error) {
                echo "<p>" .$mensaje .$error ."</p>";
                exit();
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

            private function crear_tabla() {
                $this->conectarse_db();

                // Establecemos la query que crea la tabla
                $tabla = "
                    create table if not exists PruebasUsabilidad(
                        id            VARCHAR(9)   NOT NULL,
                        nombre        VARCHAR(32)  NOT NULL,
                        apellidos     VARCHAR(64)  NOT NULL,
                        email         VARCHAR(255) NOT NULL,
                        telefono      VARCHAR(9)   NOT NULL,
                        edad          int          NOT NULL,
                        sexo          int          NOT NULL,
                        tiempo        int          NOT NULL,
                        nivel         int          NOT NULL,
                        es_completada int          NOT NULL,
                        comentarios   VARCHAR(255) NOT NULL,
                        propuestas    VARCHAR(255) NOT NULL,
                        valoracion    int          NOT NULL,

                        primary key (id),
                        check (sexo = 0          || sexo = 1),
                        check (es_completada = 0 || es_completada = 1),
                        check (tiempo >= 0),
                        check (nivel >= 0      && nivel <= 10),
                        check (valoracion >= 0 && valoracion <= 10)
                    )
                ";

                // Si hemos conseguido crear la tabla...
                if($this->db->query($tabla) === TRUE)
                    $this->mensaje_de_exito(
                        "<p> La tabla PruebasUsabilidad ha sido creada correctamente </p>"
                    );
				else // En caso de que no lo hayamos conseguido...
                    $this->mensaje_de_error(
                        "ERROR creando la tabla: ",
                        $this->db->error
                    );
                
                // Cerramos la DB
				$this->db->close(); 
            }

            private function insertar_en_tabla_gui() {
                echo "
                <form action='#' method='post'>
                    <h2>Insertar en la tabla</h2>

                    <label for='insertar_dni'>DNI:</label>
                    <input type='text' id='insertar_dni' name='insertar_dni' />

                    <label for='insertar_nombre'>Nombre:</label>
                    <input type='text' id='insertar_nombre' name='insertar_nombre' />

                    <label for='insertar_apellidos'>Apellidos:</label>
                    <input type='text' id='insertar_apellidos' name='insertar_apellidos' />

                    <label for='insertar_email'>Correo electrónico:</label>
                    <input type='email' id='insertar_email' name='insertar_email' />

                    <label for='insertar_telefono'>Teléfono:</label>
                    <input type='text' id='insertar_telefono' name='insertar_telefono' />

                    <label for='insertar_edad'>Edad:</label>
                    <input type='number' id='insertar_edad' name='insertar_edad' />

                    <fieldset>
                        <legend> ¿Sexo? </legend>
                        <input type='radio' name='insertar_sexo' value='0'> Hombre <br />
                        <input type='radio' name='insertar_sexo' value='1'> Mujer <br />
                    </fieldset>

                    <label for='insertar_nivel'>Nivel informático:</label>
                    <input type='range' id='insertar_nivel' name='insertar_nivel' min='0' max='10' step='1' />

                    <label for='insertar_tiempo'>Tiempo que ha tardado en realizar la tarea (en segundos):</label>
                    <input type='number' id='insertar_tiempo' name='insertar_tiempo' />

                    <fieldset>
                        <legend> Se ha relizado correctamente la tarea? </legend>
                        <input type='radio' name='insertar_es_completada' value='1'> Sí <br />
                        <input type='radio' name='insertar_es_completada' value='0'> No <br />
                    </fieldset>

                    <label for='insertar_comentarios'>Comentarios sobre problemas encontrados:</label>
                    <textarea id='insertar_comentarios' name='insertar_comentarios' maxlength='255'></textarea>

                    <label for='insertar_propuestas'>Propuestas para la mejora de la aplicación:</label>
                    <textarea id='insertar_propuestas' name='insertar_propuestas' maxlength='255'></textarea>

                    <label for='insertar_valoracion'>Valoración de la aplicación:</label>
                    <input type='range' id='insertar_valoracion' name='insertar_valoracion' min='0' max='10' step='1' />

                    <input type='submit' value='Enviar!' title='Enviar formulario' name='insertar'/>
                </form>";
            }

            private function insertar_en_tabla() {
                $this->conectarse_db();

                $id = $_POST['insertar_dni'];
                $nombre = $_POST['insertar_nombre'];
                $apellidos = $_POST['insertar_apellidos'];
                $email = $_POST['insertar_email'];
                $telefono = $_POST['insertar_telefono'];
                $edad = $_POST['insertar_edad'];
                $sexo = $_POST['insertar_sexo'] === 'true' ? true : false;
                $nivel = $_POST['insertar_nivel'];
                $tiempo = $_POST['insertar_tiempo'];
                $es_completada = $_POST['insertar_es_completada'] === 'true' ? true : false;
                $comentarios = $_POST['insertar_comentarios'];
                $propuestas = $_POST['insertar_propuestas'];
                $valoracion = $_POST['insertar_valoracion'];

                if(!$this->validar_datos($id, 
                                         $nombre, 
                                         $apellidos, 
                                         $email, 
                                         $telefono,
                                         $edad, 
                                         $tiempo, 
                                         $comentarios, 
                                         $propuestas) )
                    $this->mensaje_de_error(
                        "Algunos campos no son válidos: ",
                        "error de validación"
                    );
                else {
                    try {
                        // Creamos la consulta correspondiente a la inserción
                        $query = $this->db->prepare("
                            INSERT INTO PruebasUsabilidad 
                                (id,
                                 nombre,
                                 apellidos,
                                 email,
                                 telefono,
                                 edad,
                                 sexo,
                                 nivel,
                                 tiempo,
                                 es_completada,
                                 comentarios,
                                 propuestas,
                                 valoracion)
                            VALUES 
                                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                        // Asignamos a cada párametro su elemento correspondiente
                        $query->bind_param('sssssiiiiissi', $id,
                                                            $nombre,
                                                            $apellidos,
                                                            $email,
                                                            $telefono,
                                                            $edad,
                                                            $sexo,
                                                            $nivel,
                                                            $tiempo,
                                                            $es_completada,
                                                            $comentarios,
                                                            $propuestas,
                                                            $valoracion);  
                        
                        // Ejecutamos la consulta... esperando que todo salga bien
                        $query->execute();

                        if ($query->affected_rows == -1) 
                            $this->mensaje_de_error(
                                "No hemos podido insertar los elementos: ",
                                "error de inserción de datos"
                            );
                        else 
                            $this->mensaje_de_exito(
                                "Hemos insertado los datos con éxito!"
                            );

                        $query->close();
                    } catch (Error $e) {
                        $this->mensaje_de_error(
                            "ERROR: ",
                            $e->getMessage()
                        );
                    }

                    $this->db->close();
                } // en caso de que los datos sean válidos
            }

            private function validar_datos($id, $nombre, $apellidos, $email, $telefono,
                    $edad, $tiempo, $comentarios, $propuestas) {
                return !empty($id) &&
                       !empty($nombre) &&
                       !empty($apellidos) &&
                       !empty($telefono) &&
                       $edad >= 0 &&
                       $tiempo >= 0 &&
                       !empty($comentarios) &&
                       !empty($propuestas);
            }

            private function buscar_en_tabla_gui() {
                echo "
                <form action='#' method='post'>
                    <h2>Buscar en la tabla</h2>

                    <label for='buscar_dni'>DNI:</label>
                    <input type='text' id='buscar_dni' name='buscar_dni' />

                    <input type='submit' value='Enviar!' title='Enviar formulario' name='buscar'/>
                </form>";
            }

            private function buscar_en_tabla() {
                $this->conectarse_db();

                try {
					$query = $this->db->prepare("
                        SELECT * FROM PruebasUsabilidad WHERE id = ?
                    ");

                    // Asignamos el parámetro obtenido del formulario anterior
					$query->bind_param('s', $_POST["buscar_dni"]);

                    // Ejecutamos la consulta de búsqueda
					$query->execute();
				
                    // Obtenemos el resultado de la consulta
					$resultado = $query->get_result();

					if ($resultado->fetch_assoc() != NULL) {
						$resultado->data_seek(0);
						$fila = $resultado->fetch_assoc();

                        $id = $fila['id'];
                        $nombre = $fila['nombre'];
                        $apellidos = $fila['apellidos'];
                        $email = $fila['email'];
                        $telefono = $fila['telefono'];
                        $edad = $fila['edad'];
                        $sexo = $fila['sexo'] ? 'Mujer' : 'Hombre';
                        $nivel = $fila['nivel'];
                        $tiempo = $fila['tiempo'];
                        $es_completada = $fila['es_completada'] ? 'Sí' : 'No';
                        $comentarios = $fila['comentarios'];
                        $propuestas = $fila['propuestas'];
                        $valoracion = $fila['valoracion'];

						echo "
                        <h3> Hemos encontrado los siguientes datos: </h3>
                        <ul>
                            <li> DNI: $id </li>
                            <li> Nombre: $nombre </li>
                            <li> Apellidos: $apellidos </li>
                            <li> Correo Electrónico: $email </li>
                            <li> Teléfono: $telefono </li>
                            <li> Edad: $edad </li>
                            <li> Sexo: $sexo </li>
                            <li> Nivel: $nivel </li>
                            <li> Tiempo: $tiempo </li>
                            <li> Se ha completado la tarea? $es_completada </li>
                            <li> Comentarios: $comentarios </li>
                            <li> Propuestas: $propuestas </li>
                            <li> Valoración: $valoracion </li>
                        </ul>";       
					} else
                        $this->mensaje_de_error(
                            "ERROR: ", 
                            "no hemos encontrado ningún resultado para la búsqueda que está solicitando :("
                        );

					$query->close();
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

				$this->db->close();
            }

            private function modificar_en_tabla_gui() {
                echo "
                <form action='#' method='post'>
                    <h2>Modificar en la tabla</h2>

                    <label for='modificar_dni'>DNI:</label>
                    <input type='text' id='modificar_dni' name='modificar_dni' />

                    <label for='modificar_nombre'>Nombre:</label>
                    <input type='text' id='modificar_nombre' name='modificar_nombre' />

                    <label for='modificar_apellidos'>Apellidos:</label>
                    <input type='text' id='modificar_apellidos' name='modificar_apellidos' />

                    <label for='modificar_email'>Correo electrónico:</label>
                    <input type='email' id='modificar_email' name='modificar_email' />

                    <label for='modificar_telefono'>Teléfono:</label>
                    <input type='text' id='modificar_telefono' name='modificar_telefono' />

                    <label for='modificar_edad'>Edad:</label>
                    <input type='number' id='modificar_edad' name='modificar_edad' />

                    <fieldset>
                        <legend> ¿Sexo? </legend>
                        <input type='radio' name='modificar_sexo' value='0' checked> Hombre <br />
                        <input type='radio' name='modificar_sexo' value='1'> Mujer <br />
                    </fieldset>

                    <label for='modificar_nivel'>Nivel informático:</label>
                    <input type='range' id='modificar_nivel' name='modificar_nivel' min='0' max='10' step='1' />

                    <label for='modificar_tiempo'>Tiempo que ha tardado en realizar la tarea (en segundos):</label>
                    <input type='number' id='modificar_tiempo' name='modificar_tiempo' />

                    <fieldset>
                        <legend> Se ha relizado correctamente la tarea? </legend>
                        <input type='radio' name='modificar_es_completada' value='1' checked> Sí <br />
                        <input type='radio' name='modificar_es_completada' value='0'> No <br />
                    </fieldset>

                    <label for='modificar_comentarios'>Comentarios sobre problemas encontrados:</label>
                    <textarea id='modificar_comentarios' name='modificar_comentarios' maxlength='255'></textarea>

                    <label for='modificar_propuestas'>Propuestas para la mejora de la aplicación:</label>
                    <textarea id='modificar_propuestas' name='modificar_propuestas' maxlength='255'></textarea>

                    <label for='modificar_valoracion'>Valoración de la aplicación:</label>
                    <input type='range' id='modificar_valoracion' name='modificar_valoracion' min='0' max='10' step='1' />

                    <input type='submit' value='Enviar!' title='Enviar formulario' name='modificar'/>
                </form>";
            }

            private function modificar_en_tabla() {
                $this->conectarse_db();

                $id = $_POST['modificar_dni'];
                $nombre = $_POST['modificar_nombre'];
                $apellidos = $_POST['modificar_apellidos'];
                $email = $_POST['modificar_email'];
                $telefono = $_POST['modificar_telefono'];
                $edad = $_POST['modificar_edad'];
                $sexo = $_POST['modificar_sexo'] === 'true' ? true : false;
                $nivel = $_POST['modificar_nivel'];
                $tiempo = $_POST['modificar_tiempo'];
                $es_completada = $_POST['modificar_es_completada'] === 'true' ? true : false;
                $comentarios = $_POST['modificar_comentarios'];
                $propuestas = $_POST['modificar_propuestas'];
                $valoracion = $_POST['modificar_valoracion'];

                try {
					$select_query = $this->db->prepare("
                        SELECT * FROM PruebasUsabilidad WHERE id = ?"
                    );

					$select_query->bind_param('s', $_POST["modificar_dni"]);   	
					$select_query->execute();

					$resultado = $select_query->get_result();

					$select_query->close();

					if ($resultado->fetch_assoc()!=NULL) {
						$resultado->data_seek(0);
						$fila = $resultado->fetch_assoc();

						$dni = $fila['id'];

                        // Comprobamos que se ha escrito NOMBRE
						if (empty($nombre)) // En caso de que no se haya escrito...
                            $nombre = $fila['nombre']; // mantenemos el que había antes
                        
                        // Comprobamos que se ha escrito APELLIDOS
                        if (empty($apellidos)) // En caso de que no se haya escrito...
                            $apellidos = $fila['apellidos']; // mantenemos el que había antes

                        // Comprobamos que se ha escrito EMAIL
                        if (empty($email)) // En caso de que no se haya escrito...
                            $email = $fila['email']; // mantenemos el que había antes

                        // Comprobamos que se ha escrito TELÉFONO
                        if (empty($telefono)) // En caso de que no se haya escrito...
                            $telefono = $fila['email']; // mantenemos el que había antes

                        // Comprobamos que se ha escrito EDAD
						if (empty($edad)) // En caso de que no se haya escrito...
                            $edad = $fila['edad']; // mantenemos el que había antes

                        // Comprobamos que se ha escrito COMENTARIOS
                        if (empty($comentarios)) // En caso de que no se haya escrito...
                            $comentarios = $fila['comentarios']; // mantenemos el que había antes

                        // Comprobamos que se ha escrito PROPUESTAS
                        if (empty($propuestas)) // En caso de que no se haya escrito...
                            $propuestas = $fila['propuestas']; // mantenemos el que había antes
                        
                        // El resto de valores no se verificarán ya que tienen valores por defecto: sólo los STR
					
						$update_query = $this->db->prepare("
                            UPDATE PruebasUsabilidad 
                                SET nombre = ?, 
                                    apellidos = ?, 
                                    email = ?, 
                                    telefono = ?, 
                                    edad = ?, 
							        sexo = ?, 
                                    nivel = ?, 
                                    tiempo = ?, 
                                    es_completada = ?, 
                                    comentarios = ?, 
                                    propuestas = ?, 
                                    valoracion = ? 
                                WHERE id = ?"
                        );
						
                        if ($update_query === false)
                            $this->mensaje_de_error(
                                "ERROR: ", 
                                $this->db->error
                            );

                        // Asignamos a cada párametro su elemento correspondiente
                        $update_query->bind_param('ssssiiiiissis', $nombre,
                                                                   $apellidos,
                                                                   $email,
                                                                   $telefono,
                                                                   $edad,
                                                                   $sexo,
                                                                   $nivel,
                                                                   $tiempo,
                                                                   $es_completada,
                                                                   $comentarios,
                                                                   $propuestas,
                                                                   $valoracion,
                                                                   $dni);  
						
						$update_query->execute();

                        // Comprobamos si se ha hecho alún tipo de actualización
						if ($update_query->affected_rows == -1) 
                            $this->mensaje_de_error(
                                "ERROR: ", 
                                "no se ha podido actualizar ninguna fila"
                            );
						else
                            $this->mensaje_de_exito(
                                "Se ha realizado la actualización con éxito"
                            );


						$update_query->close();
					} else // no hemos encontrado el DNI...
                        $this->mensaje_de_error(
                            "ERROR: ", 
                            "usuario no encontrado"
                        );
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

				$this->db->close();
            }

            private function eliminar_en_tabla_gui() {
                echo "
                <form action='#' method='post'>
                    <h2>Eliminar de la tabla</h2>

                    <label for='eliminar_dni'>DNI:</label>
                    <input type='text' id='eliminar_dni' name='eliminar_dni' />

                    <input type='submit' value='Enviar!' title='Enviar formulario' name='eliminar'/>
                </form>";
            }

            private function eliminar_en_tabla() {
                $this->conectarse_db();

                try {
					$select_query = $this->db->prepare("
                        SELECT * FROM PruebasUsabilidad WHERE id = ?
                    ");

                    // Asignamos el parámetro obtenido del formulario anterior
					$select_query->bind_param('s', $_POST["eliminar_dni"]);

                    // Ejecutamos la consulta de búsqueda
					$select_query->execute();
				
                    // Obtenemos el resultado de la consulta
					$resultado = $select_query->get_result();

                    $select_query->close();

					if ($resultado->fetch_assoc() != NULL) {
						$resultado->data_seek(0);
						$fila = $resultado->fetch_assoc();

                        $id = $fila['id'];
                        $nombre = $fila['nombre'];
                        $apellidos = $fila['apellidos'];
                        $email = $fila['email'];
                        $telefono = $fila['telefono'];
                        $edad = $fila['edad'];
                        $sexo = $fila['sexo'] === '0' ? 'Hombre' : 'Mujer';
                        $nivel = $fila['nivel'];
                        $tiempo = $fila['tiempo'];
                        $es_completada = $fila['es_completada'] === '1' ? 'Sí' : 'No';
                        $comentarios = $fila['comentarios'];
                        $propuestas = $fila['propuestas'];
                        $valoracion = $fila['valoracion'];

						echo "
                        <h3> Vamos a eliminar los siguientes datos: </h3>
                        <ul>
                            <li> DNI: $id </li>
                            <li> Nombre: $nombre </li>
                            <li> Apellidos: $apellidos </li>
                            <li> Correo Electrónico: $email </li>
                            <li> Teléfono: $telefono </li>
                            <li> Edad: $edad </li>
                            <li> Nivel: $nivel </li>
                            <li> Edad: $edad </li>
                            <li> Tiempo: $tiempo </li>
                            <li> Se ha completado la tarea? $es_completada </li>
                            <li> Comentarios: $comentarios </li>
                            <li> Propuestas: $propuestas </li>
                            <li> Valoración: $valoracion </li>
                        </ul>";

                        $delete_query = $this->db->prepare("
                            DELETE FROM PruebasUsabilidad WHERE id = ?
                        ");

						$delete_query->bind_param('s', $fila['id']);
						$delete_query->execute();
						$delete_query->close();
						
                        $this->mensaje_de_exito(
                            "Se han eliminado correctamente los datos mostrados anteriormente"
                        );
					} else
                        $this->mensaje_de_error(
                            "ERROR: ", 
                            "no hemos encontrado ningún resultado para la búsqueda que está solicitando :("
                        );
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

				$this->db->close();
            }

            private function generar_informe() {
                $this->conectarse_db();
				
                echo "
                    <h2>Generar informe</h2>
                ";

				try {
					$query = $this->db->prepare("
                        SELECT * FROM PruebasUsabilidad
                    ");

					$query->execute();
					$resultado = $query->get_result();
                    $query->close();

					if ($resultado->fetch_assoc()!=NULL) {
						$resultado->data_seek(0);

						$numero_pruebas = 0;
						$edad_total = 0;
						$hombres = 0;
						$mujeres = 0;
						$nivel_total = 0;
						$tiempo_total = 0;
						$completadas = 0;
						$valoracion_total = 0;

						while($fila = $resultado->fetch_assoc()) {
                            // Determinamos el sexo
                            if ($fila["sexo"] === 0) // es un hombre?
                                $hombres++; 
							else // es una mujer?
                                $mujeres++; 

                            // Determinamos si ha completado la prueba
                            if ($fila["es_completada"] === 1) // hemos completado la prueba?
                                $completadas++; 

							$numero_pruebas++; // hemos hecho una prueba
							$edad_total += $fila["edad"]; // sumamos la edad
							$nivel_total += $fila["nivel"]; // sumamos el nivel del usuario que realizó la prueba
							$tiempo_total += $fila["tiempo"]; // sumamos el tiempo
							$valoracion_total += $fila["valoracion"]; // sumamos la valoración
						}

                        $edad_media = $edad_total / $numero_pruebas;
                        $porcentaje_hombres = $hombres / $numero_pruebas * 100;
                        $porcentaje_mujeres = $mujeres / $numero_pruebas * 100;
                        $nivel_medio = $nivel_total / $numero_pruebas;
                        $tiempo_medio = $tiempo_total / $numero_pruebas;
                        $porcentaje_es_completada = $completadas / $numero_pruebas * 100;
                        $valoracion_media = $valoracion_total / $numero_pruebas;

                        echo "
                        <h3> Hemos encontrado los siguientes datos: </h3>
                        <ul>
						    <li>Edad media de los usuarios = $edad_media años</li>
						    <li>Frecuencia del % de cada tipo de sexo entre los usuarios:
                                <ul>
                                    <li>Porcentaje de hombres: $porcentaje_hombres %</li>
                                    <li>Porcentaje de mujeres: $porcentaje_mujeres %</li>
                                </ul>
                            <li>Valor medio del nivel o pericia informática de los usuarios = $nivel_medio</li>
                            <li>Tiempo medio para la tarea = $tiempo_medio segundos</li>
                            <li>Porcentaje de usuarios que han realizado la tarea correctamente = $porcentaje_es_completada %</li>
                            <li>Valoracion media de los usuarios = $valoracion_media </li>
                        </ul>"; 
					} else                         
                        $this->mensaje_de_error(
                            "ERROR: ", 
                            "no hay datos en la tabla"
                        );
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

				$this->db->close();
            }

            private function importar_csv_gui() {
                echo "
                <form action='#' method='post' enctype='multipart/form-data'>
                    <h2>Importar desde archivo a la Base de Datos</h2>

                    <label for='archivo'>Selecciona un archivo:</label>
                    <input type='file' id='archivo' name='archivo'/>

                    <input type='submit' value='Enviar!' title='Enviar formulario' name='importar'/>
                </form>";  
            }

            private function importar_csv() {
                $this->conectarse_db();

                try {
                    // Comprobamos si se ha subido un archivo o no...
					if (!(isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK))
                        $this->mensaje_de_error(
                            "ERROR: ", 
                            "debes seleccionar un archivo"
                        );

                    // Comprobamos si el formato del archivo es .csv o no...
					if ($_FILES['archivo']['type'] != 'application/vnd.ms-excel')
                        $this->mensaje_de_error(
                            "ERROR: ", 
                            "debes seleccionar un archivo con un formáto valido"
                        );

					$archivo = fopen($_FILES['archivo']['tmp_name'], "r");

					while(($datos = fgetcsv($archivo, 1000, ";")) !== false) {
                        $query = $this->db->prepare("
                            INSERT INTO PruebasUsabilidad 
                                (id,
                                 nombre,
                                 apellidos,
                                 email,
                                 telefono,
                                 edad,
                                 sexo,
                                 nivel,
                                 tiempo,
                                 es_completada,
                                 comentarios,
                                 propuestas,
                                 valoracion)
                            VALUES 
                                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                        if ($query === false)
                            $this->mensaje_de_error(
                                "ERROR: ", 
                                $this->db->error
                            );

						$is_inserted = $query->bind_param('sssssiiiiissi', $datos[0],
                                                                           $datos[1],
                                                                           $datos[2],
                                                                           $datos[3],
                                                                           $datos[4],
                                                                           $datos[5],
                                                                           $datos[6],
                                                                           $datos[7],
                                                                           $datos[8],
                                                                           $datos[9],
                                                                           $datos[10],
                                                                           $datos[11],
                                                                           $datos[12]);

                        if ($is_inserted === false)
                            $this->mensaje_de_error(
                                "ERROR: ", 
                                "No hemos podido insertar la línea"
                            );

						$query->execute();
						$query->close();
					}

                    // IMPORTANTE: cerrar el archivo
					fclose($archivo);

                    $this->mensaje_de_exito(
                        "Los datos se han importado correctamente"
                    );
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}

				$this->db->close();
            }

            private function exportar_csv() {
                $this->conectarse_db();

                echo "
                    <h2>Exportar desde la Base de Datos a un archivo</h2>
                ";

				try {
					$query = $this->db->prepare("
                        SELECT * FROM PruebasUsabilidad
                    ");

					$query->execute();
					$resultado = $query->get_result();

                    // Cerramos sendas: consulta y base de datos...
					$query->close();
					$this->db->close();
				
                    // Abrimos el archivo donde vamos a almacenar los datos
					$archivo = fopen("pruebasUsabilidad_exportado.csv", "w");
					
                    if ($resultado->fetch_assoc() != NULL) {
						$resultado->data_seek(0);

						while($fila = $resultado->fetch_assoc()) {
							$fila = array($fila['id'],
                                          $fila['nombre'],
                                          $fila['apellidos'],
                                          $fila['email'],
                                          $fila['telefono'], 
								          $fila['edad'],
                                          $fila['sexo'],
                                          $fila['nivel'],
                                          $fila['tiempo'],
                                          $fila['es_completada'], 
								          $fila['comentarios'],
                                          $fila['propuestas'],
                                          $fila['valoracion']);
                            
							fputcsv($archivo, $fila, ";");
						}
					}

                    // IMPORTANTE: cerrar el archivo
					fclose($archivo);

                    $this->mensaje_de_exito(
                        "Los datos se han importado correctamente"
                    );
				} catch (Error $e) {
                    $this->mensaje_de_error(
                        "ERROR: ", 
                        $e->getMessage()
                    );
				}
			}
        }

        $db = new BaseDatos();
    ?>
</body>
</html>