<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Ángel Iglesias Préstamo" />
    <meta name="viewport" content ="width=device-width, initial scale=1.0" />
    <title>Ejercicio2 - CalculadoraCientífica</title>
    <link rel="stylesheet" href="CalculadoraCientifica.css">
</head>
<body>
    <h1> Calculadora Científica </h1>
    <?php
        session_start(); // iniciamos SESSION

        if (!isset($_SESSION['sesion_pantalla']))
            $_SESSION['sesion_pantalla'] = '';

        // Manejamos la memoria a través de la sesión
        if (!isset($_SESSION['sesion_memoria']))
            $_SESSION['sesion_memoria'] = 0;

        // Necesario para realizar algunos cálculos
        if (!isset($_SESSION['es_radianes']))
            $_SESSION['es_radianes'] = false;

        if (!isset($_SESSION['es_funcion_circular']))
                    $_SESSION['es_funcion_circular'] = false;

        class CalculadoraBasica {
            // Manejamos la pantalla
            public $pantalla; // valor que debe mostrarse en la pantalla de la calculadora

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
                
                    // Por si acabamos de hacer unset a las sesiones

                    if (!isset($_SESSION['sesion_pantalla']))
                        $_SESSION['sesion_pantalla'] = '';
            
                    // Manejamos la memoria a través de la sesión
                    if (!isset($_SESSION['sesion_memoria']))
                        $_SESSION['sesion_memoria'] = 0;
                }
            }
        
            // Añadimos el caracter a la pantalla
            public function caracter($caracter) {
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
                    } catch(DivisionByZeroError $d){
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    } catch(Error $e){
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
                } catch (Error $e) {
                    $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    $this->borrar();
                }
            }

        }

        class CalculadoraCientifica extends CalculadoraBasica {

            public function __construct() {
                parent::__construct();

                if (count($_POST) > 0) {
                    // Cambiamos las unidades de los angulos
                    if(isset($_POST['unidad_angulo'])) $this->cambiar_unidades_angulo();

                    // Nos encargamos de manejar los botones de los números
                    if(isset($_POST['pi'])) $this->caracter(M_PI);
                    if(isset($_POST['e'])) $this->caracter(M_E);

                    if(isset($_POST['parentesis_izquierdo'])) $this->caracter('(');
                    if(isset($_POST['parentesis_derecho'])) $this->caracter(')');

                    // Nos encargamos de manejar las operaciones
                    if(isset($_POST['cuadrado'])) $this->unary_operation(fn($x) => pow($x, 2));
                    if(isset($_POST['potencia'])) $this->caracter('**');
                    if(isset($_POST['raiz_cuadrada'])) $this->unary_operation(fn($x) => sqrt($x));
                    if(isset($_POST['potencia10'])) $this->unary_operation(fn($x) => pow($x, 10));
                    if(isset($_POST['logaritmo'])) $this->unary_operation(fn($x) => log10($x));
                    if(isset($_POST['logaritmo_neperiano'])) $this->unary_operation(fn($x) => log($x));
                    if(isset($_POST['modulo'])) $this->caracter('%');
                    if(isset($_POST['mas_menos'])) $this->unary_operation(fn($x) => $x * (-1));
                    if(isset($_POST['seno'])) $this->seno();
                    if(isset($_POST['coseno'])) $this->coseno();
                    if(isset($_POST['tangente'])) $this->tangente();
                    if(isset($_POST['factorial'])) $this->unary_operation(function($x) {
                                                                            $factorial = 1;

                                                                            for ($i = $x; $i > 1; $i--)
                                                                                $factorial *= $i;

                                                                            return $factorial;
                                                                        });
                    
                    if(isset($_POST['shift'])) $this->shift();

                    // Nos encargamos de manejar la memoria
                    if(isset($_POST['mr'])) $this->mr();
                    if(isset($_POST['mc'])) $this->mrc();
                    if(isset($_POST['guardar_en_memoria'])) $this->guardar_en_memoria();

                    // Otros botones
                    if(isset($_POST['backspace'])) $this->backspace();

                    $_SESSION['sesion_pantalla'] .= $this->pantalla;
                }
            }

            // +-----------------+
            // | -*- GETTERS -*- |
            // +-----------------+

            public function get_angulo() {
                if (isset($_SESSION['es_radianes']))
                    return $_SESSION['es_radianes'] ? 'RAD' : 'DEG';
            }

            public function get_coseno() {
                if (isset($_SESSION['es_funcion_circular']))
                    return $_SESSION['es_funcion_circular'] ? 'cosh' : 'cos';
            }

            public function get_seno() {
                if (isset($_SESSION['es_funcion_circular']))
                    return $_SESSION['es_funcion_circular'] ? 'senh' : 'sen';
            }

            public function get_tangente() {
                if (isset($_SESSION['es_funcion_circular']))
                    return $_SESSION['es_funcion_circular'] ? 'tanh' : 'tan';
            }

            // +------------------------+
            // | -*- UNARY OPERATOR -*- |
            // +------------------------+
            
            private function unary_operation($function) {
                if (isset($_SESSION['sesion_pantalla']))
                    try {
                        $expresion = $function($_SESSION['sesion_pantalla']);
                        $_SESSION['sesion_pantalla'] = eval("return $expresion ;"); 
                    } catch (Error $e) {
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    }
            }

            // +-----------------------+
            // | -*- TRIGONOMETRÍA -*- |
            // +-----------------------+

            private function seno() {
                if ($_SESSION['es_funcion_circular']) // si estamos trabajando con funciones circulares: sinh
                    $this->unary_operation(fn($x) => sinh($this->angulo($x)));
                else // si estamos trabjando con funciones trigonométricas convencionales
                    $this->unary_operation(fn($x) => sin($this->angulo($x)));
            }

            private function coseno() {
                if ($_SESSION['es_funcion_circular']) // si estamos trabajando con funciones circulares: cosh
                    $this->unary_operation(fn($x) => cosh($this->angulo($x)));
                else // si estamos trabjando con funciones trigonométricas convencionales
                    $this->unary_operation(fn($x) => cos($this->angulo($x)));
            }

            private function tangente() {
                if ($_SESSION['es_funcion_circular'])  // si estamos trabajando con funciones circulares: tanh
                    $this->unary_operation(fn($x) => tanh($this->angulo($x)));
                else // si estamos trabjando con funciones trigonométricas convencionales
                    $this->unary_operation(fn($x) => tan($this->angulo($x)));
            }

            // +------------------------------+
            // | -*- MANEJO de CARACTERES -*- |
            // +------------------------------+

            private function backspace() {
                $_SESSION['sesion_pantalla'] = substr($_SESSION['sesion_pantalla'],
                                                      0,
                                                      strlen($_SESSION['sesion_pantalla']) - 1);
            }

            // +-----------------+
            // | -*- MEMORIA -*- |
            // +-----------------+

            // Básicamente guarda el último valor que hemos introducido
            private function guardar_en_memoria() {
                $_SESSION['sesion_memoria'] = $_SESSION['sesion_pantalla'];
            }

            private function mr() {
                $_SESSION['sesion_pantalla'] = $_SESSION['sesion_memoria'];
            }

            // +------------------+
            // | -*- misc. -*- |
            // +------------------+

            private function cambiar_unidades_angulo() {
                $_SESSION['es_radianes'] = !$_SESSION['es_radianes'];
            }

            private function angulo($x) {
                return $_SESSION['es_radianes'] ? $x : ($x * (M_PI / 180.0));
            }

            private function shift() {
                $_SESSION['es_funcion_circular'] = !$_SESSION['es_funcion_circular'];
            }

        }

        $calculadora = new CalculadoraCientifica();

        $pantalla = $_SESSION['sesion_pantalla'];

        $angulo = $calculadora->get_angulo();

        $seno = $calculadora->get_seno();
        $coseno = $calculadora->get_coseno();
        $tangente = $calculadora->get_tangente();

        echo "
        <form action='#' method='post'>
            <!-- PANTALLA para mostrar los resultados -->
            <label for='result' hidden>Pantalla para mostrar los resultados de los cálculos</label>
            <input type='text' id='result' value='$pantalla' disabled>
    
            <!-- Units -->
            <input type='submit' value='$angulo' name='unidad_angulo' />

            <!-- Memory management -->
            <input type='submit' value='MC' name='mc' />
            <input type='submit' value='MR' name='mr' />
            <input type='submit' value='M-' name='m-' />
            <input type='submit' value='M+' name='m+' />
            <input type='submit' value='MS' name='guardar_en_memoria' />

            <!-- Buttons to perform the calculations -->
            <input type='submit' value='SHIFT'  name='shift' />
            <input type='submit' value='&#960;' name='pi' />
            <input type='submit' value='e'      name='e' />
            <input type='submit' value='C'      name='borrar' />
            <input type='submit' value='back'   name='backspace' />

            <input type='submit' value='x^2'       name='cuadrado' />
            <input type='submit' value='$seno'     name='seno' />
            <input type='submit' value='$coseno'   name='coseno' />
            <input type='submit' value='$tangente' name='tangente' />
            <input type='submit' value='mod'       name='modulo' />

            <input type='submit' value='&#8730;' name='raiz_cuadrada' />
            <input type='submit' value='('       name='parentesis_izquierdo' />
            <input type='submit' value=')'       name='parentesis_derecho' />
            <input type='submit' value='n!'      name='factorial' />
            <input type='submit' value='&#247;'  name='division' />

            <input type='submit' value='x^y'    name='potencia' />
            <input type='submit' value='7'      name='7' />
            <input type='submit' value='8'      name='8' />
            <input type='submit' value='9'      name='9' />
            <input type='submit' value='&#215;' name='producto' />

            <input type='submit' value='10x' name='potencia10' />
            <input type='submit' value='4'   name='4' />
            <input type='submit' value='5'   name='5' />
            <input type='submit' value='6'   name='6' />
            <input type='submit' value='-'   name='resta' />

            <input type='submit' value='log' name='logaritmo' />
            <input type='submit' value='1'   name='1' />
            <input type='submit' value='2'   name='2' />
            <input type='submit' value='3'   name='3' />
            <input type='submit' value='+'   name='suma' />

            <input type='submit' value='ln'     name='logaritmo_neperiano' />
            <input type='submit' value='&#177;' name='mas_menos' />
            <input type='submit' value='0'      name='0' />
            <input type='submit' value='.'      name='punto' />
            <input type='submit' value='='      name='igual' />
        </form>";
    ?>
</body>
</html>