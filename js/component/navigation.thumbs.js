;(function($, window){
	'use strict';

	function NavigationThumbs(elem){
		this.elem = elem;

		this.window = $(window);

		this.paginate = this.elem.find('.paginate');
		this.previous = this.paginate.find('#previous a');
		this.next = this.paginate.find('#next a');

		this.numOfElem = 7;

		this.thumbs = [];

		for (var i = 1; i <= this.numOfElem ; i++) {
			var $thumb = this.elem.find('#item-' + i);
			this.thumbs.push( new Thumb( $thumb, i));
			this.thumbs[i-1].init();
		};

		this.nav = new Paginate();
		this.nav.range = this.numOfElem;
	}

	NavigationThumbs.prototype.init = function init(){
		if(!this.data){
			throw new Error("Data of thumbs empty!");
		}

		this.populateThumbs( this.nav.next() );
		this.resetThumb();

		this.validNavigation();

		this.addEventListener();
	}

	NavigationThumbs.prototype.setData = function setData(data){
		this.data = data;
		this.nav.setData( this.data );
	}

	NavigationThumbs.prototype.filterData = function filterData(id){
		return this.data.filter(function(obj){
				var categorias =  obj.categorias.split(',');
				return categorias.indexOf( id.toString() ) !== -1;

			});
	};

	/*
		Listeners
	*********************************************************************/

	NavigationThumbs.prototype.addEventListener = function addEventListener(){
		this.window.on('work.filter' , this.filterGallery.bind(this));
		this.previous.on('click', this.onClickPrevious.bind(this));
		this.next.on('click', this.onClickNext.bind(this));
	}

	NavigationThumbs.prototype.filterGallery = function filterGallery(e, filterID){
		var data = (!!filterID)? this.filterData(filterID) : this.data;
		this.nav.setData( data ).reset();

		this.frozenThumbs();
		this.populateThumbs( this.nav.next() );
		this.validNavigation();
	}

	NavigationThumbs.prototype.onClickPrevious = function onClickPrevious(e){
		e.preventDefault();
		if(this.nav.frist()){ return false; }

		this.frozenThumbs();
		this.populateThumbs( this.nav.previous() );
		this.validNavigation();


	}

	NavigationThumbs.prototype.onClickNext = function onClickNext(e){
		e.preventDefault();
		if(this.nav.last()){ return false; }

		this.frozenThumbs();
		this.populateThumbs( this.nav.next() );

		this.previous.fadeOut();
		this.next.fadeOut();

		this.validNavigation();

	}

	/*
		Change state thumb
	*********************************************************************/

	NavigationThumbs.prototype.resetThumb = function resetThumb(){
		for (var i = 0; i < this.numOfElem ; i++) {
			this.thumbs[i].reset();
		};
	}

	/*
		Populate Data
	*********************************************************************/

	NavigationThumbs.prototype.frozenThumbs = function frozenThumbs(){
		for (var i = 0; i < this.numOfElem ; i++) {
			this.thumbs[i].frozen();
		};
	};

	NavigationThumbs.prototype.populateThumbs = function populateThumbs(items){
		var length = items.length;

		for (var i = 0; i < length ; i++) {
			var dataThumb = items[i];

			this.thumbs[i]
				.populate(dataThumb)
				.open(i);

		};

	}

	/*
		Navigation
	*********************************************************************/
	NavigationThumbs.prototype.validNavigation = function validNavigation(){

		if(this.nav.frist() && this.nav.last()){
			this.paginate.fadeOut();
		}else{
			this.paginate.fadeIn();
		}

		if(this.nav.frist()){
			this.previous.fadeOut();
		}else{
			this.previous.fadeIn();
		}

		if(this.nav.last()){
			this.next.fadeOut();
		}else{
			this.next.fadeIn();
		}

	}

	window.NavigationThumbs = NavigationThumbs;

})(jQuery, window);
