<!DOCTYPE html>
<html lang="es">
<head>
	<title><?php echo $metas['title']; ?></title>
	<meta charset="utf-8">
	<meta name="description" content="<?php echo $metas['description']; ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="icon" type="image/png" href="/img/favicon.png" />
	<meta property="og:title" content="Las Noticias Mas Comentadas" />
	<meta property="og:type" content="article" />
	<meta property="og:url" content="<?php echo $_SERVER['SERVER_NAME']; ?>" />
	<meta property="og:description" content="Estas son las noticias más comentadas en los principales periodicos españoles" />
	<meta property="og:image" content="http://www.lasnoticiasmascomentadas.es/webroot/img/las-noticias-mas-comentadas.png" />	
	<meta property="fb:app_id" content="241273506397164"/>
	<?= $this->Html->css('style.css') ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-105316534-1', 'auto');
	  ga('send', 'pageview');
	</script>
	<style>
		@import url("http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900,400italic");
		@import url("//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css");
	</style>
</head>
<body>
<?php 
	if (isset($metas['nav'])) {
		echo $this->element('Comun/nav', array('textoNav' => $metas['nav'])); 
	}
?>
<div class="container">    
	<?= $this->fetch('content'); ?>
</div>
<?= $this->element('Comun/footer'); ?>
<?php echo $this->Html->script('scripts'); ?>
<?php echo $this->Html->script('countup'); ?>
</body>
</html>

