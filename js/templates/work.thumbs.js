;(function($, window){
	'use strict';

	function TemplateWorkThumb(){

		Template.call(this);

		this.template =  "<div class='item'>"+
			"	<div class=\"w-over cell-full\" style=\"background-image: url({{imagem_thumb}})\"></div>" +
			"	<div class=\"w-image cell-full\" style=\"background-image: url({{imagem_thumb_over}});\"></div>" +
			"	<div class=\"w-resume cell-full\"> " +
			"		<p>{{titulo}} | {{descricao}}</p>" +
			"	</div>";
			"</div>";
	}

	TemplateWorkThumb.prototype = new Template();

	window.TemplateWorkThumb = TemplateWorkThumb;

})(jQuery, window);
