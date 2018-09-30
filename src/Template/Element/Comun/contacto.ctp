<div class="col-xs-12 col-sm-6 col-md-6 text-center">
    <h4 class="contacto">Contacta con la web <span class="fa fa-envelope-o"></span></h4>
    <div id="capa-contacto" style="display:none;">
        <form class="form-horizontal" action="/pages/contacto" method="post" id="contacto">
            <div class="form-group">
                <div class="col-sm-12">
                    <input type="email" name="email" class="form-control" id="email" placeholder="Introduzca su email" required="true">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-12">
                    <textarea maxlength="500" class="form-control" name="mensaje" rows="5" placeholder="Introduzca su mensaje" id="mensaje" required="true"></textarea>
                </div>
            </div>
            <div class="form-group"> 
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-default">Enviar</button>
                </div>
            </div>
        </form>
    </div>
    <div id="alert-contacto" class="alert alert-success" style="display:none;">
        <strong>Fantástico!</strong> Gracias por escribirnos. Pronto recibirás una respuesta ;)
    </div>
</div>