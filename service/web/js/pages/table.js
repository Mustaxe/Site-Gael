$(document).ready(function(){
	/* ---------- Datable ---------- */
	$('.datatable').dataTable({
		"sDom": "<'row'<'col-lg-6'l><'col-lg-6'f>r>t<'row'<'col-lg-12'i><'col-lg-12 center'p>>",
		"sPaginationType": "bootstrap",
		"aaSorting": [[ 4, "asc" ]],
		"oLanguage": {
			"sLengthMenu": "_MENU_ resultados por p&aacute;gina",
			"sInfo": "Mostrando _START_ de _END_ resultado(s).",
			"sSearch": "Pesquisa:",
			"sSearchEmpty": "Nenhum resultado encontrado.",
			"sZeroRecords": "Nenhum resultado encontrado.",			
			"sEmptyTable":  "Nenhum resultado encontrado.",
			"sInfoEmpty": "Nenhum resultado encontrado.",			
			"sInfoFiltered": "",
			"oPaginate": {
				"sPrevious": "Anterior",
				"sNext": "Pr&oacute;xima"
			},
			"sLoadingRecords": "Carregando...",
			"sProcessing": "Processando...",

		}
	});
});