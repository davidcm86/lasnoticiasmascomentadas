<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\I18n\Time;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow(array('index', 'contacto'));
        $this->viewBuilder()->layout('default');
    }
    
    /**
     * Displays a view
     *
     * @param string ...$path Path segments.
     * @return void|\Cake\Network\Response
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function index() {
        $this->loadModel('TopNoticias');
        $this->loadModel('Periodicos');
        $periodicos = $this->Periodicos->find('all', [
            'conditions' => ['activo' => 1, 'dominio_id' => $this->dominioId]
        ]);
        $topNoticias = array();
        $join = array(
            'table' => 'periodicos',
            'alias' => 'Periodicos',
            'type' => 'inner',
            'conditions' => array('Periodicos.id = TopNoticias.periodico_id')
        );
        foreach ($periodicos as $key => $periodico) {
            $noticia = $this->TopNoticias->find('all', [
                'join' => array($join),
                'conditions' => ['TopNoticias.periodico_id' => $periodico->id],
                'order' => ['TopNoticias.modified DESC'],
                'fields' => [
                    'Periodicos.nombre', 'TopNoticias.ruta_imagen', 'TopNoticias.num_comentarios', 'TopNoticias.titular',
                    'TopNoticias.enlace', 'TopNoticias.created', 'TopNoticias.estado_posicion',
                    'TopNoticias.created', 'Periodicos.enlace'],
                'limit' => 1
            ])->toArray();
            if (!empty($noticia)) {
                $topNoticias[$key]['imagen'] = $noticia['0']['ruta_imagen'];
                $topNoticias[$key]['num_comentarios'] = $noticia['0']['num_comentarios'];
                $topNoticias[$key]['titular'] = $noticia['0']['titular'];
                $topNoticias[$key]['enlace'] = $noticia['0']['enlace'];
                $topNoticias[$key]['nombre_periodico'] = $noticia['0']['Periodicos']['nombre'];
                $topNoticias[$key]['enlace_periodico'] = $noticia['0']['Periodicos']['enlace'];
                if ($noticia['0']['estado_posicion'] == 1) {
                    $topNoticias[$key]['flecha'] = 'img/flecha-arriba.png';
                    $topNoticias[$key]['texto_estado_posicion'] = "La noticia ha subido posiciones";
                    $topNoticias[$key]['color-caja'] = 'background-color:#00cc00';
                } elseif ($noticia['0']['estado_posicion'] == 2) {
                    $topNoticias[$key]['flecha'] = 'img/flecha-iguales.png';
                    $topNoticias[$key]['texto_estado_posicion'] = "La noticia se mantienen en la misma posición";
                    $topNoticias[$key]['color-caja'] = 'background-color:#e6e600';
                } else {
                    $topNoticias[$key]['flecha'] = 'img/flecha-abajo.png';
                    $topNoticias[$key]['texto_estado_posicion'] = "La noticia ha bajado posiciones";
                    $topNoticias[$key]['color-caja'] = 'background-color:#cc0000';
                }

                $topNoticias[$key]['created'] = $noticia['0']['created'];
            }
        }
        usort($topNoticias, function($a, $b) {
            return $b['num_comentarios'] <=> $a['num_comentarios'];
        });
        $this->set('metas', $this->__metas());
        $this->set('topNoticias', $topNoticias);
    }

    public function contacto() {
        $this->autoRender = false;
        $this->viewBuilder()->setLayout('ajax');
        if (isset($this->request->data['email']) && isset($this->request->data['mensaje']) && !empty($this->request->data['email']) 
            && !empty($this->request->data['mensaje'])) {
            // enviar correo y mandar mensaje éxito
            $datos = $this->request->data;
            $this->__enviarMail($datos, 'contacto');
        }
    }

    // los  metas descripcione sy demás para cada dominio
    private function __metas() {
        $metas = array();
        switch ($this->dominioId) {
            case '1': // es
                $metas['title'] = "Las noticias mas comentadas en los periodicos ESPAÑOLES";
                $metas['description'] = "Estas son las noticias mas comentadas en los periodicos más populares de ESPAÑA";
                $metas['nav'] = "Estas son las noticias más comentadas en los periodicos ESPAÑOLES, actualizadas cada hora";
                break;
            case '2': // mx
                $metas['title'] = "Las noticias mas comentadas en los periodicos MEXICANOS";
                $metas['description'] = "Estas son las noticias mas comentadas en los periodicos más populares de MÉXICO";
                $metas['nav'] = "Estas son las noticias más comentadas en los periodicos MEXICANOS, actualizadas cada hora";
                break;
            default;
                $metas['title'] = "Las noticias mas comentadas en los periodicos ESPAÑOLES";
                $metas['description'] = "Estas son las noticias mas comentadas en los periodicos más populares de ESPAÑA";
                $metas['nav'] = "Estas son las noticias más comentadas en los periodicos ESPAÑOLES, actualizadas cada hora";
        }
        return $metas;
    }

    private function __enviarMail($datos, $template){
        $email = new Email('default');
        $email->from(['dcarretero86@hotmail.com' => 'Las Noticias Mas Comentadas'])
            ->template($template)
            ->emailFormat('html')
            ->to('dcarretero861@gmail.com')
            ->subject('Contacto')
            ->viewVars(['datos' => $datos])
            ->send('My message');
    } 
}
