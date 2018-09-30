<?php foreach ($topNoticias as $posicion => $noticia) { ?>
    <div class="row">
        <div class="[ col-xs-12 col-sm-offset-1 col-sm-10 ]">
            <ul class="event-list">
                <li>
                    <time datetime="2014-07-20 2000">
                        <span class="day">
                            <?php echo $noticia['num_comentarios']; ?>
                        </span>
                        <span class="day-two">
                            comentarios
                        </span>
                        <span class="month">
                            <img 
                                rel="tooltip"
                                title="<?php echo $noticia['texto_estado_posicion']; ?>" 
                                alt="<?php echo $noticia['texto_estado_posicion']; ?>" 
                                src="<?php echo $noticia['flecha']; ?>" 
                            />
                        </span>
                    </time>
                    <img alt="<?php echo $noticia['titular']; ?>" title="<?php echo $noticia['titular']; ?>" src="<?php echo $noticia['imagen']; ?>" />
                    <div class="info">
                        <h2 class="title"><a rel="nofollow" href="<?php echo $noticia['enlace']; ?>" target="_blank"><?php echo $noticia['titular']; ?></a></h2>
                        <ul>
                            <li alt="Este es el tiempo que lleva online la noticia más comentada del periodico <?php echo $noticia['nombre_periodico']; ?>" rel="tooltip" title="Este es el tiempo que lleva online la noticia más comentada del periodico <?php echo $noticia['nombre_periodico']; ?>" style="width:50%;">
                                <?php echo $this->element('tiempo-online', array('tiempo' => $noticia['created'], 'posicion' => $posicion)); ?><span class="fa fa-clock-o"></span>
                            </li>
                            <li style="width:44%;"><a href="<?php echo $noticia['enlace_periodico']; ?>" target="_blank" rel="nofollow"><?php echo $noticia['nombre_periodico']; ?></a></li>
                        </ul>
                    </div>
                    <div class="social">
                        <ul>
                            <li class="facebook" style="width:33%;">
                                <a href="#" onclick="shareFb('<?php echo $noticia['enlace']; ?>');return false;" rel="nofollow" target="_blank">
                                    <span class="fa fa-facebook"></span>
                                </a>
                            </li>
                            <li class="twitter" style="width:34%;">
                                <a href="#" onclick="shareTw('<?php echo $noticia['enlace']; ?>', '<?php echo $noticia['titular']; ?>');return false;" rel="nofollow" target="_blank"><span class="fa fa-twitter"></span></a>
                            </li>
                            <li class="google-plus" style="width:33%;">
                                <a href="#" onclick="shareGo('<?php echo $noticia['enlace']; ?>');return false;" rel="nofollow" target="_blank"><span class="fa fa-google-plus"></span></a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
            <?php if ($posicion == 1 || $posicion == 4) { ?>
            <ul class="event-list">
                <div class="col-sm-14">
                    <?= $this->element('Comun/publicidad'); ?>
                </div>
                </ul>
            <?php } ?>
        </div>
    </div>
<?php } ?>
<script>
    function shareFb(url) {
        window.open('https://www.facebook.com/sharer/sharer.php?u='+url,'facebook-share-dialog',"width=626, height=436")
    }
    function shareTw(url, titular) { 
        window.open("https://twitter.com/share?url="+url+"&text="+titular + ' - http://www.lasnoticiasmascomentadas.es', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600')
    }
    function shareGo(url) {
        window.open('https://plus.google.com/share?url='+url,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600')
    }
</script>