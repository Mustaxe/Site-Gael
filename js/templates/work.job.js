;(function($, window){
	'use strict';

	/*
		Template Work Job - Assets Image
	****************************************/

	function TemplateWorkJobImage(){
		Template.call(this);
		this.template = "<div class='j-content' style='background-image: url({{img}})'></div>";
	}

	TemplateWorkJobImage.prototype = new Template();


	/*
		Template Work Job - Assets Video
	****************************************/

	function TemplateWorkJobVideo(){
		this.template = "<div class='j-content j-video' data-id-video='{{url}}'>" +
		"	<div class='btn-play'  style='background-image: url({{img}})'><span class='play'>play</span></div>" +
		"	<div class='continer-video'></div>" +
		"</div>";
		Template.call(this);
	}

	TemplateWorkJobVideo.prototype = new Template();


	/*
		Template Work Job - Assets
	****************************************/

	function TemplateWorkJobAssets(){
		this.templateImage = new TemplateWorkJobImage();
		this.templateVideo = new TemplateWorkJobVideo();
	}

	TemplateWorkJobAssets.prototype = new Template();

	TemplateWorkJobAssets.prototype.make = function make(data){
		var result = '',
			item;
		for (var i in data) {
			item = data[i];
			if(item.hasOwnProperty('url') && item.url != ''){
				result += this.templateVideo.make(item);
			}else{
				result += this.templateImage.make(item);
			}
		}

		return result;
	}


	function TemplateWorkJob(){

		Template.call(this);

		var lblVoltar = LANG == 'pt' ? 'VOLTAR' : 'BACK';

		this.templateAssets = new TemplateWorkJobAssets();

		this.template =  "<div class='container-job' data-name='{{id}}'>" +
			"	<div class='nav-prev'></div>" +
			"	<div class='nav-next'></div>" +
			"	<div class='job-main'>" +
			"		<div class='box-info'>" +
			"			<div class='job-info'>" +
			"				<div class='btn-close-job'>" +
			"					<i><img src='images/ico-back.png' alt='" + lblVoltar + "'/></i> " + lblVoltar +
			"				</div>" +
			"				<h3>{{titulo}}</h3>" +
			"				<div class='call'>{{descricao}}</div>" +
			"				<hr/>" +
			"				<div class='text'>" +
			"					<p>{{texto}}</p>" +
			"				</div>" +
			"			</div>" +
			"			<div class='btn-more'>"+
			"				<i><img src='svg/ico-more.svg' alt='Ver mais'/></i>" +
			"			</div>" +
			"		</div>" +
			"	</div>" +
			"	<div class='job-assets'>" +
			"		{{assets}}" +
			"	</div>" +
			"</div>";
	}

	TemplateWorkJob.prototype = new Template();

	TemplateWorkJob.prototype.make = function make(data){
		var result = '',
			temp = new Template(),
			newData = $.extend({}, data);

		newData.assets = this.templateAssets.make(data.assets);

		result = temp.make.call(this , newData);

		return result;
	};


	window.TemplateWorkJob = TemplateWorkJob;

})(jQuery, window);
