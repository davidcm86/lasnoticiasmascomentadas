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

class CrawlerEspania2Shell extends Shell {

    public function main() {
        $this->loadModel('Periodicos');
        $periodicos = $this->Periodicos->find('all', [
            'conditions' => ['activo' => 1, 'dominio_id' => 1]
        ]);
        foreach ($periodicos as $periodico) {
            switch ($periodico->slug) {
                case '20-minutos':
                    $this->__20Minutos($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                /*case 'el-diario': tarda muchisimo en leer las url con phatom, como si bloquearan unos segundos la ip
                    $this->__elDiario($periodico->enlace, $periodico->id, $periodico->slug);
                    break;*/
                case 'libertad-digital':
                    $this->__libertadDigital($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
            }
        }
    }

    /*
    * libertaddigital.com
    */
    private function __libertadDigital($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        foreach($html->find('.bloque ol li a') as $element) {
            $enlaces[] = $element->href;
        }
        $enlaces = array_unique($enlaces);
        $enlacesComments = array();
        $rutaJs = WWW_ROOT . 'js/save_page_libertaddigital.js ';
        $phantomJS = '/var/www/lasnoticiasmascomentadas/bin/phantomjs ';
        foreach ($enlaces as $key => $enlace) {
            $ejecutar = $phantomJS . $rutaJs . $enlace;
            $numComentarios = shell_exec($ejecutar);
            $comentarioExplode = explode('xxx', $numComentarios);
            $numComentarios = trim($comentarioExplode['1']);
            if (!empty($numComentarios) && is_numeric($numComentarios) && $numComentarios != 0) {
                $tamanio = strlen($numComentarios)/2;
                $numComentarios = substr($numComentarios, $tamanio);
                $html = file_get_html($enlace);
                if (!empty($html)) {
                    $datosNoticia[$key]['num_comentarios'] = trim($numComentarios);
                    $datosNoticia[$key]['enlace'] = $enlace;
                    // imagen noticia
                    $el = $html->find("meta[property='og:image:url']", 0);
                    if (!empty($el->content)) $datosNoticia[$key]['imagen'] = $el->content;
                    // titular noticia
                    $elTitle = $html->find("meta[property='og:title']", 0);
                    if (!empty($elTitle->content)) $datosNoticia[$key]['titular'] = $elTitle->content;
                }
            }
            sleep('8');
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
    * eldiario.es
    */
    private function __elDiario($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        $datosNoticia = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('a') as $element) {
            if(strpos($element->href, '.html') && strlen($element->href) > 50 && !strpos($element->href, 'facebook') && 
                !strpos($element->href, 'vertele') && !strpos($element->href, 'responde') && !strpos($element->href, 'clm')
                && !strpos($element->href, '#documento')) {
                $enlaces[] = str_replace('#comments', '', $element->href);
            }
        }
        $enlaces = array_unique($enlaces);
        $rutaJs = WWW_ROOT . 'js/save_page_eldiario.js ';
        $phantomJS = '/var/www/lasnoticiasmascomentadas/bin/phantomjs ';
        foreach ($enlaces as $key => $enlace) {
            if (!strpos($enlace, 'diario.es')) $enlace = 'http://www.eldiario.es' . $enlace;
            $html = file_get_html($enlace);
            $ejecutar = $phantomJS . $rutaJs . $enlace;
            $numComentarios = shell_exec($ejecutar);
            $comentarioExplode = explode('xxx', $numComentarios);
            $numComentarios = trim($comentarioExplode['1']);
            if (!empty($html) && !empty($numComentarios) && strlen(trim($numComentarios)) < 5 && $numComentarios != 0
                && ($numComentarios != 'null' || $numComentarios != null)) {
                    $datosNoticia[$key]['enlace'] = $enlace;
                    $datosNoticia[$key]['num_comentarios'] = $numComentarios;
                    // titular noticia
                    $elTitle = $html->find("meta[property='og:title']", 0);
                    if (!empty($elTitle->content)) $datosNoticia[$key]['titular'] = $elTitle->content;
                    // recuperar imagen
                    $element = $html->find("meta[property='og:image']", 0);
                    if (!empty($element->content)) $datosNoticia[$key]['imagen'] = $element->content;
                    if (!isset($datosNoticia[$key]['num_comentarios'])) unset($datosNoticia[$key]);
            }
            sleep('3');
            if ($key == 25) break; // hay muchos enlaces y no quremos coger más, lo más posible es q el mas comentado este ahi
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
    * 20minutos.es cogemos la noticia mas comentada de un tabs que tienen
    */
    private function __20Minutos($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $rutaJs = WWW_ROOT . 'js/save_page_20minutos.js ';
        $phantomJS = '/var/www/lasnoticiasmascomentadas/bin/phantomjs ';
        $ejecutar = $phantomJS . $rutaJs . $enlacePagina;
        $numComentarios = trim(shell_exec($ejecutar));
        // recuperamos los datos que queremos
        $numComentariosGet = explode('comentarios:', $numComentarios);
        $enlaceNoticia = explode('href:', $numComentarios);
        $enlaceLimpio = explode('remote', trim($enlaceNoticia['1']));
        $html2 = file_get_html($enlaceLimpio['0']);
        $datosNoticia[0]['num_comentarios'] = trim($numComentariosGet['1']);
        $datosNoticia[0]['enlace'] = $enlaceNoticia['1'];
        // imagen noticia
        $el = $html2->find("meta[property='og:image']", 0);
        if (!empty($el->content)) $datosNoticia[0]['imagen'] = $el->content;
        // titular noticia
        $elTitle = $html2->find("meta[property='og:title']", 0);
        if (!empty($elTitle->content)) $datosNoticia[0]['titular'] = str_replace(' - 20minutos.es', '', $elTitle->content);
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);
        $this->__generarPosiciones();
    }

    /*
    * lavanguardia.com
    */
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