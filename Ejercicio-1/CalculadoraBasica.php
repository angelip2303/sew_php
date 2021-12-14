<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Ángel Iglesias Préstamo" />
    <meta name="viewport" content ="width=device-width, initial scale=1.0" />
    <title>Ejercicio1 - CalculadoraBásica</title>
    <link rel="stylesheet" href="CalculadoraBasica.css">
</head>
<body>
    <h1>Calculadora Básica</h1>

    <?php
        session_start(); // iniciamos SESSION

        if (!isset($_SESSION['sesion_pantalla']))
            $_SESSION['sesion_pantalla'] = '';

        class CalculadoraBasica {
            // Manejamos la pantalla
            private $pantalla; // valor que debe mostrarse en la pantalla de la calculadora

            public function __construct () {
                $this->pantalla = '';

                if (count($_POST) > 0) {
                    // Nos encargamos de manejar los botones de los números
                    if(isset($_POST['1'])) $this->caracter(1);
                    if(isset($_POST['2'])) $this->caracter(2);
                    if(isset($_POST['3'])) $this->caracter(3);
                    if(isset($_POST['4'])) $this->caracter(4);
                    if(isset($_POST['5'])) $this->caracter(5);
                    if(isset($_POST['6'])) $this->caracter(6);
                    if(isset($_POST['7'])) $this->caracter(7);
                    if(isset($_POST['8'])) $this->caracter(8);
                    if(isset($_POST['9'])) $this->caracter(9);
                    if(isset($_POST['0'])) $this->caracter(0);

                    // Nos encargamos de manejar las operaciones
                    if(isset($_POST['division'])) $this->caracter('/');
                    if(isset($_POST['producto'])) $this->caracter('*');
                    if(isset($_POST['resta'])) $this->caracter('-');
                    if(isset($_POST['suma'])) $this->caracter('+');
                    if(isset($_POST['punto'])) $this->caracter('.');

                    if(isset($_POST['igual'])) $this->igual();

                    // Nos encargamos de manejar la memoria
                    if(isset($_POST['mrc'])) $this->mrc();
                    if(isset($_POST['m-'])) $this->m_menos();
                    if(isset($_POST['m+'])) $this->m_mas();

                    // Otros botones
                    if(isset($_POST['borrar'])) $this->borrar();

                    // Manejamos la pantalla a través de la sesión
                    if (!isset($_SESSION['sesion_pantalla']))
                        $_SESSION['sesion_pantalla'] = '';
                    $_SESSION['sesion_pantalla'] .= $this->pantalla;

                    // Manejamos la memoria a través de la sesión
                    if (!isset($_SESSION['sesion_memoria']))
                        $_SESSION['sesion_memoria'] = 0;
                }
            }
        
            // Añadimos el caracter a la pantalla
            private function caracter($caracter) {
                $this->pantalla .= $caracter;
            }
        
            /** Igual: evalua los operandos y operador que hemos indicado. Y maneja las 
             *  excepciones que puedan surgir:
             *      A) Si no hemos indicado bien algún operando --> ERROR
             *      B) Si no hemos indicado bien algún operador --> ERROR
             *      C) Si falla la evaluación --> ERROR
             * 
             *  --> NOTA: si quieres seguir trabajando con ese valor computado, deberás
             *  utilizar las teclas de memoria (para eso están).
             */
            private function igual() {
                if (isset($_SESSION['sesion_pantalla']))
                    try {
                        $expresion = $_SESSION['sesion_pantalla'];
                        $_SESSION['sesion_pantalla'] = eval("return $expresion ;"); 
                    } catch (Exception $e) {
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    } catch(ParseError $p){
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    }
            }
        
            // C: Reestablece la calculadora a un estado inicial.
            private function borrar() {
                unset($_SESSION['sesion_pantalla']);
                unset($_SESSION['sesion_memoria']);
            }
        
            /** MRC: El funcionamiento de esta tecla es el siguiente:
             *      A) La primera vez que pulsas (RECALL) --> escribe en pantalla el valor
             *      guardado en memoria.
             * 
             *      B) La segunda vez que pulsas la tecla (CLEAR) --> limpia el valor que
             *      está almacenado en memoria.
             */
            private function mrc() {
                if (isset($_SESSION['sesion_memoria']))
                    $_SESSION['sesion_pantalla'] = $_SESSION['sesion_memoria'];
            }
        
            // M-: Resta el valor que está guardado en memoria con el que aparece en pantalla
            private function m_menos() {
                $this->opera_en_memoria('-');
            }
        
            // M+: Suma el valor que está guardado en memoria con el que aparece en pantalla
            private function m_mas() {
                $this->opera_en_memoria('+');
            }
        
            private function opera_en_memoria($operador) {
                try {
                    $memoria = $_SESSION['sesion_memoria'];
                    $pantalla = $_SESSION['sesion_pantalla'];
                    $_SESSION['sesion_memoria'] = eval("return $memoria"
                                                             ."$operador"
                                                             ."$pantalla ;");
                } catch (Exception $e) {
                    $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    $this->borrar();
                } catch(ParseError $p){
                    $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    $this->borrar();
                }
            }

        }

        $calculadora = new CalculadoraBasica();

        $pantalla = $_SESSION['sesion_pantalla'];

        echo "
        <form action='#' method='post'>
            <!-- PANTALLA para mostrar los resultados -->
            <label for='result' hidden>Pantalla para mostrar los resultados de los cálculos</label>
            <input type='text' id='result' value='$pantalla' disabled>
    
            <!-- Botones encargados de hacer los cálculos -->
            <input type='submit' value='mrc' name='mrc' />
            <input type='submit' value='m-'  name='m-' />
            <input type='submit' value='m+'  name='m+' />
            <input type='submit' value='/'   name='division' />
            <input type='submit' value='7'   name='7' />
            <input type='submit' value='8'   name='8' />
            <input type='submit' value='9'   name='9' />
            <input type='submit' value='*'   name='producto' />
            <input type='submit' value='4'   name='4' />
            <input type='submit' value='5'   name='5' />
            <input type='submit' value='6'   name='6' />
            <input type='submit' value='-'   name='resta' />
            <input type='submit' value='1'   name='1' />
            <input type='submit' value='2'   name='2' />
            <input type='submit' value='3'   name='3' />
            <input type='submit' value='+'   name='suma' />
            <input type='submit' value='0'   name='0' />
            <input type='submit' value='.'   name='punto' />
            <input type='submit' value='C'   name='borrar' />
            <input type='submit' value='='   name='igual' />
        </form>";
    ?>
</body>
</html>