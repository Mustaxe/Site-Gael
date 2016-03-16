var $assets = $('.assets'),
    $template = $assets.find('.panel').last(),
    lengthItens =  $assets.find('.panel').length;

function excluiImagem(idCase, idAssets, idContainer){
    $.post(window.baseUri + '/cases/imagem/'+idCase+'/'+idAssets, { _METHOD: "DELETE" }, "json")
        .done(function(data){
            if(data.cod == "200"){
                $('#confirmDelete .modal-body p').text(data.msg).css("color", "#008000").fadeIn('fast');
                setTimeout(function(){
                    $('#' + idContainer ).remove();
                    $('#confirmDelete').modal('hide');
                }, 1000);
            }else{
                $('#confirmDelete .modal-body p').text(data.msg).css("color", "#008000").fadeIn('fast');
            }
        })
        .fail(function(data){
            $('#confirmDelete .modal-body p').text(data.statusText).css("color", "#FF2121").fadeIn('fast');
            $('#confirmDelete #confirm').hide();
        });
}

$('#confirmDelete').on('show.bs.modal', function (e) {
    var relatedTarget = $(e.relatedTarget),
        message = relatedTarget.attr('data-message'),
        title = relatedTarget.attr('data-title')
        idAsset = relatedTarget.data('id-asset'),
        idCase = relatedTarget.data('id-cases');
        idContainer = relatedTarget.data('container');

    $(this).find('.modal-body p').text(message);
    $(this).find('.modal-title').text(title);
    $(this).find('.modal-footer #confirm')
        .data('container', idContainer)
        .data('id-cases', idCase)
        .data('id-asset', idAsset);

    $('#confirmDelete #confirm').show();
});

$('#confirmDelete #confirm').on('click', function(){
    var idAsset = $(this).data('id-asset'),
        idCase = $(this).data('id-cases');
        idContainer = $(this).data('container');

    console.log('Cancelarrr');
    console.log(!!idCase , !!idAsset);

    if(!!idCase && !!idAsset){
        excluiImagem(idCase, idAsset, idContainer);
    }else{
        $('#confirmDelete').modal('hide');
        $('#' + idContainer ).remove();
    }


});


$assets.on('click' , '#add-case' , function(){
  var newItem = $template.clone(),
      btnClose;


  lengthItens++;

  newItem.find('.btn-delete').removeClass('btn-hidden');

  newItem.find('label').attr('for', 'imagem_integra' + lengthItens );
  newItem.find('input[type="file"]').attr('id', 'imagem_integra' + lengthItens);

  newItem.find('input[type="file"]').val('');
  newItem.find('input[type="text"]').val('');

  newItem.find('.panel-heading .pull-left').after( btnClose );

  console.log( newItem.find('.btn-delete'));
  newItem.find('.btn-delete').data('container' , 'item-'+ lengthItens);

  newItem.attr('id' , 'item-'+ lengthItens);

  $(this).before(newItem);

});
// <a href="#confirmDelete" data-toggle="modal" data-title="Excluir Imagem Integra5" data-message="Tem certeza que deseja excluir?" role="button" class="btn btn-danger" style="padding-left:25px;padding-right:25px;" data-id="{{ cases.integra5_id }}" data-campo="imagem_integra5"><i class="fa fa-trash-o "></i> Remover</a>
