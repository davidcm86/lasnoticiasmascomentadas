<?php
namespace App\Shell;

/*
* pintar los comentarios noticias
*select * from top_noticias order by modified desc,num_comentarios desc limit 5;
*/

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;

require_once(ROOT . DS . 'vendor' . DS . "html-dom" . DS . "simple_html_dom.php");
use SimpleHtmlDom;

class CrawlerShell extends Shell {

    public function main() {
        shell_exec('chmod -R 777 /var/www/lasnoticiasmascomentadas/tmp/cache/');
        $this->loadModel('Periodicos');
        $periodicos = $this->Periodicos->find('all', [
            'conditions' => ['activo' => 1]
        ]);
        foreach ($periodicos as $periodico) {
            switch ($periodico->slug) {
                case 'la-vanguardia':
                    $this->__laVanguardia($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                /*case 'el-mundo':
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
                case 'el-espanol':
                    $this->__elEspanol($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                case 'libertad-digital':
                    $this->__libertadDigital($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                case 'el-diario':
                    $this->__elDiario($periodico->enlace, $periodico->id, $periodico->slug);
                    break;
                case '20-minutos':
                    $this->__20Minutos($periodico->enlace, $periodico->id, $periodico->slug);
                    break;*/
            }
        }
        $this->__generarPosiciones();
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
        /*usort($enlacesComments, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($enlacesComments['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $enlacesComments['0'], $slugPeriodico, $idPeriodico);
        $this->log($enlacesComments['0']);*/
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
                    sleep('1'); // mejor no saturar a la web desde una misma ip
                }
            }
        }
        // ordenamos por comentarios mas numerosos
        usort($enlaces, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($enlaces['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $enlaces['0'], $slugPeriodico, $idPeriodico);
        $this->log($enlaces['0']);
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
        $this->log($datosNoticia);
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
            sleep('1');
        }
        // ordenamos por comentarios mas numerosos
        usort($datosNoticia, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);
        $this->log($datosNoticia['0']);
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
                !strpos($element->href, 'elandroidelibre.elespanol.com') && !strpos($element->href, 'futbo/') && 
                 !strpos($element->href, 'deportes') && !strpos($element->href, 'cultura') && !strpos($element->href, 'corazon')
                  && !strpos($element->href, 'viajes') && !strpos($element->href, 'reportajes') && !strpos($element->href, 'motor')
                  && !strpos($element->href, 'estilo') && !strpos($element->href, 'social')) {
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
            $this->log($numComentarios);
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
                break;
        }
        /*usort($datosNoticia, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);
        $this->log($datosNoticia['0']);*/
    }

    /*
    * libertaddigital.com
    */
    private function __libertadDigital($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('a') as $element) {
            if(strpos($element->href, 'www.libertaddigital.com/') && strlen($element->href) > 70 && !strpos($element->href, '=') &&
              !strpos($element->href, '/fotos/') && !strpos($element->href, '/chic/') && !strpos($element->href, '/deportes/')) {
                $enlaces[] = str_replace('#ancla_comentarios', '', $element->href);
            }
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
            $this->log('NumComentarios:' . $numComentarios);
            if (!empty($numComentarios) && is_numeric($numComentarios) && $numComentarios != 0) {
                    $tamanio = strlen($numComentarios)/2;
                    $this->log('tam:' . $tamanio);
                    $numComentarios = substr($numComentarios, $tamanio);

                $this->log($numComentarios);
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
        //$this->log($datosNoticia['0']);
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
        }
        // ordenamos por comentarios mas numerosos
        usort($datosNoticia, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $this->log($datosNoticia);
        $rowExisteEnlace = $this->__checkNoticiaRepetida($datosNoticia['0']['enlace']);
        $this->__guardarTopNoticias($rowExisteEnlace, $datosNoticia['0'], $slugPeriodico, $idPeriodico);
        $this->log($datosNoticia['0']);
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
    }

    private function __laVanguardia($enlacePagina, $idPeriodico, $slugPeriodico) {
        $html = file_get_html($enlacePagina);
        $enlaces = array();
        // busco todos los enlaces y quito los que no me sirven
        foreach($html->find('a') as $element) {
            if(strlen($element->href) > 70 && strpos($element->href, '.html')  && !strpos($element->href, 'opinion')
            && !strpos($element->href, 'cultura') && !strpos($element->href, 'gente')
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
        
        foreach ($enlaces as $key => $enlace) {
            $ejecutar = $phantomJS . $rutaJs . $enlace;
            $numComentarios = trim(shell_exec($ejecutar));
            $comentarioExplode = explode('xxx', $numComentarios);
            $numComentarios = trim($comentarioExplode['1']);
            if (!empty($numComentarios) && strlen(trim($numComentarios)) < 5 && $numComentarios != 0
                && ($numComentarios != 'null' || $numComentarios != null)) {
                    $html = file_get_html($enlace);
                    $datosNoticia['general'][$key]['num_comentarios'] = trim($numComentarios);
                    $datosNoticia['general'][$key]['enlace'] = $enlace;
                    // imagen noticia
                    $el = $html->find("meta[property='og:image']", 0);
                    if (!empty($el->content)) $datosNoticia['general'][$key]['imagen'] = $el->content;
                    // titular noticia
                    $elTitle = $html->find("meta[property='og:title']", 0);
                    if (!empty($elTitle->content)) $datosNoticia['general'][$key]['titular'] = $elTitle->content;
            }
            sleep('3');
            if ($key == 20) break;
        }
        

        $categorias = $this->__getAllCategorias();
        // TODO: enbuclarse por las categorias padres y las de sus periodicos para juntarlas en el array
        foreach ($categorias as $categoria) {
            if ($categoria->slug != 'general') {
                $this->log('$categoria->id: ' . $categoria->id);
                $subcategorias = $this->__montarArraysCategorias($categoria->id, $idPeriodico);
                foreach ($subcategorias as $subcategoria) {
                    foreach ($datosNoticia['general'] as $key => $datoNoticia) {
                        $this->log($datoNoticia);
                        $this->log('$subcategoria: ' . $subcategoria->slug);
                        if (strpos($datoNoticia['enlace'], '/' . $subcategoria->slug . '/')) {
                            $datosNoticia[$categoria->slug][$key] = $datoNoticia;
                        }
                    }
                }
            }
        }
        $this->log($datosNoticia);
        die;





        usort($datosNoticia['general'], function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $rowExisteEnlace = $this->__checkNoticiaRepetida(current($datosNoticia['general']['enlace']));
        $this->__guardarTopNoticias($rowExisteEnlace, current($datosNoticia['general']), $slugPeriodico, $idPeriodico);
        if (isset($datosNoticia['deportes'])) {
            usort($datosNoticia['deportes'], function($a, $b) {
                return $b['num_comentarios'] <=> $a['num_comentarios'];
            });
        }
        $this->log($datosNoticia);

        $this->log('---------------');
        $this->log(current($datosNoticia['general']));


    }

    private function __montarArraysCategorias($categoriaId, $periodicoId) {
        $this->loadModel('CategoriasPeriodicos');
        $categoriasPeriodicos = $this->CategoriasPeriodicos->find('all', [
            'conditions' => ['categoria_id' => $categoriaId, 'periodico_id' => $periodicoId]
        ]);
        return $categoriasPeriodicos;
    }

    private function __getAllCategorias() {
        $this->loadModel('Categorias');
        $categorias = $this->Categorias->find('all');
        return $categorias;
    }

    // guardamos las noticias y toda su info al igual que la imagen
    private function __guardarTopNoticias($rowExisteEnlace, $enlacesComments, $slugPeriodico, $idPeriodico, $categoria = null) {
        if (empty($rowExisteEnlace)) {
            if (isset($enlacesComments['imagen']) && isset($enlacesComments['titular']) && 
                isset($enlacesComments['num_comentarios'])) {
                $topNoticias = $this->TopNoticias->newEntity($enlacesComments);
                $topNoticias->periodico_id = $idPeriodico;
                $topNoticias->posicionar = 1;
                $topNoticias->categoria_id = $categoria;
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
            'conditions' => ['posicionar' => 1],
            'order' => ['num_comentarios DESC']
        ])->toArray();
        $posicion = 1;
        foreach ($posicionesComentarios as $posicionComentario) {
            $updateTopNoticia = $this->TopNoticias->get($posicionComentario->id);
            $updateTopNoticia->posicion = $posicion;
            if (empty($posicionComentario->posicion_anterior)) {
                $this->log('Vacio 2');
                $updateTopNoticia->posicion_anterior = $posicion;
                $updateTopNoticia->estado_posicion = 1; // sube
            } else {
                if ($posicionComentario->posicion < $posicion) {
                    $this->log('Lleno 1');
                    $updateTopNoticia->estado_posicion = 3; // baja
                } elseif ($posicionComentario->posicion > $posicion) {
                    $this->log('Lleno 3');
                    $updateTopNoticia->estado_posicion = 1; // sube
                } else {
                    $this->log('Lleno 2');
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