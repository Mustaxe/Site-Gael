;(function($, window){
	'use strict';

	function Work(elem){
		this.elem = elem;
		this.tltWork = this.elem.find('h2 > .tlt-work');

		this.menu = $("#bg-menu");
		this.window = $(window);

		/**
		*
		* TODO_CONFIG: Config de PATH
		*
		*/
		//this.url = 'http://70.32.77.170/~gael/gael.ag/service/cases/1/' + LANG;
		//this.url = 'http://localhost:8080/git/site_gael/Site-Gael/service/cases/1/' + LANG;
		this.url = 'http://homologacao.gael.ag/service/cases/1/' + LANG;
		//this.url = 'http://70.32.77.170/~gael/gael.ag/service/cases/1/' + LANG;
		//this.url = 'http://localhost:8080/git/site_gael/Site-Gael/service/cases/1/' + LANG;
		//this.url = 'http://gael.ag/service/cases/1';
		
		this.data = null;

		this.combo = new ComboBox(this.elem.find('.combobox'));

		this.thumbs = new NavigationThumbs(this.elem.find('#container-work-home'));
		this.jobs = new NavigationJobs(this.elem);
	}

	Work.prototype.init = function init(){
		this.getData()
			.done(this.requestDone.bind(this))
			.fail(this.requestFail.bind(this));

		this.combo.init();

		this.addEventListener();

		this.resizeActions();
	}

	/*
		Listeners
	*********************************************************************/

	Work.prototype.addEventListener = function addEventListener(){
		this.tltWork.on('hover', this.onTitleOver);
		this.tltWork.on('mouseleave', this.onTitleLeave);
		this.window.on('resize' , this.resizeActions.bind(this));
	}

	Work.prototype.onTitleOver = function onTitleOver(){
		TweenLite.to($(this), .5, {
			scale: 1.15,
			ease:Quint.easeOut
		});
	}

	Work.prototype.onTitleLeave = function onTitleLeave(){
		TweenLite.to($(this), .5, {
			scale: 1,
			marginLeft: 0,
			ease:Quint.easeOut
		});
	}

	Work.prototype.resizeActions = function resizeActions(){
		var menuHeight = this.menu.height()
			wH = this.window.height() - menuHeight;

		this.elem.css('height', wH);
	}

	/*
		Manipulate Ajax
	*********************************************************************/

	Work.prototype.getData = function getData(){
		return $.ajax({ url: this.url });
	}

	Work.prototype.requestFail = function requestFail(data){
		this.init();
	}

	Work.prototype.requestDone = function requestDone(data){
		if(data.cod == 200){
			this.data = data.res;
			this.build();
		}
	}

	/*
		Populate Data
	*********************************************************************/

	Work.prototype.build = function build(){
		this.thumbs.setData(this.data);
		this.jobs.setData(this.data);

		this.thumbs.init();
		this.jobs.init();
	}

	window.Work = Work;

})(jQuery, window);
