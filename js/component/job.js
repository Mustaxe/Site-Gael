;(function($, window){
	'use strict';

	function Job(elem) {	

		this.elem = elem;

		this.window = $(window);

		this.job = null;
		this.assetsContent = null;
		this.navNext = null;
		this.navPrev = null;
		this.btnMore = null;

		this.textBox = null;

		this.templateJob = new TemplateWorkJob();

		this.nav = new Paginate();
	}

	Job.prototype.init = function init(){

		this.addEventListener();

		return this;
	}

	/*
		Listeners
	*********************************************************************/

	Job.prototype.addEventListener = function addEventListener(){
		this.elem.on('click', '.btn-close-job', this.onClickClose.bind(this));
		this.elem.on('click', '.btn-more', this.onClickMore.bind(this));

		this.elem.on('click' , '.nav-prev', this.onClickPrev.bind(this));
		this.elem.on('click' , '.nav-next', this.onClickNext.bind(this));
	}

	Job.prototype.removeEventListener = function removeEventListener(){
		this.elem.off('click');
	}

	Job.prototype.onClickMore = function onClickMore(e){
		var btnMore = $(e.currentTarget)

		if (this.jobDescription == true){
			this.closeInfo(btnMore);
		} else {
			this.openInfo(btnMore);
		}

	};

	Job.prototype.onClickClose = function onClickClose(){		
		this.stopVideo(this.assetsContent.eq(this.nav.currentPage - 1));
		this.window.trigger('work.close.gallery');
	};

	Job.prototype.onClickPrev = function onClickPrev(e){
		e.preventDefault();
		if(this.nav.frist()){ return false; }

		this.prevJobImg();
	};

	Job.prototype.onClickNext = function onClickNext(e){
		e.preventDefault();
		if(this.nav.last()){ return false; }

		this.nextJobImg();
	};

	/*
		States Infos
	*********************************************************************/
	Job.prototype.closeInfo = function closeInfo() {
		this.animateInfo(114, 180);
		this.jobDescription = false;
	}

	Job.prototype.openInfo = function openInfo() {		
		var textBoxMaxHeight = 500,
			textBoxHeight = (this.textJobInfo.position()).top  + this.textJobInfo.height() + 25;

		if(textBoxHeight >= textBoxMaxHeight) {
			this.applySizeJobInfo();
			textBoxHeight = textBoxMaxHeight;
		}

		this.animateInfo(textBoxHeight, 45);

		this.jobDescription = true;
	}

	Job.prototype.animateInfo = function animateInfo(textBoxHeight, rotaitonImage) {
		TweenMax.to(this.jobInfo, .3, {
				height: textBoxHeight,
				ease: Quart.easeOut
			});

		TweenMax.to(this.btnMore.find("img"), .3, {
				rotation: rotaitonImage,
				ease: Quart.easeInOut
			});
	}

	/*
		Navigation
	*********************************************************************/
	Job.prototype.nextJobImg = function nextJobImg() {
		if(this.nav.last()){ return false; }

		this.closeInfo();

		this.nav.next();

		var element = this.assetsContent.eq(this.nav.currentPage - 1);
		this.stopVideo(element.prev());
		this.initVideo(element);

		element.css({
			'left': this.window.width(),
			'opacity': 1,
			'display': 'block'
		});

		TweenMax.to(element, .5, {
			'left': 0,
			'ease': Quart.easeInOut
		});

		this.refreshNavJob();
	}

	Job.prototype.prevJobImg = function prevJobImg() {
		if(this.nav.frist()){ return false; }

		this.closeInfo();

		var element = this.assetsContent.eq(this.nav.currentPage - 1);
		this.stopVideo(element);
		this.initVideo(element.prev());

		TweenMax.to(element, .5, {
			'left': this.window.width(),
			'ease': Quart.easeInOut
		});

		this.nav.previous();

		this.refreshNavJob();
	}
	/*
		Manipulation Video
	*********************************************************************/

	Job.prototype.isYoutube = function isYoutube(url){
		return /youtu/i.test(url);
	}

	Job.prototype.youtubeParser = function youtubeParser(url){
	    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
	    var match = url.match(regExp);
	    if (match && match[7].length == 11){
	        return match[7];
	    }else{
	        return '';
	    }
	}

	Job.prototype.vimeoParser = function vimeoParser(url){
		var regExp = /(\d*)$/;
	    var match = url.match(regExp);
	    if(match && match[1].length>1){
	    	return match[1];
	    }else{
	    	return '';
	    }
	}

	Job.prototype.initVideo = function initVideo(elem){
		if(elem.hasClass("j-video")){
			var videoContainer = elem.find('.continer-video'),
				videoID = elem.data('id-video').toString();

			this.currentPlayer = {
				container: videoContainer,
				btn: elem.find('.btn-play'),
				typeVideo: (this.isYoutube(videoID)) ? 'youtube' : 'vimeo',
				uniq: 'player-' + (new Date()).getTime()
			}	

			if(this.currentPlayer.typeVideo === 'youtube'){

				videoContainer.append('<div id="' + this.currentPlayer.uniq + '"></div>');

				new YT.Player(this.currentPlayer.uniq, {
						videoId: this.youtubeParser(videoID),
						height : '100%',
						width  : '100%',
						playerVars: { 'showinfo': 0 , 'rel': 0},
						events : {
							'onReady'      : this.onReadyYoutubeVideo.bind(this),
							'onStateChange': this.onStateChangeYoutubeVideo.bind(this)
						}
					});
			}else{
				videoContainer.append('<div id="playerVimeo"><iframe id="' + this.currentPlayer.uniq + '" src="//player.vimeo.com/video/' + this.vimeoParser(videoID) + '?portrait=0&player_id=' + this.currentPlayer.uniq + '&api=1" width="100%" height="100%" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>');
				this.currentPlayer.player =  $f(videoContainer.find('#playerVimeo #' + this.currentPlayer.uniq )[0]);
				this.currentPlayer.player.addEvent('ready',  this.onReadyVimeoVideo.bind(this));
			}

		}
	}

	Job.prototype.stopVideo = function stopVideo(element){
		if(element.hasClass("j-video")){
			if( !!this.currentPlayer && !!this.currentPlayer.player){
				if(this.currentPlayer.typeVideo === 'youtube'){
					this.currentPlayer.player.pauseVideo();
					this.currentPlayer.player.stopVideo();
					this.currentPlayer.player.destroy();
				}else{
					this.currentPlayer.player.api('unload');
				}
			}

			this.showBtnPlay();

			if(!!this.currentPlayer){
				this.currentPlayer.btn.off('click');
				this.currentPlayer.container.html('');
			}

			this.currentPlayer = null;
		}
	}

	Job.prototype.offReadyVimeoVideo =  function offReadyVimeoVideo(event){
		this.currentPlayer.player.removeEvent('play',  this.onPlayVimeoVideo.bind(this));
		this.currentPlayer.player.removeEvent('pause',  this.onPauseVimeoVideo.bind(this));
		this.currentPlayer.player.removeEvent('finish',  this.onPauseVimeoVideo.bind(this));
	}

	Job.prototype.onReadyVimeoVideo =  function onReadyVimeoVideo(event){
		this.currentPlayer.player.addEvent('play',  this.onPlayVimeoVideo.bind(this));
		this.currentPlayer.player.addEvent('pause',  this.onPauseVimeoVideo.bind(this));
		this.currentPlayer.player.addEvent('finish',  this.onPauseVimeoVideo.bind(this));
		if(!$._data(this.currentPlayer.btn[0], 'events')){
			this.currentPlayer.btn.on('click', this.onClickPlayer.bind(this) );
		}
	}

	Job.prototype.onPlayVimeoVideo =  function onPlayVimeoVideo(event){
		this.hideBtnPlay();
	}

	Job.prototype.onPauseVimeoVideo =  function onPauseVimeoVideo(event){
		this.showBtnPlay();
	}

	Job.prototype.onReadyYoutubeVideo =  function onReadyYoutubeVideo(event){
		this.currentPlayer.player = event.target;
		this.currentPlayer.playing = false;
		this.currentPlayer.btn.on('click', this.onClickPlayer.bind(this) );
	}

	Job.prototype.onStateChangeYoutubeVideo =  function onStateChangeYoutubeVideo(event){
		if (event.data == YT.PlayerState.PLAYING && !this.currentPlayer.playing) {
			this.hideBtnPlay();
		}
		if ( ( event.data == YT.PlayerState.PAUSED  || event.data == YT.PlayerState.CUED || event.data == YT.PlayerState.ENDED) && this.currentPlayer.playing) {
			this.showBtnPlay();
		}
	}

	Job.prototype.hideBtnPlay =  function hideBtnPlay(){
		this.currentPlayer.playing = true;
		this.currentPlayer.btn.css('display' , 'none');
	}
	
	Job.prototype.showBtnPlay =  function showBtnPlay(){
		if(!!this.currentPlayer){
			this.currentPlayer.playing = false;
			this.currentPlayer.btn.css('display' , 'block');
		}
	}

	Job.prototype.onClickPlayer =  function onClickPlayer(){
		if(!this.currentPlayer.playing){
			if(this.currentPlayer.typeVideo === 'youtube'){
				this.currentPlayer.player.playVideo();
			}else{
				this.currentPlayer.player.api('play');
			}

			this.closeInfo();
		}
	}

	/*
		Change state thumb
	*********************************************************************/

	Job.prototype.populate = function populate(data){
		this.job = $(this.templateJob.make(data));
		this.elem.prepend(this.job);

		this.nav.setData(data.assets);

		this.assetsContent = this.job.find(".j-content");

		this.navNext = this.job.find(".nav-next");
		this.navPrev = this.job.find(".nav-prev");

		this.reset();
		this.initBoxInfo();
		this.resizeActions();

		this.nav.next();
		this.refreshNavJob();
		this.initVideo(this.assetsContent.eq(this.nav.currentPage - 1));

		return this;
	}

	Job.prototype.reset = function reset(){
		var infoText;

		this.assetsContent  = this.job.find(".j-content");

		this.jobInfo = this.job.find('.job-info');
		this.callJobInfo = this.jobInfo.find('.call');
		this.textJobInfo = this.jobInfo.find('.text');

		this.btnMore = this.job.find('.btn-more');

		TweenMax.to(this.job.find('.nav-next'), 0, {
			rotation: 180
		});

		var textJobHeight = this.textJobInfo.height() ;		
		if(textJobHeight >= 280) {

			this.applySizeJobInfo();
		}
	}

	Job.prototype.refreshNavJob = function refreshNavJob() {
		if(this.nav.frist()){
			this.navPrev.hide();
		} else {
			this.navPrev.show();
		}

		if(this.nav.last()){
			this.navNext.hide();
		}else{
			this.navNext.show();
		}
	}

	Job.prototype.applySizeJobInfo = function applySizeJobInfo(){

		console.log('applySizeJobInfo');

		this.textJobInfo.css({
			'height': 315 - this.callJobInfo.height()
		});

		this.textJobInfo.mCustomScrollbar({
			theme:'rounded-dark'
		});
	}

	Job.prototype.initBoxInfo = function initBoxInfo(){
		this.jobDescription = true;
		TweenMax.to(this.btnMore.find('img'), 0, { rotation: 45 });
	}

	Job.prototype.resizeActions = function resizeActions(){
		var wW = this.window.width();

		this.job.find('.job-main').css('marginLeft', (wW/2)-500);
		this.navPrev.css('left', (wW/2)-500);
		this.navNext.css('left', (wW/2)+500);
	}

	window.Job = Job;

})(jQuery, window);


