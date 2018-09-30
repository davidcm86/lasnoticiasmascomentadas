<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<?php
echo "Error al coger los comentarios del periodico: " . $datos['periodico'];
echo "<br>";
if (isset($datos['noticia']['enlace'])) {
    echo 'Enlace: ' . $datos['noticia']['enlace'] . '<br>';
}
if (isset($datos['noticia']['num_comentarios'])) {
    echo 'Num Comentarios: ' . $datos['noticia']['num_comentarios'] . '<br>';
}
if (isset($datos['noticia']['imagen'])) {
    echo 'Imagen: ' . $datos['noticia']['imagen'] . '<br>';
}
if (isset($datos['noticia']['titular'])) {
    echo 'Titular: ' . $datos['noticia']['titular'] . '<br>';
}
?>
