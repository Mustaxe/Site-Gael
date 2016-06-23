;(function($, window){
	'use strict';

	function NavigationJobs(elem){
		this.elem = elem;

		this.menu = $("#bg-menu");
		this.window = $(window);

		this.containerThumbs = this.elem.find("#container-work-home");

		this.wrapJob = this.elem.find("#wrap-job");

		this.currentJobImg = 0;
		this.currentJobGallery = null;
	}

	NavigationJobs.prototype.init = function init(){
		if(!this.data){
			throw new Error("Data of thumbs empty!");
		}

		this.addEventListener();
	}

	NavigationJobs.prototype.setData = function setData(data){
		this.data = data;
	}

	NavigationJobs.prototype.findID = function findID(numId){
		return function(elem){
			return elem.id == numId;
		}

	}

	/*
		Listeners
	*********************************************************************/

	NavigationJobs.prototype.addEventListener = function addEventListener(){
		this.window.on('work.open.gallery' , this.onOpenGallery.bind(this));
		this.window.on('work.close.gallery' , this.onCloseGallery.bind(this));

		this.window.on('resize' , this.resizeActions.bind(this));
	}

	NavigationJobs.prototype.onOpenGallery = function onOpenGallery(e, pNum, effect){
		this.openGallery(pNum, true);
	}

	NavigationJobs.prototype.onCloseGallery = function onCloseGallery(e, pNum, effect){
		this.closeGallery(true);
	}


	/*
		Navigation
	*********************************************************************/

	NavigationJobs.prototype.openGallery = function openGallery(pNum, effect){	

		var dataJob = this.data.filter(this.findID(pNum))[0];		

		this.jobOpened = true;

		this.clearContainer();

		this.currentJobGallery = new Job(this.wrapJob);
		this.currentJobGallery
			.init()
			.populate(dataJob);


		TweenMax.to(this.containerThumbs, ((effect === undefined || effect)? .8 : 0 ), {
			marginLeft: -(this.window.width()),
			ease: Quart.easeInOut,
			onComplete: this.onCompleteOpen.bind(this)
		});

		this.currentJobGallery.job.css('display', 'block');
		this.currentJobGallery.assetsContent.eq(0).css({
			'opacity': 1,
			'display': 'block'
		});

		this.currentJobGallery.applySizeJobInfo(); ///
	}

	NavigationJobs.prototype.onCompleteOpen = function onCompleteOpen(){
		this.resizeActions();
	}

	NavigationJobs.prototype.closeGallery = function closeGallery(effect){
		this.jobOpened = false;

		TweenMax.to(this.containerThumbs , ((effect === undefined || effect)? .8 : 0 ) , {
			marginLeft: 0,
			ease: Quart.easeInOut,
			onComplete: this.onCompleteClose.bind(this)
		});
	}

	NavigationJobs.prototype.onCompleteClose = function onCompleteClose(){
		this.currentJobGallery.job.css("display", "none");
		this.currentJobGallery.assetsContent.css({
			'opacity': 0,
			'display': 'none'
		});

		this.currentJobImg = null;
		this.currentJobGallery = null;
		this.resizeActions();
	}

	NavigationJobs.prototype.clearContainer = function clearContainer() {
		if(this.currentJobGallery){
			this.currentJobGallery.removeEventListener();
		}

		this.wrapJob.html('');
	}

	NavigationJobs.prototype.resizeActions = function resizeActions(){
		var menuHeight = this.menu.height(),
			wW = this.window.width(),
			wH = this.window.height() - menuHeight;


		this.elem.css('height', wH);
	}

	window.NavigationJobs = NavigationJobs;

})(jQuery, window);
