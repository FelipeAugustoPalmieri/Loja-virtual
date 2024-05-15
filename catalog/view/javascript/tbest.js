function abrirModalTbest(){
    $('#contatbest').modal('show');
}

function fechaModalTbest(){
    $('#contatbest').modal('hide');
}

function mostrasenha(){
    var campotbest = $('#input-senhatbest');
    var linkmostra = $('.mostrasenha');
    var typecampo = campotbest.attr('type');
    if(typecampo == "password"){
        campotbest.attr('type', 'text');
        linkmostra.html('Esconder Senha');
    }else if(typecampo == "text"){
        campotbest.attr('type', 'password');
        linkmostra.html('Mostrar Senha');
    }

}

function trazerDadosTbest(){
    var cpf = $('#input-cpf').val();
    var login = $('#input-logintbest').val();
    var senha = $('#input-senhatbest').val();
    if(login.length > 0 && senha.length > 0){
        var logintrecho = "&login="+login+"&senha="+senha;
        $('#trazerdados').text('Carregando...').addClass('disabled').attr('disabled', 'disabled');
        $.blockUI({ 
            message: '<h1><img width="100" src="/image/catalog/carregando.gif" /></h1>',
            css: { background: 'none', border: 'none'}
        });
        $.ajax({
            url: 'index.php?route=account/register/consultacontatbest'+logintrecho,
            type: 'get',
            crossDomain: true,
            dataType: 'json',
            success: function(json) {
                preencherFormularioRegistro(json);
                $('#trazerdados').html('Trazer Dados').removeClass('disabled').removeAttr('disabled');
                $.unblockUI();
            },
            error: function(xhr, ajaxOptions, thrownError) {
                var html = '<div class="text-danger">Login ou Senha incorretos</div>';
                $('#input-logintbest').parent().find('.text-danger').remove();
                $('#input-logintbest').parent().append(html);
                $('#trazerdados').html('Trazer Dados').removeClass('disabled').removeAttr('disabled');
                $.unblockUI();
                $('#contatbest').modal('show');
            }
        });
    }else{
        var html = '<div class="text-danger">Para usar conta tbest, Usuário e senha são obrigatório</div>';
        $('#input-logintbest').parent().find('.text-danger').remove();
        $('#input-logintbest').parent().append(html);
        $.unblockUI();
        $('#contatbest').modal('show');
    }
    $.unblockUI();
    return false;
}