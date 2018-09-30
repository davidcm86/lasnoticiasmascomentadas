<?php
namespace App\Shell;

/**
* PROBLEMAS: NO PUEDO COGER LOS COMMENTS NUMERO DE LA VANGUARDIA Y EL ESPAÑOL (DE LOS MÁS VISITADOS)
*/

/*
* pintar los comentarios noticias
*select * from top_noticias order by modified desc,num_comentarios desc limit 5;
*/

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Datasource\ConnectionManager;

require_once(ROOT . DS . 'vendor' . DS . "html-dom" . DS . "simple_html_dom.php");
use SimpleHtmlDom;

class CrawlerEspania1Shell extends Shell {

    public function main() {
        $this->loadModel('Periodicos');
        $periodicos = $this->Periodicos->find('all', [
            'conditions' => ['activo' => 1, 'dominio_id' => 1]
        ]);
        foreach ($periodicos as $periodico) {
            switch ($periodico->slug) {
                case 'la-vanguardia':
                    $this->__laVanguardia($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                case 'el-mundo':
                    $this->__elMundo($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                case 'el-pais':
                    $this->__elPais($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                case 'abc':
                    $this->__abc($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                case 'el-confidencial':
                    $this->__elConfidencial($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
            }
        }
    }

    /*
    * elmundo.com
    */
    private function __elMundo($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('a') as $element) {
            if(strpos($element->href, 'www.elmundo.es/') && !strpos($element->href, '=') && strlen($element->href) > 70 && strpos($element->href, '.html')) {
                $enlaces[] = str_replace('#ancla_comentarios', '', $element->href);
            }
        }
        $enlaces = array_unique($enlaces);
        $enlacesComments = array();
        $cont = 0;
        foreach ($enlaces as $enlace) {
            // busco en cada articulo
            $html = file_get_html($enlace);
            if (!empty($html)) {
                $enlacesComments[$cont]['enlace'] = $enlace;
                // num comentarios
                foreach($html->find('.js-ueCommentsCounter') as $element) {
                    $enlacesComments[$cont]['num_comentarios'] = $element->plaintext;
                }
                // el src de la imagen de artículos sin video en la portada
                foreach($html->find('.container-image') as $element) {
                    foreach($element->find('img') as $element2) {
                        if (isset($element2->src) && !empty($element2->src)) {
                            $enlacesComments[$cont]['imagen'] = $element2->src;
                        }
                    }
                }
                // si no encuentra la imagen porque el artículo, su imagen principal es video, aqui cogemos esa imagen del video
                if (!isset($enlacesComments[$cont]['imagen'])) {
                    foreach($html->find('[itemprop=thumbnailUrl]') as $el){
                        $enlacesComments[$cont]['imagen'] = $el->content;
                    }
                }
                // el titular
                foreach($html->find('.js-headline') as $element) {
                    $enlacesComments[$cont]['titular'] = str_replace('"', '', $element->plaintext);
                }
                // si no coge alguan información, no lo metemos en el array final
                if (!isset($enlacesComments[$cont]['enlace']) || !isset($enlacesComments[$cont]['num_comentarios']) ||
                !isset($enlacesComments[$cont]['imagen']) || !isset($enlacesComments[$cont]['titular'])) {
                    unset($enlacesComments[$cont]);
                }
                $cont++;
                sleep('3'); // mejor no saturar a la web desde una misma ip
            }
        }
        // ordenamos por comentarios mas numerosos
        usort($enlacesComments, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($enlacesComments['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $enlacesComments['0'], $slugPeriodico, $idPeriodico);
        $this->__generarPosiciones();
    }

    /*
    * elpais.com
    */
    private function __elPais($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('.comentarios') as $key => $element) {
            if (!preg_match('/verne|cat.elpais.com|brasil.elpais.com|elviajero|=|cultura.elpais.com|cincodias.elpais.com|tentaciones.html|suscripciones|planeta_futuro.html|tematicos|vivienda.html|estados_unidos.html/i', $element->href)) {
                $enlaces[$key]['enlace'] = 'https://www.elpais.com' . str_replace('//', '', str_replace('#comentarios', '', $element->href));
                $enlaces[$key]['num_comentarios'] =  $element->plaintext;
            }
            
        }
        foreach ($enlaces as $key => $enlace) {
            if (!empty($enlace['enlace'])) {
                // busco en num comentarios en la propia pagina de comentarios, ya que en la estandar lo pintan con JS
                $html = file_get_html($enlace['enlace']);
                if (!empty($html)) {
                    // num comentarios
                    foreach($html->find('.articulo-titulo') as $element) {
                        $enlaces[$key]['titular'] = $element->plaintext;
                        break;
                    }
                    // imagenes
                    foreach($html->find("meta[property='og:image']") as $el){
                        $enlaces[$key]['imagen'] = $el->content;
                    }
                    sleep('3'); // mejor no saturar a la web desde una misma ip
                }
            }
        }
        // ordenamos por comentarios mas numerosos
        usort($enlaces, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($enlaces['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $enlaces['0'], $slugPeriodico, $idPeriodico);
        $this->__generarPosiciones();
    }

    /*
    * abc.es
    * el abc tiene una sección de lo más comentado, por lo que cojo la URL que me interesa
    */
    private function __abc($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        $datosNoticia = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find("section[name='comunidad']") as $element) {
            //echo $element;
            foreach($element->find("section") as $element2) {
                if (strpos($element2->plaintext, "Lo más comentado")) {
                    foreach($element2->find("li a") as $element3) {
                        $datosNoticia['titular'] = $element3->plaintext;
                        $datosNoticia['enlace'] = $element3->href;
                        break;
                    }
                    foreach($element2->find(".comentarios") as $element3) {
                        $datosNoticia['num_comentarios'] = intval(preg_replace('/[^0-9]+/', '', ($element3->plaintext), 10)); 
                        break;
                    }
                }
            }
        }
        $html = file_get_html($datosNoticia['enlace']);
        foreach($html->find("meta[property='og:image']") as $element) {
            $datosNoticia['imagen'] = $element->content;
        }
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia, $slugPeriodico, $idPeriodico);
        $this->__generarPosiciones();
    }

    /*
    * elconfidencial.com
    */
    private function __elConfidencial($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        $datosNoticia = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('a') as $element) {
            if(strpos($element->href, 'www.elconfidencial.com') && strpos($element->href, '_') && strlen($element->href) > 70
                && !strpos($element->href, '#')) {
                $enlaces[] = $element->href;
            }
        }
        foreach ($enlaces as $key => $enlace) {
            $html = file_get_html($enlace);
            if (!empty($html)) {
                $datosNoticia[$key]['enlace'] = $enlace;
                foreach($html->find(".comments-total-count") as $element) {
                    $numComentarios = str_replace(' ', '', str_replace('comentarios', '', $element->plaintext));
                    if (!empty($numComentarios) && is_numeric($numComentarios)) {
                        $datosNoticia[$key]['num_comentarios'] = $numComentarios;
                    }
                }
                // recuperar titular
                $element = $html->find("h1", 0);
                if (!empty($element->plaintext)) $datosNoticia[$key]['titular'] = $element->plaintext;
                // recuperar imagen
                $element = $html->find("meta[property='og:image']", 0);
                if (!empty($element->content)) $datosNoticia[$key]['imagen'] = $element->content;
                if (!isset($datosNoticia[$key]['num_comentarios'])) unset($datosNoticia[$key]);
            }
            sleep('3');
        }
        // ordenamos por comentarios mas numerosos
        usort($datosNoticia, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);
        $this->__generarPosiciones();
    }

    /*
    * elespanol.com
    * debido a que renderia los js despues de lanzar la página, utilizamos phantomjs para coger comments y ya todo lo demás
    */
    private function __elEspanol($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        $datosNoticia = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('a') as $element) {
            if(strpos($element->href, '.html') && strlen($element->href) > 30 && !strpos($element->href, '#comments')  &&
                !strpos($element->href, 'cronicaglobal') && !strpos($element->href, 'navarra.elespanol.com') &&
                !strpos($element->href, 'elandroidelibre.elespanol.com')) {
                    if (!strpos($element->href, 'www.elespanol.com')) {
                        $enlaces[] = 'http://www.elespanol.com' . $element->href;
                    } else {
                        $enlaces[] = $element->href;
                    }
               
            }
        }
        $enlaces = array_unique($enlaces);
        $cont = 0;
        $enlacesOrdenados = array();
        foreach ($enlaces as $enlace) {
            $enlacesOrdenados[$cont] = $enlace;
            $cont++;
        }
        $rutaJs = WWW_ROOT . 'js/save_page_elespanol.js ';
        $phantomJS = '/var/www/lasnoticiasmascomentadas/bin/phantomjs ';
        foreach ($enlacesOrdenados as $key => $enlace) {
            $ejecutar = $phantomJS . $rutaJs . $enlace;
            $numComentarios = shell_exec($ejecutar);
            if (!empty($numComentarios) && strlen(trim($numComentarios)) < 5 && $numComentarios != 0
                && ($numComentarios != 'null' || $numComentarios != null)) {
                    $html = file_get_html($enlace);
                    if (!empty($html)) {

                        $datosNoticia[$key]['num_comentarios'] = trim($numComentarios);
                        $datosNoticia[$key]['enlace'] = $enlace;
                        // imagen noticia
                        $el = $html->find("meta[property='og:image']", 0);
                        if (!empty($el->content)) $datosNoticia[$key]['imagen'] = $el->content;
                        // titular noticia
                        $elTitle = $html->find("meta[property='og:title']", 0);
                        if (!empty($elTitle->content)) $datosNoticia[$key]['titular'] = $elTitle->content;

                    }
                }
                sleep('5');
        }

        usort($datosNoticia, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);

    }


    private function __laVanguardia($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('a') as $element) {
            if(strlen($element->href) > 70 && strpos($element->href, '.html')  && !strpos($element->href, 'opinion')
            && !strpos($element->href, 'deportes') && !strpos($element->href, 'cultura') && !strpos($element->href, 'gente')
            && !strpos($element->href, 'series') && !strpos($element->href, 'local') && !strpos($element->href, 'moda')
            && !strpos($element->href, 'mundodeportivo')) {
                $enlaces[] = $element->href;
            }
        }
        $enlaces = array_unique($enlaces);
        $enlacesOrdenados = array();
        $cont = 0;
        foreach ($enlaces as $enlace) {
            $enlacesOrdenados[$cont] = $enlace;
            $cont++;
        }
        $rutaJs = WWW_ROOT . 'js/save_page_lavanguardia.js ';
        $phantomJS = '/var/www/lasnoticiasmascomentadas/bin/phantomjs ';
        foreach ($enlacesOrdenados as $key => $enlace) {
            $ejecutar = $phantomJS . $rutaJs . $enlace;
            $numComentarios = trim(shell_exec($ejecutar));
            $comentarioExplode = explode('xxx', $numComentarios);
            $numComentarios = trim($comentarioExplode['1']);
            if (!empty($numComentarios) && strlen(trim($numComentarios)) < 5 && $numComentarios != 0
                && ($numComentarios != 'null' || $numComentarios != null)) {
                    $html = file_get_html($enlace);
                    $datosNoticia[$key]['num_comentarios'] = trim($numComentarios);
                    $datosNoticia[$key]['enlace'] = $enlace;
                    // imagen noticia
                    $el = $html->find("meta[property='og:image']", 0);
                    if (!empty($el->content)) $datosNoticia[$key]['imagen'] = $el->content;
                    // titular noticia
                    $elTitle = $html->find("meta[property='og:title']", 0);
                    if (!empty($elTitle->content)) $datosNoticia[$key]['titular'] = $elTitle->content;
            }
            sleep('3');
        }
        usort($datosNoticia, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);
        $this->__generarPosiciones();
    }

    // guardamos las noticias y toda su info al igual que la imagen
    private function __guardarTopNoticias($rowExisteEnlace, $enlacesComments, $slugPeriodico, $idPeriodico) {
        if (empty($rowExisteEnlace)) {
            if (isset($enlacesComments['imagen']) && isset($enlacesComments['titular']) && 
                isset($enlacesComments['num_comentarios'])) {
                $topNoticias = $this->TopNoticias->newEntity($enlacesComments);
                $topNoticias->periodico_id = $idPeriodico;
                $topNoticias->dominio_id = 1;
                if ($this->TopNoticias->save($topNoticias)) {
                    $rutaImagen = WWW_ROOT . 'img' . DS . $slugPeriodico;
                    $this->__checkRuta($rutaImagen);
                    $rutaImagen = WWW_ROOT . 'img' . DS . $slugPeriodico . DS . date('m');
                    $this->__checkRuta($rutaImagen);
                    $rutaImagen = $rutaImagen . DS . $topNoticias->id . $this->__tipoImagen($topNoticias['imagen']);
                    $rutaImagenSaveBbdd = DS . 'img' . DS . $slugPeriodico . DS . date('m') . DS . $topNoticias->id . $this->__tipoImagen($topNoticias['imagen']);
                    if (copy($topNoticias['imagen'], $rutaImagen)) {
                        // guardamos la ruta en la noticia top
                        $updateTopNoticia = $this->TopNoticias->get($topNoticias->id);
                        $updateTopNoticia->ruta_imagen = $rutaImagenSaveBbdd;
                        shell_exec("jpegoptim -m 40 " . WWW_ROOT . 'img' . DS . $slugPeriodico . DS . date('m') . DS . $topNoticias->id . $this->__tipoImagen($topNoticias['imagen']));

                        if (!$this->TopNoticias->save($updateTopNoticia)) $this->log('Error salvando imagen noticia');
                    }
                } else {
                    $this->log('Error al salvar __elMundo');
                }
            } else {
                // algo ha fallado, enviar correo para saber del fallo y desactivar el periodico para no mostrar comentarios obsoletos
                $datos['periodico'] = $slugPeriodico;
                $datos['noticia'] = $enlacesComments;
                $this->__enviarMail($datos, 'error');
            }
        } else {
            // actualizar num comentarios
            $updateComentarios = $this->TopNoticias->get($rowExisteEnlace->id);
            $updateComentarios->num_comentarios = $enlacesComments['num_comentarios'];
            if (!$this->TopNoticias->save($updateComentarios)) $this->log('Error actualizando comentarios');
        }
    }

    // obtenemos el formato de la imagen
    private function __tipoImagen($tmpName) {
        switch (exif_imagetype($tmpName)) {
            case '1':
                return '.gif';
                break;
            case '2':
                return '.jpg';
                break;
            case '3':
                return '.png';
                break;
            case '17':
                return '.ico';
                break;
            default:
                return '.png';
        }
    }

    private function __checkRuta($rutaImagen) {
        if (!file_exists($rutaImagen)) {
            mkdir($rutaImagen, 0755);
        }
    }

    private function __checkNoticiaRepetida($enlace) {
        $this->loadModel('TopNoticias');
        $existeEnlace = $this->TopNoticias->find('all', [
            'conditions' => ['TopNoticias.enlace' => $enlace]
        ]);
        return $existeEnlace->first();
    }

    // ordenamos las noticias según los comentarios posición y los guardamos
    private function __generarPosiciones() {
        $comments = $this->__getComentariosParaOrdenar();
        $this->loadModel('TopNoticias');
        foreach ($comments as $posicionActual => $posicionComentario) {
            $posicionActual = $posicionActual+1;
            $updateTopNoticia = $this->TopNoticias->get($posicionComentario['id']);
            if (!empty($updateTopNoticia->posicion_anterior)) {
                if ($posicionComentario['posicion'] < $posicionActual) {
                    $updateTopNoticia->estado_posicion = 3; // baja
                } elseif ($posicionComentario['posicion'] > $posicionActual) {
                    $updateTopNoticia->estado_posicion = 1; // sube
                } else {
                    $updateTopNoticia->estado_posicion = 2; // igual
                }
                $updateTopNoticia->posicion_anterior = $posicionComentario['posicion'];
            } else {
                $updateTopNoticia->estado_posicion = 1; // sube
                $updateTopNoticia->posicion_anterior = $posicionActual;
            }
            $updateTopNoticia->posicion = $posicionActual;
            if (!$this->TopNoticias->save($updateTopNoticia)) $this->log('Error salvando posicion');
        }
        //shell_exec('chmod -R 777 /var/www/lasnoticiasmascomentadas/tmp/');
    }

    private function __getComentariosParaOrdenar() {
        $conn = ConnectionManager::get('default');
        $result = $conn->query("SELECT 
            t.periodico_id,
            (SELECT 
                    num_comentarios
                FROM
                    top_noticias
                WHERE
                    periodico_id = t.periodico_id
                ORDER BY created DESC
                LIMIT 1) AS num_comentarios,
            (SELECT 
                    id
                FROM
                    top_noticias
                WHERE
                    periodico_id = t.periodico_id
                ORDER BY created DESC
                LIMIT 1) AS id,
            (SELECT 
                    posicion
                FROM
                    top_noticias
                WHERE
                    periodico_id = t.periodico_id
                ORDER BY created DESC
                LIMIT 1) AS posicion
        FROM
            top_noticias AS t
                JOIN
            periodicos AS p ON p.id = t.periodico_id
        WHERE
            t.dominio_id = 1
        GROUP BY t.periodico_id;");
        $datosNoticias = array();
        foreach ($result as $key => $row) {
            $datosNoticias[$key]['num_comentarios'] = $row['num_comentarios'];
            $datosNoticias[$key]['periodico_id'] = $row['periodico_id'];
            $datosNoticias[$key]['id'] = $row['id'];
            $datosNoticias[$key]['posicion'] = $row['posicion'];
        }
        usort($datosNoticias, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        return $datosNoticias;
    }

    private function __enviarMail($datos, $template){
        $email = new Email('default');
        $email->from(['dcarretero86@hotmail.com' => 'Las Noticias Mas Comentadas'])
            ->template($template)
            ->emailFormat('html')
            ->to('dcarretero861@gmail.com')
            ->subject('Error')
            ->viewVars(['datos' => $datos])
            ->send('My message');
    }       
}
?>