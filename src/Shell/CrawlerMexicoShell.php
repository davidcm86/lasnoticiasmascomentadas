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

require_once(ROOT . DS . 'vendor' . DS . "html-dom" . DS . "simple_html_dom.php");
use SimpleHtmlDom;

class CrawlerMexicoShell extends Shell {

    public function main() {
        $this->loadModel('Periodicos');
        $periodicos = $this->Periodicos->find('all', [
            'conditions' => ['activo' => 1, 'dominio_id' => 2]
        ]);
        foreach ($periodicos as $periodico) {
            switch ($periodico->slug) {
                /*case 'el-universal':
                    $this->__elUniversal($periodico->enlace, $periodico->id, $periodico->slug);
                    break;*/
                case 'milenio':
                    $this->__milenio($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
            }
        }
        $this->__generarPosiciones();
    }

    private function __milenio($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        $datosNoticia = array();
        $cont = 1;
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find(".pane .lst-topmost") as $element) {
            if ($cont == 2) {
                $explodeNoticia = explode('01', $element);
                $explodeEnlaceNoticia = explode('href', $explodeNoticia[1]);
                $explodeEnlaceNoticiaTitle = str_replace('=', '', str_replace('"', '', explode('title', $explodeEnlaceNoticia[1])));
                $enlace = 'http://www.milenio.com' . $explodeEnlaceNoticiaTitle[0];
                $this->log("enalce: " . $enlace);
            }
            $cont++;
        }
        if (!empty($enlace)) {
            $html2 = file_get_html($enlace);
            // TODO: coger los comentarios con phantom
            //$datosNoticia[0]['num_comentarios'] = trim($numComentariosGet['1']);
            $datosNoticia[0]['enlace'] = $enlace;
            // imagen noticia
            $el = $html2->find("meta[property='og:image']", 0);
            if (!empty($el->content)) $datosNoticia[0]['imagen'] = $el->content;
            // titular noticia
            $elTitle = $html2->find("meta[property='og:title']", 0);
            if (!empty($elTitle->content)) $datosNoticia[0]['titular'] = $elTitle->content;
            $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
            $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);
        }
    }

    /*
    * elUniversal.com
    */
    private function __elUniversal($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('a') as $element) {
            if(!strpos($element->href, '#eu-listComments') && strpos($element->href, '/noticias/')) {
                $enlaces[] = $element->href;
            }
        }
        $enlaces = array_unique($enlaces);
        $cont = 0;
        $rutaJs = WWW_ROOT . 'js/save_page_eluniversal.js ';
        $phantomJS = '/var/www/lasnoticiasmascomentadas/bin/phantomjs ';
        foreach ($enlaces as $key => $enlace) {
            $html = file_get_html($enlace);
            $ejecutar = $phantomJS . $rutaJs . $enlace;
            $numComentarios = shell_exec($ejecutar);
            $comentarioExplode = explode('xxx', $numComentarios);
            $numComentarios = str_replace(' Comentarios', '', trim($comentarioExplode['1']));
            if (!empty($html) && !empty($numComentarios) && strlen(trim($numComentarios)) < 5 && $numComentarios != 0
                && ($numComentarios != 'null' || $numComentarios != null)) {
                    $datosNoticia[$key]['enlace'] = $enlace;
                    $datosNoticia[$key]['num_comentarios'] = $numComentarios;
                    // titular noticia
                    $elTitle = $html->find("meta[property='og:title']", 0);
                    if (!empty($elTitle->content)) {
                        $datosNoticia[$key]['titular'] = mb_convert_encoding($elTitle->content, 'UTF-8', 'ISO-8859-15');
                    }
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
        $this->log($datosNoticia);
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);

    }

    // guardamos las noticias y toda su info al igual que la imagen
    private function __guardarTopNoticias($rowExisteEnlace, $enlacesComments, $slugPeriodico, $idPeriodico) {
        if (empty($rowExisteEnlace)) {
            if (isset($enlacesComments['imagen']) && isset($enlacesComments['titular']) && 
                isset($enlacesComments['num_comentarios'])) {
                $topNoticias = $this->TopNoticias->newEntity($enlacesComments);
                $topNoticias->periodico_id = $idPeriodico;
                $topNoticias->posicionar = 1;
                $topNoticias->dominio_id = 2;
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
            $updateComentarios->posicionar = 1;
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
        $this->loadModel('TopNoticias');
        $posicionesComentarios = $this->TopNoticias->find('all', [
            'conditions' => ['posicionar' => 1, 'dominio_id' => 2],
            'order' => ['num_comentarios DESC']
        ])->toArray();
        $posicion = 1;
        foreach ($posicionesComentarios as $posicionComentario) {
            $updateTopNoticia = $this->TopNoticias->get($posicionComentario->id);
            $updateTopNoticia->posicion = $posicion;
            if (empty($posicionComentario->posicion_anterior)) {
                $updateTopNoticia->posicion_anterior = $posicion;
                $updateTopNoticia->estado_posicion = 1; // sube
            } else {
                if ($posicionComentario->posicion < $posicion) {
                    $updateTopNoticia->estado_posicion = 3; // baja
                } elseif ($posicionComentario->posicion > $posicion) {
                    $updateTopNoticia->estado_posicion = 1; // sube
                } else {
                    $updateTopNoticia->estado_posicion = 2; // igual
                }
                $updateTopNoticia->posicion_anterior = $posicionComentario->posicion;
                $updateTopNoticia->posicion = $posicion;
            }
            $updateTopNoticia->posicionar = 0; 
            if (!$this->TopNoticias->save($updateTopNoticia)) $this->log('Error salvando posicion');
            $posicion++;
        }
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